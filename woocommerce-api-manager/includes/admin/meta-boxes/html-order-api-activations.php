<?php
/**
 * Order API Key Activations HTML for meta box.
 *
 * @package WooCommerce API Manager/Admin/Meta boxes
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="wc_am_items">
	<thead>
	<div class="woocommerce_order_items_wrapper wc-order-items-editable">
		<table id="activations-table" cellpadding="0" cellspacing="0" class="woocommerce_order_items">
			<tr>
				<th class="sortable" id="wcam-header"><?php esc_html_e( 'API Key Used', 'woocommerce-api-manager' ); ?></th>
				<th class="sortable" id="wcam-header"><?php esc_html_e( 'Product ID', 'woocommerce-api-manager' ); ?></th>
				<th class="sortable" id="wcam-header"><?php esc_html_e( 'Version', 'woocommerce-api-manager' ); ?></th>
				<th class="sortable" id="wcam-header"><?php esc_html_e( 'Time', 'woocommerce-api-manager' ); ?></th>
				<th class="sortable" id="wcam-header"><?php esc_html_e( 'Object', 'woocommerce-api-manager' ); ?></th>
			</tr>
	</thead>
	<tbody id="order_line_items">
	<?php
	$i = 0;

	if ( ! empty( $resources ) ) {
		foreach ( $resources as $resource ) {
			?>
			<?php
			/**
			 * If $is_expired === true
			 *
			 * @since 2.6
			 */
			if ( $resource->sub_id == 0 ) {
				$is_expired = WC_AM_ORDER_DATA_STORE()->is_time_expired( $resource->access_expires ?? false );
			} else {
				$is_expired = ! WC_AM_SUBSCRIPTION()->is_subscription_for_order_active( $resource->sub_id );
			}

			if ( ! $is_expired ) {
				if ( $resource->api_key == $resource->master_api_key ) {
					$api_key_type = __( 'Master', 'woocommerce-api-manager' );
				} elseif ( $resource->api_key == $resource->product_order_api_key ) {
					$api_key_type = __( 'Product Order', 'woocommerce-api-manager' );
				} else {
					$api_key_type = __( 'Associated API', 'woocommerce-api-manager' );
				}
				$api_key_text = $api_key_type . ': ' . $resource->api_key;
				?>
				<tr<?php
				if ( $i % 2 == 0 ) {
					echo ' class="alternate"'; }
				?>
					>
					<td><?php esc_html_e( $api_key_text ); ?></td>
					<td><?php echo '<a href="' . esc_url( admin_url() . 'post.php?post=' . esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_parent_product_id( $resource->assigned_product_id ) ) . '&action=edit' ) . '" title="' . esc_html( WC_AM_API_RESOURCE_DATA_STORE()->get_title_by_api_resource_id( $resource->api_resource_id ) ) . '" target="_blank">' . esc_attr( $resource->assigned_product_id ) . '</a>'; ?></td>
					<td style="padding-left: 1em; padding-right: 1em"><?php echo esc_attr( ! empty( $resource->version ) ? $resource->version : '' ); ?></td>
					<td><?php echo esc_attr( WC_AM_FORMAT()->unix_timestamp_to_date( $resource->activation_time ) ); ?></td>
					<td>
						<?php
						// Remove the trailing forward slash, if it exists.
						$obj_length = strlen( $resource->object );
						$object     = ! empty( $resource->object ) && substr( $resource->object, $obj_length - 1, $obj_length ) == '/' ? substr( $resource->object, 0, $obj_length - 1 ) : $resource->object;

						if ( filter_var( $resource->object, FILTER_VALIDATE_URL ) ) {
							// If $object is a URL, then remove the http(s)//: prefix.
							echo '<a href="' . esc_url( $object ) . '" target="_blank">' . esc_attr( WC_AM_URL()->remove_url_prefix( $object ) ) . '</a>';
						} else {
							echo esc_attr( $object );
						}
						?>
					</td>
					<td>
						<button type="button"
								instance="<?php esc_attr_e( $resource->instance ); ?>" order_id="<?php esc_attr_e( $resource->order_id ); ?>"
								sub_parent_id="<?php esc_attr_e( $resource->sub_parent_id ); ?>" api_key="<?php esc_attr_e( $resource->api_key ); ?>"
								product_id="<?php echo esc_attr_e( $resource->product_id ); ?>" user_id="<?php esc_attr_e( $resource->user_id ); ?>"
								class="delete_api_key button"><?php esc_html_e( 'Delete', 'woocommerce-api-manager' ); ?></button>
					</td>
				</tr>
				<?php
			} else {
				/**
				 * If $is_expired === true
				 *
				 * @since 2.6
				 */
				?>
				<style>
					#wcam-header {
						display: none;
					}
				</style>
				<?php if ( $i == 0 ) { // Only show once. ?>
					<p style="padding:0 8px;"><?php esc_html_e( 'No activations yet.', 'woocommerce-api-manager' ); ?></p>
					<?php
				}
			}

			++$i;
		}
	}
	?>
	</tbody>
	</table>
</div>
</div>
