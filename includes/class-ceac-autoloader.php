<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Autoloader {

	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	public static function autoload( $class ) {
		if ( strpos( $class, 'CEAC_' ) !== 0 ) {
			return;
		}

		$file = CEAC_PLUGIN_DIR . 'includes/class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
