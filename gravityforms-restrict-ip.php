<?php
/**
 * Full strict types.

 *  @package GFRestrictIP\Main
 */

declare(strict_types=1);

/*
Plugin Name: Restrict IPs for GravityForms
Plugin URI: https://infostreamusa.com
Description: Gravityforms block malicious IP addresses attempting bypassing the captcha
Version: 1.3
Author: Frederic Guzman
Author URI: https://infostreamusa.com
License: GNU GENERAL
*/

use GFRestrictIP\Main;
define( 'GFREIP_TABLE_NAME', 'gf_restrict_ip' );
defined( 'ABSPATH' ) || die();
/**
 * Plugin itself class loading
 */
if ( ! class_exists( 'GFRestrictIP\Main' ) ) {
	$autoloader = require_once 'autoload.php';
	$autoloader( 'GFRestrictIP\\Main', __DIR__ . '/src/Main' );
}

/**
 * Gravity form dependency
 */
if ( ! class_exists( '\GForms' ) ) {
	\esc_html( '<div class="error notice"> <p><strong>Gravity Forms should be installed.</strong></p></div>' );
}

/**
 * Initial activation of plugin
 *
 * @return void
 */
function gf_restrict_ip_activation() {
	global $wpdb;
	global $jal_db_version;

	$charset_collate = $wpdb->get_charset_collate();

	$installed_ver = get_option( 'gf_restrict_db_version' );

	if ( $installed_ver !== $jal_db_version ) {

		$gfrestrictip_table_name = $wpdb->prefix . GFREIP_TABLE_NAME;

		if ( $gfrestrictip_table_name != $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $gfrestrictip_table_name ) ) ) {

			$sql = $wpdb->prepare(
				"CREATE TABLE %s (
					id INT(255) NOT NULL AUTO_INCREMENT,
					ip VARCHAR(255) NOT NULL,
					count INT(255) NOT NULL,
					PRIMARY KEY (id),
					UNIQUE gfrestrictip (ip)
				) %s;",
				$gfrestrictip_table_name,
				$charset_collate
			);

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		update_option( 'gf_restrict_db_version', $jal_db_version );
	}
}

// Activating.
register_activation_hook( __FILE__, 'gf_restrict_ip_activation' );

/**
 * Filtering the link on plugins page.
 *
 * @param array $links array of url.
 * @return array $links array of url.
 */
function gf_restrict_ip_clear_ip( array $links ) {
	$home_url = get_home_url();
	$settings_link = '<a target="_blank" href="' . $home_url . '/wp-json/gfrestrictip/v1/clear_ip_addresses?token=1ssdxkVe9T3nLYRWkF4Mnybasd">Clear List of IPs</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

$plugin_directory = plugin_basename( __FILE__ );
add_filter( 'plugin_action_links_' . $plugin_directory, 'gf_restrict_ip_clear_ip' );

// Loading plugin after gravity form loaded.
add_action(
	'gform_loaded',
	function () {
		Main::init();
	},
	4
);
