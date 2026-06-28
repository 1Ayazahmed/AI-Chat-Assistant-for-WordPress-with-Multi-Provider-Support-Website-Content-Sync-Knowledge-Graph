<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Theme_Sync {

	public static function extract_theme_styles() {
		$styles = array(
			'primary_color'   => CEAC_Settings::get( 'primary_color', '#1a365d' ),
			'secondary_color' => CEAC_Settings::get( 'secondary_color', '#2b6cb0' ),
			'accent_color'    => CEAC_Settings::get( 'accent_color', '#d69e2e' ),
			'font_family'     => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
			'font_size'       => CEAC_Settings::get( 'font_size', 14 ) . 'px',
			'border_radius'   => '12px',
			'button_radius'   => '8px',
			'spacing_unit'    => '8px',
			'is_dark_mode'    => false,
		);

		if ( ! CEAC_Settings::get( 'theme_sync_enabled', true ) ) {
			return $styles;
		}

		$customizer = get_theme_mods();
		if ( ! empty( $customizer ) ) {
			$color_keys = array(
				'primary_color'   => array( 'primary_color', 'accent_color', 'link_color', 'button_bg' ),
				'secondary_color' => array( 'secondary_color', 'header_bg', 'footer_bg' ),
				'accent_color'    => array( 'accent_color', 'highlight_color', 'button_hover_bg' ),
			);

			foreach ( $color_keys as $style_key => $mod_keys ) {
				foreach ( $mod_keys as $mod_key ) {
					if ( ! empty( $customizer[ $mod_key ] ) && self::is_valid_color( $customizer[ $mod_key ] ) ) {
						$styles[ $style_key ] = $customizer[ $mod_key ];
						break;
					}
				}
			}
		}

		$logo_id = get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$logo_url = wp_get_attachment_image_url( $logo_id, 'medium' );
			if ( $logo_url ) {
				$styles['logo_url'] = $logo_url;
			}
		}

		$site_icon = get_site_icon_url( 64 );
		if ( $site_icon ) {
			$styles['favicon_url'] = $site_icon;
		}

		$theme = wp_get_theme();
		$styles['theme_name'] = $theme->get( 'Name' );

		if ( CEAC_Settings::get( 'dark_mode_detection', true ) ) {
			$styles['is_dark_mode'] = self::detect_dark_mode();
		}

		$styles = apply_filters( 'ceac_theme_styles', $styles );
		return $styles;
	}

	private static function is_valid_color( $color ) {
		return preg_match( '/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $color )
			|| preg_match( '/^rgba?\(/', $color )
			|| preg_match( '/^hsla?\(/', $color );
	}

	private static function detect_dark_mode() {
		$dark_indicators = array(
			get_theme_mod( 'dark_mode', false ),
			get_theme_mod( 'color_scheme', '' ) === 'dark',
		);
		return in_array( true, $dark_indicators, true );
	}

	public static function get_css_variables() {
		$styles = self::extract_theme_styles();
		$header = CEAC_Settings::get( 'header_color' );

		$vars = array(
			'--ceac-primary'        => $styles['primary_color'],
			'--ceac-secondary'      => $styles['secondary_color'],
			'--ceac-accent'         => $styles['accent_color'],
			'--ceac-header'         => $header ?: $styles['primary_color'],
			'--ceac-font-family'    => $styles['font_family'],
			'--ceac-font-size'      => $styles['font_size'],
			'--ceac-border-radius'  => CEAC_Settings::get( 'bubble_style' ) === 'square' ? '4px' : $styles['border_radius'],
			'--ceac-button-radius'  => $styles['button_radius'],
			'--ceac-spacing'        => $styles['spacing_unit'],
			'--ceac-widget-width'   => CEAC_Settings::get( 'widget_width', 380 ) . 'px',
			'--ceac-widget-height'  => CEAC_Settings::get( 'widget_height', 520 ) . 'px',
			'--ceac-transparency'   => CEAC_Settings::get( 'widget_transparency', 1 ),
		);

		if ( ! empty( $styles['is_dark_mode'] ) ) {
			$vars['--ceac-bg']         = '#1a202c';
			$vars['--ceac-text']       = '#f7fafc';
			$vars['--ceac-bubble-bot'] = '#2d3748';
			$vars['--ceac-bubble-user'] = '#2b6cb0';
		} else {
			$vars['--ceac-bg']         = '#ffffff';
			$vars['--ceac-text']       = '#1a202c';
			$vars['--ceac-bubble-bot'] = '#f7fafc';
			$vars['--ceac-bubble-user'] = $styles['primary_color'];
		}

		return $vars;
	}
}
