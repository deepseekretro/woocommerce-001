<?php
/**
 * Order API Resources HTML for meta box.
 *
 * @package WooCommerce API Manager/Admin/Meta boxes
 */

defined( 'ABSPATH' ) || exit;

$expires = '';

if ( ! empty( $resource ) ) {
	if ( WCAM()->get_wc_subs_exist() && ! empty( $resource->sub_id ) ) {
		$expires = ( WC_AM_SUBSCRIPTION()->has_end_date_by_sub( $resource->sub_id ) ) ? date_i18n( wc_date_format(), WC_AM_SUBSCRIPTION()->get_subscription_time_by_sub_id( $resource->sub_id, 'end', 'site' ) ) : _x( 'When Cancelled', 'Used as end date for an indefinite subscription', 'woocommerce-api-manager' );
	} elseif ( WC_AM_ORDER_DATA_STORE()->is_time_expired( $resource->access_expires ?? false ) ) {
			$expires = __( 'Expired', 'woocommerce-api-manager' );
	} else {
		$expires = $resource->access_expires == 0 ? _x( 'Never', 'Used as end date for an indefinite subscription', 'woocommerce-api-manager' ) : esc_attr( WC_AM_FORMAT()->unix_timestamp_to_date( $resource->access_expires ) );
	}

	$version = WC_AM_PRODUCT_DATA_STORE()->get_meta( $resource->product_id, '_api_new_version' );
	?>

	<style>
		img.ui-datepicker-trigger {
			position: relative;
			top: 0.5em;
		}

		.activation-resources-help-tip .woocommerce-help-tip {
			display: inline;
			margin: 1px !important;
		}
	</style>

	<div class="wc-metaboxes">
		<div class="wc-metabox closed">
			<h3 class="fixed">
				<div style="padding: 1em; border-radius: 1em;" <?php
				if ( $i % 2 == 0 ) {
					echo ' class="alternate"'; }
				?>
					>
					<span class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'woocommerce-api-manager' ); ?>"></span>
					<strong><?php printf( esc_html__( 'Product ID: %1$s | Product Title: %2$s | Activations: %3$s out of %4$s | Current Version: %5$s | Expires: %6$s', 'woocommerce-api-manager' ), esc_attr( $resource->product_id ), esc_html( $resource->product_title ), esc_attr( $resource->activations_total ), esc_attr( $resource->activations_purchased_total ), ! empty( $version ) ? esc_attr( $version ) : esc_attr( '' ), esc_html( $expires ) ); ?></strong>
				</div>
			</h3>
			<table cellpadding="0" cellspacing="0" class="wc-metabox-content">
				<tbody>
				<tr>
					<td>
						<label for="poak<?php esc_attr_e( $i ); ?>"><?php esc_html_e( 'Product Order API Key:', 'woocommerce-api-manager' ); ?></label>
						<input type="text" class="short am_expand_text_box" id="poak<?php esc_attr_e( $i ); ?>" name="product_order_api_key[<?php esc_attr_e( $i ); ?>]"
								value="<?php esc_attr_e( $resource->product_order_api_key ); ?>" readonly/>
					</td>
					<td>
						<label><span style="display: inline"><?php esc_html_e( 'Activation Limit Total:', 'woocommerce-api-manager' ); ?></span>
							<span class="activation-resources-help-tip">
								<?php echo wc_help_tip( __( 'A value less than the current Activation Limit Total is not valid.', 'woocommerce-api-manager' ) ); ?>
							</span>
						</label>
						<div id="activations-purchased-total-div<?php esc_attr_e( $i ); ?>">
							<input type="number" id="activations_purchased_total[<?php esc_attr_e( $i ); ?>]"
									class="short"
									name="activations_purchased_total[<?php esc_attr_e( $i ); ?>]" step="1" min="<?php esc_html_e( $resource->activations_purchased_total ); ?>"
									value="<?php esc_attr_e( $resource->activations_purchased_total ); ?>"
									placeholder="<?php esc_html_e( '1', 'woocommerce-api-manager' ); ?>"/>
						</div>
					</td>
					<input type="hidden" id="current_activations_purchased_total[<?php esc_attr_e( $i ); ?>]" name="current_activations_purchased_total[<?php esc_attr_e( $i ); ?>]"
							value="<?php esc_html_e( $resource->activations_purchased_total ); ?>">
					<td>
						<label><?php esc_html_e( 'Current Version:', 'woocommerce-api-manager' ); ?></label>
						<input type="text" class="short" name="version[<?php esc_attr_e( $i ); ?>]"
								value="<?php echo esc_attr( ! empty( $version ) ? $version : '' ); ?>"
								placeholder="<?php esc_html_e( 'Required', 'woocommerce-api-manager' ); ?>" readonly/>
					</td>
				</tr>
				<tr>
					<td>
						<label><?php esc_html_e( 'Resource Title:', 'woocommerce-api-manager' ); ?></label>
						<input type="text" class="am_tooltip short am_expand_text_box" name="product_title[<?php esc_attr_e( $i ); ?>]"
								value="<?php esc_attr_e( $resource->product_title ); ?>"
								placeholder="<?php esc_html_e( 'Required', 'woocommerce-api-manager' ); ?>" readonly/>
					</td>
					<td>
						<label><?php esc_html_e( 'Product ID:', 'woocommerce-api-manager' ); ?></label>

						<div style="display: inline-block; vertical-align: middle;">
							<input type="text" class="short" name="product_id[<?php esc_attr_e( $i ); ?>]"
									value="<?php esc_attr_e( $resource->product_id ); ?>"
									placeholder="<?php esc_html_e( 'Required', 'woocommerce-api-manager' ); ?>" readonly/>
						</div>
						<div style="display: inline-block; vertical-align: middle;">
							<span style="text-decoration: none;">
							<?php echo '<a href="' . esc_url( admin_url() . 'post.php?post=' . esc_attr( WC_AM_PRODUCT_DATA_STORE()->get_parent_product_id( $resource->product_id ) ) . '&action=edit' ) . '" title="' . esc_html( WC_AM_API_RESOURCE_DATA_STORE()->get_title_by_api_resource_id( $resource->api_resource_id ) ) . '" target="_blank">'; ?>
						</span>
							<span style="text-decoration:none; font-size: large; vertical-align: middle;" class="dashicons dashicons-admin-links"></span></a>
						</div>
					</td>
					<td>
						<label><?php esc_html_e( 'Access Expires:', 'woocommerce-api-manager' ); ?>
							<?php
							if ( empty( $resource->sub_id ) && ! empty( $resource->access_expires ) ) {
								?>
								<span class="activation-resources-help-tip"><?php
								echo wc_help_tip( esc_html__( 'A date in the future can be chosen to extend the Access Expires value.', 'woocommerce-api-manager' ) );
								?>
								</span><?php
							}
							?>
						</label>
						<input type="text" class="short" id="wc_am_access_expires_api_resources_<?php esc_attr_e( $i ); ?>" name="access_expires_<?php esc_attr_e( $i ); ?>"
								value="<?php esc_html_e( $expires ); ?>"
								placeholder="<?php esc_html_e( 'Required', 'woocommerce-api-manager' ); ?>" readonly/>
						<input type="hidden" id="access_expires_before_change_<?php esc_attr_e( $i ); ?>" name="access_expires_before_change_<?php esc_attr_e( $i ); ?>"
								value="<?php esc_html_e( $expires ); ?>">
						<input type="hidden" id="new_access_expires_<?php esc_attr_e( $i ); ?>" name="new_access_expires_<?php esc_attr_e( $i ); ?>"
								value="">
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
<?php } ?>
