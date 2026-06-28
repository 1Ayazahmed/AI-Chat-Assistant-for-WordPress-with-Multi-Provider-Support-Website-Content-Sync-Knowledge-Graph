<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Analytics {

	public static function track( $event_type, $event_data = array(), $conversation_id = 0 ) {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'ceac_analytics', array(
			'event_type'      => $event_type,
			'event_data'      => wp_json_encode( $event_data ),
			'conversation_id' => $conversation_id,
		) );
	}

	public static function get_dashboard_stats( $days = 30 ) {
		global $wpdb;
		$since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$total_chats = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}ceac_conversations WHERE created_at >= %s",
			$since
		) );

		$total_messages = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}ceac_messages WHERE created_at >= %s",
			$since
		) );

		$fallback_count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}ceac_messages WHERE is_fallback = 1 AND created_at >= %s",
			$since
		) );

		$escalated = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}ceac_conversations WHERE escalated = 1 AND created_at >= %s",
			$since
		) );

		$avg_length = (float) $wpdb->get_var( $wpdb->prepare(
			"SELECT AVG(msg_count) FROM (
				SELECT COUNT(*) as msg_count FROM {$wpdb->prefix}ceac_messages m
				JOIN {$wpdb->prefix}ceac_conversations c ON m.conversation_id = c.id
				WHERE c.created_at >= %s GROUP BY c.id
			) sub",
			$since
		) );

		$top_intents = $wpdb->get_results( $wpdb->prepare(
			"SELECT intent, COUNT(*) as count FROM {$wpdb->prefix}ceac_messages
			 WHERE intent != '' AND role = 'user' AND created_at >= %s
			 GROUP BY intent ORDER BY count DESC LIMIT 10",
			$since
		) );

		$top_queries = $wpdb->get_results( $wpdb->prepare(
			"SELECT content, COUNT(*) as count FROM {$wpdb->prefix}ceac_messages
			 WHERE role = 'user' AND created_at >= %s
			 GROUP BY content ORDER BY count DESC LIMIT 10",
			$since
		) );

		$peak_hours = $wpdb->get_results( $wpdb->prepare(
			"SELECT HOUR(created_at) as hour, COUNT(*) as count FROM {$wpdb->prefix}ceac_conversations
			 WHERE created_at >= %s GROUP BY HOUR(created_at) ORDER BY hour",
			$since
		) );

		$daily_tokens = 0;
		for ( $i = 0; $i < $days; $i++ ) {
			$daily_tokens += (int) get_option( 'ceac_daily_tokens_' . gmdate( 'Ymd', strtotime( "-{$i} days" ) ), 0 );
		}

		$resolution_rate = $total_messages > 0
			? round( ( ( $total_messages - $fallback_count ) / $total_messages ) * 100, 1 )
			: 0;

		$fallback_rate = $total_messages > 0
			? round( ( $fallback_count / $total_messages ) * 100, 1 )
			: 0;

		return array(
			'total_chats'      => $total_chats,
			'total_messages'   => $total_messages,
			'fallback_count'   => $fallback_count,
			'fallback_rate'    => $fallback_rate,
			'resolution_rate'  => $resolution_rate,
			'escalated'        => $escalated,
			'avg_length'       => round( $avg_length, 1 ),
			'top_intents'      => $top_intents,
			'top_queries'      => $top_queries,
			'peak_hours'       => $peak_hours,
			'total_tokens'     => $daily_tokens,
			'estimated_cost'   => round( $daily_tokens * 0.00001, 2 ),
		);
	}

	public static function export_csv( $days = 30 ) {
		global $wpdb;
		$since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT c.id, c.session_id, c.language, c.status, c.escalated, c.token_count, c.created_at,
			 GROUP_CONCAT(CONCAT(m.role, ': ', LEFT(m.content, 200)) SEPARATOR ' | ') as messages
			 FROM {$wpdb->prefix}ceac_conversations c
			 LEFT JOIN {$wpdb->prefix}ceac_messages m ON c.id = m.conversation_id
			 WHERE c.created_at >= %s
			 GROUP BY c.id ORDER BY c.created_at DESC",
			$since
		), ARRAY_A );

		return $rows;
	}
}
