<?php

defined( 'ABSPATH' ) || exit;

/**
 * Abstract subscribe now handler.
 * 
 * @class ENR_Abstract_Subscribe_Now
 * @package Class
 */
abstract class ENR_Abstract_Subscribe_Now {

	/**
	 * Core data for the subscribed plan.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Default data for the subscribed plan.
	 *
	 * @var array
	 */
	protected $default_data = array(
		'subscribed'                          => false,
		'subscribed_plan'                     => null,
		'subscribed_price_percent'            => null,
		'subscribed_period'                   => null,
		'subscribed_period_interval'          => null,
		'subscribed_length'                   => null,
		'subscribed_trial_period'             => null,
		'subscribed_trial_length'             => null,
		'subscribed_sign_up_fee'              => null,
		'subscribed_payment_sync_date'        => null,
		'subscribed_for_shipping_cycle'       => null,
		'subscribed_shipping_period_interval' => null,
		'subscribed_shipping_period'          => null,
		'allowed_cancelling_to'               => null,
		'allowed_cancelling_after'            => null,
		'allowed_cancelling_after_due'        => null,
		'allowed_cancelling_before_due'       => null,
	);

	/**
	 * Get the subscribe now type.
	 * 
	 * @return string
	 */
	public function get_type() {
		return '';
	}

	/**
	 * Checks a product is supported by the plugin for subscribe now.
	 *
	 * @param  WC_Product  $product
	 * @return bool
	 */
	public function is_supported_product_type( $product ) {
		/**
		 * Get the subscribe now supported product types.
		 * 
		 * @param array $product_types
		 * @param ENR_Abstract_Subscribe_Now $this
		 * @since 1.0
		 */
		return $product && is_callable( array( $product, 'is_type' ) ) && $product->is_type( apply_filters( 'enr_subscribe_now_supported_product_types', array( 'simple', 'variable', 'variation' ), $this ) );
	}

	/**
	 * Checks a product is a WCS subscription-type product
	 *
	 * @param  WC_Product  $product
	 * @return bool
	 */
	public function is_subscription_product_type( $product ) {
		return $product && is_callable( array( $product, 'is_type' ) ) && $product->is_type( array( 'subscription', 'subscription_variation', 'variable-subscription' ) );
	}

	/**
	 * Check whether the site admin allowed their users to subscribe now ?
	 * 
	 * @return bool
	 */
	public function enabled() {
		/**
		 * Is subscribe now enabled?
		 * 
		 * @param bool $enabled
		 * @param ENR_Abstract_Subscribe_Now $this
		 * @since 1.0
		 */
		return apply_filters( 'enr_subscribe_now_enabled', false, $this );
	}

	/**
	 * Is available to subscribe now ?
	 * 
	 * @return bool
	 */
	public function is_available() {
		/**
		 * Is subscribe now available?
		 * 
		 * @param bool $enabled
		 * @param ENR_Abstract_Subscribe_Now $this
		 * @since 1.0
		 */
		return apply_filters( 'enr_is_available_to_subscribe_now', $this->enabled(), $this );
	}

	/**
	 * Check whether user is subscribed.
	 * 
	 * @return bool
	 */
	public function is_subscribed() {
		return false;
	}

	/**
	 * Validate the plans and return the array of available plans to use.
	 * 
	 * @param array|int $plans
	 * @return array
	 */
	public function validate_plans( $plans = array() ) {
		if ( empty( $plans ) ) {
			return array();
		}

		if ( ! is_array( $plans ) ) {
			$plans = ( array ) $plans;
		}

		foreach ( $plans as &$plan_id ) {
			/**
			 * WPML current language.
			 * 
			 * @since 1.0.0
			 */
			$plan_id = _enr_maybe_get_wpml_object_id( $plan_id, apply_filters( 'wpml_current_language', null ) );
		}

		return $this->sort_plans( array_filter( $plans, array( 'ENR_Subscription_Plan', 'exists' ) ) );
	}

	/**
	 * Sort the plans.
	 * 
	 * @param array|int $plan_ids
	 * @param string $order ASC|DESC
	 * @return array
	 */
	public function sort_plans( $plan_ids, $order = 'ASC' ) {
		global $wpdb;
		$_wpdb        = &$wpdb;
		$plan_ids     = is_array( $plan_ids ) ? $plan_ids : ( array ) $plan_ids;
		$orderby      = 'asc' === strtolower( $order ) ? ' ORDER BY menu_order ASC ' : ' ORDER BY menu_order DESC ';
		$sorted_plans = $_wpdb->get_col( $_wpdb->prepare( "SELECT ID FROM {$_wpdb->posts} WHERE 1=%d AND post_type = 'enr_subsc_plan' AND post_status = 'publish' AND ID IN ('" . implode( "','", $plan_ids ) . "') $orderby", 1 ) );
		return $sorted_plans;
	}

