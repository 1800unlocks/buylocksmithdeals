<?php

/**
 * Auction details metabox template after product excerpt
 *
 * Used by WCMp_AFM_Simple_Auction_Integration->auction_after_product_excerpt_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/simple-auction/html-product-bid-info.php.
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
<div class="panel panel-default pannel-outer-heading auction-details-wrapper show_if_auction">
    <div class="panel-heading">
        <h3 class="pull-left"><?php esc_html_e( 'Auction details', 'wc_simple_auctions' ); ?></h3>
    </div>
    <div class="panel-body panel-content-padding form-group-wrapper"> 
        <div class="auction_status_wrapper">
        <!-- templates -->
        <?php
            $auction_relisted = $product->get_auction_relisted();
            if ( ! empty( $auction_relisted ) ) {
                echo '<p>' . esc_html__( 'Auction has been relisted on:', 'wc_simple_auctions' ) . ' ' . $auction_relisted . '</p>';
            }

            if ( $product->is_closed() === TRUE && $product->is_started() === TRUE ) {
                echo '<p>' . esc_html__( 'Auction has finished', 'wc_simple_auctions' ) . '</p>';
                if ( $product->get_auction_fail_reason() == '1' ) {
                    echo "<p>" . esc_html__( 'Auction failed because there were no bids', 'wc_simple_auctions' ) . "</p>";
                } elseif ( $product->get_auction_fail_reason() == '2' ) {
                    echo "<p class='reservefail'>" .
                    esc_html__( 'Auction failed because item did not make it to reserve price', 'wc_simple_auctions' );
                    if( current_vendor_can( 'simple_auction_remove_reserve_price' ) ) {
                        echo ' <a class="removereserve" href="#" data-postid="' . $id . '">' .
                        esc_html__( 'Remove reserve price', 'wc_simple_auctions' ) .
                        '</a>';
                    }
                    echo "</p>";
                }
                if ( $product->get_auction_closed() == '3' ) {
                    echo '<p>' . esc_html__( 'Product sold for buy now price', 'wc_simple_auctions' ) . ': <span>' . wc_price( $product->get_regular_price() ) . '</span></p>';
                } elseif ( $product->get_auction_current_bider() ) {
                    echo '<p>' . esc_html__( 'Highest bidder was', 'wc_simple_auctions' ) . ': <span class="higestbider">' . get_userdata( $product->get_auction_current_bider() )->display_name . '</span></p>';
                    echo '<p>' . esc_html__( 'Highest bid was', 'wc_simple_auctions' ) . ': <span class="higestbid" >' . wc_price( $product->get_curent_bid() ) . '</span></p>';
                    $auction_order_id =  $product->get_order_id();
                    $order_url = $auction_order_id ? wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), $auction_order_id ) : '';
                    if ( $product->get_auction_payed() ) {
                        echo '<p>' . esc_html__( 'Order has been paid, order ID is', 'wc_simple_auctions' ) . ': <span><a href="' . $order_url . '">#' . $auction_order_id . '</a></span></p>';
                    } elseif ( $auction_order_id ) {
                        $order = wc_get_order( $auction_order_id );
                        if ( $order ) {
                            $order_status = $order->get_status() ? $order->get_status() : __( 'unknown', 'wc_simple_auctions' );
                            echo '<p>' . esc_html__( 'Order has been made, order status is', 'wc_simple_auctions' ) . ': <a href="' . $order_url . '">' . $order_status . '</a><span></p>';
                        }
                    }
                }

                if ( $product->get_number_of_sent_mails() ) {
                    $dates_of_sent_mail = get_post_meta( $id, '_dates_of_sent_mails', FALSE );
                    echo '<p>' . esc_html__( 'Number of sent reminder emails', 'wc_simple_auctions' ) . ': <span> ' . $product->get_number_of_sent_mails() . '</span></p>';
                    echo '<p>' . esc_html__( 'Last reminder mail was sent on', 'wc_simple_auctions' ) . ': <span> ' . date( 'Y-m-d', end( $dates_of_sent_mail ) ) . '</span></p>';
                    ?>
                    <p class="reminder-status">
                        <?php
                        echo esc_html__( 'Reminder status', 'wc_simple_auctions' ) . ": ";
                        if ( $product->get_stop_mails() ) {
                            echo '<span class="error">' . esc_html__( 'Stopped', 'wc_simple_auctions' ) . '</span>';
                        } else {
                            echo '<span class="ok">' . esc_html__( 'Running', 'wc_simple_auctions' ) . '</span>';
                        }
                        ?>
                    </p>
                    <?php
                }
            }
            if ( ($product->is_closed() === FALSE) and ( $product->is_started() === TRUE) ) {
                if ( $product->get_auction_proxy() ) {
                    echo '<p>' . esc_html__( 'This is proxy auction', 'wc_simple_auctions' ) . '</p>';
                    if ( $product->get_auction_max_bid() && $product->get_auction_max_current_bider() ) {
                        ?>
                        <p>
                            <?php esc_html_e( 'Maximum bid is', 'wc_simple_auctions' ); ?> <?php echo $product->get_auction_max_bid(); ?> <?php esc_html_e( 'by', 'wc_simple_auctions' ); ?>
                            <?php echo get_userdata( $product->get_auction_max_current_bider() )->display_name; ?>
                        </p>
                        <?php
                    }
                }
            }
        ?>
        </div>
        <?php
            $template_vars = array( 'id' => $id, 'product' => $product, );
            afm()->template->get_template( 'products/simple-auction/html-auction-bids.php', $template_vars );
        ?>
    </div>
</div>