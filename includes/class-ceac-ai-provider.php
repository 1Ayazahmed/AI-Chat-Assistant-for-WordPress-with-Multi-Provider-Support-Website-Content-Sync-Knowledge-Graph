<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_AI_Provider {

	/**
	 * Fetch available models from OpenAI-compatible /models endpoint.
	 */
	public static function fetch_models( $base_url = null, $api_key = null, $use_fallback = false ) {
		if ( $use_fallback ) {
			$base_url = $base_url ?: CEAC_Settings::get( 'fallback_api_base_url' );
			$api_key  = $api_key ?: self::get_fallback_api_key();
		} else {
			$base_url = $base_url ?: CEAC_Settings::get( 'api_base_url' );
			$api_key  = $api_key ?: CEAC_Settings::get_api_key();
		}

		$base_url = rtrim( $base_url, '/' );
		$cache_key = 'ceac_models_' . md5( $base_url );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$url = $base_url . '/models';

		$response = wp_remote_get( $url, array(
			'timeout' => 15,
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			),
			'sslverify' => apply_filters( 'ceac_ssl_verify', true ),
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error'   => $response->get_error_message(),
				'models'  => self::get_fallback_model_list(),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code !== 200 ) {
			$error_msg = isset( $body['error']['message'] ) ? $body['error']['message'] : "HTTP $code";
			return array(
				'success' => false,
				'error'   => $error_msg,
				'models'  => self::get_fallback_model_list(),
			);
		}

		$models = array();
		if ( isset( $body['data'] ) && is_array( $body['data'] ) ) {
			foreach ( $body['data'] as $model ) {
				$id = isset( $model['id'] ) ? $model['id'] : '';
				if ( ! empty( $id ) ) {
					$models[] = array(
						'id'      => $id,
						'owned_by' => isset( $model['owned_by'] ) ? $model['owned_by'] : '',
					);
				}
			}
		}

		usort( $models, function( $a, $b ) {
			return strcmp( $a['id'], $b['id'] );
		} );

		$result = array(
			'success' => true,
			'models'  => $models,
		);

		set_transient( $cache_key, $result, HOUR_IN_SECONDS );
		return $result;
	}

	private static function get_fallback_model_list() {
		return array(
			array( 'id' => 'gpt-4o', 'owned_by' => 'openai' ),
			array( 'id' => 'gpt-4o-mini', 'owned_by' => 'openai' ),
			array( 'id' => 'gpt-4-turbo', 'owned_by' => 'openai' ),
			array( 'id' => 'gpt-3.5-turbo', 'owned_by' => 'openai' ),
			array( 'id' => 'claude-3-5-sonnet-20241022', 'owned_by' => 'anthropic' ),
			array( 'id' => 'claude-3-opus-20240229', 'owned_by' => 'anthropic' ),
			array( 'id' => 'llama-3.1-70b-versatile', 'owned_by' => 'groq' ),
			array( 'id' => 'mixtral-8x7b-32768', 'owned_by' => 'groq' ),
		);
	}

	/**
	 * Send chat completion request.
	 */
	public static function chat( $messages, $options = array() ) {
		$use_fallback = ! empty( $options['use_fallback'] );
		unset( $options['use_fallback'] );

		$result = self::send_request( $messages, $options, $use_fallback );

		if ( ! $result['success'] && ! $use_fallback && CEAC_Settings::get( 'fallback_api_base_url' ) ) {
			$result = self::send_request( $messages, $options, true );
			$result['used_fallback_provider'] = true;
		}

		return $result;
	}

	private static function send_request( $messages, $options, $use_fallback = false ) {
		if ( $use_fallback ) {
			$base_url = CEAC_Settings::get( 'fallback_api_base_url' );
			$api_key  = self::get_fallback_api_key();
			$model    = CEAC_Settings::get( 'fallback_model', CEAC_Settings::get( 'model' ) );
		} else {
			$base_url = CEAC_Settings::get( 'api_base_url' );
			$api_key  = CEAC_Settings::get_api_key();
			$model    = CEAC_Settings::get( 'model', 'gpt-4o' );
		}

		if ( empty( $api_key ) ) {
			return array(
				'success' => false,
				'error'   => __( 'API key not configured. Set it in plugin settings or CEAC_API_KEY environment variable.', 'ceac' ),
			);
		}

		$daily_usage = (int) get_option( 'ceac_daily_tokens_' . gmdate( 'Ymd' ), 0 );
		$daily_cap   = CEAC_Settings::get( 'daily_token_cap', 100000 );
		if ( $daily_usage >= $daily_cap ) {
			return array(
				'success' => false,
				'error'   => __( 'Daily token usage limit reached.', 'ceac' ),
			);
		}

		$base_url = rtrim( $base_url, '/' );
		$url      = $base_url . '/chat/completions';

		$body = array(
			'model'       => isset( $options['model'] ) ? $options['model'] : $model,
			'messages'    => $messages,
			'temperature' => isset( $options['temperature'] ) ? $options['temperature'] : CEAC_Settings::get( 'temperature', 0.7 ),
			'max_tokens'  => isset( $options['max_tokens'] ) ? $options['max_tokens'] : CEAC_Settings::get( 'max_tokens', 1024 ),
		);

		$response = wp_remote_post( $url, array(
			'timeout' => 60,
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $body ),
			'sslverify' => apply_filters( 'ceac_ssl_verify', true ),
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error'   => $response->get_error_message(),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code !== 200 ) {
			$error = isset( $data['error']['message'] ) ? $data['error']['message'] : "HTTP $code";
			return array(
				'success' => false,
				'error'   => $error,
			);
		}

		$content     = isset( $data['choices'][0]['message']['content'] ) ? $data['choices'][0]['message']['content'] : '';
		$tokens_used = isset( $data['usage']['total_tokens'] ) ? (int) $data['usage']['total_tokens'] : 0;

		update_option( 'ceac_daily_tokens_' . gmdate( 'Ymd' ), $daily_usage + $tokens_used );

		return array(
			'success'      => true,
			'content'      => $content,
			'tokens_used'  => $tokens_used,
			'model'        => $body['model'],
			'finish_reason' => isset( $data['choices'][0]['finish_reason'] ) ? $data['choices'][0]['finish_reason'] : 'stop',
		);
	}

	private static function get_fallback_api_key() {
		$encrypted = CEAC_Settings::get( 'fallback_api_key', '' );
		if ( empty( $encrypted ) ) {
			return CEAC_Settings::get_api_key();
		}
		return self::decrypt_key( $encrypted );
	}

	private static function decrypt_key( $encrypted ) {
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

	public static function test_connection( $base_url, $api_key ) {
		$result = self::fetch_models( $base_url, $api_key );
		return array(
			'success' => $result['success'],
			'message' => $result['success']
				? sprintf( __( 'Connected! Found %d models.', 'ceac' ), count( $result['models'] ) )
				: ( isset( $result['error'] ) ? $result['error'] : __( 'Connection failed.', 'ceac' ) ),
			'models'  => isset( $result['models'] ) ? $result['models'] : array(),
		);
	}
}
