<?php
/**
 * Add-ons product tab
 *
 * Used by WCMp_AFM_Product_Addons_Integration->additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/product-addons/html-product-data-product-addons.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/product-addons
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div role="tabpanel" class="tab-pane fade collapsable-component-wrapper" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <?php do_action( 'wcmp-afm-product-addons-panel-start' ); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="toolbar pull-right">
                    <span class="expand-close">
                        <a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
                    </span>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <div class="product_addons wc-metaboxes product-addons-wrapper">  
                    <?php
                    $product_addon_type = WCMp_AFM_Product_Addons_Integration::get_product_addon_type();
                    
                    $loop = 0;
                    foreach ( $product_addons as $addon ) {
                        afm()->template->get_template( 'products/product-addons/html-product-addons.php', array( 'loop' => $loop, 'product_addons' => $product_addons, 'addon' => $addon, 'product_addon_type' => $product_addon_type, 'product_object' => $product_object ) );
                        $loop ++;
                    }
                    ?>
                </div>
            </div>
        </div>  
        <div class="button-group">
            <button type="button" class="btn btn-default add_new_addon button-primary"><?php esc_html_e( 'New add-on', 'woocommerce-product-addons' ); ?></button>
            <div class="toolbar pull-right">
                <button type="button" class="btn btn-default import_addons"><?php esc_html_e( 'Import', 'woocommerce-product-addons' ); ?></button>
                <button type="button" class="btn btn-default export_addons"><?php esc_html_e( 'Export', 'woocommerce-product-addons' ); ?></button>
            </div>
            <div class="mt-10">
                <textarea name="export_product_addon" class="form-control export" cols="20" rows="5" readonly="readonly"><?php echo esc_textarea( serialize( $product_addons ) ); ?></textarea>
                <textarea name="import_product_addon" class="form-control import" cols="20" rows="5" placeholder="<?php esc_html_e( 'Paste exported form data here and then save to import fields. The imported fields will be appended.', 'woocommerce-product-addons' ); ?>"></textarea>
            </div>
        </div> 
        <hr/>
        <?php if ( (bool) $product_object->get_id() ) : ?>
            <div class="form-group-row button-group">
                <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_product_addons_exclude_global"><?php esc_html_e( 'Global Addon Exclusion', 'woocommerce-product-addons' ); ?></label>
                    <div class="col-md-6 col-sm-9">
                        <input type="checkbox" class="form-control" id="_product_addons_exclude_global" name="_product_addons_exclude_global" value="1" <?php checked( $exclude_global, 1 ); ?>>
                        <span class="inline-description form-text"><?php _e( 'Check this to exclude this product from all Global Addons', 'woocommerce-product-addons' ); ?></span>
                    </div>
                </div> 
            </div>
        <?php endif; ?>

        <?php do_action( 'wcmp-afm-product-addons-panel-end' ); ?>
    </div>
</div>