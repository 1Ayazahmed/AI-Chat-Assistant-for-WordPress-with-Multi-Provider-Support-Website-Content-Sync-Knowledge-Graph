<?php
/**
 * Plugin Name:       AI Assistant with Knowledge Graph
 * Plugin URI:        https://example.com
 * Description:       AI chatbot with knowledge graph, theme sync, OpenAI-compatible providers, analytics, and smart fallback.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Ayaz Ahmed
 * License:           GPL v2 or later
 * Text Domain:       ceac
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CEAC_VERSION', '1.0.0' );
define( 'CEAC_PLUGIN_FILE', __FILE__ );
define( 'CEAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CEAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CEAC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once CEAC_PLUGIN_DIR . 'includes/class-ceac-autoloader.php';
CEAC_Autoloader::register();

register_activation_hook( __FILE__, array( 'CEAC_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'CEAC_Deactivator', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'CEAC_Plugin', 'instance' ) );
