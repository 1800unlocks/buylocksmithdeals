<?php
/**
 * The template for displaying vendor order detail and called from vendor_order_item.php template
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/vendor-dashboard/vendor-orders/vendor-order-details.php
 *
 * @author 	WC Marketplace
 * @package 	WCMp/Templates
 * @version   2.2.0
 */
if (!defined('ABSPATH')) {
    // Exit if accessed directly    
    exit;
}
global $woocommerce, $WCMp;
$vendor = get_current_vendor();
$order = wc_get_order($order_id);
if (!$order) {
    ?>
    <div class="col-md-12">
        <div class="panel panel-default">
            <?php _e('Invalid order', 'dc-woocommerce-multi-vendor'); ?>
        </div>
    </div>
    <?php
    return;
}

// Get the payment gateway
$payment_gateway = wc_get_payment_gateway_by_order( $order );
$vendor_order = wcmp_get_order($order_id);
$vendor_shipping_method = get_wcmp_vendor_order_shipping_method($order->get_id(), $vendor->id);
$vendor_items = get_wcmp_vendor_orders(array('order_id' => $order->get_id(), 'vendor_id' => $vendor->id));
$vendor_order_amount = get_wcmp_vendor_order_amount(array('order_id' => $order->get_id(), 'vendor_id' => $vendor->id));
$subtotal = 0;
?>
<div id="wcmp-order-details" class="col-md-12">
    <div class="panel panel-default panel-pading pannel-outer-heading mt-0 order-detail-top-panel">
        <div class="panel-heading clearfix">
            <h3 class="pull-left">
                <?php 
                /* translators: 1: order type 2: order number */
                printf(
                        esc_html__( 'Order details #%1$s', 'dc-woocommerce-multi-vendor' ),
                        esc_html( $order->get_order_number() )
                ); ?>
                <input type="hidden" id="order_ID" value="<?php echo $order->get_id(); ?>" />
            </h3>
            <div class="change-status pull-left">
                <div class="order-status-text pull-left <?php echo 'wc-' . $order->get_status( 'edit' ); ?>">
                    <i class="wcmp-font ico-pendingpayment-status-icon"></i>
                    <span class="order_status_lbl"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span>
                </div>
                <?php if( $order->get_status( 'edit' ) != 'cancelled' ) : ?>
                <div class="dropdown-order-statuses dropdown pull-left clearfix">
                    <!--<span class="order-status-edit-button pull-left dropdown-toggle" data-toggle="dropdown"><u><?php _e( 'Edit', 'dc-woocommerce-multi-vendor' ); ?></u></span>-->
                    <input type="hidden" id="order_current_status" value="<?php echo 'wc-' . $order->get_status( 'edit' ); ?>" />
                    <ul id="order_status" class="dropdown-menu dropdown-menu-right" style="margin-top:9px;z-index:1;">
                            <?php
                            $statuses = apply_filters( 'wcmp_vendor_order_statuses', wc_get_order_statuses(), $order );
                            foreach ( $statuses as $status => $status_name ) {
                                    echo '<li><a href="javascript:void(0);" data-status="' . esc_attr( $status ) . '" ' . selected( $status, 'wc-' . $order->get_status( 'edit' ), false ) . '>' . esc_html( $status_name ) . '</a></li>';
                            }
                            ?>
                    </ul>   
                </div>   
                <?php endif; ?>
            </div>
            
         </div>
        
        <?php
        $WCMp->template->get_template( 'vendor-dashboard/vendor-orders/views/html-order-info.php', array( 'order' => $order, 'vendor_order' => $vendor_order, 'vendor' => $vendor ) );
        ?>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php
            $WCMp->template->get_template( 'vendor-dashboard/vendor-orders/views/html-order-items.php', array( 'order' => $order, 'vendor_order' => $vendor_order, 'vendor' => $vendor ) );
            ?>
        </div>
        
        <div class="col-md-8">
            <!-- Downloadable product permissions -->
            <?php
            $WCMp->template->get_template( 'vendor-dashboard/vendor-orders/views/html-order-downloadable-permissions.php', array( 'order' => $order, 'vendor_order' => $vendor_order, 'vendor' => $vendor ) );
            ?>    
        </div>
        
        <div class="col-md-4">
            <?php
            $WCMp->template->get_template( 'vendor-dashboard/vendor-orders/views/html-order-notes.php', array( 'order' => $order, 'vendor_order' => $vendor_order, 'vendor' => $vendor ) );
            ?>
        </div>
        
    </div>
</div>

<style>
    .right-alignnment{
        float: right;
    }
    .confirmation {
        background-color: #fff;
        border: 1px solid #d3dbe2;
        width: 33%;
        height: 150px;
        margin: 0 0 15px 0;
        padding: 10px 15px;
        float: right;
    }
    .panel.panel-default.pannel-outer-heading .panel-body {
        background-color: #fff;
        padding: 0;
        border: 1px solid #d3dbe2;
        clear: both;
    }
    #error_message{
        color:red;
    }
    .image_loader_code {
        text-align: center;
        position: absolute;
        top: 120px;
        margin: 0 auto;
        right: 221px;
        display: none; 
    }
    .image_loader_code img {
        width: 50px;
        margin: 0 auto;
        opacity: 0.7;
        z-index: 9999999;
        position: relative;
    }
</style>
<script>
jQuery(document).ready(function(){
    jQuery("#code_confirmation").click(function(){
        var current_status=jQuery('#order_current_status').val();
        if(jQuery('.confirmation').css('display') == 'none')
        {
            jQuery(".confirmation").show();
            if(current_status == 'wc-completed'){
                jQuery("#completed").show();
                jQuery("#not_completed").hide();   
            }
            else{
             jQuery("#not_completed").show();   
             jQuery("#completed").hide();
            }
            
        }
        else{
            jQuery(".confirmation").hide();
        }
         
    });
    jQuery('#submit-code-confirmation').click(function(){
        var code=jQuery('#confirmation_code').val();
        var order_id=jQuery('#code_order_id').val();
        if(code != ''){
            jQuery('.image_loader_code').show();
            jQuery.ajax({
                 url: '<?php echo add_query_arg( 'action', 'blsd_update_vendor_order_status', $WCMp->ajax_url() ); ?>',
                 type: "post",
                 data: {code:code,order_id:order_id},
                 success: function(resultData) {
                     if(resultData == 'success'){
                        window.location.href=window.location.href;
                     }
                     else{
                         jQuery('.image_loader_code').hide();
                         jQuery('#error_message').show();
                          jQuery('#error_message').text('Please enter valid code');
                         setTimeout(function() {
                           jQuery('#error_message').fadeOut('fast');
                        }, 3000);
                    }
                 }
            });
        }
    });
});    
</script>

