<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Content_Indexer {

	public static function full_sync() {
		$post_types = apply_filters( 'ceac_indexable_post_types', array( 'page', 'post' ) );

		foreach ( $post_types as $post_type ) {
			$posts = get_posts( array(
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			) );

			foreach ( $posts as $post_id ) {
				self::index_post( $post_id );
			}
		}

		CEAC_Knowledge::build_knowledge_links();
		update_option( 'ceac_last_sync', current_time( 'mysql' ) );
	}

	public static function on_post_save( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) || $post->post_status !== 'publish' ) {
			return;
		}

		$allowed = apply_filters( 'ceac_indexable_post_types', array( 'page', 'post' ) );
		if ( ! in_array( $post->post_type, $allowed, true ) ) {
			return;
		}

		self::index_post( $post_id );
		CEAC_Knowledge::build_knowledge_links();
	}

	public static function index_post( $post_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ceac_knowledge';
		$post  = get_post( $post_id );

		if ( ! $post ) {
			return false;
		}

		$content = wp_strip_all_tags( $post->post_content );
		$content = preg_replace( '/\s+/', ' ', $content );

		$tables = self::extract_tables( $post->post_content );
		if ( ! empty( $tables ) ) {
			$content .= "\n\nTabular Data:\n" . $tables;
		}

		$category = self::detect_category( $post );
		$keywords = self::extract_keywords_from_post( $post );

		$existing = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $table WHERE source_type = %s AND source_id = %d",
			$post->post_type,
			$post_id
		) );

		$data = array(
			'source_type' => $post->post_type,
			'source_id'   => $post_id,
			'title'       => $post->post_title,
			'content'     => $content,
			'url'         => get_permalink( $post_id ),
			'category'    => $category,
			'keywords'    => implode( ', ', $keywords ),
		);

		if ( $existing ) {
			$wpdb->update( $table, $data, array( 'id' => $existing ) );
		} else {
			$wpdb->insert( $table, $data );
		}

		return true;
	}

	private static function detect_category( $post ) {
		$title_lower   = strtolower( $post->post_title );
		$content_lower = strtolower( wp_strip_all_tags( $post->post_content ) );

		$rules = array(
			'compliance' => array( 'aml', 'ctf', 'compliance', 'kyc', 'regulation' ),
			'rates'      => array( 'rate', 'exchange', 'forex', 'currency' ),
			'contact'    => array( 'contact', 'location', 'office', 'support' ),
			'services'   => array( 'transfer', 'remittance', 'service', 'forex' ),
			'company'    => array( 'about', 'who we are' ),
		);

		foreach ( $rules as $category => $keywords ) {
			foreach ( $keywords as $kw ) {
				if ( strpos( $title_lower, $kw ) !== false || strpos( $content_lower, $kw ) !== false ) {
					return $category;
				}
			}
		}

		return $post->post_type === 'page' ? 'page' : 'post';
	}

	private static function extract_keywords_from_post( $post ) {
		$text  = strtolower( $post->post_title . ' ' . wp_strip_all_tags( $post->post_content ) );
		$words = str_word_count( $text, 1 );
		$freq  = array_count_values( $words );

		$stop = array( 'the', 'and', 'for', 'are', 'but', 'not', 'you', 'all', 'can', 'had', 'her', 'was', 'one', 'our', 'out', 'has', 'have', 'been', 'from', 'with', 'they', 'this', 'that', 'will', 'each', 'make', 'like', 'than', 'them', 'into', 'more', 'some', 'time', 'very', 'when', 'come', 'here', 'just', 'also', 'your', 'what', 'about', 'which', 'their', 'would', 'there', 'could', 'should', 'other', 'after', 'before', 'between', 'through', 'during', 'without', 'within', 'along', 'across', 'against' );

		$title_words = array_filter( explode( ' ', strtolower( $post->post_title ) ), function( $w ) {
			return strlen( $w ) > 2;
		} );

		$filtered = array();
		foreach ( $freq as $word => $count ) {
			$is_in_title = in_array( $word, $title_words, true );
			if ( ( strlen( $word ) > 3 && ! in_array( $word, $stop, true ) && $count >= 2 ) ||
			     ( $is_in_title && strlen( $word ) > 2 ) ) {
				$filtered[ $word ] = $count;
			}
		}

		arsort( $filtered );
		return array_slice( array_keys( $filtered ), 0, 15 );
	}

	private static function extract_tables( $html ) {
		if ( ! preg_match_all( '/<table[^>]*>(.*?)<\/table>/is', $html, $matches ) ) {
			return '';
		}

		$output = '';
		foreach ( $matches[1] as $table_html ) {
			$text = wp_strip_all_tags( $table_html );
			$text = preg_replace( '/\s+/', ' | ', trim( $text ) );
			$output .= $text . "\n";
		}
		return $output;
	}
}
