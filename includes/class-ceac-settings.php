<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Settings {

	const OPTION_KEY = 'ceac_settings';

	public static function get_defaults() {
		return array(
			// AI Provider
			'provider'              => 'openai',
			'api_base_url'          => 'https://api.openai.com/v1',
			'api_key'               => '',
			'model'                 => 'gpt-4o',
			'temperature'           => 0.7,
			'max_tokens'            => 1024,
			'system_prompt'         => self::default_system_prompt(),
			'fallback_provider'     => '',
			'fallback_api_base_url' => '',
			'fallback_api_key'      => '',
			'fallback_model'        => '',
			'daily_token_cap'       => 100000,
			'per_chat_token_budget' => 4000,

			// Widget
			'widget_enabled'        => true,
			'widget_position'       => 'bottom-right',
			'widget_offset_x'       => 20,
			'widget_offset_y'       => 20,
			'widget_width'          => 380,
			'widget_height'         => 520,
			'widget_transparency'   => 1.0,
			'bubble_style'          => 'rounded',
			'font_size'             => 14,
			'header_color'          => '',
			'auto_open_delay'       => 0,
			'scroll_depth_trigger'  => 0,
			'exit_intent'           => false,
			'click_to_open_only'    => false,
			'entrance_animation'      => 'slide-up',
			'animation_duration'    => 300,
			'sound_enabled'         => false,
			'haptics_enabled'       => true,

			// Avatar
			'avatar_type'           => 'default',
			'avatar_url'            => '',
			'avatar_library'        => 'financial-1',
			'bot_name'              => 'AI Assistant',
			'chat_header_title'     => 'AI Assistant',
			'brand_name'            => '',
			'footer_text'           => '',
			'status_indicator'      => true,

			// Greetings
			'greeting_default_en'   => 'Welcome! How can I assist you today?',
			'greeting_default_ar'   => 'مرحباً! كيف يمكنني مساعدتك اليوم؟',
			'contextual_greetings'  => array(),

			// Business hours
			'business_hours_enabled' => false,
			'business_hours'        => array(
				'mon' => array( '09:00', '18:00' ),
				'tue' => array( '09:00', '18:00' ),
				'wed' => array( '09:00', '18:00' ),
				'thu' => array( '09:00', '18:00' ),
				'fri' => array( '09:00', '18:00' ),
				'sat' => array( 'closed' ),
				'sun' => array( 'closed' ),
			),
			'timezone'              => 'Asia/Dubai',
			'offline_message'       => 'We are currently offline. Please leave your email and we will get back to you.',

			// Theme sync
			'theme_sync_enabled'    => true,
			'dark_mode_detection'   => true,

			// Language
			'auto_language_detect'  => true,
			'default_language'      => 'en',
			'rtl_support'           => true,

			// Privacy & compliance
			'cookie_consent_required' => true,
			'log_retention_days'    => 90,
			'store_pii'             => false,
			'aml_guardrails'        => true,

			// Fallback
			'fallback_message'      => 'I\'m not sure I can answer that question. Please try rephrasing or contact our team directly for further assistance.',
			'escalation_email'      => '',
			'escalation_webhook'    => '',
			'low_confidence_threshold' => 0.4,

			// Integrations
			'crm_webhook_url'       => '',
			'crm_provider'          => '',
			'rate_api_url'          => '',
			'rate_api_key'          => '',

			// Branding
			'logo_url'              => '',
			'primary_color'         => '#1a365d',
			'secondary_color'       => '#2b6cb0',
			'accent_color'          => '#d69e2e',
		);
	}

	public static function default_system_prompt() {
		return "You are the AI Assistant — a helpful, professional virtual assistant.

IDENTITY & TONE:
- Professional, concise, and friendly
- Authoritative yet approachable
- Respond in the user's language

SERVICES YOU COVER:
- Answering questions based on available website knowledge
- Providing helpful information about products and services
- Assisting with common inquiries

STRICT RULES:
- NEVER provide personal financial, medical, or legal advice
- NEVER store or request sensitive PII (passport numbers, full account numbers, PINs)
- For complex queries: direct users to official documentation or contact channels
- If unsure or query is out of scope, use the fallback response and offer escalation options";
	}

	public static function process_system_prompt( $prompt ) {
		$brand_name = self::get( 'brand_name', '' );
		if ( ! empty( $brand_name ) ) {
			$prompt = str_replace( '{brand_name}', $brand_name, $prompt );
		}
		return $prompt;
	}

	public static function get( $key = null, $default = null ) {
		$settings = get_option( self::OPTION_KEY, self::get_defaults() );
		$settings = wp_parse_args( $settings, self::get_defaults() );

		if ( null === $key ) {
			return $settings;
		}

		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	public static function update( $data ) {
		$current  = self::get();
		$updated  = wp_parse_args( $data, $current );
		$sanitized = self::sanitize( $updated );
		return update_option( self::OPTION_KEY, $sanitized );
	}

	public static function get_api_key() {
		$key = self::get( 'api_key', '' );
		return self::decrypt( $key );
	}

	public static function encrypt_api_key( $key ) {
		if ( empty( $key ) ) {
			return '';
		}
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return base64_encode( $key );
		}
		$salt = wp_salt( 'auth' );
		$iv   = substr( hash( 'sha256', $salt ), 0, 16 );
		$enc  = openssl_encrypt( $key, 'AES-256-CBC', substr( hash( 'sha256', $salt ), 0, 32 ), 0, $iv );
		return base64_encode( $iv . '::' . $enc );
	}

	private static function decrypt( $encrypted ) {
		if ( empty( $encrypted ) ) {
			return '';
		}
		if ( ! function_exists( 'openssl_decrypt' ) ) {
			return base64_decode( $encrypted );
		}
		$data = base64_decode( $encrypted );
		if ( false === strpos( $data, '::' ) ) {
			return base64_decode( $encrypted );
		}
		list( $iv, $enc ) = explode( '::', $data, 2 );
		$salt = wp_salt( 'auth' );
		return openssl_decrypt( $enc, 'AES-256-CBC', substr( hash( 'sha256', $salt ), 0, 32 ), 0, $iv );
	}

	public static function get_providers() {
		return array(
			'openai'    => array( 'name' => 'OpenAI', 'url' => 'https://api.openai.com/v1' ),
			'anthropic' => array( 'name' => 'Anthropic', 'url' => 'https://api.anthropic.com/v1' ),
			'groq'      => array( 'name' => 'Groq', 'url' => 'https://api.groq.com/openai/v1' ),
			'together'  => array( 'name' => 'Together AI', 'url' => 'https://api.together.xyz/v1' ),
			'ollama'    => array( 'name' => 'Ollama (Local)', 'url' => 'http://localhost:11434/v1' ),
			'lmstudio'  => array( 'name' => 'LM Studio', 'url' => 'http://localhost:1234/v1' ),
			'custom'    => array( 'name' => 'Custom Provider', 'url' => '' ),
		);
	}

	private static function sanitize( $data ) {
		$bool_keys = array(
			'widget_enabled', 'exit_intent', 'click_to_open_only', 'sound_enabled',
			'haptics_enabled', 'status_indicator', 'business_hours_enabled',
			'theme_sync_enabled', 'dark_mode_detection', 'auto_language_detect',
			'rtl_support', 'cookie_consent_required', 'store_pii', 'aml_guardrails',
		);

		foreach ( $bool_keys as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$data[ $key ] = (bool) $data[ $key ];
			}
		}

		$float_keys = array( 'temperature', 'widget_transparency', 'low_confidence_threshold' );
		foreach ( $float_keys as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$data[ $key ] = floatval( $data[ $key ] );
			}
		}

		$int_keys = array(
			'max_tokens', 'daily_token_cap', 'per_chat_token_budget',
			'widget_offset_x', 'widget_offset_y', 'widget_width', 'widget_height',
			'font_size', 'auto_open_delay', 'scroll_depth_trigger', 'animation_duration',
			'log_retention_days',
		);
		foreach ( $int_keys as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$data[ $key ] = absint( $data[ $key ] );
			}
		}

		if ( isset( $data['api_key'] ) && ! empty( $data['api_key'] ) && strpos( $data['api_key'], '••••' ) === false ) {
			$data['api_key'] = self::encrypt_api_key( sanitize_text_field( $data['api_key'] ) );
		} elseif ( isset( $data['api_key'] ) && strpos( $data['api_key'], '••••' ) !== false ) {
			unset( $data['api_key'] );
		}

		if ( isset( $data['fallback_api_key'] ) && ! empty( $data['fallback_api_key'] ) && strpos( $data['fallback_api_key'], '••••' ) === false ) {
			$data['fallback_api_key'] = self::encrypt_api_key( sanitize_text_field( $data['fallback_api_key'] ) );
		} elseif ( isset( $data['fallback_api_key'] ) && strpos( $data['fallback_api_key'], '••••' ) !== false ) {
			unset( $data['fallback_api_key'] );
		}

		$text_keys = array(
			'provider', 'api_base_url', 'model', 'fallback_provider',
			'fallback_api_base_url', 'fallback_model', 'widget_position', 'bubble_style',
			'entrance_animation', 'avatar_type', 'avatar_library', 'bot_name',
			'timezone', 'default_language', 'escalation_email', 'crm_provider',
			'rate_api_url', 'primary_color', 'secondary_color', 'accent_color',
			'brand_name', 'chat_header_title', 'footer_text',
		);
		foreach ( $text_keys as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$data[ $key ] = sanitize_text_field( $data[ $key ] );
			}
		}

		$url_keys = array( 'avatar_url', 'logo_url', 'escalation_webhook', 'crm_webhook_url' );
		foreach ( $url_keys as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$data[ $key ] = esc_url_raw( $data[ $key ] );
			}
		}

		$textarea_keys = array( 'system_prompt', 'fallback_message', 'offline_message', 'greeting_default_en', 'greeting_default_ar' );
		foreach ( $textarea_keys as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$data[ $key ] = sanitize_textarea_field( $data[ $key ] );
			}
		}

		return $data;
	}
}
