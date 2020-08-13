<?php

/**
 * Auction status in Auction details metabox template
 *
 * Used by html-product-bid-info.php
 * Used by WCMp_AFM_Ajax->resend_winner_email()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/yith-auction/html-auction-status.php.
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

$to_auction = ( $datetime = yit_get_prop( $auctionable_product, '_yith_auction_to', true ) ) ? absint( $datetime ) : '';
$to_auction = $to_auction ? get_date_from_gmt( date( 'Y-m-d H:i:s', $to_auction ) ) : '';

$instance = YITH_Auctions()->bids;
$max_bidder = $instance->get_max_bid( $id );
if ( $max_bidder ) {
    $user = get_user_by( 'id', $max_bidder->user_id );
    $username = $user->data->user_nicename;
}
?>
<div class="auction_status_wrapper deep">
    <p><?php esc_html_e( 'Status:', 'yith-auctions-for-woocommerce' ); ?> <span><?php echo $auctionable_product->get_auction_status(); ?></span></p>
    <p><?php esc_html_e( 'End time:', 'yith-auctions-for-woocommerce' ); ?> <span><?php echo $to_auction; ?></span></p>
    <?php if ( ! $auctionable_product->is_closed() ) { ?>

        <?php if ( $max_bidder ) { ?>
            <p><?php esc_html_e( 'Max bidder:', 'yith-auctions-for-woocommerce' ); ?> <span><?php echo $username ?></span></p>
        <?php } else {
            ?>
            <p><?php esc_html_e( 'Max bidder:' ); ?> <span id=""> <?php esc_html_e( 'There is no bid for this product', 'yith-auctions-for-woocommerce' ); ?> </span></p>
            <?php
        }
        ?>
        <?php
    } else {

        $winner_email = yit_get_prop( $auctionable_product, 'yith_wcact_send_winner_email', true );
        $check_email_is_send = yit_get_prop( $auctionable_product, 'yith_wcact_winner_email_is_send', true );
        $user_email_information = yit_get_prop( $auctionable_product, 'yith_wcact_winner_email_send_custoner', true );

        if ( $winner_email ) {
            if ( apply_filters( 'yith_wcact_check_email_is_send', $check_email_is_send, $auctionable_product ) ) {
                ?>
                <p><?php esc_html_e( 'Email is send to:', 'yith-auctions-for-woocommerce' ); ?> <span><?php echo $user_email_information->user_login; ?>( <?php echo $user_email_information->data->user_email; ?> )</span></p>
                <?php
                if ( current_vendor_can( 'yith_send_winner_email' ) ) {
                    echo '<p><a href="#" id="yith-wcact-send-winner-email">' . __( 'Send Winner Email', 'yith-auctions-for-woocommerce' ) . '</a></p>';
                }
            } elseif ( yit_get_prop( $auctionable_product, 'yith_wcact_winner_email_is_not_send', true ) ) {
                ?>
                <p><?php esc_html_e( 'Email is send to:', 'yith-auctions-for-woocommerce' ); ?> <span><?php esc_html_e( 'Error send the email', 'yith-auctions-for-woocommerce' ); ?></span></p>
                <?php
                if ( current_vendor_can( 'yith_send_winner_email' ) ) {
                    echo '<p><a href="#" id="yith-wcact-send-winner-email">' . __( 'Send Winner Email', 'yith-auctions-for-woocommerce' ) . '</a></p>';
                }
            } else {
                //not needed
            }
        }
        ?>
    <?php } ?>
</div>
