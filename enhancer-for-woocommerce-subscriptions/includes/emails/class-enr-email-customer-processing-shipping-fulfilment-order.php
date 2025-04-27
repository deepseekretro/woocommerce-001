<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Processing Shipping Fulfillment Order Email.
 *
 * An email will be sent to the customer when a shipping fulfillment order is created. It contains the shipping fulfillment order details.
 *
 * @class ENR_Email_Customer_Processing_Shipping_Fulfilment_Order
 * @extends ENR_Abstract_Email
 */
class ENR_Email_Customer_Processing_Shipping_Fulfilment_Order extends ENR_Abstract_Email {

	/**
	 * Get an order.
	 * 
	 * @var WC_Order
	 */
	public $order;

	/**
	 * Gets the shipping fulfilment orders count.
	 * 
	 * @var int
	 */
	public $shipping_fulfilment_orders_count;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = ENR_PREFIX . 'customer_processing_shipping_fulfilment_order';
		$this->customer_email = true;
		$this->title          = __( 'Processing Shipping Fulfillment Order', 'enhancer-for-woocommerce-subscriptions' );
		$this->description    = __( 'Processing shipping fulfillment order emails are sent to the customers(subscribers) when a shipping fulfillment order is created. It contains the shipping fulfillment order details.', 'enhancer-for-woocommerce-subscriptions' );
		$this->heading        = __( 'Your subscription shipping fulfillment order is being processed', 'enhancer-for-woocommerce-subscriptions' );
		$this->subject        = __( 'Your {blogname} subscription shipping fulfillment order is being processed', 'enhancer-for-woocommerce-subscriptions' );
		$this->template_html  = 'emails/customer-processing-shipping-fulfilment-order.php';
		$this->template_plain = 'emails/plain/customer-processing-shipping-fulfilment-order.php';

		add_action( 'enr_wc_subscriptions_shipping_fulfilment_order_created_notification', array( $this, 'trigger' ), 10, 3 );

		// Call parent constructor
		parent::__construct();
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param WC_Subscription|false $subscription Subscription object.
	 */
	public function trigger( $subscription, $shipping_fulfilment_order, $shipping_fulfilment_orders_count ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription );

		if ( $subscription ) {
			$this->object                           = $subscription;
			$this->order                            = $shipping_fulfilment_order;
			$this->recipient                        = $this->object->get_billing_email();
			$this->shipping_fulfilment_orders_count = $shipping_fulfilment_orders_count;
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->wpml_switch_language();
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'subscription'                     => $this->object,
			'order'                            => $this->order,
			'shipping_fulfilment_orders_count' => $this->shipping_fulfilment_orders_count,
			'email_heading'                    => $this->get_heading(),
			'additional_content'               => is_callable( array( $this, 'get_additional_content' ) ) ? $this->get_additional_content() : '',
			'sent_to_admin'                    => false,
			'plain_text'                       => false,
			'email'                            => $this,
				), '', $this->template_base );
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'subscription'                     => $this->object,
			'order'                            => $this->order,
			'shipping_fulfilment_orders_count' => $this->shipping_fulfilment_orders_count,
			'email_heading'                    => $this->get_heading(),
			'additional_content'               => is_callable( array( $this, 'get_additional_content' ) ) ? $this->get_additional_content() : '',
			'sent_to_admin'                    => false,
			'plain_text'                       => true,
			'email'                            => $this,
				), '', $this->template_base );
	}
}

return new ENR_Email_Customer_Processing_Shipping_Fulfilment_Order();
