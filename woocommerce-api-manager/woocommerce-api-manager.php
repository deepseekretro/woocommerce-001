<?php
/**
 * Plugin Name: API Manager for WooCommerce
 * Plugin URI: https://woo.com/products/woocommerce-api-manager/
 * Description: Sell license keys and subscriptions for software and online services to grow your subscription business.
 * Version: 3.4.2
 * Author: Kestrel
 * Author URI: https://kestrelwp.com
 * Text Domain: woocommerce-api-manager
 * Domain Path: /i18n/languages/
 * Requires WP: 6.0
 * Requires at least: 6.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * WC requires at least: 7.4
 * WC tested up to: 9.8.2
 * Woo: 260110:f7cdcfb7de76afa0889f07bcb92bf12e
 *
 * Copyright: (c) 2013-2025 Kestrel [hey@kestrelwp.com]
 *
 * @since       1.0
 * @author      Kestrel
 * @category    Plugin
 * @copyright   Copyright: (c) Kestrel [hey@kestrelwp.com]
 * @package     WooCommerce API Manager
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WC_AM_VERSION' ) ) {
	define( 'WC_AM_VERSION', '3.4.2' );
}

// Minimum WooCommerce version required.
if ( ! defined( 'WC_AM_WC_MIN_REQUIRED_VERSION' ) ) {
	define( 'WC_AM_WC_MIN_REQUIRED_VERSION', '7.4' );
}

// Minimum PHP version required.
if ( ! defined( 'WC_AM_REQUIRED_PHP_VERSION' ) ) {
	define( 'WC_AM_REQUIRED_PHP_VERSION', '7.4' );
}

// Minimum WooCommerce Subscriptions version required.
if ( ! defined( 'WC_AM_WC_SUBS_MIN_REQUIRED_VERSION' ) ) {
	define( 'WC_AM_WC_SUBS_MIN_REQUIRED_VERSION', '4.9.1' );
}

if ( ! defined( 'WC_AM_PLUGIN_FILE' ) ) {
	define( 'WC_AM_PLUGIN_FILE', __FILE__ );
}

/**
 * @since 2.5.7
 */
if ( ! defined( 'WC_AM_ENABLE_CACHE' ) ) {
	define( 'WC_AM_ENABLE_CACHE', true );
}

/**
 * @since 2.6.6
 */
if ( ! defined( 'WC_AM_DISABLE_HOMEPAGE_CACHE' ) ) {
	define( 'WC_AM_DISABLE_HOMEPAGE_CACHE', true );
}

if ( ! WooCommerce_API_Manager::is_woocommerce_active_static() ) {
	add_action( 'admin_notices', 'WooCommerce_API_Manager::woocommerce_inactive_notice' );

	return;
}

// Required PHP version notice.
if ( version_compare( PHP_VERSION, WC_AM_REQUIRED_PHP_VERSION, '<' ) ) {
	add_action( 'admin_notices', 'WooCommerce_API_Manager::wam_php_requirement' );

	return;
}

// Disable the WooCommerce API Manager until WooCommerce has been upgraded to the required minimum version.
$wam_wc_active_version = get_option( 'woocommerce_version' );

if ( ! empty( $wam_wc_active_version ) && version_compare( $wam_wc_active_version, WC_AM_WC_MIN_REQUIRED_VERSION, '<' ) ) {
	add_action( 'admin_notices', 'WooCommerce_API_Manager::upgrade_wc_am_warning' );

	return;
}

/**
 * Disable the WooCommerce API Manager until WooCommerce Subscriptions has been upgraded to the required minimum version,
 * if WooCommerce Subscriptions is installed and active.
 *
 * @since 2.0.15
 */
if ( WooCommerce_API_Manager::is_wc_subscriptions_active_static() ) {
	$wam_wc_subs_active_version = get_option( 'woocommerce_subscriptions_active_version' );

	if ( ! empty( $wam_wc_subs_active_version ) && version_compare( $wam_wc_subs_active_version, WC_AM_WC_SUBS_MIN_REQUIRED_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'WooCommerce_API_Manager::upgrade_wc_sub_am_warning' );

		return;
	}
}

final class WooCommerce_API_Manager {

