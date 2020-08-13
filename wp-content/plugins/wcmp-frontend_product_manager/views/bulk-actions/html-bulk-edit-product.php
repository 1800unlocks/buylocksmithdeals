<?php
/**
 * Vendor dashboard products page bulk edit template
 *
 * Used by WCMp_AFM_Endpoint->add_modal_html()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/bulk-actions/html-bulk-edit-product.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/bulk-actions
 * @version     3.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<form id="wcmp-afm-bulk-edit-form" class="form-horizontal" method="post">
    <?php do_action( 'wcmp_afm_product_bulk_edit_start' ); ?>
    <div class="row">
        <div class="col-md-5">
            <div class="form-group">
                <label class="control-label col-md-12"><?php esc_html_e( 'Products to edit', WCMp_AFM_TEXT_DOMAIN ); ?></label>
                <div class="col-md-12">
                    <select multiple class="edit_product_list form-control" name="edit_product_list[]"></select>
                </div>
            </div>
            <?php
            $product_taxonomies = get_object_taxonomies( 'product', 'objects' );
            if ( ! empty( $product_taxonomies ) ) {
                foreach ( $product_taxonomies as $product_taxonomy ) {
                    if ( ! in_array( $product_taxonomy->name, apply_filters( 'afm_exclude_bulk_edit_taxonomies', array( 'product_cat', 'product_tag' ) ) ) ) {
                        if ( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb ) {
                            afm()->template->get_template( 'products/metabox/html-taxonomy-metabox.php', array( 'product_id' => null, 'product_taxonomy' => $product_taxonomy ) );
                        }
                    }
                }
            }
            ?>
        </div>
        <div class="col-md-7">
            <div class="form-group">
                <label class="control-label col-md-12"><?php esc_html_e( 'Price', 'woocommerce' ); ?></label>
                <div class="col-md-12">
                    <select class="change_regular_price change_to form-control" name="change_regular_price">
                        <?php
                        $options = array(
                            ''  => __( '— No change —', 'woocommerce' ),
                            '1' => __( 'Change to:', 'woocommerce' ),
                            '2' => __( 'Increase existing price by (fixed amount or %):', 'woocommerce' ),
                            '3' => __( 'Decrease existing price by (fixed amount or %):', 'woocommerce' ),
                        );
                        foreach ( $options as $key => $value ) {
                            echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                        }
                        ?>
                    </select>
                    <div class="change-input">
                        <input type="text" name="_regular_price" class="regular_price form-control" placeholder="<?php printf( esc_attr__( 'Enter price (%s)', 'woocommerce' ), get_woocommerce_currency_symbol() ); ?>" value="" />
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-12"><?php esc_html_e( 'Sale', 'woocommerce' ); ?></label>
                <div class="col-md-12">
                    <select class="change_sale_price change_to form-control" name="change_sale_price">
                        <?php
                        $options = array(
                            ''  => __( '— No change —', 'woocommerce' ),
                            '1' => __( 'Change to:', 'woocommerce' ),
                            '2' => __( 'Increase existing sale price by (fixed amount or %):', 'woocommerce' ),
                            '3' => __( 'Decrease existing sale price by (fixed amount or %):', 'woocommerce' ),
                            '4' => __( 'Set to regular price decreased by (fixed amount or %):', 'woocommerce' ),
                        );
                        foreach ( $options as $key => $value ) {
                            echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                        }
                        ?>
                    </select>
                    <div class="change-input">
                        <input type="text" name="_sale_price" class="sale_price form-control" placeholder="<?php printf( esc_attr__( 'Enter sale price (%s)', 'woocommerce' ), get_woocommerce_currency_symbol() ); ?>" value="" />
                    </div>
                </div>
            </div>
            <?php if ( afm_is_enabled_vendor_tax() ) : ?>
                <div class="form-group-row">
                    <div class="form-group">
                        <label class="control-label col-md-12"><?php esc_html_e( 'Tax status', 'woocommerce' ); ?></label>
                        <div class="col-md-12">
                            <select class="tax_status form-control" name="_tax_status">
                                <?php
                                $options = array(
                                    ''         => __( '— No change —', 'woocommerce' ),
                                    'taxable'  => __( 'Taxable', 'woocommerce' ),
                                    'shipping' => __( 'Shipping only', 'woocommerce' ),
                                    'none'     => _x( 'None', 'Tax status', 'woocommerce' ),
                                );
                                foreach ( $options as $key => $value ) {
                                    echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-12"><?php esc_html_e( 'Tax class', 'woocommerce' ); ?></label>
                        <div class="col-md-12">
                            <select class="tax_class form-control" name="_tax_class">
                                <?php
                                $options = array(
                                    ''         => __( '— No change —', 'woocommerce' ),
                                    'standard' => __( 'Standard', 'woocommerce' ),
                                );

                                $tax_classes = WC_Tax::get_tax_classes();

                                if ( ! empty( $tax_classes ) ) {
                                    foreach ( $tax_classes as $class ) {
                                        $options[sanitize_title( $class )] = esc_html( $class );
                                    }
                                }

                                foreach ( $options as $key => $value ) {
                                    echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ( afm_is_allowed_vendor_shipping() ) : ?>
                <?php if ( wc_product_weight_enabled() ) : ?>
                    <div class="form-group">
                        <label class="control-label col-md-12"><?php esc_html_e( 'Weight', 'woocommerce' ); ?></label>
                        <div class="col-md-12">
                            <select class="change_weight change_to form-control" name="change_weight">
                                <?php
                                $options = array(
                                    ''  => __( '— No change —', 'woocommerce' ),
                                    '1' => __( 'Change to:', 'woocommerce' ),
                                );
                                foreach ( $options as $key => $value ) {
                                    echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                                }
                                ?>
                            </select>
                            <div class="change-input">
                                <input type="text" name="_weight" class="weight form-control" placeholder="<?php printf( esc_attr__( '%1$s (%2$s)', 'woocommerce' ), wc_format_localized_decimal( 0 ), get_option( 'woocommerce_weight_unit' ) ); ?>" value="" />
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( wc_product_dimensions_enabled() ) : ?>
                    <div class="form-group">
                        <label class="control-label col-md-12"><?php esc_html_e( 'L/W/H', 'woocommerce' ); ?></label>
                        <div class="col-md-12">
                            <select class="change_dimensions change_to form-control" name="change_dimensions">
                                <?php
                                $options = array(
                                    ''  => __( '— No change —', 'woocommerce' ),
                                    '1' => __( 'Change to:', 'woocommerce' ),
                                );
                                foreach ( $options as $key => $value ) {
                                    echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                                }
                                ?>
                            </select>
                            <div class="change-input">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" name="_length" class="length form-control col-md-4" placeholder="<?php printf( esc_attr__( 'Length (%s)', 'woocommerce' ), get_option( 'woocommerce_dimension_unit' ) ); ?>" value="">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="_width" class="width form-control col-md-4" placeholder="<?php printf( esc_attr__( 'Width (%s)', 'woocommerce' ), get_option( 'woocommerce_dimension_unit' ) ); ?>" value="">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="_height" class="height form-control col-md-4" placeholder="<?php printf( esc_attr__( 'Height (%s)', 'woocommerce' ), get_option( 'woocommerce_dimension_unit' ) ); ?>" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="control-label col-md-12"><?php esc_html_e( 'Shipping class', 'woocommerce' ); ?></label>
                    <div class="col-md-12">
                        <select class="shipping_class form-control" name="_shipping_class">
                            <option value=""><?php _e( '— No change —', 'woocommerce' ); ?></option>
                            <option value="_no_shipping_class"><?php _e( 'No shipping class', 'woocommerce' ); ?></option>
                            <?php
                            foreach ( get_current_vendor_shipping_classes() as $key => $value ) {
                                echo '<option value="' . esc_attr( $value['slug'] ) . '">' . $value['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ( afm_is_allowed_vendor_feature_product() ) : ?>
                <div class="form-group">
                    <label class="control-label col-md-12"><?php esc_html_e( 'Visibility', 'woocommerce' ); ?></label>
                    <div class="col-md-12">
                        <select class="visibility form-control" name="_visibility">
                            <?php
                            $options = array(
                                ''        => __( '— No change —', 'woocommerce' ),
                                'visible' => __( 'Catalog &amp; search', 'woocommerce' ),
                                'catalog' => __( 'Catalog', 'woocommerce' ),
                                'search'  => __( 'Search', 'woocommerce' ),
                                'hidden'  => __( 'Hidden', 'woocommerce' ),
                            );
                            foreach ( $options as $key => $value ) {
                                echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-12"><?php esc_html_e( 'Featured', 'woocommerce' ); ?></label>
                    <div class="col-md-12">
                        <select class="featured form-control" name="_featured">
                            <?php
                            $options = array(
                                ''    => __( '— No change —', 'woocommerce' ),
                                'yes' => __( 'Yes', 'woocommerce' ),
                                'no'  => __( 'No', 'woocommerce' ),
                            );
                            foreach ( $options as $key => $value ) {
                                echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ( afm_is_allowed_vendor_manage_stock() ) : ?>
                <div class="form-group">
                    <label class="control-label col-md-12"><?php esc_html_e( 'In stock?', 'woocommerce' ); ?></label>
                    <div class="col-md-12">
                        <select class="stock_status form-control" name="_stock_status">
                            <?php
                            echo '<option value="">' . esc_html__( '— No Change —', 'woocommerce' ) . '</option>';

                            foreach ( wc_get_product_stock_status_options() as $key => $value ) {
                                echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-12"><?php esc_html_e( 'Manage stock?', 'woocommerce' ); ?></label>
                    <div class="col-md-12">
                        <select class="manage_stock form-control" name="_manage_stock">
                            <?php
                            $options = array(
                                ''    => __( '— No change —', 'woocommerce' ),
                                'yes' => __( 'Yes', 'woocommerce' ),
                                'no'  => __( 'No', 'woocommerce' ),
                            );
                            foreach ( $options as $key => $value ) {
                                echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-12"><?php esc_html_e( 'Stock qty', 'woocommerce' ); ?></label>
                    <div class="col-md-12">
                        <select class="change_stock change_to form-control" name="change_stock">
                            <?php
                            $options = array(
                                ''  => __( '— No change —', 'woocommerce' ),
                                '1' => __( 'Change to:', 'woocommerce' ),
                            );
                            foreach ( $options as $key => $value ) {
                                echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                            }
                            ?>
                        </select>
                        <div class="change-input">
                            <input type="text" name="_stock" class="stock form-control" placeholder="<?php esc_attr_e( 'Stock qty', 'woocommerce' ); ?>" step="any" value="">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-12"><?php esc_html_e( 'Backorders?', 'woocommerce' ); ?></label>
                    <div class="col-md-12">
                        <select class="backorders form-control" name="_backorders">
                            <?php
                            echo '<option value="">' . esc_html__( '— No Change —', 'woocommerce' ) . '</option>';

                            foreach ( wc_get_product_backorder_options() as $key => $value ) {
                                echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-12"><?php esc_html_e( 'Sold individually?', 'woocommerce' ); ?></label>
                    <div class="col-md-12">
                        <select class="sold_individually form-control" name="_sold_individually">
                            <?php
                            $options = array(
                                ''    => __( '— No change —', 'woocommerce' ),
                                'yes' => __( 'Yes', 'woocommerce' ),
                                'no'  => __( 'No', 'woocommerce' ),
                            );
                            foreach ( $options as $key => $value ) {
                                echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php do_action( 'wcmp_afm_product_bulk_edit_end' ); ?>
</form>