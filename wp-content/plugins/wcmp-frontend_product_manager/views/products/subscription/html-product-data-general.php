<?php

/**
 * General product tab subscription template
 *
 * Used by WCMp_AFM_Subscription_Integration->woocommerce_subscription_general_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/subscription/html-product-data-general.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/subscription
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

if ( wcmp_is_allowed_product_type( 'subscription' ) ) :

    global $wp_locale;

    $chosen_price = get_post_meta( $id, '_subscription_price', true );
    $chosen_interval = get_post_meta( $id, '_subscription_period_interval', true );
    $chosen_sign_up_fee = get_post_meta( $id, '_subscription_sign_up_fee', true );
    $chosen_subscription_length = get_post_meta( $id, '_subscription_length', true );
    $chosen_trial_length = WC_Subscriptions_Product::get_trial_length( $id );
    $chosen_trial_period = WC_Subscriptions_Product::get_trial_period( $id );

    $price_tooltip = __( 'Choose the subscription price, billing interval and period.', 'woocommerce-subscriptions' );
    // translators: placeholder is trial period validation message if passed an invalid value (e.g. "Trial period can not exceed 4 weeks")
    $trial_tooltip = sprintf( _x( 'An optional period of time to wait before charging the first recurring payment. Any sign up fee will still be charged at the outset of the subscription. %s', 'Trial period field tooltip on Edit Product administration screen', 'woocommerce-subscriptions' ), WC_Subscriptions_Admin::get_trial_period_validation_message() );

    // Set month as the default billing period
    if ( ! $chosen_period = get_post_meta( $id, '_subscription_period', true ) ) {
        $chosen_period = 'month';
    }
    ?>
    <div class="form-group-row pricing show_if_subscription"> 
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_subscription_price"><?php printf( esc_html__( 'Subscription price (%s)', 'woocommerce-subscriptions' ), esc_html( get_woocommerce_currency_symbol() ) ); ?></label>
            <div class="col-md-6 col-sm-9">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" id="_subscription_price" name="_subscription_price" class="wc_input_price wc_input_subscription_price form-control" placeholder="<?php echo esc_attr_x( 'e.g. 5.90', 'example price', 'woocommerce-subscriptions' ); ?>" step="any" min="0" value="<?php echo esc_attr( wc_format_localized_price( $chosen_price ) ); ?>" />
                    </div>
                    <div class="col-md-4">
                        <label for="_subscription_period_interval" class="wcs_hidden_label"><?php esc_html_e( 'Subscription interval', 'woocommerce-subscriptions' ); ?></label>
                        <select id="_subscription_period_interval" name="_subscription_period_interval" class="wc_input_subscription_period_interval form-control">
                            <?php foreach ( wcs_get_subscription_period_interval_strings() as $value => $label ) { ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $chosen_interval, true ) ?>><?php echo esc_html( $label ); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="_subscription_period" class="wcs_hidden_label"><?php esc_html_e( 'Subscription period', 'woocommerce-subscriptions' ); ?></label>
                        <select id="_subscription_period" name="_subscription_period" class="wc_input_subscription_period last form-control" >
                            <?php foreach ( wcs_get_subscription_period_strings() as $value => $label ) { ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $chosen_period, true ) ?>><?php echo esc_html( $label ); ?></option>
                            <?php } ?>
                        </select> 
                    </div>
                </div>   
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_subscription_length">
                <?php _e( 'Expire after', 'woocommerce-subscriptions' ); ?>
                <span class="img_tip" data-desc="<?php esc_attr_e( 'Automatically expire the subscription after this length of time. This length is in addition to any free trial or amount of time provided before a synchronised first renewal date.', 'woocommerce-product-bundles' ) ?>"></span>
            </label>
            <div class="col-md-6 col-sm-9">
                <select class="form-control" id="_subscription_length" name="_subscription_length">
                    <?php foreach ( wcs_get_subscription_ranges( $chosen_period ) as $value => $label ) { ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $chosen_subscription_length, true ) ?>><?php echo esc_html( $label ); ?></option>
                    <?php } ?>
                </select> 
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_subscription_sign_up_fee">
                <?php printf( __( 'Sign-up fee (%s)', 'woocommerce-subscriptions' ), get_woocommerce_currency_symbol() ); ?>                
                <span class="img_tip" data-desc="<?php esc_attr_e( 'Optionally include an amount to be charged at the outset of the subscription. The sign-up fee will be charged immediately, even if the product has a free trial or the payment dates are synced.', 'woocommerce-product-bundles' ) ?>"></span>
            </label>
            <div class="col-md-6 col-sm-9">
                <input type="text" id="_subscription_sign_up_fee" name="_subscription_sign_up_fee" class="wc_input_price wc_input_subscription_sign_up_fee form-control" placeholder="<?php echo esc_attr_x( 'e.g. 5.90', 'example price', 'woocommerce-subscriptions' ); ?>" step="any" min="0" value="<?php echo esc_attr( wc_format_localized_price( $chosen_sign_up_fee ) ); ?>" /> 
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_subscription_trial_length_field">
                <?php esc_html_e( 'Free trial', 'woocommerce-subscriptions' ); ?>
                <span class="img_tip" data-desc="<?php esc_attr_e( $trial_tooltip ); ?>"></span> 
            </label>
            <div class="col-md-6 col-sm-9">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" id="_subscription_trial_length" name="_subscription_trial_length" class="wc_input_subscription_trial_length form-control" value="<?php echo esc_attr( $chosen_trial_length ); ?>" />
                    </div>
                    <div class="col-md-6">
                        <label for="_subscription_trial_period" class="wcs_hidden_label"><?php esc_html_e( 'Subscription Trial Period', 'woocommerce-subscriptions' ); ?></label>
                        <select id="_subscription_trial_period" name="_subscription_trial_period" class="wc_input_subscription_trial_period last form-control" >
                            <?php foreach ( wcs_get_available_time_periods() as $value => $label ) { ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $chosen_trial_period, true ) ?>><?php echo esc_html( $label ); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div> 
            </div>
        </div>
        <?php
        $sync_payments_enabled = get_option( "woocommerce_subscriptions_sync_payments" );
        if ( $sync_payments_enabled == 'yes' ) {

            // Set month as the default billing period
            if ( ! $subscription_period = get_post_meta( $id, '_subscription_period', true ) ) {
                $subscription_period = 'month';
            }

            // Determine whether to display the week/month sync fields or the annual sync fields
            $display_week_month_select = ( ! in_array( $subscription_period, array( 'month', 'week' ) ) ) ? 'display: none;' : '';
            $display_annual_select = ( 'year' != $subscription_period ) ? 'display: none;' : '';

            $payment_day = WCMp_AFM_Subscription_Integration::get_products_payment_day( $sync_payments_enabled, $id );

            // An annual sync date is already set in the form: array( 'day' => 'nn', 'month' => 'nn' ), create a MySQL string from those values (year and time are irrelvent as they are ignored)
            if ( is_array( $payment_day ) ) {
                $payment_month = $payment_day['month'];
                $payment_day = $payment_day['day'];
            } else {
                $payment_month = gmdate( 'm' );
            }

            echo '<div class="options_group subscription_pricing subscription_sync show_if_subscription">';
            echo '<div class="subscription_sync_week_month" style="' . esc_attr( $display_week_month_select ) . '">';
            ?>

            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_subscription_payment_sync_date_field">
                    <?php esc_html_e( 'Synchronise renewals', 'woocommerce-subscriptions' ); ?>
					<span class="img_tip" data-desc="<?php esc_attr_e( __( 'Align the payment date for all customers who purchase this subscription to a specific day of the week or month.', 'woocommerce' ) ); ?>"></span>
                </label>
                <div class="col-md-6 col-sm-9">
                    <select id="_subscription_payment_sync_date" name="_subscription_payment_sync_date" class="form-control wc_input_subscription_payment_sync_date last" >
        <?php foreach ( WCMp_AFM_Subscription_Integration::get_billing_period_ranges( $subscription_period ) as $value => $label ) { ?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $payment_day, true ) ?>><?php echo esc_html( $label ); ?></option>
                        <?php }
                        ?>
                    </select> 
                </div>
            </div>

        <?php
        echo '</div>';

        echo '<div class="form-group subscription_sync_annual" style="' . esc_attr( $display_annual_select ) . '">';
        ?>
            <label class="control-label col-sm-3 col-md-3" for="_subscription_payment_sync_date_day"><?php echo esc_html( __( 'Synchronise renewals', 'woocommerce-subscriptions' ) ); ?></label>
            <div class="col-md-6 col-sm-9">
                <input type="number" id="<?php echo esc_attr( '_subscription_payment_sync_date_day' ); ?>" name="<?php echo esc_attr( '_subscription_payment_sync_date_day' ); ?>" class="wc_input_subscription_payment_sync" value="<?php echo esc_attr( $payment_day ); ?>" placeholder="<?php echo esc_attr_x( 'Day', 'input field placeholder for day field for annual subscriptions', 'woocommerce-subscriptions' ); ?>"  />

                <label for="<?php echo esc_attr( '_subscription_payment_sync_date_month' ); ?>" class="wcs_hidden_label"><?php esc_html_e( 'Month for Synchronisation', 'woocommerce-subscriptions' ); ?></label>
                <select id="<?php echo esc_attr( '_subscription_payment_sync_date_month' ); ?>" name="<?php echo esc_attr( '_subscription_payment_sync_date_month' ); ?>" class="wc_input_subscription_payment_sync last form-control" >
        <?php foreach ( $wp_locale->month as $value => $label ) { ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $payment_month, true ) ?>><?php echo esc_html( $label ); ?></option>
                    <?php } ?>
                </select>
            </div>
            <span class="form-text"><?php sprintf( _x( 'Align the payment date for this subscription to a specific day of the year. If the date has already taken place this year, the first payment will be processed in %s. Set the day to 0 to disable payment syncing for this product.', 'used in subscription product edit screen', 'woocommerce-subscriptions' ), gmdate( 'Y', wcs_date_to_time( '+1 year' ) ) ); ?> </span>
        <?php
        echo '</div>';
        echo '</div>';
    }
    wp_nonce_field( 'wcs_subscription_meta', '_wcsnonce' );
    ?>
    </div>
    <?php endif; ?>
