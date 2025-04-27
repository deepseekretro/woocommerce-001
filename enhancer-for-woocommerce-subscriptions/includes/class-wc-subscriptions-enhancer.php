<?php

defined( 'ABSPATH' ) || exit;

/**
 * Initiate Plugin Core class.
 * 
 * @class WC_Subscriptions_Enhancer
 * @package Class
 */
final class WC_Subscriptions_Enhancer {

	/**
	 * Plugin version.
	 */
	const VERSION = '4.7.0';

	/**
	 * Required WC version.
	 */
	const REQ_WC_VERSION = '3.5.0';

	/**
	 * Required WC Subscriptions version.
	 */
	const REQ_WCS_VERSION = '3.0.1';

	/**
	 * The single instance of the class.
	 */
	protected static $instance = null;

	/**
	 * WC_Subscriptions_Enhancer constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'admin_notices', array( $this, 'plugin_dependencies_notice' ) );
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'enhancer-for-woocommerce-subscriptions' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'enhancer-for-woocommerce-subscriptions' ), '1.0' );
	}

	/**
	 * Main WC_Subscriptions_Enhancer Instance.
	 * Ensures only one instance of WC_Subscriptions_Enhancer is loaded or can be loaded.
	 * 
	 * @return WC_Subscriptions_Enhancer - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get plugin version.
	 * 
	 * @return string
	 */
	public function get_version() {
		return self::VERSION;
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return ENR_DIR . 'templates/';
	}

