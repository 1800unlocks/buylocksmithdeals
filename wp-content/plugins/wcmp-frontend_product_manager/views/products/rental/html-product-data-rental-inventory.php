<?php

/**
 * Inventory product tab template for Rental products - RnB - WooCommerce Rental & Bookings System
 *
 * Used by WCMp_AFM_Rentalpro_Integration->redq_rental_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/rental/html-product-data-rental-inventory.php.
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
$inventory_child_posts = get_post_meta( $id, 'inventory_child_posts', true );
$redq_inventory_products_quique_models = get_post_meta( $id, 'redq_inventory_products_quique_models', true );

$rental_inventory = array();
$rnb_taxonomies = array( 'rnb_categories', 'pickup_location', 'dropoff_location', 'resource', 'person', 'deposite', 'attributes', 'features' );

if ( ! empty( $inventory_child_posts ) && is_array( $inventory_child_posts ) ) {
    foreach ( $inventory_child_posts as $index => $inventory_child_id ) {
        $rental_inventory[$index]['inventory_child_id'] = $inventory_child_id;
        $rental_inventory[$index]['products_unique_name'] = isset( $redq_inventory_products_quique_models[$index] ) ? $redq_inventory_products_quique_models[$index] : "";

        foreach ( $rnb_taxonomies as $taxonomy ) {
            $rental_inventory[$index][$taxonomy] = wp_get_post_terms( $inventory_child_id, $taxonomy, array( 'fields' => 'slugs' ) );
        }
        $rental_inventory[$index]['redq_rental_availability'] = (array) get_post_meta( $inventory_child_id, 'redq_rental_availability', true );
    }
}
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <div class="row">
            <div class="col-md-12">
                <h4 class="redq-headings pull-left margin-0"><?php esc_html_e( 'Inventory management', 'redq-rental' ); ?></h4>
                <div class="toolbar pull-right">
                    <span class="expand-close">
                        <a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
                    </span>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12 rental-inventory-panel">
                <div class="rental-inventory-wrapper">
                    <?php
                    if ( ! empty( $rental_inventory ) && is_array( $rental_inventory ) ) {
                        foreach ( $rental_inventory as $i => $inventory_item ) {
                            include( 'html-product-rental-inventory.php' );
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="button-group">
            <button type="button" class="btn btn-default add_inventory_item_action button-primary"><?php esc_html_e( 'Add Inventory Items', 'redq-rental' ); ?></button>
            <div class="toolbar pull-right">
                <span class="expand-close">
                    <a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
                </span>
            </div>
        </div>
    </div>
    <?php do_action( 'wcmp_afm_after_rental_inventory_product_data' ); ?>
</div>