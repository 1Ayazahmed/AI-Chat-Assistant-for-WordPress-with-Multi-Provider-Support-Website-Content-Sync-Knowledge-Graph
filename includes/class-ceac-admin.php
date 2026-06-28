<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Admin {

	public static function register_menus() {
		add_menu_page(
			__( 'AI Assistant', 'ceac' ),
			__( 'AI Assistant', 'ceac' ),
			'manage_options',
			'ceac-dashboard',
			array( __CLASS__, 'render_dashboard' ),
			'dashicons-format-chat',
			30
		);

		add_submenu_page( 'ceac-dashboard', __( 'Dashboard', 'ceac' ), __( 'Dashboard', 'ceac' ), 'manage_options', 'ceac-dashboard', array( __CLASS__, 'render_dashboard' ) );
		add_submenu_page( 'ceac-dashboard', __( 'AI Provider', 'ceac' ), __( 'AI Provider', 'ceac' ), 'manage_options', 'ceac-provider', array( __CLASS__, 'render_provider' ) );
		add_submenu_page( 'ceac-dashboard', __( 'Widget Settings', 'ceac' ), __( 'Widget', 'ceac' ), 'manage_options', 'ceac-widget', array( __CLASS__, 'render_widget_settings' ) );
		add_submenu_page( 'ceac-dashboard', __( 'Knowledge Graph', 'ceac' ), __( 'Knowledge Graph', 'ceac' ), 'manage_options', 'ceac-knowledge', array( __CLASS__, 'render_knowledge_graph' ) );
		add_submenu_page( 'ceac-dashboard', __( 'Analytics', 'ceac' ), __( 'Analytics', 'ceac' ), 'manage_options', 'ceac-analytics', array( __CLASS__, 'render_analytics' ) );
		add_submenu_page( 'ceac-dashboard', __( 'Conversations', 'ceac' ), __( 'Conversations', 'ceac' ), 'manage_options', 'ceac-conversations', array( __CLASS__, 'render_conversations' ) );
		add_submenu_page( 'ceac-dashboard', __( 'Settings', 'ceac' ), __( 'Settings', 'ceac' ), 'manage_options', 'ceac-settings', array( __CLASS__, 'render_settings' ) );
	}

	public static function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'ceac' ) === false ) {
			return;
		}

		wp_enqueue_style( 'ceac-admin', CEAC_PLUGIN_URL . 'admin/css/admin.css', array(), CEAC_VERSION );
		wp_enqueue_script( 'ceac-admin', CEAC_PLUGIN_URL . 'admin/js/admin.js', array( 'jquery' ), CEAC_VERSION, true );

		if ( strpos( $hook, 'ceac-knowledge' ) !== false ) {
			wp_enqueue_script( 'vis-network', 'https://unpkg.com/vis-network@9.1.6/standalone/umd/vis-network.min.js', array(), '9.1.6', true );
			wp_enqueue_style( 'vis-network', 'https://unpkg.com/vis-network@9.1.6/styles/vis-network.min.css', array(), '9.1.6' );
			wp_enqueue_script( 'ceac-graph', CEAC_PLUGIN_URL . 'admin/js/knowledge-graph.js', array( 'vis-network', 'jquery' ), CEAC_VERSION, true );
		}

		wp_localize_script( 'ceac-admin', 'ceacAdmin', array(
			'apiUrl'  => rest_url( 'ceac/v1' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'providers' => CEAC_Settings::get_providers(),
			'settings' => self::get_safe_settings(),
			'i18n'    => array(
				'saving'           => __( 'Saving...', 'ceac' ),
				'saved'            => __( 'Settings saved!', 'ceac' ),
				'error'            => __( 'An error occurred.', 'ceac' ),
				'fetching_models'  => __( 'Fetching models...', 'ceac' ),
				'models_fetched'   => __( 'Models loaded successfully.', 'ceac' ),
				'testing'          => __( 'Testing connection...', 'ceac' ),
				'syncing'          => __( 'Syncing content...', 'ceac' ),
				'sync_complete'    => __( 'Content sync complete!', 'ceac' ),
			),
		) );
	}

	private static function get_safe_settings() {
		$settings = CEAC_Settings::get();
		if ( ! empty( $settings['api_key'] ) ) {
			$key = CEAC_Settings::get_api_key();
			$settings['api_key'] = ! empty( $key ) ? '••••••••' . substr( $key, -4 ) : '';
		}
		if ( ! empty( $settings['fallback_api_key'] ) ) {
			$settings['fallback_api_key'] = '••••••••';
		}
		return $settings;
	}

	public static function render_dashboard() {
		self::render_admin_header();
		include CEAC_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	public static function render_provider() {
		self::render_admin_header();
		include CEAC_PLUGIN_DIR . 'admin/views/provider.php';
	}

	public static function render_widget_settings() {
		self::render_admin_header();
		include CEAC_PLUGIN_DIR . 'admin/views/widget-settings.php';
	}

	public static function render_knowledge_graph() {
		self::render_admin_header();
		include CEAC_PLUGIN_DIR . 'admin/views/knowledge-graph.php';
	}

	public static function render_analytics() {
		self::render_admin_header();
		include CEAC_PLUGIN_DIR . 'admin/views/analytics.php';
	}

	public static function render_conversations() {
		self::render_admin_header();
		include CEAC_PLUGIN_DIR . 'admin/views/conversations.php';
	}

	public static function render_settings() {
		self::render_admin_header();
		include CEAC_PLUGIN_DIR . 'admin/views/settings.php';
	}

	private static function render_admin_header() {
		$current = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'ceac-dashboard';
		$nav_items = array(
			'ceac-dashboard'     => array( 'label' => __( 'Dashboard', 'ceac' ), 'icon' => 'dashicons-dashboard' ),
			'ceac-provider'      => array( 'label' => __( 'AI Provider', 'ceac' ), 'icon' => 'dashicons-cloud' ),
			'ceac-widget'        => array( 'label' => __( 'Widget', 'ceac' ), 'icon' => 'dashicons-screenoptions' ),
			'ceac-knowledge'     => array( 'label' => __( 'Knowledge Graph', 'ceac' ), 'icon' => 'dashicons-networking' ),
			'ceac-analytics'     => array( 'label' => __( 'Analytics', 'ceac' ), 'icon' => 'dashicons-chart-area' ),
			'ceac-conversations' => array( 'label' => __( 'Conversations', 'ceac' ), 'icon' => 'dashicons-format-chat' ),
			'ceac-settings'      => array( 'label' => __( 'Settings', 'ceac' ), 'icon' => 'dashicons-admin-generic' ),
		);
		$version = CEAC_VERSION;
		$last_sync = get_option( 'ceac_last_sync' ) ?: __( 'Not yet', 'ceac' );
		include CEAC_PLUGIN_DIR . 'admin/views/header.php';
	}

	public static function ajax_get_conversation() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$id = absint( $_POST['id'] ?? 0 );
		if ( ! $id ) {
			wp_send_json_error( 'Invalid ID' );
		}

		global $wpdb;
		$messages = $wpdb->get_results( $wpdb->prepare(
			"SELECT role, content, created_at FROM {$wpdb->prefix}ceac_messages WHERE conversation_id = %d ORDER BY id ASC",
			$id
		), ARRAY_A );

		wp_send_json_success( $messages );
	}
}