	/**
	 * Check whether the plugin dependencies met.
	 * 
	 * @return bool|string True on Success
	 */
	private function plugin_dependencies_met() {
		// WC Subscriptions check.
		if ( ! class_exists( 'WC_Subscriptions' ) || version_compare( WC_Subscriptions::$version, self::REQ_WCS_VERSION, '<' ) ) {
			if ( ! class_exists( 'WC_Subscriptions' ) ) {
				return wp_kses_post( __( '<strong>Enhancer for WooCommerce Subscriptions is inactive.</strong> The <a href="http://woocommerce.com/products/woocommerce-subscriptions/" target="_blank">WooCommerce Subscriptions plugin</a> must be active for Enhancer for WooCommerce Subscriptions to work. Please install & activate WooCommerce Subscriptions.', 'enhancer-for-woocommerce-subscriptions' ) );
			} else {
				// translators: %s: required WCS version
				return sprintf( wp_kses_post( __( '<strong>Enhancer for WooCommerce Subscriptions is inactive.</strong> <a href="http://woocommerce.com/products/woocommerce-subscriptions/" target="_blank">WooCommerce Subscriptions</a> plugin version <strong>%s</strong> or higher must be active for Enhancer for WooCommerce Subscriptions to work. Please update WooCommerce Subscriptions plugin and check.', 'enhancer-for-woocommerce-subscriptions' ) ), self::REQ_WCS_VERSION );
			}
		}

		if ( version_compare( get_option( 'woocommerce_db_version' ), WC_Subscriptions::$wc_minimum_supported_version, '<' ) ) {
			return false;
		}

		// WC check.
		if ( ! function_exists( 'WC' ) ) {
			$install_url = wp_nonce_url( add_query_arg( array( 'action' => 'install-plugin', 'plugin' => 'woocommerce' ), admin_url( 'update.php' ) ), 'install-plugin_woocommerce' );
			// translators: 1$-2$: opening and closing <strong> tags, 3$-4$: link tags, takes to woocommerce plugin on wp.org, 5$-6$: opening and closing link tags, leads to plugins.php in admin
			return sprintf( esc_html__( '%1$sEnhancer for WooCommerce Subscriptions is inactive.%2$s The %3$sWooCommerce plugin%4$s must be active for Enhancer for WooCommerce Subscriptions to work. Please %5$sinstall & activate WooCommerce &raquo;%6$s', 'enhancer-for-woocommerce-subscriptions' ), '<strong>', '</strong>', '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . esc_url( $install_url ) . '">', '</a>' );
		}

		return true;
	}

	/**
	 * When WP has loaded all plugins, check whether the plugin is compatible with the present environment and load our files.
	 */
	public function plugins_loaded() {
		if ( true !== $this->plugin_dependencies_met() ) {
			return;
		}

		$this->define_constants();
		$this->include_files();
		$this->init_hooks();
		$this->other_plugin_support_includes();

		/**
		 * Trigger after the plugin is loaded.
		 * 
		 * @since 1.0
		 */
		do_action( 'enr_loaded' );
	}

	/**
	 * Output a admin notice when plugin dependencies not met.
	 */
	public function plugin_dependencies_notice() {
		$return = $this->plugin_dependencies_met();

		if ( true !== $return && $return && current_user_can( 'activate_plugins' ) ) {
			$dependency_notice = $return;
			printf( '<div class="error"><p>%s</p></div>', wp_kses_post( $dependency_notice ) );
		}
	}

	/**
	 * Define constants.
	 */
	private function define_constants() {
		$this->define( 'ENR_ABSPATH', dirname( ENR_FILE ) . '/' );
		$this->define( 'ENR_DIR', plugin_dir_path( ENR_FILE ) );
		$this->define( 'ENR_URL', untrailingslashit( plugins_url( '/', ENR_FILE ) ) );
		$this->define( 'ENR_PREFIX', '_enr_' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Is frontend request ?
	 *
	 * @return bool
	 */
	private function is_frontend() {
		if ( function_exists( 'wcs_is_frontend_request' ) && function_exists( 'wcs_is_checkout_blocks_api_request' ) ) {
			return wcs_is_frontend_request() || wcs_is_checkout_blocks_api_request();
		} else if ( function_exists( 'wcs_is_frontend_request' ) ) {
			return wcs_is_frontend_request();
		}

		return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
	}

	/**
	 * Include required core files.
	 */
	private function include_files() {
		//Class autoloader.
		include_once 'class-enr-autoload.php';

		//Abstract classes.
		include_once 'abstracts/abstract-enr-subscribe-now.php';

		//Core functions.
		include_once 'enr-core-functions.php';

		//Core classes.
		include_once 'class-enr-post-types.php';
		include_once 'class-enr-install.php';
		include_once 'class-enr-emails.php';
		include_once 'class-enr-ajax.php';
		include_once 'privacy/class-enr-privacy.php';
		include_once 'class-enr-subscriptions-manager.php';
		include_once 'class-enr-blocks-compatibility.php';

		if ( is_admin() ) {
			include_once 'admin/class-enr-admin.php';
			include_once 'admin/class-enr-admin-post-types.php';
		}

		if ( $this->is_frontend() ) {
			include_once 'class-enr-subscriptions-limiter.php';
			include_once 'class-enr-cart-level-subscribe-now.php';
			include_once 'class-enr-product-level-subscribe-now.php';
			include_once 'class-enr-form-handler.php';
			include_once 'enr-template-hooks.php';
		}
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		register_activation_hook( ENR_FILE, array( 'ENR_Install', 'install' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_script' ), 11 );
		add_action( 'woocommerce_settings_saved', array( $this, 'init_background_process' ), 20 );
		add_action( 'woocommerce_process_product_meta', array( $this, 'init_background_process' ), 20, 1 );
		add_action( 'enr_update_old_subscriptions', array( $this, 'update_old_subscriptions' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Init WC_Subscriptions_Enhancer when WordPress Initializes. 
	 */
	public function init() {
		/**
		 * Init before the plugin.
		 * 
		 * @since 4.6.0
		 */
		do_action( 'before_enr_init' );

		$this->load_plugin_textdomain();

		/**
		 * Init the plugin.
		 * 
		 * @since 4.6.0
		 */
		do_action( 'enr_init' );
	}

	/**
	 * Load Localization files.
	 */
	public function load_plugin_textdomain() {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			$locale = is_admin() ? get_user_locale() : get_locale();
		}

		/**
		 * Get the plugin text domain.
		 * 
		 * @since 1.0 
		 */
		$locale = apply_filters( 'plugin_locale', $locale, 'enhancer-for-woocommerce-subscriptions' );

		unload_textdomain( 'enhancer-for-woocommerce-subscriptions' );
		load_textdomain( 'enhancer-for-woocommerce-subscriptions', WP_LANG_DIR . '/enhancer-for-woocommerce-subscriptions/enhancer-for-woocommerce-subscriptions-' . $locale . '.mo' );
		load_textdomain( 'enhancer-for-woocommerce-subscriptions', WP_LANG_DIR . '/plugins/enhancer-for-woocommerce-subscriptions-' . $locale . '.mo' );
		load_plugin_textdomain( 'enhancer-for-woocommerce-subscriptions', false, dirname( plugin_basename( ENR_FILE ) ) . '/languages' );
	}

	/**
	 * Perform script localization in frontend.
	 */
	public function frontend_script() {
		global $wp, $post;

		$product_id = $post && 'product' === get_post_type( $post ) ? $post->ID : false;
		$product    = ! empty( $product_id ) ? wc_get_product( $product_id ) : false;

		wp_register_script( 'enr-frontend', ENR_URL . '/assets/js/frontend.js', array( 'jquery' ), _enr()->get_version() );
		wp_register_style( 'enr-frontend', ENR_URL . '/assets/css/frontend.css', array(), _enr()->get_version() );
		wp_localize_script( 'enr-frontend', 'enr_frontend_params', array(
			'ajax_url'                     => admin_url( 'admin-ajax.php' ),
			'is_checkout'                  => is_checkout(),
			'is_user_logged_in'            => is_user_logged_in(),
			'is_switch_request'            => _enr_is_switch_request(),
			'cart_level_subscribed'        => _enr_cart_contains_subscribed_product( 'cart_level' ),
			'subscribe_now_nonce'          => wp_create_nonce( 'enr-subscribe-now-handle' ),
			'subscribe_now_button_text'    => get_option( 'woocommerce_subscriptions_add_to_cart_button_text' ),
			'single_add_to_cart_text'      => $product ? $product->single_add_to_cart_text() : __( 'Add to cart', 'enhancer-for-woocommerce-subscriptions' ),
			'hide_variable_limited_notice' => $product && 'no' !== ENR_Subscriptions_Limiter::get_product_limitation( $product ) && 'variant-level' === get_post_meta( $product_id, '_enr_variable_subscription_limit_level', true ) ? 'yes' : '',
		) );
		wp_enqueue_script( 'enr-frontend' );
		wp_enqueue_style( 'enr-frontend' );
	}

	/**
	 * Init the background process.
	 */
	public function init_background_process( $post_id = 0 ) {
		global $current_tab;

		if ( ( ! empty( $post_id ) || 'subscriptions' === $current_tab ) && ! wp_next_scheduled( 'enr_update_old_subscriptions' ) ) {
			wp_schedule_single_event( time() + 10, 'enr_update_old_subscriptions' );
		}
	}

	/**
	 * Run the background process of old subscriptions update.
	 */
	public function update_old_subscriptions() {
		$subscriptions = wcs_get_subscriptions( array(
			'subscriptions_per_page' => -1,
			'subscription_status'    => 'active',
			'return'                 => 'ids',
				) );

		if ( empty( $subscriptions ) ) {
			return;
		}

		foreach ( $subscriptions as $id => $subscription ) {
			$subscription = wcs_get_subscription( $subscription );

			if ( $subscription ) {
				ENR_Subscriptions_Manager::maybe_enable_for_old_subscription( $subscription );
				ENR_Shipping_Cycle::maybe_enable_for_old_subscription( $subscription );
				ENR_Subscriptions_Price_Update::maybe_enable_for_old_subscription( $subscription );
				ENR_Action_Scheduler::maybe_schedule_when_status_updated( $subscription, $subscription->get_status() );
			}
		}
	}

	/**
	 * Include classes for plugin support.
	 * 
	 * @since 4.5.0
	 */
	private function other_plugin_support_includes() {
		ENR_Compatibles::load();
	}
}
