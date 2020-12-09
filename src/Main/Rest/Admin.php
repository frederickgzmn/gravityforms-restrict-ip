<?php

declare(strict_types=1);

namespace GFRestrictIP\Main\Rest;

use GFRestrictIP\Main;

/**
 * API Class Admin
 * @package GFRestrictIP\Rest
 */
class Admin {

	public function __construct() {
		//GET
		register_rest_route('gfrestrictip/v1', 'clear_ip_addresses', [
			'methods' => 'GET',
			'callback' => [$this, 'ClearIPAddresses'],
		]);
	}

	public function ClearIPAddresses(\WP_REST_Request $params): bool {
		if ( $params->get_param('token') == Main::$token ) {
			Main::clear_ip_list();
		}
		return true;
	}
}
