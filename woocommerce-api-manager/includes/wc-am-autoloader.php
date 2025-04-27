<?php

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Autoloader Class
 *
 * @since       1.5
 *
 * @author      Kestrel
 * @author      Copyright (c) Kestrel [hey@kestrelwp.com]
 * @package     WooCommerce API Manager/Autoloader
 */
class WC_AM_Autoloader {

	/**
	 * Path to the includes directory.
	 *
	 * @since 3.0
	 * @var string
	 */
	private $include_path = '';

	/**
	 * WC_AM_Autoloader constructor.
	 *
	 * @since   1.5
	 * @updated 2.6 __autoload removed and is deprecated as of 7.2.0, and removed as of PHP 8.0.0.
	 * @updated 3.0 Added $this->include_path.
	 */
	public function __construct() {
		spl_autoload_register( [ $this, 'autoload' ] );

		$this->include_path = untrailingslashit( plugin_dir_path( WC_AM_PLUGIN_FILE ) ) . '/includes/';
	}

	/**
	 * Make class name lowercase, then replace underscores with dashes, and append a .php.
	 *
	 * @since 1.5
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	private function get_file_name_from_class( $class ) {
		return str_replace( '_', '-', strtolower( $class ) ) . '.php';
	}

	/**
	 * Make sure the file is readable, then load it.
	 *
	 * @since 1.5
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			// nosemgrep
			require_once $path; // phpcs:ignore

			return true;
		}

		return false;
	}

	/**
	 * Autoload the class if it has not been loaded already.
	 *
	 * @since 1.5
	 * @since 3.0 Add conditional checks before foreach and before loadfile().
	 *
	 * @param string $class_name
	 */
	private function autoload( $class_name ) {
		// If WC_AM_ is found at position 0, then this is the class we're looking for.
		if ( strpos( $class_name, 'WC_AM_' ) === 0 ) {
			$file = $this->get_file_name_from_class( $class_name );

			$paths = [
				$this->include_path . $file,
				$this->include_path . 'admin/' . $file,
				$this->include_path . 'admin/menus/' . $file,
				$this->include_path . 'api/' . $file,
				$this->include_path . 'data-stores/' . $file,
				$this->include_path . 'queue/' . $file,
			];

			if ( ! empty( $paths ) && ! empty( $file ) ) {
				foreach ( $paths as $key => $path ) {
					if ( ! $this->load_file( $file ) ) {
						$this->load_file( $path );
					}
				}
			}
		}
	}
}

new WC_AM_Autoloader();
