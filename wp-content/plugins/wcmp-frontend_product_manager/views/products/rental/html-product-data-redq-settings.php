<?php

/**
 * Settings product tab template for Rental products - RnB - WooCommerce Rental & Bookings System
 *
 * Used by WCMp_AFM_Rentalpro_Integration->redq_rental_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/rental/html-product-data-redq-settings.php.
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
 * 
 * $redq_rental_local_show_pickup_date      @type string open | closed
 * $redq_rental_local_show_pickup_time      @type array
 * $redq_rental_local_show_dropoff_date     @type array
 * 
 */
if ( empty( $redq_rental_local_show_pickup_date ) ) {
    $global_option = get_option( 'rnb_show_pickup_date' );
    if ( empty( $global_option ) || $global_option === 'yes' ) {
        $redq_rental_local_show_pickup_date = 'open';
    } else {
        $redq_rental_local_show_pickup_date = 'closed';
    }
}
if ( empty( $redq_rental_local_show_pickup_time ) ) {
    $global_option = get_option( 'rnb_show_pickup_time' );
    if ( empty( $global_option ) || $global_option === 'yes' ) {
        $redq_rental_local_show_pickup_time = 'open';
    } else {
        $redq_rental_local_show_pickup_time = 'closed';
    }
}
if ( empty( $redq_rental_local_show_dropoff_date ) ) {
    $global_option = get_option( 'rnb_show_dropoff_date' );
    if ( empty( $global_option ) || $global_option === 'yes' ) {
        $redq_rental_local_show_dropoff_date = 'open';
    } else {
        $redq_rental_local_show_dropoff_date = 'closed';
    }
}
if ( empty( $redq_rental_local_show_dropoff_time ) ) {
    $global_option = get_option( 'rnb_show_dropoff_time' );
    if ( empty( $global_option ) || $global_option === 'yes' ) {
        $redq_rental_local_show_dropoff_time = 'open';
    } else {
        $redq_rental_local_show_dropoff_time = 'closed';
    }
}
if ( empty( $redq_rental_local_show_pricing_flip_box ) ) {
    $global_option = get_option( 'rnb_enable_price_flipbox' );
    if ( empty( $global_option ) || $global_option === 'yes' ) {
        $redq_rental_local_show_pricing_flip_box = 'open';
    } else {
        $redq_rental_local_show_pricing_flip_box = 'closed';
    }
}
if ( empty( $redq_rental_local_show_price_discount_on_days ) ) {
    $global_option = get_option( 'rnb_enable_price_discount' );
    if ( empty( $global_option ) || $global_option === 'yes' ) {
        $redq_rental_local_show_price_discount_on_days = 'open';
    } else {
        $redq_rental_local_show_price_discount_on_days = 'closed';
    }
}
if ( empty( $redq_rental_local_show_price_instance_payment ) ) {
    $global_option = get_option( 'rnb_enable_instance_payment' );
    if ( empty( $global_option ) || $global_option === 'yes' ) {
        $redq_rental_local_show_price_instance_payment = 'open';
    } else {
        $redq_rental_local_show_price_instance_payment = 'closed';
    }
}
if ( empty( $redq_rental_local_show_request_quote ) ) {
    $global_option = get_option( 'rnb_enable_rft_endpoint' );
    if ( empty( $global_option ) || $global_option === 'yes' ) {
        $redq_rental_local_show_request_quote = 'open';
    } else {
        $redq_rental_local_show_request_quote = 'closed';
    }
}
if ( empty( $redq_rental_local_show_book_now ) ) {
    $global_option = get_option( 'rnb_enable_book_now_btn' );
    if ( empty( $global_option ) || $global_option === 'yes' ) {
        $redq_rental_local_show_book_now = 'closed';
    } else {
        $redq_rental_local_show_book_now = 'open';
    }
}

