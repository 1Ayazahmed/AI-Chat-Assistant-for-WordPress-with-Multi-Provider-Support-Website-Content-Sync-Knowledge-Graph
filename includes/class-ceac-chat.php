<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Chat {

	public static function process_message( $session_id, $message, $context = array() ) {
		$settings = CEAC_Settings::get();

		if ( ! CEAC_Security::check_rate_limit( $session_id ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Too many requests. Please wait a moment.', 'ceac' ),
			);
		}

		$conversation = self::get_or_create_conversation( $session_id, $context );
		$language     = isset( $context['language'] ) ? $context['language'] : CEAC_I18n::detect_language();

		self::save_message( $conversation->id, 'user', $message );

		if ( CEAC_Security::contains_pii_request( $message ) ) {
			$response = __( 'For your security, I cannot process sensitive personal information through chat. Please contact our team directly for account-specific matters.', 'ceac' );
			self::save_message( $conversation->id, 'assistant', $response, 1.0, false, 'pii_blocked' );
			return self::format_response( $response, $conversation, false );
		}

		$knowledge  = CEAC_Knowledge::search( $message, 5 );
		$confidence = CEAC_Knowledge::calculate_confidence( $message, $knowledge );

		if ( CEAC_Security::is_aml_sensitive( $message ) && $settings['aml_guardrails'] ) {
			$response = CEAC_Fallback::aml_response( $language );
			self::save_message( $conversation->id, 'assistant', $response, 0.9, false, 'aml_redirect' );
			CEAC_Analytics::track( 'aml_redirect', array( 'query' => $message ), $conversation->id );
			return self::format_response( $response, $conversation, false, array( 'type' => 'aml_redirect' ) );
		}

		$is_low_confidence = $confidence < $settings['low_confidence_threshold'];
		$is_out_of_scope   = CEAC_Fallback::is_out_of_scope( $message );
		$used_fallback     = false;

		if ( $is_low_confidence && $is_out_of_scope ) {
			$response = CEAC_Fallback::get_fallback_response( $language );
			self::save_message( $conversation->id, 'assistant', $response, $confidence, true, 'out_of_scope' );
			CEAC_Fallback::log_query( $message, $conversation->id );
			CEAC_Analytics::track( 'fallback', array( 'query' => $message, 'confidence' => $confidence ), $conversation->id );
			return self::format_response( $response, $conversation, true, CEAC_Fallback::get_options() );
		}

		$enhance_knowledge = $is_low_confidence && ! empty( $knowledge );
		$messages = self::build_messages( $conversation, $message, $knowledge, $language, $context, $enhance_knowledge );
		$ai_result = CEAC_AI_Provider::chat( $messages );

		if ( ! $ai_result['success'] ) {
			if ( ! empty( $knowledge ) ) {
				$snippet = CEAC_Knowledge::find_relevant_snippet( $knowledge[0], $message );
				$response = sprintf(
					__( "I'm currently unable to provide a complete answer through our AI service, but here's relevant information from our website:\n\n%s\n\nFor more details, please contact our team directly or try rephrasing your question.", 'ceac' ),
					$snippet
				);
				$used_fallback = true;
			} else {
				$response = CEAC_Fallback::get_fallback_response( $language );
				$used_fallback = true;
			}
			self::save_message( $conversation->id, 'assistant', $response, $confidence, true, 'api_error' );
			$options = ! empty( $knowledge ) ? array() : CEAC_Fallback::get_options();
			return self::format_response( $response, $conversation, true, $options );
		}

		$response = $ai_result['content'];
		$tokens   = isset( $ai_result['tokens_used'] ) ? $ai_result['tokens_used'] : 0;

		self::update_conversation_tokens( $conversation->id, $tokens );
		self::save_message( $conversation->id, 'assistant', $response, $confidence, $used_fallback, self::detect_intent( $message ) );

		CEAC_Analytics::track( 'message', array(
			'tokens'     => $tokens,
			'confidence' => $confidence,
			'fallback'   => $used_fallback,
		), $conversation->id );

		CEAC_Security::audit_log( $conversation->id, 'message_processed', array(
			'tokens' => $tokens,
			'model'  => isset( $ai_result['model'] ) ? $ai_result['model'] : '',
		) );

		$options = $used_fallback ? CEAC_Fallback::get_options() : array();
		return self::format_response( $response, $conversation, $used_fallback, $options, $tokens );
	}

	private static function build_messages( $conversation, $user_message, $knowledge, $language, $context, $enhance_knowledge = false ) {
		$system = CEAC_Settings::get( 'system_prompt' );
		$system = CEAC_Settings::process_system_prompt( $system );

		$system .= "\n\nRespond in " . ( $language === 'ar' ? 'Arabic' : 'English' ) . '.';

		if ( ! empty( $knowledge ) ) {
			$system .= "\n\nRELEVANT WEBSITE KNOWLEDGE:\n";
			foreach ( $knowledge as $item ) {
				$system .= "- [{$item->title}]: " . wp_trim_words( $item->content, 80 ) . "\n";
			}

			if ( $enhance_knowledge ) {
				$system .= "\n\nIMPORTANT INSTRUCTION: The user is asking something that may be answered from the website knowledge above. PRIORITIZE this knowledge as your primary source. If the knowledge contains the answer, provide it directly and accurately. Do NOT say you don't have access to real-time data or the website if the answer is clearly in the knowledge above. Only if the knowledge truly does not contain the answer, respond helpfully within your scope as an AI assistant.";
			}
		}

		if ( ! empty( $context['page_url'] ) ) {
			$system .= "\n\nUser is currently on: " . esc_url( $context['page_url'] );
		}

		$rate_disclaimer = __( 'Note: Exchange rates shown are indicative. For live rates, please contact us directly.', 'ceac' );
		if ( self::mentions_rates( $user_message ) ) {
			$system .= "\n\nInclude this rate disclaimer: $rate_disclaimer";
		}

		$messages = array( array( 'role' => 'system', 'content' => $system ) );

		$history = self::get_conversation_history( $conversation->id, 10 );
		$history = array_reverse( $history );
		foreach ( $history as $msg ) {
			$messages[] = array(
				'role'    => $msg->role,
				'content' => $msg->content,
			);
		}

		$messages[] = array( 'role' => 'user', 'content' => $user_message );

		return $messages;
	}

	private static function mentions_rates( $message ) {
		$keywords = array( 'rate', 'exchange', 'forex', 'currency', 'aed', 'usd', 'eur', 'gbp', 'sar' );
		$lower    = strtolower( $message );
		foreach ( $keywords as $kw ) {
			if ( strpos( $lower, $kw ) !== false ) {
				return true;
			}
		}
		return false;
	}

	private static function detect_intent( $message ) {
		$intents = array(
			'transfer'   => array( 'transfer', 'send money', 'remittance', 'تحويل' ),
			'exchange'   => array( 'exchange', 'rate', 'currency', 'forex', 'صرف' ),
			'compliance' => array( 'aml', 'ctf', 'compliance', 'kyc', 'امتثال' ),
			'contact'    => array( 'contact', 'phone', 'email', 'address', 'اتصل' ),
			'hours'      => array( 'hours', 'open', 'closed', 'timing', 'ساعات' ),
		);

		$lower = strtolower( $message );
		foreach ( $intents as $intent => $keywords ) {
			foreach ( $keywords as $kw ) {
				if ( strpos( $lower, $kw ) !== false ) {
					return $intent;
				}
			}
		}
		return 'general';
	}

	public static function get_or_create_conversation( $session_id, $context = array() ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ceac_conversations';

		$conversation = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $table WHERE session_id = %s AND status = 'active' ORDER BY id DESC LIMIT 1",
			$session_id
		) );

		if ( $conversation ) {
			return $conversation;
		}

		$wpdb->insert( $table, array(
			'session_id'   => sanitize_text_field( $session_id ),
			'user_id'      => get_current_user_id(),
			'user_name'    => isset( $context['user_name'] ) ? sanitize_text_field( $context['user_name'] ) : '',
			'page_url'     => isset( $context['page_url'] ) ? esc_url_raw( $context['page_url'] ) : '',
			'page_context' => isset( $context['page_context'] ) ? sanitize_text_field( $context['page_context'] ) : '',
			'language'     => isset( $context['language'] ) ? sanitize_text_field( $context['language'] ) : 'en',
		) );

		$id = $wpdb->insert_id;
		CEAC_Analytics::track( 'conversation_start', $context, $id );

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
	}

	public static function save_message( $conversation_id, $role, $content, $confidence = 1.0, $is_fallback = false, $intent = '' ) {
		global $wpdb;
		return $wpdb->insert( $wpdb->prefix . 'ceac_messages', array(
			'conversation_id' => $conversation_id,
			'role'            => $role,
			'content'         => $content,
			'confidence'      => $confidence,
			'is_fallback'     => $is_fallback ? 1 : 0,
			'intent'          => $intent,
		) );
	}

	private static function get_conversation_history( $conversation_id, $limit = 10 ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT role, content FROM {$wpdb->prefix}ceac_messages
			 WHERE conversation_id = %d ORDER BY id DESC LIMIT %d",
			$conversation_id,
			$limit
		) );
	}

	private static function update_conversation_tokens( $conversation_id, $tokens ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->prefix}ceac_conversations SET token_count = token_count + %d WHERE id = %d",
			$tokens,
			$conversation_id
		) );
	}

	private static function format_response( $content, $conversation, $is_fallback, $options = array(), $tokens = 0 ) {
		return array(
			'success'         => true,
			'message'         => $content,
			'conversation_id' => $conversation->id,
			'is_fallback'     => $is_fallback,
			'options'         => $options,
			'tokens_used'     => $tokens,
			'typing_delay'    => min( strlen( $content ) * 15, 3000 ),
		);
	}

	public static function get_greeting( $page_url = '', $language = 'en' ) {
		$settings = CEAC_Settings::get();
		$greetings = isset( $settings['contextual_greetings'] ) ? $settings['contextual_greetings'] : array();

		foreach ( $greetings as $greeting ) {
			if ( ! empty( $greeting['url_pattern'] ) && strpos( $page_url, $greeting['url_pattern'] ) !== false ) {
				$key = 'message_' . $language;
				if ( ! empty( $greeting[ $key ] ) ) {
					return $greeting[ $key ];
				}
			}
		}

		$key = 'greeting_default_' . $language;
		return $settings[ $key ] ?? $settings['greeting_default_en'];
	}
}
