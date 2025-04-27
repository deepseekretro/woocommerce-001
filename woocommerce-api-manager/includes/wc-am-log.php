<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager API Log Class
 *
 * @since       2.0
 *
 * @author      Kestrel
 * @author      Copyright (c) Kestrel [hey@kestrelwp.com]
 * @package     WooCommerce API Manager/Log
 */
class WC_AM_Log {

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_Log
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() { }

	/**
	 * Logs debug messages for the APIs.
	 *
	 * @since 2.0
	 *
	 * @param string $message
	 */
	public function api_debug_log( $message ) {
		$logger = wc_get_logger();

		$logger->debug( $message, [ 'source' => 'wc-am-api-query-log' ] );
	}

	/**
	 * Logs error messages for the APIs.
	 *
	 * @since 2.0
	 *
	 * @param string $message
	 */
	public function api_error_log( $message ) {
		$logger = wc_get_logger();

		$logger->error( $message, [ 'source' => 'wc-am-api-error-log' ] );
	}

	/**
	 * Logs response messages for the APIs.
	 *
	 * @since 2.0
	 *
	 * @param string $message
	 */
	public function api_response_log( $message ) {
		$logger = wc_get_logger();

		$logger->info( $message, [ 'source' => 'wc-am-api-response-log' ] );
	}

	/**
	 * Logs any error.
	 *
	 * @since   2.3.2
	 * @updated 2.4.5 with added $source parameter
	 *
	 * @param string $message
	 * @param string $source
	 */
	public function log_error( $message, $source = '' ) {
		$logger = wc_get_logger();
		$value  = 'wc-am-error-' . $source . '-log';

		$logger->error( $message, [ 'source' => $value ] );
	}

	/**
	 * Logs any info.
	 *
	 * @since   2.3.2
	 * @updated 2.4.5 with added $source parameter
	 *
	 * @param string $message
	 * @param string $source
	 */
	public function log_info( $message, $source = '' ) {
		$logger = wc_get_logger();
		$value  = 'wc-am-info-' . $source . '-log';

		$logger->info( $message, [ 'source' => $value ] );
	}

	/**
	 * Logs test messages.
	 *
	 * @since 2.0
	 *
	 * @param string $message
	 */
	public function test_log( $message ) {
		$logger = wc_get_logger();

		$logger->info( $message, [ 'source' => 'wc-am-api-test-log' ] );
	}

}
