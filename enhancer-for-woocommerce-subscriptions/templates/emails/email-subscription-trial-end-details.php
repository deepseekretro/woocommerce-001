<?php
/**
 * Subscription trial end details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-subscription-trial-end-details.php.
 */
defined( 'ABSPATH' ) || exit ;

$text_align = is_rtl() ? 'right' : 'left' ;
?>
<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ) ; ?>;"><?php esc_html_e( 'Subscription', 'enhancer-for-woocommerce-subscriptions' ) ; ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ) ; ?>;"><?php echo esc_html_x( 'Price', 'table headings in notification email', 'enhancer-for-woocommerce-subscriptions' ) ; ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ) ; ?>;"><?php echo esc_html_x( 'Trial End Date', 'table headings in notification email', 'enhancer-for-woocommerce-subscriptions' ) ; ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="td" width="1%" style="text-align:<?php echo esc_attr( $text_align ) ; ?>; vertical-align:middle;">
					<a href="<?php echo esc_url( $subscription->get_view_order_url() ) ; ?>">#<?php echo esc_html( $subscription->get_order_number() ) ; ?></a>
				</td>
				<td class="td" style="text-align:<?php echo esc_attr( $text_align ) ; ?>; vertical-align:middle;">
					<?php echo wp_kses_post( $subscription->get_formatted_order_total() ) ; ?>
				</td>
				<td class="td" style="text-align:<?php echo esc_attr( $text_align ) ; ?>; vertical-align:middle;">
					<?php echo esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'trial_end', 'site' ) ) ) ; ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
