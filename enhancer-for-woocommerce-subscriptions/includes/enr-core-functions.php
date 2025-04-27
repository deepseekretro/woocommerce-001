<?php

defined( 'ABSPATH' ) || exit;

include_once 'enr-time-functions.php';
include_once 'enr-template-functions.php';

/**
 * Get enhancer template.
 *
 * @param string $template_name
 * @param array $args (default: array())
 */
function _enr_get_template( $template_name, $args = array() ) {
	if ( $template_name ) {
		wc_get_template( $template_name, $args, trailingslashit( dirname( plugin_basename( ENR_FILE ) ) ), _enr()->template_path() );
	}
}

/**
 * Maybe get the subscription instance.
 *
 * @param mixed $subscription
 * @return WC_Subscription
 */
function _enr_maybe_get_subscription_instance( $subscription ) {
	if ( ! is_a( $subscription, 'WC_Subscription' ) ) {
		$subscription = wcs_get_subscription( $subscription );
	}

	return $subscription;
}

/**
 * Check if a given order was created for shipping fulfilment.
 *
 * @param WC_Order|int $order The WC_Order object or ID of a WC_Order order.
 */
function _enr_order_contains_shipping_fulfilment( $order ) {
	if ( ! is_a( $order, 'WC_Abstract_Order' ) ) {
		$order = wc_get_order( $order );
	}

	$related_subscriptions = wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'enr_shipping_fulfilment' ) );

	if ( wcs_is_order( $order ) && ! empty( $related_subscriptions ) ) {
		$is_shipping_fulfilment_order = true;
	} else {
		$is_shipping_fulfilment_order = false;
	}

	/**
	 * Is shipping fulfilment order?
	 * 
	 * @param bool $is_shipping_fulfilment_order 
	 * @param WC_Order $order
	 * @since 1.0
	 */
	return apply_filters( 'woocommerce_subscriptions_is_enr_shipping_fulfilment_order', $is_shipping_fulfilment_order, $order );
}

/**
 * Is user subscribed product?
 * 
 * @param WC_Product $product
 * @param string $type Values are either 'product_level' | 'cart_level' | 'any'
 * @return boolean
 */
function _enr_is_subscribed_product( $product, $type = 'any' ) {
	if ( ! is_object( $product ) ) {
		return false;
	}

	if ( 'any' === $type ) {
		return ENR_Product_Level_Subscribe_Now::instance()->is_subscribed( $product ) || ENR_Cart_Level_Subscribe_Now::instance()->is_subscribed( $product );
	} else if ( 'product_level' === $type ) {
		return ENR_Product_Level_Subscribe_Now::instance()->is_subscribed( $product );
	} else if ( 'cart_level' === $type ) {
		return ENR_Cart_Level_Subscribe_Now::instance()->is_subscribed( $product );
	}

	return false;
}

/**
 * Is valid to schedule the given reminder?
 * 
 * @param WC_Subscription $subscription
 * @param string $reminder_type
 * @return bool
 */
function _enr_can_schedule_reminder( $subscription, $reminder_type ) {
	$excluded_reminder_emails = $subscription->get_meta( ENR_PREFIX . 'exclude_reminder_emails', true, 'edit' );
	return in_array( $reminder_type, ( array ) $excluded_reminder_emails ) ? false : true;
}

/**
 * Check whether the cart contains subscribed product.
 * 
 * @param string $type Values are either 'product_level' | 'cart_level' | 'any'
 * @return boolean
 */
