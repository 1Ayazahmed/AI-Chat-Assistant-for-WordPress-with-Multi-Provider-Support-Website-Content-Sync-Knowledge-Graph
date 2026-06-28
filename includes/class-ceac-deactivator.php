<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Deactivator {

	public static function deactivate() {
		wp_clear_scheduled_hook( 'ceac_sync_content' );
		wp_clear_scheduled_hook( 'ceac_purge_logs' );
		flush_rewrite_rules();
	}
}
