<?php

/**
 * Cart level subscribe now handler.
 *
 * @class ENR_Cart_Level_Subscribe_Now
 * @package Class
 */
class ENR_Cart_Level_Subscribe_Now extends ENR_Abstract_Subscribe_Now {

	/**
	 * The single instance of the class.
	 *
	 * @var ENR_Cart_Level_Subscribe_Now|null
	 */
	protected static $instance = null;

	/**
	 * Gets the main ENR_Cart_Level_Subscribe_Now Instance.
	 *
	 * @return ENR_Cart_Level_Subscribe_Now Main instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_filter( 'woocommerce_is_subscription', array( self::$instance, 'enable_as_subscription' ), 20, 3 );
			add_action( 'woocommerce_add_to_cart', array( self::$instance, 'init_subscription_from_session_in_cart' ), 19, 0 );
			add_action( 'woocommerce_cart_loaded_from_session', array( self::$instance, 'load_subscription_from_session_in_cart' ), 5 );
			add_action( 'woocommerce_before_calculate_totals', array( self::$instance, 'load_subscription_from_session_in_cart' ), 5 );
			add_action( 'woocommerce_before_calculate_totals', array( self::$instance, 'add_price_calculation_filter' ), -1 );
			add_action( 'woocommerce_before_product_object_save', array( self::$instance, 'destroy_subscription_cache' ) );
			add_action( 'wp_loaded', array( self::$instance, 'add_actions_to_render_subscribe_form' ) );
		}

		return self::$instance;
	}

	/**
	 * Get the subscribe now type.
	 * 
	 * @return string
	 */
	public function get_type() {
		return 'cart_level';
	}

	/**
	 * Check whether the site admin allowed their users to subscribe?
	 * 
	 * @return bool
	 */
	public function enabled() {
		/**
		 * Is subscribe now enabled on cart?
		 * 
		 * @param bool $enabled
		 * @param ENR_Cart_Level_Subscribe_Now $this
		 * @since 1.0
		 */
		return apply_filters( 'enr_subscribe_now_enabled', ( 'yes' === get_option( ENR_PREFIX . 'allow_cart_level_subscribe_now' ) ? true : false ), $this );
	}

	/**
	 * Is available to subscribe now ?
	 * 
	 * @param WC_Cart|null $cart
	 * @return bool
	 */
	public function is_available( $cart = null ) {
		$available = $this->enabled();

		if ( ! is_null( $cart ) && is_a( $cart, 'WC_Cart' ) && ! empty( $cart->cart_contents ) ) {
			foreach ( $cart->cart_contents as $cart_item ) {
				$subscribed_type = $cart_item[ 'data' ]->get_meta( '_enr_subscribed_type', true, 'edit' );

				if ( ! empty( $subscribed_type ) && $this->get_type() !== $subscribed_type ) {
					$available = false;
					break;
				}

				if ( $this->is_subscription_product_type( $cart_item[ 'data' ] ) ) {
					$available = false;
					break;
				}

				if ( ! $this->is_supported_product_type( $cart_item[ 'data' ] ) ) {
					$available = false;
					break;
				}

				if ( isset( $cart_item[ 'subscription_renewal' ] ) || isset( $cart_item[ 'subscription_initial_payment' ] ) || isset( $cart_item[ 'subscription_resubscribe' ] ) ) {
					$available = false;
					break;
				}
			}
		}

		/**
		 * Is subscribe now available to cart?
		 * 
		 * @param bool $enabled
		 * @param ENR_Cart_Level_Subscribe_Now $this
		 * @since 1.0
		 */
		return apply_filters( 'enr_is_available_to_subscribe_now', $available, $this, $cart );
	}

	/**
	 * Check whether user is subscribed.
	 * 
	 * @param null|WC_Product $product
	 * @return bool
	 */
	public function is_subscribed( $product = null ) {
		$subscribed = false;

		if ( is_object( $product ) && $this->is_available() && $this->get_type() === $product->get_meta( '_enr_subscribed_type', true, 'edit' ) ) {
			$subscribed = true;
		}

		return $subscribed;
	}

	/**
	 * Read data from the DB.
	 */
	public function read_data() {
		if ( WC()->session ) {
			$this->data = WC()->session->get( "enr_{$this->get_type()}_subscribed_data" );
		}
	}

