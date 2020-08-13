<?php
/**
 * Vendor dashboard Bookings->Calender menu month view template
 *
 * Used by WCMp_AFM_Booking_Calendar_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-calendar-month.php.
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
    <?php do_action( 'before_wcmp_afm_booking_month_calendar_form' ); ?>
    <form method="get" id="mainform" enctype="multipart/form-data" class="wc_bookings_calendar_form">
        <?php do_action( 'wcmp_afm_booking_month_calendar_form_start' ); ?>
        <div class="panel panel-default pannel-outer-heading mt-0">
            <div class="panel-body panel-content-padding form-horizontal">
                <input type="hidden" name="calendar_month" value="<?php echo absint( $month ); ?>" />
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
                        <a class="prev" href="<?php
                        echo esc_url( add_query_arg( array(
                            'calendar_year'  => $year,
                            'calendar_month' => $month - 1,
                        ) ) );
                        ?>">&larr;</a>
                        <div>
                            <select name="calendar_month" class="form-control inline-select">
                                <?php for ( $i = 1; $i <= 12; $i ++ ) : ?>
                                    <option value="<?php echo $i; ?>" <?php selected( $month, $i ); ?>><?php echo ucfirst( date_i18n( 'M', strtotime( '2018-' . $i . '-01' ) ) ); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <select name="calendar_year" class="form-control inline-select">
                                <?php for ( $i = ( date( 'Y' ) - 1 ); $i <= ( date( 'Y' ) + 5 ); $i ++ ) : ?>
                                    <option value="<?php echo $i; ?>" <?php selected( $year, $i ); ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <a class="next" href="<?php
                        echo esc_url( add_query_arg( array(
                            'calendar_year'  => $year,
                            'calendar_month' => $month + 1,
                        ) ) );
                        ?>">&rarr;</a>
                    </div>
                    <div class="views">
                        <a class="day" href="<?php echo esc_url( add_query_arg( 'view', 'day' ) ); ?>"><i class="wcmp-font ico-eye-icon"></i><?php _e( 'Day View', 'woocommerce-bookings' ); ?></a>
                    </div>
                    <script type="text/javascript">
                        jQuery( ".tablenav select" ).change( function () {
                            jQuery( "#mainform" ).submit();
                        } );
                    </script>
                </div>

                <table class="wc_bookings_calendar widefat table table-bordered">
                    <thead>
                        <tr>
                            <?php for ( $ii = get_option( 'start_of_week', 1 ); $ii < get_option( 'start_of_week', 1 ) + 7; $ii ++ ) : ?>
                                <th><?php echo date_i18n( _x( 'l', 'date format', 'woocommerce-bookings' ), strtotime( "next sunday +{$ii} day" ) ); ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php
                            $timestamp = $start_time;
                            $index = 0;
                            while ( $timestamp <= $end_time ) :
                                ?>
                                <td width="14.285%" class="<?php
                                if ( date( 'n', $timestamp ) != absint( $month ) ) {
                                    echo 'calendar-diff-month';
                                }
                                ?>">
                                    <a href="<?php echo '?view=day&calendar_day=' . date( 'Y-m-d', $timestamp ); ?>">
                                        <?php echo date( 'd', $timestamp ); ?>
                                    </a>
                                    <div class="bookings">
                                        <ul>
                                            <?php
                                            $self->list_bookings(
                                                date( 'd', $timestamp ), date( 'm', $timestamp ), date( 'Y', $timestamp )
                                            );
                                            ?>
                                        </ul>
                                    </div>
                                </td>
    <?php
    $timestamp = strtotime( '+1 day', $timestamp );
    $index ++;

    if ( 0 === $index % 7 ) {
        echo '</tr><tr>';
    }
endwhile;
?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
<?php do_action( 'wcmp_afm_booking_month_calendar_form_end' ); ?>
    </form>
</div>
