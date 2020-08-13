<?php

/**
 * Product Manager->Auctions endpoint content
 *
 * Used by WCMp_AFM_Yith_Auctions_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/yith-auction/html-endpoint-auctions.php.
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

global $WCMp;
do_action( 'before_wcmp_vendor_dashboard_auctions_table' );
?>
<div class="col-md-12">
    <div class="panel panel-default panel-pading mt-0">
        <?php
       // $statuses = WCMp_AFM_Yith_Auctionpro_Integration::auction_status_filter_options();
        ?>
        <table id="auctions_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?php _e( 'Name', WCMp_AFM_TEXT_DOMAIN ); ?></th>
                    <th><?php _e( 'Start Date', 'yith-auctions-for-woocommerce' ); ?></th>
                    <th><?php _e( 'End Date', 'yith-auctions-for-woocommerce' ); ?></th>
                    <th><?php _e( 'Auction status', 'yith-auctions-for-woocommerce' ); ?></th>
                    <th><?php _e( 'Max bidder', 'yith-auctions-for-woocommerce' ); ?></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<?php
do_action( 'after_wcmp_vendor_dashboard_auctions_table' );
