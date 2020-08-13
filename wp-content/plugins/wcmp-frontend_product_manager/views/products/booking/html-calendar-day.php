<?php
/**
 * Vendor dashboard Bookings->Calender menu day view template
 *
 * Used by WCMp_AFM_Booking_Calendar_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-calendar-day.php.
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
?>
<div class="col-md-12 booking-calendar-wrapper">
    <?php do_action( 'before_wcmp_afm_booking_day_calendar_form' ); ?>
    <form method="get" id="mainform" enctype="multipart/form-data" class="wc_bookings_calendar_form">
        <?php do_action( 'wcmp_afm_booking_day_calendar_form_start' ); ?>
        <div class="panel panel-default pannel-outer-heading">
            <div class="panel-body panel-content-padding form-horizontal">
                <input type="hidden" name="view" value="<?php echo esc_attr( $view ); ?>" />
                <div class="tablenav">
                    <div class="filters">
                        <select id="calendar-bookings-filter" name="filter_bookings" class="form-control inline-select wc-enhanced-select" style="width:200px">
                            <option value=""><?php _e( 'Filter Bookings', 'woocommerce-bookings' ); ?></option>
                            <?php
                            $product_filters = WCMp_AFM_Booking_Integration::product_filters();
                            if ( $product_filters ) :
                                ?>
                                <optgroup label="<?php _e( 'By bookable product', 'woocommerce-bookings' ); ?>">
                                    <?php foreach ( $product_filters as $filter_id => $filter_name ) : ?>
                                        <option value="<?php echo $filter_id; ?>" <?php selected( $product_filter, $filter_id ); ?>><?php echo $filter_name; ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                            <?php
                            $resources_filters = WCMp_AFM_Booking_Integration::resources_filters();
                            if ( $resources_filters ) :
                                ?>
                                <optgroup label="<?php _e( 'By resource', 'woocommerce-bookings' ); ?>">
                                    <?php foreach ( $resources_filters as $filter_id => $filter_name ) : ?>
                                        <option value="<?php echo $filter_id; ?>" <?php selected( $product_filter, $filter_id ); ?>><?php echo $filter_name; ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="date_selector">
                        <a class="prev" href="<?php echo esc_url( add_query_arg( 'calendar_day', date_i18n( 'Y-m-d', strtotime( '-1 day', strtotime( $day ) ) ) ) ); ?>">&larr;</a>
                        <div>
                            <span class="date-inp-wrap"><input type="text" name="calendar_day" class="calendar_day form-control" placeholder="yyyy-mm-dd" value="<?php echo esc_attr( $day ); ?>" /></span>
                        </div>
                        <a class="next" href="<?php echo esc_url( add_query_arg( 'calendar_day', date_i18n( 'Y-m-d', strtotime( '+1 day', strtotime( $day ) ) ) ) ); ?>">&rarr;</a>
                    </div>
                    <div class="views">
                        <a class="month" href="<?php echo esc_url( add_query_arg( 'view', 'month' ) ); ?>"><i class="wcmp-font ico-eye-icon"></i> <?php _e( 'Month View', 'woocommerce-bookings' ); ?></a>
                    </div>
                    <script type="text/javascript">
                        jQuery( function () {
                            jQuery( ".tablenav select, .tablenav input" ).change( function () {
                                jQuery( "#mainform" ).submit();
                            } );
                            jQuery( '.calendar_day' ).datepicker( {
                                dateFormat: 'yy-mm-dd',
                                firstDay: <?php echo get_option( 'start_of_week' ); ?>,
                                numberOfMonths: 1,
                            } );
                            // Tooltips
                            jQuery( ".bookings li" ).tipTip( {
                                'attribute': 'data-tip',
                                'fadeIn': 50,
                                'fadeOut': 50,
                                'delay': 200
                            } );
                        } );
                    </script>
                </div>

                <div class="calendar_days">
                    <ul class="hours">
                        <?php for ( $i = 0; $i < 24; $i ++ ) : ?>
                            <li><label>
                                    <?php
                                    if ( 0 != $i && 24 != $i ) {
                                        echo date_i18n( wc_time_format(), strtotime( "midnight +{$i} hour" ) );
                                    }
                                    ?>
                                </label></li>
                        <?php endfor; ?>
                    </ul>
                    <ul class="bookings">
                        <?php $self->list_bookings_for_day(); ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php do_action( 'wcmp_afm_booking_day_calendar_form_end' ); ?>
    </form>
</div>
