<?php
/**
 * PPS product shipping tab template
 *
 * Used by WCMp_AFM_Per_Product_Shipping_Integration->per_product_shipping_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/per-product-shipping/html-product-data-shipping.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/per-product-shipping
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
global $wpdb;
$pps_add_to_all = get_post_meta( $post_id, '_per_product_shipping_add_to_all', true );
?>
<div class="form-group-row rules per_product_shipping_rules">
	<div class="form-group">
		<label class="control-label col-sm-3 col-md-3" for="_per_product_shipping_add_to_all[<?php echo $post_id; ?>]"><?php esc_html_e( 'Adjust Shipping Costs', 'woocommerce-shipping-per-product' ); ?></label>
		<div class="col-md-6 col-sm-9">
			<input class="form-control" type="checkbox" id="_per_product_shipping_add_to_all[<?php echo $post_id; ?>]" name="_per_product_shipping_add_to_all[<?php echo $post_id; ?>]" value="yes"<?php checked( wc_bool_to_string( $pps_add_to_all ), 'yes' ); ?>>
			<span class="form-text"><?php esc_html_e( 'Add per-product shipping cost to all shipping method rates?', 'woocommerce-shipping-per-product' ); ?></span>
		</div>
	</div> 
	<div class="form-group">
		<div class="col-md-12">
			<table class="table table-outer-border">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th><?php esc_html_e( 'Country Code', 'woocommerce-shipping-per-product' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_html_e( 'A 2 digit country code, e.g. US. Leave blank to apply to all.', 'woocommerce-shipping-per-product' ); ?>">[?]</a></th>
						<th><?php esc_html_e( 'State/County Code', 'woocommerce-shipping-per-product' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_html_e( 'A state code, e.g. AL. Leave blank to apply to all.', 'woocommerce-shipping-per-product' ); ?>">[?]</a></th>
						<th><?php esc_html_e( 'Zip/Postal Code', 'woocommerce-shipping-per-product' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_html_e( 'Postcode for this rule. Wildcards (*) can be used. Leave blank to apply to all areas.', 'woocommerce-shipping-per-product' ); ?>">[?]</a></th>
						<th class="cost"><?php esc_html_e( 'Line Cost (Excl. Tax)', 'woocommerce-shipping-per-product' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_html_e( 'Decimal cost for the line as a whole.', 'woocommerce-shipping-per-product' ); ?>">[?]</a></th>
						<th class="item_cost"><?php esc_html_e( 'Item Cost (Excl. Tax)', 'woocommerce-shipping-per-product' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_html_e( 'Decimal cost for the item (multiplied by qty).', 'woocommerce-shipping-per-product' ); ?>">[?]</a></th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="7">
							<a href="#" class="btn btn-default insert" data-postid="<?php echo $post_id; ?>" data-row="<?php
							$rule = (object) array(
								'rule_id' => '',
								'rule_country' => '',
								'rule_state' => '',
								'rule_postcode' => '',
								'rule_cost' =>  '0.00',
								'rule_item_cost' =>  '0.00',
							);
							ob_start();
							include( 'html-product-pps.php' );
							esc_attr_e( ob_get_clean() );
							?>"><?php esc_html_e( 'Insert row', 'woocommerce-shipping-per-product' ); ?></a>
							<div class="actions-csv">
								<a href="#" download="per-product-rates-<?php echo $post_id ?>.csv" class="btn export" data-postid="<?php echo $post_id; ?>"><?php esc_html_e( 'Export CSV', 'woocommerce-shipping-per-product' ); ?></a>
								<a href="#" class="btn import"><?php esc_html_e( 'Import CSV', 'woocommerce-shipping-per-product' ); ?></a>
							</div>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php
					$rules = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_per_product_shipping_rules WHERE product_id = %d ORDER BY rule_order;", $post_id ) );
					foreach ( $rules as $rule ) {
						include( 'html-product-pps.php' );
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>