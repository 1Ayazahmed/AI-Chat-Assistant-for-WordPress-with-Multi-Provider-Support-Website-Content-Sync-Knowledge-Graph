<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Fallback {

	private static $out_of_scope_patterns = array(
		'personal banking', 'credit card', 'mortgage', 'loan application',
		'stock market', 'crypto trading', 'bitcoin investment',
		'tax evasion', 'hide money', 'anonymous transfer', 'bypass kyc',
		'تجنب', 'إخفاء', 'تهرب', 'قرض شخصي', 'بطاقة ائتمان',
	);

	public static function is_out_of_scope( $message ) {
		$lower = strtolower( $message );
		foreach ( self::$out_of_scope_patterns as $pattern ) {
			if ( strpos( $lower, strtolower( $pattern ) ) !== false ) {
				return true;
			}
		}
		return false;
	}

	public static function get_fallback_response( $language = 'en' ) {
		$message = CEAC_Settings::get( 'fallback_message' );
		if ( $language === 'ar' ) {
			return 'أنا غير متأكد من إجابتي على هذا السؤال. يرجى إعادة الصياغة أو التواصل مع فريقنا مباشرة للمساعدة.';
		}
		return $message;
	}

	public static function aml_response( $language = 'en' ) {
		if ( $language === 'ar' ) {
			return 'جميع المعاملات تخضع لسياسات مكافحة غسل الأموال وتمويل الإرهاب الصارمة وفقاً للوائح المحلية. للحصول على معلومات الامتثال الرسمية، يرجى مراجعة وثائق الامتثال على موقعنا أو التواصل مع فريق الامتثال.';
		}
		return 'All transactions are subject to strict AML/CTF policies in accordance with applicable regulations. For official compliance information, please review our compliance documentation on the website or contact our compliance team directly.';
	}

	public static function get_options() {
		return array(
			array( 'id' => 'rephrase', 'label' => __( 'Rephrase my question', 'ceac' ), 'action' => 'rephrase' ),
			array( 'id' => 'topics', 'label' => __( 'Browse related topics', 'ceac' ), 'action' => 'show_topics' ),
			array( 'id' => 'support', 'label' => __( 'Contact support', 'ceac' ), 'action' => 'escalate' ),
			array( 'id' => 'message', 'label' => __( 'Leave a message', 'ceac' ), 'action' => 'leave_message' ),
		);
	}

	public static function log_query( $query, $conversation_id = 0 ) {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'ceac_fallback_queries', array(
			'query'           => $query,
			'conversation_id' => $conversation_id,
		) );
	}

	public static function escalate( $conversation_id, $method, $data = array() ) {
		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . 'ceac_conversations',
			array( 'escalated' => 1, 'status' => 'escalated' ),
			array( 'id' => $conversation_id )
		);

		$email = CEAC_Settings::get( 'escalation_email' );
		if ( $email && in_array( $method, array( 'email', 'leave_message' ), true ) ) {
			$messages = $wpdb->get_results( $wpdb->prepare(
				"SELECT role, content FROM {$wpdb->prefix}ceac_messages WHERE conversation_id = %d ORDER BY id ASC",
				$conversation_id
			) );

			$body = "Escalation Request - Conversation #$conversation_id\n\n";
			if ( ! empty( $data['user_email'] ) ) {
				$body .= "User Email: " . sanitize_email( $data['user_email'] ) . "\n";
			}
			if ( ! empty( $data['user_phone'] ) ) {
				$body .= "User Phone: " . sanitize_text_field( $data['user_phone'] ) . "\n";
			}
			$body .= "\nConversation:\n";
			foreach ( $messages as $msg ) {
				$body .= strtoupper( $msg->role ) . ": " . $msg->content . "\n\n";
			}

			wp_mail( $email, '[AI Assistant] Chat Escalation #' . $conversation_id, $body );
		}

		$webhook = CEAC_Settings::get( 'escalation_webhook' );
		if ( $webhook ) {
			wp_remote_post( $webhook, array(
				'timeout' => 10,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array(
					'conversation_id' => $conversation_id,
					'method'          => $method,
					'data'            => $data,
				) ),
			) );
		}

		$crm_webhook = CEAC_Settings::get( 'crm_webhook_url' );
		if ( $crm_webhook ) {
			wp_remote_post( $crm_webhook, array(
				'timeout' => 10,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array(
					'source'          => 'ceac_chatbot',
					'conversation_id' => $conversation_id,
					'method'          => $method,
					'contact'         => $data,
				) ),
			) );
		}

		CEAC_Analytics::track( 'escalation', array( 'method' => $method ), $conversation_id );
		CEAC_Security::audit_log( $conversation_id, 'escalation', array( 'method' => $method ) );

		return array( 'success' => true, 'message' => __( 'Your request has been forwarded to our team.', 'ceac' ) );
	}
}
