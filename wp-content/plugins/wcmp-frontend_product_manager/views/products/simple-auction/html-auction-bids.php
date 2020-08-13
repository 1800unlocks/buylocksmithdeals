<?php

/**
 * Auction history table in Auction details metabox template
 *
 * Used by html-product-bid-info.php
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/simple-auction/html-auction-bids.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/simple-auction
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="auction_bids_wrapper deep">
    <?php
    $auction_history = apply_filters( 'woocommerce__auction_history_data', afm_woo()->auction_history( $id ) );
    ?>
    <table class="table table-outer-border auction-table">
        <?php if ( ! empty( $auction_history ) ) : ?>
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'wc_simple_auctions' ); ?></th>
                    <th><?php esc_html_e( 'Bid', 'wc_simple_auctions' ); ?></th>
                    <th><?php esc_html_e( 'User', 'wc_simple_auctions' ); ?></th>
                    <th><?php esc_html_e( 'Auto', 'wc_simple_auctions' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'wc_simple_auctions' ); ?></th>
                    <?php do_action( 'wcmp_afm_simple_auction_admin_history_header', $product, $auction_history ); ?>
                </tr>
            </thead>
            <?php
            foreach ( $auction_history as $history_value ) {
                if ( $history_value->date < $product->get_auction_relisted() && ! isset( $displayed_relist ) ) {
                    ?>
                    <tr>
                        <td class="date"><?php echo $product->get_auction_start_time(); ?></td>
                        <td colspan="4"  class="relist"><?php esc_html_e( 'Auction relisted', 'wc_simple_auctions' ); ?></td>
                    </tr>
                    <?php
                    $displayed_relist = true;
                }
                ?>
                <tr>
                    <td class='date'><?php echo $history_value->date; ?></td>
                    <td class='bid'><?php echo $history_value->bid; ?></td>
                    <td class='username'><?php echo get_userdata( $history_value->userid )->display_name; ?></td>
                    <?php if ( $history_value->proxy == 1 ) : ?>
                        <td class='proxy'><?php esc_html_e( 'Auto', 'wc_simple_auctions' ); ?></td>
                    <?php else : ?>
                        <td class='proxy'></td>
                    <?php endif; ?>
                    <td class='action'>
                        <?php if ( current_vendor_can( 'simple_auction_delete_bid' ) ) : ?>
                            <a href='#' data-id="<?php echo $history_value->id; ?>" data-postid="<?php echo $id; ?>"><?php esc_html_e( 'Delete', 'wc_simple_auctions' ); ?></a>
                        <?php endif; ?>
                    </td>
                    <?php
                    do_action( 'wcmp_afm_simple_auction_admin_history_row', $product, $history_value );
                    ?>
                </tr>
                <?php
            }
            ?>
        <?php endif; ?>
        <tr class="start">
            <?php if ( $product->is_started() === TRUE ) : ?>
                <td class="date"><?php echo $product->get_auction_start_time(); ?></td>
                <td colspan="4" class="started"><?php esc_html_e( 'Auction started', 'wc_simple_auctions' ); ?></td>
            <?php else : ?>
                <td  class="date"><?php echo $product->get_auction_start_time(); ?></td>
                <td colspan="4" class="starting"><?php esc_html_e( 'Auction starting', 'wc_simple_auctions' ); ?></td>
            <?php endif; ?>
        </tr>
        </tbody>
    </table>
</div>