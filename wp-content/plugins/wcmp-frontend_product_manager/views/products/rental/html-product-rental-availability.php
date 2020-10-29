<?php

/**
 * Product Date Availabilities template under Inventory tab -> Inventory items
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/rental/html-product-rental-availability.php.
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

$availability_from = isset( $availability['from'] ) ? $availability['from'] : '';
$availability_to = isset( $availability['to'] ) ? $availability['to'] : '';
?>
<tr></tr>
    <td class="range_type">
        <select name="redq_inventory[<?php esc_attr_e( $i ); ?>][redq_rental_availability][<?php esc_attr_e( $j ); ?>][type]" class="form-control inline-select">
            <option value="custom_date" selected="selected"><?php esc_html_e( 'Custom date range', 'redq-rental' ); ?></option>
        </select>
    </td>
    <td class="from">
        <span class="date-inp-wrap">
            <input type="text" datepicker class="form-control" placeholder="<?php esc_attr_e( 'From...', 'wcmp-afm' ); ?>" name="redq_inventory[<?php esc_attr_e( $i ); ?>][redq_rental_availability][<?php esc_attr_e( $j ); ?>][from]" value="<?php echo esc_attr( $availability_from ); ?>" />
        </span>
    </td>
    <td class="to">
        <span class="date-inp-wrap">
            <input type="text" datepicker class="form-control" placeholder="<?php esc_attr_e( 'To...', 'wcmp-afm' ); ?>" name="redq_inventory[<?php esc_attr_e( $i ); ?>][redq_rental_availability][<?php esc_attr_e( $j ); ?>][to]" value="<?php echo esc_attr( $availability_to ); ?>" />
        </span>
    </td>
    <td class="bookable">
        <select name="redq_inventory[<?php esc_attr_e( $i ); ?>][redq_rental_availability][<?php esc_attr_e( $j ); ?>][rentable]" class="form-control inline-select">
            <option value="no" <?php selected( isset( $availability['rentable'] ) && $availability['rentable'] === 'no', true ) ?>><?php esc_html_e( 'Not', 'redq-rental' ); ?></option>
            <!-- <option value="yes" <?php selected( isset( $availability['bookable'] ) && $availability['bookable'] === 'yes', true ) ?>><?php esc_html_e( 'Yes', 'redq-rental' ); ?></option> -->
        </select>
    </td>
    <td width="1%">
        <a href="#" class="delete" title="<?php esc_html_e( 'Delete', 'woocommerce' ); ?>"><i class="wcmp-font ico-delete-icon wcmp-3x"></i></a>
    </td>
</tr>