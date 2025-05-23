<?php
/**
 * Woocommerce-direct-checkout-pro
 *
 * @package  WooCommerce Direct Checkout PRO
 * @since    1.0.0
 */

namespace QuadLayers\WCDC_PRO\View\Frontend;

/**
 * Checkout
 *
 * @class Checkout
 * @version 1.0.0
 */
class Checkout {

	/**
	 * The single instance of the class.
	 *
	 * @var WCDC_PRO
	 */
	protected static $instance;

	/**
	 * Construct
	 */
	public function __construct() {
		// add_action('wp_enqueue_scripts', array($this, 'add_product_js'), 99);.
		add_action( 'wp_ajax_qlwcdc_update_cart', array( $this, 'ajax_update_cart' ) );
		add_action( 'wp_ajax_nopriv_qlwcdc_update_cart', array( $this, 'ajax_update_cart' ) );
		add_action( 'woocommerce_checkout_init', array( $this, 'add_checkout_coupon' ), 20 );

		if ( isset( $_GET['add-to-cart'] ) && absint( $_GET['add-to-cart'] ) > 0 ) {
			add_filter( 'wc_add_to_cart_message_html', '__return_false' );
		}

		if ( 'yes' === get_option( 'qlwcdc_remove_checkout_columns' ) ) {
			add_action( 'wp_head', array( $this, 'remove_checkout_columns' ) );
		}

		if ( 'yes' === get_option( 'qlwcdc_remove_checkout_gateway_icon' ) ) {
			add_filter( 'woocommerce_gateway_icon', '__return_false' );
		}

		if ( 'remove' === get_option( 'qlwcdc_remove_checkout_coupon_form' ) ) {
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
		} elseif ( 'toggle' === get_option( 'qlwcdc_remove_checkout_coupon_form' ) ) {
			add_action( 'wp_head', array( $this, 'remove_coupon_toggle' ) );
		} elseif ( 'checkout' === get_option( 'qlwcdc_remove_checkout_coupon_form' ) ) {
			add_action( 'woocommerce_review_order_after_order_total', array( $this, 'add_checkout_coupon_form' ) );
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
		}

		// add_action('woocommerce_before_checkout_form', array($this, 'review_offer'));.
		add_filter( 'wc_get_template', array( $this, 'wc_get_template' ), 10, 5 );
	}

	/**
	 * Instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Ajax update cart
	 */
	public function ajax_update_cart() {
		if ( ! check_ajax_referer( QLWCDC_PRO_DOMAIN, 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Please reload the page.', 'woocommerce-direct-checkout-pro' ) );
		}

		if ( isset( $_POST['hash'] ) ) {
			$cart_item_key = wc_clean( wp_unslash( $_POST['hash'] ) );
		}

		$threeball_product_values = WC()->cart->get_cart_item( $cart_item_key );

		if ( isset( $_POST['quantity'] ) ) {
			/**
			 * Apply filters: woocommerce_stock_amount_cart_item woocommerce_stock_amount.
			 *
			 * @since 1.0.0
			 */
			$threeball_product_quantity = apply_filters( 'woocommerce_stock_amount_cart_item', apply_filters( 'woocommerce_stock_amount', preg_replace( '/[^0-9\.]/', '', filter_var( wp_unslash( $_POST['quantity'] ), FILTER_SANITIZE_NUMBER_INT ) ) ), $cart_item_key );
		}

		/**
		 * Apply filters: woocommerce_update_cart_validation.
		 *
		 * @since 1.0.0
		 */
		$passed_validation = apply_filters( 'woocommerce_update_cart_validation', true, $cart_item_key, $threeball_product_values, $threeball_product_quantity );

		if ( $passed_validation ) {
			WC()->cart->set_quantity( $cart_item_key, $threeball_product_quantity, true );
		}

		/**
		 * Fix compatibility issues with WooCommerce PayPal Express Checkout
		 */
		if ( 'yes' !== get_option( 'qlwcdc_add_checkout_cart_ajax', 'yes' ) ) {
			wp_send_json_success( true );
		}

		ob_start();
		// remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );.
		?>
		<?php
			/**
			 * Do Action woocommerce_checkout_before_order_review.
			 *
			 * @since 1.0.0
			 */
			do_action( 'woocommerce_checkout_before_order_review' );
		?>
		<div id="order_review" class="woocommerce-checkout-review-order">
			<?php
				/**
				 * Do Action woocommerce_checkout_order_review.
				 *
				 * @since 1.0.0
				 */
				do_action( 'woocommerce_checkout_order_review' );
			?>
		</div>
		<?php
			/**
			 * Do Action woocommerce_checkout_after_order_review.
			 *
			 * @since 1.0.0
			 */
			do_action( 'woocommerce_checkout_after_order_review' );
		?>
		<?php
		$data = ob_get_clean();

		wp_send_json( $data );

		wp_die();
	}

	/**
	 * Add checkout coupon
	 */
	public function add_checkout_coupon() {
		if ( ! empty( $_GET['coupon_code'] ) ) {

			$coupon_code = wc_format_coupon_code( wc_clean( wp_unslash( $_GET['coupon_code'] ) ) );

			if ( ! in_array( $coupon_code, WC()->cart->get_applied_coupons() ) ) {
				WC()->cart->add_discount( $coupon_code );
			}
		}
	}

	/**
	 * Remove checkout columns
	 */
	public function remove_checkout_columns() {
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			?>
			<style>
				.woocommerce .col2-set .col-1,
				.woocommerce-page .col2-set .col-1,
				.woocommerce-checkout .woocommerce #payment,
				.woocommerce-checkout .woocommerce #order_review,
				.woocommerce-checkout .woocommerce #order_review_heading,
				.woocommerce-checkout .woocommerce #customer_details {
					width: 100% !important;
					float: none !important;
					margin: auto;
					box-sizing: border-box;
				}
			</style>
			<?php
		}
	}

	/**
	 * Remove coupon toggle
	 */
	public function remove_coupon_toggle() {
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			?>
			<style>
				.woocommerce-form-coupon-toggle .woocommerce-info {
					display: none !important;
				}
				.woocommerce-form-coupon {
					display: block !important;
				}
			</style>
			<?php
		}
	}

	/**
	 * Add checkout coupon form
	 */
	public function add_checkout_coupon_form() {
		?>
		<tr id="qlwcdc_order_coupon_code" class="coupon-code">
			<td colspan="100%">
				<p class="form-row" style="margin: 0;">
				<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Click here to enter your coupon code', 'woocommerce-direct-checkout-pro' ); ?>" id="qlwcdc_coupon_code" value="" />
				</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Wc get template
	 *
	 * @param string $located Located.
	 * @param string $template_name Template name.
	 * @param array  $args Args.
	 * @param string $template_path Template path.
	 * @param string $default_path Default path.
	 */
	public function wc_get_template( $located, $template_name, $args, $template_path, $default_path ) {
		if ( 'checkout/review-order.php' == $template_name ) {
			if ( 'yes' === get_option( 'qlwcdc_add_checkout_cart' ) && count( get_option( 'qlwcdc_add_checkout_cart_fields', array() ) ) ) {
				$located = QLWCDC_PRO_PLUGIN_DIR . 'templates/checkout/review-order.php';
			}
		}

		if ( 'order/order-details-customer.php' == $template_name ) {
			if ( 'yes' === get_option( 'qlwcdc_remove_order_details_address' ) ) {
				$located = QLWCDC_PRO_PLUGIN_DIR . 'templates/order/order-details-customer.php';
			}
		}

		return $located;
	}
}