function _enr_cart_contains_subscribed_product( $type = 'any' ) {
	if ( is_null( WC()->cart ) ) {
		return false;
	}

	foreach ( WC()->cart->cart_contents as $cart_item ) {
		if ( ! empty( $cart_item[ 'data' ] ) && _enr_is_subscribed_product( $cart_item[ 'data' ], $type ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Is switching between subscription plans allowed?
 *
 * @return boolean
 */
function _enr_allowed_switching_btw_plans() {
	return 'yes' === get_option( 'woocommerce_subscriptions_allow_switching_enr_subscription_plans' );
}

/**
 * Is switch in progress ?
 *
 * @return boolean
 */
function _enr_is_switch_request() {
	return isset( $_GET[ 'switch-subscription' ], $_GET[ 'item' ] );
}

/**
 * Check if a given subscription's shipping frequency is synced to a certain day.
 * 
 * @param WC_Subscription|WC_Product $object
 * @return bool
 */
function _enr_is_shipping_frequency_synced( $object ) {
	return ENR_Shipping_Cycle::is_frequency_synced( $object );
}

/**
 * Get the shipping frequency string.
 * 
 * @param array $args
 * @return string
 */
function _enr_get_shipping_frequency_string( $args ) {
	return ENR_Shipping_Cycle::prepare_frequency_string( $args );
}

/**
 * Get the type in which the array is sorted by.
 * 
 * @param array $array
 * @return boolean|string
 */
function _enr_array_sorted_by( $array ) {
	$o_array = $array;

	$asc = $o_array;
	sort( $asc );
	if ( $o_array === $asc ) {
		return 'asc';
	}

	$desc = $o_array;
	rsort( $desc );
	if ( $o_array === $desc ) {
		return 'desc';
	}

	return false;
}

/**
 * Get Number Suffix to Display.
 * 
 * @param int $number
 * @return string
 */
function _enr_get_number_suffix( $number ) {
	// Special case 'teenth'
	if ( ( $number / 10 ) / 10 != 1 ) {
		// Handle 1st, 2nd, 3rd
		switch ( $number % 10 ) {
			case 1:
				return $number . 'st';
			case 2:
				return $number . 'nd';
			case 3:
				return $number . 'rd';
		}
	}
	// Everything else is 'nth'
	return $number . 'th';
}

/**
 * Return the array of subscription length ranges based upon given period and interval.
 * 
 * @param string $period
 * @param int $interval
 * @return array
 */
function _enr_get_subscription_length_ranges( $period, $interval = 1 ) {
	$ranges = array();

	foreach ( wcs_get_subscription_ranges( $period ) as $length => $label ) {
		if ( 0 === absint( $length ) || 0 === ( absint( $length ) % $interval ) ) {
			$ranges[ $length ] = $label;
		}
	}

	return $ranges;
}

/**
 * Get all terms for a product by ID, including hierarchy
 *
 * @param  int $product_id Product ID.
 * @param  string $taxonomy Taxonomy slug.
 * @return array
 */
function _enr_get_product_term_ids( $product_id, $taxonomy = 'product_cat' ) {
	$product_terms = wc_get_product_term_ids( $product_id, $taxonomy );

	foreach ( $product_terms as $product_term ) {
		$product_terms = array_merge( $product_terms, get_ancestors( $product_term, $taxonomy ) );
	}

	return $product_terms;
}

/**
 * Return the array of subscription plan types.
 *
 * @return array
 */
function _enr_get_subscription_plan_types() {
	return array(
		'predefined'  => __( 'Predefined', 'enhancer-for-woocommerce-subscriptions' ),
		'userdefined' => __( 'Customer Defined', 'enhancer-for-woocommerce-subscriptions' ),
	);
}

/**
 * Return the array of subscription plans.
 * 
 * @return int[]
 */
function _enr_get_subscription_plans() {
	global $wpdb;
	$_wpdb = &$wpdb;
	$plans = $_wpdb->get_col( $_wpdb->prepare( "SELECT ID FROM {$_wpdb->posts} WHERE 1=%d AND post_type = 'enr_subsc_plan' AND post_status = 'publish' ORDER BY menu_order ASC", 1 ) );
	return $plans;
}

/**
 * Get the mapped email template id.
 * 
 * @param string $wc_email_id
 * @param string $mapping_key
 * @param WC_Subscription $subscription
 * @return int|null
 */
function _enr_get_mapped_email_template_id( $wc_email_id, $mapping_key, $subscription ) {
	global $wpdb;
	$wpdb_ref           = &$wpdb;
	$email_template_ids = $wpdb_ref->get_col(
			$wpdb_ref->prepare( "
                 SELECT DISTINCT ID FROM {$wpdb_ref->posts} AS p
                 INNER JOIN {$wpdb_ref->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key='_wc_email_id' AND pm.meta_value=%s)
                 WHERE p.post_type = 'enr_email_template' AND p.post_status = 'publish' AND p.post_excerpt = %s
                ", esc_sql( $wc_email_id ), esc_sql( $mapping_key )
			) );

	if ( ! empty( $email_template_ids ) && is_a( $subscription, 'WC_Subscription' ) ) {
		foreach ( $email_template_ids as $email_template_id ) {
			if ( _enr_email_template_contains_subscription( $email_template_id, $subscription ) ) {
				return _enr_maybe_get_wpml_object_id( $email_template_id, $subscription->get_meta( 'wpml_language' ) );
			}
		}
	}

	return null;
}

/**
 * Check if email template contains subscription.
 * 
 * @param int $template_id
 * @param WC_Subscription $subscription
 * @return bool
 */
function _enr_email_template_contains_subscription( $template_id, $subscription ) {
	$canonical_products      = array();
	$canonical_product_terms = array();

	foreach ( $subscription->get_items( 'line_item' ) as $line_item ) {
		$canonical_products[]    = $line_item[ 'variation_id' ] > 0 ? $line_item[ 'variation_id' ] : $line_item[ 'product_id' ];
		$canonical_product_terms = array_unique( array_merge( $canonical_product_terms, _enr_get_product_term_ids( $line_item[ 'product_id' ] ) ) );
	}

	$contains_subscription = true;
	switch ( ENR_Subscription_Email_Template::get_prop( $template_id, 'email_product_filter' ) ) {
		case 'included-products':
			if ( empty( $canonical_products ) || ( 0 === count( array_intersect( $canonical_products, ENR_Subscription_Email_Template::get_prop( $template_id, 'email_included_products' ) ) ) ) ) {
				$contains_subscription = false;
			}
			break;
		case 'included-categories':
			if ( empty( $canonical_product_terms ) || ( 0 === count( array_intersect( $canonical_product_terms, ENR_Subscription_Email_Template::get_prop( $template_id, 'email_included_categories' ) ) ) ) ) {
				$contains_subscription = false;
			}
			break;
	}

	return $contains_subscription;
}

/**
 * Return an array of reminder emails.
 * 
 * @return array
 */
function _enr_get_reminder_email_options() {
	return array(
		'trial_end'      => __( 'Trial Ending Reminder', 'enhancer-for-woocommerce-subscriptions' ),
		'auto_renewal'   => __( 'Auto Renewal Reminder', 'enhancer-for-woocommerce-subscriptions' ),
		'manual_renewal' => __( 'Manual Renewal Reminder', 'enhancer-for-woocommerce-subscriptions' ),
		'expiry'         => __( 'Expiry Reminder', 'enhancer-for-woocommerce-subscriptions' ),
	);
}

/**
 * Maybe get the product instance.
 *
 * @param mixed $product
 * @return WC_Product
 */
function _enr_maybe_get_product_instance( $product ) {
	if ( ! is_a( $product, 'WC_Product' ) ) {
		$product = wc_get_product( $product );
	}

	return $product;
}

/**
 * WPML compatibility.
 * 
 * @param int $element_id
 * @param string $lang
 * @param string $element_type
 * @param bool $return_original_if_missing
 * @return int
 */
function _enr_maybe_get_wpml_object_id( $element_id, $lang, $element_type = 'post', $return_original_if_missing = false ) {
	global $sitepress;

	// WPML Compatibility.
	if ( $sitepress && ! empty( $lang ) ) {
		return $sitepress->get_object_id( $element_id, $element_type, $return_original_if_missing, $lang );
	}

	return $element_id;
}

/**
 * Get the numeral suffix.
 * 
 * @param int $number
 * @return string
 */
function _enr_append_numeral_suffix( $number ) {
	return function_exists( 'wcs_append_numeral_suffix' ) ? wcs_append_numeral_suffix( $number ) : WC_Subscriptions::append_numeral_suffix( $number );
}
