<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_API {

	public static function register_routes() {
		$namespace = 'ceac/v1';

		register_rest_route( $namespace, '/chat', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'handle_chat' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, '/greeting', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'get_greeting' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, '/escalate', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'handle_escalate' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, '/feedback', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'handle_feedback' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, '/models', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'fetch_models' ),
			'permission_callback' => array( __CLASS__, 'admin_permission' ),
		) );

		register_rest_route( $namespace, '/test-connection', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'test_connection' ),
			'permission_callback' => array( __CLASS__, 'admin_permission' ),
		) );

		register_rest_route( $namespace, '/sync-content', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'sync_content' ),
			'permission_callback' => array( __CLASS__, 'admin_permission' ),
		) );

		register_rest_route( $namespace, '/knowledge-graph', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'knowledge_graph' ),
			'permission_callback' => array( __CLASS__, 'admin_permission' ),
		) );

		register_rest_route( $namespace, '/analytics', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'get_analytics' ),
			'permission_callback' => array( __CLASS__, 'admin_permission' ),
		) );

		register_rest_route( $namespace, '/export', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'export_data' ),
			'permission_callback' => array( __CLASS__, 'admin_permission' ),
		) );

		register_rest_route( $namespace, '/settings', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_settings' ),
				'permission_callback' => array( __CLASS__, 'admin_permission' ),
			),
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_settings' ),
				'permission_callback' => array( __CLASS__, 'admin_permission' ),
			),
		) );
	}

	public static function admin_permission() {
		return current_user_can( 'manage_options' );
	}

	public static function handle_chat( $request ) {
		$session_id = sanitize_text_field( $request->get_param( 'session_id' ) );
		$message    = sanitize_textarea_field( $request->get_param( 'message' ) );

		if ( empty( $session_id ) || empty( $message ) ) {
			return new WP_Error( 'missing_params', __( 'Session ID and message are required.', 'ceac' ), array( 'status' => 400 ) );
		}

		$context = array(
			'page_url'     => esc_url_raw( $request->get_param( 'page_url' ) ),
			'page_context' => sanitize_text_field( $request->get_param( 'page_context' ) ),
			'language'     => sanitize_text_field( $request->get_param( 'language' ) ),
			'user_name'    => sanitize_text_field( $request->get_param( 'user_name' ) ),
		);

		$result = CEAC_Chat::process_message( $session_id, $message, $context );
		return rest_ensure_response( $result );
	}

	public static function get_greeting( $request ) {
		$page_url = esc_url_raw( $request->get_param( 'page_url' ) );
		$language = sanitize_text_field( $request->get_param( 'language' ) ) ?: CEAC_I18n::detect_language();

		return rest_ensure_response( array(
			'greeting' => CEAC_Chat::get_greeting( $page_url, $language ),
			'language' => $language,
			'rtl'      => CEAC_I18n::is_rtl( $language ),
			'online'   => self::is_business_hours(),
		) );
	}

	public static function handle_escalate( $request ) {
		$conversation_id = absint( $request->get_param( 'conversation_id' ) );
		$method          = sanitize_text_field( $request->get_param( 'method' ) );
		$data            = array(
			'user_email' => sanitize_email( $request->get_param( 'user_email' ) ),
			'user_phone' => sanitize_text_field( $request->get_param( 'user_phone' ) ),
			'message'    => sanitize_textarea_field( $request->get_param( 'message' ) ),
		);

		$result = CEAC_Fallback::escalate( $conversation_id, $method, $data );
		return rest_ensure_response( $result );
	}

	public static function handle_feedback( $request ) {
		$conversation_id = absint( $request->get_param( 'conversation_id' ) );
		$rating          = sanitize_text_field( $request->get_param( 'rating' ) );

		CEAC_Analytics::track( 'feedback', array( 'rating' => $rating ), $conversation_id );
		return rest_ensure_response( array( 'success' => true ) );
	}

	public static function fetch_models( $request ) {
		$base_url = sanitize_text_field( $request->get_param( 'base_url' ) );
		$api_key  = sanitize_text_field( $request->get_param( 'api_key' ) );

		if ( empty( $api_key ) || strpos( $api_key, '••••' ) !== false ) {
			$api_key = CEAC_Settings::get_api_key();
		}

		if ( empty( $base_url ) ) {
			$base_url = CEAC_Settings::get( 'api_base_url' );
		}

		delete_transient( 'ceac_models_' . md5( rtrim( $base_url, '/' ) ) );
		$result = CEAC_AI_Provider::fetch_models( $base_url, $api_key );
		return rest_ensure_response( $result );
	}

	public static function test_connection( $request ) {
		$base_url = sanitize_text_field( $request->get_param( 'base_url' ) );
		$api_key  = sanitize_text_field( $request->get_param( 'api_key' ) );

		if ( empty( $api_key ) || strpos( $api_key, '••••' ) !== false ) {
			$api_key = CEAC_Settings::get_api_key();
		}

		$result = CEAC_AI_Provider::test_connection( $base_url, $api_key );
		return rest_ensure_response( $result );
	}

	public static function sync_content( $request ) {
		CEAC_Content_Indexer::full_sync();
		return rest_ensure_response( array(
			'success'   => true,
			'message'   => __( 'Content sync completed.', 'ceac' ),
			'last_sync' => get_option( 'ceac_last_sync' ),
		) );
	}

	public static function knowledge_graph( $request ) {
		return rest_ensure_response( CEAC_Knowledge::get_graph_data() );
	}

	public static function get_analytics( $request ) {
		$days = absint( $request->get_param( 'days' ) ) ?: 30;
		return rest_ensure_response( CEAC_Analytics::get_dashboard_stats( $days ) );
	}

	public static function export_data( $request ) {
		$days  = absint( $request->get_param( 'days' ) ) ?: 30;
		$format = sanitize_text_field( $request->get_param( 'format' ) ) ?: 'csv';
		$rows  = CEAC_Analytics::export_csv( $days );

		if ( $format === 'json' ) {
			return rest_ensure_response( $rows );
		}

		return rest_ensure_response( array(
			'format' => 'csv',
			'headers' => array( 'ID', 'Session', 'Language', 'Status', 'Escalated', 'Tokens', 'Date', 'Messages' ),
			'rows'    => $rows,
		) );
	}

	public static function get_settings( $request ) {
		$settings = CEAC_Settings::get();
		if ( ! empty( $settings['api_key'] ) ) {
			$settings['api_key'] = '••••••••' . substr( CEAC_Settings::get_api_key(), -4 );
		}
		if ( ! empty( $settings['fallback_api_key'] ) ) {
			$settings['fallback_api_key'] = '••••••••';
		}
		return rest_ensure_response( $settings );
	}

	public static function save_settings( $request ) {
		$data = $request->get_json_params();
		if ( empty( $data ) ) {
			$data = $request->get_params();
		}
		unset( $data['_wpnonce'] );

		CEAC_Settings::update( $data );
		return rest_ensure_response( array( 'success' => true, 'message' => __( 'Settings saved.', 'ceac' ) ) );
	}

	private static function is_business_hours() {
		if ( ! CEAC_Settings::get( 'business_hours_enabled' ) ) {
			return true;
		}

		$timezone = CEAC_Settings::get( 'timezone', 'Asia/Dubai' );
		$tz       = new DateTimeZone( $timezone );
		$now      = new DateTime( 'now', $tz );
		$day      = strtolower( $now->format( 'D' ) );
		$day_map  = array( 'mon' => 'mon', 'tue' => 'tue', 'wed' => 'wed', 'thu' => 'thu', 'fri' => 'fri', 'sat' => 'sat', 'sun' => 'sun' );

		$day_key = isset( $day_map[ $day ] ) ? $day_map[ $day ] : $day;
		$hours   = CEAC_Settings::get( 'business_hours' );

		if ( ! isset( $hours[ $day_key ] ) || $hours[ $day_key ] === array( 'closed' ) || $hours[ $day_key ][0] === 'closed' ) {
			return false;
		}

		$open  = DateTime::createFromFormat( 'H:i', $hours[ $day_key ][0], $tz );
		$close = DateTime::createFromFormat( 'H:i', $hours[ $day_key ][1], $tz );
		$current_time = DateTime::createFromFormat( 'H:i', $now->format( 'H:i' ), $tz );

		return $current_time >= $open && $current_time <= $close;
	}
}
