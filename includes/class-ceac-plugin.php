<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Plugin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init_hooks();
	}

	private function init_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'rest_api_init', array( 'CEAC_API', 'register_routes' ) );
		add_action( 'wp_enqueue_scripts', array( 'CEAC_Frontend', 'enqueue_assets' ) );
		add_action( 'wp_footer', array( 'CEAC_Frontend', 'render_widget' ) );
		add_action( 'admin_menu', array( 'CEAC_Admin', 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( 'CEAC_Admin', 'enqueue_assets' ) );
		add_action( 'save_post', array( 'CEAC_Content_Indexer', 'on_post_save' ), 10, 2 );
		add_action( 'ceac_sync_content', array( 'CEAC_Content_Indexer', 'full_sync' ) );
		add_action( 'ceac_purge_logs', array( 'CEAC_Security', 'purge_old_logs' ) );
		add_shortcode( 'ceac_chatbot', array( 'CEAC_Frontend', 'shortcode' ) );
		add_shortcode( 'ai_assistant', array( 'CEAC_Frontend', 'shortcode' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widget' ) );
		add_action( 'wp_ajax_ceac_get_conversation', array( 'CEAC_Admin', 'ajax_get_conversation' ) );

		if ( ! wp_next_scheduled( 'ceac_sync_content' ) ) {
			wp_schedule_event( time(), 'hourly', 'ceac_sync_content' );
		}
		if ( ! wp_next_scheduled( 'ceac_purge_logs' ) ) {
			wp_schedule_event( time(), 'daily', 'ceac_purge_logs' );
		}
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'ceac', false, dirname( CEAC_PLUGIN_BASENAME ) . '/languages' );
	}

	public function register_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		register_block_type( CEAC_PLUGIN_DIR . 'blocks/chatbot-block' );
	}

	public function register_elementor_widget( $widgets_manager ) {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}
		require_once CEAC_PLUGIN_DIR . 'includes/class-ceac-elementor-widget.php';
		$widgets_manager->register( new CEAC_Elementor_Widget() );
	}
}
