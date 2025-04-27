<?php
defined( 'ABSPATH' ) || exit;

/**
 * Enhancer for WooCommerce Subscriptions Admin.
 * 
 * @class ENR_Admin
 * @package Class
 */
class ENR_Admin {

	/**
	 * Init ENR_Admin.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_enqueues', 11 );
		add_filter( 'woocommerce_screen_ids', __CLASS__ . '::load_wc_enqueues' );
		add_action( 'woocommerce_product_options_general_product_data', __CLASS__ . '::admin_edit_subscription_product_fields' );
		add_action( 'woocommerce_product_options_general_product_data', __CLASS__ . '::admin_edit_product_fields' );
		add_action( 'woocommerce_variable_subscription_pricing', __CLASS__ . '::admin_edit_subscription_variation_fields', 10, 3 );
		add_action( 'woocommerce_product_after_variable_attributes', __CLASS__ . '::admin_edit_variation_fields', 10, 3 );
		add_action( 'woocommerce_product_options_advanced', __CLASS__ . '::admin_edit_subscription_product_advanced_fields', 11 );
		add_filter( 'woocommerce_subscription_settings', __CLASS__ . '::subscription_settings', 99 );
		add_filter( 'woocommerce_subscriptions_allow_switching_options', __CLASS__ . '::switching_settings' );
		add_action( 'woocommerce_admin_field_enr_cart_level_subscription_plans_selector', __CLASS__ . '::cart_level_subscription_plans_selector' );

		add_action( 'woocommerce_process_product_meta', __CLASS__ . '::save_subscription_meta' );
		add_action( 'woocommerce_save_product_variation', __CLASS__ . '::save_subscription_variation_meta', 10, 2 );
		add_action( 'woocommerce_update_options_subscriptions', __CLASS__ . '::update_subscription_settings' );
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueues() {
		global $post;
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		wp_register_script( 'enr-admin', ENR_URL . '/assets/js/admin.js', array( 'jquery', 'wc-backbone-modal', 'wc-enhanced-select' ), _enr()->get_version() );
		wp_register_style( 'enr-admin', ENR_URL . '/assets/css/admin.css', array( 'woocommerce_admin_styles' ), _enr()->get_version() );

		$billing_period_strings = WC_Subscriptions_Synchroniser::get_billing_period_ranges();
		wp_localize_script( 'enr-admin', 'enr_admin_params', array(
			'period'                                       => wcs_get_subscription_period_strings(),
			'preview_email_inputs_nonce'                   => wp_create_nonce( 'enr-collect-preview-email-inputs' ),
			'preview_email_nonce'                          => wp_create_nonce( 'enr-preview-email' ),
			'email_default_data'                           => ENR_Meta_Box_Subscription_Email_Template_Data::get_default_data(),
			'email_placeholders'                           => ENR_Meta_Box_Subscription_Email_Template_Data::get_placeholders(),
			'subscription_lengths'                         => wcs_get_subscription_ranges(),
			'sync_options'                                 => array(
				'week'  => $billing_period_strings[ 'week' ],
				'month' => $billing_period_strings[ 'month' ],
				'year'  => WC_Subscriptions_Synchroniser::get_year_sync_options(),
			),
			'back_to_all_subscription_plans_url'           => esc_url( admin_url( 'edit.php?post_type=enr_subsc_plan' ) ),
			'back_to_all_subscription_email_templates_url' => esc_url( admin_url( 'edit.php?post_type=enr_email_template' ) ),
			'back_to_all_label'                            => esc_attr__( 'Back to all', 'enhancer-for-woocommerce-subscriptions' ),
		) );

		wp_enqueue_script( 'enr-admin' );
		wp_enqueue_style( 'enr-admin' );

		if ( 'edit-enr_subsc_plan' === $screen_id ) {
			wp_enqueue_script( 'enr-post-ordering', ENR_URL . '/assets/js/post-ordering.js', array( 'jquery-ui-sortable' ), _enr()->get_version() );
		}

		if ( in_array( $screen_id, array( 'enr_email_template', 'enr_subsc_plan', 'edit-enr_email_template', 'edit-enr_subsc_plan' ) ) ) {
			wp_dequeue_script( 'autosave' );
		}
	}

	/**
	 * Add our screens to WC screens.
	 *
	 * @param array $screen_ids
	 * @return array
	 */
	public static function load_wc_enqueues( $screen_ids ) {
		global $typenow;

		if ( in_array( $typenow, array( 'enr_subsc_plan', 'enr_email_template' ) ) ) {
			$screen = get_current_screen();

			if ( $screen ) {
				$screen_ids[] = $screen->id;
			}
		}

		return $screen_ids;
	}

	/**
	 * Output the ENR fields on the admin page "Edit Product(Subscription)" -> "General".
	 */
	public static function admin_edit_subscription_product_fields() {
		global $post;
		include 'views/html-subscription-product-enr-options.php';
	}

	/**
	 * Output the ENR fields on the admin page "Edit Product" -> "General".
	 */
	public static function admin_edit_product_fields() {
		global $post;
		include 'views/html-product-enr-options.php';
	}

