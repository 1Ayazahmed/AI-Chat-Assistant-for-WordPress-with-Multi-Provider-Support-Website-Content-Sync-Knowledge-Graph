<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Security {

	public static function check_rate_limit( $session_id ) {
		$key   = 'ceac_rate_' . md5( $session_id );
		$count = (int) get_transient( $key );

		if ( $count >= 30 ) {
			return false;
		}

		set_transient( $key, $count + 1, MINUTE_IN_SECONDS );
		return true;
	}

	public static function contains_pii_request( $message ) {
		$patterns = array(
			'/passport\s*(number|no|#)/i',
			'/credit\s*card/i',
			'/account\s*(number|no|#)/i',
			'/pin\s*(code|number)/i',
			'/social\s*security/i',
			'/رقم\s*الجواز/i',
			'/رقم\s*الحساب/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $message ) ) {
				return true;
			}
		}
		return false;
	}

	public static function is_aml_sensitive( $message ) {
		$patterns = array(
			'bypass aml', 'avoid kyc', 'anonymous transfer', 'hide money',
			'tax evasion', 'no documentation', 'without id', 'circumvent',
			'تجاوز', 'بدون هوية', 'إخفاء', 'تهرب ضريبي',
		);
		$lower = strtolower( $message );
		foreach ( $patterns as $p ) {
			if ( strpos( $lower, $p ) !== false ) {
				return true;
			}
		}
		return false;
	}

	public static function audit_log( $conversation_id, $action, $details = array() ) {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'ceac_audit_log', array(
			'conversation_id' => $conversation_id,
			'action'          => $action,
			'details'         => wp_json_encode( $details ),
			'ip_address'      => self::get_client_ip(),
			'user_agent'      => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		) );
	}

	public static function purge_old_logs() {
		global $wpdb;
		$days = CEAC_Settings::get( 'log_retention_days', 90 );
		$date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$tables = array( 'ceac_conversations', 'ceac_messages', 'ceac_audit_log', 'ceac_analytics', 'ceac_fallback_queries' );
		foreach ( $tables as $table ) {
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}{$table} WHERE created_at < %s",
				$date
			) );
		}
	}

	private static function get_client_ip() {
		$headers = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
				$ip = explode( ',', $ip )[0];
				if ( filter_var( trim( $ip ), FILTER_VALIDATE_IP ) ) {
					return trim( $ip );
				}
			}
		}
		return '';
	}

	public static function verify_nonce( $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! $nonce ) {
			$nonce = $request->get_param( '_wpnonce' );
		}
		return wp_verify_nonce( $nonce, 'wp_rest' ) || wp_verify_nonce( $nonce, 'ceac_chat' );
	}
}