$setting_navs = array(
    'rental_settings_display_tab'     => __( 'Display', 'redq-rental' ),
    'rental_settings_labels_tab'      => __( 'Labels', 'redq-rental' ),
    'rental_settings_conditions_tab'  => __( 'Conditions', 'redq-rental' ),
    'rental_settings_validations_tab' => __( 'Validations', 'redq-rental' ),
);
$days = array(
    esc_html__( 'Sunday', 'redq-rental' ),
    esc_html__( 'Monday', 'redq-rental' ),
    esc_html__( 'Tuesday', 'redq-rental' ),
    esc_html__( 'Wednesday', 'redq-rental' ),
    esc_html__( 'Thursday', 'redq-rental' ),
    esc_html__( 'Friday', 'redq-rental' ),
    esc_html__( 'Saturday', 'redq-rental' ),
);
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <div class="sub-tab-wrapper"> 
            <ul class="nav rental-settings-nav-tabs sub-tab-nav" role="tablist" id="rental_settings_nav_tabs">
                <?php foreach ( $setting_navs as $tab => $label ) : ?>
                    <li role="presentation" class="rental_settings_tab">
                        <a href="#<?php esc_attr_e( $tab ); ?>" aria-controls="<?php esc_attr_e( $tab ); ?>" role="tab" data-toggle="tab" aria-expanded="false"><i class="wcmp-font ico-orders-icon"></i> <span><?php esc_html_e( $label ); ?></span></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="tab-content rental-settings-tab-content sub-tab-content">
                <div role="tabpanel" class="tab-pane fade" id="rental_settings_display_tab">
                    <div class="row-padding">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label col-md-3" for="rnb_settings_for_display">
                                        <?php esc_html_e( 'Choose Settings For Display Tab', 'redq-rental' ); ?>
                                        <span class="img_tip" data-desc="<?php _e( 'If you choose local setting then these following options will work, If you chooose Global Setting then ' . __( 'Global Settings', 'redq-rental' ) . ' Of This Plugin will work ', 'redq-rental' ); ?>"></span>
                                    </label>
                                    <div class="col-md-9">
                                        <select class="form-control regular-select redq_settings_preference" id="rnb_settings_for_display" name="rnb_settings_for_display">
                                            <option value="global" <?php selected( $rnb_settings_for_display, 'global' ); ?>><?php esc_attr_e( 'Global Settings', 'redq-rental' ); ?></option>
                                            <option value="local" <?php selected( $rnb_settings_for_display, 'local' ); ?>><?php esc_attr_e( 'Local Settings', 'redq-rental' ); ?></option>
                                        </select> 
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row show_if_local">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_show_pickup_date"><?php esc_html_e( 'Show Pickup Date', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_show_pickup_date" name="redq_rental_local_show_pickup_date" value="open"<?php checked( $redq_rental_local_show_pickup_date, "open" ); ?>/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_show_pickup_time"><?php esc_html_e( 'Show Pickup Time', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_show_pickup_time" name="redq_rental_local_show_pickup_time" value="open"<?php checked( $redq_rental_local_show_pickup_time, "open" ); ?>/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_show_dropoff_date"><?php esc_html_e( 'Show Dropoff Date', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_show_dropoff_date" name="redq_rental_local_show_dropoff_date" value="open"<?php checked( $redq_rental_local_show_dropoff_date, "open" ); ?>/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_show_dropoff_time"><?php esc_html_e( 'Show Dropoff Time', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_show_dropoff_time" name="redq_rental_local_show_dropoff_time" value="open"<?php checked( $redq_rental_local_show_dropoff_time, "open" ); ?>/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_show_pricing_flip_box"><?php esc_html_e( 'Show pricing flip box', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_show_pricing_flip_box" name="redq_rental_local_show_pricing_flip_box" value="open"<?php checked( $redq_rental_local_show_pricing_flip_box, "open" ); ?>/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_show_price_discount_on_days"><?php esc_html_e( 'Show price discount', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_show_price_discount_on_days" name="redq_rental_local_show_price_discount_on_days" value="open"<?php checked( $redq_rental_local_show_price_discount_on_days, "open" ); ?>/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_show_price_instance_payment"><?php esc_html_e( 'Show instance payment', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_show_price_instance_payment" name="redq_rental_local_show_price_instance_payment" value="open"<?php checked( $redq_rental_local_show_price_instance_payment, "open" ); ?>/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_show_request_quote"><?php esc_html_e( 'Show Quote Request', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_show_request_quote" name="redq_rental_local_show_request_quote" value="open"<?php checked( $redq_rental_local_show_request_quote, "open" ); ?>/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_show_book_now"><?php esc_html_e( 'Show Book Now', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_show_book_now" name="redq_rental_local_show_book_now" value="open"<?php checked( $redq_rental_local_show_book_now, "open" ); ?>/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="rental_settings_labels_tab">
                    <div class="row-padding">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label col-md-3" for="rnb_settings_for_labels">
                                        <?php esc_html_e( 'Choose Settings For Labels Tab', 'redq-rental' ); ?>
                                        <span class="img_tip" data-desc="<?php _e( 'If you choose local setting then these following options will work, If you chooose Global Setting then ' . __( 'Global Settings', 'redq-rental' ) . ' Of This Plugin will work ', 'redq-rental' ); ?>"></span>
                                    </label>
                                    <div class="col-md-9">
                                        <select class="form-control regular-select redq_settings_preference" id="rnb_settings_for_labels" name="rnb_settings_for_labels">
                                            <option value="global" <?php selected( $rnb_settings_for_labels, 'global' ); ?>><?php esc_attr_e( 'Global Settings', 'redq-rental' ); ?></option>
                                            <option value="local" <?php selected( $rnb_settings_for_labels, 'local' ); ?>><?php esc_attr_e( 'Local Settings', 'redq-rental' ); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row show_if_local">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_show_pricing_flipbox_text"><?php esc_html_e( 'Show Pricing Text', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_show_pricing_flipbox_text" name="redq_show_pricing_flipbox_text" value="<?php esc_attr_e( $redq_show_pricing_flipbox_text ); ?>" placeholder="<?php esc_attr_e( 'Show Pricing Text', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_flip_pricing_plan_text"><?php esc_html_e( 'Show Pricing Info Heading Text', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_flip_pricing_plan_text" name="redq_flip_pricing_plan_text" value="<?php esc_attr_e( $redq_flip_pricing_plan_text ); ?>" placeholder="<?php esc_attr_e( 'Show Pricing Info Heading Text', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_pickup_location_heading_title"><?php esc_html_e( 'Pickup Location Heading Title', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_pickup_location_heading_title" name="redq_pickup_location_heading_title" value="<?php esc_attr_e( $redq_pickup_location_heading_title ); ?>" placeholder="<?php esc_attr_e( 'Pickup Location Heading Title', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_pickup_loc_placeholder"><?php esc_html_e( 'Pickup Location Placeholder', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_pickup_loc_placeholder" name="redq_pickup_loc_placeholder" value="<?php esc_attr_e( $redq_pickup_loc_placeholder ); ?>" placeholder="<?php esc_attr_e( 'Pickup Location Placeholder', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_dropoff_location_heading_title"><?php esc_html_e( 'Dropoff Location Heading Title', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_dropoff_location_heading_title" name="redq_dropoff_location_heading_title" value="<?php esc_attr_e( $redq_dropoff_location_heading_title ); ?>" placeholder="<?php esc_attr_e( 'Dropoff Location Heading Title', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_return_loc_placeholder"><?php esc_html_e( 'Dropoff Location Placeholder', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_return_loc_placeholder" name="redq_return_loc_placeholder" value="<?php esc_attr_e( $redq_return_loc_placeholder ); ?>" placeholder="<?php esc_attr_e( 'Dropoff Location Placeholder', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_pickup_date_heading_title"><?php esc_html_e( 'Pickup Date Heading Title', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_pickup_date_heading_title" name="redq_pickup_date_heading_title" value="<?php esc_attr_e( $redq_pickup_date_heading_title ); ?>" placeholder="<?php esc_attr_e( 'Pickup Date Heading Title', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_pickup_date_placeholder"><?php esc_html_e( 'Pickup Date Placeholder', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_pickup_date_placeholder" name="redq_pickup_date_placeholder" value="<?php esc_attr_e( $redq_pickup_date_placeholder ); ?>" placeholder="<?php esc_attr_e( 'Pickup Date Placeholder', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_pickup_time_placeholder"><?php esc_html_e( 'Pickup Time Placeholder', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_pickup_time_placeholder" name="redq_pickup_time_placeholder" value="<?php esc_attr_e( $redq_pickup_time_placeholder ); ?>" placeholder="<?php esc_attr_e( 'Pickup Time Placeholder', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_dropoff_date_heading_title"><?php esc_html_e( 'Dropoff Date Heading Title', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_dropoff_date_heading_title" name="redq_dropoff_date_heading_title" value="<?php esc_attr_e( $redq_dropoff_date_heading_title ); ?>" placeholder="<?php esc_attr_e( 'Dropoff Date Heading Title', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_dropoff_date_placeholder"><?php esc_html_e( 'Drop-off Date Placeholder', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_dropoff_date_placeholder" name="redq_dropoff_date_placeholder" value="<?php esc_attr_e( $redq_dropoff_date_placeholder ); ?>" placeholder="<?php esc_attr_e( 'Drop-off Date Placeholder', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_dropoff_time_placeholder"><?php esc_html_e( 'Drop-off Time Placeholder', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_dropoff_time_placeholder" name="redq_dropoff_time_placeholder" value="<?php esc_attr_e( $redq_dropoff_time_placeholder ); ?>" placeholder="<?php esc_attr_e( 'Drop-off Time Placeholder', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rnb_cat_heading"><?php esc_html_e( 'Category Heading Title', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_rnb_cat_heading" name="redq_rnb_cat_heading" value="<?php esc_attr_e( $redq_rnb_cat_heading ); ?>" placeholder="<?php esc_attr_e( 'Category Heading Title', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_resources_heading_title"><?php esc_html_e( 'Resources Heading Title', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_resources_heading_title" name="redq_resources_heading_title" value="<?php esc_attr_e( $redq_resources_heading_title ); ?>" placeholder="<?php esc_attr_e( 'Resources Heading Title', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_adults_heading_title"><?php esc_html_e( 'Adults Heading Title', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_adults_heading_title" name="redq_adults_heading_title" value="<?php esc_attr_e( $redq_adults_heading_title ); ?>" placeholder="<?php esc_attr_e( 'Adults Heading Title', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_adults_placeholder"><?php esc_html_e( 'Adults Placeholder', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_adults_placeholder" name="redq_adults_placeholder" value="<?php esc_attr_e( $redq_adults_placeholder ); ?>" placeholder="<?php esc_attr_e( 'Adults Placeholder', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_childs_heading_title"><?php esc_html_e( 'Childs Heading Title', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_childs_heading_title" name="redq_childs_heading_title" value="<?php esc_attr_e( $redq_childs_heading_title ); ?>" placeholder="<?php esc_attr_e( 'Childs Heading Title', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_childs_placeholder"><?php esc_html_e( 'Childs Placeholder', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_childs_placeholder" name="redq_childs_placeholder" value="<?php esc_attr_e( $redq_childs_placeholder ); ?>" placeholder="<?php esc_attr_e( 'Childs Placeholder', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_security_deposite_heading_title"><?php esc_html_e( 'Security Deposite Heading Title', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_security_deposite_heading_title" name="redq_security_deposite_heading_title" value="<?php esc_attr_e( $redq_security_deposite_heading_title ); ?>" placeholder="<?php esc_attr_e( 'Security Deposite Heading Title', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_discount_text_title"><?php esc_html_e( 'Discount Text', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_discount_text_title" name="redq_discount_text_title" value="<?php esc_attr_e( $redq_discount_text_title ); ?>" placeholder="<?php esc_attr_e( 'Discount Text', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_instance_pay_text_title"><?php esc_html_e( 'Instance Payment Text', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_instance_pay_text_title" name="redq_instance_pay_text_title" value="<?php esc_attr_e( $redq_instance_pay_text_title ); ?>" placeholder="<?php esc_attr_e( 'Instance Payment Text', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_total_cost_text_title"><?php esc_html_e( 'Total Cost Text', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_total_cost_text_title" name="redq_total_cost_text_title" value="<?php esc_attr_e( $redq_total_cost_text_title ); ?>" placeholder="<?php esc_attr_e( 'Total Cost Text', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_book_now_button_text"><?php esc_html_e( 'Book Now Button Text', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_book_now_button_text" name="redq_book_now_button_text" value="<?php esc_attr_e( $redq_book_now_button_text ); ?>" placeholder="<?php esc_attr_e( 'Book Now Button Text', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rfq_button_text"><?php esc_html_e( 'Request For Quote Button Text', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="text" id="redq_rfq_button_text" name="redq_rfq_button_text" value="<?php esc_attr_e( $redq_rfq_button_text ); ?>" placeholder="<?php esc_attr_e( 'Request For Quote Button Text', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="rental_settings_conditions_tab">
                    <div class="row-padding">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label col-md-3" for="rnb_settings_for_conditions">
                                        <?php esc_html_e( 'Choose Settings For Conditions Tab', 'redq-rental' ); ?>
                                        <span class="img_tip" data-desc="<?php _e( 'If you choose local setting then these following options will work, If you chooose Global Setting then ' . __( 'Global Settings', 'redq-rental' ) . ' Of This Plugin will work ', 'redq-rental' ); ?>"></span>
                                    </label>
                                    <div class="col-md-9">
                                        <select class="form-control regular-select redq_settings_preference" id="rnb_settings_for_conditions" name="rnb_settings_for_conditions">
                                            <option value="global" <?php selected( $rnb_settings_for_conditions, 'global' ); ?>><?php esc_attr_e( 'Global Settings', 'redq-rental' ); ?></option>
                                            <option value="local" <?php selected( $rnb_settings_for_conditions, 'local' ); ?>><?php esc_attr_e( 'Local Settings', 'redq-rental' ); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row show_if_local">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_block_general_dates"><?php esc_html_e( 'Block Rental Dates', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <select class="form-control regular-select" id="redq_block_general_dates" name="redq_block_general_dates">
                                            <option value="yes" <?php selected( $redq_block_general_dates, 'yes' ); ?>><?php esc_attr_e( 'Yes', 'redq-rental' ); ?></option>
                                            <option value="no" <?php selected( $redq_block_general_dates, 'no' ); ?>><?php esc_attr_e( 'No', 'redq-rental' ); ?></option>
                                        </select>
                                        <label><?php esc_html_e( 'This will be applicable for calendar date blocks', 'redq-rental' ); ?></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_calendar_date_format"><?php esc_html_e( 'Date Format Settings', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <select class="form-control regular-select" id="redq_calendar_date_format" name="redq_calendar_date_format">
                                            <option value="m/d/Y" <?php selected( $redq_calendar_date_format, 'm/d/Y' ); ?>><?php esc_attr_e( 'm/d/Y', 'redq-rental' ); ?></option>
                                            <option value="d/m/Y" <?php selected( $redq_calendar_date_format, 'd/m/Y' ); ?>><?php esc_attr_e( 'd/m/Y', 'redq-rental' ); ?></option>
                                            <option value="Y/m/d" <?php selected( $redq_calendar_date_format, 'Y/m/d' ); ?>><?php esc_attr_e( 'Y/m/d', 'redq-rental' ); ?></option>
                                        </select>
                                        <label><?php esc_html_e( 'This will be applicable for all date calendar', 'redq-rental' ); ?></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_max_time_late"><?php esc_html_e( 'Maximum time late (Hours)', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="integer" step="1" min="0" id="redq_max_time_late" name="redq_max_time_late" value="<?php esc_attr_e( $redq_max_time_late ); ?>" placeholder="<?php esc_attr_e( 'time', 'redq-rental' ); ?>"/>
                                        <label><?php esc_html_e( 'Another day will be count if anyone being late during departure', 'redq-rental' ); ?></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_enable_single_day_time_based_booking"><?php esc_html_e( 'Single Day Booking', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_enable_single_day_time_based_booking" name="redq_rental_local_enable_single_day_time_based_booking" value="open"<?php checked( $redq_rental_local_enable_single_day_time_based_booking, "open" ); ?>/>
                                        <label><?php esc_html_e( 'Checked : If pickup and return date are same then it counts as 1-day. Also select this for single date. FYI : Set max time late as at least 0 for this. UnChecked : If pickup and return date are same then it counts as 0-day. Also select this for single date.', 'redq-rental' ); ?></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_max_rental_days"><?php esc_html_e( 'Maximum Booking Days', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="integer" step="1" min="0" id="redq_max_rental_days" name="redq_max_rental_days" value="<?php esc_attr_e( $redq_max_rental_days ); ?>" placeholder="<?php esc_attr_e( 'Maximum Booking Days', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_min_rental_days"><?php esc_html_e( 'Minimum Booking Days', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="integer" step="1" min="0" id="redq_min_rental_days" name="redq_min_rental_days" value="<?php esc_attr_e( $redq_min_rental_days ); ?>" placeholder="<?php esc_attr_e( 'Minimum Booking Days', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_starting_block_dates"><?php esc_html_e( 'No. of Block Days Before Booking Started', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="integer" step="1" min="0" id="redq_rental_starting_block_dates" name="redq_rental_starting_block_dates" value="<?php esc_attr_e( $redq_rental_starting_block_dates ); ?>" placeholder="<?php esc_attr_e( 'No. of Block Days Before Booking Started', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_post_booking_block_dates"><?php esc_html_e( 'No. of Block Days After a Booking', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="integer" step="1" min="0" id="redq_rental_post_booking_block_dates" name="redq_rental_post_booking_block_dates" value="<?php esc_attr_e( $redq_rental_post_booking_block_dates ); ?>" placeholder="<?php esc_attr_e( 'No. of Block Days After a Booking', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_time_interval"><?php esc_html_e( 'Time Inverval', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control regular-text" type="integer" step="1" min="0" max="60" id="redq_time_interval" name="redq_time_interval" value="<?php esc_attr_e( $redq_time_interval ); ?>" placeholder="<?php esc_attr_e( 'Time Inverval in mins E.X - 20', 'redq-rental' ); ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_allowed_times"><?php esc_html_e( 'Allowed Times', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <textarea class="form-control regular-textarea" id="redq_allowed_times" name="redq_allowed_times" placeholder="<?php esc_attr_e( 'Insert allowed time in comma seperated format like 10:00, 12:00', 'redq-rental' ); ?>"><?php esc_html_e( $redq_allowed_times ); ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_off_days"><?php esc_html_e( 'Time Inverval', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <select multiple="multiple" class="form-control regular-select multiselect" id="redq_rental_off_days" name="redq_rental_off_days">
                                            <?php foreach($days as $key => $value) : ?>
                                            <option value="<?php esc_attr_e($key); ?>" <?php selected( in_array($key, $redq_rental_off_days), true ); ?>><?php echo esc_html_e($value); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="rental_settings_validations_tab">
                    <div class="row-padding">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label col-md-3" for="rnb_settings_for_validations">
                                        <?php esc_html_e( 'Choose Settings For Validations Tab', 'redq-rental' ); ?>
                                        <span class="img_tip" data-desc="<?php esc_attr_e( 'If you choose local setting then these following options will work, If you chooose Global Setting then ' . __( 'Global Settings', 'redq-rental' ) . ' Of This Plugin will work ', 'redq-rental' ); ?>"></span>
                                    </label>
                                    <div class="col-md-9">
                                        <select class="form-control regular-select redq_settings_preference" id="rnb_settings_for_validations" name="rnb_settings_for_validations">
                                            <option value="global" <?php selected( $rnb_settings_for_validations, 'global' ); ?>><?php esc_attr_e( 'Global Settings', 'redq-rental' ); ?></option>
                                            <option value="local" <?php selected( $rnb_settings_for_validations, 'local' ); ?>><?php esc_attr_e( 'Local Settings', 'redq-rental' ); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row show_if_local">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_required_pickup_location"><?php esc_html_e( 'Required Pickup Location', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_required_pickup_location" name="redq_rental_local_required_pickup_location" value="open"<?php checked( $redq_rental_local_required_pickup_location, "open" ); ?>/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_required_return_location"><?php esc_html_e( 'Required Return Location', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_required_return_location" name="redq_rental_local_required_return_location" value="open"<?php checked( $redq_rental_local_required_return_location, "open" ); ?>/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_local_required_person"><?php esc_html_e( 'Required Person', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_local_required_person" name="redq_rental_local_required_person" value="open"<?php checked( $redq_rental_local_required_person, "open" ); ?>/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_required_local_pickup_time"><?php esc_html_e( 'Required Pickup Time', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_required_local_pickup_time" name="redq_rental_required_local_pickup_time" value="open"<?php checked( $redq_rental_required_local_pickup_time, "open" ); ?>/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_required_local_return_time"><?php esc_html_e( 'Required Return Time', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="checkbox" id="redq_rental_required_local_return_time" name="redq_rental_required_local_return_time" value="open"<?php checked( $redq_rental_required_local_return_time, "open" ); ?>/>
                                    </div>
                                </div>
                                <h4 class="redq-headings"><?php esc_html_e('Daily Basis Openning & Closing Time','redq-rental') ?></h4>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_fri_min_time"><?php esc_html_e( 'Friday', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_fri_min_time" name="redq_rental_fri_min_time" value="<?php echo esc_attr( $redq_rental_fri_min_time ); ?>" placeholder="<?php esc_attr_e('Min Time', 'redq-rental'); ?>"/>
                                        </div>
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_fri_max_time" name="redq_rental_fri_max_time" value="<?php echo esc_attr( $redq_rental_fri_max_time ); ?>" placeholder="<?php esc_attr_e('Max Time', 'redq-rental'); ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_sat_min_time"><?php esc_html_e( 'Saturday', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_sat_min_time" name="redq_rental_sat_min_time" value="<?php echo esc_attr( $redq_rental_sat_min_time ); ?>" placeholder="<?php esc_attr_e('Min Time', 'redq-rental'); ?>"/>
                                        </div>
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_sat_max_time" name="redq_rental_sat_max_time" value="<?php echo esc_attr( $redq_rental_sat_max_time ); ?>" placeholder="<?php esc_attr_e('Max Time', 'redq-rental'); ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_sun_min_time"><?php esc_html_e( 'Sunday', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_sun_min_time" name="redq_rental_sun_min_time" value="<?php echo esc_attr( $redq_rental_sun_min_time ); ?>" placeholder="<?php esc_attr_e('Min Time', 'redq-rental'); ?>"/>
                                        </div>
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_sun_max_time" name="redq_rental_sun_max_time" value="<?php echo esc_attr( $redq_rental_sun_max_time ); ?>" placeholder="<?php esc_attr_e('Max Time', 'redq-rental'); ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_mon_min_time"><?php esc_html_e( 'Monday', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_mon_min_time" name="redq_rental_mon_min_time" value="<?php echo esc_attr( $redq_rental_mon_min_time ); ?>" placeholder="<?php esc_attr_e('Min Time', 'redq-rental'); ?>"/>
                                        </div>
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_mon_max_time" name="redq_rental_mon_max_time" value="<?php echo esc_attr( $redq_rental_mon_max_time ); ?>" placeholder="<?php esc_attr_e('Max Time', 'redq-rental'); ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_thu_min_time"><?php esc_html_e( 'Tuesday', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_thu_min_time" name="redq_rental_thu_min_time" value="<?php echo esc_attr( $redq_rental_thu_min_time ); ?>" placeholder="<?php esc_attr_e('Min Time', 'redq-rental'); ?>"/>
                                        </div>
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_thu_max_time" name="redq_rental_thu_max_time" value="<?php echo esc_attr( $redq_rental_thu_max_time ); ?>" placeholder="<?php esc_attr_e('Max Time', 'redq-rental'); ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_wed_min_time"><?php esc_html_e( 'Wednesday', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_wed_min_time" name="redq_rental_wed_min_time" value="<?php echo esc_attr( $redq_rental_wed_min_time ); ?>" placeholder="<?php esc_attr_e('Min Time', 'redq-rental'); ?>"/>
                                        </div>
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_wed_max_time" name="redq_rental_wed_max_time" value="<?php echo esc_attr( $redq_rental_wed_max_time ); ?>" placeholder="<?php esc_attr_e('Max Time', 'redq-rental'); ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3" for="redq_rental_thur_min_time"><?php esc_html_e( 'Thursday', 'redq-rental' ); ?></label>
                                    <div class="col-md-9">
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_thur_min_time" name="redq_rental_thur_min_time" value="<?php echo esc_attr( $redq_rental_thur_min_time ); ?>" placeholder="<?php esc_attr_e('Min Time', 'redq-rental'); ?>"/>
                                        </div>
                                        <div class="col-md-6">
                                            <input class="form-control regular-text" type="text" id="redq_rental_thur_max_time" name="redq_rental_thur_max_time" value="<?php echo esc_attr( $redq_rental_thur_max_time ); ?>" placeholder="<?php esc_attr_e('Max Time', 'redq-rental'); ?>"/>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php do_action( 'wcmp_afm_after_rental_settings_product_data' ); ?>
</div>