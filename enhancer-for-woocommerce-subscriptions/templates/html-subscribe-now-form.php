<?php
/**
 * Subscribe Now Form.
 *
 * This template can be overridden by copying it to yourtheme/enhancer-for-woocommerce-subscriptions/html-subscribe-now-form.php.
 */
defined( 'ABSPATH' ) || exit ;
?>
<table class="shop_table enr-subscribe-now-wrapper">
	<tbody>
		<tr class="enr-subscribe-now-handle-row">
			<td>
				<p>
					<?php if ( $force_subscribe ) { ?>
						<input type="checkbox" class="enr-subscribe-forced" id="enr_subscribe_now" name="enr_subscribed" value="yes" checked="checked" readonly="readonly"/>
					<?php } else { ?>
						<input type="checkbox" class="enr-subscribe-now" id="enr_subscribe_now" name="enr_subscribed" value="yes" <?php checked( $is_subscribed, true, true ) ; ?>/>
					<?php } ?>
					<label for="enr_subscribe_now"><?php echo esc_html( $subscribe_label ) ; ?></label>
				</p>
				<?php if ( $is_subscribed ) { ?>
					<p>
						<?php if ( ! empty( $available_plans ) ) { ?>
							<select id="enr_subscribe_plans" name="enr_subscribed_plan">
								<optgroup label="<?php esc_attr_e( 'Select Plan', 'enhancer-for-woocommerce-subscriptions' ) ; ?>">
									<?php foreach ( $available_plans as $plan_id ) { ?>
										<option value="<?php echo esc_attr( $plan_id ) ; ?>" <?php selected( $plan_id, $chosen_plan, true ) ; ?>><?php echo wp_kses_post( get_the_title( $plan_id ) ) ; ?></option>
									<?php } ?>
								</optgroup>
							</select>
						<?php } else { ?>
							<span><?php esc_html_e( 'No plans available', 'enhancer-for-woocommerce-subscriptions' ) ; ?></span>
						<?php } ?>
					</p>
					<?php if ( ! empty( $subscribed_price_string ) ) { ?>
						<p><?php echo wp_kses_post( $subscribed_price_string ) ; ?></p>
					<?php } ?>
				<?php } else { ?>
					<input type="hidden" name="enr_subscribed_plan" value="<?php echo esc_attr( $default_plan ) ; ?>"/>
				<?php } ?>
				<input type="hidden" name="enr_subscribed_key" value="<?php echo esc_attr( $subscribed_key ) ; ?>"/>
				<?php wp_nonce_field( $subscribe_type, 'enr_subscribe_now_type_nonce' ) ; ?>
			</td>        
		</tr>
		<?php if ( $is_subscribed && 'userdefined' === $subscribed_plan_type ) { ?>
			<tr class="enr-subscribe-now-period-interval-row">
				<td>
					<label for="subscribe_interval"><?php esc_html_e( 'Subscription billing interval', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
				</td>
				<td>
					<select id="enr_subscribe_period_interval" name="enr_subscribed_period_interval">
						<?php foreach ( wcs_get_subscription_period_interval_strings() as $value => $label ) { ?>
							<?php if ( isset( $interval_to_subscribe[ 'min' ][ $chosen_period ], $interval_to_subscribe[ 'max' ][ $chosen_period ] ) && $value >= $interval_to_subscribe[ 'min' ][ $chosen_period ] && $value <= $interval_to_subscribe[ 'max' ][ $chosen_period ] ) { ?>
								<option value="<?php echo esc_attr( $value ) ; ?>" <?php selected( $value, $chosen_interval, true ) ; ?>><?php echo esc_html( $label ) ; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr class="enr-subscribe-now-period-row">
				<td>
					<label for="subscribe_period"><?php esc_html_e( 'Subscription period', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
				</td>
				<td>
					<select id="enr_subscribe_period" name="enr_subscribed_period">
						<?php foreach ( wcs_get_subscription_period_strings() as $value => $label ) { ?>
							<?php if ( in_array( $value, ( array ) $period_to_subscribe ) ) { ?>
								<option value="<?php echo esc_attr( $value ) ; ?>" <?php selected( $value, $chosen_period, true ) ; ?>><?php echo esc_html( $label ) ; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr class="enr-subscribe-now-length-row">
				<td>
					<label for="subscribe_length"><?php esc_html_e( 'Expire after', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
				</td>
				<td>
					<select id="enr_subscribe_length" name="enr_subscribed_length">
						<?php
						if ( isset( $length_to_subscribe[ 'min' ][ $chosen_period ], $length_to_subscribe[ 'max' ][ $chosen_period ] ) ) {
							$add_never_expire = true ;

							foreach ( _enr_get_subscription_length_ranges( $chosen_period, $chosen_interval ) as $value => $label ) {
								if ( $value >= $length_to_subscribe[ 'min' ][ $chosen_period ] ) {
									if ( '0' === $length_to_subscribe[ 'max' ][ $chosen_period ] ) {
										$add_never_expire = true ;
										?>
										<option value="<?php echo esc_attr( $value ) ; ?>" <?php selected( $value, $chosen_length, true ) ; ?>><?php echo esc_html( $label ) ; ?></option>
										<?php
									} else if ( $value <= $length_to_subscribe[ 'max' ][ $chosen_period ] ) {
										$add_never_expire = false ;
										?>
										<option value="<?php echo esc_attr( $value ) ; ?>" <?php selected( $value, $chosen_length, true ) ; ?>><?php echo esc_html( $label ) ; ?></option>
										<?php
									}
								}
							}

							if ( $add_never_expire ) {
								?>
								<option value="0" <?php selected( '0', $chosen_length, true ) ; ?>><?php esc_html_e( 'Never expire', 'enhancer-for-woocommerce-subscriptions' ) ; ?></option>
								<?php
							}
						}
						?>
					</select>
				</td>
			</tr>	
		<?php } ?>
	</tbody>
</table>
