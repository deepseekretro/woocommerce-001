<?php
/**
 * Expiring AM Subscription email (plain text)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/expiring-subscription.php.
 *
 * HOWEVER, on occasion WooCommerce API Manager will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 *
 * @version 3.4.1
 */
defined( 'ABSPATH' ) or exit;

/**
 * @var WC_Order $order
 * @var string $email_heading
 * @var string $additional_content
 * @var bool $sent_to_admin
 * @var bool $plain_text
 * @var object $api_resource
 * @var WC_Email $email
 */
echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: Placeholder: %s - Customer first name */
echo esc_html( sprintf( __( 'Hi %s,', 'woocommerce-api-manager' ), esc_html( $order->get_billing_first_name() ) ) );
echo "\n\n";

$item_quantity = 1;

if ( $api_resource->refund_qty < $api_resource->item_qty ) {
	$item_quantity = $api_resource->item_qty - $api_resource->refund_qty;
}

/* translators: Placeholder: %s - Product title */
echo esc_html( sprintf( __( 'An access renewal link for your API Product has been prepared for you on %s. Use this link to login to your account to renew the API Product: ', 'woocommerce-api-manager' ), esc_html( get_bloginfo( 'name', 'display' ) ) ) );
echo esc_url( wc_get_endpoint_url( 'api-keys', '', wc_get_page_permalink( 'myaccount' ) ) ) . "\n\n";

$is_expired           = WC_AM_ORDER_DATA_STORE()->is_time_expired( $api_resource->access_expires );
$grace_period_expired = WC_AM_GRACE_PERIOD()->is_expired( $api_resource->api_resource_id );

if ( $is_expired && ! $grace_period_expired ) {
	echo esc_html( sprintf( __( 'The API Product is renewable until: %s', 'woocommerce-api-manager' ), WC_AM_FORMAT()->unix_timestamp_to_date( WC_AM_GRACE_PERIOD()->get_expiration( $api_resource->api_resource_id ) ) ) );
	echo "\n\n";
}

$discount = get_option( 'woocommerce_api_manager_manual_renewal_discount' );

if ( ! empty( $discount ) ) {
	echo esc_html( sprintf( __( 'If you renew before your API Product access expires you will get a %s discount.', 'woocommerce-api-manager' ), $discount . '%' ) );
	echo "\n\n";
}

/**
 * Hook for the woocommerce_email_order_details.
 *
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 *
 * @since  2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n----------------------------------------\n\n";

/**
 * Hook for the woocommerce_email_order_meta.
 *
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/**
 * Hook for woocommerce_email_customer_details
 *
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n\n----------------------------------------\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
