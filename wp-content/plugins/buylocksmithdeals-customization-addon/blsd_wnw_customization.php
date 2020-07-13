<?php

function avlabs_encrypt_decrypt($action, $string) {
    $output = false;

    $encrypt_method = "AES-256-CBC";
    $secret_key = 'This is my secret key';
    $secret_iv = 'This is my secret iv';

    // hash
    $key = hash('sha256', $secret_key);
    
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

add_action('wp','avlabs_verify_vendor_redirect_booking_edit');
function avlabs_verify_vendor_redirect_booking_edit(){
	if(isset($_REQUEST['verify']) && $_REQUEST['verify'] != ''){
		$decrypt_key = avlabs_encrypt_decrypt('decrypt', $_REQUEST['verify']);
		if($decrypt_key != ''){
			$decrypt_data = explode('-',$decrypt_key);
			if(!empty($decrypt_data)){
				$vendor_id = $decrypt_data[0];
				$vendor_order_id = $decrypt_data[1];
				$vendor_booking_id = $decrypt_data[2];
				if ( FALSE === get_post_status( $vendor_order_id ) ) {
					$vendor_order_id_exit = 0;
				} 
				else{	
					$vendor_order_id_exit = 1;
				}
				
				if ( FALSE === get_post_status( $vendor_booking_id ) ) {
					$vendor_booking_id_exit = 0;
				} 
				else{	
					$vendor_booking_id_exit = 1;
				}
				
				if(avlabs_user_id_exists($vendor_id) && $vendor_order_id_exit ==1 && $vendor_booking_id_exit){
					 wp_set_auth_cookie($vendor_id);
					 $location =site_url().'/dashboard/vendor-orders?order_id='.$vendor_order_id.'&bookings_vendor='.$vendor_booking_id;
					 wp_redirect($location);
					 exit;
				}else{
					?>
					<script>
					alert('Something went worng.');
					</script>
					<?php
				}
			}else{
				?>
					<script>
					alert('Something went worng.');
					</script>
					<?php
			}
		}else{
			?>
					<script>
					alert('Something went worng.');
					</script>
					<?php
		}
	}
}

function avlabs_user_id_exists($user){

    global $wpdb;

    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user));

    if($count == 1){ return true; }else{ return false; }

}

