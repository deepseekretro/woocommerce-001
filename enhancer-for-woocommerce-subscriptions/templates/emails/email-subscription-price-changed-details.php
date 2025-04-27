<?php
/**
 * Subscription price changed details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-subscription-price-changed-details.php.
 */
defined( 'ABSPATH' ) || exit ;

$text_align = is_rtl() ? 'right' : 'left' ;
?>
<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ) ; ?>;"><?php echo esc_html_x( 'New Price', 'table headings in notification email', 'enhancer-for-woocommerce-subscriptions' ) ; ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ) ; ?>;"><?php echo esc_html_x( 'Old Price', 'table headings in notification email', 'enhancer-for-woocommerce-subscriptions' ) ; ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $price_changed_items as $changed ) { ?>
				<tr>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ) ; ?>; vertical-align:middle;">
						<?php echo wp_kses_post( $changed[ 'to_string' ] ) ; ?>
					</td>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ) ; ?>; vertical-align:middle;">
						<?php echo wp_kses_post( $changed[ 'from_string' ] ) ; ?>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
