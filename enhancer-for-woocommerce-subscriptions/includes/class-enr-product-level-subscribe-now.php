<?php

/**
 * Product level subscribe now handler.
 *
 * @class ENR_Product_Level_Subscribe_Now
 * @package Class
 */
class ENR_Product_Level_Subscribe_Now extends ENR_Abstract_Subscribe_Now {

	/**
	 * The single instance of the class.
	 *
	 * @var ENR_Product_Level_Subscribe_Now|null
	 */
	protected static $instance = null;

	/**
	 * Gets the main ENR_Product_Level_Subscribe_Now Instance.
	 *
	 * @return ENR_Product_Level_Subscribe_Now Main instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_filter( 'woocommerce_is_subscription', array( self::$instance, 'enable_as_subscription' ), 20, 3 );
			add_filter( 'woocommerce_add_cart_item_data', array( self::$instance, 'add_subscribe_type' ), 20, 3 );
			add_action( 'woocommerce_add_to_cart', array( self::$instance, 'init_subscription_from_session_in_cart' ), 18, 0 );
			add_action( 'woocommerce_cart_loaded_from_session', array( self::$instance, 'load_subscription_from_session_in_cart' ), 4 );
			add_action( 'woocommerce_before_calculate_totals', array( self::$instance, 'load_subscription_from_session_in_cart' ), 4 );
			add_action( 'woocommerce_before_calculate_totals', array( self::$instance, 'add_price_calculation_filter' ), -1 );
			add_action( 'woocommerce_before_product_object_save', array( self::$instance, 'destroy_subscription_cache' ) );
			add_filter( 'woocommerce_available_variation', array( self::$instance, 'add_variation_data' ), 10, 3 );
			add_action( 'wp_loaded', array( self::$instance, 'add_actions_to_render_subscribe_form' ) );

			add_filter( 'wcs_is_product_switchable', array( self::$instance, 'is_product_switchable' ), 100, 2 );
			add_filter( 'woocommerce_subscriptions_can_item_be_switched', array( self::$instance, 'can_switch_item' ), 5, 3 );
			add_filter( 'woocommerce_subscriptions_switch_is_identical_product', array( self::$instance, 'is_identical_product' ), 100, 5 );

			add_action( 'woocommerce_subscription_cart_before_grouping', array( self::$instance, 'fix_trial_in_cart' ), 9 );
			add_filter( 'wcs_recurring_cart_start_date', array( self::$instance, 'fix_trial_in_cart' ), -1 );
			add_filter( 'woocommerce_subscriptions_calculated_total', array( self::$instance, 'fix_trial_in_cart' ), 999 );
		}

		return self::$instance;
	}

	/**
	 * Get the subscribe now type.
	 * 
	 * @return string
	 */
	public function get_type() {
		return 'product_level';
	}

	/**
	 * Check whether the site admin allowed their users to subscribe?
	 * 
	 * @param null|WC_Product $product
	 * @return bool
	 */
	public function enabled( $product = null ) {
		$enabled = false;

		if ( ! $this->is_subscription_product_type( $product ) && $this->is_supported_product_type( $product ) ) {
			$enabled = 'yes' === get_post_meta( $product->get_id(), ENR_PREFIX . 'allow_subscribe_now', true ) ? true : false;
		}

		/**
		 * Is subscribe now enabled on product?
		 * 
		 * @param bool $enabled
		 * @param ENR_Product_Level_Subscribe_Now $this
		 * @param WC_Product $product
		 * @since 1.0
		 */
		return apply_filters( 'enr_subscribe_now_enabled', $enabled, $this, $product );
	}

	/**
	 * Is available to subscribe now ?
	 * 
	 * @param null|WC_Product $product
	 * @return bool
	 */
	public function is_available( $product = null ) {
		/**
		 * Is subscribe now available to product?
		 * 
		 * @param bool $enabled
		 * @param ENR_Product_Level_Subscribe_Now $this
		 * @param WC_Product $product
		 * @since 1.0
		 */
		return apply_filters( 'enr_is_available_to_subscribe_now', $this->enabled( $product ), $this, $product );
	}

	/**
	 * Check whether user is subscribed.
	 * 
	 * @param null|WC_Product $product
	 * @return bool
	 */
	public function is_subscribed( $product = null ) {
		$subscribed = false;

		if ( is_object( $product ) && $this->is_available( $product ) && $this->get_type() === $product->get_meta( '_enr_subscribed_type', true, 'edit' ) ) {
			$subscribed = true;
		}

		return $subscribed;
	}

