<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Subscription Price Updated Email.
 *
 * An email will be sent to the customer when the subscription price of the product they have purchased is updated and the customer wants to pay the updated price for their renewals.
 *
 * @class ENR_Email_Customer_Subscription_Price_Updated
 * @extends ENR_Abstract_Email
 */
class ENR_Email_Customer_Subscription_Price_Updated extends ENR_Abstract_Email {

	/**
	 * Email supports.
	 *
	 * @var array Supports
	 */
	public $supports = array( 'multiple_content' );

	/**
	 * Gets price changed items.
	 * 
	 * @var array
	 */
	public $price_changed_items;

	/**
	 * Strings to find/replace in multiple content supported email.
	 *
	 * @var array Supports
	 */
	public $multiple_content_placeholders = array(
		'{view_subscription_url}'              => '',
		'{renewal_amount}'                     => '',
		'{subscription_price_changed_details}' => '',
		'{subscription_details}'               => '',
		'{customer_addresses}'                 => '',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = ENR_PREFIX . 'customer_subscription_price_updated';
		$this->customer_email = true;
		$this->title          = __( 'Subscription Price Updated', 'enhancer-for-woocommerce-subscriptions' );
		$this->description    = __( 'Subscription price updated emails are sent to the customers(subscribers) when the price of the subscribed product has been modified. Your customers(subscribers) will have to pay the updated price for their upcoming renewals.', 'enhancer-for-woocommerce-subscriptions' );
		$this->heading        = __( 'Your subscription price is updated', 'enhancer-for-woocommerce-subscriptions' );
		$this->subject        = __( 'Your {blogname} subscription price updated', 'enhancer-for-woocommerce-subscriptions' );
		$this->template_html  = 'emails/customer-subscription-price-updated.php';
		$this->template_plain = 'emails/plain/customer-subscription-price-updated.php';

		add_action( 'enr_wc_subscriptions_remind_subscription_price_changed_before_renewal_notification', array( $this, 'trigger' ), 10, 3 );

		// Call parent constructor
		parent::__construct();
	}

	/**
	 * Get default content to show as main email content.
	 *
	 * @return string
	 */
	public function get_default_content() {
		return __( "The subscription price for your subscription {view_subscription_url} has been updated. You have to pay the updated price for the future renewals. Here's the details of your subscription. {subscription_price_changed_details} {customer_addresses}", 'enhancer-for-woocommerce-subscriptions' );
	}

	/**
	 * Collect multiple content placeholders.
	 */
	protected function collect_multiple_content_placeholders() {
		parent::collect_multiple_content_placeholders();

		ob_start();
		ENR_Emails::subscription_price_changed_details( $this->object, $this->price_changed_items, false, 'plain' === $this->get_email_type(), $this );
		$this->multiple_content_placeholders[ '{subscription_price_changed_details}' ] = ob_get_clean();
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param WC_Subscription|false $subscription Subscription object.
	 * @param int|null $day_to_remind
	 */
	public function trigger( $subscription, $price_changed_items, $day_to_remind = null ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription );

		if ( $subscription ) {
			$this->object              = $subscription;
			$this->recipient           = $this->object->get_billing_email();
			$this->price_changed_items = $price_changed_items;
			$this->email_template_id   = is_numeric( $day_to_remind ) ? _enr_get_mapped_email_template_id( $this->id, $day_to_remind, $this->object ) : null;
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
			'subscription'        => $this->object,
			'price_changed_items' => $this->price_changed_items,
			'from_price_string'   => '',
			'to_price_string'     => '',
			'email_heading'       => $this->get_heading(),
			'additional_content'  => is_callable( array( $this, 'get_additional_content' ) ) ? $this->get_additional_content() : '',
			'sent_to_admin'       => false,
			'plain_text'          => false,
			'email'               => $this,
				), '', $this->template_base );
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'subscription'        => $this->object,
			'price_changed_items' => $this->price_changed_items,
			'from_price_string'   => '',
			'to_price_string'     => '',
			'email_heading'       => $this->get_heading(),
			'additional_content'  => is_callable( array( $this, 'get_additional_content' ) ) ? $this->get_additional_content() : '',
			'sent_to_admin'       => false,
			'plain_text'          => true,
			'email'               => $this,
				), '', $this->template_base );
	}
}

return new ENR_Email_Customer_Subscription_Price_Updated();
