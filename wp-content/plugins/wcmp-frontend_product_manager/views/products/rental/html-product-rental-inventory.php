<?php

/**
 * Inventory items template under Inventory tab
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/rental/html-product-rental-inventory.php.
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

$inventory_child_id = isset( $inventory_item['inventory_child_id'] ) ? $inventory_item['inventory_child_id'] : '';
$products_unique_name = isset( $inventory_item['products_unique_name'] ) ? $inventory_item['products_unique_name'] : '';
$rnb_categories = isset( $inventory_item['rnb_categories'] ) ? $inventory_item['rnb_categories'] : array();
$pickup_location = isset( $inventory_item['pickup_location'] ) ? $inventory_item['pickup_location'] : array();
$dropoff_location = isset( $inventory_item['dropoff_location'] ) ? $inventory_item['dropoff_location'] : array();
$resource = isset( $inventory_item['resource'] ) ? $inventory_item['resource'] : array();
$person = isset( $inventory_item['person'] ) ? $inventory_item['person'] : array();
$deposite = isset( $inventory_item['deposite'] ) ? $inventory_item['deposite'] : array();
$attributes = isset( $inventory_item['attributes'] ) ? $inventory_item['attributes'] : array();
$features = isset( $inventory_item['features'] ) ? $inventory_item['features'] : array();
$redq_rental_availability = isset( $inventory_item['redq_rental_availability'] ) ? $inventory_item['redq_rental_availability'] : array();
/*
 * Available variables from array extraction 
 * 
 * $inventory_child_id              @type int
 * $products_unique_name            @type string
 * $rnb_categories                  @type array
 * $pickup_location                 @type array
 * $dropoff_location                @type array
 * $resource                        @type array
 * $person                          @type array
 * $deposite                        @type array
 * $attributes                      @type array
 * $features                        @type array
 * $redq_rental_availability        @type array
 * 
 */
$term_list_options = array();
$inventory_field_options = array();//array( 'inventory_child_id' => array( 'type' => 'hidden' ) );
foreach ( $rnb_taxonomies as $taxonomy ) {
    $text = str_replace( '_', ' ', str_replace( 'rnb_', '', $taxonomy ) );
    $label = "Select " . ucfirst( $text );
    $placeholder = "Set " . $text;
    //Retrieve the terms in the given taxonomy
    $taxonomy_terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
    $term_list_options[$taxonomy] = array();
    if ( ! empty( $taxonomy_terms ) ) {
        foreach ( $taxonomy_terms as $taxonomy_term ) {
            $term_list_options[$taxonomy][$taxonomy_term->slug] = $taxonomy_term->name;
        }
    }
    $inventory_field_options[$taxonomy] = array(
        'label'             => __( $label, 'wcmp-afm' ),
        'type'              => 'select',
        'options'           => $term_list_options[$taxonomy],
        'custom_attributes' => array( 'placeholder' => __( $placeholder, 'wcmp-afm' ) ),
        'attributes'        => array( 'multiple' => 'multiple' ),
        'class'             => 'regular-select multiselect inventory-taxonomy form-control',
    );
}
//print_r( $inventory_field_options );
?>
<div class="wcmp-metabox-wrapper redq_inventory" rel="<?php echo esc_attr( $i ); ?>">
    <div class="wcmp-metabox-title inventory-title" data-toggle="collapse" data-target="#redq_inventory_<?php echo esc_attr( $i ); ?>"  aria-expanded="false" aria-controls="collapseExample">
        <div class="inventory_group">
            <strong class="summary"><?php esc_html_e( $products_unique_name ); ?></strong>
        </div>
        <div class="wcmp-metabox-action inventory_action">
            <i class="wcmp-font ico-up-arrow-icon"></i>
            <a href="#" class="remove_row delete remove-inventory-item"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></a>
        </div>
    </div>
    <div class="wcmp-metabox-content redq_inventory_data collapse" id="redq_inventory_<?php echo esc_attr( $i ); ?>">
        <table cellpadding="0" cellspacing="0" class="table wcmp-metabox-table">
            <tbody>
                <tr>
                    <td>
                        <label>
                            <?php esc_html_e( 'Unique product model', 'redq-rental' ); ?>
                            <span class="img_tip" data-desc="<?php esc_html_e( 'Hourly price will be applicabe if booking or rental days min 1day', 'woocommerce' ); ?>"></span>
                        </label>
                    </td>
                    <td>
                        <input type="text" name="redq_inventory[<?php esc_attr_e( $i ); ?>][products_unique_name]" class="form-control regular-text products_unique_name" placeholder="<?php esc_attr_e( 'Unique product model', 'redq-rental' ) ?>" value="<?php esc_attr_e( $products_unique_name ); ?>" />
                    </td>
                </tr>
                <?php foreach( $inventory_field_options as $name => $field ) : ?>
                <tr>
                    <td><label><?php esc_html_e( $field['label'] ); ?></label></td>
                    <td>
                        <select multiple="multiple" name="redq_inventory[<?php esc_attr_e( $i ); ?>][<?php esc_attr_e( $name ); ?>][]" class="<?php esc_attr_e( $field['class'] ); ?>">
                        <?php foreach( $field['options'] as $key => $label ) : ?>
                            <option value="<?php esc_attr_e( $key ); ?>"<?php selected( in_array( $key, $$name ), true ); ?>><?php esc_html_e( $label ); ?></option>
                        <?php endforeach; ?>
                        </select>
                    </td>
                </tr>    
                <?php endforeach; ?>
            </tbody>
        </table>
        <input type="hidden" name="redq_inventory[<?php esc_attr_e( $i ); ?>][inventory_child_id]" class="form-control" value="<?php esc_attr_e( $inventory_child_id ); ?>" />
        <div class="col-md-12">
            <label><?php esc_html_e( 'Product Date Availabilities','redq-rental' ); ?></label>
            <div class="form-group resource_availabilities border-wrapper">
                <table class="table margin-0">
                    <thead>
                        <tr>
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
                            foreach ( $redq_rental_availability as $j => $availability ) {
                                include( 'html-product-rental-availability.php' );
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6">
                                <a href="#" class="btn btn-default insert"><?php esc_html_e( 'Add Dates', 'redq-rental' ); ?></a>
                                <span class="description"><?php esc_html_e( 'Please select the date range to be disabled for the product.', 'redq-rental' ); ?></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>