	/**
	 * Output the variation ENR fields on the admin page "Edit Product(Subscription)" -> "Variations".
	 */
	public static function admin_edit_subscription_variation_fields( $loop, $variation_data, $variation ) {
		include 'views/html-subscription-product-variation-enr-options.php';
	}

	/**
	 * Output the variation ENR fields on the admin page "Edit Product" -> "Variations".
	 */
	public static function admin_edit_variation_fields( $loop, $variation_data, $variation ) {
		include 'views/html-product-variation-enr-options.php';
	}

	/**
	 * Output the ENR fields on the admin page "Edit Product(Subscription)" -> "Advanced".
	 */
	public static function admin_edit_subscription_product_advanced_fields() {
		echo '<div class="options_group show_if_variable-subscription hidden">';
		woocommerce_wp_select(
				array(
					'id'      => '_enr_variable_subscription_limit_level',
					'label'   => __( 'Limit subscription level', 'enhancer-for-woocommerce-subscriptions' ),
					'options' => array(
						'product-level' => __( 'Product Level', 'enhancer-for-woocommerce-subscriptions' ),
						'variant-level' => __( 'Variant Level', 'enhancer-for-woocommerce-subscriptions' ),
					),
				)
		);
		echo '</div>';

		echo '<div class="options_group show_if_subscription show_if_variable-subscription hidden">';
		woocommerce_wp_checkbox(
				array(
					'id'          => '_enr_limit_trial_to_one',
					'label'       => __( 'Limit trial to one', 'enhancer-for-woocommerce-subscriptions' ),
					'description' => __( 'Restrict customers to use trial only once', 'enhancer-for-woocommerce-subscriptions' ),
				)
		);
		echo '</div>';
	}