	/**
	 * Delete the data from a special array.
	 *
	 * @param string $key key for the prop.
	 */
	public function delete_data( $key = null ) {
		unset( $this->data[ $key ] );
	}

	/**
	 * Get the subscribe now form.
	 */
	public function get_subscribe_form( $wrapper = true, $echo = true, $product = null ) {
		ob_start();

		if ( $wrapper ) {
			echo '<span class="enr-' . esc_attr( $this->get_type() ) . '-subscribe-now-wrapper">';
		}

		_enr_get_template( 'html-subscribe-now-form.php', $this->get_subscribe_form_args( $product ) );

		if ( $wrapper ) {
			echo '</span>';
		}

		if ( $echo ) {
			ob_end_flush();
		} else {
			return ob_get_clean();
		}
	}

	/**
	 * Return the array of subscribe now form args.
	 * 
	 * @param mixed $product
	 * @return array
	 */
	public function get_subscribe_form_args( $product = null ) {
		if ( ! is_object( $product ) || ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product );
		}

		$args = parent::get_subscribe_form_args( $product->get_id() );

		if ( ! empty( $this->data[ $product->get_id() ] ) ) {
			$this->add_price_calculation_filter();
			$this->add_subscription_cache( $product, $this->data[ $product->get_id() ] );
			$args[ 'subscribed_price_string' ] = WC_Subscriptions_Product::get_price_string( $product, array( 'price' => wc_price( $product->get_price() ) ) );
			$this->destroy_subscription_cache( $product );
			$this->remove_price_calculation_filter();
		}

		$args[ 'subscribed_key' ]  = $product->get_id();
		$args[ 'force_subscribe' ] = _enr_is_switch_request();
		$args[ 'available_plans' ] = $this->validate_plans( get_post_meta( $product->get_id(), ENR_PREFIX . 'subscription_plans', true ) );
		$args[ 'default_plan' ]    = ! empty( $args[ 'available_plans' ] ) ? current( $args[ 'available_plans' ] ) : 0;

