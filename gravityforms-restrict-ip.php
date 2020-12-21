<?php

declare(strict_types=1);

/*
Plugin Name: GravityForms Restrict Forms
Plugin URI: https://infostreamusa.com
Description: Gravityforms block malicious IP addresses attempting bypassing the captcha
Version: 1.0
Author: Frederic Guzman
Author URI: https://infostreamusa.com
License: GNU GENERAL
*/

use GFRestrictIP\Main;

define('GFREIP_TABLE_NAME', 'gf_restrict_ip');
//define('GFREIP_token', '1ssdxkVe9T3nLYRWkF4Mnybasd');
defined('ABSPATH') or die();

if (!class_exists('GFRestrictIP\Main')) {
	$autoloader = require_once('autoload.php');
	$autoloader('GFRestrictIP\\Main', __DIR__ . '/src/Main');
}

add_action( 'admin_init', function() {
	if ( !\is_plugin_active('gravityforms/gravityforms.php') ) {
		echo __('<div class="error notice"> <p><strong>GravityForms Restrict Forms: Gravity Forms should be installed.</strong></p></div>');
	}
});

function GFRestrictIPActivation() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	$gfrestrictip_table_name  = $wpdb->prefix . GFREIP_TABLE_NAME;

	$wpdb->query( "
		CREATE TABLE IF NOT EXISTS {$gfrestrictip_table_name} (
		id INT(255) NOT NULL AUTO_INCREMENT,
		ip VARCHAR(255) NOT NULL, 
		count INT(255) NOT NULL, 
		PRIMARY KEY (id), 
		UNIQUE gfrestrictip (ip)
		) $charset_collate;
	" );

}
register_activation_hook( __FILE__, 'GFRestrictIPActivation');

add_action('gform_loaded', function () {
	Main::init();
}, 4);