	/**
	 * Return the array of our settings to WC Subscriptions.
	 * 
	 * @param array $settings
	 * @return array
	 */
	public static function subscription_settings( $settings ) {
		$button_options_end = wp_list_filter( $settings, array( 'id' => 'woocommerce_subscriptions_button_text', 'type' => 'sectionend' ) );

		array_splice( $settings, key( $button_options_end ), 0, array(
			array(
				'name'    => __( 'Subscribe Now Checkbox Text', 'enhancer-for-woocommerce-subscriptions' ),
				'id'      => ENR_PREFIX . '_subscribe_now_checkbox_text',
				'default' => __( 'Subscribe Now', 'enhancer-for-woocommerce-subscriptions' ),
				'type'    => 'text',
			),
		) );

		$renewal_options_end = wp_list_filter( $settings, array( 'id' => 'woocommerce_subscriptions_renewal_options', 'type' => 'sectionend' ) );
		array_splice( $settings, key( $renewal_options_end ), 0, array(
			array(
				'name'     => __( 'Subscription Price for Old Subscriptions', 'enhancer-for-woocommerce-subscriptions' ),
				'id'       => ENR_PREFIX . 'apply_old_subscription_price_as',
				'default'  => 'old-price',
				'type'     => 'select',
				'options'  => array(
					'old-price' => __( 'Old price', 'enhancer-for-woocommerce-subscriptions' ),
					'new-price' => __( 'New price', 'enhancer-for-woocommerce-subscriptions' ),
				),
				'desc_tip' => true,
				'desc'     => __( 'If the subscription price for products are updated and if you want to update new price for the old subscriptions which are renewed hereafter, then select "New price" option. The customers will be notified by email regarding the subscription price update. Note: If the subscription is placed using Auto Renewal, then new price will be updated only if the payment gateway supports "amount change" subscription feature. You can also configure this settings for each product separately within the product settings.', 'enhancer-for-woocommerce-subscriptions' ),
			),
			array(
				'name'     => __( 'Notify Subscription Price Update for Old Subscriptions', 'enhancer-for-woocommerce-subscriptions' ),
				'id'       => ENR_PREFIX . 'notify_subscription_price_update_before',
				'default'  => '',
				'type'     => 'number',
				'desc_tip' => false,
				/* translators: 1: multiple email templates create/edit url */
				'desc'     => sprintf( __( 'day(s) before the subscription due date. <br><br>You will be able to create multiple email templates to send different email content. To create a new email template/edit an existing one click <a class="button-primary" target="_blank" href="%s">Add/Edit subscription email template</a>', 'enhancer-for-woocommerce-subscriptions' ), esc_url( admin_url( 'edit.php?post_type=enr_email_template' ) ) ),
			),
		) );

		$switch_options_end = wp_list_filter( $settings, array( 'id' => 'woocommerce_subscriptions_switch_settings', 'type' => 'sectionend' ) );
		array_splice( $settings, key( $switch_options_end ), 0, array(
			array(
				'name'     => __( 'Allow Switching After', 'enhancer-for-woocommerce-subscriptions' ),
				'id'       => ENR_PREFIX . 'allow_switching_after',
				'default'  => '0',
				'type'     => 'number',
				'desc_tip' => __( 'Set 0 to allow subscribers to switch immediately. If left empty, customers will not be able to switch their subscriptions.', 'enhancer-for-woocommerce-subscriptions' ),
				'desc'     => __( 'day(s) from the subscription start date', 'enhancer-for-woocommerce-subscriptions' ),
			),
			array(
				'name'     => __( 'Allow Switching After Each Renewal', 'enhancer-for-woocommerce-subscriptions' ),
				'id'       => ENR_PREFIX . 'allow_switching_after_due',
				'default'  => '0',
				'type'     => 'number',
				'desc_tip' => __( 'Set 0 to allow subscribers to switch immediately. If left empty, customers will not be able to switch their subscriptions.', 'enhancer-for-woocommerce-subscriptions' ),
				'desc'     => __( 'day(s) from the subscription renewal date', 'enhancer-for-woocommerce-subscriptions' ),
			),
			array(
				'name'     => __( 'Prevent Switching', 'enhancer-for-woocommerce-subscriptions' ),
				'id'       => ENR_PREFIX . 'allow_switching_before_due',
				'default'  => '0',
				'type'     => 'number',
				'desc_tip' => __( 'If left empty or set 0, subscribers will not be prevented from switching their subscriptions until the renewal date.', 'enhancer-for-woocommerce-subscriptions' ),
				'desc'     => __( 'day(s) before the subscription renewal date', 'enhancer-for-woocommerce-subscriptions' ),
			),
		) );

		$misc_section_start = wp_list_filter( $settings, array( 'id' => 'woocommerce_subscriptions_miscellaneous', 'type' => 'title' ) );
		array_splice( $settings, key( $misc_section_start ), 0, array(
			array(
				'name' => _x( 'Cart Level Subscription', 'options section heading', 'enhancer-for-woocommerce-subscriptions' ),
				'type' => 'title',
				'id'   => ENR_PREFIX . 'cart_level_subscription',
			),
			array(
				'name'    => __( 'Allow Cart Level Subscription', 'enhancer-for-woocommerce-subscriptions' ),
				'id'      => ENR_PREFIX . 'allow_cart_level_subscribe_now',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'Allow customers to subscribe the whole cart items as a single subscription. <br><b>Note:</b> Customer can subscribe only if cart contains non-subscription products', 'enhancer-for-woocommerce-subscriptions' ),
			),
			array(
				'type' => 'enr_cart_level_subscription_plans_selector',
			),
			array(
				'name'    => __( 'Page to Display Cart Level Subscription', 'enhancer-for-woocommerce-subscriptions' ),
				'id'      => ENR_PREFIX . 'page_to_display_cart_level_subscribe_now_form',
				'class'   => 'wc-enhanced-select',
				'default' => 'cart',
				'type'    => 'multiselect',
				'options' => array(
					'cart'     => __( 'Cart', 'enhancer-for-woocommerce-subscriptions' ),
					'checkout' => __( 'Checkout', 'enhancer-for-woocommerce-subscriptions' ),
				),
			),
			array(
				'name'    => __( 'Position to Display Subscribe Option in Checkout Page', 'enhancer-for-woocommerce-subscriptions' ),
				'id'      => ENR_PREFIX . 'cart_level_subscribe_now_form_position_in_checkout_page',
				'default' => 'woocommerce_checkout_order_review',
				'type'    => 'select',
				'options' => array(
					'woocommerce_checkout_order_review'           => 'Woocommerce Checkout Order Review',
					'woocommerce_checkout_after_customer_details' => 'Woocommerce Checkout After Customer Details',
					'woocommerce_before_checkout_form'            => 'Woocommerce Before Checkout Form',
					'woocommerce_checkout_before_order_review'    => 'Woocommerce Checkout Before Order Review',
				),
				'desc'    => __( "Positioning updated here doesn't work for Block Editor. To change positioning in Block Editor, edit the cart and checkout blocks and move the 'Cart Level Subscription' block as per your needs.", 'enhancer-for-woocommerce-subscriptions' ),
			),
			array( 'type' => 'sectionend', 'id' => ENR_PREFIX . 'cart_level_subscription' ),
			array(
				'name' => _x( 'Cancelling', 'options section heading', 'enhancer-for-woocommerce-subscriptions' ),
				'type' => 'title',
				/* translators: 1: learn more */
				'desc' => sprintf( _x( 'Be aware that removing cancellation buttons can have legal implications. For example, %1$sCalifornia has an Automatic Renewal Law%2$s which requires stores to provide an easy-to-use mechanism for cancelling. Before removing cancellation button, we recommend you discuss potential implications with a legal professional.', 'used in the general subscription options page', 'enhancer-for-woocommerce-subscriptions' ), '<a href="' . esc_url( 'https://www.dlapiper.com/en/us/insights/publications/2014/09/california-automatic-renewal-law/' ) . '">', '</a>' ),
				'id'   => ENR_PREFIX . 'cancelling',
			),
			array(
				'name'    => __( 'Allow Cancelling', 'enhancer-for-woocommerce-subscriptions' ),
				'id'      => ENR_PREFIX . 'allow_cancelling',
				'default' => 'yes',
				'type'    => 'checkbox',
				'desc'    => __( 'Allow subscribers to cancel their subscriptions', 'enhancer-for-woocommerce-subscriptions' ),
			),
			array(
				'name'     => __( 'Allow Cancelling After', 'enhancer-for-woocommerce-subscriptions' ),
				'id'       => ENR_PREFIX . 'allow_cancelling_after',
				'default'  => '0',
				'type'     => 'number',
				'desc_tip' => __( 'Set 0 to allow subscribers to cancel immediately. If left empty, customers will not be able to cancel their subscriptions. You can also set cancel delay duration for each product separately within the product settings.', 'enhancer-for-woocommerce-subscriptions' ),
				'desc'     => __( 'day(s) from the subscription start date', 'enhancer-for-woocommerce-subscriptions' ),
			),
			array(
				'name'     => __( 'Allow Cancelling After Each Renewal', 'enhancer-for-woocommerce-subscriptions' ),
				'id'       => ENR_PREFIX . 'allow_cancelling_after_due',
				'default'  => '0',
				'type'     => 'number',
				'desc_tip' => __( 'Set 0 to allow subscribers to cancel immediately. If left empty, customers will not be able to cancel their subscriptions. You can also set cancel delay duration for each product separately within the product settings.', 'enhancer-for-woocommerce-subscriptions' ),
				'desc'     => __( 'day(s) from the subscription renewal date', 'enhancer-for-woocommerce-subscriptions' ),
			),
			array(
				'name'     => __( 'Prevent Cancelling', 'enhancer-for-woocommerce-subscriptions' ),
				'id'       => ENR_PREFIX . 'allow_cancelling_before_due',
				'default'  => '0',
				'type'     => 'number',
				'desc_tip' => __( 'If left empty or set 0, subscribers will not be prevented from cancelling their subscriptions until the renewal date. You can also set cancel prevention duration for each product separately within the product settings.', 'enhancer-for-woocommerce-subscriptions' ),
				'desc'     => __( 'day(s) before the subscription renewal date', 'enhancer-for-woocommerce-subscriptions' ),
			),
			array( 'type' => 'sectionend', 'id' => ENR_PREFIX . 'cancelling' ),
		) );

