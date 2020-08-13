<?php

/**
 * Auction product tab
 *
 * Used by WCMp_AFM_Yith_Auctionpro_Integration->auction_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/yith-auction/html-product-bid-info.php.
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
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <?php do_action( 'wcmp_afm_before_yith_auction_data' ); ?>
        <?php
        $from_auction = ( $datetime = yit_get_prop( $auctionable_product, '_yith_auction_for', true ) ) ? absint( $datetime ) : '';
        $from_auction = $from_auction ? get_date_from_gmt( date( 'Y-m-d H:i:s', $from_auction ) ) : '';
        $to_auction = ( $datetime = yit_get_prop( $auctionable_product, '_yith_auction_to', true ) ) ? absint( $datetime ) : '';
        $to_auction = $to_auction ? get_date_from_gmt( date( 'Y-m-d H:i:s', $to_auction ) ) : '';
        ?>
        <div class="form-group-row"> 
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="wc_auction_dates_from"><?php esc_html_e( 'Auction Dates', 'yith-auctions-for-woocommerce' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <div class="row">
                        <div class="col-md-6">
                            <span class="date-inp-wrap">
                                <input type="text" class="form-control wc_auction_datepicker" id="_yith_auction_for" name="_yith_auction_for" value="<?php echo $from_auction ; ?>" placeholder="<?php esc_html_e( 'From', 'yith-auctions-for-woocommerce' );?>" title="YYYY-MM-DD hh:mm:ss">
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span class="date-inp-wrap">
                                <input type="text" class="form-control wc_auction_datepicker" id="_yith_auction_to" name="_yith_auction_to" value="<?php echo $to_auction ; ?>" placeholder="<?php esc_html_e( 'To', 'yith-auctions-for-woocommerce' );?>" title="YYYY-MM-DD hh:mm:ss">
                            </span>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
        <?php do_action( 'wcmp_afm_after_yith_auction_data' ); ?>
    </div>
</div>