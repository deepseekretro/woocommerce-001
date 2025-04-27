<?php

/**
 * A class for integrating with WooCommerce Blocks scripts.
 */
use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Blocks integration class.
 *
 * @class  ENR_Blocks_Integration
 * @implements   IntegrationInterface
 * @package Class
 */
class ENR_Blocks_Integration implements IntegrationInterface {

	/**
	 * The single instance of the class.
	 *
	 * @var ENR_Blocks_Integration
	 */
	protected static $instance = null;

	/**
	 * Main ENR_Blocks_Integration instance. Ensures only one instance of ENR_Blocks_Integration is loaded or can be loaded.
	 *
	 * @return ENR_Blocks_Integration
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'enhancer-for-woocommerce-subscriptions';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'enr-blocks-integration' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'enr-blocks-integration' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return array(
			'enr-blocks-integration'     => 'active',
			'pages_to_render_cart_level' => get_option( ENR_PREFIX . 'page_to_display_cart_level_subscribe_now_form', 'cart' ),
			'is_checkout'                => is_checkout(),
			'is_user_logged_in'          => is_user_logged_in(),
			'cart_level_subscribed'      => _enr_cart_contains_subscribed_product( 'cart_level' ),
		);
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		return _enr()->get_version();
	}

	/**
	 * Enqueue block assets for the editor.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_block_editor_assets() {
		$script_path       = 'blocks/admin/index.js';
		$script_url        = ENR_URL . "/assets/{$script_path}";
		$script_asset_path = ENR_ABSPATH . 'assets/blocks/admin/index.asset.php';
		$script_asset      = file_exists( $script_asset_path ) ? require $script_asset_path : array(
			'dependencies' => array(),
			'version'      => $this->get_file_version( $script_asset_path ),
		);

		wp_register_script(
				'enr-admin-blocks-integration',
				$script_url,
				$script_asset[ 'dependencies' ],
				$script_asset[ 'version' ],
				true
		);

		wp_enqueue_script( 'enr-admin-blocks-integration' );
	}

	/**
	 * Enqueue block assets for both editor and front-end.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_block_assets() {
		$script_path = 'blocks/frontend/index.js';
		$style_path  = 'blocks/frontend/index.css';

		$script_url = ENR_URL . "/assets/{$script_path}";
		$style_url  = ENR_URL . "/assets/{$style_path}";

		$script_asset_path = ENR_ABSPATH . 'assets/blocks/frontend/index.asset.php';
		$style_asset_path  = ENR_ABSPATH . 'assets/blocks/frontend/index.css';

		$script_asset = file_exists( $script_asset_path ) ? require $script_asset_path : array(
			'dependencies' => array(),
			'version'      => $this->get_file_version( $script_asset_path ),
		);

		wp_enqueue_style(
				'enr-blocks-integration',
				$style_url,
				'',
				$this->get_file_version( $style_asset_path ),
				'all'
		);
		wp_register_script(
				'enr-blocks-integration',
				$script_url,
				$script_asset[ 'dependencies' ],
				$script_asset[ 'version' ],
				true
		);
		wp_set_script_translations(
				'enr-blocks-integration',
				'enhancer-for-woocommerce-subscriptions',
				ENR_ABSPATH . 'languages/'
		);
	}
}
