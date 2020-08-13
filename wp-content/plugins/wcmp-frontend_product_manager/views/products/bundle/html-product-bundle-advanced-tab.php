<?php
/**
 * Bundled Products tab Added products to bundle Advanced Settings tab template
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
    $is_priced_individually = isset( $item_data['priced_individually'] ) && 'yes' === $item_data['priced_individually'];
    $hide_thumbnail = isset( $item_data['hide_thumbnail'] ) ? $item_data['hide_thumbnail'] : '';
    $override_title = isset( $item_data['override_title'] ) ? $item_data['override_title'] : '';
    $override_description = isset( $item_data['override_description'] ) ? $item_data['override_description'] : '';
    $visibility = array(
        'product' => ! empty( $item_data['single_product_visibility'] ) && 'hidden' === $item_data['single_product_visibility'] ? 'hidden' : 'visible',
        'cart'    => ! empty( $item_data['cart_visibility'] ) && 'hidden' === $item_data['cart_visibility'] ? 'hidden' : 'visible',
        'order'   => ! empty( $item_data['order_visibility'] ) && 'hidden' === $item_data['order_visibility'] ? 'hidden' : 'visible',
    );
    $price_visibility = array(
        'product' => ! empty( $item_data['single_product_price_visibility'] ) && 'hidden' === $item_data['single_product_price_visibility'] ? 'hidden' : 'visible',
        'cart'    => ! empty( $item_data['cart_price_visibility'] ) && 'hidden' === $item_data['cart_price_visibility'] ? 'hidden' : 'visible',
        'order'   => ! empty( $item_data['order_price_visibility'] ) && 'hidden' === $item_data['order_price_visibility'] ? 'hidden' : 'visible',
    );
    ?>
    <div class="form-group-row item_visibility">
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3"><?php esc_html_e( 'Visibility', 'woocommerce-product-bundles' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <div>
                    <label>
                        <input type="checkbox" class="form-control visibility_product"<?php echo ( 'visible' === $visibility['product'] ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][single_product_visibility]" <?php echo ( 'visible' === $visibility['product'] ? 'value="1"' : '' ); ?>/>
                        <?php esc_html_e( 'Product details', 'woocommerce-product-bundles' ); ?>
                    </label>
                    <span class="description form-text"><?php esc_html_e( 'Controls the visibility of the bundled item in the single-product template of this bundle.', 'woocommerce-product-bundles' ); ?></span>
                </div>
                <div>
                    <label>
                        <input type="checkbox" class="form-control visibility_cart"<?php echo ( 'visible' === $visibility['cart'] ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][cart_visibility]" <?php echo ( 'visible' === $visibility['cart'] ? 'value="1"' : '' ); ?>/>
                        <?php esc_html_e( 'Cart/checkout', 'woocommerce-product-bundles' ); ?>
                    </label>
                    <span class="description form-text"><?php esc_html_e( 'Controls the visibility of the bundled item in cart/checkout templates.', 'woocommerce-product-bundles' ); ?></span>
                </div>
                <div>
                    <label>
                        <input type="checkbox" class="form-control visibility_order"<?php echo ( 'visible' === $visibility['order'] ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][order_visibility]" <?php echo ( 'visible' === $visibility['order'] ? 'value="1"' : '' ); ?>/>
                        <?php esc_html_e( 'Order details', 'woocommerce-product-bundles' ); ?>
                    </label>
                    <span class="description form-text"><?php esc_html_e( 'Controls the visibility of the bundled item in order details &amp; e-mail templates.', 'woocommerce-product-bundles' ); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group-row price_visibility" <?php echo $is_priced_individually ? '' : 'style="display:none;"'; ?>>
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3"><?php esc_html_e( 'Price Visibility', 'woocommerce-product-bundles' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <div class="price_visibility_product_wrapper">
                    <label>
                        <input type="checkbox" class="form-control price_visibility_product"<?php echo ( 'visible' === $price_visibility['product'] ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][single_product_price_visibility]" <?php echo ( 'visible' === $price_visibility['product'] ? 'value="1"' : '' ); ?>/>
                        <?php esc_html_e( 'Product details', 'woocommerce-product-bundles' ); ?>
                    </label>
                    <span class="description form-text"><?php esc_html_e( 'Controls the visibility of the bundled-item price in the single-product template of this bundle.', 'woocommerce-product-bundles' ); ?></span>
                </div>
                <div class="price_visibility_cart_wrapper">
                    <label>
                        <input type="checkbox" class="form-control price_visibility_cart"<?php echo ( 'visible' === $price_visibility['cart'] ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][cart_price_visibility]" <?php echo ( 'visible' === $price_visibility['cart'] ? 'value="1"' : '' ); ?>/>
                        <?php esc_html_e( 'Cart/checkout', 'woocommerce-product-bundles' ); ?>
                    </label>
                    <span class="description form-text"><?php esc_html_e( 'Controls the visibility of the bundled-item price in cart/checkout templates.', 'woocommerce-product-bundles' ); ?></span>
                </div>
                <div class="price_visibility_order_wrapper">
                    <label>
                        <input type="checkbox" class="form-control price_visibility_order"<?php echo ( 'visible' === $price_visibility['order'] ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][order_price_visibility]" <?php echo ( 'visible' === $price_visibility['order'] ? 'value="1"' : '' ); ?>/>
                        <?php esc_html_e( 'Order details', 'woocommerce-product-bundles' ); ?>
                    </label>
                    <span class="description form-text"><?php esc_html_e( 'Controls the visibility of the bundled-item price in order details &amp; e-mail templates.', 'woocommerce-product-bundles' ); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="form-group-row hide_thumbnail">
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="bundle_data[<?php echo $loop; ?>][hide_thumbnail]">
                <?php esc_html_e( 'Hide Thumbnail', 'woocommerce-product-bundles' ) ?>
                <span class="img_tip" data-desc="<?php esc_attr_e( 'Check this option to hide the thumbnail image of this bundled product.', 'woocommerce-product-bundles' ) ?>"></span>
            </label>
            <div class="col-md-6 col-sm-9">
                <input type="checkbox" class="form-control"<?php echo ( 'yes' === $hide_thumbnail ? ' checked="checked"' : '' ); ?> id="bundle_data[<?php echo $loop; ?>][hide_thumbnail]" name="bundle_data[<?php echo $loop; ?>][hide_thumbnail]" <?php echo ( 'yes' === $hide_thumbnail ? 'value="1"' : '' ); ?>/>
            </div>
        </div>
    </div>
    
    <div class="form-group-row override_title">
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="bundle_data[<?php echo $loop; ?>][override_title]">
                <?php esc_html_e( 'Override Title', 'woocommerce-product-bundles' ) ?>
                <span class="img_tip" data-desc="<?php esc_attr_e( 'Check this option to override the default product title.', 'woocommerce-product-bundles' ) ?>"></span>
            </label>
            <div class="col-md-6 col-sm-9">
                <input type="checkbox" class="form-control"<?php echo ( 'yes' === $override_title ? ' checked="checked"' : '' ); ?> id="bundle_data[<?php echo $loop; ?>][override_title]" name="bundle_data[<?php echo $loop; ?>][override_title]" <?php echo ( 'yes' === $override_title ? 'value="1"' : '' ); ?>/>
                <div class="custom_title mt-10">
                    <?php
                    $title = isset( $item_data['title'] ) ? $item_data['title'] : '';
                    ?>
                    <textarea class="form-control" name="bundle_data[<?php echo $loop; ?>][title]" placeholder="" rows="2" cols="20"><?php echo esc_textarea( $title ); ?></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <div class="form-group-row override_description">
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="bundle_data[<?php echo $loop; ?>][override_description]">
                <?php esc_html_e( 'Override Short Description', 'woocommerce-product-bundles' ) ?>
                <span class="img_tip" data-desc="<?php esc_attr_e( 'Check this option to override the default short product description.', 'woocommerce-product-bundles' ) ?>"></span>
            </label>
            <div class="col-md-6 col-sm-9">
                <input type="checkbox" class="form-control"<?php echo ( 'yes' === $override_description ? ' checked="checked"' : '' ); ?> id="bundle_data[<?php echo $loop; ?>][override_description]" name="bundle_data[<?php echo $loop; ?>][override_description]" <?php echo ( 'yes' === $override_description ? 'value="1"' : '' ); ?>/> 
                <div class="custom_description mt-10">
                    <?php
                    $description = isset( $item_data['description'] ) ? $item_data['description'] : '';
                    ?>
                    <textarea class="form-control" name="bundle_data[<?php echo $loop; ?>][description]" placeholder="" rows="2" cols="20"><?php echo esc_textarea( $description ); ?></textarea>
                </div>
            </div>
        </div>
    </div>
    
</div>