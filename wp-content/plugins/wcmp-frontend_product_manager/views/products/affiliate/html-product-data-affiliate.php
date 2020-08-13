<?php
defined( 'ABSPATH' ) || exit;


$rate_type = get_post_meta($product_object->get_id(), '_affwp_woocommerce_product_rate_type', true); 
$rate_type_value = isset( $rate_type ) ? $rate_type : '';
$rate = get_post_meta($product_object->get_id(), '_affwp_woocommerce_product_rate', true); 
$rate_value = isset( $rate ) ? $rate : '';
$checkbox = get_post_meta($product_object->get_id(), '_affwp_woocommerce_referrals_disabled', true); 
$checkbox_value = isset( $checkbox ) ? $checkbox : '';

?>
<div role="tabpanel" class="tab-pane fade" id="affiliate_product_data">
	<div class="row-padding">
	<?php if ( ! affwp_is_per_order_rate() ): ?>
		<p><h4><?php _e( 'Configure affiliate rates for this product. These settings will be used to calculate affiliate earnings per-sale.', 'affiliate-wp' ); ?></h4></p>
		<div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_affwp_woocommerce_product_rate_type"><?php esc_html_e( 'Affiliate Rate Type', 'affiliate-wp' ); ?></label>
            <span class="img_tip" data-desc="<?php esc_html_e( 'Earnings can be based on either a percentage or a flat rate amount.', 'affiliate-wp' ); ?>"></span>            
            <div class="col-md-6 col-sm-9">
                <select name="_affwp_woocommerce_product_rate_type" id="_affwp_woocommerce_product_rate_type" class="form-control regular-select">
                <?php $affiliate_product	= array_merge( array( '' => __( 'Site Default', 'affiliate-wp' ) ), affwp_get_affiliate_rate_types() ); ?>
                    <?php foreach ( $affiliate_product as $key => $class_name  ) : ?>
                        <option value="<?php esc_attr_e( $key ); ?>" <?php selected( $rate_type_value, $key ); ?>><?php esc_html_e( $class_name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div> 
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_affwp_woocommerce_product_rate"><?php esc_html_e( 'Affiliate Rate', 'affiliate-wp' ); ?></label>
            <span class="img_tip" data-desc="<?php esc_html_e( 'Leave blank to use default affiliate rates.', 'affiliate-wp' ); ?>"></span>            
            <div class="col-md-6 col-sm-9">
                <input id="_affwp_woocommerce_product_rate" name="_affwp_woocommerce_product_rate" type="text" class="form-control" value="<?php echo $rate_value; ?>" step="1">
            </div>
        </div>
    <?php else: ?>
    	<p>
    		<em>
    			<?php _e( sprintf( 'Per-product rates are disabled because the flat rate referral basis is set to per order. You can change that in <a href="%s">Affiliates > Settings</a>.', affwp_admin_url( 'settings' ) ), 'affiliate-wp' ); ?>
    		</em>
    	</p>
    <?php endif; ?>
    	<div class="form-group">
        	<label class="control-label col-sm-3 col-md-3" for="_affwp_woocommerce_referrals_disabled"><?php esc_html_e( 'Disable referrals', 'affiliate-wp' ); ?></label>
        	<div class="col-md-6 col-sm-9">
        		<input id="_affwp_woocommerce_referrals_disabled" name="_affwp_woocommerce_referrals_disabled" type="checkbox" class="form-control" value="<?php esc_attr_e( $product_object->get_reviews_allowed( 'edit' ) ? 'open' : 'closed'  ); ?>" <?php checked( $checkbox_value, true ); ?>> <?php _e( 'This will prevent this product from generating referral commissions for affiliates.', 'affiliate-wp' ); ?>
        	</div>
        </div> 
	</div>
</div>