	/**
	 * Save the data which is collected in to DB.
	 */
	public function save_data() {
		if ( WC()->session ) {
			WC()->session->set( "enr_{$this->get_type()}_subscribed_data", $this->data );
		}
	}

	/**
	 * Delete the data from DB.
	 */
	public function delete_data() {
		if ( WC()->session ) {
			WC()->session->__unset( "enr_{$this->get_type()}_subscribed_data" );
			$this->data = array();
		}
	}

	/**
	 * Return the array of subscribe now form args.
	 * 
	 * @return array
	 */
	public function get_subscribe_form_args( $key = null ) {
		$args = parent::get_subscribe_form_args( $key );

		$dummy_product                     = new WC_Product( 0 );
		$this->remove_price_calculation_filter();
		$this->add_subscription_cache( $dummy_product, $this->data );
		/* translators: 1: subscribed period and interval */
		$args[ 'subscribed_price_string' ] = sprintf( __( 'Billed %s', 'enhancer-for-woocommerce-subscriptions' ), WC_Subscriptions_Product::get_price_string( $dummy_product, array( 'price' => '', 'subscription_price' => false ) ) );
		$this->destroy_subscription_cache( $dummy_product );
		$this->add_price_calculation_filter();

		$args[ 'available_plans' ] = $this->validate_plans( get_option( ENR_PREFIX . 'cart_level_subscription_plans', array() ) );
		$args[ 'default_plan' ]    = ! empty( $args[ 'available_plans' ] ) ? current( $args[ 'available_plans' ] ) : 0;

		/**
		 * Get the form args.
		 * 
		 * @param array $args
		 * @param ENR_Cart_Level_Subscribe_Now $this
		 * @since 1.0
		 */
		return ( array ) apply_filters( 'enr_cart_level_subscribe_now_form_args', $args, $this );
	}

	/**
	 * Load the subscription from session in cart.
	 * 
	 * @param WC_Cart $cart
	 */
	public function load_subscription_from_session_in_cart( $cart ) {
		if ( ! $this->is_available( $cart ) ) {
			$this->delete_data();
			return;
		}

		$this->read_data();

		if ( 'yes' === $this->get_prop( 'subscribed' ) && ! ENR_Subscription_Plan::exists( $this->get_prop( 'subscribed_plan' ) ) ) {
			$this->delete_data();
		}

		if ( 'yes' === $this->get_prop( 'subscribed' ) ) {
			foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
				$this->add_subscription_cache( $cart->cart_contents[ $cart_item_key ][ 'data' ], $this->data );
			}
		} else {
			foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
				$this->destroy_subscription_cache( $cart->cart_contents[ $cart_item_key ][ 'data' ] );
			}
		}
	}

	/**
	 * Add the price filter dependent hooks. 
	 */
	public function add_price_calculation_filter() {
		add_filter( 'woocommerce_product_get_price', array( self::$instance, 'calculate_subscription_price' ), 99, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( self::$instance, 'calculate_subscription_price' ), 99, 2 );
	}

	/**
	 * Remove the price filter dependent hooks. 
	 */
	public function remove_price_calculation_filter() {
		remove_filter( 'woocommerce_product_get_price', array( self::$instance, 'calculate_subscription_price' ), 99 );
		remove_filter( 'woocommerce_product_variation_get_price', array( self::$instance, 'calculate_subscription_price' ), 99 );
	}

	/**
	 * WP add_actions to render subscribe now form.
	 */
	public function add_actions_to_render_subscribe_form() {
		$pages_to_render = get_option( ENR_PREFIX . 'page_to_display_cart_level_subscribe_now_form', 'cart' );

		if ( in_array( 'cart', ( array ) $pages_to_render ) ) {
			add_action( 'woocommerce_before_cart_totals', array( self::$instance, 'maybe_render_subscribe_form' ) );
		}

		if ( in_array( 'checkout', ( array ) $pages_to_render ) ) {
			$hook = sanitize_title( get_option( ENR_PREFIX . 'cart_level_subscribe_now_form_position_in_checkout_page', 'woocommerce_checkout_order_review' ) );
			add_action( $hook, array( self::$instance, 'maybe_render_subscribe_form' ), 5 );
		}
	}

	/**
	 * Maybe render the subscribe now form.
	 */
	public function maybe_render_subscribe_form() {
		if ( $this->is_available( WC()->cart ) ) {
			$this->get_subscribe_form();
		}
	}
}

ENR_Cart_Level_Subscribe_Now::instance();
