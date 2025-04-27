<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Subscription Shipping Frequency Notification Email.
 *
 * An email will be sent to the customer when a shipping fulfillment order is created. It contains the shipping fulfillment order details.
 *
 * @class ENR_Email_Customer_Shipping_Frequency_Notification
 * @extends ENR_Abstract_Email
 */
class ENR_Email_Customer_Shipping_Frequency_Notification extends ENR_Abstract_Email {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = ENR_PREFIX . 'customer_shipping_frequency_notification';
		$this->customer_email = true;
		$this->title          = __( 'Subscription Shipping Frequency Notification', 'enhancer-for-woocommerce-subscriptions' );
		$this->description    = __( 'Subscription shipping frequency notification emails are sent to the previously purchased customers of the subscription product at the time of their next renewal, when the subscription product is enabled with Separate shipping cycle for old subscriptions.', 'enhancer-for-woocommerce-subscriptions' );
		$this->heading        = __( 'Subscription Shipping Frequency Notification', 'enhancer-for-woocommerce-subscriptions' );
		$this->subject        = __( 'Subscription Shipping Frequency Notification', 'enhancer-for-woocommerce-subscriptions' );
		$this->template_html  = 'emails/customer-shipping-frequency-notification.php';
		$this->template_plain = 'emails/plain/customer-shipping-frequency-notification.php';

		add_action( 'enr_wc_subscriptions_shipping_fulfilment_enabled_for_old_orders_notification', array( $this, 'trigger' ) );

		// Call parent constructor
		parent::__construct();
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param WC_Subscription|false $subscription Subscription object.
	 */
	public function trigger( $subscription ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription );

		if ( $subscription ) {
			$this->object    = $subscription;
			$this->recipient = $this->object->get_billing_email();
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->wpml_switch_language();
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}
}

return new ENR_Email_Customer_Shipping_Frequency_Notification();
