<?php
/**
 * Full strict types.

 *  @package GFRestrictIP/Rest
 */

declare(strict_types=1);

namespace GFRestrictIP\Main\Rest;

use GFRestrictIP\Main;

/**
 * API Class Admin

 * @package Admin
 */
class Admin {

	/**
	 * Rest api constructor
	 */
	public function __construct() {

		register_rest_route(
			'gfrestrictip/v1',
			'clear_ip_addresses',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'clear_ip_addresses' ),
			)
		);
	}

	/**
	 * Action rest api
	 *
	 * @param \WP_REST_Request $params fields sent by ajax.
	 * @return boolean
	 */
	public function clear_ip_addresses( \WP_REST_Request $params ): bool {
		if ( $params->get_param( 'token' ) === Main::$token ) {
			Main::clear_ip_list();
		}

		return true;
	}
}
