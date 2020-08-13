<?php
/**
 * Bundled Products tab Added products to bundle Basic Settings tab template
 * Not overridable
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/bundle
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="row-padding">
    <?php
    $product = isset( $item_data['bundled_item'] ) ? $item_data['bundled_item']->product : wc_get_product( $product_id );

    if ( in_array( $product->get_type(), array( 'variable', 'variable-subscription' ) ) ) {

        $allowed_variations = isset( $item_data['allowed_variations'] ) ? $item_data['allowed_variations'] : '';
        $default_attributes = isset( $item_data['default_variation_attributes'] ) ? $item_data['default_variation_attributes'] : '';

        $override_variations = isset( $item_data['override_variations'] ) && 'yes' === $item_data['override_variations'] ? 'yes' : '';
        $override_defaults = isset( $item_data['override_default_variation_attributes'] ) && 'yes' === $item_data['override_default_variation_attributes'] ? 'yes' : '';
        ?>
        <div class="form-group-row override_variations">
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="bundle_data[<?php echo $loop; ?>][override_variations]"><?php esc_html_e( 'Filter Variations', 'woocommerce-product-bundles' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="checkbox" class="form-control"<?php echo ( 'yes' === $override_variations ? ' checked="checked"' : '' ); ?> id="bundle_data[<?php echo $loop; ?>][override_variations]" name="bundle_data[<?php echo $loop; ?>][override_variations]" <?php echo ( 'yes' === $override_variations ? 'value="1"' : '' ); ?>/>
                    <span class="description form-text"><?php esc_html_e( 'Check to enable only a subset of the available variations.', 'woocommerce-product-bundles' ); ?></span>
                    <div class="allowed_variations" <?php echo 'yes' === $override_variations ? '' : 'style="display:none;"'; ?>>
                        <?php
                        $variations = $product->get_children();
                        $attributes = $product->get_attributes();

                        if ( sizeof( $variations ) < 50 ) {
                            ?>
                            <select class="form-control wc-enhanced-select" multiple="multiple" name="bundle_data[<?php echo $loop; ?>][allowed_variations][]" data-placeholder="<?php _e( 'Choose variations&hellip;', 'woocommerce-product-bundles' ); ?>">
                                <?php
                                foreach ( $variations as $variation_id ) {

                                    if ( is_array( $allowed_variations ) && in_array( $variation_id, $allowed_variations ) ) {
                                        $selected = 'selected="selected"';
                                    } else {
                                        $selected = '';
                                    }

                                    $variation_description = WC_PB_Helpers::get_product_variation_title( $variation_id, 'flat' );

                                    if ( ! $variation_description ) {
                                        continue;
                                    }

                                    echo '<option value="' . $variation_id . '" ' . $selected . '>' . $variation_description . '</option>';
                                }
                                ?>
                            </select>
                            <?php
                        } else {
                            $allowed_variations_descriptions = array();
                            if ( ! empty( $allowed_variations ) ) {
                                foreach ( $allowed_variations as $allowed_variation_id ) {
                                    $variation_description = WC_PB_Helpers::get_product_variation_title( $allowed_variation_id, 'flat' );
                                    if ( ! $variation_description ) {
                                        continue;
                                    }
                                    $allowed_variations_descriptions[$allowed_variation_id] = $variation_description;
                                }
                            }
                            ?>
                            <select class="form-control wc-enhanced-select" multiple="multiple" name="bundle_data[<?php echo $loop; ?>][allowed_variations][]" data-placeholder="<?php esc_attr_e( 'Search for variations&hellip;', 'woocommerce-product-bundles' ); ?>" data-action="woocommerce_search_bundled_variations" data-limit="500" data-include="<?php echo $product_id; ?>"><?php
                                foreach ( $allowed_variations_descriptions as $allowed_variation_id => $allowed_variation_description ) {
                                    echo '<option value="' . esc_attr( $allowed_variation_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $allowed_variation_description ) . '</option>';
                                }
                                ?>
                            </select>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div> 
        </div>
        <div class="form-group-row override_default_variation_attributes">
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="bundle_data[<?php echo $loop; ?>][override_default_variation_attributes]"><?php esc_html_e( 'Override Default Selections', 'woocommerce-product-bundles' ) ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="checkbox" class="form-control"<?php echo ( 'yes' === $override_defaults ? ' checked="checked"' : '' ); ?> id="bundle_data[<?php echo $loop; ?>][override_default_variation_attributes]" name="bundle_data[<?php echo $loop; ?>][override_default_variation_attributes]" <?php echo ( 'yes' === $override_defaults ? 'value="1"' : '' ); ?>/>
                    <span class="description form-text"><?php esc_html_e( 'In effect for this bundle only. The available options are in sync with the filtering settings above. Always save any changes made above before configuring this section.', 'woocommerce-product-bundles' ); ?></span>
                    <div class="default_variation_attributes" <?php echo 'yes' === $override_defaults ? '' : 'style="display:none;"'; ?>>
                        <?php
                        foreach ( $attributes as $attribute ) {

                            if ( ! $attribute->get_variation() ) {
                                continue;
                            }

                            $selected_value = isset( $default_attributes[sanitize_title( $attribute->get_name() )] ) ? $default_attributes[sanitize_title( $attribute->get_name() )] : '';
                            ?>
                            <select class="form-control inline-select" name="bundle_data[<?php echo $loop; ?>][default_variation_attributes][<?php echo sanitize_title( $attribute->get_name() ); ?>]" data-current="<?php echo esc_attr( $selected_value ); ?>">
                                <option value=""><?php echo esc_html( sprintf( __( 'No default %s&hellip;', 'woocommerce' ), wc_attribute_label( $attribute->get_name() ) ) ); ?></option>
                                <?php
                                if ( $attribute->is_taxonomy() ) {
                                    foreach ( $attribute->get_terms() as $option ) {
                                        ?>
                                        <option <?php selected( $selected_value, $option->slug ); ?> value="<?php echo esc_attr( $option->slug ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option->name ) ); ?></option>
                                        <?php
                                    }
                                } else {
                                    foreach ( $attribute->get_options() as $option ) {
                                        ?>
                                        <option <?php selected( $selected_value, $option ); ?> value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ); ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    $item_quantity = isset( $item_data['quantity_min'] ) ? absint( $item_data['quantity_min'] ) : 1;
    $item_quantity_max = $item_quantity;

    if ( isset( $item_data['quantity_max'] ) ) {
        if ( '' !== $item_data['quantity_max'] ) {
            $item_quantity_max = absint( $item_data['quantity_max'] );
        } else {
            $item_quantity_max = '';
        }
    }

    $is_priced_individually = isset( $item_data['priced_individually'] ) && 'yes' === $item_data['priced_individually'] ? 'yes' : '';
    $is_shipped_individually = isset( $item_data['shipped_individually'] ) && 'yes' === $item_data['shipped_individually'] ? 'yes' : '';
    $item_discount = isset( $item_data['discount'] ) && (double) $item_data['discount'] > 0 ? $item_data['discount'] : '';
    $is_optional = isset( $item_data['optional'] ) ? $item_data['optional'] : '';

// When adding a subscription-type product for the first time, enable "Priced Individually" by default.
    if ( did_action( 'wp_ajax_woocommerce_add_bundled_product' ) && $product->is_type( array( 'subscription', 'variable-subscription' ) ) && ! isset( $item_data['priced_individually'] ) ) {
        $is_priced_individually = 'yes';
    }
    ?>
    <div class="form-group-row optional">
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="bundle_data[<?php echo $loop; ?>][optional]">
                <?php esc_html_e( 'Optional', 'woocommerce-product-bundles' ) ?>
                <span class="img_tip" data-desc="<?php esc_attr_e( 'Check this option to mark the bundled product as optional.', 'woocommerce' ); ?>"></span>      
            </label>
            <div class="col-md-6 col-sm-9">
                <input type="checkbox" class="form-control"<?php echo ( 'yes' === $is_optional ? ' checked="checked"' : '' ); ?> id="bundle_data[<?php echo $loop; ?>][optional]" name="bundle_data[<?php echo $loop; ?>][optional]" <?php echo ( 'yes' === $is_optional ? 'value="1"' : '' ); ?>/>
            </div>
        </div>
    </div>
    <div class="form-group-row quantity_min">
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="bundle_data[<?php echo $loop; ?>][quantity_min]">
                <?php esc_html_e( 'Quantity Min', 'woocommerce-product-bundles' ); ?>
                <span class="img_tip" data-desc="<?php esc_attr_e( 'The minimum/default quantity of this bundled product.', 'woocommerce' ); ?>"></span>    
            </label>
            <div class="col-md-6 col-sm-9">
                <input type="number" class="form-control item_quantity" size="6" id="bundle_data[<?php echo $loop; ?>][quantity_min]" name="bundle_data[<?php echo $loop; ?>][quantity_min]" value="<?php echo $item_quantity; ?>" step="any" min="0" /> 
            </div>
        </div>
    </div>
    <div class="form-group-row quantity_max">
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="bundle_data[<?php echo $loop; ?>][quantity_max]">
                <?php esc_html_e( 'Quantity Max', 'woocommerce-product-bundles' ); ?>
                <span class="img_tip" data-desc="<?php esc_attr_e( 'The maximum quantity of this bundled product. Leave the field empty for an unlimited maximum quantity.', 'woocommerce' ); ?>"></span> 
            </label>
            <div class="col-md-6 col-sm-9">
                <input type="number" class="form-control item_quantity" size="6" id="bundle_data[<?php echo $loop; ?>][quantity_max]" name="bundle_data[<?php echo $loop; ?>][quantity_max]" value="<?php echo $item_quantity_max; ?>" step="any" min="0" />
            </div>
        </div>
    </div>

    <?php if ( $product->needs_shipping() ) : ?>

        <div class="form-group-row shipped_individually">
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="bundle_data[<?php echo $loop; ?>][shipped_individually]">
                    <?php esc_html_e( 'Shipped Individually', 'woocommerce-product-bundles' ); ?>
                    <span class="img_tip" data-desc="<?php esc_attr_e( 'Check this option if this bundled item is shipped separately from the bundle.', 'woocommerce' ); ?>"></span> 
                </label>
                <div class="col-md-6 col-sm-9">
                    <input type="checkbox" class="form-control"<?php echo ( 'yes' === $is_shipped_individually ? ' checked="checked"' : '' ); ?> id="bundle_data[<?php echo $loop; ?>][shipped_individually]" name="bundle_data[<?php echo $loop; ?>][shipped_individually]" <?php echo ( 'yes' === $is_shipped_individually ? 'value="1"' : '' ); ?>/> 
                </div>
            </div>
        </div>

    <?php endif; ?>

    <div class="form-group-row priced_individually">
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="bundle_data[<?php echo $loop; ?>][priced_individually]">
                <?php esc_html_e( 'Priced Individually', 'woocommerce-product-bundles' ); ?>
                <span class="img_tip" data-desc="<?php esc_attr_e( 'Check this option to have the price of this bundled item added to the base price of the bundle.', 'woocommerce' ); ?>"></span> 
            </label>
            <div class="col-md-6 col-sm-9">
                <input type="checkbox" class="form-control"<?php echo ( 'yes' === $is_priced_individually ? ' checked="checked"' : '' ); ?> id="bundle_data[<?php echo $loop; ?>][priced_individually]" name="bundle_data[<?php echo $loop; ?>][priced_individually]" <?php echo ( 'yes' === $is_priced_individually ? 'value="1"' : '' ); ?>/>
            </div>
        </div>
    </div>

    <div class="form-group-row discount" <?php echo 'yes' === $is_priced_individually ? '' : 'style="display:none;"'; ?>>
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="bundle_data[<?php echo $loop; ?>][discount]">
                <?php esc_html_e( 'Discount %', 'woocommerce-product-bundles' ); ?>
                <span class="img_tip" data-desc="<?php esc_attr_e( 'Discount applied to the regular price of this bundled product when Priced Individually is checked. If a Discount is applied to a bundled product which has a sale price defined, the sale price will be overridden.', 'woocommerce' ); ?>"></span> 
            </label>
            <div class="col-md-6 col-sm-9">
                <input type="text" class="form-control item_discount wc_input_decimal" size="5" id="bundle_data[<?php echo $loop; ?>][discount]" name="bundle_data[<?php echo $loop; ?>][discount]" value="<?php echo $item_discount; ?>" /> 
            </div>
        </div>
    </div>
</div>