		/**
		 * Get the form args.
		 * 
		 * @param array $args
		 * @param ENR_Product_Level_Subscribe_Now $this
		 * @since 1.0
		 */
		return ( array ) apply_filters( 'enr_product_level_subscribe_now_form_args', $args, $this );
	}

	/**
	 * Read posted data.
	 *
	 * @param array $posted Values of the prop.
	 * @param mixed $product
	 */
	public function read_posted_data( $posted, $product = null ) {
		if ( ! is_object( $product ) || ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product );
		}

		if ( ! $product ) {
			return;
		}

		if ( ! $this->is_available( $product ) || empty( $posted[ 'enr_subscribe_now_type_nonce' ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $posted[ 'enr_subscribe_now_type_nonce' ] ) ), $this->get_type() ) ) {
			$this->delete_data( $product->get_id() );
			return;
		}

		parent::read_posted_data( $posted, $product->get_id() );
	}

	/**
	 * Enable the non WCS product as subscription.
	 * 
	 * @param bool $is_subscription
	 * @param int $product_id
	 * @param WC_Product $product
	 * @return bool
	 */
	public function enable_as_subscription( $is_subscription, $product_id = null, $product = null ) {
		if ( ! $product ) {
			return $is_subscription;
		}

		if ( $this->is_subscribed( $product ) ) {
			return true;
		}

		// Is switch in progress ?
		if ( _enr_is_switch_request() ) {
			if ( isset( $_REQUEST[ 'add-to-cart' ] ) && is_numeric( $_REQUEST[ 'add-to-cart' ] ) ) {
				/**
				 * Get the add to cart product ID.
				 * 
				 * @since 1.0
				 */
				$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( wp_unslash( $_REQUEST[ 'add-to-cart' ] ) ) );
				$product    = wc_get_product(  ! empty( $_REQUEST[ 'variation_id' ] ) ? absint( wp_unslash( $_REQUEST[ 'variation_id' ] ) ) : $product_id );
			}

			$this->read_posted_data( $_REQUEST, $product );

			if ( 'yes' === $this->get_prop( 'subscribed', null, $product->get_id() ) && ENR_Subscription_Plan::exists( $this->get_prop( 'subscribed_plan', null, $product->get_id() ) ) ) {
				$this->add_subscription_cache( $product, $this->data[ $product->get_id() ] );
				$is_subscription = $this->is_subscribed( $product );
				$this->destroy_subscription_cache( $product );
			}
		}

		return $is_subscription;
	}

	/**
	 * Add subscribe type to the cart item data.
	 * 
	 * @param array $cart_item_data
	 * @param int $product_id
	 * @param int $variation_id
	 * @return array
	 */
	public function add_subscribe_type( $cart_item_data, $product_id, $variation_id ) {
		$product = wc_get_product( $variation_id > 0 ? $variation_id : $product_id );
		$this->read_posted_data( $_REQUEST, $product );

		if ( $product && 'yes' === $this->get_prop( 'subscribed', null, $product->get_id() ) && ENR_Subscription_Plan::exists( $this->get_prop( 'subscribed_plan', null, $product->get_id() ) ) ) {
			$cart_item_data[ 'enr_subscribed' ] = array_merge( array(
				'type' => $this->get_type(),
					), $this->data[ $product->get_id() ] );
		}

		return $cart_item_data;
	}

	/**
	 * Load the subscription from session in cart.
	 * 
	 * @param WC_Cart $cart
	 */
	public function load_subscription_from_session_in_cart( $cart ) {
		foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
			if ( empty( $cart_item[ 'enr_subscribed' ] ) ) {
				continue;
			}

			if ( ! $this->is_available( $cart->cart_contents[ $cart_item_key ][ 'data' ] ) || isset( $cart_item[ 'subscription_renewal' ] ) || isset( $cart_item[ 'subscription_initial_payment' ] ) || isset( $cart_item[ 'subscription_resubscribe' ] ) ) {
				$this->destroy_subscription_cache( $cart->cart_contents[ $cart_item_key ][ 'data' ] );
				continue;
			}

			if ( ! ENR_Subscription_Plan::exists( $cart_item[ 'enr_subscribed' ][ 'subscribed_plan' ] ) ) {
				$this->destroy_subscription_cache( $cart->cart_contents[ $cart_item_key ][ 'data' ] );
				continue;
			}

			$this->add_subscription_cache( $cart->cart_contents[ $cart_item_key ][ 'data' ], $cart_item[ 'enr_subscribed' ] );
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
	 * Add the variation data on demand which will used upon selecting each variation.
	 * 
	 * @param array $variation_data
	 * @param WC_Product_Variable $variable
	 * @param WC_Product_Variation $variation
	 * @return array
	 */
	public function add_variation_data( $variation_data, $variable, $variation ) {
		if ( $this->is_available( $variation ) ) {
			$variation_data[ 'enr_subscribe_now_form' ]      = $this->get_subscribe_form( true, false, $variation );
			$variation_data[ 'enr_single_add_to_cart_text' ] = $variation->single_add_to_cart_text();
		}

		return $variation_data;
	}

	/**
	 * WP add_actions to render subscribe now form.
	 */
	public function add_actions_to_render_subscribe_form() {
		add_action( 'woocommerce_before_add_to_cart_button', array( self::$instance, 'maybe_render_subscribe_form' ) );
	}

	/**
	 * Maybe render the subscribe now form.
	 */
	public function maybe_render_subscribe_form() {
		global $product, $post;

		if ( ! is_callable( array( $product, 'is_type' ) ) ) {
			if ( ! isset( $post->ID ) ) {
				return;
			}

			$product = wc_get_product( $post->ID );

			if ( ! $product ) {
				return;
			}
		}

		if ( $this->is_available( $product ) ) {
			$this->get_subscribe_form( true, true, $product );
		}
	}

	/**
	 * Allow the non WCS product to be switchable.
	 *
	 * @param  boolean     $is_switchable
	 * @param  WC_Product  $product
	 * @return boolean
	 */
	public function is_product_switchable( $is_switchable, $product ) {
		if ( empty( $product ) || ! ( $product instanceof WC_Product ) ) {
			return $is_switchable;
		}

		if ( ! _enr_allowed_switching_btw_plans() ) {
			return $is_switchable;
		}

		if ( ! $is_switchable ) {
			$is_switchable = $this->is_available( $product );
		}

		if ( ! $is_switchable && $product->get_parent_id() > 0 ) {
			$parent_product = wc_get_product( $product->get_parent_id() );

			if ( $parent_product ) {
				$child_ids = $parent_product->get_children();

				foreach ( $child_ids as $child_id ) {
					$child_product = wc_get_product( $child_id );

					if ( $this->is_available( $child_product ) ) {
						$is_switchable = true;
						break;
					}
				}
			}
		}

		return $is_switchable;
	}

	/**
	 * Do not allow switching to the non supported subscription item.
	 *
	 * @param  boolean          $can
	 * @param  WC_Order_Item    $item
	 * @param  WC_Subscription  $subscription
	 * @return boolean
	 */
	public function can_switch_item( $can, $item, $subscription ) {
		if ( ! _enr_allowed_switching_btw_plans() ) {
			return $can;
		}

		$allowed_to_subscribe_items_count = 0;

		foreach ( $subscription->get_items() as $item ) {
			$product = $item->get_product();

			if ( $product && $this->is_supported_product_type( $product ) ) {
				$allowed_to_subscribe_items_count++;
			}
		}

		if ( $allowed_to_subscribe_items_count > 1 ) {
			$can = false;
		}

		return $can;
	}

	/**
	 * Check whether the non WCS product is identical?
	 *
	 * @param  boolean         $is_identical
	 * @param  int             $product_id
	 * @param  int             $quantity
	 * @param  int             $variation_id
	 * @param  WC_Subscription $subscription
	 * @return boolean
	 */
	public function is_identical_product( $is_identical, $product_id, $quantity, $variation_id, $subscription ) {
		if ( ! _enr_allowed_switching_btw_plans() ) {
			return $is_identical;
		}

		if ( $is_identical ) {
			$product = wc_get_product( $variation_id > 0 ? $variation_id : $product_id );
			$this->read_posted_data( $_REQUEST, $product );

			if ( $product && 'yes' === $this->get_prop( 'subscribed', null, $product->get_id() ) && ENR_Subscription_Plan::exists( $this->get_prop( 'subscribed_plan', null, $product->get_id() ) ) ) {
				$is_identical = ( $subscription->get_billing_period() === $this->get_prop( 'subscribed_period', null, $product->get_id() ) && $subscription->get_billing_interval() == $this->get_prop( 'subscribed_period_interval', null, $product->get_id() ) ) ? true : false;
			}
		}

		return $is_identical;
	}

	/**
	 * Force subscribe based upon the current request.
	 * 
	 * @param array $args
	 * @return array
	 */
	public function maybe_force_subscribe( $args ) {
		$args[ 'force_subscribe' ] = _enr_is_switch_request() || ( isset( $_POST[ 'security' ], $_POST[ 'is_switch_request' ] ) && wp_verify_nonce( wc_clean( wp_unslash( $_POST[ 'security' ] ) ), 'enr-subscribe-now-handle' ) && wc_string_to_bool( wc_clean( $_POST[ 'is_switch_request' ] ) ) ) ? true : false;
		return $args;
	}

	/**
	 * Fix trial not set in cart.
	 */
	public function fix_trial_in_cart( $total = '' ) {
		remove_action( 'woocommerce_subscription_cart_before_grouping', 'WC_Subscriptions_Synchroniser::maybe_unset_free_trial' );
		remove_filter( 'wcs_recurring_cart_start_date', 'WC_Subscriptions_Synchroniser::maybe_unset_free_trial', 0, 1 );
		remove_filter( 'woocommerce_subscriptions_calculated_total', 'WC_Subscriptions_Synchroniser::maybe_unset_free_trial', 10000, 1 );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			// Dont override trial length set while resubscribing, unless proration is disabled.
			if ( WC_Subscriptions_Synchroniser::is_product_synced( $cart_item[ 'data' ] ) && ( ! isset( $cart_item[ 'subscription_resubscribe' ] ) || ! WC_Subscriptions_Synchroniser::is_sync_proration_enabled() ) ) {

				// When reinstating the trial length, set resubscribes trial length to 0 so we don't grant a second trial period.
				if ( isset( $cart_item[ 'subscription_resubscribe' ] ) ) {
					$trial_length = 0;
				} elseif ( $cart_item[ 'data' ]->get_meta( '_enr_subscribed_plan', true, 'edit' ) ) {
					$trial_length = WC_Subscriptions_Product::get_trial_length( $cart_item[ 'data' ] );
				} else {
					$trial_length = WC_Subscriptions_Product::get_trial_length( wcs_get_canonical_product_id( $cart_item ) );
				}

				wcs_set_objects_property( WC()->cart->cart_contents[ $cart_item_key ][ 'data' ], 'subscription_trial_length', $trial_length, 'set_prop_only' );
			}
		}

		return $total;
	}
}

ENR_Product_Level_Subscribe_Now::instance();
