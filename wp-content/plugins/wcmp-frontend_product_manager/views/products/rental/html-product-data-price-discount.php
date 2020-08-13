<?php

/**
 * Price Discount product tab template for Rental products - RnB - WooCommerce Rental & Bookings System
 *
 * Used by WCMp_AFM_Rentalpro_Integration->redq_rental_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/rental/html-product-data-price-discount.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/rental
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

//extracts array key value pairs to variables
extract( $fields );
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <div class="row">
            <div class="col-md-12">
                <h4 class="redq-headings pull-left margin-0"><?php esc_html_e( 'Set price discount depending on day length', 'redq-rental' ); ?></h4>
                <div class="toolbar pull-right">
                    <span class="expand-close">
                        <a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
                    </span>
                </div>
            </div>
        </div>
                <hr>
        <div class="row">
            <div class="col-md-12 price-discount-panel">
                <div class="price-discount-wrapper sortable">
                    <?php
                    if ( ! empty( $redq_price_discount_cost ) && is_array( $redq_price_discount_cost ) ) {
                        foreach ( $redq_price_discount_cost as $i => $discount ) {
                            include( 'html-product-price-discount.php' );
                        }
                    }
                    ?>
                </div>
            </div>
        </div> 
        <div class="button-group">
            <button type="button" class="btn btn-default add_price_discount_action"><?php esc_html_e( 'Add Price Discount', 'redq-rental' ); ?></button>
            <div class="toolbar pull-right">
                <span class="expand-close">
                    <a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
                </span>
            </div>
        </div> 
    </div>
    <?php do_action( 'wcmp_afm_after_price_discount_product_data' ); ?>
</div>