<?php

/**
 * Auction details metabox template after product excerpt
 *
 * Used by WCMp_AFM_Yith_Auctionpro_Integration->auction_after_product_excerpt_content()
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
<div class="panel panel-default pannel-outer-heading auction-details-wrapper show_if_auction">
    <div class="panel-heading">
        <h3 class="pull-left"><?php esc_html_e( 'Auction details', 'wcmp-afm' ); ?></h3>
    </div>
    <div class="panel-body panel-content-padding form-group-wrapper"> 
        <!-- templates -->
        <?php
        $template_vars = array( 'id' => $id, 'auctionable_product' => $auctionable_product, );
        afm()->template->get_template( 'products/yith-auction/html-auction-status.php', $template_vars );
        afm()->template->get_template( 'products/yith-auction/html-auction-bids.php', $template_vars );
        ?>
    </div>
</div>