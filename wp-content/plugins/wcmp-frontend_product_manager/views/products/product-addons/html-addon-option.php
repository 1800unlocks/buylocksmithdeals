<?php
/**
 * Add-ons product tab Addon list item template
 *
 * Used by html-product-addons.php template
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/product-addons/html-addon-option.php.
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
<tr>
    <td><input type="text" class="form-control" name="product_addon_option_label[<?php echo $loop; ?>][]" value="<?php esc_attr_e( $option['label'] ); ?>" placeholder="<?php esc_html_e( 'Default Label', 'woocommerce-product-addons' ); ?>" /></td>
    <td class="price_column"><input type="text" class="form-control wc_input_price" name="product_addon_option_price[<?php echo $loop; ?>][]" value="<?php esc_attr_e( wc_format_localized_price( $option['price'] ) ); ?>" placeholder="0.00" /></td>
    <td class="minmax_column">
        <input type="number" class="form-control inline-input" name="product_addon_option_min[<?php echo $loop; ?>][]" value="<?php esc_attr_e( $option['min'] ) ?>" placeholder="Min" min="0" step="any" />
        <input type="number" class="form-control inline-input" name="product_addon_option_max[<?php echo $loop; ?>][]" value="<?php esc_attr_e( $option['max'] ) ?>" placeholder="Max" min="0" step="any" />
    </td>

    <?php do_action( 'wcmp_afm_product_addons_panel_option_row', isset( $product_object ) ? $product_object : null, $product_addons, $loop, $option ); ?>

    <td class="actions" width="1%"><a href="#" class="delete remove_addon_option" title="<?php esc_html_e( 'Delete', 'woocommerce' ); ?>"><i class="wcmp-font ico-delete-icon"></i></a></td>
</tr>