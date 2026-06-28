<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$tables = array(
	'ceac_conversations',
	'ceac_messages',
	'ceac_knowledge',
	'ceac_knowledge_links',
	'ceac_analytics',
	'ceac_audit_log',
	'ceac_fallback_queries',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" );
}

delete_option( 'ceac_settings' );
delete_option( 'ceac_db_version' );
delete_option( 'ceac_last_sync' );

$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ceac_daily_tokens_%'" );

wp_clear_scheduled_hook( 'ceac_sync_content' );
wp_clear_scheduled_hook( 'ceac_purge_logs' );
