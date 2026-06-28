<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Activator {

	public static function activate() {
		self::create_tables();
		self::set_default_options();
		CEAC_Knowledge::seed_default_knowledge();
		flush_rewrite_rules();
	}

	private static function create_tables() {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		$tables = array(
			"CREATE TABLE {$wpdb->prefix}ceac_conversations (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				session_id VARCHAR(64) NOT NULL,
				user_id BIGINT UNSIGNED DEFAULT 0,
				user_name VARCHAR(255) DEFAULT '',
				page_url TEXT,
				page_context VARCHAR(255) DEFAULT '',
				language VARCHAR(10) DEFAULT 'en',
				status VARCHAR(20) DEFAULT 'active',
				sentiment VARCHAR(20) DEFAULT 'neutral',
				token_count INT UNSIGNED DEFAULT 0,
				escalated TINYINT(1) DEFAULT 0,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY session_id (session_id),
				KEY created_at (created_at)
			) $charset;",

			"CREATE TABLE {$wpdb->prefix}ceac_messages (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				conversation_id BIGINT UNSIGNED NOT NULL,
				role VARCHAR(20) NOT NULL,
				content LONGTEXT NOT NULL,
				confidence FLOAT DEFAULT 1.0,
				is_fallback TINYINT(1) DEFAULT 0,
				intent VARCHAR(100) DEFAULT '',
				token_count INT UNSIGNED DEFAULT 0,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY conversation_id (conversation_id),
				KEY intent (intent)
			) $charset;",

			"CREATE TABLE {$wpdb->prefix}ceac_knowledge (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				source_type VARCHAR(50) NOT NULL,
				source_id BIGINT UNSIGNED DEFAULT 0,
				title VARCHAR(500) NOT NULL,
				content LONGTEXT NOT NULL,
				url TEXT,
				category VARCHAR(100) DEFAULT 'general',
				keywords TEXT,
				embedding_hash VARCHAR(64) DEFAULT '',
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY source_type (source_type),
				KEY category (category),
				FULLTEXT KEY content_search (title, content, keywords)
			) $charset;",

			"CREATE TABLE {$wpdb->prefix}ceac_knowledge_links (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				source_knowledge_id BIGINT UNSIGNED NOT NULL,
				target_knowledge_id BIGINT UNSIGNED NOT NULL,
				link_type VARCHAR(50) DEFAULT 'related',
				strength FLOAT DEFAULT 1.0,
				PRIMARY KEY (id),
				KEY source_knowledge_id (source_knowledge_id),
				KEY target_knowledge_id (target_knowledge_id)
			) $charset;",

			"CREATE TABLE {$wpdb->prefix}ceac_analytics (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				event_type VARCHAR(50) NOT NULL,
				event_data LONGTEXT,
				conversation_id BIGINT UNSIGNED DEFAULT 0,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY event_type (event_type),
				KEY created_at (created_at)
			) $charset;",

			"CREATE TABLE {$wpdb->prefix}ceac_audit_log (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				conversation_id BIGINT UNSIGNED DEFAULT 0,
				action VARCHAR(100) NOT NULL,
				details LONGTEXT,
				ip_address VARCHAR(45) DEFAULT '',
				user_agent TEXT,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY conversation_id (conversation_id),
				KEY action (action)
			) $charset;",

			"CREATE TABLE {$wpdb->prefix}ceac_fallback_queries (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				query TEXT NOT NULL,
				conversation_id BIGINT UNSIGNED DEFAULT 0,
				resolved TINYINT(1) DEFAULT 0,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY resolved (resolved)
			) $charset;",
		);

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $tables as $sql ) {
			dbDelta( $sql );
		}

		update_option( 'ceac_db_version', CEAC_VERSION );
	}

	private static function set_default_options() {
		$defaults = CEAC_Settings::get_defaults();
		$existing   = get_option( 'ceac_settings', array() );
		update_option( 'ceac_settings', wp_parse_args( $existing, $defaults ) );
	}
}