	/**
	 * Gets a prop from from either current pending changes, or the DB itself.
	 *
	 * @param string $prop Name of prop to get.
	 * @param mixed $default Default value for the prop.
	 * @param string $key Subscribed key for the prop.
	 * @return mixed
	 */
	public function get_prop( $prop, $default = null, $key = null ) {
		if ( is_null( $key ) || '' === $key ) {
			return isset( $this->data[ $prop ] ) ? $this->data[ $prop ] : $default;
		} else {
			return isset( $this->data[ $key ][ $prop ] ) ? $this->data[ $key ][ $prop ] : $default;
		}

		return null;
	}

	/**
	 * Sets a prop in a special array so we can track what needs saving the DB later.
	 *
	 * @param string $prop Name of prop to get.
	 * @param mixed  $value Value of the prop.
	 * @param string $key Subscribed key for the prop.
	 */
	public function set_prop( $prop, $value, $key = null ) {
		if ( is_null( $key ) || '' === $key ) {
			$this->data[ $prop ] = $value;
		} else {
			$this->data[ $key ][ $prop ] = $value;
		}
	}

	/**
	 * Read data from the DB.
	 */
	public function read_data() {
	}

	/**
	 * Save the data which is collected in to DB.
	 */
	public function save_data() {
	}

	/**
	 * Delete the data from DB.
	 */
	public function delete_data() {
	}

