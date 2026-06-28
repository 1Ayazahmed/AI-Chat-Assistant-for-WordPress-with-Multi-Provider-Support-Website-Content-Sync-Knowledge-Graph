<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_I18n {

	public static function detect_language() {
		if ( ! CEAC_Settings::get( 'auto_language_detect', true ) ) {
			return CEAC_Settings::get( 'default_language', 'en' );
		}

		$locale = get_locale();
		if ( strpos( $locale, 'ar' ) === 0 ) {
			return 'ar';
		}

		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$accept = sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) );
			if ( strpos( $accept, 'ar' ) !== false ) {
				return 'ar';
			}
		}

		return CEAC_Settings::get( 'default_language', 'en' );
	}

	public static function is_rtl( $language = null ) {
		if ( ! CEAC_Settings::get( 'rtl_support', true ) ) {
			return false;
		}
		$language = $language ?: self::detect_language();
		return $language === 'ar';
	}

	public static function format_currency( $amount, $currency = 'AED' ) {
		$language = self::detect_language();
		if ( $language === 'ar' ) {
			return number_format( $amount, 2 ) . ' ' . $currency;
		}
		return $currency . ' ' . number_format( $amount, 2 );
	}

	public static function get_strings( $language = 'en' ) {
		$brand_name = CEAC_Settings::get( 'brand_name', '' );
		$footer_text = CEAC_Settings::get( 'footer_text', '' );
		$powered_by = ! empty( $footer_text ) ? $footer_text : ( ! empty( $brand_name ) ? $brand_name : '' );

		$strings = array(
			'en' => array(
				'placeholder'    => 'Type your message...',
				'send'           => 'Send',
				'online'         => 'Online',
				'offline'        => 'Offline',
				'typing'         => 'Typing...',
				'powered_by'     => $powered_by,
				'consent_title'  => 'Chat Privacy Notice',
				'consent_text'   => 'This chat may collect conversation data for service improvement. No sensitive personal information should be shared.',
				'consent_accept' => 'I Agree, Start Chat',
				'consent_decline'=> 'Decline',
				'email_label'    => 'Your Email',
				'offline_submit' => 'Send Message',
			),
			'ar' => array(
				'placeholder'    => 'اكتب رسالتك...',
				'send'           => 'إرسال',
				'online'         => 'متصل',
				'offline'        => 'غير متصل',
				'typing'         => 'يكتب...',
				'powered_by'     => $powered_by,
				'consent_title'  => 'إشعار خصوصية المحادثة',
				'consent_text'   => 'قد تجمع هذه المحادثة بيانات لتحسين الخدمة. يرجى عدم مشاركة معلومات شخصية حساسة.',
				'consent_accept' => 'أوافق، ابدأ المحادثة',
				'consent_decline'=> 'رفض',
				'email_label'    => 'بريدك الإلكتروني',
				'offline_submit' => 'إرسال الرسالة',
			),
		);

		return isset( $strings[ $language ] ) ? $strings[ $language ] : $strings['en'];
	}
}
