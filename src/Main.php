<?php

declare(strict_types=1);

namespace GFRestrictIP;

use \WP_Error;
use \GForms;
use GFRestrictIP\Main\Rest;

/**
 * Class GFRestrictIP
 * @package GFRestrictIP
 */
class Main
{
	/**
	 * @var self Plugin instance.
	 */
	private static $instance;

	/**
	 * @return self Plugin instance.
	 */
	public static function init(): self
	{
		if (self::$instance === null) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * @var int Attempt limit by IP
	 */
	private static $AttemptLimit = 8;

	/**
	 * @var string RestAPI token
	 */
	public static $token = '1ssdxkVe9T3nLYRWkF4Mnybasd';

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		add_filter( 'gform_validation', [ $this, 'GFRestrictIPValidation' ] );
		add_filter( 'gform_pre_render', [ $this, 'GFRestrictIPCheckRestriction' ] );
		add_action( 'gform_pre_submission', [ $this, 'GFRestrictIPCheckRestriction' ] );

		add_filter( 'gform_field_validation_2_7', function ( $result, $value, $form, $field ) {
			if ( $result['is_valid'] == false ) {
				$this->GFRestrictIPAddCount();
			}

			return $result;
		}, 10, 4 );

		//add_action('wp_enqueue_scripts', [$this, 'adminScripts']);
		add_action('rest_api_init', [$this, 'registerRestMethods']);
	}

	/**
	 * Register REST methods
	 */
	public function registerRestMethods() {
		new Rest\Admin();
	}

	public function GFRestrictIPCheckRestriction( $form ) {

		$totalAttempt = $this->GFRestrictIPCount();

		if ( $totalAttempt >= self::$AttemptLimit ) {
			echo "<style>.gform_not_found { display: none }</style>";
			return esc_html_e("Sorry! You have exceeded the allowed payment attempts for 1 day.  Please try again in 24 hours, or to process payment now please call us at 1-800-52-MUSIC!", 'text-GFRestrictIP');
		}

		return $form;
	}

	public function GFRestrictIPValidation( $validation_result ) {
		if ( !$validation_result['is_valid'] ) {
			$this->GFRestrictIPAddCount();
		}

		return $validation_result;
	}

	private function GFRestrictIPCount () {
		global $wpdb;

		$ip = $this->getIP();
		$gfrestrictip_table_name  = $wpdb->prefix . GFREIP_TABLE_NAME;

		$rowcount = $wpdb->get_var("SELECT count FROM {$gfrestrictip_table_name} WHERE ip = '{$ip}'");

		return $rowcount ? $rowcount : false;
	}

	private function GFRestrictIPAddCount () {
		global $wpdb;

		$ip = $this->getIP();
		$gfrestrictip_table_name  = $wpdb->prefix . GFREIP_TABLE_NAME;

		$rowcount = $wpdb->get_var("SELECT count FROM {$gfrestrictip_table_name} WHERE ip = '{$ip}'");

		$countQly = $rowcount ? $rowcount : false;

		if ( $countQly === false ) {
			$return = $wpdb->query( "INSERT INTO {$gfrestrictip_table_name} (ip, count)
    		VALUES('{$ip}', 1)" );
		} else {
			$count_query = $countQly + 1;
			$return = $wpdb->query( "UPDATE {$gfrestrictip_table_name} SET count = {$count_query} WHERE ip = '{$ip}'" );
		}

		return $return;
	}

	public static function clear_ip_list () {
		global $wpdb;
		$gfrestrictip_table_name  = $wpdb->prefix . GFREIP_TABLE_NAME;
		return $wpdb->query( "DELETE FROM {$gfrestrictip_table_name} WHERE  1=1" );
	}

	private function getIP() {
		$ip = $_SERVER['HTTP_CLIENT_IP'] ? $_SERVER['HTTP_CLIENT_IP'] : ($_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);

		return $ip;
	}
}
