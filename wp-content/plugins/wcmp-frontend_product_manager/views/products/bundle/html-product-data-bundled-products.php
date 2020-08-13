<?php
/**
 * Bundled Products - product tab template
 *
 * Used by WCMp_AFM_Product_Bundle_Integration->product_bundle_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/bundle/html-product-data-bundled-products.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/bundle
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <?php do_action( 'wcmp_afm_before_bundled_product_data' ); ?>
        <div class="form-group-row"> 
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_pb_layout_style">
                    <?php esc_html_e( 'Layout', 'woocommerce-product-bundles' ); ?>
                    <span class="img_tip" data-desc="<?php esc_attr_e( 'Select the <strong>Tabular</strong> option to have the thumbnails, descriptions and quantities of bundled products arranged in a table. Recommended for displaying multiple bundled products with configurable quantities.', 'woocommerce' ); ?>"></span>
                </label>
                <div class="col-md-6 col-sm-9">
                    <select class="form-control" id="_wc_pb_layout_style" name="_wc_pb_layout_style">
                        <?php
                        $options = WC_Product_Bundle::get_layout_options();
                        $selected = $bundled_product->get_layout( 'edit' );
                        foreach ( $options as $key => $value ) {
                            echo '<option value="' . esc_attr( $key ) . '"' . selected( $key, $selected, false ) . '>' . esc_html( $value ) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div> 
        </div>
        <div class="form-group-row">
            <div class="form-group bundle_group_mode bundled_product_data_field">
                <label class="control-label col-sm-3 col-md-3" for="_wc_pb_group_mode">
                    <?php esc_html_e( 'Group mode', 'woocommerce-product-bundles' ); ?>
                    <span class="img_tip" data-desc="<?php esc_attr_e( 'Modifies the <strong>visibility</strong> and <strong>indentation</strong> of parent/child line items in cart/order templates.', 'woocommerce' ); ?>"></span>
                </label>
                <div class="col-md-6 col-sm-9">
                    <?php
                    $group_mode_options = WC_Product_Bundle::get_group_mode_options( true );

                    $group_modes_without_parent = array();

                    foreach ( $group_mode_options as $group_mode_key => $group_mode_title ) {
                        if ( false === WC_Product_Bundle::group_mode_has( $group_mode_key, 'parent_item' ) ) {
                            $group_modes_without_parent[] = '<strong>' . $group_mode_title . '</strong>';
                        }
                    }

                    $mode_selected = $bundled_product->get_group_mode( 'edit' );
                    ?>
                    <select class="form-control" id="_wc_pb_group_mode" name="_wc_pb_group_mode">
                        <?php
                        foreach ( $group_mode_options as $key => $value ) {
                            echo '<option value="' . esc_attr( $key ) . '"' . selected( $key, $mode_selected, false ) . '>' . esc_html( $value ) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div> 
        </div>
        <div class="form-group-row bundle_edit_in_cart">
            <div class="form-group bundled_product_data_field">
                <label class="control-label col-sm-3 col-md-3" for="_wc_pb_edit_in_cart">
                    <?php esc_html_e( 'Edit in cart', 'woocommerce-product-bundles' ); ?>
                    <span class="img_tip" data-desc="<?php esc_html_e( 'Enable this option to allow changing the configuration of this bundle in the cart. Applicable to bundles with configurable attributes and/or quantities.', 'woocommerce' ); ?>"></span>                            
                    <span class="description form-text"></span>
                </label>
                <div class="col-md-6 col-sm-9">
                    <input type="checkbox" class="form-control" id="_wc_pb_edit_in_cart" name="_wc_pb_edit_in_cart" value="yes" <?php checked( $bundled_product->get_editable_in_cart( 'edit' ), true ); ?>>
                </div>
            </div> 
        </div>
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
                <div class="bundle_product_items wc-metaboxes wc-bundle-metaboxes-wrapper bundle_product_items-wrapper">  
                    <?php
                    /*
                     * Bundled products options.
                     */
                    $bundled_items = $bundled_product->get_bundled_items( 'edit' );
                    $tabs = $self->get_bundled_product_tabs();
                    $toggle = 'closed';
                    if ( ! empty( $bundled_items ) ) {

                        $loop = 0;

                        foreach ( $bundled_items as $item_id => $item ) {

                            $item_availability = '';
                            $item_data = $item->get_data();
                            $item_data['bundled_item'] = $item;

                            if ( false === $item->is_in_stock() ) {
                                if ( $item->product->is_in_stock() ) {
                                    $item_availability = '<mark class="outofstock insufficient_stock">' . __( 'Insufficient stock', 'woocommerce-product-bundles' ) . '</mark>';
                                } else {
                                    $item_availability = '<mark class="outofstock">' . __( 'Out of stock', 'woocommerce' ) . '</mark>';
                                }
                            }

                            $product_id = $item->get_product_id();
                            $title = $item->product->get_title();
                            $sku = $item->product->get_sku();
                            $title = WC_PB_Helpers::format_product_title( $title, $sku, '', true );
                            $title = sprintf( _x( '#%1$s: %2$s', 'bundled product admin title', 'woocommerce-product-bundles' ), $product_id, $title );

                            include( 'html-product-bundle-items.php' );

                            $loop ++;
                        }
                    }
                    ?>
                </div>
            </div>
        </div> 
        <div class="button-group">
            <div class="form-group">
                <div class="col-md-3 col-sm3">
                    <select class="wc-product-search form-control inline-select" id="bundled_product" name="bundled_product" data-placeholder="<?php _e( 'Add a bundled product&hellip;', 'woocommerce-product-bundles' ); ?>" data-action="wcmp_afm_json_search_bundle_items" data-exclude="<?php echo intval( $id ); ?>" multiple="multiple" data-limit="500">
                        <option></option>
                    </select>
                </div>
                <span class="tool_tip" data-desc="<?php esc_attr_e( 'Search for a product and add it to this bundle by clicking its name in the results list.', 'woocommerce-product-bundles' ) ?>"></span>
                <!-- <div class="toolbar pull-right">
                    <span class="expand-close">
                        <a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
                    </span>
                </div> -->
            </div>
        </div>
        <?php do_action( 'wcmp_afm_after_bundled_product_data' ); ?>
    </div>
</div>