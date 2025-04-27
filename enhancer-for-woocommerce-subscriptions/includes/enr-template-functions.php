<?php
/**
 * Our Templates
 *
 * Functions for the templating system.
 */
defined( 'ABSPATH' ) || exit ;

/**
 * Enhanced cancel option to subscriber.
 * 
 * @param array $actions
 * @param WC_Subscription $subscription
 * @return array
 */
function _enr_account_cancel_option_to_subscriber( $actions, $subscription ) {
	if ( empty( $actions[ 'cancel' ] ) ) {
		return $actions ;
	}

	$hide               = true ;
	$start_timestamp    = $subscription->get_time( 'start', 'gmt' ) ;
	$next_due_timestamp = $subscription->get_time( 'next_payment', 'gmt' ) ;

	if ( 'yes' === get_option( ENR_PREFIX . 'allow_cancelling', 'yes' ) ) {
		if ( 'override-storewide' === $subscription->get_meta( ENR_PREFIX . 'allow_cancelling_to' ) ) {
			$no_of_days_to_wait_to_cancel_after_start = $subscription->get_meta( ENR_PREFIX . 'allow_cancelling_after' ) ;
			$no_of_days_to_wait_to_cancel_after_due   = $subscription->get_meta( ENR_PREFIX . 'allow_cancelling_after_due' ) ;
			$hide_cancel_before_due                   = $subscription->get_meta( ENR_PREFIX . 'allow_cancelling_before_due' ) ;
		} else {
			$no_of_days_to_wait_to_cancel_after_start = get_option( ENR_PREFIX . 'allow_cancelling_after', '0' ) ;
			$no_of_days_to_wait_to_cancel_after_due   = get_option( ENR_PREFIX . 'allow_cancelling_after_due', '0' ) ;
			$hide_cancel_before_due                   = get_option( ENR_PREFIX . 'allow_cancelling_before_due', '0' ) ;
		}

		if ( is_numeric( $no_of_days_to_wait_to_cancel_after_start ) ) {
			$no_of_days_to_wait_to_cancel_after_start = absint( $no_of_days_to_wait_to_cancel_after_start ) ;

			if ( 0 === $no_of_days_to_wait_to_cancel_after_start || 0 === $start_timestamp ) {
				$hide = false ;
			} else {
				$min_time_user_wait_to_cancel = $start_timestamp + ( $no_of_days_to_wait_to_cancel_after_start * DAY_IN_SECONDS ) ;
				$hide                         = time() < $min_time_user_wait_to_cancel ? true : false ;
			}
		}

		if ( ! $hide && is_numeric( $no_of_days_to_wait_to_cancel_after_due ) ) {
			$no_of_days_to_wait_to_cancel_after_due = absint( $no_of_days_to_wait_to_cancel_after_due ) ;

			if ( 0 === $no_of_days_to_wait_to_cancel_after_due || 0 === $next_due_timestamp ) {
				$hide = false ;
			} else {
				$min_time_user_wait_to_cancel = $next_due_timestamp + ( $no_of_days_to_wait_to_cancel_after_due * DAY_IN_SECONDS ) ;
				$hide                         = time() < $min_time_user_wait_to_cancel ? true : false ;
			}
		}

		if ( ! $hide && is_numeric( $hide_cancel_before_due ) ) {
			$hide_cancel_before_due = absint( $hide_cancel_before_due ) ;

			if ( 0 === $hide_cancel_before_due || 0 === $next_due_timestamp ) {
				$hide = false ;
			} else {
				$min_time_user_can_cancel_before_due = $next_due_timestamp - ( $hide_cancel_before_due * DAY_IN_SECONDS ) ;
				$hide                                = time() > $min_time_user_can_cancel_before_due ? true : false ;
			}
		}
	}

	if ( $hide ) {
		unset( $actions[ 'cancel' ] ) ;
	}

	return $actions ;
}

/**
 * Enhanced switch option to subscriber.
 * 
 * @param string $switch_link
 * @param int $item_id
 * @param WC_Order_Item $item
 * @param WC_Subscription $subscription
 * @return string
 */
function _enr_account_switch_option_to_subscriber( $switch_link, $item_id, $item, $subscription ) {
	if ( empty( $switch_link ) ) {
		return $switch_link ;
	}

	$hide               = true ;
	$start_timestamp    = $subscription->get_time( 'start', 'gmt' ) ;
	$next_due_timestamp = $subscription->get_time( 'next_payment', 'gmt' ) ;

	$no_of_days_to_wait_to_switch_after_start = get_option( ENR_PREFIX . 'allow_switching_after', '0' ) ;
	$no_of_days_to_wait_to_switch_after_due   = get_option( ENR_PREFIX . 'allow_switching_after_due', '0' ) ;
	$hide_switch_before_due                   = get_option( ENR_PREFIX . 'allow_switching_before_due', '0' ) ;

	if ( is_numeric( $no_of_days_to_wait_to_switch_after_start ) ) {
		$no_of_days_to_wait_to_switch_after_start = absint( $no_of_days_to_wait_to_switch_after_start ) ;

		if ( 0 === $no_of_days_to_wait_to_switch_after_start || 0 === $start_timestamp ) {
			$hide = false ;
		} else {
			$min_time_user_wait_to_switch = $start_timestamp + ( $no_of_days_to_wait_to_switch_after_start * DAY_IN_SECONDS ) ;
			$hide                         = time() < $min_time_user_wait_to_switch ? true : false ;
		}
	}

	if ( ! $hide && is_numeric( $no_of_days_to_wait_to_switch_after_due ) ) {
		$no_of_days_to_wait_to_switch_after_due = absint( $no_of_days_to_wait_to_switch_after_due ) ;

		if ( 0 === $no_of_days_to_wait_to_switch_after_due || 0 === $next_due_timestamp ) {
			$hide = false ;
		} else {
			$min_time_user_wait_to_switch = $next_due_timestamp + ( $no_of_days_to_wait_to_switch_after_due * DAY_IN_SECONDS ) ;
			$hide                         = time() < $min_time_user_wait_to_switch ? true : false ;
		}
	}

	if ( ! $hide && is_numeric( $hide_switch_before_due ) ) {
		$hide_switch_before_due = absint( $hide_switch_before_due ) ;

		if ( 0 === $hide_switch_before_due || 0 === $next_due_timestamp ) {
			$hide = false ;
		} else {
			$min_time_user_can_switch_before_due = $next_due_timestamp - ( $hide_switch_before_due * DAY_IN_SECONDS ) ;
			$hide                                = time() > $min_time_user_can_switch_before_due ? true : false ;
		}
	}

	if ( $hide ) {
		$switch_link = '' ;
	}

	return $switch_link ;
}

/**
 * Display the shipping cycle details.
 * 
 * @param WC_Subscription $subscription
 */
function _enr_account_shipping_details( $subscription ) {
	if ( 'yes' === $subscription->get_meta( ENR_PREFIX . 'enable_seperate_shipping_cycle' ) ) {
		?>
		<tr>
			<td><?php esc_html_e( 'Delivered every', 'enhancer-for-woocommerce-subscriptions' ) ; ?></td>
			<td><?php echo esc_html( wcs_get_subscription_period_strings( $subscription->get_meta( ENR_PREFIX . 'shipping_period_interval' ), $subscription->get_meta( ENR_PREFIX . 'shipping_period' ) ) ) ; ?></td>
		</tr>
		<?php
	}
}
