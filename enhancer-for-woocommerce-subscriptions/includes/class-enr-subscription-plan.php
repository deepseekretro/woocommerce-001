<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Subscription plan handler.
 * 
 * @class ENR_Subscription_Plan
 * @package Class
 */
class ENR_Subscription_Plan {

	/**
	 * Core data for the subscription plan.
	 *
	 * @var array
	 */
	protected static $data = array() ;

	/**
	 * Default data for the subscription plan.
	 *
	 * @var array
	 */
	protected static $default_data = array(
		'name'                           => '',
		'type'                           => 'predefined',
		'subscription_price'             => '0',
		'subscription_period'            => 'month',
		'subscription_period_interval'   => '1',
		'subscription_length'            => '0',
		'subscription_trial_period'      => 'day',
		'subscription_trial_length'      => '0',
		'subscription_sign_up_fee'       => '0',
		'subscription_payment_sync_date' => '0',
		'enable_seperate_shipping_cycle' => 'no',
		'shipping_period_interval'       => '0',
		'shipping_period'                => 'day',
		'allow_cancelling_to'            => 'use-storewide',
		'allow_cancelling_after'         => '0',
		'allow_cancelling_after_due'     => '0',
		'allow_cancelling_before_due'    => '0',
			) ;

	/**
	 * Post instance of the subscription plan.
	 *
	 * @var WP_Post[]
	 */
	protected static $post = array() ;

	const POST_TYPE = 'enr_subsc_plan' ;

	/**
	 * Gets a default prop.
	 *
	 * @param string $prop Name of prop to get.
	 * @return mixed
	 */
	public static function get_default_prop( $prop ) {
		return isset( self::$default_data[ $prop ] ) ? self::$default_data[ $prop ] : null ;
	}

	/**
	 * Get the cached subscription plan post instance.
	 * 
	 * @param int $plan_id
	 * @return false|WP_Post
	 */
	public static function maybe_get_post( $plan_id ) {
		if ( ! $plan_id || ! is_numeric( $plan_id ) ) {
			return false ;
		}

		if ( ! isset( self::$post[ $plan_id ] ) ) {
			$post = get_post( $plan_id ) ;

			if ( ! $post || self::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
				return false ;
			}

			self::$post[ $plan_id ] = $post ;
		}

		return self::$post[ $plan_id ] ;
	}

	/**
	 * Check whether the plan exists?
	 * 
	 * @param int $plan_id
	 * @return bool
	 */
	public static function exists( $plan_id ) {
		$post = self::maybe_get_post( $plan_id ) ;
		return is_a( $post, 'WP_Post' ) ;
	}

	/**
	 * Get plan type.
	 *
	 * @param int $plan_id
	 * @return string
	 */
	public static function get_type( $plan_id ) {
		if ( ! self::exists( $plan_id ) ) {
			return null ;
		}

		if ( ! isset( self::$data[ $plan_id ] ) ) {
			$type = get_post_meta( $plan_id, '_type', true ) ;

			if ( array_key_exists( $type, _enr_get_subscription_plan_types() ) ) {
				self::$data[ $plan_id ][ $type ] = array() ;
			}
		}

		return ! empty( self::$data[ $plan_id ] ) ? implode( array_keys( self::$data[ $plan_id ] ) ) : self::get_default_prop( 'type' ) ;
	}

	/**
	 * Gets a prop.
	 * Gets the value from either cached data or from db itself.
	 *
	 * @param int $plan_id
	 * @param string $type Name of plan type.
	 * @param string $prop Name of prop to get.
	 * @param bool $default
	 * @return mixed
	 */
	public static function get_prop( $plan_id, $type, $prop, $default = true ) {
		if ( isset( self::$data[ $plan_id ][ $type ][ $prop ] ) ) {
			return self::$data[ $plan_id ][ $type ][ $prop ] ;
		}

		if ( ! array_key_exists( $type, _enr_get_subscription_plan_types() ) ) {
			return null ;
		}

		if ( is_null( self::get_default_prop( $prop ) ) ) {
			return null ;
		}

		if ( ! self::exists( $plan_id ) ) {
			return $default ? self::get_default_prop( $prop ) : null ;
		}

		if ( 'name' === $prop ) {
			self::$data[ $plan_id ][ $type ][ $prop ] = self::$post[ $plan_id ]->post_title ;
		} else {
			$metadata = get_post_meta( $plan_id ) ;

			if ( isset( $metadata[ "_$prop" ][ 0 ] ) ) {
				$unserialized_metadata = maybe_unserialize( $metadata[ "_$prop" ][ 0 ] ) ;

				if ( isset( $unserialized_metadata[ $type ] ) ) {
					self::$data[ $plan_id ][ $type ][ $prop ] = $unserialized_metadata[ $type ] ;
				}
			}
		}

		return isset( self::$data[ $plan_id ][ $type ][ $prop ] ) ? self::$data[ $plan_id ][ $type ][ $prop ] : ( $default ? self::get_default_prop( $prop ) : '' ) ;
	}
}
