<?php

declare(strict_types=1);

namespace GFRestrictIP\Main\Ajax;

use GFRestrictIP\Main;

/**
 * API Class Admin
 * @package GFRestrictIP\Ajax
 */
class Admin {

	public function __construct() {

		add_action( "wp_ajax_gfrestrictip_clearip", [ $this, "ClearIPAddresses" ] );
	}

	public function ClearIPAddresses(): array {

		// Check for nonce security
		$nonce = $_POST['nonce'];

		if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			return wp_die( [ 'response' => __( 'Invalid permissions', "cb_plugin_textdomain" ) ] );
		}

		if ( empty( $_POST ) ) {
			return wp_die( [ 'response' => 'Invalid data', "gfrestrictip" ] );
		}

		Main::clear_ip_list();

		wp_die( json_encode( [ 'response' => true ] ) );
	}
}