		$misc_section_end = wp_list_filter( $settings, array( 'id' => 'woocommerce_subscriptions_miscellaneous', 'type' => 'sectionend' ) );
		array_splice( $settings, key( $misc_section_end ), 0, array(
			array(
				'name'    => __( 'Disable WooCommerce Emails for Shipping Fulfillment Orders', 'enhancer-for-woocommerce-subscriptions' ),
				'id'      => ENR_PREFIX . 'disabled_wc_emails_for_shipping_orders',
				'default' => array(),
				'class'   => 'wc-enhanced-select',
				'type'    => 'multiselect',
				'options' => array(
					'new'        => __( 'New order', 'enhancer-for-woocommerce-subscriptions' ),
					'processing' => __( 'Processing order', 'enhancer-for-woocommerce-subscriptions' ),
					'completed'  => __( 'Completed order', 'enhancer-for-woocommerce-subscriptions' ),
				),
			),
			array( 'type' => 'sectionend', 'id' => 'woocommerce_subscriptions_miscellaneous' ),
			array(
				'name' => _x( 'Reminder Emails', 'options section heading', 'enhancer-for-woocommerce-subscriptions' ),
				'type' => 'title',
				/* translators: 1: multiple email templates create/edit url */
				'desc' => sprintf( _x( 'You will be able to create multiple email templates to send different email content. To create a new email template/edit an existing one click <a class="button-primary" target="_blank" href="%s">Add/Edit subscription email template</a>
                    <b>Note:</b>
                    1. If no templates are created for the respective reminder email type / if there is any mismatch in the day for which the email has to be send is set, then the default email templates created in "WooCommerce > Emails" will be sent.
                    2. If a new template is created, then the new template will be sent only for the new subscriptions / after one renewal for the existing subscriptions.
                        ', 'used in the general subscription options page', 'enhancer-for-woocommerce-subscriptions' ), esc_url( admin_url( 'edit.php?post_type=enr_email_template' ) ) ),
				'id'   => ENR_PREFIX . 'reminder_emails',
			),
			array(
				'name'        => __( 'Send Trial Ending Reminder', 'enhancer-for-woocommerce-subscriptions' ),
				'id'          => ENR_PREFIX . 'send_trial_ending_reminder_before',
				'default'     => '',
				'placeholder' => __( 'e.g. 3,2,1', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'text',
				'desc'        => __( 'day(s) before trial end date.', 'enhancer-for-woocommerce-subscriptions' ),
				'desc_tip'    => __( 'Multiple trial ending reminders can be sent to the customer. To send multiple reminders, enter the day(s) to send notification before the trial end date in descending order. Multiple values should be separated by comma(for example 3,2,1)', 'enhancer-for-woocommerce-subscriptions' ),
			),
			array(
				'name'        => __( 'Send Auto Renewal Reminder', 'enhancer-for-woocommerce-subscriptions' ),
				'id'          => ENR_PREFIX . 'send_auto_renewal_reminder_before',
				'default'     => '',
				'placeholder' => __( 'e.g. 3,2,1', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'text',
				'desc'        => __( 'day(s) before subscription due date.', 'enhancer-for-woocommerce-subscriptions' ),
				'desc_tip'    => __( 'Multiple auto renewal reminders can be sent to the customer. To send multiple reminders, enter the day(s) to send notification before the renewal date in descending order. Multiple values should be separated by comma(for example 3,2,1)', 'enhancer-for-woocommerce-subscriptions' ),
			),
			array(
				'name'        => __( 'Send Manual Renewal Reminder', 'enhancer-for-woocommerce-subscriptions' ),
				'id'          => ENR_PREFIX . 'send_manual_renewal_reminder_before',
				'default'     => '',
				'placeholder' => __( 'e.g. 3,2,1', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'text',
				'desc'        => __( 'day(s) before subscription due date.', 'enhancer-for-woocommerce-subscriptions' ),
				'desc_tip'    => __( 'Multiple manual renewal reminders can be sent to the customer. To send multiple reminders, enter the day(s) to send notification before the renewal date in descending order. Multiple values should be separated by comma(for example 3,2,1)', 'enhancer-for-woocommerce-subscriptions' ),
			),
			array(
				'name'        => __( 'Send Expiry Reminder', 'enhancer-for-woocommerce-subscriptions' ),
				'id'          => ENR_PREFIX . 'send_expiry_reminder_before',
				'default'     => '',
				'placeholder' => __( 'e.g. 3,2,1', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'text',
				'desc'        => __( 'day(s) before subscription expiry date.', 'enhancer-for-woocommerce-subscriptions' ),
				'desc_tip'    => __( 'Multiple expiry reminders can be sent to the customer. To send multiple reminders, enter the day(s) to send notification before the expiry date in descending order. Multiple values should be separated by comma(for example 3,2,1)', 'enhancer-for-woocommerce-subscriptions' ),
			),
			array( 'type' => 'sectionend', 'id' => ENR_PREFIX . 'reminder_emails' ),
		) );

		return $settings;
	}

	/**
	 * Add switching option for subscription plans.
	 * 
	 * @param array $data
	 * @return array
	 */
	public static function switching_settings( $data ) {
		return array_merge( $data, array(
			array(
				'id'    => 'enr_subscription_plans',
				'label' => __( 'Between Subscription Plans', 'enhancer-for-woocommerce-subscriptions' ),
			),
				) );
	}

	/**
	 * Cart Level Subscription Plans Selector field.
	 */
	public static function cart_level_subscription_plans_selector() {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="select_subscription_plans"><?php esc_html_e( 'Subscription Plans to be Shown for Cart Level Subscription', 'enhancer-for-woocommerce-subscriptions' ); ?>
					<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'Customer can choose the subscription plans from this list.', 'enhancer-for-woocommerce-subscriptions' ); ?>"></span>
				</label>
			</th>
			<td class="forminp forminp-select">
				<?php
				self::search_field( array(
					'class'       => 'wc-product-search',
					'id'          => ENR_PREFIX . 'cart_level_subscription_plans',
					'action'      => '_enr_json_search_subscription_plan',
					'type'        => 'subscription_plan',
					'placeholder' => __( 'Search for a subscription plan&hellip;', 'enhancer-for-woocommerce-subscriptions' ),
					'options'     => get_option( ENR_PREFIX . 'cart_level_subscription_plans' ),
				) );
				?>
				<p><a class="button-primary" target="_blank" href="<?php echo esc_url( admin_url( 'edit.php?post_type=enr_subsc_plan' ) ); ?>"><?php esc_html_e( 'Add/Edit subscription plan', 'enhancer-for-woocommerce-subscriptions' ); ?></a></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Get WC search field
	 * 
	 * @param array $args
	 * @param bool $echo
	 * @return string echo search field
	 */
	public static function search_field( $args = array(), $echo = true ) {
		$args = wp_parse_args( $args, array(
			'class'       => '',
			'id'          => '',
			'name'        => '',
			'type'        => '',
			'action'      => '',
			'placeholder' => '',
			'css'         => 'width: 50%;',
			'multiple'    => true,
			'allow_clear' => true,
			'selected'    => true,
			'options'     => array(),
				) );

		ob_start();
		?>
		<select 
			id="<?php echo esc_attr( $args[ 'id' ] ); ?>" 
			class="<?php echo esc_attr( $args[ 'class' ] ); ?>" 
			name="<?php echo esc_attr( '' !== $args[ 'name' ] ? $args[ 'name' ] : $args[ 'id' ]  ); ?><?php echo ( $args[ 'multiple' ] ) ? '[]' : ''; ?>" 
			data-action="<?php echo esc_attr( $args[ 'action' ] ); ?>" 
			data-placeholder="<?php echo esc_attr( $args[ 'placeholder' ] ); ?>" 
			<?php echo ( $args[ 'allow_clear' ] ) ? 'data-allow_clear="true"' : ''; ?> 
			<?php echo ( $args[ 'multiple' ] ) ? 'multiple="multiple"' : ''; ?> 
			style="<?php echo esc_attr( $args[ 'css' ] ); ?>">
				<?php
				if ( ! is_array( $args[ 'options' ] ) ) {
					$args[ 'options' ] = ( array ) $args[ 'options' ];
				}

				$args[ 'options' ] = array_filter( $args[ 'options' ] );

				foreach ( $args[ 'options' ] as $id ) {
					$option_value = '';

					switch ( $args[ 'type' ] ) {
						case 'product':
							$product = wc_get_product( $id );
							if ( $product ) {
								$option_value = wp_kses_post( $product->get_formatted_name() );
							}
							break;
						case 'customer':
							$user = get_user_by( 'id', $id );
							if ( $user ) {
								$option_value = ( esc_html( $user->display_name ) . '(#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email ) . ')' );
							}
							break;
						default:
							$post = get_post( $id );
							if ( $post ) {
								$option_value = sprintf( '(#%s) %s', $post->ID, wp_kses_post( $post->post_title ) );
							}
							break;
					}

					if ( $option_value ) {
						?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php echo ( $args[ 'selected' ] ) ? 'selected="selected"' : ''; ?>><?php echo esc_html( $option_value ); ?></option>
						<?php
					}
				}
				?>
		</select>
		<?php
		if ( $echo ) {
			ob_end_flush();
		} else {
			return ob_get_clean();
		}
	}

	/**
	 * Return the array of categories for the products.
	 * 
	 * @param  array $args
	 * @return array
	 */
	public static function get_product_term_options( $args = array() ) {
		$categories = array();
		$args       = wp_parse_args( $args, array( 'taxonomy' => 'product_cat', 'orderby' => 'name' ) );
		$terms      = get_terms( $args );

		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$categories[ $term->term_id ] = $term->name;
			}
		}

		return $categories;
	}

	/**
	 * Save subscription meta.
	 */
	public static function save_subscription_meta( $product_id ) {
		$product_type = empty( $_POST[ 'product-type' ] ) ? WC_Product_Factory::get_product_type( $product_id ) : sanitize_title( wp_unslash( $_POST[ 'product-type' ] ) );
		$product_type = $product_type ? $product_type : 'simple';

		// Subscription product meta save
		if ( ! empty( $_POST[ '_wcsnonce' ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ '_wcsnonce' ] ) ), 'wcs_subscription_meta' ) ) {
			update_post_meta( $product_id, ENR_PREFIX . 'enable_seperate_shipping_cycle', isset( $_POST[ ENR_PREFIX . 'enable_seperate_shipping_cycle' ] ) ? wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'enable_seperate_shipping_cycle' ] ) ) : ''  );
			update_post_meta( $product_id, ENR_PREFIX . 'enable_seperate_shipping_cycle_for_old_subscriptions', isset( $_POST[ ENR_PREFIX . 'enable_seperate_shipping_cycle_for_old_subscriptions' ] ) ? wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'enable_seperate_shipping_cycle_for_old_subscriptions' ] ) ) : ''  );
			update_post_meta( $product_id, ENR_PREFIX . 'limit_trial_to_one', isset( $_POST[ ENR_PREFIX . 'limit_trial_to_one' ] ) ? wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'limit_trial_to_one' ] ) ) : ''  );

			if ( isset( $_POST[ ENR_PREFIX . 'shipping_period_interval' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'shipping_period_interval', is_numeric( $_POST[ ENR_PREFIX . 'shipping_period_interval' ] ) ? absint( wp_unslash( $_POST[ ENR_PREFIX . 'shipping_period_interval' ] ) ) : ''  );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'shipping_period' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'shipping_period', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'shipping_period' ] ) ) );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'shipping_frequency_sync_date_day' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'shipping_frequency_sync_date_day', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'shipping_frequency_sync_date_day' ] ) ) );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'shipping_frequency_sync_date_week' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'shipping_frequency_sync_date_week', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'shipping_frequency_sync_date_week' ] ) ) );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'allow_price_update_for_old_subscriptions' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'allow_price_update_for_old_subscriptions', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'allow_price_update_for_old_subscriptions' ] ) ) );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'subscription_price_for_old_subscriptions' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'subscription_price_for_old_subscriptions', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'subscription_price_for_old_subscriptions' ] ) ) );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'notify_subscription_price_update_before' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'notify_subscription_price_update_before', is_numeric( $_POST[ ENR_PREFIX . 'notify_subscription_price_update_before' ] ) ? absint( wp_unslash( $_POST[ ENR_PREFIX . 'notify_subscription_price_update_before' ] ) ) : ''  );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'allow_cancelling_to' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'allow_cancelling_to', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'allow_cancelling_to' ] ) ) );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'allow_cancelling_after' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'allow_cancelling_after', is_numeric( $_POST[ ENR_PREFIX . 'allow_cancelling_after' ] ) ? absint( wp_unslash( $_POST[ ENR_PREFIX . 'allow_cancelling_after' ] ) ) : ''  );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'allow_cancelling_after_due' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'allow_cancelling_after_due', is_numeric( $_POST[ ENR_PREFIX . 'allow_cancelling_after_due' ] ) ? absint( wp_unslash( $_POST[ ENR_PREFIX . 'allow_cancelling_after_due' ] ) ) : ''  );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'allow_cancelling_before_due' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'allow_cancelling_before_due', is_numeric( $_POST[ ENR_PREFIX . 'allow_cancelling_before_due' ] ) ? absint( wp_unslash( $_POST[ ENR_PREFIX . 'allow_cancelling_before_due' ] ) ) : ''  );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'variable_subscription_limit_level' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'variable_subscription_limit_level', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'variable_subscription_limit_level' ] ) ) );
			}

			if ( 'subscription' === $product_type ) {
				if ( isset( $_POST[ ENR_PREFIX . 'exclude_reminder_emails' ] ) ) {
					update_post_meta( $product_id, ENR_PREFIX . 'exclude_reminder_emails', ! is_array( $_POST[ ENR_PREFIX . 'exclude_reminder_emails' ] ) ? array_filter( explode( ',', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'exclude_reminder_emails' ] ) ) ) ) : wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'exclude_reminder_emails' ] ) )  );
				} else {
					update_post_meta( $product_id, ENR_PREFIX . 'exclude_reminder_emails', array() );
				}
			}
		}

		// Product meta save
		if ( ! empty( $_POST[ 'woocommerce_meta_nonce' ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ 'woocommerce_meta_nonce' ] ) ), 'woocommerce_save_data' ) ) {
			update_post_meta( $product_id, ENR_PREFIX . 'allow_subscribe_now', isset( $_POST[ ENR_PREFIX . 'allow_subscribe_now' ] ) ? wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'allow_subscribe_now' ] ) ) : ''  );

			if ( isset( $_POST[ ENR_PREFIX . 'subscription_plans' ] ) ) {
				update_post_meta( $product_id, ENR_PREFIX . 'subscription_plans', ! is_array( $_POST[ ENR_PREFIX . 'subscription_plans' ] ) ? array_filter( array_map( 'absint', explode( ',', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'subscription_plans' ] ) ) ) ) ) : wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'subscription_plans' ] ) )  );
			} else {
				update_post_meta( $product_id, ENR_PREFIX . 'subscription_plans', array() );
			}

			if ( 'simple' === $product_type ) {
				if ( isset( $_POST[ ENR_PREFIX . 'subscribe_now_exclude_reminder_emails' ] ) ) {
					update_post_meta( $product_id, ENR_PREFIX . 'exclude_reminder_emails', ! is_array( $_POST[ ENR_PREFIX . 'subscribe_now_exclude_reminder_emails' ] ) ? array_filter( explode( ',', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'subscribe_now_exclude_reminder_emails' ] ) ) ) ) : wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'subscribe_now_exclude_reminder_emails' ] ) )  );
				} else {
					update_post_meta( $product_id, ENR_PREFIX . 'exclude_reminder_emails', array() );
				}
			}
		}
	}

	/**
	 * Save subscription variation meta.
	 */
	public static function save_subscription_variation_meta( $variation_id, $loop ) {
		$product_type = empty( $_POST[ 'product-type' ] ) ? WC_Product_Factory::get_product_type( $variation_id ) : sanitize_title( wp_unslash( $_POST[ 'product-type' ] ) );
		$product_type = $product_type ? $product_type : 'variable';

		// Subscription variation meta save
		if ( ! empty( $_POST[ '_wcsnonce_save_variations' ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ '_wcsnonce_save_variations' ] ) ), 'wcs_subscription_variations' ) ) {
			update_post_meta( $variation_id, ENR_PREFIX . 'enable_seperate_shipping_cycle', isset( $_POST[ ENR_PREFIX . 'enable_seperate_shipping_cycle' ][ $loop ] ) ? wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'enable_seperate_shipping_cycle' ][ $loop ] ) ) : ''  );
			update_post_meta( $variation_id, ENR_PREFIX . 'enable_seperate_shipping_cycle_for_old_subscriptions', isset( $_POST[ ENR_PREFIX . 'enable_seperate_shipping_cycle_for_old_subscriptions' ][ $loop ] ) ? wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'enable_seperate_shipping_cycle_for_old_subscriptions' ][ $loop ] ) ) : ''  );

			if ( isset( $_POST[ ENR_PREFIX . 'shipping_period_interval' ][ $loop ] ) ) {
				update_post_meta( $variation_id, ENR_PREFIX . 'shipping_period_interval', is_numeric( $_POST[ ENR_PREFIX . 'shipping_period_interval' ][ $loop ] ) ? absint( wp_unslash( $_POST[ ENR_PREFIX . 'shipping_period_interval' ][ $loop ] ) ) : ''  );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'shipping_period' ][ $loop ] ) ) {
				update_post_meta( $variation_id, ENR_PREFIX . 'shipping_period', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'shipping_period' ][ $loop ] ) ) );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'shipping_frequency_sync_date_day' ][ $loop ] ) ) {
				update_post_meta( $variation_id, ENR_PREFIX . 'shipping_frequency_sync_date_day', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'shipping_frequency_sync_date_day' ][ $loop ] ) ) );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'shipping_frequency_sync_date_week' ][ $loop ] ) ) {
				update_post_meta( $variation_id, ENR_PREFIX . 'shipping_frequency_sync_date_week', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'shipping_frequency_sync_date_week' ][ $loop ] ) ) );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'allow_cancelling_to' ][ $loop ] ) ) {
				update_post_meta( $variation_id, ENR_PREFIX . 'allow_cancelling_to', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'allow_cancelling_to' ][ $loop ] ) ) );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'allow_price_update_for_old_subscriptions' ][ $loop ] ) ) {
				update_post_meta( $variation_id, ENR_PREFIX . 'allow_price_update_for_old_subscriptions', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'allow_price_update_for_old_subscriptions' ][ $loop ] ) ) );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'subscription_price_for_old_subscriptions' ][ $loop ] ) ) {
				update_post_meta( $variation_id, ENR_PREFIX . 'subscription_price_for_old_subscriptions', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'subscription_price_for_old_subscriptions' ][ $loop ] ) ) );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'notify_subscription_price_update_before' ][ $loop ] ) ) {
				update_post_meta( $variation_id, ENR_PREFIX . 'notify_subscription_price_update_before', is_numeric( $_POST[ ENR_PREFIX . 'notify_subscription_price_update_before' ][ $loop ] ) ? absint( wp_unslash( $_POST[ ENR_PREFIX . 'notify_subscription_price_update_before' ][ $loop ] ) ) : ''  );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'allow_cancelling_after' ][ $loop ] ) ) {
				update_post_meta( $variation_id, ENR_PREFIX . 'allow_cancelling_after', is_numeric( $_POST[ ENR_PREFIX . 'allow_cancelling_after' ][ $loop ] ) ? absint( wp_unslash( $_POST[ ENR_PREFIX . 'allow_cancelling_after' ][ $loop ] ) ) : ''  );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'allow_cancelling_after_due' ][ $loop ] ) ) {
				update_post_meta( $variation_id, ENR_PREFIX . 'allow_cancelling_after_due', is_numeric( $_POST[ ENR_PREFIX . 'allow_cancelling_after_due' ][ $loop ] ) ? absint( wp_unslash( $_POST[ ENR_PREFIX . 'allow_cancelling_after_due' ][ $loop ] ) ) : ''  );
			}

			if ( isset( $_POST[ ENR_PREFIX . 'allow_cancelling_before_due' ][ $loop ] ) ) {
				update_post_meta( $variation_id, ENR_PREFIX . 'allow_cancelling_before_due', is_numeric( $_POST[ ENR_PREFIX . 'allow_cancelling_before_due' ][ $loop ] ) ? absint( wp_unslash( $_POST[ ENR_PREFIX . 'allow_cancelling_before_due' ][ $loop ] ) ) : ''  );
			}

			if ( 'variable-subscription' === $product_type ) {
				if ( isset( $_POST[ ENR_PREFIX . 'exclude_reminder_emails' ][ $loop ] ) ) {
					update_post_meta( $variation_id, ENR_PREFIX . 'exclude_reminder_emails', ! is_array( $_POST[ ENR_PREFIX . 'exclude_reminder_emails' ][ $loop ] ) ? array_filter( explode( ',', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'exclude_reminder_emails' ][ $loop ] ) ) ) ) : wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'exclude_reminder_emails' ][ $loop ] ) )  );
				} else {
					update_post_meta( $variation_id, ENR_PREFIX . 'exclude_reminder_emails', array() );
				}
			}
		}

		// Variation meta save
		if ( ! empty( $_POST[ 'security' ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ 'security' ] ) ), 'save-variations' ) ) {
			update_post_meta( $variation_id, ENR_PREFIX . 'allow_subscribe_now', isset( $_POST[ ENR_PREFIX . 'allow_subscribe_now' ][ $loop ] ) ? wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'allow_subscribe_now' ][ $loop ] ) ) : ''  );

			if ( isset( $_POST[ ENR_PREFIX . 'subscription_plans' ][ $loop ] ) ) {
				update_post_meta( $variation_id, ENR_PREFIX . 'subscription_plans', ! is_array( $_POST[ ENR_PREFIX . 'subscription_plans' ][ $loop ] ) ? array_filter( array_map( 'absint', explode( ',', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'subscription_plans' ][ $loop ] ) ) ) ) ) : wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'subscription_plans' ][ $loop ] ) )  );
			} else {
				update_post_meta( $variation_id, ENR_PREFIX . 'subscription_plans', array() );
			}

			if ( 'variable' === $product_type ) {
				if ( isset( $_POST[ ENR_PREFIX . 'subscribe_now_exclude_reminder_emails' ][ $loop ] ) ) {
					update_post_meta( $variation_id, ENR_PREFIX . 'exclude_reminder_emails', ! is_array( $_POST[ ENR_PREFIX . 'subscribe_now_exclude_reminder_emails' ][ $loop ] ) ? array_filter( explode( ',', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'subscribe_now_exclude_reminder_emails' ][ $loop ] ) ) ) ) : wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'subscribe_now_exclude_reminder_emails' ][ $loop ] ) )  );
				} else {
					update_post_meta( $variation_id, ENR_PREFIX . 'exclude_reminder_emails', array() );
				}
			}
		}
	}

	/**
	 * Update subscription settings.
	 */
	public static function update_subscription_settings() {
		if ( empty( $_POST[ '_wcsnonce' ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ '_wcsnonce' ] ) ), 'wcs_subscription_settings' ) ) {
			return;
		}

		if ( isset( $_POST[ ENR_PREFIX . 'cart_level_subscription_plans' ] ) ) {
			update_option( ENR_PREFIX . 'cart_level_subscription_plans', ! is_array( $_POST[ ENR_PREFIX . 'cart_level_subscription_plans' ] ) ? array_filter( array_map( 'absint', explode( ',', wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'cart_level_subscription_plans' ] ) ) ) ) ) : wc_clean( wp_unslash( $_POST[ ENR_PREFIX . 'cart_level_subscription_plans' ] ) )  );
		} else {
			update_option( ENR_PREFIX . 'cart_level_subscription_plans', array() );
		}
	}
}

ENR_Admin::init();
