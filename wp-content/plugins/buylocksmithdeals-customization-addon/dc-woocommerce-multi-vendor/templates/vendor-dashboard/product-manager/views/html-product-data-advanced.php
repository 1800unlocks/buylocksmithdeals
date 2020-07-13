<?php

/**
 * Advanced product tab template
 *
 * Used by wcmp-afm-add-product.php template
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/woocommerce/html-product-data-advanced.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/woocommerce
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div role="tabpanel" class="tab-pane fade" id="advanced_product_data">
    <div class="row-padding"> 
        <div class="hide_if_external hide_if_grouped">
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_purchase_note"><?php esc_html_e( 'Purchase note', 'woocommerce' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <textarea id="_purchase_note" name="_purchase_note" class="form-control"><?php esc_html_e( $product_object->get_purchase_note( 'edit' ) ); ?></textarea>
                </div>
            </div> 
        </div> 
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="menu_order"><?php esc_html_e( 'Menu order', 'woocommerce' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <input id="menu_order" name="menu_order" type="number" class="form-control" value="<?php esc_attr_e( $product_object->get_menu_order( 'edit' ) ); ?>" step="1">
            </div>
        </div> 

        <?php if ( post_type_supports( 'product', 'comments' ) ) : ?> 
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="comment_status"><?php esc_html_e( 'Enable reviews', 'woocommerce' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input id="comment_status" name="comment_status" type="checkbox" class="form-control" value="<?php esc_attr_e( $product_object->get_reviews_allowed( 'edit' ) ? 'open' : 'closed'  ); ?>" <?php checked( $product_object->get_reviews_allowed( 'edit' ), true ); ?>>
                </div>
            </div> 
        <?php endif; ?>

        <?php do_action( 'wcmp_afm_product_options_advanced', $post->ID, $product_object, $post ); ?>
    </div>
</div>