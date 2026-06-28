<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Frontend {

	public static function enqueue_assets() {
		if ( ! CEAC_Settings::get( 'widget_enabled', true ) ) {
			return;
		}

		wp_enqueue_style(
			'ceac-widget',
			CEAC_PLUGIN_URL . 'public/css/widget.css',
			array(),
			CEAC_VERSION
		);

		wp_enqueue_script(
			'ceac-widget',
			CEAC_PLUGIN_URL . 'public/js/widget.js',
			array(),
			CEAC_VERSION,
			true
		);

		$language = CEAC_I18n::detect_language();
		$vars     = CEAC_Theme_Sync::get_css_variables();
		$css_vars = '';
		foreach ( $vars as $key => $value ) {
			$css_vars .= "$key: $value; ";
		}

		wp_add_inline_style( 'ceac-widget', ':root { ' . $css_vars . ' }' );

		wp_localize_script( 'ceac-widget', 'ceacConfig', array(
			'apiUrl'           => rest_url( 'ceac/v1' ),
			'nonce'            => wp_create_nonce( 'wp_rest' ),
			'sessionId'        => self::get_session_id(),
			'pageUrl'          => self::get_current_url(),
			'language'         => $language,
			'rtl'              => CEAC_I18n::is_rtl( $language ),
			'strings'          => CEAC_I18n::get_strings( $language ),
			'position'         => CEAC_Settings::get( 'widget_position', 'bottom-right' ),
			'offsetX'          => CEAC_Settings::get( 'widget_offset_x', 20 ),
			'offsetY'          => CEAC_Settings::get( 'widget_offset_y', 20 ),
			'botName'          => CEAC_Settings::get( 'bot_name' ),
			'avatarUrl'        => self::get_avatar_url(),
			'logoUrl'          => CEAC_Settings::get( 'logo_url' ) ?: ( CEAC_Theme_Sync::extract_theme_styles()['logo_url'] ?? '' ),
			'animation'        => CEAC_Settings::get( 'entrance_animation', 'slide-up' ),
			'animationDuration'=> CEAC_Settings::get( 'animation_duration', 300 ),
			'autoOpenDelay'    => CEAC_Settings::get( 'auto_open_delay', 0 ),
			'scrollDepth'      => CEAC_Settings::get( 'scroll_depth_trigger', 0 ),
			'exitIntent'       => CEAC_Settings::get( 'exit_intent', false ),
			'clickToOpenOnly'  => CEAC_Settings::get( 'click_to_open_only', false ),
			'cookieConsent'    => CEAC_Settings::get( 'cookie_consent_required', true ),
			'soundEnabled'     => CEAC_Settings::get( 'sound_enabled', false ),
			'hapticsEnabled'   => CEAC_Settings::get( 'haptics_enabled', true ),
			'statusIndicator'  => CEAC_Settings::get( 'status_indicator', true ),
			'businessHours'    => CEAC_Settings::get( 'business_hours_enabled', false ),
			'offlineMessage'   => CEAC_Settings::get( 'offline_message' ),
			'bubbleStyle'      => CEAC_Settings::get( 'bubble_style', 'rounded' ),
		) );
	}

	public static function render_widget() {
		if ( ! CEAC_Settings::get( 'widget_enabled', true ) ) {
			return;
		}
		include CEAC_PLUGIN_DIR . 'public/views/widget.php';
	}

	public static function shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'inline' => 'true',
		), $atts );

		ob_start();
		echo '<div id="ceac-chatbot-inline" class="ceac-inline-widget" data-inline="true"></div>';
		self::enqueue_assets();
		return ob_get_clean();
	}

	private static function get_session_id() {
		if ( isset( $_COOKIE['ceac_session'] ) ) {
			return sanitize_text_field( wp_unslash( $_COOKIE['ceac_session'] ) );
		}
		$session_id = wp_generate_uuid4();
		if ( ! headers_sent() ) {
			setcookie( 'ceac_session', $session_id, time() + DAY_IN_SECONDS * 30, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		}
		return $session_id;
	}

	private static function get_current_url() {
		if ( is_singular() ) {
			return get_permalink();
		}
		return home_url( add_query_arg( array() ) );
	}

	private static function get_avatar_url() {
		$custom = CEAC_Settings::get( 'avatar_url' );
		if ( ! empty( $custom ) ) {
			return $custom;
		}

		$library = CEAC_Settings::get( 'avatar_library', 'financial-1' );
		$avatars = array(
			'financial-1' => CEAC_PLUGIN_URL . 'assets/avatars/financial-1.svg',
			'financial-2' => CEAC_PLUGIN_URL . 'assets/avatars/financial-2.svg',
			'financial-3' => CEAC_PLUGIN_URL . 'assets/avatars/financial-3.svg',
		);

		return isset( $avatars[ $library ] ) ? $avatars[ $library ] : $avatars['financial-1'];
	}
}
