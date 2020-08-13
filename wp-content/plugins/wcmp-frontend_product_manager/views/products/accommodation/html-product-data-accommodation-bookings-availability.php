<?php
/**
 * Availability product tab template
 *
 * Used by WCMp_AFM_Accommodation_Integration->accommodation_booking_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/accommodation/html-product-data-accommodation-bookings-availability.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/accommodation
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

$min_date = $bookable_product->get_min_date_value( 'edit' );
$min_date_unit = $bookable_product->get_min_date_unit( 'edit' );
$max_date = $bookable_product->get_max_date_value( 'edit' );
$max_date_unit = $bookable_product->get_max_date_unit( 'edit' );

$weekdays = array(
    __( 'Sunday', 'woocommerce-accommodation-bookings' ),
    __( 'Monday', 'woocommerce-accommodation-bookings' ),
    __( 'Tuesday', 'woocommerce-accommodation-bookings' ),
    __( 'Wednesday', 'woocommerce-accommodation-bookings' ),
    __( 'Thursday', 'woocommerce-accommodation-bookings' ),
    __( 'Friday', 'woocommerce-accommodation-bookings' ),
    __( 'Saturday', 'woocommerce-accommodation-bookings' ),
);
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <div class="form-group-row"> 
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_accommodation_booking_qty"><?php esc_html_e( 'Number of rooms available', 'woocommerce-accommodation-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="" step="1" id="_wc_accommodation_booking_qty" name="_wc_accommodation_booking_qty" value="<?php esc_attr_e( $bookable_product->get_qty( 'edit' ) ); ?>" class="form-control">
                    <span class="form-text"><?php esc_html_e( 'The maximum number of rooms available.', 'woocommerce-accommodation-bookings' ); ?></span>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_accommodation_booking_min_date"><?php esc_html_e( 'Bookings can be made starting', 'woocommerce-accommodation-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" min="0" step="1" id="_wc_accommodation_booking_min_date" name="_wc_accommodation_booking_min_date" value="<?php esc_attr_e( $min_date ); ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <select name="_wc_accommodation_booking_min_date_unit" id="_wc_accommodation_booking_min_date_unit" class="short form-control regular-select">
                                <option value="month" <?php selected( $min_date_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'woocommerce-accommodation-bookings' ); ?></option>
                                <option value="week" <?php selected( $min_date_unit, 'week' ); ?>><?php esc_html_e( 'Week(s)', 'woocommerce-accommodation-bookings' ); ?></option>
                                <option value="day" <?php selected( $min_date_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'woocommerce-accommodation-bookings' ); ?></option>
                                <option value="hour" <?php selected( $min_date_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'woocommerce-accommodation-bookings' ); ?></option>
                            </select>
                        </div>
                    </div>
                    <span class="form-text"><?php esc_html_e( 'into the future', 'woocommerce-accommodation-bookings' ); ?></span>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_accommodation_booking_max_date"><?php esc_html_e( 'Bookings can only be made', 'woocommerce-accommodation-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" min="1" step="1" id="_wc_accommodation_booking_max_date" name="_wc_accommodation_booking_max_date" value="<?php esc_attr_e( $max_date ); ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <select name="_wc_accommodation_booking_max_date_unit" id="_wc_accommodation_booking_max_date_unit" class="short form-control regular-select">
                                <option value="month" <?php selected( $max_date_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'woocommerce-accommodation-bookings' ); ?></option>
                                <option value="week" <?php selected( $max_date_unit, 'week' ); ?>><?php esc_html_e( 'Week(s)', 'woocommerce-accommodation-bookings' ); ?></option>
                                <option value="day" <?php selected( $max_date_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'woocommerce-accommodation-bookings' ); ?></option>
                                <option value="hour" <?php selected( $max_date_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'woocommerce-accommodation-bookings' ); ?></option>
                            </select>
                        </div>
                    </div>
                    <span class="form-text"><?php esc_html_e( 'into the future', 'woocommerce-accommodation-bookings' ); ?></span>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_accommodation_booking_has_restricted_days"><?php esc_html_e( 'Restrict start days?', 'woocommerce-accommodation-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="checkbox" class="form-control" id="_wc_accommodation_booking_has_restricted_days" name="_wc_accommodation_booking_has_restricted_days" value="yes" <?php checked( $bookable_product->has_restricted_days( 'edit' ), true ); ?>>
                    <span class="form-text"><?php esc_html_e( 'Restrict bookings so that they can only start on certain days of the week. Does not affect availability.', 'woocommerce-accommodation-bookings' ); ?></span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-9">
                    <ul class="wc_booking_restricted_days_field">
                        <?php for ( $i = 0; $i < 7; $i ++ ) : ?>
                            <li>
                                <label>
                                    <?php esc_html_e( $weekdays[$i] ); ?>
                                    <input type="checkbox" class="form-control" id="_wc_accommodation_booking_restricted_days[<?php echo $i; ?>]" name="_wc_accommodation_booking_restricted_days[<?php echo $i; ?>]" value="<?php echo $i; ?>" <?php checked( $restricted_days[$i], $i ); ?>>
                                </label>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="form-group-row"> 
            <div class="form-group">
                <div class="col-md-12">
                    <div class="booking_availability">
                        <table class="table table-outer-border">
                            <thead>
                                <tr>
                                    <th class="sort" width="1%">&nbsp;</th>
                                    <th><?php esc_html_e( 'Range type', 'woocommerce-accommodation-bookings' ); ?></th>
                                    <th><?php esc_html_e( 'From', 'woocommerce-accommodation-bookings' ); ?></th>
                                    <th><?php esc_html_e( 'To', 'woocommerce-accommodation-bookings' ); ?></th>
                                    <th><?php esc_html_e( 'Bookable', 'woocommerce-accommodation-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_attr_e( 'If not bookable, users won\'t be able to choose this room.', 'woocommerce-accommodation-bookings' ); ?>">[?]</a></th>
                                    <th><?php esc_html_e( 'Priority', 'woocommerce-accommodation-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_attr_e( 'The lower the priority number, the earlier this rule gets applied. By default, global rules take priority over product rules which take priority over resource rules. By using priority numbers you can execute rules in different orders.', 'woocommerce-accommodation-bookings' ); ?>">[?]</a></th>
                                    <th class="remove" width="1%">&nbsp;</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="9">
                                        <a href="#" class="btn btn-default insert" data-row="<?php
                                        ob_start();
                                        include( 'html-accommodation-booking-availability.php' );
                                        $html = ob_get_clean();
                                        echo esc_attr( $html );
                                        ?>"><?php esc_html_e( 'Add Range', 'woocommerce-accommodation-bookings' ); ?></a>
                                        <span class="description"><?php esc_html_e( 'Rules with lower numbers will execute first. Rules further down this table with the same priority will also execute first.', 'woocommerce-accommodation-bookings' ); ?></span>
                                    </th>
                                </tr>
                            </tfoot>
                            <tbody id="availability_rows">
                                <?php
                                $values = $bookable_product->get_availability( 'edit' );
                                if ( ! empty( $values ) && is_array( $values ) ) {
                                    foreach ( $values as $availability ) {
                                        include( 'html-accommodation-booking-availability.php' );
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>  
                </div>
            </div>
        </div>
    </div>
</div>