	private bool $db_cache         = false;
	private int $db_cache_expires  = 5;
	private int $api_cache_expires = 5;
	private bool $wc_subs_exist    = false;
	private string $file;
	private string $plugin_file;
	private bool $grant_access_after_payment = false;
	private int $unlimited_activation_limit  = 0;
	private bool $wc_hpos_active             = false;
	private bool $is_wc_custom_order_tables_usage_enabled = false;

	public $product_tabs;

	/**
	 * @var null The single instance of the class
	 */
	private static $_instance = null;

	/**
	 * Singular class instance safeguard.
	 * Ensures only one instance of a class can be instantiated.
	 * Follows a singleton design pattern.
	 *
	 * @static
	 *
	 * @return WooCommerce_API_Manager - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'woocommerce-api-manager' ), '2.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'woocommerce-api-manager' ), '2.0' );
	}

	private function __construct() {
		$this->db_cache          = WC_AM_ENABLE_CACHE;
		$this->db_cache_expires  = 1440; // 24 hours.
		$this->api_cache_expires = 60; // 1 hour.
		$this->wc_subs_exist     = $this->is_wc_subscriptions_active();
		$this->file              = plugin_basename( __FILE__ );
		$this->plugin_file       = __FILE__;
		$this->grant_access_after_payment = get_option( 'woocommerce_downloads_grant_access_after_payment' ) === 'yes';
		$this->unlimited_activation_limit = apply_filters( 'wc_api_manager_unlimited_activation_limit', 100000 ); // since 2.2

		// Include required files
		$this->includes();
	}

	/**
	 * @since 2.3.1
	 *
	 * @return bool
	 */
	public function get_db_cache() {
		return $this->db_cache;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return int
	 */
	public function get_db_cache_expires() {
		return $this->db_cache_expires;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return int
	 */
	public function get_api_cache_expires() {
		return $this->api_cache_expires;
	}

	/**
	 * Return the WooCommerce version.
	 *
	 * @since 2.0
	 *
	 * @return string|bool
	 */
	public function get_wc_version() {
		if ( defined( 'WC_VERSION' ) && WC_VERSION ) {
			return WC_VERSION;
		} elseif ( defined( 'WOOCOMMERCE_VERSION' ) && WOOCOMMERCE_VERSION ) {
			return WOOCOMMERCE_VERSION;
		} elseif ( ! is_null( get_option( 'woocommerce_version', null ) ) ) {
			return get_option( 'woocommerce_version' );
		}

		return false;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return bool
	 */
	public function get_wc_subs_exist() {
		return $this->wc_subs_exist;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return string
	 */
	public function get_file() {
		return $this->file;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return string
	 */
	public function get_plugin_file() {
		return $this->plugin_file;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return bool
	 */
	public function get_grant_access_after_payment() {
		return $this->grant_access_after_payment;
	}

	/**
	 * @since 2.3.1
	 *
	 * @return int
	 */
	public function get_unlimited_activation_limit() {
		return (int) $this->unlimited_activation_limit;
	}

	/**
	 * Returns the WC_AM_API_Requests class object.
	 *
	 * @since 2.0
	 *
	 * @return \WC_AM_API_Requests
	 */
	public function api_requests() {
		return WC_AM_API_REQUESTS( $_REQUEST );
	}

	/**
	 * Define a constant if it is not already defined.
	 *
	 * @since 2.0
	 *
	 * @param string $name  Constant name.
	 * @param string $value Value.
	 */
	public function maybe_define_constant( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Get the plugin's url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return plugins_url( '/', __FILE__ );
	}

	/**
	 * Get the plugin directory url.
	 *
	 * @return string
	 */
	public function plugins_dir_url() {
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get the plugin basename.
	 *
	 * @return string
	 */
	public function plugins_basename() {
		return untrailingslashit( $this->file );
	}

	/**
	 * Get the directory name of the plugin basename.
	 *
	 * @since 2.0.10
	 *
	 * @return string
	 */
	public function plugin_dirname_of_plugin_basename() {
		return dirname( untrailingslashit( $this->file ) );
	}

	/**
	 * Get Ajax URL.
	 *
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php' );
	}

	/**
	 * admin_scripts function.
	 */

	/**
	 * admin_scripts function.
	 *
	 * @param String $hook_suffix
	 *
	 * @updated 2.8
	 */
	public function admin_scripts( $hook_suffix ) {
		wp_enqueue_style( 'woocommerce_api_manager_admin_styles', $this->plugin_url() . 'includes/assets/css/admin.min.css', [], WC_AM_VERSION );
	}

	/**
	 * Get styles for the frontend
	 *
	 * @param array
	 *
	 * @return array
	 */
	public function enqueue_styles( $styles ) {
		if ( is_account_page() ) {
			$styles['woocommerce-api-manager'] = [
				'src'     => $this->plugin_url() . 'includes/assets/css/woocommerce-api-manager.min.css?' . WC_AM_VERSION,
				'deps'    => 'woocommerce-smallscreen',
				'version' => WC_AM_VERSION,
				'media'   => 'all',
			];

			if ( wp_get_theme() == 'Storefront' ) {
				$styles['wc-am-storefront-icons'] = [
					'src'     => $this->plugin_url() . 'includes/assets/css/wc-am-storefront-icons.min.css?' . WC_AM_VERSION,
					'deps'    => 'woocommerce-smallscreen',
					'version' => WC_AM_VERSION,
					'media'   => 'all',
				];
			}
		}

		return $styles;
	}

	/**
	 * Output queued JavaScript code in the footer inline.
	 *
	 * @since 1.3
	 *
	 * @param string $queued_js JavaScript
	 */
	public function wc_am_print_js( $queued_js ) {
		if ( ! empty( $queued_js ) ) {
			// Sanitize
			$queued_js = wp_check_invalid_utf8( $queued_js );
			$queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $queued_js );
			$queued_js = str_replace( "\r", '', $queued_js );

			echo "<!-- WooCommerce API Manager JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) ";
			echo '{';
			echo $queued_js . "});\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			unset( $queued_js );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		add_action( 'init', [ $this, 'maybe_activate_woocommerce_api_manager' ] );
		register_deactivation_hook( $this->plugin_file, [ $this, 'deactivate_woocommerce_api_manager' ] );

		require_once 'includes/interfaces/wcam-grace-period-data-store-interface.php';
		require_once 'includes/wc-am-autoloader.php';
		require_once 'includes/wc-am-core-functions.php';
		require_once 'includes/wcam-time-functions.php';
		require_once 'includes/wcam-api-activation-functions.php';
		require_once 'includes/wcam-api-resources-functions.php';

		// Load dependents of other plugins
		add_action( 'plugins_loaded', [ $this, 'load_dependents' ] );

		/**
		 * API requests handler.
		 *
		 * @since 2.0
		 */
		add_action( 'woocommerce_api_wc-am-api', [ $this, 'api_requests' ] );

		/**
		 * @deprecated @since 2.0
		 */
		add_action( 'woocommerce_api_upgrade-api', [ $this, 'api_requests' ] );
		add_action( 'woocommerce_api_am-software-api', [ $this, 'api_requests' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		// add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

		/**
		 * Run after Storefront because it sets the styles to be empty.
		 */
		add_filter( 'woocommerce_enqueue_styles', [ $this, 'enqueue_styles' ], 100, 1 );

		if ( is_admin() ) {
			add_action( 'admin_footer', [ $this, 'wc_am_print_js' ], 25 );
		}

		add_action( 'in_plugin_update_message-' . plugin_basename( __FILE__ ), [ $this, 'in_plugin_update_message' ], 10, 2 );

		/*
		 * @since 2.5
		 * Declare compatibility with WooCommerce HPOS.
		 */
		add_action( 'before_woocommerce_init', function () {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );

				if ( \Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled( 'custom_order_tables' ) ) {
					$this->wc_hpos_active = true;
				}

				// Check if WooCommerce custom order tables usage is enabled.
				if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && $this->wc_hpos_active ) {
					$this->is_wc_custom_order_tables_usage_enabled = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
				}
			}
		} );

		if ( 'yes' === get_option( 'woocommerce_api_manager_add_tabs_to_product_pages' ) ) {
			$this->product_tabs = new WC_AM_Product_Tabs();
		}
	}

	/**
	 * Checks on each admin page load if WooCommerce API Manager is activated.
	 *
	 * @since 2.0
	 */
	public function maybe_activate_woocommerce_api_manager() {
		$is_active = get_option( 'woocommerce_api_manager_active', false );

		if ( $is_active == false ) {
			add_option( 'woocommerce_api_manager_active', true );
			flush_rewrite_rules();
		}

		do_action( 'wc_api_manager_activated' );
	}

	/**
	 * Called when the WooCommerce API Manager is deactivated.
	 *
	 * @since 2.0
	 */
	public function deactivate_woocommerce_api_manager() {
		delete_option( 'woocommerce_api_manager_active' );
		flush_rewrite_rules();

		do_action( 'wc_api_manager_deactivated' );
	}

	/**
	 * Load dependents of other plugins
	 *
	 * @since 1.4.6.1
	 */
	public function load_dependents() {
		// Set up localisation
		$this->load_plugin_textdomain();

		/**
		 * @since 2.0.16
		 */
		if ( $this->is_wc_subscriptions_active() ) {
			// phpcs:ignore
			if ( class_exists( 'WC_Subscriptions' ) && version_compare( WC_Subscriptions::$version, WC_AM_WC_SUBS_MIN_REQUIRED_VERSION, '<' ) ) {
				add_action( 'admin_notices', __CLASS__ . '::upgrade_wc_sub_am_warning' );

				return;
			}

			// phpcs:ignore
			if ( class_exists( 'WC_Subscriptions_Core_Plugin' ) && version_compare( WC_subscriptions_Core_Plugin::instance()->get_plugin_version(), WC_AM_WC_SUBS_MIN_REQUIRED_VERSION, '<' ) ) {
				add_action( 'admin_notices', __CLASS__ . '::upgrade_wc_sub_am_warning' );

				return;
			}
		}

		$this->remove_my_account_email_download_links();

		require_once 'includes/wc-api-manager-query.php';
		/**
		 * Must run after the query.
		 *
		 * @since 3.0
		 */
		require_once 'includes/wc-am-renew-subscription.php';
		require_once 'includes/wc-am-emails.php';
	}

	/**
	 * Removes all download links from email, My Account, and Order Details in the My Account dashboard.
	 * The API Downloads table contains download URLs for each product.
	 *
	 * @since 1.3.4
	 */
	public function remove_my_account_email_download_links() {
		// Remove API downlads from My Account downloads.
		add_filter( 'woocommerce_customer_get_downloadable_products', [ WC_AM_PRODUCT_DATA_STORE(), 'filter_get_downloadable_products' ] );
		// Remove all download links from emails and My Account view-order Order Details
		add_filter( 'woocommerce_get_item_downloads', [ WC_AM_PRODUCT_DATA_STORE(), 'filter_get_item_downloads' ], 10, 2 );
	}

	/**
	 * Load Localization files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-api-manager', false, dirname( $this->plugins_basename() ) . '/i18n/languages/' );
	}

	/**
	 * Displays an inactive notice when WooCommerce is inactive.
	 *
	 * @since 1.0
	 */
	public static function woocommerce_inactive_notice() {
		?>
		<div class="notice notice-info is-dismissible">
			<p><?php
				esc_html_e( 'The ', 'woocommerce-api-manager' );
				echo '<strong>';
				esc_html_e( __( 'WooCommerce API Manager', 'woocommerce-api-manager' ) );
				echo '</strong>';
				esc_html_e( ' is inactive. The ', 'woocommerce-api-manager' );
				echo '<a href="' . esc_url( 'https://wordpress.org/plugins/woocommerce/' ) . '" target="_blank">';
				esc_html_e( 'WooCommerce', 'woocommerce-api-manager' );
				echo '</a>';
				esc_html_e( ' plugin must be active for the WooCommerce API Manager to work. Please activate WooCommerce on the ', 'woocommerce-api-manager' );
				echo '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">';
				esc_html_e( 'plugin page', 'woocommerce-api-manager' );
				echo '</a>';
				esc_html_e( ' once it is installed.', 'woocommerce-api-manager' );
			?>
			</p>
		</div>
		<?php
	}

	/**
	 * Required version of PHP.
	 *
	 * Types of notices:
	 * notice-error – error message displayed with a red border
	 * notice-warning – warning message displayed with a yellow border
	 * notice-success – success message displayed with a green border
	 * notice-info – info message displayed with a blue border
	 */
	public static function wam_php_requirement() {

		?>
		<div class="error notice-warning">
			<p><?php esc_html_e( sprintf( __( 'The WooCommerce API Manager is inactive because it requires PHP version %1$s or greater, but your server has %2$s installed. Ask your web host to upgrade your version of PHP.', 'woocommerce-api-manager' ), WC_AM_REQUIRED_PHP_VERSION, PHP_VERSION ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Returns the required checks based on the request type
	 *
	 * @since 1.0
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}

		return false;
	}

	/**
	 * Displays an error message indicating the WooCommerce API Manager will remain disabled until WooCommerce
	 * has been upgraded to the required minimum version.
	 *
	 * @since 2.0
	 */
	public static function upgrade_wc_am_warning() {

		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( sprintf( __( 'The WooCommerce API Manager requires WooCommerce version %1$s or greater, but your server has WooCommerce version %2$s installed. The WooCommerce API Manager will remain disabled until WooCommerce has been upgraded to version %3$s or greater.', 'woocommerce-api-manager' ), WC_AM_WC_MIN_REQUIRED_VERSION, get_option( 'woocommerce_version' ), WC_AM_WC_MIN_REQUIRED_VERSION ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Displays an error message indicating the WooCommerce API Manager will remain disabled until WooCommerce Subscriptions
	 * has been upgraded to the required minimum version.
	 *
	 * @since 2.0.13
	 */
	public static function upgrade_wc_sub_am_warning() {
		$wam_wc_subs_active_version = class_exists( 'WC_Subscriptions' ) ? WC_Subscriptions::$version : get_option( 'woocommerce_subscriptions_active_version' );

		if ( empty( $wam_wc_subs_active_version ) ) {
			$wam_wc_subs_active_version = class_exists( 'WC_Subscriptions_Core_Plugin' ) ? WC_subscriptions_Core_Plugin::instance()->get_plugin_version() : '';
		}

		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( sprintf( __( 'The WooCommerce API Manager requires WooCommerce Subscriptions version %1$s or greater, but your server has WooCommerce Subscriptions version %2$s installed. Please upgrade WooCommerce Subscriptions to version %3$s or greater.', 'woocommerce-api-manager' ), WC_AM_WC_SUBS_MIN_REQUIRED_VERSION, $wam_wc_subs_active_version, WC_AM_WC_SUBS_MIN_REQUIRED_VERSION ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Fires at the end of the update message container in each
	 * row of the plugins list table.
	 *
	 * The dynamic portion of the hook name, `$file`, refers to the path
	 * of the plugin's primary file relative to the plugins directory.
	 *
	 * @see   /wp-admin/includes/update.php
	 *
	 * @since 2.0
	 *
	 * @param array $plugin_data {
	 *                           An array of plugin metadata.
	 *                           Information about the plugin.
	 *
	 * @type string $name        The human-readable name of the plugin.
	 * @type string $plugin_uri  Plugin URI.
	 * @type string $version     Plugin version.
	 * @type string $description Plugin description.
	 * @type string $author      Plugin author.
	 * @type string $author_uri  Plugin author URI.
	 * @type string $text_domain Plugin text domain.
	 * @type string $domain_path Relative path to the plugin's .mo file(s).
	 * @type bool   $network     Whether the plugin can only be activated network wide.
	 * @type string $title       The human-readable title of the plugin.
	 * @type string $author_name Plugin author's name.
	 * @type bool   $update      Whether there's an available update. Default null.
	 *                           }
	 *
	 * @param array $response    {
	 *                           An array of metadata about the available plugin update.
	 *                           Response from the server about the new version.
	 *
	 * @type int    $id          Plugin ID.
	 * @type string $slug        Plugin slug.
	 * @type string $new_version New plugin version.
	 * @type string $url         Plugin URL.
	 * @type string $package     Plugin update package URL.
	 *                           }
	 */
	public function in_plugin_update_message( $plugin_data, $response ) {

		// Bail if the update notice is not relevant, i.e. new version is not yet 2.0, or we're already on 2.0.
		if ( version_compare( '2.0.0', $plugin_data['new_version'], '>' ) || version_compare( '2.0.0', $plugin_data['Version'], '<=' ) ) {
			return;
		}

		$update_notice = '<div class="wc_plugin_upgrade_notice">';
		// translators: placeholders are opening and closing tags. Leads to docs on version 2
		$update_notice .= sprintf( __( 'Warning! Version 2.0 is a major update to the WooCommerce API Manager extension. Before updating, please create a backup, update all WooCommerce extensions, and test all plugins and custom code with version 2.0 on a staging site. %1$sLearn more about the changes in version 2.0 &raquo;%2$s', 'woocommerce-api-manager' ), '<a href="https://docs.woocommerce.com/document/woocommerce-api-manager/">', '</a>' );
		$update_notice .= '</div> ';

		echo wp_kses_post( $update_notice );
	}

	/**
	 * Checks if a plugin is activated
	 *
	 * @since 1.1
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function is_plugin_active( $slug ) {
		$active_plugins = (array) get_option( 'active_plugins', [] );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', [] ) );
		}

		return in_array( $slug, $active_plugins ) || array_key_exists( $slug, $active_plugins );
	}

	/**
	 * Return true if pre argument version is older than the current version.
	 *
	 * @since 1.4.4
	 *
	 * @param string $version
	 *
	 * @return bool
	 */
	public function is_woocommerce_pre( $version ) {
		return ! empty( $this->get_wc_version() ) && version_compare( $this->get_wc_version(), $version, '<' );
	}

	/**
	 * Is WooCommerce Subscriptions plugin active?
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	public function is_wc_subscriptions_active() {
		/**
		 * A plugin can be removed without using the Plugins screen, so it remains listed as active, but the root plugin class will not exist.
		 */
		return $this->is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' );
	}

	/**
	 * Is the WooCommerce Subscriptions plugin active?
	 *
	 * @since  2.0.15
	 * @access static
	 *
	 * @return bool
	 */
	public static function is_wc_subscriptions_active_static() {
		$slug           = 'woocommerce-subscriptions/woocommerce-subscriptions.php';
		$active_plugins = (array) get_option( 'active_plugins', [] );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', [] ) );
		}

		return in_array( $slug, $active_plugins ) || array_key_exists( $slug, $active_plugins );
	}

	/**
	 * Is the WooCommerce plugin active?
	 *
	 * @since  3.0
	 * @access static
	 *
	 * @return bool
	 */
	public static function is_woocommerce_active_static() {
		$slug           = 'woocommerce/woocommerce.php';
		$active_plugins = (array) get_option( 'active_plugins', [] );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', [] ) );
		}

		return in_array( $slug, $active_plugins ) || array_key_exists( $slug, $active_plugins );
	}

	/**
	 * Gets an object's admin page screen ID in a WooCommerce version compatible way.
	 *
	 * @since 2.5
	 *
	 * @param string $object_type The object type. eg 'shop_order', 'shop_subscription'.
	 *
	 * @return string The page screen ID. On CPT stores, the screen ID is equal to the post type. On HPOS, the screen ID is generated by WooCommerce and fetched via wc_get_page_screen_id().
	 */
	public function get_wc_page_screen_id( $object_type ) {
		return $this->is_woocommerce_pre( '7.3.0' ) ? $object_type : wc_get_page_screen_id( $object_type );
	}

	/**
	 * Determines whether custom order tables usage is enabled.
	 *
	 * Custom order table feature can be enabled but the store is still using WP posts as the authoriative source of order data, therefore this function will only return true if:
	 *  - the HPOS feature is enabled
	 *  - the HPOS tables have been generated
	 *  - HPOS is the authoriative source of order data
	 *
	 * @since 2.5
	 *
	 * @return bool
	 */
	public function is_custom_order_tables_usage_enabled() {
		return $this->is_wc_custom_order_tables_usage_enabled;
	}

} // End class

/**
 * Returns the main instance of WooCommerce_API_Manager to prevent the need to use globals.
 *
 * @since  1.3
 * @return WooCommerce_API_Manager
 */
function WCAM() {
	return WooCommerce_API_Manager::instance();
}

// Initialize the class instance only once
WCAM();
