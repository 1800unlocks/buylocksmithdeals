<?php

/**
 * Variations product tab template
 *
 * Used by wcmp-afm-add-product.php template
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/woocommerce/html-product-data-variations.php.
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

global $wpdb;

$variation_attributes = array_filter( $product_object->get_attributes(), array( $self, 'filter_variation_attributes' ) );
$default_attributes = $product_object->get_default_attributes();
$variations_count = absint( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'product_variation' AND post_status IN ('publish', 'private')", $post->ID ) ) );
$variations_per_page = absint( apply_filters( 'woocommerce_admin_meta_boxes_variations_per_page', 15 ) );
$variations_total_pages = ceil( $variations_count / $variations_per_page );
?>

<div role="tabpanel" class="tab-pane fade collapsable-component-wrapper" id="variable_product_options">
    <div class="row-padding" id="variable_product_options_inner">
        <?php if ( ! count( $variation_attributes ) ) : ?>
        <div class="row">
            <div class="col-md-12">
                <div id="message" class="inline notice woocommerce-message">
                    <p><?php echo wp_kses_post( __( 'Before you can add a variation you need to add some variation attributes on the <strong>Attributes</strong> tab.', 'woocommerce' ) ); ?></p>
                    <p><a class="button-primary" href="<?php echo esc_url( apply_filters( 'woocommerce_docs_url', 'https://docs.woocommerce.com/document/variable-product/', 'product-variations' ) ); ?>" target="_blank"><?php esc_html_e( 'Learn more', 'woocommerce' ); ?></a></p>
                </div>
            </div>
        </div>
        <?php else : ?>
        <div class="row">
            <div class="col-md-12">
                <div class="toolbar variations-defaults">
                    <strong><?php esc_html_e( 'Default Form Values', 'woocommerce' ); ?>: </strong>
                    <?php
                    foreach ( $variation_attributes as $attribute ) {
                        $selected_value = isset( $default_attributes[sanitize_title( $attribute->get_name() )] ) ? $default_attributes[sanitize_title( $attribute->get_name() )] : '';
                        ?>
                        <select name="default_attribute_<?php echo esc_attr( sanitize_title( $attribute->get_name() ) ); ?>" data-current="<?php echo esc_attr( $selected_value ); ?>" class="form-control inline-select">
                            <?php /* translators: WooCommerce attribute label */ ?>
                            <option value=""><?php esc_html( printf( __( 'No default %s&hellip;', 'woocommerce' ), wc_attribute_label( $attribute->get_name() ) ) ); ?></option>
                            <?php if ( $attribute->is_taxonomy() ) : ?>
                                <?php foreach ( $attribute->get_terms() as $option ) : ?>
                                    <option <?php selected( $selected_value, $option->slug ); ?> value="<?php echo esc_attr( $option->slug ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option->name ) ); ?></option>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <?php foreach ( $attribute->get_options() as $option ) : ?>
                                    <option <?php selected( $selected_value, $option ); ?> value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php
                    }
                    ?>
                    <hr>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="toolbar toolbar-top">
                    <select id="field_to_edit" class="variation_actions inline-select form-control">
                        <option data-global="true" value="add_variation"><?php esc_html_e( 'Add variation', 'woocommerce' ); ?></option>
                        <option data-global="true" value="link_all_variations"><?php esc_html_e( 'Create variations from all attributes', 'woocommerce' ); ?></option>
                        <option value="delete_all"><?php esc_html_e( 'Delete all variations', 'woocommerce' ); ?></option>
                        <optgroup label="<?php esc_attr_e( 'Status', 'woocommerce' ); ?>">
                            <option value="toggle_enabled"><?php esc_html_e( 'Toggle &quot;Enabled&quot;', 'woocommerce' ); ?></option>
                            <option value="toggle_downloadable"><?php esc_html_e( 'Toggle &quot;Downloadable&quot;', 'woocommerce' ); ?></option>
                            <option value="toggle_virtual"><?php esc_html_e( 'Toggle &quot;Virtual&quot;', 'woocommerce' ); ?></option>
                        </optgroup>
                        <optgroup label="<?php esc_attr_e( 'Pricing', 'woocommerce' ); ?>">
                            <option value="variable_regular_price"><?php esc_html_e( 'Set regular prices', 'woocommerce' ); ?></option>
                            <option value="variable_regular_price_increase"><?php esc_html_e( 'Increase regular prices (fixed amount or percentage)', 'woocommerce' ); ?></option>
                            <option value="variable_regular_price_decrease"><?php esc_html_e( 'Decrease regular prices (fixed amount or percentage)', 'woocommerce' ); ?></option>
                            <option value="variable_sale_price"><?php esc_html_e( 'Set sale prices', 'woocommerce' ); ?></option>
                            <option value="variable_sale_price_increase"><?php esc_html_e( 'Increase sale prices (fixed amount or percentage)', 'woocommerce' ); ?></option>
                            <option value="variable_sale_price_decrease"><?php esc_html_e( 'Decrease sale prices (fixed amount or percentage)', 'woocommerce' ); ?></option>
                            <option value="variable_sale_schedule"><?php esc_html_e( 'Set scheduled sale dates', 'woocommerce' ); ?></option>
                        </optgroup>
                        <optgroup label="<?php esc_attr_e( 'Inventory', 'woocommerce' ); ?>">
                            <option value="toggle_manage_stock"><?php esc_html_e( 'Toggle &quot;Manage stock&quot;', 'woocommerce' ); ?></option>
                            <option value="variable_stock"><?php esc_html_e( 'Stock', 'woocommerce' ); ?></option>
                            <option value="variable_stock_status_instock"><?php esc_html_e( 'Set Status - In stock', 'woocommerce' ); ?></option>
                            <option value="variable_stock_status_outofstock"><?php esc_html_e( 'Set Status - Out of stock', 'woocommerce' ); ?></option>
                            <option value="variable_stock_status_onbackorder"><?php esc_html_e( 'Set Status - On backorder', 'woocommerce' ); ?></option>
                        </optgroup>
                        <optgroup label="<?php esc_attr_e( 'Shipping', 'woocommerce' ); ?>">
                            <option value="variable_length"><?php esc_html_e( 'Length', 'woocommerce' ); ?></option>
                            <option value="variable_width"><?php esc_html_e( 'Width', 'woocommerce' ); ?></option>
                            <option value="variable_height"><?php esc_html_e( 'Height', 'woocommerce' ); ?></option>
                            <option value="variable_weight"><?php esc_html_e( 'Weight', 'woocommerce' ); ?></option>
                        </optgroup>
                        <optgroup label="<?php esc_attr_e( 'Downloadable products', 'woocommerce' ); ?>">
                            <option value="variable_download_limit"><?php esc_html_e( 'Download limit', 'woocommerce' ); ?></option>
                            <option value="variable_download_expiry"><?php esc_html_e( 'Download expiry', 'woocommerce' ); ?></option>
                        </optgroup>
                        <?php do_action( 'woocommerce_variable_product_bulk_edit_actions' ); ?>
                    </select>
                    <a class="btn btn-default bulk_edit do_variation_action"><?php esc_html_e( 'Go', 'woocommerce' ); ?></a>
                    <div class="variations-pagenav">
                        <?php /* translators: variations count */ ?>
                        <span class="displaying-num"><?php echo esc_html( sprintf( _n( '%s item', '%s items', $variations_count, 'woocommerce' ), $variations_count ) ); ?></span>
                        <span class="expand-close">
                            (<a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>)
                        </span>
                        <span class="pagination-links">
                            <a class="first-page disabled" title="<?php esc_attr_e( 'Go to the first page', 'woocommerce' ); ?>" href="#">&laquo;</a>
                            <a class="prev-page disabled" title="<?php esc_attr_e( 'Go to the previous page', 'woocommerce' ); ?>" href="#">&lsaquo;</a>
                            <span class="paging-select">
                                <label for="current-page-selector-1" class="screen-reader-text"><?php esc_html_e( 'Select Page', 'woocommerce' ); ?></label>
                                <select class="page-selector" id="current-page-selector-1" title="<?php esc_attr_e( 'Current page', 'woocommerce' ); ?>">
                                    <?php for ( $i = 1; $i <= $variations_total_pages; $i++ ) : ?>
                                        <option value="<?php echo $i; // WPCS: XSS ok. ?>"><?php echo $i; // WPCS: XSS ok. ?></option>
                                    <?php endfor; ?>
                                </select>
                                <?php echo esc_html_x( 'of', 'number of pages', 'woocommerce' ); ?> <span class="total-pages"><?php echo esc_html( $variations_total_pages ); ?></span>
                            </span>
                            <a class="next-page" title="<?php esc_attr_e( 'Go to the next page', 'woocommerce' ); ?>" href="#">&rsaquo;</a>
                            <a class="last-page" title="<?php esc_attr_e( 'Go to the last page', 'woocommerce' ); ?>" href="#">&raquo;</a>
                        </span>
                    </div>
                </div>
            </div> 
        </div>
        <?php
        // esc_attr does not double encode - htmlspecialchars does.
        $attributes_data = htmlspecialchars( wp_json_encode( wc_list_pluck( $variation_attributes, 'get_data' ) ) );
        ?>
        <div class="row">
            <div class="col-md-12">
                <div class="woocommerce_variations product-variations-wrapper wc-metaboxes" data-attributes="<?php echo $attributes_data; // WPCS: XSS ok.   ?>" data-total="<?php echo esc_attr( $variations_count ); ?>" data-total_pages="<?php echo esc_attr( $variations_total_pages ); ?>" data-page="1" data-edited="false">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="toolbar button-group">
                    <button type="button" class="btn btn-default save-variation-changes" disabled="disabled"><?php esc_html_e( 'Save changes', 'woocommerce' ); ?></button>
                    <button type="button" class="btn btn-default cancel-variation-changes" disabled="disabled"><?php esc_html_e( 'Cancel', 'woocommerce' ); ?></button>

                    <div class="variations-pagenav">
                        <?php /* translators: variations count */ ?>
                        <span class="displaying-num"><?php echo esc_html( sprintf( _n( '%s item', '%s items', $variations_count, 'woocommerce' ), $variations_count ) ); ?></span>
                        <span class="expand-close">
                            (<a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>)
                        </span>
                        <span class="pagination-links">
                            <a class="first-page disabled" title="<?php esc_attr_e( 'Go to the first page', 'woocommerce' ); ?>" href="#">&laquo;</a>
                            <a class="prev-page disabled" title="<?php esc_attr_e( 'Go to the previous page', 'woocommerce' ); ?>" href="#">&lsaquo;</a>
                            <span class="paging-select">
                                <label for="current-page-selector-1" class="screen-reader-text"><?php esc_html_e( 'Select Page', 'woocommerce' ); ?></label>
                                <select class="page-selector" id="current-page-selector-1" title="<?php esc_attr_e( 'Current page', 'woocommerce' ); ?>">
                                    <?php for ( $i = 1; $i <= $variations_total_pages; $i ++ ) : ?>
                                        <option value="<?php echo $i; // WPCS: XSS ok.  ?>"><?php echo $i; // WPCS: XSS ok.  ?></option>
                                    <?php endfor; ?>
                                </select>
                                <?php echo esc_html_x( 'of', 'number of pages', 'woocommerce' ); ?> <span class="total-pages"><?php echo esc_html( $variations_total_pages ); ?></span>
                            </span>
                            <a class="next-page" title="<?php esc_attr_e( 'Go to the next page', 'woocommerce' ); ?>" href="#">&rsaquo;</a>
                            <a class="last-page" title="<?php esc_attr_e( 'Go to the last page', 'woocommerce' ); ?>" href="#">&raquo;</a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
