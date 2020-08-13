<?php
/**
 * Add-ons product tab Addons list template
 *
 * Used by html-product-data-product-addons.php template
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/product-addons/html-product-addons.php.
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
<div class="wcmp-metabox-wrapper woocommerce_product_addon wc-metabox closed" rel="<?php echo esc_attr( $loop ); ?>">
    <div class="wcmp-metabox-title addon-title title-with-type" data-toggle="collapse" data-target="#product_addon_<?php echo esc_attr( $loop ); ?>"  aria-expanded="false" aria-controls="collapseExample">
        <div class="addon-select-group">
            <span class="sortable-icon"></span>
            <strong class="addon_name"><?php esc_html_e( 'Group', 'woocommerce-product-addons' ); ?> <span class="group_name"><?php if ( $addon['name'] ) echo '"' . esc_html( $addon['name'] ) . '"'; ?></span> &mdash; </strong>
            <select name="product_addon_type[<?php echo $loop; ?>]" class="form-control inline-select product_addon_type">
                <?php
                foreach( $product_addon_type as $key => $val ) {
                    if( is_array( $val ) ) {
                        if( ! empty( $val['label'] ) && is_array( $val['options'] ) ) {
                            echo '<optgroup label="' . esc_attr( $val['label'] ) .'">';
                            foreach( $val['options'] as $opt_key => $opt_val ) {
                                echo '<option value="' . $opt_key . '"' . selected( $opt_key, $addon['type'] ) . '>'.esc_html($opt_val).'</option>';
                            }
                            echo '</optgroup>';
                        }
                    } else {
                        echo '<option value="' . $key . '"' . selected( $key, $addon['type'] ) . '>'.esc_html($val).'</option>';
                    }
                }
                ?>
            </select>
            <input type="hidden" name="product_addon_position[<?php echo $loop; ?>]" class="product_addon_position" value="<?php echo $loop; ?>" />
        </div>
        <div class="wcmp-metabox-action addon-action">
            <i class="wcmp-font ico-up-arrow-icon"></i>
            <a href="#" class="remove_row delete remove-addon"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></a>
        </div>
    </div>

    <div class="wcmp-metabox-content woocommerce_product_addon_data wc-metabox-content collapse" id="product_addon_<?php esc_attr_e( $loop ); ?>">
        <table class="table table-outer-border table-addon-list mb-0">
            <tbody>
                <tr>
                    <td class="addon_name">
                        <label><?php esc_html_e( 'Name', 'woocommerce' ); ?></label>
                        <input type="text" class="form-control" name="product_addon_name[<?php echo $loop; ?>]" value="<?php esc_attr_e( $addon['name'] ); ?>" />
                    </td>
                    <td class="addon_required">
                        <label><?php esc_html_e( 'Required fields?', 'woocommerce-product-addons' ); ?></label>
                        <input type="checkbox" class="form-control" name="product_addon_required[<?php echo $loop; ?>]" <?php checked( $addon['required'], 1 ) ?> />
                    </td>
                </tr>
                <tr>
                    <td class="addon_description" colspan="2">
                        <label><?php esc_html_e( 'Description', 'woocommerce-product-addons' ); ?></label>
                        <textarea cols="20" rows="3" class="form-control" name="product_addon_description[<?php echo $loop; ?>]"><?php echo esc_textarea( $addon['description'] ) ?></textarea>
                    </td>
                </tr>
                <?php do_action( 'wcmp_afm_product_addons_panel_before_options', $product_object, $addon, $loop ); ?>
                <tr>
                    <td class="data" colspan="2">
                        <table class="table table-outer-border table-addon-options mb-0">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Label', 'woocommerce-product-addons' ); ?></th>
                                    <th class="price_column"><?php esc_html_e( 'Price', 'woocommerce-product-addons' ); ?></th>
                                    <th class="minmax_column"><span class="column-title"><?php esc_html_e( 'Min / Max', 'woocommerce-product-addons' ); ?></span></th>
                                    <?php do_action( 'wcmp_afm_product_addons_panel_option_heading', $product_object, $addon, $loop ); ?>
                                    <th width="1%"></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td colspan="4">
                                        <a href="#" class="btn btn-default add_addon_option" data-row="<?php
                                        $option = WCMp_AFM_Product_Addons_Integration::get_new_addon_option();
                                        ob_start();
                                        afm()->template->get_template( 'products/product-addons/html-addon-option.php', array( 'option' => $option, 'loop' => $loop, 'product_addons' => $product_addons, 'addon' => $addon, 'product_addon_type' => $product_addon_type, 'product_object' => $product_object ) );
                                        echo esc_attr( ob_get_clean() );
                                        ?>"><?php esc_html_e( 'New&nbsp;Option', 'woocommerce-product-addons' ); ?>
                                        </a>
                                    </td>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php
                                foreach ( $addon['options'] as $option ) {
                                    afm()->template->get_template( 'products/product-addons/html-addon-option.php', array( 'option' => $option, 'loop' => $loop, 'product_addons' => $product_addons, 'addon' => $addon, 'product_addon_type' => $product_addon_type, 'product_object' => $product_object ) );
                                }
                                ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>