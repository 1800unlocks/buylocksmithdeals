<?php

/**
 * Auction history table in Auction details metabox template
 *
 * Used by html-product-bid-info.php
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/yith-auction/html-auction-bids.php.
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

$instance = YITH_Auctions()->bids;
?>

<div class="auction_bids_wrapper deep">
    <input type="hidden" id="yith-wcact-product-id" name="yith-wcact-product" value="<?php esc_attr_e( $id ); ?>">
    <?php
    $auction_list = $instance->get_bids_auction( $id );

    if ( count( $auction_list ) == 0 ) {
        ?>

        <p id="single-product-no-bid"><?php esc_html_e( 'There is no bid for this product', 'yith-auctions-for-woocommerce' ); ?></p>

        <?php
    } else {
        ?>
        <table class="table table-outer-border">
            <thead>
                <tr>
                    <th><?php echo __( 'Username', 'yith-auctions-for-woocommerce' ); ?></th>
                    <th><?php echo __( 'Bid Amount', 'yith-auctions-for-woocommerce' ); ?></th>
                    <th><?php echo __( 'Datetime', 'yith-auctions-for-woocommerce' ); ?></th>
                    <th><?php echo __( 'Actions', 'yith-auctions-for-woocommerce' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $auction_list as $object => $auction_obj ) {
                    $user = get_user_by( 'id', $auction_obj->user_id );
                    $user = get_user_by( 'id', $auction_obj->user_id );
                    $username = ($user) ? $user->data->user_nicename : 'anonymous';
                    $bid = $auction_obj->bid;
                    ?>
                    <tr class="yith-wcact-row">
                        <td><?php echo $username ?></td>
                        <td><?php echo wc_price( $bid ) ?></td>
                        <td class="yith_auction_datetime"><?php echo $auction_obj->date ?></td>
                        <td>
                            <a href="#" class="yith-wcact-delete-bid" data-user-id="<?php echo absint( ( $auction_obj->user_id ) ) ?>" data-date-time="<?php echo $auction_obj->date ?>" data-product-id="<?php echo $id ?>"><?php esc_html_e( 'Delete bid', 'yith-auctions-for-woocommerce' ); ?></a>
                        </td>
                    </tr>
                    <?php
                }
                if ( $auctionable_product->is_start() && $auction_list ) {
                    ?>
                    <tr class="yith-wcact-row">
                        <td><?php esc_html_e( 'Start auction', 'yith-auctions-for-woocommerce' ); ?></td>
                        <td><?php echo wc_price( $auctionable_product->get_start_price() ); ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
    }
    ?>
</div>