	/**
	 * Get the subscribe now form.
	 */
	public function get_subscribe_form( $wrapper = true, $echo = true, $key = null ) {
		$this->read_data();

		ob_start();

		if ( $wrapper ) {
			echo '<span class="enr-' . esc_attr( $this->get_type() ) . '-subscribe-now-wrapper">';
		}

		_enr_get_template( 'html-subscribe-now-form.php', $this->get_subscribe_form_args( $key ) );

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
	 * @return array
	 */
	public function get_subscribe_form_args( $key = null ) {
		$default_args = array(
			'subscribe_type'       => $this->get_type(),
			'subscribe_label'      => '',
			'subscribed_key'       => null,
			'is_subscribed'        => false,
			'force_subscribe'      => false,
			'chosen_plan'          => null,
			'chosen_interval'      => null,
			'chosen_period'        => null,
			'chosen_length'        => null,
			'subscribed_plan_type' => null,
			'available_plans'      => null,
			'default_plan'         => null,
		);

		/**
		 * Get the subscribe now args.
		 * 
		 * @param array $args
		 * @param ENR_Abstract_Subscribe_Now $this
		 * @since 1.0
		 */
		$args = wp_parse_args( ( array ) apply_filters( 'enr_subscribe_now_form_args', array(
					'subscribe_type'          => $this->get_type(),
					'subscribe_label'         => get_option( ENR_PREFIX . '_subscribe_now_checkbox_text', __( 'Subscribe Now', 'enhancer-for-woocommerce-subscriptions' ) ),
					'is_subscribed'           => 'yes' === $this->get_prop( 'subscribed', null, $key ),
					'chosen_plan'             => $this->get_prop( 'subscribed_plan', null, $key ),
					'subscribed_price_string' => '',
					'chosen_interval'         => $this->get_prop( 'subscribed_period_interval', ENR_Subscription_Plan::get_default_prop( 'subscription_period_interval' ), $key ),
					'chosen_period'           => $this->get_prop( 'subscribed_period', ENR_Subscription_Plan::get_default_prop( 'subscription_period' ), $key ),
					'chosen_length'           => $this->get_prop( 'subscribed_length', ENR_Subscription_Plan::get_default_prop( 'subscription_length' ), $key ),
					'subscribed_plan_type'    => ENR_Subscription_Plan::get_type( $this->get_prop( 'subscribed_plan', null, $key ) ),
					'period_to_subscribe'     => ENR_Subscription_Plan::get_prop( $this->get_prop( 'subscribed_plan', null, $key ), 'userdefined', 'subscription_period' ),
					'interval_to_subscribe'   => ENR_Subscription_Plan::get_prop( $this->get_prop( 'subscribed_plan', null, $key ), 'userdefined', 'subscription_period_interval' ),
					'length_to_subscribe'     => ENR_Subscription_Plan::get_prop( $this->get_prop( 'subscribed_plan', null, $key ), 'userdefined', 'subscription_length' ),
					'available_plans'         => array(),
					'default_plan'            => '0',
						), $this ), $default_args );

		return $args;
	}

	/**
	 * Read posted data.
	 *
	 * @param array $posted Values of the prop.
	 * @param mixed $key Subscribed key for the prop.
	 */
	public function read_posted_data( $posted, $key = null ) {
		if ( empty( $posted[ 'enr_subscribed' ] ) ) {
			$this->delete_data( $key );
			return;
		}

		$plan_chosen = ! empty( $posted[ 'enr_subscribed_plan' ] ) ? absint( wp_unslash( $posted[ 'enr_subscribed_plan' ] ) ) : 0;

		$this->set_prop( 'subscribed', 'yes', $key );
		$this->set_prop( 'subscribed_plan', $plan_chosen, $key );
		$this->set_prop( 'subscribed_price_percent', ENR_Subscription_Plan::get_prop( $plan_chosen, ENR_Subscription_Plan::get_type( $plan_chosen ), 'subscription_price' ), $key );

		switch ( ENR_Subscription_Plan::get_type( $plan_chosen ) ) {
			case 'userdefined':
				if ( ! empty( $posted[ 'enr_subscribed_period_interval' ] ) ) {
					$period        = sanitize_title( wp_unslash( $posted[ 'enr_subscribed_period' ] ) );
					$length        = absint( wp_unslash( $posted[ 'enr_subscribed_length' ] ) );
					$length_ranges = wcs_get_subscription_ranges( $period );
					$length_keys   = array_keys( $length_ranges );
					$max_length    = end( $length_keys );

					$this->set_prop( 'subscribed_period', $period, $key );
					$this->set_prop( 'subscribed_period_interval', absint( wp_unslash( $posted[ 'enr_subscribed_period_interval' ] ) ), $key );

					if ( is_numeric( $max_length ) && $length <= $max_length ) {
						$this->set_prop( 'subscribed_length', $length, $key );
					} else {
						$this->set_prop( 'subscribed_length', 0, $key );
					}
				} else {
					$this->set_prop( 'subscribed_period', ENR_Subscription_Plan::get_default_prop( 'subscription_period' ), $key );
					$this->set_prop( 'subscribed_period_interval', ENR_Subscription_Plan::get_default_prop( 'subscription_period_interval' ), $key );
					$this->set_prop( 'subscribed_length', ENR_Subscription_Plan::get_default_prop( 'subscription_length' ), $key );
				}
				$this->save_data();
				break;
			case 'predefined':
				$this->set_prop( 'subscribed_period', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'subscription_period' ), $key );
				$this->set_prop( 'subscribed_period_interval', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'subscription_period_interval' ), $key );
				$this->set_prop( 'subscribed_length', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'subscription_length' ), $key );
				$this->set_prop( 'subscribed_trial_period', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'subscription_trial_period' ), $key );
				$this->set_prop( 'subscribed_trial_length', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'subscription_trial_length' ), $key );
				$this->set_prop( 'subscribed_sign_up_fee', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'subscription_sign_up_fee' ), $key );
				$this->set_prop( 'subscribed_payment_sync_date', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'subscription_payment_sync_date' ), $key );
				$this->set_prop( 'subscribed_for_shipping_cycle', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'enable_seperate_shipping_cycle' ), $key );
				$this->set_prop( 'subscribed_shipping_period_interval', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'shipping_period_interval' ), $key );
				$this->set_prop( 'subscribed_shipping_period', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'shipping_period' ), $key );
				$this->set_prop( 'allowed_cancelling_to', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'allow_cancelling_to' ), $key );
				$this->set_prop( 'allowed_cancelling_after', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'allow_cancelling_after' ), $key );
				$this->set_prop( 'allowed_cancelling_after_due', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'allow_cancelling_after_due' ), $key );
				$this->set_prop( 'allowed_cancelling_before_due', ENR_Subscription_Plan::get_prop( $plan_chosen, 'predefined', 'allow_cancelling_before_due' ), $key );
				$this->save_data();
				break;
		}
	}

	/**
	 * Add the product object cache.
	 * 
	 * @param WC_Product $product
	 * @param array $data
	 */
	public function add_subscription_cache( $product, $data = array() ) {
		$data = wp_parse_args( $data, $this->default_data );

		if ( ! $this->is_subscription_product_type( $product ) ) {
			$product->add_meta_data( '_enr_subscribed_type', $this->get_type(), true );
			$product->add_meta_data( '_enr_subscribed_plan', $data[ 'subscribed_plan' ], true );
			$product->add_meta_data( '_enr_subscribed_data', $data, true );
			$product->add_meta_data( '_subscription_price', $product->get_price(), true );
			$product->add_meta_data( '_subscription_sign_up_fee', $data[ 'subscribed_sign_up_fee' ], true );
			$product->add_meta_data( '_subscription_period', $data[ 'subscribed_period' ], true );
			$product->add_meta_data( '_subscription_period_interval', $data[ 'subscribed_period_interval' ], true );
			$product->add_meta_data( '_subscription_length', $data[ 'subscribed_length' ], true );
			$product->add_meta_data( '_subscription_trial_period', $data[ 'subscribed_trial_period' ], true );
			$product->add_meta_data( '_subscription_trial_length', $data[ 'subscribed_trial_length' ], true );
			$product->add_meta_data( '_subscription_payment_sync_date', $data[ 'subscribed_payment_sync_date' ], true );
			$product->add_meta_data( '_enr_enable_seperate_shipping_cycle', $data[ 'subscribed_for_shipping_cycle' ], true );
			$product->add_meta_data( '_enr_shipping_period_interval', $data[ 'subscribed_shipping_period_interval' ], true );
			$product->add_meta_data( '_enr_shipping_period', $data[ 'subscribed_shipping_period' ], true );
			$product->add_meta_data( '_enr_allow_cancelling_to', $data[ 'allowed_cancelling_to' ], true );
			$product->add_meta_data( '_enr_allow_cancelling_after', $data[ 'allowed_cancelling_after' ], true );
			$product->add_meta_data( '_enr_allow_cancelling_after_due', $data[ 'allowed_cancelling_after_due' ], true );
			$product->add_meta_data( '_enr_allow_cancelling_before_due', $data[ 'allowed_cancelling_before_due' ], true );
		}
	}

	/**
	 * Destroy the product object cache before data saves in to the database.
	 * 
	 * @param WC_Product $product
	 */
	public function destroy_subscription_cache( $product ) {
		if ( ! $this->is_subscription_product_type( $product ) ) {
			$product->delete_meta_data( '_enr_subscribed_type' );
			$product->delete_meta_data( '_enr_subscribed_plan' );
			$product->delete_meta_data( '_enr_subscribed_data' );
			$product->delete_meta_data( '_subscription_price' );
			$product->delete_meta_data( '_subscription_sign_up_fee' );
			$product->delete_meta_data( '_subscription_period' );
			$product->delete_meta_data( '_subscription_period_interval' );
			$product->delete_meta_data( '_subscription_length' );
			$product->delete_meta_data( '_subscription_trial_period' );
			$product->delete_meta_data( '_subscription_trial_length' );
			$product->delete_meta_data( '_subscription_payment_sync_date' );
			$product->delete_meta_data( '_enr_enable_seperate_shipping_cycle' );
			$product->delete_meta_data( '_enr_shipping_period_interval' );
			$product->delete_meta_data( '_enr_shipping_period' );
			$product->delete_meta_data( '_enr_allow_cancelling_to' );
			$product->delete_meta_data( '_enr_allow_cancelling_after' );
			$product->delete_meta_data( '_enr_allow_cancelling_after_due' );
			$product->delete_meta_data( '_enr_allow_cancelling_before_due' );
		}
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
		if ( $this->is_subscribed( $product ) ) {
			$is_subscription = true;
		}

		return $is_subscription;
	}

	/**
	 * Init the subscription from session while adding to cart.
	 */
	public function init_subscription_from_session_in_cart() {
		$this->load_subscription_from_session_in_cart( WC()->cart );
	}

	/**
	 * Load the subscription from session in cart.
	 * 
	 * @param WC_Cart $cart
	 */
	public function load_subscription_from_session_in_cart( $cart ) {
	}

	/**
	 * Calculate the subscription price.
	 * 
	 * @param float $price
	 * @param WC_Product $product
	 * @return float
	 */
	public function calculate_subscription_price( $price, $product ) {
		/**
		 * Need to calculate subscription price?
		 * 
		 * @since 1.0
		 */
		if ( $this->is_subscribed( $product ) && apply_filters( 'enr_calculate_subscription_price', true, $product, $this ) ) {
			$subscribed_data    = $product->get_meta( '_enr_subscribed_data', true, 'edit' );
			$price_percent      = floatval( $subscribed_data[ 'subscribed_price_percent' ] );
			$subscription_price = 0;

			if ( $price_percent > 0 ) {
				$subscription_price = ( floatval( $price ) * $price_percent ) / 100;
			}

			return wc_format_decimal( $subscription_price );
		}

		return $price;
	}

	/**
	 * WP add_actions to render subscribe now form.
	 */
	public function add_actions_to_render_subscribe_form() {
	}

	/**
	 * Maybe render the subscribe now form.
	 */
	public function maybe_render_subscribe_form() {
	}
}
