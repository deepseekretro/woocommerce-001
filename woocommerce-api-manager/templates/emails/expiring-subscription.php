<?php
/**
 * Expiring AM Subscription email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/expiring-subscription.php.
 *
 * HOWEVER, on occasion WooCommerce API Manager will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 *
 * @since 2.5.0
 *
 * @version 3.4.1
 *
 * @var WC_Order $order order object
 * @var WC_Email $email email object
 * @var object $api_resource API Resource object
 * @var string $email_heading heading
 * @var string $additional_content additional content
 * @var bool $sent_to_admin sent to admin
 * @var bool $plain_text plain text
 */
defined( 'ABSPATH' ) or exit;

/**
 * Executes the e-mail header.
 *
 * @hooked WC_Emails::email_header() Output the email header
 *
 * @since 2.5.0
 *
 * @param string $email_heading heading
 * @param WC_Email $email WC_Email instance
 */
do_action( 'woocommerce_email_header', $email_heading, $email );

?>
<p>
	<?php

	/* translators: Placeholder: %s - First name */
	echo sprintf( esc_html__( 'Hi %s,', 'woocommerce-api-manager' ), $order->get_billing_first_name() ); // phpcs:ignore

	?>
</p>
<p>
	<?php

	// phpcs:ignore
	printf(
		/* translators: Placeholders: %1$s - Opening link <a> tag, %2$s - Closing link </a> tag */
		esc_html__( '%1$sRenew your API Product%2$s.', 'woocommerce-api-manager' ),
		'<a href="' . esc_url( wc_get_endpoint_url( 'api-keys', '', wc_get_page_permalink( 'myaccount' ) ) ) . '">',
		'</a>'
	);

	?>
</p>
<p>
	<?php

	$is_expired           = WC_AM_ORDER_DATA_STORE()->is_time_expired( $api_resource->access_expires );
	$grace_period_expired = WC_AM_GRACE_PERIOD()->is_expired( $api_resource->api_resource_id );

	if ( $is_expired && ! $grace_period_expired ) :

		/* translators: Placeholder: %s - Expiration date */
		printf( esc_html__( 'The API Product is renewable until: %s', 'woocommerce-api-manager' ), WC_AM_FORMAT()->unix_timestamp_to_date( WC_AM_GRACE_PERIOD()->get_expiration( $api_resource->api_resource_id ) ) );

 // phpcs:ignore

	endif;
	?>
</p>
<?php

$discount = get_option( 'woocommerce_api_manager_manual_renewal_discount' );

if ( ! empty( $discount ) ) :

	/* translators: Placeholder: %s - Discount percentage */
	echo '<p>' . sprintf( esc_html__( 'If you renew before your API Product access expires you will get a %s discount.', 'woocommerce-api-manager' ), (string) $discount . '%' ) . '</p>';

 // phpcs:ignore

endif;

/**
 * Hook for the woocommerce_email_order_details.
 *
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 *
 * @since  2.5.0
 *
 * @param WC_Order $order order object
 * @param bool $sent_to_admin sent to admin
 * @param bool $plain_text plain text email
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * Hook for the woocommerce_email_order_meta.
 *
 * @hooked WC_Emails::order_meta() Shows order meta data.
 *
 * @since 2.5.0
 *
 * @param WC_Order $order order object
 * @param bool $sent_to_admin sent to admin
 * @param bool $plain_text plain text email
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/**
 * Hook for woocommerce_email_customer_details.
 *
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 *
 * @since 2.5.0
 *
 * @param WC_Order $order order object
 * @param bool $sent_to_admin sent to admin
 * @param bool $plain_text plain text email
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/**
 * Executes the email footer.
 *
 * @hooked WC_Emails::email_footer() Output the email footer
 *
 * @since 2.5.0
 *
 * @param WC_Email $email WC_Email instance
 */
do_action( 'woocommerce_email_footer', $email );
