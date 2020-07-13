<?php
/**
 * Email Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-addresses.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.5.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align = is_rtl() ? 'right' : 'left';
$address    = $order->get_formatted_billing_address();
$shipping   = $order->get_formatted_shipping_address();

global $wpdb;
$orderid = $order->get_id();

$booking_id = $wpdb->get_var("SELECT ID FROM wp_posts where post_parent=$orderid AND post_type='wc_booking'");

?>
 <?php /*if(!$sent_to_admin){  
      $sub_orders = get_children( array('post_parent' => $order->get_id(), 'post_type' => 'shop_order' ) );
      if(!empty($sub_orders)){
		$show_section=1;
		foreach($sub_orders as $sorder){
			$suborder_id=$sorder->ID;
			$order_completed_by_code=get_post_meta($suborder_id,'_order_completed_by_code',true);
			if($order_completed_by_code == 'yes'){
				$show_section=0;
			}
		}
		if($show_section == 1){
	?>
    <table id="completion_code" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
            <tr>
		<td style="text-align:<?php echo esc_attr( $text_align ); ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;" valign="top" width="50%">
                    <h2><?php esc_html_e( 'Job Completion Code', 'woocommerce' ); ?></h2>
                    <address class="address">
                       <?php
                        include BUYLOCKSMITH_DEALS_PLUGIN_DIR.'/assets/phpqrcode/qrlib.php';
                        foreach($sub_orders as $sorder){
							echo '<div style="border-bottom: 1px solid #e4e4e4; margin: 0 0 16px;">';
							$suborder_id=$sorder->ID;
							$unique_id=get_post_meta($suborder_id,'unique_token',true); 
							if(empty($unique_id) && $unique_id =='' ){
								$unique_id=substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 10);
							}
                            $suborder_authorid=$sorder->post_author;
                            $suborder_authorname=get_author_name( $suborder_authorid);
                            update_post_meta($suborder_id,'unique_token',$unique_id); 
                            echo 'Locksmith : '.$suborder_authorname.'<br/>' ; 
                            echo 'Completion Code : '.$unique_id.'<br/>';
                            
                            $code=$suborder_authorid."&".$suborder_id;
                            $key='buylocksmithdeals';
                            $string=BuyLockSmithDealsCustomizationAddon::code_encrypt($code, $key);
                            $text = home_url().'/vendor-confirmation?code='.$string;
                            $path = BUYLOCKSMITH_DEALS_PLUGIN_DIR.'/assets/images/'; 
                            $file = $path.$unique_id.".png"; 
                            $file_image = BUYLOCKSMITH_DEALS_ASSETS_PATH."/images/".$unique_id.".png"; 
                            // $ecc stores error correction capability('L') 
                            $ecc = 'L'; 
                            $pixel_Size = 10; 
                            $frame_Size = 10; 
                            // Generates QR Code and Stores it in directory given 
                            QRcode::png($text, $file, $ecc, $pixel_Size, $frame_size); 
                            // Displaying the stored QR code from directory 
                           echo "<center><img src='".$file_image."' style='height:100px !important; margin:10px !important;'></center>"; 
                            echo '</div>';
                        } ?>
                       <h3 style="font-style: normal;">Once the job is completed to your satisfaction, please show your technician this code.</h3>
                       <h4 style="font-style: normal;">To reschedule or cancel this job, please contact the locksmith directly. Their contact information is below. Please reference your order number</h4>
                       <?php 
                        $code=$order->get_id()."&".$suborder_id;
                        $key='buylocksmithdeals';
                        $string=BuyLockSmithDealsCustomizationAddon::code_encrypt($code, $key);
                        $dispute_link=home_url().'/order-dispute?code='.$string;?>
                       <h4 style="font-style: normal;">If for any reason you feel like the job was not completed professionally or correctly, or you feel the charges were not explained honestly, <a href="<?php echo $dispute_link; ?>" >please click here to dispute.</a> </h4>
                    </address>
		</td>
            </tr>
    </table>
 <?php } } } */ ?>
 <?php if($booking_id){ ?>
	<div class="block-grid two-up" style="Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;">
	<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">

	<div class="col num6" style="vertical-align: top; width:100%;">
	<div style="width:100% !important;">
	<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">

	<div style="color:#555555;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px;border-radius: 30px;border: 2px solid #333333;">
		<div style="line-height: 1.2; font-size: 12px; color: #555555; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 14px;">
			<p style="font-size: 14px; line-height: 1.2; word-break: break-word; mso-line-height-alt: 17px; margin: 0;"><u><span style="color: #000000;"><strong>Customer Details</strong></span></u></p>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<strong style="color: #000000;">Name:</strong> <?php echo $order->get_billing_first_name().' '.$order->get_billing_last_name(); ?>
			</p>
			<?php if($order->get_billing_company()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<strong style="color: #000000;">Company:</strong> <?php echo $order->get_billing_company(); ?>
			</p>
			<?php } ?>
			<?php if($order->get_billing_address_1()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<strong style="color: #000000;">Address:</strong> <?php echo $order->get_billing_address_1(); ?>
			</p>
			<?php } ?>
			<?php if($order->get_billing_address_2()){ ?>
			, <span style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo $order->get_billing_address_2(); ?>
			</span>
			<?php } ?>
			<?php if($order->get_billing_city()){ ?>
			, <span style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo $order->get_billing_city(); ?>
			</span>
			<?php } ?>
			<?php if($order->get_billing_state()){ ?>
			, <span style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo WC()->countries->states[$order->get_billing_country()][$order->get_billing_state()]; ?>
			</span>
			<?php } ?>
			<?php if($order->get_billing_country()){ ?>
			, <span style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo WC()->countries->countries[$order->get_billing_country()]; ?>
			</span>
			<?php } ?>
			<?php if($order->get_billing_postcode()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<strong style="color: #000000;">Zip/Postal Code:</strong> <?php echo $order->get_billing_postcode(); ?>
			</p>
			<?php } ?>
			<?php if($order->get_billing_phone()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<strong style="color: #000000;">Phone:</strong> <?php echo $order->get_billing_phone(); ?>
			</p>
			<?php } ?>
			<?php if($order->get_billing_email()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<strong style="color: #000000;">Email:</strong> <?php echo $order->get_billing_email(); ?>
			</p>
			<?php } ?>
			
		</div>
	</div>
	 
	</div>
	 
	</div>
	</div>

	<div class="col num6" style="vertical-align: top; width: 100%;">
	<div style="width:100% !important;">
	 
	<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
	 
	<div style="color:#555555;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px;border-radius: 30px;border: 2px solid #333333;">
	<div style="line-height: 1.2; font-size: 12px; color: #555555; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 14px;">
	
		<p style="font-size: 14px; line-height: 1.2; word-break: break-word; mso-line-height-alt: 17px; margin: 0;"><u><span style="color: #000000;"><strong>Job Details</strong></span></u></p>
		<?php 
				$items = $order->get_items();
				foreach ( $items as $item_id => $item ) :
					
						/* wc_display_item_meta(
							$item,
							array(
								'label_before' => '<p style="line-height: 1.2; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">',
							)
						); */
						
						foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
							//$value = trim( $meta->display_value );
							if($meta->key == 'Sold By' || $meta->key == 'Subtotal'){

							}else{
								echo '<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;"><strong style="color: #000000;">'.$meta->key. ': </strong>' .$meta->value.'</p>';
							}
						}
				endforeach;
				
				foreach ( $order->get_order_item_totals() as $key => $total ) {
						if($total['label'] == 'Subtotal:'){

						}else{
					?>
						<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;"><strong style="color: #000000;"><?php echo esc_html( $total['label'] ); ?></strong> <?php echo ( 'payment_method' === $key ) ? esc_html( $total['value'] ) : wp_kses_post( $total['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
							
						<?php
					}
				}
		?>
	</div>
	</div>
	 
	</div> 
	</div>
	</div> 
	</div>
	</div> 
<?php }else{ ?>
<table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
	<tr>
		<td style="text-align:<?php echo esc_attr( $text_align ); ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;" valign="top" width="50%">
			<h2><?php esc_html_e( 'Customer Billing Address', 'woocommerce' ); ?></h2>

			<address class="address">
				<?php echo wp_kses_post( $address ? $address : esc_html__( 'N/A', 'woocommerce' ) ); ?>
				<?php if ( $order->get_billing_phone() ) : ?>
					<br/><?php echo esc_html( $order->get_billing_phone() ); ?>
				<?php endif; ?>
				<?php if ( $order->get_billing_email() ) : ?>
					<br/><?php echo esc_html( $order->get_billing_email() ); ?>
				<?php endif; ?>
			</address>
		</td>
		<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $shipping ) : ?>
			<td style="text-align:<?php echo esc_attr( $text_align ); ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; padding:0;" valign="top" width="50%">
				<h2><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></h2>

				<address class="address"><?php echo wp_kses_post( $shipping ); ?></address>
			</td>
		<?php endif; ?>
	</tr>
</table>
 <?php } ?>