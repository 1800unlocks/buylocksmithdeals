<?php

/**
 * Availability product tab template for Rental products - Booking and Rental System (Woocommerce) (FREE)
 *
 * Used by WCMp_AFM_Rental_Integration->redq_rental_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/rental/html-product-data-availability.php.
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
/*
 * Available variables from array extraction 
 * NIL
 */
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <div class="row">
            <div class="col-md-12 rental-availability-panel">
                <h4><?php esc_html_e( 'Product Availabilities', 'redq-rental' ); ?></h4>
                <div class="rental-availability-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="sort" width="1%">&nbsp;</th>
                                <th><?php _e( 'Range type', 'redq-rental' ); ?></th>
                                <th><?php _e( 'From', 'redq-rental' ); ?></th>
                                <th><?php _e( 'To', 'redq-rental' ); ?></th>
                                <th><?php _e( 'Bookable', 'redq-rental' ); ?>&nbsp;<a class="tips" data-tip="<?php _e( 'Please select the date range for which you want the product to be disabled.', 'redq-rental' ); ?>">[?]</a></th>
                                <th class="remove" width="1%">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ( ! empty( $redq_rental_availability ) && is_array( $redq_rental_availability ) ) {
                                foreach ( $redq_rental_availability as $i => $availability ) {
                                    include( 'html-product-rental-own-avaiablity.php' );
                                }
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6">
                                    <a href="#" class="button insert"><?php esc_html_e( 'Add Dates', 'redq-rental' ); ?></a>
                                    <span class="description"><?php esc_html_e( 'Please select the date range to be disabled for the product.', 'redq-rental' ); ?></span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php do_action( 'wcmp_afm_after_rental_availability_product_data' ); ?>
</div>