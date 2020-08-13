<?php

/**
 * Price Calculation product tab template for Rental products - Booking and Rental System (Woocommerce) (FREE)
 *
 * Used by WCMp_AFM_Rental_Integration->redq_rental_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/rental/html-product-data-price-calculation.php.
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

$pricing_types = apply_filters( 'rental_pricing_types', array(
    'general_pricing' => __( 'General Pricing', 'redq-rental' ),
    'daily_pricing'   => __( 'Daily Pricing', 'redq-rental' ),
    'monthly_pricing' => __( 'Monthly Pricing', 'redq-rental' ),
    'days_range'      => __( 'Days Range Pricing', 'redq-rental' ),
    ) );
$week_days = array( 'friday', 'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday' );
$months = array( 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december' );
if ( ! afm()->integrations->is_active_class( 'rentalpro' ) ) {
    $redq_daily_pricing = array();
    $redq_monthly_pricing = array();
    $redq_day_ranges_cost = array();
}
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding"> 
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="pricing_type"><?php esc_html_e( 'Set Price Type', 'redq-rental' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <select id="pricing_type" name="pricing_type" class="form-control regular-select">
                    <?php foreach ( $pricing_types as $key => $option ) : ?>
                        <option value="<?php esc_attr_e( $key ); ?>" <?php selected( $pricing_type, $key ); ?>><?php esc_html_e( $option ); ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="form-text"><?php _e( sprintf( __( 'Choose a price type - this controls the <a href="%s">schema</a>.', 'redq-rental' ), 'http://schema.org/' ) ); ?></label>
            </div>
        </div> 
        <div class="hourly-pricing-panel show_if_general_pricing">
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="hourly_price">
                    <?php esc_html_e( sprintf( __( 'Hourly Price ( %s )', 'redq-rental' ), get_woocommerce_currency_symbol() ) ); ?>
                    <span class="img_tip" data-desc="<?php esc_attr_e( 'Hourly price will be applicabe if booking or rental days min 1day', 'woocommerce' ); ?>"></span>
                </label>
                <div class="col-md-6 col-sm-9">
                    <input type="text" id="hourly_price" name="hourly_price" class="form-control" placeholder="<?php esc_attr_e( 'Enter price here', 'redq-rental' ) ?>" value="<?php esc_attr_e( $hourly_price ); ?>" />
                </div>
            </div>
        </div>
        <div class="general-pricing-panel show_if_general_pricing"> 
            <div class="form-group">
                <h3 class="redq-headings col-md-12"><?php esc_html_e( 'Set general pricing plan', 'redq-rental' ); ?></h3>
                <label class="control-label col-sm-3 col-md-3" for="general_price"><?php esc_html_e( sprintf( __( 'General Price ( %s )', 'redq-rental' ), get_woocommerce_currency_symbol() ) ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="text" id="general_price" name="general_price" class="form-control" placeholder="<?php esc_attr_e( 'Enter price here', 'redq-rental' ) ?>" value="<?php esc_attr_e( $general_price ); ?>" />
                </div>
            </div>
        </div>
        <?php if ( afm()->integrations->is_active_class( 'rentalpro' ) ) : ?>
            <div class="daily-pricing-panel show_if_daily_pricing">
                <h4 class="redq-headings"><?php esc_html_e( 'Set daily pricing plan', WCMp_AFM_TEXT_DOMAIN ); ?></h4>
                <?php foreach ( $week_days as $day ) : ?>
                    <div class="form-group">
                        <label class="control-label col-sm-3 col-md-3" for="<?php esc_attr_e( $day ); ?>_price"><?php esc_html_e( sprintf( __( '%s ( %s )', 'redq-rental' ), ucfirst( $day ), get_woocommerce_currency_symbol() ) ); ?></label>
                        <div class="col-md-6 col-sm-9">
                            <input type="text" id="<?php esc_attr_e( $day ); ?>_price" name="redq_daily_pricing[<?php esc_attr_e( $day ); ?>]" class="form-control" placeholder="<?php esc_attr_e( 'Enter price here', 'redq-rental' ) ?>" value="<?php isset( $redq_daily_pricing[$day] ) ? esc_attr_e( $redq_daily_pricing[$day] ) : ''; ?>" />
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="monthly-pricing-panel show_if_monthly_pricing">
                <h4 class="redq-headings"><?php esc_html_e( 'Set monthly pricing plan', 'redq-rental' ); ?></h4>
                <?php foreach ( $months as $month ) : ?>
                    <div class="form-group">
                        <label class="control-label col-sm-3 col-md-3" for="<?php esc_attr_e( $month ); ?>_price"><?php esc_html_e( sprintf( __( '%s ( %s )', 'redq-rental' ), ucfirst( $month ), get_woocommerce_currency_symbol() ) ); ?></label>
                        <div class="col-md-6 col-sm-9">
                            <input type="text" id="<?php esc_attr_e( $month ); ?>_price" name="redq_monthly_pricing[<?php esc_attr_e( $month ); ?>]" class="form-control" placeholder="<?php esc_attr_e( 'Enter price here', 'redq-rental' ) ?>" value="<?php isset( $redq_monthly_pricing[$month] ) ? esc_attr_e( $redq_monthly_pricing[$month] ) : ''; ?>" />
                        </div>
                    </div> 
                <?php endforeach; ?>
            </div>
            <div class="days-range-panel show_if_days_range">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="redq-headings pull-left margin-0"><?php esc_html_e( 'Set day ranges pricing plans', 'redq-rental' ); ?></h4>
                        <div class="toolbar pull-right">
                            <span class="expand-close">
                                <a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
                            </span>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="days-range-wrapper sortable" id="resource_availability_rows">
                    <?php
                    if ( ! empty( $redq_day_ranges_cost ) && is_array( $redq_day_ranges_cost ) ) {
                        foreach ( $redq_day_ranges_cost as $i => $day_range ) {
                            include( 'html-product-price-calculation.php' );
                        }
                    }
                    ?>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-default add_days_range_action button-primary"><?php esc_html_e( 'Add Days Range', 'redq-rental' ); ?></button>
                    <div class="toolbar pull-right">
                        <span class="expand-close">
                            <a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?> 
    </div>
    <?php do_action( 'wcmp_afm_after_price_calculation_product_data' ); ?>
</div>