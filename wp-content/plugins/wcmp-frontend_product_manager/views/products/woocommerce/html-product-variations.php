<?php

/**
 * Outputs a variation for editing.
 * Used by WCMp_AFM_Ajax->load_variations_callback()
 * Used by WCMp_AFM_Ajax->add_variation_callback()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/woocommerce/html-product-variations.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      WC Marketplace
 * @package     WCMp_AFM/views/products/woocommerce
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wcmp-metabox-wrapper woocommerce_variation wc-metabox closed">
    <div class="wcmp-metabox-title variation-title" data-toggle="collapse" data-target="#<?php echo esc_attr( $variation_id ); ?>"  aria-expanded="false" aria-controls="collapseExample">
        <div class="variation-select-group">
            <span class="sort sortable-icon tips" data-tip="<?php esc_attr_e( 'Drag and drop, or click to set admin variation order', 'woocommerce' ); ?>"></span>
            <strong>#<?php echo esc_html( $variation_id ); ?> </strong>
            <?php
            $attribute_values = $variation_object->get_attributes( 'edit' );
            foreach ( $product_object->get_attributes( 'edit' ) as $attribute ) {
                if ( ! $attribute->get_variation() ) {
                    continue;
                }
                $selected_value = isset( $attribute_values[sanitize_title( $attribute->get_name() )] ) ? $attribute_values[sanitize_title( $attribute->get_name() )] : '';
                ?>
                <select name="attribute_<?php echo sanitize_title( $attribute->get_name() ) . "[{$loop}]"; ?>" class="inline-select form-control">
                    <option value=""><?php
                        /* translators: %s: attribute label */
                        printf( esc_html__( 'Any %s&hellip;', 'woocommerce' ), wc_attribute_label( $attribute->get_name() ) );
                        ?></option>
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
        </div>
        <div class="wcmp-metabox-action variation-action">
            <i class="wcmp-font ico-up-arrow-icon"></i>
            <a href="#" class="remove_variation delete" rel="<?php echo esc_attr( $variation_id ); ?>"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></a>
        </div>
        <input type="hidden" name="variable_post_id[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation_id ); ?>" />
        <input type="hidden" class="variation_menu_order" name="variation_menu_order[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation_object->get_menu_order( 'edit' ) ); ?>" />
    </div>
    <div class="wcmp-metabox-content woocommerce_variable_attributes wc-metabox-content collapse" id="<?php echo esc_attr( $variation_id ); ?>">
        <div class="data variation-content">
            <div class="row">
                <div class="col-md-7">
                    <div class="form-group">
                        <div class="col-md-5">
                            <div class="variation-img upload_image">
                                <a href="#" class="upload_image_button tips <?php echo $variation_object->get_image_id( 'edit' ) ? 'remove' : ''; ?>" <?php echo current_user_can( 'upload_files' ) ? '' : 'data-nocaps="true" '; ?>data-tip="<?php echo $variation_object->get_image_id( 'edit' ) ? esc_attr__( 'Remove this image', 'woocommerce' ) : esc_attr__( 'Upload an image', 'woocommerce' ); ?>" rel="<?php echo esc_attr( $variation_id ); ?>">
                                    <div class="upload-placeholder pos-middle">
                                        <i class="wcmp-font ico-image-icon"></i> 
                                    </div>
                                    <img src="<?php echo $variation_object->get_image_id( 'edit' ) ? esc_url( wp_get_attachment_thumb_url( $variation_object->get_image_id( 'edit' ) ) ) : esc_url( wc_placeholder_img_src() ); ?>" />
                                    <input type="hidden" name="upload_image_id[<?php echo esc_attr( $loop ); ?>]" class="upload_image_id" value="<?php echo esc_attr( $variation_object->get_image_id( 'edit' ) ); ?>" />
                                </a>
                            </div>
                        </div>
                        <div class="col-md-7 attribute-chk-option form-group-wrapper">
                            <div class="form-group">
                                <label for="variable_enabled[<?php echo esc_attr( $loop ); ?>]"><input type="checkbox" class="form-control" id="variable_enabled[<?php echo esc_attr( $loop ); ?>]" name="variable_enabled[<?php echo esc_attr( $loop ); ?>]" <?php checked( in_array( $variation_object->get_status( 'edit' ), array( 'publish', false ), true ), true ); ?> /> <?php esc_html_e( 'Enabled', 'woocommerce' ); ?></label>
                            </div>
                            <?php if ( afm_is_allowed_downloadable() ) : ?>
                            <div class="form-group">
                                <label for="variable_is_downloadable[<?php echo esc_attr( $loop ); ?>]"><input type="checkbox" class="form-control variable_is_downloadable" id="variable_is_downloadable[<?php echo esc_attr( $loop ); ?>]" name="variable_is_downloadable[<?php echo esc_attr( $loop ); ?>]" <?php checked( $variation_object->get_downloadable( 'edit' ), true ); ?> /> <?php esc_html_e( 'Downloadable', 'woocommerce' ); ?></label>
                            </div>
                            <?php endif; ?>

                            <?php if ( afm_is_allowed_virtual() ) : ?>
                                <div class="form-group">
                                    <label for="variable_is_virtual[<?php echo esc_attr( $loop ); ?>]"><input type="checkbox" class="form-control variable_is_virtual" id="variable_is_virtual[<?php echo esc_attr( $loop ); ?>]" name="variable_is_virtual[<?php echo esc_attr( $loop ); ?>]" <?php checked( $variation_object->get_virtual( 'edit' ), true ); ?> /> <?php esc_html_e( 'Virtual', 'woocommerce' ); ?></label>
                                </div>
                            <?php endif; ?>
                            <?php if ( afm_is_allowed_vendor_manage_stock() ) : ?>
                                <div class="form-group">
                                    <label for="variable_manage_stock[<?php echo esc_attr( $loop ); ?>]"><input type="checkbox" class="form-control variable_manage_stock" id="variable_manage_stock[<?php echo esc_attr( $loop ); ?>]" name="variable_manage_stock[<?php echo esc_attr( $loop ); ?>]" <?php checked( $variation_object->get_manage_stock( 'edit' ), true ); ?> /> <?php esc_html_e( 'Manage stock?', 'woocommerce' ); ?></label>
                                </div>
                            <?php endif; ?>

                            <?php do_action( 'wcmp_afm_variation_options', $loop, $variation_data, $variation ); ?>
                        </div>
                    </div>
                </div>
                <?php if ( wc_product_sku_enabled() ) { ?>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="control-label col-md-12"><?php esc_html_e( 'SKU', 'woocommerce' ); ?></label>
                            <div class="col-md-12">
                                <input type="text" class="form-control" id="variable_sku<?php echo esc_attr( $loop ); ?>" name="variable_sku[<?php echo esc_attr( $loop ); ?>]" value="<?php echo $variation_object->get_sku( 'edit' ); ?>" placeholder="<?php echo $variation_object->get_sku(); ?>">
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php do_action( 'wcmp_afm_after_variation_sku', $loop, $variation_data, $variation ); ?>
            <div class="row form-group-row variable_pricing">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label col-md-12"><?php printf( __( 'Regular price (%s)', 'woocommerce' ), get_woocommerce_currency_symbol() ); ?></label>
                        <div class="col-md-12">
                            <input type="text" class="form-control short wc_input_price" id="variable_regular_price_<?php echo esc_attr( $loop ); ?>" name="variable_regular_price[<?php echo esc_attr( $loop ); ?>]" value="<?php echo wc_format_localized_price( $variation_object->get_regular_price( 'edit' ) ); ?>" placeholder="<?php echo __( 'Variation price (required)', 'woocommerce' ); ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label col-md-12">
                            <?php printf( __( 'Sale price (%s)', 'woocommerce' ), get_woocommerce_currency_symbol() ); ?>
                            <a href="#" class="sale_schedule"><?php echo esc_html__( 'Schedule', 'woocommerce' ); ?></a><a href="#" class="cancel_sale_schedule" style="display:none;"><?php echo esc_html__( 'Cancel schedule', 'woocommerce' ); ?></a>
                        </label>
                        <div class="col-md-12">
                            <input type="text" class="form-control short wc_input_price" id="variable_sale_price<?php echo esc_attr( $loop ); ?>" name="variable_sale_price[<?php echo esc_attr( $loop ); ?>]" value="<?php echo wc_format_localized_price( $variation_object->get_sale_price( 'edit' ) ); ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-12 sale_price_dates_fields" style="display:none;">
                    <div class="row">
                        <?php
                        $sale_price_dates_from = $variation_object->get_date_on_sale_from( 'edit' ) && ( $date = $variation_object->get_date_on_sale_from( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
                        $sale_price_dates_to = $variation_object->get_date_on_sale_to( 'edit' ) && ( $date = $variation_object->get_date_on_sale_to( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
                        ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label col-md-12"><?php echo __( 'Sale start date', 'woocommerce' ); ?></label>
                                <div class="col-md-12">
                                    <input type="text" datepicker class="form-control sale_price_dates_from" name="variable_sale_price_dates_from[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $sale_price_dates_from ); ?>" placeholder="<?php echo _x( 'From&hellip;', 'placeholder', 'woocommerce' ); ?> YYYY-MM-DD" maxlength="10" pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label col-md-12"><?php echo __( 'Sale end date', 'woocommerce' ); ?></label>
                                <div class="col-md-12">
                                    <input type="text" datepicker class="form-control sale_price_dates_to" name="variable_sale_price_dates_to[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $sale_price_dates_to ); ?>" placeholder="<?php echo esc_html_x( 'To&hellip;', 'placeholder', 'woocommerce' ); ?> YYYY-MM-DD" maxlength="10" pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" />
                                </div>
                            </div>
                        </div>
                        <?php
                        /**
                         * woocommerce_variation_options_pricing action.
                         *
                         * @since 2.5.0
                         *
                         * @param int     $loop
                         * @param array   $variation_data
                         * @param WP_Post $variation
                         */
                        do_action( 'wcmp_afm_variation_options_pricing', $loop, $variation_data, $variation );
                        ?>
                    </div>

                </div>
            </div>

            <?php if ( afm_is_allowed_vendor_manage_stock() ) : ?>
                <div class="row show_if_variation_manage_stock">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label col-md-12"><?php echo __( 'Stock quantity', 'woocommerce' ); ?></label>
                            <div class="col-md-12">
                                <input type="number" class="form-control" id="variable_stock<?php echo esc_attr( $loop ); ?>" name="variable_stock[<?php echo esc_attr( $loop ); ?>]" value="<?php echo wc_stock_amount( $variation_object->get_stock_quantity( 'edit' ) ); ?>" step="any" />
                            </div>
                        </div>
                        <input type="hidden" name="variable_original_stock[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_stock_amount( $variation_object->get_stock_quantity( 'edit' ) ) ); ?>" />
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label col-md-12"><?php echo __( 'Allow backorders?', 'woocommerce' ); ?></label>
                            <div class="col-md-12">
                                <select id="variable_backorders<?php echo esc_attr( $loop ); ?>" name="variable_backorders[<?php echo esc_attr( $loop ); ?>]" class="form-control">
                                    <?php foreach ( wc_get_product_backorder_options() as $key => $option ) : ?>
                                        <option value="<?php echo $key; ?>" <?php selected( $variation_object->get_backorders( 'edit' ), $key ); ?>><?php echo $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php
                        /**
                         * woocommerce_variation_options_inventory action.
                         *
                         * @since 2.5.0
                         *
                         * @param int     $loop
                         * @param array   $variation_data
                         * @param WP_Post $variation
                         */
                        do_action( 'wcmp_afm_variation_options_inventory', $loop, $variation_data, $variation );
                        ?>
                    </div>
                </div>
                <div class="row hide_if_variation_manage_stock">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label col-md-12"><?php echo __( 'Stock status', 'woocommerce' ); ?></label>
                            <div class="col-md-12">
                                <select id="variable_stock_status<?php echo esc_attr( $loop ); ?>" name="variable_stock_status[<?php echo esc_attr( $loop ); ?>]" class="form-control">
                                    <?php foreach ( wc_get_product_stock_status_options() as $key => $option ) : ?>
                                        <option value="<?php echo $key; ?>" <?php selected( $variation_object->get_stock_status( 'edit' ), $key ); ?>><?php echo $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>    
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row">
                <?php if ( afm_is_allowed_vendor_shipping() ) : ?>
                    <?php if ( wc_product_weight_enabled() ) : ?>
                        <div class="col-md-6 hide_if_variation_virtual">
                            <div class="form-group">
                                <label class="control-label col-md-12"><?php printf( __( 'Weight (%s)', 'woocommerce' ), esc_html( get_option( 'woocommerce_weight_unit' ) ) ); ?></label>    
                                <div class="col-md-12">
                                    <input type="text" class="form-control" id="variable_weight<?php echo esc_attr( $loop ); ?>" name="variable_weight[<?php echo esc_attr( $loop ); ?>]" value="<?php echo wc_format_localized_decimal( $variation_object->get_weight( 'edit' ) ); ?>" placeholder="<?php echo wc_format_localized_decimal( $product_object->get_weight() ); ?>" />
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ( wc_product_dimensions_enabled() ) : ?>
                        <?php
                        $parent_length = wc_format_localized_decimal( $product_object->get_length() );
                        $parent_width = wc_format_localized_decimal( $product_object->get_width() );
                        $parent_height = wc_format_localized_decimal( $product_object->get_height() );
                        ?>
                        <div class="col-md-6 dimensions_field hide_if_variation_virtual">
                            <div class="form-group">
                                <label class="control-label col-md-12"><?php printf( esc_html__( 'Dimensions (L&times;W&times;H) (%s)', 'woocommerce' ), esc_html( get_option( 'woocommerce_dimension_unit' ) ) ); ?></label>    
                                <div class="col-md-4">
                                    <input placeholder="<?php echo $parent_length ? esc_attr( $parent_length ) : esc_attr__( 'Length', 'woocommerce' ); ?>" class="input-text form-control col-md-4 wc_input_decimal" size="6" type="text" name="variable_length[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( $variation_object->get_length( 'edit' ) ) ); ?>" />
                                </div>
                                <div class="col-md-4">
                                    <input placeholder="<?php echo $parent_width ? esc_attr( $parent_width ) : esc_attr__( 'Width', 'woocommerce' ); ?>" class="input-text form-control col-md-4 wc_input_decimal" size="6" type="text" name="variable_width[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( $variation_object->get_width( 'edit' ) ) ); ?>" />
                                </div>
                                <div class="col-md-4">
                                    <input placeholder="<?php echo $parent_height ? esc_attr( $parent_height ) : esc_attr__( 'Height', 'woocommerce' ); ?>" class="input-text form-control col-md-4 wc_input_decimal" size="6" type="text" name="variable_height[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( $variation_object->get_height( 'edit' ) ) ); ?>" />
                                </div>
                            </div>
                            <?php
                            do_action( 'wcmp_afm_variation_options_dimensions', $loop, $variation_data, $variation );
                            ?>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-6 hide_if_variation_virtual">
                        <div class="form-group">
                            <label class="control-label col-md-12"><?php esc_html_e( 'Shipping class', 'woocommerce' ); ?></label>
                            <div class="col-md-12">
                                <select name="variable_shipping_class[<?php esc_attr_e( $loop ); ?>]" class="form-control regular-select">
                                    <option value="-1"><?php esc_html_e( 'Same as parent', 'woocommerce' ); ?></option>
                                    <?php foreach ( get_current_vendor_shipping_classes() as $key => $value ) : ?>
                                        <option value="<?php esc_attr_e( $key ); ?>" <?php selected( $variation_object->get_shipping_class_id( 'edit' ), $key ); ?>><?php esc_html_e( $value['name'] ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>    
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( afm_is_enabled_vendor_tax() ) : ?>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label col-md-12"><?php esc_html_e( 'Tax class', 'woocommerce' ); ?></label>
                            <div class="col-md-12">
                                <select id="variable_tax_class<?php echo esc_attr( $loop ); ?>" name="variable_tax_class[<?php echo esc_attr( $loop ); ?>]" class="form-control">
                                    <?php $tax_classes = array_merge( array( 'parent' => __( 'Same as parent', 'woocommerce' ) ), wc_get_product_tax_class_options() ); ?>
                                    <?php foreach ( $tax_classes as $key => $option ) : ?>
                                        <option value="<?php echo $key; ?>" <?php selected( $variation_object->get_tax_class( 'edit' ), $key ); ?>><?php echo $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>    
                        </div> 
                    </div><?php
                    /**
                     * wcmp_afm_variation_options_tax action.
                     *
                     * @since 2.5.0
                     *
                     * @param int     $loop
                     * @param array   $variation_data
                     * @param WP_Post $variation
                     */
                    do_action( 'wcmp_afm_variation_options_tax', $loop, $variation_data, $variation );
                    ?>  
                <?php endif; ?>
            </div>
            <div class="row show_if_variation_downloadable" style="display: none;">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label col-sm-3 col-md-3"><?php esc_html_e( 'Downloadable files', 'woocommerce' ); ?></label>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="downloadable_files">
                                <table class="table table-outer-border">
                                    <thead>
                                        <tr>
                                            <th>&nbsp;</th>
                                            <th><?php _e( 'Name', 'woocommerce' ); ?></th>
                                            <th><?php _e( 'File URL', 'woocommerce' ); ?></th>
                                            <th>&nbsp;</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ( $downloads = $variation_object->get_downloads( 'edit' ) ) {
                                            foreach ( $downloads as $key => $file ) {
                                                include( 'html-product-variation-download.php' );
                                            }
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5">
                                                <a href="#" class="btn btn-default insert" data-row="<?php
                                                $key = '';
                                                $file = array(
                                                    'file' => '',
                                                    'name' => '',
                                                );
                                                ob_start();
                                                include( 'html-product-variation-download.php' );
                                                echo esc_attr( ob_get_clean() );
                                                ?>"><?php esc_html_e( 'Add File', 'woocommerce' ); ?></a>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label col-md-12">
                            <?php esc_html_e( 'Download limit', 'woocommerce' ); ?>
                            <span class="img_tip" data-desc="<?php esc_attr_e( 'Leave blank for unlimited re-downloads.', 'woocommerce-product-bundles' ) ?>"></span> 
                        </label>
                        <div class="col-md-12">
                            <input class="form-control" type="number" id="variable_download_limit<?php echo esc_attr( $loop ); ?>" name="variable_download_limit[<?php echo esc_attr( $loop ); ?>]" placeholder="<?php esc_html_e( 'Unlimited', 'woocommerce' ); ?>" value="<?php echo $variation_object->get_download_limit( 'edit' ) < 0 ? '' : $variation_object->get_download_limit( 'edit' ); ?>" step="1" min="0"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label col-md-12">
                            <?php esc_html_e( 'Download expiry', 'woocommerce' ); ?>
                            <span class="img_tip" data-desc="<?php esc_attr_e( 'Enter the number of days before a download link expires, or leave blank.', 'woocommerce-product-bundles' ) ?>"></span> 
                        </label>
                        <div class="col-md-12">
                            <input class="form-control" type="number" id="variable_download_expiry<?php echo esc_attr( $loop ); ?>" name="variable_download_expiry[<?php echo esc_attr( $loop ); ?>]" placeholder="<?php esc_html_e( 'Never', 'woocommerce' ); ?>" value="<?php echo $variation_object->get_download_expiry( 'edit' ) < 0 ? '' : $variation_object->get_download_expiry( 'edit' ); ?>" step="1" min="0"/>
                        </div>
                    </div>
                </div>
                <?php
                /**
                 * wcmp_afm_variation_options_download action.
                 *
                 * @since 2.5.0
                 *
                 * @param int     $loop
                 * @param array   $variation_data
                 * @param WP_Post $variation
                 */
                do_action( 'wcmp_afm_variation_options_download', $loop, $variation_data, $variation );
                ?>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label col-md-12"><?php esc_html_e( 'Description', 'woocommerce' ); ?></label>
                        <div class="col-md-12">
                            <textarea class="form-control" id="variable_description<?php echo esc_attr( $loop ); ?>" name="variable_description[<?php echo esc_attr( $loop ); ?>]" placeholder="<?php esc_html_e( 'Enter an optional description for this variation.', 'woocommerce' ); ?>" rows="2" cols="20"><?php esc_html_e( esc_textarea( $variation_object->get_description( 'edit' ) ) ); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <?php do_action( 'wcmp_afm_product_after_variable_attributes', $loop, $variation_data, $variation ); ?>
        </div>
    </div>
</div>
