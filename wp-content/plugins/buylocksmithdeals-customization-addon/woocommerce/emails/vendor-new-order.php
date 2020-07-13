<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/vendor-new-order.php
 *
 * @author 		WC Marketplace
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly 
global $WCMp,$wpdb;
$vendor = get_wcmp_vendor(absint($vendor_id));
do_action( 'woocommerce_email_header', $email_heading, $email );
$text_align = is_rtl() ? 'right' : 'left';
?>

<?php // printf(__('A new order was received and marked as %s from %s. Their order is as follows:', 'dc-woocommerce-multi-vendor'), $order->get_status( 'edit' ), $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?>
<p><?php printf(__('A new deal was bought by %s. The order details are below. Please review then call the customer to confirm the appointment information. Make any changes to the schedule in your dashboard - %s', 'dc-woocommerce-multi-vendor'), $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() , home_url('dashboard')); ?></p>
<?php do_action('woocommerce_email_before_order_table', $order, true, false); ?>

<table>
		
		<?php 
			$vendor_order_id = $order->get_id();
			$vendor_id = $vendor->id;
			
			$main_order_id = $wpdb->get_var("SELECT post_parent FROM wp_posts where ID=$vendor_order_id AND post_type='shop_order'");
			
			$booking_id = $wpdb->get_var("SELECT ID FROM wp_posts where post_parent=$main_order_id AND post_type='wc_booking'");
			
			if($booking_id != '' && $vendor_id !='' && $vendor_order_id != ''){
				$request = $vendor_id.'-'.$vendor_order_id.'-'.$booking_id;
				$encrypt_key = avlabs_encrypt_decrypt('encrypt',$request);
				$url = site_url().'/?verify='.$encrypt_key;
				
				$code=$vendor_id."&".$vendor_order_id;
				$key='buylocksmithdeals';
				$string=BuyLockSmithDealsCustomizationAddon::code_encrypt($code, $key);
				$markUrl = home_url().'/vendor-confirmation?code='.$string;
				
				$unique_id=get_post_meta($vendor_order_id,'unique_token',true); 
				if(empty($unique_id) && $unique_id =='' ){
					$unique_id=substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 10);
				}
				update_post_meta($vendor_order_id,'unique_token',$unique_id); 
				
				?>
				<tr>
				<td><a href="<?php echo $url; ?>" style="font-weight: normal;text-decoration: underline;border: 1px solid #77c84e;background: #77c84e;padding: 8px;border-radius: 6px;color: #fff;text-decoration: none;font-weight: 600;">Update Job Time/Date</a></td>
				</tr>
				<tr><td><p>When the job is complete, please click the button below to trigger an email to the customer asking for a review</p></td></tr>
				<tr>
				<td><a href="<?php echo $markUrl; ?>" style="font-weight: normal;text-decoration: underline;border: 1px solid #77c84e;background: #77c84e;padding: 8px;border-radius: 6px;color: #fff;text-decoration: none;font-weight: 600;">Mark Completed</a></td>
				</tr>
			<?php } ?>
			
		
</table>


<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
    <thead>
        <tr>
            <?php do_action('wcmp_before_vendor_order_table_header', $order, $vendor->term_id); ?>
            <th scope="col" style="text-align:<?php echo $text_align; ?>; border: 1px solid #eee;"><?php _e('Product', 'dc-woocommerce-multi-vendor'); ?></th>
            <th scope="col" style="text-align:<?php echo $text_align; ?>; border: 1px solid #eee;"><?php _e('Quantity', 'dc-woocommerce-multi-vendor'); ?></th>
            <th scope="col" style="text-align:<?php echo $text_align; ?>; border: 1px solid #eee;"><?php _e('Commission', 'dc-woocommerce-multi-vendor'); ?></th>
            <?php do_action('wcmp_after_vendor_order_table_header', $order, $vendor->term_id); ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $vendor->vendor_order_item_table($order, $vendor->term_id);

        ?>
    </tbody>
</table>
<?php
if (apply_filters('show_cust_order_calulations_field', true, $vendor->id)) {
    ?>
    <table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
        <?php
        $totals = $vendor->wcmp_vendor_get_order_item_totals($order, $vendor->term_id);
        if ($totals) {
            foreach ($totals as $total_key => $total) {
                ?><tr>
                    <th scope="row" colspan="2" style="text-align:left; border: 1px solid #eee;"><?php echo $total['label']; ?></th>
                    <td style="text-align:<?php echo $text_align; ?>; border: 1px solid #eee;"><?php echo $total['value']; ?></td>
                </tr><?php
            }
        }
        if ( $order->get_customer_note() ) {
            ?>
            <tr>
                <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
                <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
    }
    if (apply_filters('show_cust_address_field', true, $vendor->id) || apply_filters( 'is_vendor_can_see_customer_details', true, $vendor->id, $order ) ) {
    ?>
    <h2><?php _e('Customer Details', 'dc-woocommerce-multi-vendor'); ?></h2>
    <?php if ($order->get_billing_email()) { ?>
        <p><strong><?php _e('Customer Name:', 'dc-woocommerce-multi-vendor'); ?></strong> <?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></p>
        <p><strong><?php _e('Email:', 'dc-woocommerce-multi-vendor'); ?></strong> <?php echo $order->get_billing_email(); ?></p>
    <?php } ?>
    <?php if ($order->get_billing_phone()) { ?>
        <p><strong><?php _e('Telephone:', 'dc-woocommerce-multi-vendor'); ?></strong> <?php echo $order->get_billing_phone(); ?></p>
    <?php
        }
    }
    ?>
    <table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
	<tr>
            <?php if (apply_filters('show_cust_billing_address_field', true, $vendor->id)) { ?>
            <td style="text-align:<?php echo $text_align; ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;" valign="top" width="50%">
                <h2><?php _e( 'Billing Address', 'dc-woocommerce-multi-vendor' ); ?></h2>
                <address class="address">
                    <?php echo ( $address = $order->get_formatted_billing_address() ) ? $address : __( 'N/A', 'woocommerce' ); ?>
                </address>
            </td>
            <?php } ?>
            <?php if ( apply_filters('show_cust_shipping_address_field', true, $vendor->id) && ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && ( $shipping = $order->get_formatted_shipping_address() ) ) : ?>
                <td style="text-align:<?php echo $text_align; ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; padding:0;" valign="top" width="50%">
                        <h2><?php _e('Shipping Address', 'dc-woocommerce-multi-vendor'); ?></h2>
                        <address class="address"><?php echo $shipping; ?></address>
                </td>
            <?php endif; ?>
	</tr>
    </table>
	
<?php do_action('wcmp_email_footer'); ?>