<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Knowledge {

	public static function search( $query, $limit = 5 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ceac_knowledge';

		$words = self::extract_keywords( $query );
		if ( empty( $words ) ) {
			return $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM $table ORDER BY updated_at DESC LIMIT %d",
				$limit
			) );
		}

		$like_clauses = array();
		$prepare_args = array();
		foreach ( $words as $word ) {
			$like_clauses[] = '(title LIKE %s OR content LIKE %s OR keywords LIKE %s)';
			$wildcard       = '%' . $wpdb->esc_like( $word ) . '%';
			$prepare_args[] = $wildcard;
			$prepare_args[] = $wildcard;
			$prepare_args[] = $wildcard;
		}

		$prepare_args[] = $limit;
		$sql = "SELECT * FROM $table WHERE " . implode( ' OR ', $like_clauses ) . " ORDER BY updated_at DESC LIMIT %d";

		$results = $wpdb->get_results( $wpdb->prepare( $sql, $prepare_args ) );

		if ( empty( $results ) ) {
			return array();
		}

		usort( $results, function( $a, $b ) use ( $words ) {
			$score_a = self::calculate_relevance_score( $a, $words );
			$score_b = self::calculate_relevance_score( $b, $words );
			return $score_b <=> $score_a;
		} );

		return array_slice( $results, 0, $limit );
	}

	private static function calculate_relevance_score( $item, $words ) {
		$title_lower    = strtolower( $item->title );
		$content_lower  = strtolower( $item->content . ' ' . $item->keywords );
		$score          = 0;

		foreach ( $words as $word ) {
			if ( strpos( $title_lower, $word ) !== false ) {
				$score += 4;
			} elseif ( strpos( $content_lower, $word ) !== false ) {
				$score += 1;
			}
		}

		return $score;
	}

	public static function calculate_confidence( $query, $results ) {
		if ( empty( $results ) ) {
			return 0.1;
		}

		$words       = self::extract_keywords( $query );
		if ( empty( $words ) ) {
			return 0.5;
		}

		$total_words = count( $words );
		$total_score = 0;
		$max_possible = $total_words * 4;

		foreach ( $results as $result ) {
			$total_score += self::calculate_relevance_score( $result, $words );
		}

		$confidence = min( 1.0, $total_score / ( $max_possible * 1.5 ) );

		$result_bonus = min( 0.2, count( $results ) * 0.04 );
		$confidence   = min( 1.0, $confidence + $result_bonus );

		return round( $confidence, 2 );
	}

	public static function find_relevant_snippet( $item, $query, $max_length = 180 ) {
		$text = $item->content;
		$words = self::extract_keywords( $query );

		$sentences = preg_split( '/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY );

		$best_sentence = '';
		$best_score    = 0;

		foreach ( $sentences as $sentence ) {
			$sentence_lower = strtolower( $sentence );
			$score = 0;
			foreach ( $words as $word ) {
				if ( strpos( $sentence_lower, $word ) !== false ) {
					$score++;
				}
			}
			if ( $score > $best_score ) {
				$best_score    = $score;
				$best_sentence = $sentence;
			}
		}

		if ( empty( $best_sentence ) ) {
			$best_sentence = wp_trim_words( $text, 20 );
		}

		return wp_trim_words( $best_sentence, 25 );
	}

	private static function extract_keywords( $query ) {
		$stop_words = array( 'the', 'a', 'an', 'is', 'are', 'was', 'what', 'how', 'can', 'do', 'does', 'i', 'my', 'your', 'about', 'من', 'في', 'هل', 'ما', 'كيف' );
		$words      = preg_split( '/\s+/', strtolower( trim( $query ) ) );
		$words      = array_filter( $words, function( $w ) use ( $stop_words ) {
			return strlen( $w ) > 2 && ! in_array( $w, $stop_words, true );
		} );
		return array_values( $words );
	}

	public static function seed_default_knowledge() {
		global $wpdb;
		$table = $wpdb->prefix . 'ceac_knowledge';

		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE source_type = 'seed'" );
		if ( $count > 0 ) {
			return;
		}

		$seed_file = CEAC_PLUGIN_DIR . 'data/seed-knowledge.json';
		if ( ! file_exists( $seed_file ) ) {
			self::insert_seed_data();
			return;
		}

		$data = json_decode( file_get_contents( $seed_file ), true );
		if ( ! is_array( $data ) ) {
			self::insert_seed_data();
			return;
		}

		foreach ( $data as $item ) {
			$wpdb->insert( $table, array(
				'source_type' => 'seed',
				'title'       => $item['title'],
				'content'     => $item['content'],
				'category'    => isset( $item['category'] ) ? $item['category'] : 'general',
				'keywords'    => isset( $item['keywords'] ) ? $item['keywords'] : '',
				'url'         => isset( $item['url'] ) ? $item['url'] : '',
			) );
		}

		self::build_knowledge_links();
	}

	private static function insert_seed_data() {
		global $wpdb;
		$table = $wpdb->prefix . 'ceac_knowledge';

		$items = array(
			array(
				'title'    => 'About Us',
				'content'  => 'We are a trusted service provider committed to delivering quality solutions to our customers. Our team is dedicated to providing excellent support and service.',
				'category' => 'company',
				'keywords' => 'about, company, services, information',
			),
			array(
				'title'    => 'Our Services',
				'content'  => 'We offer a range of services designed to meet your needs. Our platform provides reliable, secure, and efficient solutions with full regulatory compliance.',
				'category' => 'services',
				'keywords' => 'services, solutions, platform, reliable, secure',
			),
			array(
				'title'    => 'Compliance & Security',
				'content'  => 'We maintain strict compliance policies and security measures. All transactions undergo verification and ongoing monitoring to ensure safety and regulatory adherence.',
				'category' => 'compliance',
				'keywords' => 'compliance, security, verification, monitoring, regulatory',
			),
			array(
				'title'    => 'Contact & Support',
				'content'  => 'Contact us via our website contact form, email support, or visit our offices. Our support team is available during business hours to assist you.',
				'category' => 'contact',
				'keywords' => 'contact, support, phone, email, hours, help, office, business hours',
			),
			array(
				'title'    => 'Rate Information',
				'content'  => 'Rates and fees displayed on our website are indicative and subject to change. For confirmed rates and large transactions, please contact us directly.',
				'category' => 'rates',
				'keywords' => 'rates, fees, pricing, indicative, information',
			),
		);

		foreach ( $items as $item ) {
			$wpdb->insert( $table, array(
				'source_type' => 'seed',
				'title'       => $item['title'],
				'content'     => $item['content'],
				'category'    => $item['category'],
				'keywords'    => $item['keywords'],
			) );
		}

		self::build_knowledge_links();
	}

	public static function build_knowledge_links() {
		global $wpdb;
		$knowledge_table = $wpdb->prefix . 'ceac_knowledge';
		$links_table     = $wpdb->prefix . 'ceac_knowledge_links';

		$wpdb->query( "DELETE FROM $links_table" );

		$items = $wpdb->get_results( "SELECT id, category, keywords FROM $knowledge_table" );
		$by_category = array();

		foreach ( $items as $item ) {
			$by_category[ $item->category ][] = $item;
		}

		foreach ( $items as $item ) {
			if ( isset( $by_category[ $item->category ] ) ) {
				foreach ( $by_category[ $item->category ] as $related ) {
					if ( $related->id !== $item->id ) {
						$wpdb->insert( $links_table, array(
							'source_knowledge_id' => $item->id,
							'target_knowledge_id' => $related->id,
							'link_type'           => 'same_category',
							'strength'            => 0.8,
						) );
					}
				}
			}

			$item_keywords = array_map( 'trim', explode( ',', strtolower( $item->keywords ) ) );
			foreach ( $items as $other ) {
				if ( $other->id === $item->id ) {
					continue;
				}
				$other_keywords = array_map( 'trim', explode( ',', strtolower( $other->keywords ) ) );
				$overlap        = array_intersect( $item_keywords, $other_keywords );
				if ( count( $overlap ) >= 2 ) {
					$exists = $wpdb->get_var( $wpdb->prepare(
						"SELECT id FROM $links_table WHERE source_knowledge_id = %d AND target_knowledge_id = %d",
						$item->id,
						$other->id
					) );
					if ( ! $exists ) {
						$wpdb->insert( $links_table, array(
							'source_knowledge_id' => $item->id,
							'target_knowledge_id' => $other->id,
							'link_type'           => 'keyword_overlap',
							'strength'            => min( 1.0, count( $overlap ) * 0.3 ),
						) );
					}
				}
			}
		}
	}

	public static function get_graph_data() {
		global $wpdb;
		$knowledge_table = $wpdb->prefix . 'ceac_knowledge';
		$links_table     = $wpdb->prefix . 'ceac_knowledge_links';
		$fallback_table  = $wpdb->prefix . 'ceac_fallback_queries';

		$nodes = array();
		$edges = array();

		$knowledge = $wpdb->get_results( "SELECT id, title, category, source_type FROM $knowledge_table" );
		$category_colors = array(
			'company'    => '#1a365d',
			'services'   => '#2b6cb0',
			'compliance' => '#c53030',
			'contact'    => '#38a169',
			'rates'      => '#d69e2e',
			'general'    => '#718096',
			'page'       => '#805ad5',
			'post'       => '#dd6b20',
		);

		foreach ( $knowledge as $item ) {
			$nodes[] = array(
				'id'       => 'k_' . $item->id,
				'label'    => $item->title,
				'group'    => $item->category,
				'type'     => $item->source_type,
				'color'    => isset( $category_colors[ $item->category ] ) ? $category_colors[ $item->category ] : '#718096',
				'size'     => $item->source_type === 'seed' ? 25 : 18,
			);
		}

		$links = $wpdb->get_results( "SELECT * FROM $links_table" );
		foreach ( $links as $link ) {
			$edges[] = array(
				'from'   => 'k_' . $link->source_knowledge_id,
				'to'     => 'k_' . $link->target_knowledge_id,
				'type'   => $link->link_type,
				'weight' => (float) $link->strength,
			);
		}

		$fallbacks = $wpdb->get_results(
			"SELECT id, query FROM $fallback_table WHERE resolved = 0 ORDER BY created_at DESC LIMIT 50"
		);
		foreach ( $fallbacks as $fb ) {
			$node_id = 'f_' . $fb->id;
			$nodes[] = array(
				'id'    => $node_id,
				'label' => wp_trim_words( $fb->query, 6 ),
				'group' => 'fallback',
				'type'  => 'fallback_query',
				'color' => '#e53e3e',
				'size'  => 12,
			);

			$best_match = self::search( $fb->query, 1 );
			if ( ! empty( $best_match ) ) {
				$edges[] = array(
					'from'   => $node_id,
					'to'     => 'k_' . $best_match[0]->id,
					'type'   => 'unanswered_to_knowledge',
					'weight' => 0.3,
					'dashes' => true,
				);
			}
		}

		$intents = $wpdb->get_results(
			"SELECT intent, COUNT(*) as cnt FROM {$wpdb->prefix}ceac_messages
			 WHERE intent != '' AND role = 'user' GROUP BY intent ORDER BY cnt DESC LIMIT 20"
		);
		foreach ( $intents as $intent ) {
			$node_id = 'i_' . $intent->intent;
			$nodes[] = array(
				'id'    => $node_id,
				'label' => ucfirst( $intent->intent ) . ' (' . $intent->cnt . ')',
				'group' => 'intent',
				'type'  => 'user_intent',
				'color' => '#319795',
				'size'  => 15 + min( $intent->cnt, 20 ),
			);
		}

		return array(
			'nodes' => $nodes,
			'edges' => $edges,
			'stats' => array(
				'knowledge_count' => count( $knowledge ),
				'link_count'      => count( $links ),
				'fallback_count'  => count( $fallbacks ),
				'intent_count'    => count( $intents ),
			),
		);
	}
}