add_action('wp','avlabs_remove_wcmp_email_class_action');
function avlabs_remove_wcmp_email_class_action(){
	
	global $wpdb;
	
	avlabs_remove_class_action('woocommerce_email_customer_details','WCMp_Email','wcmp_vendor_messages_customer_support',30);
	add_action('woocommerce_email_customer_details','avlabs_wcmp_vendor_messages_customer_support',30);
	
}
function avlabs_wcmp_vendor_messages_customer_support($order, $sent_to_admin = false, $plain_text = false){
	
		global $WCMp;
		$WCMp->load_class( 'template' );
		$WCMp->template = new WCMp_Template();
		$items = $order->get_items( 'line_item' );
		$vendor_array = array();
		$author_id = '';
		$customer_support_details_settings = get_option('wcmp_general_customer_support_details_settings_name');
		$is_csd_by_admin = '';
		
		foreach( $items as $item_id => $item ) {			
			$product_id = wc_get_order_item_meta( $item_id, '_product_id', true );
			if( $product_id ) {				
				$author_id = wc_get_order_item_meta( $item_id, '_vendor_id', true );
				if( empty($author_id) ) {
					$product_vendors = get_wcmp_product_vendors($product_id);
					if(isset($product_vendors) && (!empty($product_vendors))) {
						$author_id = $product_vendors->id;
					}
					else {
						$author_id = get_post_field('post_author', $product_id);
					}
				}
				if(isset($vendor_array[$author_id])){
					$vendor_array[$author_id] = $vendor_array[$author_id].','.$item['name'];
				}
				else {
					$vendor_array[$author_id] = $item['name'];
				}								
			}						
		}		
		if($plain_text) {
			
		}
		else {	
                        $is_customer_support_details = apply_filters('is_customer_support_details', true);
			if(apply_filters('can_vendor_add_message_on_email_and_thankyou_page', true) ) {
				
				global $wpdb;
				$orderid = $order->get_id();

				$booking_id = $wpdb->get_var("SELECT ID FROM wp_posts where post_parent=$orderid AND post_type='wc_booking'");
				if($booking_id!=''){
					?>
				<div class="col num12" style="min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;">
					<div style="width:100% !important;"> 
					<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">

					<div style="color:#555555;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:20px;padding-right:30px;padding-bottom:20px;padding-left:30px;border-radius: 20px;
						border: 2px solid #333333;">
					<div style="line-height: 1.2; font-size: 12px; color: #555555; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 14px;">
					<p style="font-size: 14px; line-height: 1.2; word-break: break-word; mso-line-height-alt: 17px; margin: 0;"><u><span style="color: #000000;"><strong>Contact Your Locksmith</strong></span></u></p>
					
		<?php 
			foreach ($vendor_array as $vendor_id => $products) {
				if(is_user_wcmp_vendor($vendor_id)){
					$vendor_meta = get_user_meta($vendor_id);
					$vendor = get_wcmp_vendor($vendor_id);
					$vendor_message_to_buyer = apply_filters('wcmp_display_vendor_message_to_buyer', get_user_meta($vendor_id, '_vendor_message_to_buyers', true), $vendor_id);
				?>
				<p style="font-size: 14px; line-height: 1.2; word-break: break-word; mso-line-height-alt: 17px; margin: 0;"><span style="color: #000000;"><strong>Name:</strong> <?php echo $vendor->page_title; ?></span></p>
				<p style="font-size: 14px; line-height: 1.2; word-break: break-word; mso-line-height-alt: 17px; margin: 0;"><span style="color: #000000;"><strong>Get Out Latest Offers at:</strong> <?php echo site_url().'/locksmith-store/'.$vendor->page_slug; ?></span></p>
				<?php if($vendor_meta['_vendor_phone'][0] !=''){ ?>
				<p style="font-size: 14px; line-height: 1.2; word-break: break-word; mso-line-height-alt: 17px; margin: 0;"><span style="color: #000000;"><strong>Call us:</strong> <?php echo $vendor_meta['_vendor_phone'][0]; ?></span></p>
				<?php
					}
				 ?>
				 <?php 
					
					$fb_icon=BUYLOCKSMITH_DEALS_ASSETS_PATH.'img/facebook.png';
					
					$twitter_icon=BUYLOCKSMITH_DEALS_ASSETS_PATH.'img/twitter.png';
					
					$linkedin_icon=BUYLOCKSMITH_DEALS_ASSETS_PATH.'img/linkedin.png';/*
				 ?>
				<p style="font-size: 14px; line-height: 1.2; word-break: break-word; mso-line-height-alt: 17px; margin: 0;">
					<span style="color: #000000;">
					<?php if($vendor_meta['_vendor_fb_profile'][0] !=''){ ?>
					<a href="<?php echo $vendor_meta['_vendor_fb_profile'][0]; ?>" target="_blank"><img src="<?php echo $fb_icon; ?>" height="30" width="30"></a>
					<?php } ?>
					<?php if($vendor_meta['_vendor_twitter_profile'][0] !=''){ ?>
					<a href="<?php echo $vendor_meta['_vendor_twitter_profile'][0]; ?>" target="_blank" ><img src="<?php echo $twitter_icon; ?>" height="30" width="30"></a>
					<?php } ?>
					<?php if($vendor_meta['_vendor_linkdin_profile'][0] !=''){ ?>
					<a href="<?php echo $vendor_meta['_vendor_linkdin_profile'][0]; ?>" target="_blank" ><img src="<?php echo $linkedin_icon; ?>" height="30" width="30"></a>
					<?php } ?>
					</span>
				</p>
				<?php*/
			
					
				}
			}
		?>
		</div>
		</div>

		</div> 
		</div>
	</div>
	<?php
					
				}else{
				
					$WCMp->template->get_template( 'vendor_message_to_buyer.php', array( 'vendor_array'=>$vendor_array, 'capability_settings'=>$customer_support_details_settings, 'customer_support_details_settings'=>$customer_support_details_settings ));
				}
			}
			elseif(get_wcmp_vendor_settings ('is_customer_support_details', 'general') == 'Enable' && $is_customer_support_details) {
				$WCMp->template->get_template( 'customer_support_details_to_buyer.php', array( 'vendor_array'=>$vendor_array, 'capability_settings'=>$customer_support_details_settings, 'customer_support_details_settings'=>$customer_support_details_settings ));
			}
		}
	
}

