<?php

/**
 * price discount range template
  *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/rental/html-product-price-discount.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      WC Marketplace
 * @package     WCMp_AFM/views/products/rental
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

$price_discount_type = array(
    ''           => __( 'Select type', 'redq-rental' ),
    'percentage' => __( 'Percentage', 'redq-rental' ),
    'fixed'      => __( 'Fixed Price', 'redq-rental' ),
);

$min_days = ! empty( $discount['min_days'] ) ? absint( $discount['min_days'] ) : '';
$max_days = ! empty( $discount['max_days'] ) ? absint( $discount['max_days'] ) : '';
$discount_type = ! empty( $discount['discount_type'] ) ? wc_clean( $discount['discount_type'] ) : '';
$discount_amount = ! empty( $discount['discount_amount'] ) ? wc_clean( $discount['discount_amount'] ) : '';

$symbol = ( $discount_type === 'percentage' ) ? '%' : get_woocommerce_currency_symbol();
?>
<div class="wcmp-metabox-wrapper redq_price_discount" rel="<?php echo esc_attr( $i ); ?>">
    <div class="wcmp-metabox-title price-discount-title" data-toggle="collapse" data-target="#price_discount_<?php echo esc_attr( $i ); ?>"  aria-expanded="false" aria-controls="collapseExample">
        <div class="price_discount_group">
            <strong class="summary"><?php _e( sprintf( __( ' Days ( <span class="min">%d</span> - <span class="max">%d</span> ) -  Discount : <span class="discount">%s</span><span class="symbol">%s</span>', 'wcmp-afm' ), $min_days, $max_days, $discount_amount, $symbol ) ); ?></strong>
        </div>
        <div class="wcmp-metabox-action price_discount_action">
            <i class="wcmp-font ico-up-arrow-icon"></i>
            <a href="#" class="remove_row delete remove-price-discount"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></a>
        </div>
    </div>
    <div class="wcmp-metabox-content redq_price_discount_data collapse" id="price_discount_<?php echo esc_attr( $i ); ?>">
        <table cellpadding="0" cellspacing="0" class="table wcmp-metabox-table">
            <tbody>
                <tr>
                    <td><label><?php esc_html_e( 'Min Days', 'redq-rental' ); ?></label></td>
                    <td><input type="number" min="0" step="1" name="redq_price_discount_cost[<?php esc_attr_e( $i ); ?>][min_days]" class="form-control" placeholder="<?php esc_attr_e( 'Days', 'redq-rental' ) ?>" value="<?php esc_attr_e( $min_days ); ?>" /></td>
                </tr>
                <tr>
                    <td><label><?php esc_html_e( 'Max Days', 'redq-rental' ); ?></label></td>
                    <td><input type="number" min="0" step="1" name="redq_price_discount_cost[<?php esc_attr_e( $i ); ?>][max_days]" class="form-control" placeholder="<?php esc_attr_e( 'Days', 'redq-rental' ) ?>" value="<?php esc_attr_e( $max_days ); ?>" /></td>
                </tr>
                <tr>
                    <td><label><?php esc_html_e( 'Discount Type', 'redq-rental' ); ?></label></td>
                    <td>
                        <select name="redq_price_discount_cost[<?php esc_attr_e( $i ); ?>][discount_type]" class="form-control regular-select">
                            <?php foreach ( $price_discount_type as $key => $option ) : ?>
                                <option value="<?php esc_attr_e( $key ); ?>" <?php selected( $discount_type, $key ); ?>><?php esc_html_e( $option ); ?></option>
                            <?php endforeach; ?>		
                        </select>
                        <label class="form-text"><?php esc_html_e( 'This will be applicable during booking cost calculation', 'redq-rental' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <td><label><?php esc_html_e( sprintf( __( 'Discount Amount ( %s )', 'redq-rental' ), get_woocommerce_currency_symbol() ) ); ?></label></td>
                    <td><input type="text" name="redq_price_discount_cost[<?php esc_attr_e( $i ); ?>][discount_amount]" class="form-control" placeholder="<?php esc_attr_e( 'Discount amount', 'redq-rental' ) ?>" value="<?php esc_attr_e( $discount_amount ); ?>" /></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>