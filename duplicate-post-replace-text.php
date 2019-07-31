<?php
/*
 Plugin Name: Duplicate Post and Replace text
 Plugin URI: 
 Description: Clone posts and replace the text.
 Version: 1.1.1
 Author: Vishal Chodvadiya
 Author URI: 
 Text Domain: dprt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialise the internationalisation domain
 */
function dprt_load_plugin_textdomain() {
    load_plugin_textdomain( 'duplicate-post', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'dprt_load_plugin_textdomain' );


add_filter("plugin_action_links_".plugin_basename(__FILE__), "dprt_plugin_actions", 10, 4);

function dprt_plugin_actions( $actions, $plugin_file, $plugin_data, $context ) {
	array_unshift($actions, "<a href=\"".menu_page_url('dprt', false)."\">".esc_html__("Settings")."</a>");
	return $actions;
}

// require_once (dirname(__FILE__).'/duplicate-post-common.php');

if (is_admin()){
	require_once (dirname(__FILE__).'/duplicate-post-replace-text-admin.php');
}