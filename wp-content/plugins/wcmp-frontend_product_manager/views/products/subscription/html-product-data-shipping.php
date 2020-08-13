<?php

/**
 * Shipping product tab subscription template
 *
 * Used by WCMp_AFM_Subscription_Integration->woocommerce_subscription_shipping_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/subscription/html-product-data-shipping.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/subscription
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

if ( wcmp_is_allowed_product_type( 'subscription' ) || wcmp_is_allowed_product_type( 'variable-subscription' ) ) :
	$chosen_subscription_one_time_shipping = get_post_meta( $id, '_subscription_one_time_shipping', true );
?>
	<div class="form-group subscription_one_time_shipping show_if_subscription show_if_variable-subscription">
		<label class="control-label col-sm-3 col-md-3" for="_subscription_one_time_shipping">
			<?php esc_html_e( 'One time shipping', 'woocommerce-subscriptions' ); ?>
			<span class="img_tip" data-desc="<?php esc_html_e( 'Shipping for subscription products is normally charged on the initial order and all renewal orders. Enable this to only charge shipping once on the initial order. Note: for this setting to be enabled the subscription must not have a free trial or a synced renewal date.', 'woocommerce' ); ?>"></span>
		</label>
		<div class="col-md-6 col-sm-9">
			<label><input type="checkbox" class="checkbox" style="" name="_subscription_one_time_shipping" id="_subscription_one_time_shipping" <?php checked($chosen_subscription_one_time_shipping, 'yes'); ?> value="yes"> 
		</div>
	</div>
<?php endif; ?>
