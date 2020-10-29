<?php
/**
 * Booking Persons template
* This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-product-booking-persons.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      WC Marketplace
 * @package     WCMp_AFM/views/products/booking
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wcmp-metabox-wrapper booking_person wc-metabox closed" rel="<?php esc_attr_e( $person_type->get_id() ); ?>">
    <div class="wcmp-metabox-title person-title" data-toggle="collapse" data-target="#person_<?php echo esc_attr( $loop ); ?>"  aria-expanded="false" aria-controls="collapseExample">
        <div class="person-select-group">
            <span class="sortable-icon"></span>
            <strong>#<?php echo esc_html( $person_type->get_id() ); ?> &mdash; <span class="person_title"><?php echo esc_html( $person_type->get_name() ); ?></span></strong>
            <input type="hidden" name="person_id[<?php echo $loop; ?>]" value="<?php esc_attr_e( $person_type->get_id() ); ?>" />
            <input type="hidden" class="person_menu_order" name="person_menu_order[<?php echo $loop; ?>]" value="<?php echo $loop; ?>" />
        </div>
        <div class="wcmp-metabox-action person-action">
            <i class="wcmp-font ico-up-arrow-icon"></i>
            <a href="#" class="remove_row delete remove_person"><?php esc_html_e( 'Unlink', 'woocommerce-bookings' ); ?></a>
        </div>
    </div>
    <div class="wcmp-metabox-content booking_person_data collapse" id="person_<?php echo esc_attr( $loop ); ?>">
        <table cellpadding="0" cellspacing="0" class="table">
            <tbody>
                <tr>
                    <td>
                        <label><?php esc_html_e( 'Person Type Name', 'woocommerce-bookings' ); ?>:</label>
                        <input type="text" class="form-control person_name" name="person_name[<?php echo $loop; ?>]" value="<?php esc_attr_e( $person_type->get_name( 'edit' ) ); ?>" placeholder="<?php esc_attr_e( 'Name', 'woocommerce-bookings' ); ?>" />
                    </td>
                    <td>
                        <label><?php esc_html_e( 'Base Cost', 'woocommerce-bookings' ); ?>:</label>
                        <input type="number" class="form-control" name="person_cost[<?php echo $loop; ?>]" value="<?php esc_attr_e( $person_type->get_cost( 'edit' ) ); ?>" placeholder="0.00" step="0.01" />

                        <?php do_action( 'afm_bookings_after_person_cost', $person_type->get_id() ); ?>
                    </td>
                    <td>
                        <label><?php esc_html_e( 'Block Cost', 'woocommerce-bookings' ); ?>:</label>
                        <input type="number" class="form-control" name="person_block_cost[<?php echo $loop; ?>]" value="<?php esc_attr_e( $person_type->get_block_cost( 'edit' ) ); ?>" placeholder="0.00" step="0.01" />

                        <?php do_action( 'afm_bookings_after_person_block_cost', $person_type->get_id() ); ?>
                    </td>

                    <?php do_action( 'afm_bookings_after_person_block_cost_column', $person_type->get_id() ); ?>
                </tr>
                <tr>
                    <td>
                        <label><?php esc_html_e( 'Description', 'woocommerce-bookings' ); ?>:</label>
                        <input type="text" class="form-control person_description" name="person_description[<?php echo $loop; ?>]" value="<?php esc_attr_e( $person_type->get_description( 'edit' ) ); ?>" />
                    </td>
                    <td>
                        <label><?php esc_html_e( 'Min', 'woocommerce-bookings' ); ?>:</label>
                        <input type="number" class="form-control" name="person_min[<?php echo $loop; ?>]" value="<?php esc_attr_e( $person_type->get_min( 'edit' ) ); ?>" min="0" />
                    </td>
                    <td>
                        <label><?php esc_html_e( 'Max', 'woocommerce-bookings' ); ?>:</label>
                        <input type="number" class="form-control" name="person_max[<?php echo $loop; ?>]" value="<?php esc_attr_e( $person_type->get_max( 'edit' ) ); ?>" min="0" />
                    </td>

                    <?php do_action( 'afm_bookings_after_person_max_column', $person_type->get_id() ); ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>