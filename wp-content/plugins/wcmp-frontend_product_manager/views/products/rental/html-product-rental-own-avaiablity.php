<?php

/**
 * Product Availabilities template under Availability tab 
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/rental/html-product-rental-own-avaiablity.php.
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
<tr>
    <td class="sort">&nbsp;</td>
    <td class="range_type">
        <select name="redq_rental_availability[<?php esc_attr_e( $i ); ?>][type]" class="form-control inline-select">
            <option value="custom_date" selected="selected"><?php esc_html_e( 'Custom date range', 'redq-rental' ); ?></option>
        </select>
    </td>
    <td class="from"><input type="text" datepicker class="form-control" placeholder="<?php esc_attr_e( 'From...', 'wcmp-afm' ); ?>" name="redq_rental_availability[<?php esc_attr_e( $i ); ?>][from]" value="<?php echo esc_attr( $availability_from ); ?>" /></td>
    <td class="to"><input type="text" datepicker class="form-control" placeholder="<?php esc_attr_e( 'To...', 'wcmp-afm' ); ?>" name="redq_rental_availability[<?php esc_attr_e( $i ); ?>][to]" value="<?php echo esc_attr( $availability_to ); ?>" /></td>
    <td class="bookable">
        <select name="redq_rental_availability[<?php esc_attr_e( $i ); ?>][rentable]" class="form-control inline-select">
            <option value="no" <?php selected( isset( $availability['rentable'] ) && $availability['rentable'] === 'no', true ) ?>><?php esc_html_e( 'Not', 'redq-rental' ); ?></option>
            <!-- <option value="yes" <?php selected( isset( $availability['bookable'] ) && $availability['bookable'] === 'yes', true ) ?>><?php esc_html_e( 'Yes', 'redq-rental' ); ?></option> -->
        </select>
    </td>
    <td width="1%"><a href="#" class="delete"><?php esc_html_e( 'Delete', 'woocommerce' ); ?></a></td>
</tr>