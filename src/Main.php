<?php
/**
 * Full strict types.

 *  @package GFRestrictIP
 */

declare(strict_types=1);

namespace GFRestrictIP;

use \WP_Error;
use \GForms;
use GFRestrictIP\Main\Rest;

/**
 * Class Main
 */
class Main {
	/**
	 * Plugin instance.

	 * @var self
	 */
	private static $instance;

	/**
	 * Plugin instance.

	 * @return self
	 */
	public static function init(): self {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 *  Attempt limit by IP

	 * @var int
	 */
	private static $attempt_limit = 8;

	/**
	 *  RestAPI token

	 * @var string
	 */
	public static $token = '1ssdxkVe9T3nLYRWkF4Mnybasd'; // test token.

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		add_filter( 'gform_validation', array( $this, 'gf_restrict_ip_validation' ) );
		add_filter( 'gform_pre_render', array( $this, 'gf_restrict_ip_check_restriction' ) );
		add_action( 'gform_pre_submission', array( $this, 'gf_restrict_ip_check_restriction' ) );

		add_filter(
			'gform_field_validation_2_7',
			function ( $result, $value, $form, $field ) {
				if ( false === $result['is_valid'] ) {
					$this->gf_restrict_ip_add_count();
				}

				return $result;
			},
			10,
			4,
		);

		add_action( 'rest_api_init', array( $this, 'register_rest_methods' ) );
	}

	/**
	 * Register REST methods
	 */
	public function register_rest_methods() {
		new Rest\Admin();
	}

	/**
	 * Apply restriction to users.
	 *
	 * @param [GravityForm] $form Gravity form object.
	 * @return [GravityForm] $form Gravity form object.
	 */
	public function gf_restrict_ip_check_restriction( $form ) {

		$total_attempt = $this->gf_restrict_ip_count();

		if ( $total_attempt >= self::$attempt_limit ) {
			echo esc_attr( '<style>.gform_not_found { display: none }</style>' );
			return esc_html_e( 'Sorry! You have exceeded the allowed payment attempts for 1 day.  Please try again in 24 hours, or to process payment now please call us at 1-800-52-MUSIC!', 'text-GFRestrictIP' );
		}

		return $form;
	}

	/**
	 * Validation of the IP address detected.

	 * @param [array] $validation_result IP detected.
	 * @return array
	 */
	public function gf_restrict_ip_validation( array $validation_result ) {
		if ( ! $validation_result['is_valid'] ) {
			$this->gf_restrict_ip_add_count();
		}

		return $validation_result;
	}

	/**
	 * Count of ip addresses blocked

	 * @return mixed
	 */
	private function gf_restrict_ip_count() {
		global $wpdb;

		$ip = $this->get_ip();
		$gfrestrictip_table_name = $wpdb->prefix . GFREIP_TABLE_NAME;

		$rowcount = $wpdb->get_var( $wpdb->prepare( 'SELECT count FROM %s WHERE ip = %s', $gfrestrictip_table_name, $ip ) );

		return $rowcount ? $rowcount : false;
	}

	/**
	 * Add ip address to the block list

	 * @return void
	 */
	private function gf_restrict_ip_add_count() {
		global $wpdb;

		$ip                      = $this->get_ip();
		$gfrestrictip_table_name = $wpdb->prefix . GFREIP_TABLE_NAME;

		$rowcount = $wpdb->get_var( $wpdb->prepare( 'SELECT count FROM %s WHERE ip = %s', $gfrestrictip_table_name, $ip ) );

		$count_qly = $rowcount ? $rowcount : false;

		if ( false === $count_qly ) {
			$return = $wpdb->query( $wpdb->prepare( 'INSERT INTO %s ( ip, count ) VALUES ( %s, 1 )', $gfrestrictip_table_name, $ip ) );
		} else {
			$count_query = $count_qly + 1;
			$return = $wpdb->query( $wpdb->prepare( 'UPDATE %s SET count = %d WHERE ip = %s', $gfrestrictip_table_name, $count_query, $ip ) );
		}
	}

	/**
	 * Clear the list of ip addresses blocked.

	 * @return bol
	 */
	public static function clear_ip_list() {
		global $wpdb;
		$gfrestrictip_table_name = $wpdb->prefix . GFREIP_TABLE_NAME;

		return $wpdb->query( $wpdb->prepare( 'DELETE FROM %s WHERE 1=1', $gfrestrictip_table_name ) );
	}

	/**
	 * Get the ip address detected by the server from the browser.

	 * @return string
	 */
	private function get_ip() {
		$ip_address = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} else {
			$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip_address;
	}
}
