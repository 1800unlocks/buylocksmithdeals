<?php

/**
 * Auction product tab template
 *
 * Used by WCMp_AFM_Simple_Auction_Integration->auction_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/simple-auction/html-product-data-simple-auction.php.
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
//extracts array key value pairs to variables
extract( $fields );
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <?php do_action( 'wcmp_afm_before_simple_auction_data' ); ?>
        <div class="form-group-row">
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_auction_item_condition"><?php esc_html_e( 'Item condition', 'wc_simple_auctions' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <select class="form-control" id="_auction_item_condition" name="_auction_item_condition">
                        <option value="new"<?php selected( $auction_item_condition, "new" ); ?>><?php esc_html_e( 'New', 'wc_simple_auctions' ); ?></option>
                        <option value="used"<?php selected( $auction_item_condition, "used" ); ?>><?php esc_html_e( 'Used', 'wc_simple_auctions' ); ?></option>
                    </select>
                </div>
            </div> 
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_auction_type"><?php esc_html_e( 'Auction type', 'wc_simple_auctions' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <select class="form-control" id="_auction_type" name="_auction_type">
                        <option value="normal"<?php selected( $auction_type, "normal" ); ?>><?php esc_html_e( 'Normal', 'wc_simple_auctions' ); ?></option>
                        <option value="reverse"<?php selected( $auction_type, "reverse" ); ?>><?php esc_html_e( 'Reverse', 'wc_simple_auctions' ); ?></option>
                    </select>
                </div>
            </div> 
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_auction_proxy"><?php esc_html_e( 'Proxy bidding?', 'wc_simple_auctions' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <?php
                    $proxy = in_array( $auction_proxy, array( '0', 'yes' ) ) ? $auction_proxy : get_option( 'simple_auctions_proxy_auction_on', 'no' );
                    ?>
                    <input type="checkbox" class="form-control" id="_auction_proxy" name="_auction_proxy" value="yes" <?php checked( $proxy, 'yes' ); ?>>
                    <span class="description form-text"><?php esc_html_e( 'Enable proxy bidding', 'wc_simple_auctions' ); ?></span>
                </div>
            </div> 
            <?php if ( get_option( 'simple_auctions_sealed_on', 'no' ) == 'yes' ) : ?>
                <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_auction_sealed"><?php esc_html_e( 'Sealed Bid?', 'wc_simple_auctions' ); ?></label>
                    <div class="col-md-6 col-sm-9">
                        <input type="checkbox" class="form-control" id="_auction_sealed" name="_auction_sealed" value="yes" <?php checked( $auction_sealed, 'yes' ); ?>>
                        <span class="description form-text"><?php esc_html_e( 'In this type of auction all bidders simultaneously submit sealed bids so that no bidder knows the bid of any other participant. The highest bidder pays the price they submitted. If two bids with same value are placed for auction the one which was placed first wins the auction.', 'wc_simple_auctions' ); ?></span>
                    </div>
                </div> 
            <?php endif; ?>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_auction_start_price"><?php esc_html_e( 'Start Price', 'wc_simple_auctions' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" class="form-control wc_input_price" id="_auction_start_price" name="_auction_start_price" value="<?php esc_attr_e( $auction_start_price ); ?>" step="any" min="0">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_auction_bid_increment"><?php esc_html_e( 'Bid increment', 'wc_simple_auctions' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" class="form-control wc_input_price" id="_auction_bid_increment" name="_auction_bid_increment" value="<?php esc_attr_e( $auction_bid_increment ); ?>" step="any" min="0">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_auction_reserved_price">
                    <?php esc_html_e( 'Reserve price', 'wc_simple_auctions' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?>
                    <span class="img_tip" data-desc="<?php esc_attr_e( sprintf( _x( 'A reserve price is the lowest price at which you are willing to sell your item. If you don’t want to sell your item below a certain price, you can set a reserve price. The amount of your reserve price is not disclosed to your bidders, but they will see that your auction has a reserve price and whether or not the reserve has been met. If a bidder does not meet that price, you are not obligated to sell your item.', 'woocommerce-subscriptions' ) ) ); ?>"></span>
                </label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" class="form-control wc_input_price" id="_auction_reserved_price" name="_auction_reserved_price" value="<?php esc_attr_e( $auction_reserved_price ); ?>" step="any" min="0">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_sa_regular_price">
                    <?php esc_html_e( 'Buy it now price', 'wc_simple_auctions' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?>
                    <span class="img_tip" data-desc="<?php esc_attr_e( sprintf( _x( 'Buy it now disappears when bid exceeds the Buy now price for normal auction, or is lower than reverse auction', 'woocommerce-subscriptions' ) ) ); ?>"></span>
                </label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" class="form-control wc_input_price" id="_sa_regular_price" name="_regular_price" value="<?php esc_attr_e( $regular_price ); ?>">
                </div>
            </div>
            <div class="form-group auction_dates_fields">
                <label class="control-label col-sm-3 col-md-3" for="_auction_dates_from"><?php esc_html_e( 'Auction Dates', 'wc_simple_auctions' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <div class="row">
                        <div class="col-md-6">
                            <span class="date-inp-wrap">
                                <input type="text" class="form-control wc_auction_datepicker" id="_auction_dates_from" name="_auction_dates_from" value="<?php echo $auction_dates_from; ?>" placeholder="<?php esc_html_e( 'From&hellip; YYYY-MM-DD HH:MM', 'wc_simple_auctions' ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span class="date-inp-wrap">
                                <input type="text" class="form-control wc_auction_datepicker" id="_auction_dates_to" name="_auction_dates_to" value="<?php echo $auction_dates_to; ?>" placeholder="<?php esc_html_e( 'To&hellip; YYYY-MM-DD HH:MM', 'wc_simple_auctions' ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ( (method_exists( $product, 'get_type' ) && $product->get_type() == 'auction') && $product->get_auction_closed() && ! $product->get_auction_payed() ) : ?>
                <div class="form-group relist_dates_fields">
                    <div class="col-md-6 col-md-offset-3 col-sm-9">
                        <a class="btn btn-default relist" href="#" id="relistauction"><?php esc_html_e('Relist', 'wc_simple_auctions'); ?></a>
                    </div>
                </div>
                <div class="form-group relist_auction_dates_fields">
                    <label class="control-label col-sm-3 col-md-3" for="_relist_auction_dates_from"><?php esc_html_e( 'Relist Auction Dates', 'wc_simple_auctions' ); ?></label>
                    <div class="col-md-6 col-sm-9">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" class="form-control wc_auction_datepicker" id="_relist_auction_dates_from" name="_relist_auction_dates_from" value="<?php echo $relist_auction_dates_from; ?>" placeholder="<?php esc_html_e( 'From&hellip; YYYY-MM-DD HH:MM', 'wc_simple_auctions' ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control wc_auction_datepicker" id="_relist_auction_dates_to" name="_relist_auction_dates_to" value="<?php echo $relist_auction_dates_to; ?>" placeholder="<?php esc_html_e( 'To&hellip; YYYY-MM-DD HH:MM', 'wc_simple_auctions' ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])" />
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php do_action( 'wcmp_afm_product_options_auction' ); ?>
    </div>
</div>