function avlabs_remove_class_action($tag, $class = '', $method, $priority = null) : bool {
    global $wp_filter;
    if (isset($wp_filter[$tag])) {
        $len = strlen($method);

        foreach($wp_filter[$tag] as $_priority => $actions) {

            if ($actions) {
                foreach($actions as $function_key => $data) {

                    if ($data) {
                        if (substr($function_key, -$len) == $method) {

                            if ($class !== '') {
                                $_class = '';
                                if (is_string($data['function'][0])) {
                                    $_class = $data['function'][0];
                                }
                                elseif (is_object($data['function'][0])) {
                                    $_class = get_class($data['function'][0]);
                                }
                                else {
                                    return false;
                                }

                                if ($_class !== '' && $_class == $class) {
                                    if (is_numeric($priority)) {
                                        if ($_priority == $priority) {
                                            //if (isset( $wp_filter->callbacks[$_priority][$function_key])) {}
                                            return $wp_filter[$tag]->remove_filter($tag, $function_key, $_priority);
                                        }
                                    }
                                    else {
                                        return $wp_filter[$tag]->remove_filter($tag, $function_key, $_priority);
                                    }
                                }
                            }
                            else {
                                if (is_numeric($priority)) {
                                    if ($_priority == $priority) {
                                        return $wp_filter[$tag]->remove_filter($tag, $function_key, $_priority);
                                    }
                                }
                                else {
                                    return $wp_filter[$tag]->remove_filter($tag, $function_key, $_priority);
                                }
                            }

                        }
                    }
                }
            }
        }

    }

    return false;
}

add_action('wp','print_order');
function print_order(){
	if($_REQUEST['print_order'] == 'yes'){
		$order = new WC_Order( 3013 );
		?>
		<div class="block-grid two-up" style="Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;">
	<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">

	<div class="col num6" style="max-width: 320px; min-width: 250px; display: table-cell; vertical-align: top; width: 250px;">
	<div style="width:100% !important;">
	<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">

	<div style="color:#555555;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px;border-radius: 30px;border: 2px solid #5d5dff;width: 75%;min-height:220px">
		<div style="line-height: 1.2; font-size: 12px; color: #555555; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; mso-line-height-alt: 14px;">
			<p style="font-size: 14px; line-height: 1.2; word-break: break-word; mso-line-height-alt: 17px; margin: 0;"><u><span style="color: #000000;"><strong>Customer Details</strong></span></u></p>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo $order->get_billing_first_name().' '.$order->get_billing_last_name(); ?>
			</p>
			<?php if($order->get_billing_company()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo $order->get_billing_company(); ?>
			</p>
			<?php } ?>
			<?php if($order->get_billing_address_1()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo $order->get_billing_address_1(); ?>
			</p>
			<?php } ?>
			<?php if($order->get_billing_address_2()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo $order->get_billing_address_2(); ?>
			</p>
			<?php } ?>
			<?php if($order->get_billing_city()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo $order->get_billing_city(); ?>
			</p>
			<?php } ?>
			<?php if($order->get_billing_state()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo WC()->countries->states[$order->get_billing_country()][$order->get_billing_state()]; ?>
			</p>
			<?php } ?>
			<?php if($order->get_billing_country()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo WC()->countries->countries[$order->get_billing_country()]; ?>
			</p>
			<?php } ?>
			<?php if($order->get_billing_postcode()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo $order->get_billing_postcode(); ?>
			</p>
			<?php } ?>
			<?php if($order->get_billing_phone()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo $order->get_billing_phone(); ?>
			</p>
			<?php } ?>
			<?php if($order->get_billing_email()){ ?>
			<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">
			<?php echo $order->get_billing_email(); ?>
			</p>
			<?php } ?>
			
		</div>
	</div>
	 
	</div>
	 
	</div>
	</div>

	<div class="col num6" style="max-width: 320px; min-width: 250px; display: table-cell; vertical-align: top; width: 250px;">
	<div style="width:100% !important;">
	 
	<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
	 
	<div style="color:#555555;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px;border-radius: 30px;border: 2px solid #5d5dff;width: 75%;float: right;min-height:220px">
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
							echo '<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;">'.$meta->key. ':' .$meta->value.'</p>';
						}
				endforeach;
				
				foreach ( $order->get_order_item_totals() as $key => $total ) {
					?>
						<p style="line-height: 1.7; word-break: break-word; mso-line-height-alt: NaNpx; margin: 0;"><?php echo esc_html( $total['label'] ); ?><?php echo ( 'payment_method' === $key ) ? esc_html( $total['value'] ) : wp_kses_post( $total['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
							
						<?php
				}
		?>
	</div>
	</div>
	 
	</div> 
	</div>
	</div> 
	</div>
	</div>
		<?php
				exit;
	}
}