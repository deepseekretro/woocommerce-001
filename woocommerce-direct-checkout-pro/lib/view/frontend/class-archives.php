<?php
/**
 * Woocommerce-direct-checkout-pro
 *
 * @package  WooCommerce Direct Checkout PRO
 * @since    1.0.0
 */

namespace QuadLayers\WCDC_PRO\View\Frontend;

/**
 * Archives
 *
 * @class Archives
 * @version 1.0.0
 */
class Archives {

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
		add_action( 'wp_ajax_qlwcdc_quick_view_modal', array( $this, 'ajax_quick_view_modal_content' ) );
		add_action( 'wp_ajax_nopriv_qlwcdc_quick_view_modal', array( $this, 'ajax_quick_view_modal_content' ) );
		add_action( 'woocommerce_loop_add_to_cart_link', array( $this, 'add_quick_view_button' ) );
		add_action( 'wp_footer', array( $this, 'add_quick_view_modal' ) );
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
	 * Ajax quick view modal content
	 */
	public function ajax_quick_view_modal_content() {

		if ( ! check_ajax_referer( QLWCDC_PRO_DOMAIN, 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Please reload the page.', 'woocommerce-direct-checkout-pro' ) );
		}

		global $post, $product;

		if ( isset( $_POST['product_id'] ) ) {
			$post = get_post( absint( $_POST['product_id'] ) );
		}

		$product = wc_get_product( $post->ID );

		remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );

		ob_start();
		?>
		<div class="modal-content">
		<div class="modal-body">
			<div class="single-product">
			<div class="product">
				<span class="close">&times;</span>
				<div id="product-<?php echo esc_attr( $product->get_id() ); ?>">
					<?php
						/**
						 * Do Action woocommerce_before_single_product_summary.
						 *
						 * @since 1.0.0
						 */
						do_action( 'woocommerce_before_single_product_summary' );
					?>
				<div class="summary entry-summary">
					<?php
						/**
						 * Do Action woocommerce_single_product_summary.
						 *
						 * @since 1.0.0
						 */
						do_action( 'woocommerce_single_product_summary' );
					?>
				</div>
				<div class="clearfix"></div>
				</div>
			</div>
			</div>
		</div>
		</div>
		<?php
		$data = ob_get_clean();

		wp_send_json( $data );

		wp_die();
	}

	/**
	 * Add quick view button
	 *
	 * @param string $html Tabs.
	 */
	public function add_quick_view_button( $html ) {

		if ( 'yes' == get_option( 'qlwcdc_add_archive_quick_view' ) ) {

			global $product;

			ob_start();

			if ( method_exists( $product, 'get_id' ) && $product->get_id() ) {
				?>
		<i class="qlwcdc_quick_view button alt" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>">+</i>
				<?php
			}

			$icon = ob_get_clean();

			return $html . $icon;
		}

		return $html;
	}

	/**
	 * Add quick view modal
	 */
	public function add_quick_view_modal() {

		if ( did_action( 'before_woocommerce_init' ) ) {

			if ( 'yes' == get_option( 'qlwcdc_add_archive_quick_view' ) ) {

				wp_enqueue_script( 'wc-single-product' );
				?>
		<div class="modal" id="qlwcdc_quick_view_modal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
				</div>
			</div>
		</div>
				<?php
			}
		}
	}
}
