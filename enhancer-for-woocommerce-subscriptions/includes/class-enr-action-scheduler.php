<?php

/**
 * Scheduler for subscription enhancer events that uses the Action Scheduler
 *
 * @class ENR_Action_Scheduler
 * @package Class
 */
class ENR_Action_Scheduler {

	/**
	 * An internal cache of action hooks and corresponding date types.
	 *
	 * @var array An array of $action_hook => $date_type values
	 */
	protected static $action_hooks = array(
		'enr_woocommerce_scheduled_subscription_trial_end_reminder'        => 'trial_end',
		'enr_woocommerce_scheduled_subscription_auto_renewal_reminder'     => 'next_payment',
		'enr_woocommerce_scheduled_subscription_manual_renewal_reminder'   => 'next_payment',
		'enr_woocommerce_scheduled_subscription_price_changed_reminder'    => 'next_payment',
		'enr_woocommerce_scheduled_subscription_shipping_fulfilment_order' => 'next_payment',
		'enr_woocommerce_scheduled_subscription_expiration_reminder'       => 'end',
	);

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'woocommerce_subscription_status_updated', __CLASS__ . '::maybe_schedule_when_status_updated', 0, 2 );
		add_action( 'woocommerce_subscription_date_updated', __CLASS__ . '::maybe_schedule_when_date_updated', 0, 2 );
		add_action( 'woocommerce_subscription_date_deleted', __CLASS__ . '::maybe_schedule_when_date_updated', 0, 2 );
		add_action( 'woocommerce_subscriptions_switch_completed', __CLASS__ . '::maybe_schedule_when_switched', 11 );
	}

	/**
	 * Get the args to set on the scheduled action.
	 *
	 * @param string $date_type Can be 'trial_end', 'next_payment', 'expiration', 'end_of_prepaid_term' or a custom date type
	 * @param object $subscription An instance of WC_Subscription object
	 * @return array Array of name => value pairs stored against the scheduled action.
	 */
	public static function get_action_args( $date_type, $subscription ) {
		/**
		 * Get the subscription scheduled action args.
		 * 
		 * @param array $args 
		 * @param string $date_type
		 * @param WC_Subscription $subscription
		 * @since 1.0
		 */
		return apply_filters( 'woocommerce_subscriptions_scheduled_action_args', array( 'subscription_id' => $subscription->get_id() ), $date_type, $subscription );
	}

	/**
	 * Schedule the multiple reminders before the end time.
	 * 
	 * @param mixed $end_time
	 * @param string $hook
	 * @param mixed $days_to_remind
	 * @param array $action_args
	 */
	public static function schedule_reminders( $end_time, $hook, $days_to_remind, $action_args ) {
		if ( empty( $days_to_remind ) ) {
			return;
		}

		if ( ! is_array( $days_to_remind ) ) {
			$days_to_remind = array_map( 'trim', explode( ',', $days_to_remind ) );
		}

		$days_to_remind = array_map( 'absint', $days_to_remind );
		$days_to_remind = _enr_get_dates( time(), $end_time, $days_to_remind );

		if ( empty( $days_to_remind ) ) {
			return;
		}

		foreach ( $days_to_remind as $day_to_remind => $timestamp ) {
			$action_args[ 'day_to_remind' ] = $day_to_remind;
			as_schedule_single_action( $timestamp, $hook, $action_args );
		}
	}

	/**
	 * When a subscription's status is updated, maybe schedule some events.
	 *
	 * @param object $subscription An instance of a WC_Subscription object
	 * @param string $new_status
	 * @param string $old_status
	 */
	public static function maybe_schedule_when_status_updated( $subscription, $new_status ) {
		switch ( $new_status ) {
			case 'active':
				self::maybe_schedule_when_date_updated( $subscription, 'trial_end' );
				self::maybe_schedule_when_date_updated( $subscription, 'next_payment' );
				self::maybe_schedule_when_date_updated( $subscription, 'end' );
				break;
			case 'pending-cancel':
				self::maybe_schedule_when_date_updated( $subscription, 'end' );
				break;
			case 'on-hold':
				self::unschedule_all_actions( $subscription, 'enr_woocommerce_scheduled_subscription_shipping_fulfilment_order' );
				break;
			case 'cancelled':
			case 'switched':
			case 'expired':
			case 'trash':
				self::unschedule_all_actions( $subscription );
				break;
		}
	}

	/**
	 * When a subscription's date is updated, maybe schedule some events.
	 *
	 * @param WC_Subscription $subscription
	 * @param string $date_type Can be 'trial_end', 'next_payment', 'end', 'end_of_prepaid_term' or a custom date type
	 */
	public static function maybe_schedule_when_date_updated( $subscription, $date_type ) {
		if ( ! $subscription->has_status( array( 'active', 'pending-cancel' ) ) ) {
			self::unschedule_all_actions( $subscription );
			return;
		}

		$timestamp   = $subscription->get_time( $date_type );
		$action_args = self::get_action_args( $date_type, $subscription );

		if ( ! $timestamp ) {
			foreach ( self::$action_hooks as $hook => $value ) {
				if ( $value === $date_type && 'enr_woocommerce_scheduled_subscription_shipping_fulfilment_order' !== $hook ) {
					self::unschedule_all_actions_by_hook( $hook, $action_args );
				}
			}

			if ( 'trial_end' === $date_type ) {
				foreach ( self::$action_hooks as $_date_type ) {
					if ( 'trial_end' !== $_date_type ) {
						self::schedule_actions( $subscription, $subscription->get_time( $_date_type ), $_date_type, $action_args, true );
					}
				}
			}
			return;
		}

		// Check whether Trial is in active
		if ( $subscription->get_time( 'trial_end' ) > 0 && $subscription->get_time( 'trial_end' ) > gmdate( 'U' ) ) {
			self::unschedule_all_actions( $subscription, 'enr_woocommerce_scheduled_subscription_trial_end_reminder' );
			self::schedule_actions( $subscription, $timestamp, 'trial_end', $action_args );
		} else {
			self::schedule_actions( $subscription, $timestamp, $date_type, $action_args );
		}
	}

	/**
	 * When a subscription is switched, maybe schedule some events.
	 * 
	 * @param WC_Order $order
	 */
	public static function maybe_schedule_when_switched( $order ) {
		$switch_order_data = wcs_get_objects_property( $order, 'subscription_switch_data' );

		if ( empty( $switch_order_data ) || ! is_array( $switch_order_data ) ) {
			return;
		}

		foreach ( $switch_order_data as $subscription_id => $switch_data ) {
			$subscription = wcs_get_subscription( $subscription_id );

			if ( $subscription instanceof WC_Subscription ) {
				self::maybe_schedule_when_status_updated( $subscription, $subscription->get_status() );
			}
		}
	}

	/**
	 * Schedule the actions for the date type given.
	 * 
	 * @param WC_Subscription $subscription
	 * @param int $timestamp
	 * @param string $date_type Can be 'trial_end', 'next_payment', 'end', 'end_of_prepaid_term' or a custom date type
	 * @param array $action_args
	 * @param bool $force
	 */
	public static function schedule_actions( $subscription, $timestamp, $date_type, $action_args, $force = false ) {
		if ( ! $timestamp ) {
			return;
		}

		switch ( $date_type ) {
			case 'trial_end':
				$trial_end_scheduled = as_next_scheduled_action( 'woocommerce_scheduled_subscription_trial_end', $action_args );

				if ( $timestamp !== $trial_end_scheduled || $force ) {
					self::unschedule_all_actions_by_date_type( 'trial_end', $action_args );

					if ( _enr_can_schedule_reminder( $subscription, 'trial_end' ) ) {
						self::schedule_reminders( $timestamp, 'enr_woocommerce_scheduled_subscription_trial_end_reminder', get_option( ENR_PREFIX . 'send_trial_ending_reminder_before' ), $action_args );
					}
				}
				break;
			case 'next_payment':
				$payment_scheduled = as_next_scheduled_action( 'woocommerce_scheduled_subscription_payment', $action_args );

				if ( $timestamp !== $payment_scheduled || $force ) {
					self::unschedule_all_actions_by_date_type( 'next_payment', $action_args );

					if ( ENR_Subscriptions_Price_Update::can_apply_new_price( $subscription ) ) {
						self::schedule_reminders( $timestamp, 'enr_woocommerce_scheduled_subscription_price_changed_reminder', ENR_Subscriptions_Price_Update::get_days_to_remind_before( $subscription ), $action_args );
					}

					if ( $subscription->is_manual() ) {
						if ( _enr_can_schedule_reminder( $subscription, 'manual_renewal' ) ) {
							self::schedule_reminders( $timestamp, 'enr_woocommerce_scheduled_subscription_manual_renewal_reminder', get_option( ENR_PREFIX . 'send_manual_renewal_reminder_before' ), $action_args );
						}
					} elseif ( _enr_can_schedule_reminder( $subscription, 'auto_renewal' ) ) {
						self::schedule_reminders( $timestamp, 'enr_woocommerce_scheduled_subscription_auto_renewal_reminder', get_option( ENR_PREFIX . 'send_auto_renewal_reminder_before' ), $action_args );
					}

					if ( ENR_Shipping_Cycle::can_be_scheduled( $subscription ) ) {
						ENR_Shipping_Cycle::schedule_shipping_fulfilment_orders( $subscription, $timestamp, $action_args );
					}
				}
				break;
			case 'end':
				$check_for_fulfilment_schedules = false;

				if ( $subscription->has_status( 'pending-cancel' ) ) {
					if ( as_next_scheduled_action( 'woocommerce_scheduled_subscription_end_of_prepaid_term', $action_args ) !== $timestamp || $force ) {
						self::unschedule_all_actions_by_hook( 'enr_woocommerce_scheduled_subscription_expiration_reminder', $action_args );
						$check_for_fulfilment_schedules = true;
					}
				} elseif ( as_next_scheduled_action( 'woocommerce_scheduled_subscription_expiration', $action_args ) !== $timestamp || $force ) {
					self::unschedule_all_actions_by_hook( 'enr_woocommerce_scheduled_subscription_expiration_reminder', $action_args );

					if ( _enr_can_schedule_reminder( $subscription, 'expiry' ) ) {
						self::schedule_reminders( $timestamp, 'enr_woocommerce_scheduled_subscription_expiration_reminder', get_option( ENR_PREFIX . 'send_expiry_reminder_before' ), $action_args );
					}

					$check_for_fulfilment_schedules = true;
				}

				if ( $check_for_fulfilment_schedules && ENR_Shipping_Cycle::can_be_scheduled( $subscription ) && empty( $subscription->get_time( 'next_payment' ) ) ) {
					self::unschedule_all_actions_by_hook( 'enr_woocommerce_scheduled_subscription_shipping_fulfilment_order', $action_args );
					ENR_Shipping_Cycle::schedule_shipping_fulfilment_orders( $subscription, $timestamp, $action_args );
				}
				break;
		}
	}

	/**
	 * Unschedule all actions by hook in bulk.
	 * 
	 * @param string $hook
	 * @param array $action_args
	 */
	public static function unschedule_all_actions_by_hook( $hook, $action_args ) {
		$subscription_id = $action_args[ 'subscription_id' ];

		switch ( $hook ) {
			case 'enr_woocommerce_scheduled_subscription_trial_end_reminder':
			case 'enr_woocommerce_scheduled_subscription_auto_renewal_reminder':
			case 'enr_woocommerce_scheduled_subscription_manual_renewal_reminder':
			case 'enr_woocommerce_scheduled_subscription_price_changed_reminder':
			case 'enr_woocommerce_scheduled_subscription_expiration_reminder':
				$scheduled_actions = as_get_scheduled_actions( array(
					'hook'     => $hook,
					'status'   => 'pending',
					'per_page' => -1,
						) );

				foreach ( $scheduled_actions as $action ) {
					$args = $action->get_args();

					if ( $subscription_id == $args[ 'subscription_id' ] ) {
						as_unschedule_all_actions( $hook, $args );
					}
				}
				break;
			default:
				as_unschedule_all_actions( $hook, $action_args );
		}
	}

	/**
	 * Unschedule all actions by date type in bulk.
	 * 
	 * @param string $date_type
	 * @param array $action_args
	 */
	public static function unschedule_all_actions_by_date_type( $date_type, $action_args ) {
		foreach ( self::$action_hooks as $hook => $value ) {
			if ( $value === $date_type ) {
				self::unschedule_all_actions_by_hook( $hook, $action_args );
			}
		}
	}

	/**
	 * Unschedule all actions in bulk.
	 * 
	 * @param object $subscription An instance of a WC_Subscription object
	 * @param array $exclude_hooks
	 */
	public static function unschedule_all_actions( $subscription, $exclude_hooks = array() ) {
		foreach ( self::$action_hooks as $hook => $date_type ) {
			if ( in_array( $hook, ( array ) $exclude_hooks ) ) {
				continue;
			}

			self::unschedule_all_actions_by_hook( $hook, self::get_action_args( $date_type, $subscription ) );
		}
	}
}
