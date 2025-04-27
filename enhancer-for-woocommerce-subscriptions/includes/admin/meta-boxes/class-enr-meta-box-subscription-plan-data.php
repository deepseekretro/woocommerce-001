<?php

defined( 'ABSPATH' ) || exit;

/**
 * Subscription Plan Data.
 * 
 * @class ENR_Meta_Box_Subscription_Plan_Data
 * @package Class
 */
class ENR_Meta_Box_Subscription_Plan_Data {

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function output( $post ) {
		global $post;

		$subscription_plan_post = $post;
		$chosen_type            = ENR_Subscription_Plan::get_type( $post->ID );

		foreach ( _enr_get_subscription_plan_types() as $type => $label ) {
			${"{$type}_chosen_price"}                    = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'subscription_price' );
			${"{$type}_chosen_sign_up_fee"}              = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'subscription_sign_up_fee' );
			${"{$type}_chosen_period"}                   = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'subscription_period' );
			${"{$type}_chosen_interval"}                 = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'subscription_period_interval' );
			${"{$type}_chosen_length"}                   = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'subscription_length' );
			${"{$type}_chosen_trial_period"}             = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'subscription_trial_period' );
			${"{$type}_chosen_trial_length"}             = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'subscription_trial_length' );
			${"{$type}_chosen_sync_date"}                = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'subscription_payment_sync_date' );
			${"{$type}_seperate_shipping_cycle_enabled"} = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'enable_seperate_shipping_cycle' );
			${"{$type}_chosen_shipping_period_interval"} = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'shipping_period_interval' );
			${"{$type}_chosen_shipping_period"}          = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'shipping_period' );
			${"{$type}_allow_cancelling_to"}             = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'allow_cancelling_to' );
			${"{$type}_allow_cancelling_after"}          = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'allow_cancelling_after' );
			${"{$type}_allow_cancelling_after_due"}      = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'allow_cancelling_after_due' );
			${"{$type}_allow_cancelling_before_due"}     = ENR_Subscription_Plan::get_prop( $post->ID, $type, 'allow_cancelling_before_due' );

			$display_sync_week_month_select = ! in_array( $predefined_chosen_period, array( 'month', 'week' ) ) ? 'display: none;' : '';
			$display_sync_annual_select     = 'year' !== $predefined_chosen_period ? 'display: none;' : '';

			if ( is_array( $predefined_chosen_sync_date ) ) {
				$sync_payment_day   = absint( $predefined_chosen_sync_date[ 'day' ] );
				$sync_payment_month = 0 === $sync_payment_day ? 0 : $predefined_chosen_sync_date[ 'month' ];
			} else {
				$sync_payment_day   = $predefined_chosen_sync_date;
				$sync_payment_month = 0;
			}
		}

		include 'views/html-subscription-plan-data.php';
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public static function save( $post_id, $post, $posted ) {
		// Validate before save.
		foreach ( wcs_get_subscription_period_strings() as $period => $label ) {
			if ( isset( $posted[ '_subscription_length' ][ 'userdefined' ][ 'min' ][ $period ], $posted[ '_subscription_length' ][ 'userdefined' ][ 'max' ][ $period ] ) ) {
				if ( '0' === $posted[ '_subscription_length' ][ 'userdefined' ][ 'min' ][ $period ] || '0' === $posted[ '_subscription_length' ][ 'userdefined' ][ 'max' ][ $period ] ) {
					if ( $posted[ '_subscription_length' ][ 'userdefined' ][ 'max' ][ $period ] > 0 ) {
						$posted[ '_subscription_length' ][ 'userdefined' ][ 'min' ][ $period ] = '1';
					}
				} elseif ( $posted[ '_subscription_length' ][ 'userdefined' ][ 'min' ][ $period ] >= $posted[ '_subscription_length' ][ 'userdefined' ][ 'max' ][ $period ] ) {
						$posted[ '_subscription_length' ][ 'userdefined' ][ 'min' ][ $period ] = $posted[ '_subscription_length' ][ 'userdefined' ][ 'max' ][ $period ];
				}
			}
		}

		if ( isset( $posted[ '_subscription_period' ][ 'predefined' ] ) && 'predefined' === $posted[ '_plan_type' ] && 'year' === $posted[ '_subscription_period' ][ 'predefined' ] ) {
			$payment_sync_date                 = array();
			$payment_sync_date[ 'predefined' ] = array(
				'day'   => isset( $posted[ '_subscription_payment_sync_date_day' ][ 'predefined' ] ) ? $posted[ '_subscription_payment_sync_date_day' ][ 'predefined' ] : 0,
				'month' => isset( $posted[ '_subscription_payment_sync_date_month' ][ 'predefined' ] ) ? $posted[ '_subscription_payment_sync_date_month' ][ 'predefined' ] : '01',
			);
		} else {
			$payment_sync_date = isset( $posted[ '_subscription_payment_sync_date' ] ) ? $posted[ '_subscription_payment_sync_date' ] : '';
		}

		$post_data = array(
			'menu_order' => count( _enr_get_subscription_plans() ) + 1,
		);

		if ( doing_action( 'save_post' ) ) {
			$GLOBALS[ 'wpdb' ]->update( $GLOBALS[ 'wpdb' ]->posts, $post_data, array( 'ID' => $post_id ) );
			clean_post_cache( $post_id );
		} else {
			wp_update_post( array_merge( array( 'ID' => $post_id ), $post_data ) );
		}

		update_post_meta( $post_id, '_type', sanitize_title( wp_unslash( $posted[ '_plan_type' ] ) ) );
		update_post_meta( $post_id, '_subscription_price', wc_clean( wp_unslash( $posted[ '_subscription_price' ] ) ) );
		update_post_meta( $post_id, '_subscription_sign_up_fee', wc_clean( wp_unslash( $posted[ '_subscription_sign_up_fee' ] ) ) );
		update_post_meta( $post_id, '_subscription_period', wc_clean( wp_unslash( $posted[ '_subscription_period' ] ) ) );
		update_post_meta( $post_id, '_subscription_period_interval', wc_clean( wp_unslash( $posted[ '_subscription_period_interval' ] ) ) );
		update_post_meta( $post_id, '_subscription_length', wc_clean( wp_unslash( $posted[ '_subscription_length' ] ) ) );
		update_post_meta( $post_id, '_subscription_trial_period', wc_clean( wp_unslash( $posted[ '_subscription_trial_period' ] ) ) );
		update_post_meta( $post_id, '_subscription_trial_length', wc_clean( wp_unslash( $posted[ '_subscription_trial_length' ] ) ) );
		update_post_meta( $post_id, '_subscription_payment_sync_date', wc_clean( wp_unslash( $payment_sync_date ) ) );
		update_post_meta( $post_id, '_enable_seperate_shipping_cycle', isset( $posted[ '_enable_seperate_shipping_cycle' ] ) ? wc_clean( wp_unslash( $posted[ '_enable_seperate_shipping_cycle' ] ) ) : ''  );
		update_post_meta( $post_id, '_shipping_period_interval', wc_clean( wp_unslash( $posted[ '_shipping_period_interval' ] ) ) );
		update_post_meta( $post_id, '_shipping_period', wc_clean( wp_unslash( $posted[ '_shipping_period' ] ) ) );
		update_post_meta( $post_id, '_allow_cancelling_to', wc_clean( wp_unslash( $posted[ '_allow_cancelling_to' ] ) ) );
		update_post_meta( $post_id, '_allow_cancelling_after', wc_clean( wp_unslash( $posted[ '_allow_cancelling_after' ] ) ) );
		update_post_meta( $post_id, '_allow_cancelling_after_due', wc_clean( wp_unslash( $posted[ '_allow_cancelling_after_due' ] ) ) );
		update_post_meta( $post_id, '_allow_cancelling_before_due', wc_clean( wp_unslash( $posted[ '_allow_cancelling_before_due' ] ) ) );
	}
}
