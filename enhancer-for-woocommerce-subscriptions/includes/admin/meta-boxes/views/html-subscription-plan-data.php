<?php
defined( 'ABSPATH' ) || exit ;

wp_nonce_field( 'enr_save_data', 'enr_save_meta_nonce' ) ;
?>
<table class="widefat striped enr-subscription-plan-data">
	<tr class="enr-subscription-plan-type-field-row">
		<td>
			<label for="plan_type"><?php esc_html_e( 'Plan type', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<select name="_plan_type">
				<?php foreach ( _enr_get_subscription_plan_types() as $value => $label ) { ?>
					<option value="<?php echo esc_attr( $value ) ; ?>" <?php selected( $value, $chosen_type, true ) ; ?>><?php echo esc_html( $label ) ; ?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr class="enr-plan-fields-row enr-subscription-plan-signup-fee-fields-row">
		<td>
			<label for="subscription_sign_up_fee">
				<?php
				/* translators: %s is a currency symbol */
				printf( esc_html__( 'Sign-up fee (%s)', 'enhancer-for-woocommerce-subscriptions' ), esc_html( get_woocommerce_currency_symbol() ) ) ;
				?>
			</label>
		</td>
		<td>
			<input class="wc_input_price" name="_subscription_sign_up_fee[predefined]" type="text" placeholder="<?php echo esc_html_x( 'e.g. 9.90', 'example price', 'enhancer-for-woocommerce-subscriptions' ) ; ?>" step="any" min="0" value="<?php echo esc_attr( $predefined_chosen_sign_up_fee ) ; ?>">
		</td>
	</tr>
	<tr class="enr-plan-fields-row enr-subscription-plan-trial-fields-row">
		<td>
			<label for="subscription_trial_length"><?php esc_html_e( 'Free trial', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<input name="_subscription_trial_length[predefined]" type="number" min="0" value="<?php echo esc_attr( $predefined_chosen_trial_length ) ; ?>" />
			<select name="_subscription_trial_period[predefined]">
				<?php foreach ( wcs_get_available_time_periods() as $value => $label ) { ?>
					<option value="<?php echo esc_attr( $value ) ; ?>" <?php selected( $value, $predefined_chosen_trial_period, true ) ; ?>><?php echo esc_html( $label ) ; ?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr class="enr-predefined-plan-fields-row enr-subscription-predefined-plan-price-fields-row">
		<td>
			<label for="subscription_price">
				<?php
				/* translators: %s is a currency symbol */
				printf( esc_html__( 'Subscription price (%s)', 'enhancer-for-woocommerce-subscriptions' ), esc_html( get_woocommerce_currency_symbol() ) ) ;
				?>
			</label>
		</td>
		<td>
			<input name="_subscription_price[predefined]" type="number" step="0.01" min="0" value="<?php echo esc_attr( $predefined_chosen_price ) ; ?>">
			<span class="description"><?php esc_html_e( '% of Regular Price/Sale Price', 'enhancer-for-woocommerce-subscriptions' ) ; ?></span>
			<select id="subscription_period_interval" name="_subscription_period_interval[predefined]">
				<?php foreach ( wcs_get_subscription_period_interval_strings() as $value => $label ) { ?>
					<option value="<?php echo esc_attr( $value ) ; ?>" <?php selected( $value, $predefined_chosen_interval, true ) ; ?>><?php echo esc_html( $label ) ; ?></option>
				<?php } ?>
			</select>
			<select id="subscription_period" name="_subscription_period[predefined]">
				<?php foreach ( wcs_get_subscription_period_strings() as $value => $label ) { ?>
					<option value="<?php echo esc_attr( $value ) ; ?>" <?php selected( $value, $predefined_chosen_period, true ) ; ?>><?php echo esc_html( $label ) ; ?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr class="enr-predefined-plan-fields-row enr-subscription-predefined-plan-sync-fields-row">
		<td>
			<label for="subscription_payment_sync_date"><?php esc_html_e( 'Synchronise renewals', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<select id="subscription_payment_sync_date" name="_subscription_payment_sync_date[predefined]" style="<?php echo esc_attr( $display_sync_week_month_select ) ; ?>">
				<?php foreach ( WC_Subscriptions_Synchroniser::get_billing_period_ranges( $predefined_chosen_period ) as $value => $label ) { ?>
					<option value="<?php echo esc_attr( $value ) ; ?>" <?php selected( $value, $predefined_chosen_sync_date, true ) ; ?>><?php echo esc_html( $label ) ; ?></option>
				<?php } ?>
			</select>     
			<span class="subscription-year-sync-wrap" style="<?php echo esc_attr( $display_sync_annual_select ) ; ?>">
				<select id="subscription_payment_sync_date_month" name="_subscription_payment_sync_date_month[predefined]">
					<?php foreach ( WC_Subscriptions_Synchroniser::get_year_sync_options() as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ) ; ?>" <?php selected( $value, $sync_payment_month, true ) ; ?>><?php echo esc_html( $label ) ; ?></option>
					<?php } ?>
				</select>

				<?php $daysInMonth = $sync_payment_month ? gmdate( 't', wc_string_to_timestamp( "2001-{$sync_payment_month}-01" ) ) : 0 ; ?>
				<input type="number" id="subscription_payment_sync_date_day" name="_subscription_payment_sync_date_day[predefined]" value="<?php echo esc_attr( $sync_payment_day ) ; ?>" placeholder="<?php echo esc_attr_x( 'Day', 'input field placeholder for day field for annual subscriptions', 'enhancer-for-woocommerce-subscriptions' ) ; ?>" step="1" min="<?php echo esc_attr( min( 1, $daysInMonth ) ) ; ?>" max="<?php echo esc_attr( $daysInMonth ) ; ?>" <?php disabled( 0, $sync_payment_month, true ) ; ?> />
			</span>
		</td>
	</tr>        
	<tr class="enr-predefined-plan-fields-row enr-subscription-predefined-plan-length-fields-row">
		<td>
			<label for="subscription_length"><?php esc_html_e( 'Expire after', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<select name="_subscription_length[predefined]">
				<?php foreach ( wcs_get_subscription_ranges( $predefined_chosen_period ) as $value => $label ) { ?>
					<option value="<?php echo esc_attr( $value ) ; ?>" <?php selected( $value, $predefined_chosen_length, true ) ; ?>><?php echo esc_html( $label ) ; ?></option>
				<?php } ?>
			</select>
		</td>
	</tr>        
	<tr class="enr-predefined-plan-fields-row enr-subscription-predefined-plan-shipping-cycle-fields-row">
		<td>
			<label for="seperate_shipping_cycle"><?php esc_html_e( 'Separate shipping cycle', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'Enabling this option creates separate shipping fulfilment orders for the subscription', 'enhancer-for-woocommerce-subscriptions' ) ; ?>"></span>
			<input type="checkbox" name="_enable_seperate_shipping_cycle[predefined]" id="enable_seperate_shipping_cycle" value="yes" <?php checked( 'yes', $predefined_seperate_shipping_cycle_enabled, true ) ; ?>/>
		</td>
	</tr>
	<tr class="enr-predefined-plan-fields-row enr-subscription-predefined-plan-shipping-cycle-fields-row">
		<td>
			<label for="shipping_frequency_every"><?php esc_html_e( 'Shipping Frequency Every', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'Choose the interval to create the shipping fulfilment order', 'enhancer-for-woocommerce-subscriptions' ) ; ?>"></span>
			<input type="number" name="_shipping_period_interval[predefined]" id="shipping_period_interval" value="<?php echo esc_attr( $predefined_chosen_shipping_period_interval ) ; ?>" min="0"/>
			<select name="_shipping_period[predefined]" id="shipping_period">
				<?php foreach ( wcs_get_subscription_period_strings() as $value => $label ) { ?>
					<option value="<?php echo esc_attr( $value ) ; ?>" <?php selected( $value, $predefined_chosen_shipping_period, true ) ; ?>><?php echo esc_html( $label ) ; ?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr class="enr-predefined-plan-fields-row enr-subscription-predefined-plan-allow-cancelling-fields-row">
		<td>
			<label for="allow_cancelling_to"><?php esc_html_e( 'Allow cancelling', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<select name="_allow_cancelling_to[predefined]" id="allow_cancelling_to">
				<option value="use-storewide" <?php selected( 'use-storewide', $predefined_allow_cancelling_to, true ) ; ?>><?php esc_html_e( 'Inherit storewide settings', 'enhancer-for-woocommerce-subscriptions' ) ; ?></option>
				<option value="override-storewide" <?php selected( 'override-storewide', $predefined_allow_cancelling_to, true ) ; ?>><?php esc_html_e( 'Override storewide settings', 'enhancer-for-woocommerce-subscriptions' ) ; ?></option>
			</select>
		</td>
	</tr>
	<tr class="enr-predefined-plan-fields-row enr-subscription-predefined-plan-allow-cancelling-fields-row">
		<td>
			<label for="allow_cancelling_after"><?php esc_html_e( 'Allow cancelling after', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Set 0 to allow subscribers to cancel immediately. If empty, customers will not be allowed to cancel.', 'enhancer-for-woocommerce-subscriptions' ) ; ?>"></span>
			<input type="number" name="_allow_cancelling_after[predefined]" id="allow_cancelling_after" value="<?php echo esc_attr( $predefined_allow_cancelling_after ) ; ?>"/>
			<span class="description"><?php esc_html_e( 'day(s) from the subscription start date.', 'enhancer-for-woocommerce-subscriptions' ) ; ?></span>
		</td>
	</tr>
	<tr class="enr-predefined-plan-fields-row enr-subscription-predefined-plan-allow-cancelling-fields-row">
		<td>
			<label for="allow_cancelling_after_due"><?php esc_html_e( 'Allow cancelling after each renewal', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Set 0 to allow subscribers to cancel immediately. If empty, customers will not be allowed to cancel.', 'enhancer-for-woocommerce-subscriptions' ) ; ?>"></span>
			<input type="number" name="_allow_cancelling_after_due[predefined]" id="allow_cancelling_after_due" value="<?php echo esc_attr( $predefined_allow_cancelling_after_due ) ; ?>"/>
			<span class="description"><?php esc_html_e( 'day(s) from the subscription renewal date.', 'enhancer-for-woocommerce-subscriptions' ) ; ?></span>
		</td>
	</tr>
	<tr class="enr-predefined-plan-fields-row enr-subscription-predefined-plan-allow-cancelling-fields-row">
		<td>
			<label for="allow_cancelling_before_due"><?php esc_html_e( 'Prevent cancelling', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'If left empty or set 0, subscribers will not be prevented from cancelling their subscriptions until the renewal date.', 'enhancer-for-woocommerce-subscriptions' ) ; ?>"></span>
			<input type="number" name="_allow_cancelling_before_due[predefined]" id="allow_cancelling_before_due" value="<?php echo esc_attr( $predefined_allow_cancelling_before_due ) ; ?>"/>
			<span class="description"><?php esc_html_e( 'day(s) before the subscription renewal date.', 'enhancer-for-woocommerce-subscriptions' ) ; ?></span>
		</td>
	</tr>
	<tr class="enr-userdefined-plan-fields-row enr-subscription-userdefined-plan-price-field-row">
		<td>
			<label for="subscription_price">
				<?php
				/* translators: %s is a currency symbol */
				printf( esc_html__( 'Subscription price (%s)', 'enhancer-for-woocommerce-subscriptions' ), esc_html( get_woocommerce_currency_symbol() ) ) ;
				?>
			</label>
		</td>
		<td>
			<input name="_subscription_price[userdefined]" type="number" step="0.01" min="0" value="<?php echo esc_attr( $userdefined_chosen_price ) ; ?>">
			<span class="description"><?php esc_html_e( '% of Regular Price/Sale Price', 'enhancer-for-woocommerce-subscriptions' ) ; ?></span>
		</td>
	</tr>
	<tr class="enr-userdefined-plan-fields-row enr-subscription-userdefined-plan-period-field-row">
		<td>
			<label for="subscription_period"><?php esc_html_e( 'Subscription Period Options to be Shown for Customer', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<select name="_subscription_period[userdefined][]" multiple="multiple" class="wc-enhanced-select" style="width: 40%">
				<?php
				foreach ( wcs_get_subscription_period_strings() as $value => $label ) {
					?>
					<option value="<?php echo esc_attr( $value ) ; ?>"
					<?php
					if ( is_array( $userdefined_chosen_period ) ) {
						selected( in_array( ( string ) $value, $userdefined_chosen_period, true ), true ) ;
					}
					?>
							><?php echo esc_html( $label ) ; ?></option>
							<?php
				}
				?>
			</select>
		</td>
	</tr>
	<tr class="enr-userdefined-plan-fields-row enr-subscription-userdefined-plan-period-interval-field-row">
		<td>
			<label for="subscription_period_interval"><?php esc_html_e( 'Subscription Billing Interval Options to be Shown for Customer', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<table>
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th><?php esc_html_e( 'Min', 'enhancer-for-woocommerce-subscriptions' ) ; ?></th>
						<th><?php esc_html_e( 'Max', 'enhancer-for-woocommerce-subscriptions' ) ; ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( wcs_get_subscription_period_strings() as $period => $label ) { ?>
						<tr>
							<td>
								<span>
									<?php
									/* translators: 1: period label */
									printf( esc_html__( 'For %s(s)', 'enhancer-for-woocommerce-subscriptions' ), esc_html( $label ) ) ;
									?>
								</span>
							</td>
							<?php foreach ( array( 'min', 'max' ) as $range ) { ?>
								<td>
									<select name="_subscription_period_interval[userdefined][<?php echo esc_attr( $range ) ; ?>][<?php echo esc_attr( $period ) ; ?>]">
										<?php
										foreach ( array_keys( wcs_get_subscription_period_interval_strings() ) as $value ) {
											?>
											<option value="<?php echo esc_attr( $value ) ; ?>"
											<?php
											if ( isset( $userdefined_chosen_interval[ $range ][ $period ] ) ) {
												selected( $value, ( string ) $userdefined_chosen_interval[ $range ][ $period ] ) ;
											}
											?>
													>
														<?php if ( 1 === $value ) { ?>
															<?php
															/* translators: 1: period label */
															printf( esc_html__( 'every %1$s', 'enhancer-for-woocommerce-subscriptions' ), esc_html( $label ) ) ;
															?>
														<?php } else { ?>
															<?php
															/* translators: 1: period value 2: period label */
															printf( esc_html__( 'every %1$s %2$s', 'enhancer-for-woocommerce-subscriptions' ), esc_html( _enr_get_number_suffix( $value ) ), esc_html( $label ) ) ;
															?>
														<?php } ?>
											</option>
											<?php
										}
										?>
									</select>
								</td>
							<?php } ?>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</td>
	</tr>
	<tr class="enr-userdefined-plan-fields-row enr-subscription-userdefined-plan-length-field-row">
		<td>
			<label for="subscription_length"><?php esc_html_e( 'Expire After Options to be Shown for Customer', 'enhancer-for-woocommerce-subscriptions' ) ; ?></label>
		</td>
		<td>
			<table>
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th><?php esc_html_e( 'Min', 'enhancer-for-woocommerce-subscriptions' ) ; ?></th>
						<th><?php esc_html_e( 'Max', 'enhancer-for-woocommerce-subscriptions' ) ; ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( wcs_get_subscription_period_strings() as $period => $label ) { ?>
						<tr>
							<td>
								<span>
									<?php
									/* translators: 1: period label */
									printf( esc_html__( 'For %s(s)', 'enhancer-for-woocommerce-subscriptions' ), esc_html( $label ) ) ;
									?>
								</span>
							</td>
							<?php foreach ( array( 'min', 'max' ) as $range ) { ?>
								<td>
									<select name="_subscription_length[userdefined][<?php echo esc_attr( $range ) ; ?>][<?php echo esc_attr( $period ) ; ?>]">
										<?php
										foreach ( wcs_get_subscription_ranges( $period ) as $value => $label ) {
											?>
											<option value="<?php echo esc_attr( $value ) ; ?>"
											<?php
											if ( isset( $userdefined_chosen_length[ $range ][ $period ] ) ) {
												selected( $value, ( string ) $userdefined_chosen_length[ $range ][ $period ] ) ;
											}
											?>
													><?php echo esc_html( $label ) ; ?></option>
													<?php
										}
										?>
									</select>
								</td>
							<?php } ?>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</td>
	</tr>
</table>
