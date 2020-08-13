<?php

/**
 * Automatic Relist product tab template
 *
 * Used by WCMp_AFM_Simple_Auction_Integration->auction_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/simple-auction/html-product-data-automatic-relist.php.
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
        <?php do_action( 'wcmp_afm_before_automatic_relist_data' ); ?>
        <div class="form-group-row">
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_auction_automatic_relist">
                    <?php esc_html_e( 'Automatic relist auction', 'wc_simple_auctions' ); ?>
                    <span class="img_tip" data-desc="<?php esc_attr_e( sprintf( _x( 'Enable automatic relisting', 'woocommerce-subscriptions' ) ) ); ?>"></span>
                </label>
                <div class="col-md-6 col-sm-9">
                    <input type="checkbox" class="form-control" id="_auction_automatic_relist" name="_auction_automatic_relist" value="yes" <?php checked( $auction_automatic_relist, 'yes' ); ?>>
                </div>
            </div> 
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_auction_relist_fail_time"><?php esc_html_e( 'Relist if fail after n hours', 'wc_simple_auctions' ) ; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" class="form-control" id="_auction_relist_fail_time" name="_auction_relist_fail_time" value="<?php esc_attr_e( $auction_relist_fail_time ); ?>" step="any" min="0">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_auction_relist_not_paid_time"><?php esc_html_e( 'Relist if not paid after n hours', 'wc_simple_auctions' ) ; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" class="form-control" id="_auction_relist_not_paid_time" name="_auction_relist_not_paid_time" value="<?php esc_attr_e( $auction_relist_not_paid_time ); ?>" step="any" min="0">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_auction_relist_duration"><?php esc_html_e( 'Relist auction duration in h', 'wc_simple_auctions' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" class="form-control" id="_auction_relist_duration" name="_auction_relist_duration" value="<?php esc_attr_e( $auction_relist_duration ); ?>" step="any" min="0">
                </div>
            </div>
        </div>
        <?php do_action( 'wcmp_afm_after_automatic_relist_data' ); ?>
    </div>
</div>