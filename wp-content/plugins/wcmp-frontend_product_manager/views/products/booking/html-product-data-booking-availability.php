<?php
/**
 * Booking Availability product tab template
 *
 * Used by WCMp_AFM_Booking_Integration->booking_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-product-data-booking-availability.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/booking
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

$min_date = $bookable_product->get_min_date_value( 'edit' );
$min_date_unit = $bookable_product->get_min_date_unit( 'edit' );
$max_date = $bookable_product->get_max_date_value( 'edit' );
$max_date_unit = $bookable_product->get_max_date_unit( 'edit' );

$weekdays = array(
    __( 'Sunday', 'woocommerce-bookings' ),
    __( 'Monday', 'woocommerce-bookings' ),
    __( 'Tuesday', 'woocommerce-bookings' ),
    __( 'Wednesday', 'woocommerce-bookings' ),
    __( 'Thursday', 'woocommerce-bookings' ),
    __( 'Friday', 'woocommerce-bookings' ),
    __( 'Saturday', 'woocommerce-bookings' ),
);
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <div class="form-group-row"> 
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_qty">
                    <?php esc_html_e( 'Max bookings per block', 'woocommerce-bookings' ); ?>
                    <span class="img_tip" data-desc="<?php esc_attr_e( 'The maximum bookings allowed for each block. Can be overridden at resource level.', 'woocommerce' ); ?>"></span>
                </label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="" step="1" id="_wc_booking_qty" name="_wc_booking_qty" value="<?php esc_attr_e( $bookable_product->get_qty( 'edit' ) ); ?>" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_min_date"><?php esc_html_e( 'Minimum block bookable', 'woocommerce-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" min="0" step="1" id="_wc_booking_min_date" name="_wc_booking_min_date" value="<?php esc_attr_e( $min_date ); ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <select name="_wc_booking_min_date_unit" id="_wc_booking_min_date_unit" class="short form-control regular-select">
                                <option value="month" <?php selected( $min_date_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'woocommerce-bookings' ); ?></option>
                                <option value="week" <?php selected( $min_date_unit, 'week' ); ?>><?php esc_html_e( 'Week(s)', 'woocommerce-bookings' ); ?></option>
                                <option value="day" <?php selected( $min_date_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'woocommerce-bookings' ); ?></option>
                                <option value="hour" <?php selected( $min_date_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'woocommerce-bookings' ); ?></option>
                            </select>
                        </div>
                    </div>
                    <span class="form-text"><?php esc_html_e( 'into the future', 'woocommerce-bookings' ); ?></span>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_max_date"><?php esc_html_e( 'Maximum block bookable', 'woocommerce-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" min="1" step="1" id="_wc_booking_max_date" name="_wc_booking_max_date" value="<?php esc_attr_e( $max_date ); ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <select name="_wc_booking_max_date_unit" id="_wc_booking_max_date_unit" class="short form-control regular-select">
                                <option value="month" <?php selected( $max_date_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'woocommerce-bookings' ); ?></option>
                                <option value="week" <?php selected( $max_date_unit, 'week' ); ?>><?php esc_html_e( 'Week(s)', 'woocommerce-bookings' ); ?></option>
                                <option value="day" <?php selected( $max_date_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'woocommerce-bookings' ); ?></option>
                                <option value="hour" <?php selected( $max_date_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'woocommerce-bookings' ); ?></option>
                            </select>
                        </div>
                    </div>
                    <span class="form-text"><?php esc_html_e( 'into the future', 'woocommerce-bookings' ); ?></span>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_buffer_period"><?php esc_html_e( 'Require a buffer period of', 'woocommerce-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" min="0" step="1" id="_wc_booking_buffer_period" name="_wc_booking_buffer_period" value="<?php esc_attr_e( $bookable_product->get_buffer_period( 'edit' ) ); ?>" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <span class="form-text"><span class='_wc_booking_buffer_period_unit'></span><?php esc_html_e( 'between bookings', 'woocommerce-bookings' ); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_apply_adjacent_buffer">
                    <?php esc_html_e( 'Adjacent Buffering?', 'woocommerce-bookings' ); ?>
                    <span class="img_tip" data-desc="<?php esc_attr_e( 'By default buffer period applies forward into the future of a booking. Enabling this option will apply adjacently (before and after Bookings).', 'woocommerce' ); ?>"></span>
                </label>
                <div class="col-md-6 col-sm-9">
                    <input type="checkbox" class="form-control" id="_wc_booking_apply_adjacent_buffer" name="_wc_booking_apply_adjacent_buffer" value="yes" <?php checked( $bookable_product->get_apply_adjacent_buffer( 'edit' ), true ); ?>>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_default_date_availability">
                    <?php esc_html_e( 'All dates are...', 'woocommerce-bookings' ); ?>        
                    <span class="img_tip" data-desc="<?php esc_attr_e( 'This option affects how you use the rules below.', 'woocommerce' ); ?>"></span>        
                </label>
                <div class="col-md-6 col-sm-9">
                    <select name="_wc_booking_default_date_availability" id="_wc_booking_default_date_availability" class="form-control">
                        <option value="available" <?php selected( $bookable_product->get_default_date_availability( 'edit' ), 'available' ); ?>><?php esc_html_e( 'available by default', 'woocommerce-bookings' ); ?></option>
                        <option value="non-available" <?php selected( $bookable_product->get_default_date_availability( 'edit' ), 'non-available' ); ?>><?php esc_html_e( 'not-available by default', 'woocommerce-bookings' ); ?></option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_check_availability_against">
                    <?php esc_html_e( 'Check rules against...', 'woocommerce-bookings' ); ?>
                    <span class="img_tip" data-desc="<?php esc_attr_e( 'This option affects how bookings are checked for availability.', 'woocommerce' ); ?>"></span>                  
                </label>
                <div class="col-md-6 col-sm-9">
                    <select name="_wc_booking_check_availability_against" id="_wc_booking_check_availability_against" class="form-control">
                        <option value="" <?php selected( $bookable_product->get_check_start_block_only( 'edit' ), '' ); ?>><?php esc_html_e( 'All blocks being booked', 'woocommerce-bookings' ); ?></option>
                        <option value="start" <?php selected( $bookable_product->get_check_start_block_only( 'edit' ), 'start' ); ?>><?php esc_html_e( 'The starting block only', 'woocommerce-bookings' ); ?></option>
                    </select>
                </div>
            </div>
            <div class="form-group _wc_booking_first_block_time_field">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_first_block_time"><?php esc_html_e( 'First block starts at...', 'woocommerce-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="time" class="form-control" id="_wc_booking_first_block_time" name="_wc_booking_first_block_time" value="<?php esc_attr_e( $bookable_product->get_first_block_time( 'edit' ) ); ?>" placeholder="HH:MM">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_has_restricted_days">
                    <?php esc_html_e( 'Restrict start days?', 'woocommerce-bookings' ); ?>
                    <span class="img_tip" data-desc="<?php esc_attr_e( 'Restrict bookings so that they can only start on certain days of the week. Does not affect availability.', 'woocommerce' ); ?>"></span>                  
                </label>
                <div class="col-md-6 col-sm-9">
                    <input type="checkbox" class="form-control" id="_wc_booking_has_restricted_days" name="_wc_booking_has_restricted_days" value="yes" <?php checked( $bookable_product->has_restricted_days( 'edit' ), true ); ?>> 
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-9">
                    <ul class="wc_booking_restricted_days_field">
                        <?php for ( $i = 0; $i < 7; $i++ ) : ?>
                        <li>
                            <label>
                                <?php esc_html_e($weekdays[$i]);?>
                                <input type="checkbox" class="form-control" id="_wc_booking_restricted_days[<?php echo $i; ?>]" name="_wc_booking_restricted_days[<?php echo $i; ?>]" value="<?php echo $i; ?>" <?php checked( $restricted_days[ $i ], $i ); ?>>
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
                                    <th><?php esc_html_e( 'Range type', 'woocommerce-bookings' ); ?></th>
                                    <th><?php esc_html_e( 'Range', 'woocommerce-bookings' ); ?></th>
                                    <th></th>
                                    <th></th>
                                    <th><?php esc_html_e( 'Bookable', 'woocommerce-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_attr_e( 'If not bookable, users won\'t be able to choose this block for their booking.', 'woocommerce-bookings' ); ?>">[?]</a></th>
                                    <th><?php esc_html_e( 'Priority', 'woocommerce-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_attr_e( get_wc_booking_priority_explanation() ); ?>">[?]</a></th>
                                    <th class="remove" width="1%">&nbsp;</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="9">
                                        <a href="#" class="btn btn-default insert" data-row="<?php
                                        ob_start();
                                        include( 'html-booking-availability.php' );
                                        $html = ob_get_clean();
                                        echo esc_attr( $html );
                                        ?>"><?php esc_html_e( 'Add Range', 'woocommerce-bookings' ); ?></a>
                                        <span class="description"><?php esc_html_e( get_wc_booking_rules_explanation() ); ?></span>
                                    </th>
                                </tr>
                            </tfoot>
                            <tbody id="availability_rows">
                                <?php
                                $values = $bookable_product->get_availability( 'edit' );
                                if ( ! empty( $values ) && is_array( $values ) ) {
                                    foreach ( $values as $availability ) {
                                        include( 'html-booking-availability.php' );
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