<?php

/**
 * Auction product tab before auction data template
 *
 * Used by WCMp_AFM_Yith_Auctionpro_Integration->auction_before_auction_tab_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/yith-auction/html-product-before-auction-data.php.
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
        <label class = "control-label col-sm-3 col-md-3" for = "_yith_auction_start_price"><?php esc_html_e( sprintf( "%s (%s)", __( 'Starting Price', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) );
?></label>
        <div class="col-md-6 col-sm-9">
            <input type="number" class="form-control wc_input_price" id="_yith_auction_start_price" name="_yith_auction_start_price" value="<?php echo yit_get_prop( $auctionable_product, '_yith_auction_start_price', true ); ?>" step="any" min="0">
        </div>
    </div> 
</div>
<div class="form-group-row"> 
    <div class="form-group">
        <label class="control-label col-sm-3 col-md-3" for="_yith_auction_bid_increment"><?php esc_html_e( sprintf( "%s (%s)", __( 'Bid up', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) ); ?></label>
        <div class="col-md-6 col-sm-9">
            <input type="number" class="form-control wc_input_price" id="_yith_auction_bid_increment" name="_yith_auction_bid_increment" value="<?php echo yit_get_prop( $auctionable_product, '_yith_auction_bid_increment', true ); ?>" step="any" min="0">
        </div>
    </div> 
</div>
<div class="form-group-row"> 
    <div class="form-group">
        <label class="control-label col-sm-3 col-md-3" for="_yith_auction_minimum_increment_amount">
            <?php esc_html_e( sprintf( "%s (%s)", __( 'Minimum increment amount', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) ); ?>
            <span class="img_tip" data-desc="<?php esc_attr_e( 'Minimum amount to increase manual bids', 'woocommerce' ); ?>"></span>
        </label>
        <div class="col-md-6 col-sm-9">
            <input type="number" class="form-control wc_input_price" id="_yith_auction_minimum_increment_amount" name="_yith_auction_minimum_increment_amount" value="<?php echo yit_get_prop( $auctionable_product, '_yith_auction_minimum_increment_amount', true ); ?>" step="any" min="0"> 
        </div>
    </div> 
</div>
<div class="form-group-row"> 
    <div class="form-group">
        <label class="control-label col-sm-3 col-md-3" for="_yith_auction_reserve_price"><?php esc_html_e( sprintf( "%s (%s)", __( 'Reserve price', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) ); ?></label>
        <div class="col-md-6 col-sm-9">
            <input type="number" class="form-control wc_input_price" id="_yith_auction_reserve_price" name="_yith_auction_reserve_price" value="<?php echo yit_get_prop( $auctionable_product, '_yith_auction_reserve_price', true ); ?>" step="any" min="0">
        </div>
    </div> 
</div>
<div class="form-group-row"> 
    <div class="form-group">
        <label class="control-label col-sm-3 col-md-3" for="_yith_auction_buy_now"><?php esc_html_e( sprintf( "%s (%s)", __( 'Buy it now price', 'yith-auctions-for-woocommerce' ), get_woocommerce_currency_symbol() ) ); ?></label>
        <div class="col-md-6 col-sm-9">
            <input type="number" class="form-control wc_input_price" id="_yith_auction_buy_now" name="_yith_auction_buy_now" value="<?php echo yit_get_prop( $auctionable_product, '_yith_auction_buy_now', true ); ?>" step="any" min="0">
        </div>
    </div> 
</div>