<?php
defined( 'ABSPATH' ) || exit;

wp_nonce_field( 'enr_save_data', 'enr_save_meta_nonce' );
?>
<table class="widefat enr-email-template-data">
	<tr class="enr-email-template-email-id-field-row">
		<td>
			<label for="wc_email_id"><?php esc_html_e( 'Select reminder type', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		</td>
		<td>
			<select name="_wc_email_id">
				<?php foreach ( $available_wc_emails as $wc_email_id => $wc_email ) { ?>
					<option value="<?php echo esc_attr( $wc_email_id ); ?>" <?php selected( $wc_email_id, $selected_wc_email_id, true ); ?>><?php echo esc_html( $wc_email->get_title() ); ?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr class="enr-email-template-email-mapping-key-field-row">
		<td>
			<label for="email_mapping_key"><?php esc_html_e( 'Use this template for sending email', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		</td>
		<td>
			<input type="number" required="required" min="1" name="_email_mapping_key" value="<?php echo esc_attr( $selected_email_mapping_key ); ?>"/>
			<span class="description"><?php echo wp_kses_post( $default_data[ 'description' ][ 'email_mapping_key' ] ); ?></span>
		</td>
	</tr>
	<tr class="enr-email-template-email-product-filter-field-row">
		<td>
			<label for="email_product_filter"><?php esc_html_e( 'Product filter', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		</td>
		<td>
			<select name="_email_product_filter">
				<option value="all-products" <?php selected( 'all-products', $selected_email_product_filter, true ); ?>><?php esc_html_e( 'All products', 'enhancer-for-woocommerce-subscriptions' ); ?></option>
				<option value="included-products" <?php selected( 'included-products', $selected_email_product_filter, true ); ?>><?php esc_html_e( 'Included products', 'enhancer-for-woocommerce-subscriptions' ); ?></option>
				<option value="included-categories" <?php selected( 'included-categories', $selected_email_product_filter, true ); ?>><?php esc_html_e( 'Included categories', 'enhancer-for-woocommerce-subscriptions' ); ?></option>
			</select>
		</td>
	</tr>
	<tr class="enr-email-template-email-included-products-field-row">
		<td>
			<label for="email_included_products"><?php esc_html_e( 'Select products', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		</td>
		<td>
			<?php
			ENR_Admin::search_field( array(
				'class'       => 'wc-product-search',
				'name'        => '_email_included_products',
				'action'      => 'woocommerce_json_search_products_and_variations',
				'type'        => 'product',
				'placeholder' => __( 'Search for a product&hellip;', 'enhancer-for-woocommerce-subscriptions' ),
				'options'     => $selected_email_included_products,
			) );
			?>
		</td>
	</tr>
	<tr class="enr-email-template-email-included-categories-field-row">
		<td>
			<label for="email_included_categories"><?php esc_html_e( 'Select categories', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		</td>
		<td>
			<select name="_email_included_categories[]" multiple="multiple" class="wc-enhanced-select" style="width:50%;">
				<?php foreach ( $product_terms as $key => $val ) : ?>
					<?php if ( is_array( $val ) ) : ?>
						<optgroup label="<?php echo esc_attr( $key ); ?>">
							<?php foreach ( $val as $option_key_inner => $option_value_inner ) : ?>
								<option value="<?php echo esc_attr( $option_key_inner ); ?>" <?php selected( in_array( ( string ) $option_key_inner, $selected_email_included_categories, true ), true ); ?>><?php echo esc_html( $option_value_inner ); ?></option>
							<?php endforeach; ?>
						</optgroup>
					<?php else : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( in_array( $key, $selected_email_included_categories ), true, true ); ?>><?php echo esc_html( $val ); ?></option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr class="enr-email-template-email-subject-field-row">
		<td>
			<label for="email_subject"><?php esc_html_e( 'Email subject', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		</td>
		<td>
			<input type="text" name="_email_subject" style="width: 50%" value="<?php echo esc_attr( $selected_email_subject ); ?>" placeholder="<?php echo esc_attr( $default_data[ 'placeholder' ][ 'email_subject' ] ); ?>"><br>
			<span class="description"><?php echo wp_kses_post( $default_data[ 'description' ][ 'email_subject' ] ); ?></span>
		</td>
	</tr>
	<tr class="enr-email-template-email-heading-field-row">
		<td>
			<label for="email_heading"><?php esc_html_e( 'Email heading', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		</td>
		<td>
			<input type="text" name="_email_heading" style="width: 50%" value="<?php echo esc_attr( $selected_email_heading ); ?>" placeholder="<?php echo esc_attr( $default_data[ 'placeholder' ][ 'email_heading' ] ); ?>"><br>
			<span class="description"><?php echo wp_kses_post( $default_data[ 'description' ][ 'email_heading' ] ); ?></span>
		</td>
	</tr>
	<tr class="enr-email-template-email-placeholders-field-row">
		<td>
			<label for="email_placeholders"><?php esc_html_e( 'Shortcodes supported', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		</td>
		<td>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Placeholder', 'enhancer-for-woocommerce-subscriptions' ); ?></th>
						<th><?php esc_html_e( 'Purpose', 'enhancer-for-woocommerce-subscriptions' ); ?></th>
					</tr>
				</thead>
				<tbody>                
					<?php foreach ( $email_placeholders as $placeholder => $purpose ) { ?>
						<tr>
							<td><?php echo esc_html( $placeholder ); ?></td>
							<td><?php echo esc_html( $purpose ); ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</td>
	</tr>
	<tr class="enr-email-template-email-content-field-row">
		<td>
			<label for="email_content"><?php esc_html_e( 'Email content', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		</td>
		<td>
			<?php
			wp_editor( htmlspecialchars_decode( $selected_email_content, ENT_QUOTES ), '_email_content' );
			?>
		</td>
	</tr>
</tr>
</table>
