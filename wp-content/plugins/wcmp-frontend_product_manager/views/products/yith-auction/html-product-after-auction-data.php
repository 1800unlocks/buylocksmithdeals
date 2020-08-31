<?php

/**
 * Auction product tab after auction data template
 *
 * Used by WCMp_AFM_Yith_Auctionpro_Integration->auction_after_auction_tab_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/yith-auction/html-product-after-auction-data.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/yith-auction
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class = "form-group-row">
    <div class = "form-group">
        <label class = "control-label col-sm-3 col-md-3" for = "_yith_check_time_for_overtime_option">
            <?php esc_html_e( 'Time to add overtime', 'yith-auctions-for-woocommerce' ); ?>
            <span class="img_tip" data-desc="<?php esc_attr_e( 'Number of minutes before auction ends to check if overtime added. (Override the settings option)', 'woocommerce' ); ?>"></span>
        </label>
        <div class="col-md-6 col-sm-9">
            <input type="number" class="form-control wc_input_decimal" id="_yith_check_time_for_overtime_option" name="_yith_check_time_for_overtime_option" value="<?php echo yit_get_prop( $auctionable_product, '_yith_check_time_for_overtime_option', true ); ?>" step="any" min="0">
        </div>
    </div> 
</div>
<div class="form-group-row"> 
    <div class="form-group">
        <label class="control-label col-sm-3 col-md-3" for="_yith_overtime_option">
            <?php esc_html_e( 'Overtime', 'yith-auctions-for-woocommerce' ); ?>
            <span class="img_tip" data-desc="<?php esc_attr_e( 'Number of minutes by which the auction will be extended. (Overrride the settings option)', 'woocommerce' ); ?>"></span>
        </label>
        <div class="col-md-6 col-sm-9">
            <input type="number" class="form-control wc_input_decimal" id="_yith_overtime_option" name="_yith_overtime_option" value="<?php echo yit_get_prop( $auctionable_product, '_yith_overtime_option', true ); ?>" step="any" min="0">
        </div>
    </div> 
</div>
<div class="form-group-row"> 
    <div class="form-group">
        <label class="control-label col-sm-3 col-md-3" for="_yith_wcact_auction_automatic_reschedule">
            <?php esc_html_e( 'Time value for automatic rescheduling', 'yith-auctions-for-woocommerce' ); ?>
            <span class="img_tip" data-desc="<?php esc_attr_e( 'Number of days/hours/minutes to reschedule auction without bids automatically (Override the settings option)', 'woocommerce' ); ?>"></span>
        </label>
        <div class="col-md-6 col-sm-9">
            <input type="number" class="form-control wc_input_decimal" id="_yith_wcact_auction_automatic_reschedule" name="_yith_wcact_auction_automatic_reschedule" value="<?php echo yit_get_prop( $auctionable_product, '_yith_wcact_auction_automatic_reschedule', true ); ?>" step="any" min="0">
        </div>
    </div> 
</div>
<?php
$reschedule_auction_unit = yit_get_prop( $auctionable_product, '_yith_wcact_automatic_reschedule_auction_unit', true, 'edit' );
?>
<div class="form-group-row"> 
    <div class="form-group">
        <label class="control-label col-sm-3 col-md-3" for="_yith_wcact_automatic_reschedule_auction_unit"><?php esc_html_e( 'Select unit for automatic rescheduling', 'yith-auctions-for-woocommerce' ); ?></label>
        <div class="col-md-6 col-sm-9">
            <select class="form-control" id="_yith_wcact_automatic_reschedule_auction_unit" name="_yith_wcact_automatic_reschedule_auction_unit">
                <option value="days"<?php selected( $reschedule_auction_unit, "days" ); ?>><?php esc_html_e( 'days', 'yith-auctions-for-woocommerce' ); ?></option>
                <option value="hours"<?php selected( $reschedule_auction_unit, "hours" ); ?>><?php esc_html_e( 'hours', 'yith-auctions-for-woocommerce' ); ?></option>
                <option value="minutes"<?php selected( $reschedule_auction_unit, "minutes" ); ?>><?php esc_html_e( 'minutes', 'yith-auctions-for-woocommerce' ); ?></option>
            </select>
        </div>
    </div> 
</div>
<div class="form-group-row"> 
    <div class="form-group">
        <label class="control-label col-sm-3 col-md-3" for="_yith_wcact_show_upbid"><?php esc_html_e( 'Show bid up', 'yith-auctions-for-woocommerce' ); ?></label>
        <div class="col-md-6 col-sm-9">
            <input type="checkbox" class="form-control" id="_yith_wcact_show_upbid" name="_yith_wcact_show_upbid" value="yes" <?php checked( yit_get_prop( $auctionable_product, '_yith_wcact_upbid_checkbox', true ), 'yes' ); ?>>
            <span class="description form-text"><?php esc_html_e( 'Check this option to show Bid up on product page', 'yith-auctions-for-woocommerce' ); ?></span>
        </div>
    </div> 
</div>
<div class="form-group-row"> 
    <div class="form-group">
        <label class="control-label col-sm-3 col-md-3" for="_yith_wcact_show_overtime"><?php esc_html_e( 'Show overtime', 'yith-auctions-for-woocommerce' ); ?></label>
        <div class="col-md-6 col-sm-9">
            <input type="checkbox" class="form-control" id="_yith_wcact_show_overtime" name="_yith_wcact_show_overtime" value="yes" <?php checked( yit_get_prop( $auctionable_product, '_yith_wcact_overtime_checkbox', true ), 'yes' ); ?>>
            <span class="description form-text"><?php esc_html_e( 'Check this option to show overtime on product page', 'yith-auctions-for-woocommerce' ); ?></span>
        </div>
    </div> 
</div>
<?php
//Apply tax class
if ( afm_is_enabled_vendor_tax() ) {
    $tax_status = $auctionable_product->get_tax_status( 'edit' );
    $tax_class = $auctionable_product->get_tax_class( 'edit' );
    ?>
    <div class="form-group-row show_if_auctions"> 
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_tax_status">
                <?php esc_html_e( 'Tax status', 'yith-auctions-for-woocommerce' ); ?>
                <span class="img_tip" data-desc="<?php esc_attr_e( 'Define whether or not the entire product is taxable, or just the cost of shipping it.', 'woocommerce' ); ?>"></span>
            </label>
            <div class="col-md-6 col-sm-9">
                <select class="form-control" id="_tax_status" name="_tax_status">
                    <option value="taxable"<?php selected( $tax_status, "taxable" ); ?>><?php esc_html_e( 'Taxable', 'yith-auctions-for-woocommerce' ); ?></option>
                    <option value="shipping"<?php selected( $tax_status, "shipping" ); ?>><?php esc_html_e( 'Shipping only', 'yith-auctions-for-woocommerce' ); ?></option>
                    <option value="none"<?php selected( $tax_status, "none" ); ?>><?php esc_html_e( 'None', 'yith-auctions-for-woocommerce' ); ?></option>
                </select>
            </div>
        </div> 
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_tax_class">
                <?php esc_html_e( 'Tax class', 'yith-auctions-for-woocommerce' ); ?>
                <span class="img_tip" data-desc="<?php esc_attr_e( 'Choose a tax class for this product. Tax classes are used to apply different tax rates specific to certain types of product.', 'woocommerce' ); ?>"></span>
            </label>
            <div class="col-md-6 col-sm-9">
                <select class="form-control" id="_tax_class" name="_tax_class">
                    <?php
                    $tax_class_options = $self->get_product_tax_class_options();
                    foreach ( $tax_class_options as $key => $option ) {
                        echo '<option value = "' . $key . '"' . selected( $tax_class, $key, false ) . '>' . esc_html( $option, 'yith-auctions-for-woocommerce' ) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div> 
    </div>
    <?php
}

if ( $auctionable_product && 'auction' == $auctionable_product->get_type() && ($auctionable_product->is_closed() || yit_get_prop( $auctionable_product, 'stock_status', true ) == 'outofstock' ) ) {
    ?>
    <div class="form-group-row yith-reschedule"> 
        <div class="form-group wc_auction_reschedule">
            <div class="col-md-6 col-sm-9 col-md-offset-3">
                <input type="button" class="btn btn-default" id="reschedule_button" value="<?php esc_attr_e( 'Re-schedule', 'yith-auctions-for-woocommerce' );?>">
                <span class="description form-text" id="yith_reschedule_notice_admin"><?php esc_html_e( 'Change the dates and click on the update button to re-schedule the auction', 'wcmp-afm' ); ?></span>
            </div>
        </div> 
    </div>
    <?php
}