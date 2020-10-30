<?php

/*Modify product query on forntend for showing deleted product - RSV 13-10-2020 start*/

function shop_filter_cat($query) {
    if (!is_admin() && is_post_type_archive( 'product' ) && $query->is_main_query()) {
		
		$query->set('meta_query', array(
			array ('key' => '_vendor_product_parent',
				   'compare' => 'NOT EXISTS'
			)
		) );   
	}

    return $query;
}
add_action('pre_get_posts','shop_filter_cat',9999,1);

/*End*/

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



add_filter('woocommerce_email_customizer_plus_short_code_values','woocommerce_email_customizer_plus_short_code_values_new',10,4);
function woocommerce_email_customizer_plus_short_code_values_new($short_codes, $order, $email_arguments, $sample){
	global $wpdb;
	$orderid = $order->get_id();
	$short_codes['url']['dashboard'] = home_url('dashboard');

	//test vendor logo
	$suborder_authorname=''; 
	$profile_image='';
	$sub_orders = get_children( array('post_parent' => $order->get_id(), 'post_type' => 'shop_order' ) );
	if(!empty($sub_orders)){
		foreach($sub_orders as $sorder){
		$suborder_id=$sorder->ID;
		$suborder_authorid=$sorder->post_author;
		$suborder_authorname=get_author_name( $suborder_authorid);
		$vendor_profile_image = get_user_meta($suborder_authorid, '_vendor_profile_image', true);
		if (isset($vendor_profile_image) && $vendor_profile_image > 0){
			$profile_image = wp_get_attachment_url($vendor_profile_image);
		}
		$code=$order->get_id()."&".$suborder_id."&".$suborder_authorid;
		$key='buylocksmithdeals';
		$string=code_encrypt_new($code, $key);
		$review_rating=site_url().'/review-rating?code='.$string;
		}
	}
	else{
		$suborder_authorid = get_post_field( 'post_author', $order->get_id() );
		$suborder_authorname=get_author_name( $suborder_authorid);
		$vendor_profile_image = get_user_meta($suborder_authorid, '_vendor_profile_image', true);
		if (isset($vendor_profile_image) && $vendor_profile_image > 0){
			$profile_image = wp_get_attachment_url($vendor_profile_image);
		}
	}
	if($profile_image == ''){
		$short_codes['vendor']['logo'] = $suborder_authorname;
	}else{
		$short_codes['vendor']['logo'] = "<img width='100px' height='100px' src=".$profile_image.">";
	}
	$short_codes['vendor']['name'] = $suborder_authorname;

	$sub_orders = get_children( array('post_parent' => $orderid, 'post_type' => 'shop_order' ) );
	$review_rating='';
	foreach($sub_orders as $sorder){
		$suborder_id=$sorder->ID;
		$suborder_authorid=$sorder->post_author;
		$code=$order->get_id()."&".$suborder_id."&".$suborder_authorid;
		$key='buylocksmithdeals';
		$string=BuyLockSmithDealsCustomizationAddon::code_encrypt($code, $key);
		$review_rating=home_url().'/review-rating?code='.$string;
	}
	$vendor_review = '<a href="'.$review_rating.'" target="_blank"><button type="button" style="background: #92d050;display: inline-block;padding: 15px 50px;border: 2px solid #2f528f;border-radius: 10px;font-size: 16px;font-weight: 400;color: #fff;cursor: pointer;">Leave Us A Review</button></a>';

	$short_codes['vendor']['vendor_review_button'] = $vendor_review;
	
	//test order end date
	$order_date = wc_format_datetime( $order->get_date_created() );
	$short_codes['order']['order_date'] = $order_date; 

	//test job schedule details
	$booking_id = $wpdb->get_var("SELECT ID FROM wp_posts where post_parent=$orderid AND post_type='wc_booking'");
	if($booking_id!=''){		
		$booking = get_wc_booking( $booking_id );
		$booking_start_date = date('d F,Y', $booking->get_start( 'view' ));
		$booking_start_time = date('h:i a', $booking->get_start( 'view' ));
	}
	$short_codes['job_details']['job_time'] = $booking_start_time;
	$short_codes['job_details']['job_date'] = $booking_start_date;

	//test job item details
	$item_details = '';
	$items = $order->get_items();
	foreach ( $items as $item_id => $item ) :
		foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
			if($meta->key == 'Sold By' || $meta->key == 'Subtotal'){

			}else{
				$item_details .= '<p><b>'.$meta->key. ': </b>' .$meta->value.'<p>';
			}
		}
	endforeach;
	foreach ( $order->get_order_item_totals() as $key => $total ) {
		if($total['label'] == 'Subtotal:'){

		}else{
			if( 'payment_method' === $key ) { 
				$value = esc_html( $total['value'] ); 
			}else{ 
				$value = wp_kses_post( $total['value'] );
			}
			$item_details .= '<p><b>'.esc_html( $total['label'] ). ' </b>' .$value.'<p>';
		}
	}
	$short_codes['job_details']['job_meta_all'] = $item_details;


	//test vendor details
	global $WCMp;
	$items = $order->get_items( 'line_item' );
	$vendor_array = array();
	$vendor_details = '';
	$author_id = '';
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

	foreach ($vendor_array as $vendor_id => $products) {
		if(is_user_wcmp_vendor($vendor_id)){
			$vendor_meta = get_user_meta($vendor_id);
			$vendor = get_wcmp_vendor($vendor_id);
			
			$vendor_details .= "<p><b>Name: </b>".$vendor->page_title."</p>";
			$vendor_details .= "<p><b>Get Out Latest Offers at: </b>".site_url().'/locksmith-store/'.$vendor->page_slug."</p>";
		
			if($vendor_meta['_vendor_phone'][0] !=''){

				$vendor_details .= "<p><b>Call us: </b>".$vendor_meta['_vendor_phone'][0]."</p>";

			}
		}
	}
	$short_codes['vendor']['contact_details'] = $vendor_details;

	//test order disbute details
	$dispute_table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_table_name();
	$query = "SELECT * FROM $dispute_table_name WHERE order_id = $suborder_id order by id DESC";
	$result_dispute = $wpdb->get_row($query);
	$ordersub = new WC_Order($suborder_id);
	if($result_dispute){
		$dispute_message_table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_message_table_name();
		$query = "SELECT * FROM $dispute_message_table_name WHERE dispute_id = $result_dispute->id order by id DESC";
		$result_message_dispute = $wpdb->get_row($query);
		
		ob_start();
		$customer_dispute_data = blsd_email_dispute_content($order, $result_dispute->who_opose_user_id, $result_message_dispute->title, $result_message_dispute->message, $result_message_dispute->username, $result_message_dispute->phone_number, $result_message_dispute->email, $suborder_id, $is_admin = '0');
		$customer_dispute_data_echo = ob_get_contents();
		ob_get_clean();
		// $short_codes['dispute']['customer_details'] = 'dklfjsldfjkslf';
		$short_codes['dispute']['customer_details'] = $customer_dispute_data_echo;
	}

	//test vendor order email details
	$vendor_order_details = '';
	$update_job_time = '';
	$mark_complete = '';
	$vendor = get_wcmp_vendor(absint($suborder_authorid));
	$text_align = is_rtl() ? 'right' : 'left';
	$vendor_order_id = $order->get_id();
	$vendor_id = $vendor->id;
	$main_order_id = $wpdb->get_var("SELECT post_parent FROM wp_posts where ID=$vendor_order_id AND post_type='shop_order'");
	$booking_id = $wpdb->get_var("SELECT ID FROM wp_posts where post_parent=$main_order_id AND post_type='wc_booking'");
	//important button	
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

			$update_job_time .='<a href="'.$url.'" style="font-weight: normal;text-decoration: underline;border: 1px solid #77c84e;background: #77c84e;padding: 8px;border-radius: 6px;color: #fff;text-decoration: none;font-weight: 600;">Update Job Time/Date</a>';
			$mark_complete .= '<a href="'.$markUrl.'" style="font-weight: normal;text-decoration: underline;border: 1px solid #77c84e;background: #77c84e;padding: 8px;border-radius: 6px;color: #fff;text-decoration: none;font-weight: 600;">Mark Completed</a>';
			
		}
	
		//vendor order table

		ob_start();
		?>
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
		$vendor_order_details = ob_get_contents();
		ob_get_clean();
	$short_codes['vendor']['order_details'] = $vendor_order_details; 
	$short_codes['vendor']['update_job_time'] = $update_job_time; 
	$short_codes['vendor']['mark_complete'] = $mark_complete; 

	//test feedback code
	$social_logo = '';
	
	$suborder_authorname=''; 
	$profile_image='';
	$sub_orders = get_children( array('post_parent' => $orderid, 'post_type' => 'shop_order' ) );
	foreach($sub_orders as $sorder){
		$suborder_id=$sorder->ID;
		$suborder_authorid=$sorder->post_author;
		$suborder_authorname=get_author_name( $suborder_authorid);
		$vendor_profile_image = get_user_meta($suborder_authorid, '_vendor_profile_image', true);
		if (isset($vendor_profile_image) && $vendor_profile_image > 0){
			$profile_image = wp_get_attachment_url($vendor_profile_image);
		}
	}

	$sharing_fb_image=BUYLOCKSMITH_DEALS_ASSETS_PATH.'img/review-us-facebook.jpg';
	$sharing_google_image=BUYLOCKSMITH_DEALS_ASSETS_PATH.'img/review-us-google.jpg';
	$sharing_unlocks_image=BUYLOCKSMITH_DEALS_ASSETS_PATH.'img/review-us-1800.jpg';
	$vendor_fb_profile = get_user_meta($suborder_authorid, '_vendor_fb_profile', true);
	$vendor_google_plus_profile = get_user_meta($suborder_authorid, '_vendor_google_plus_profile', true);
	$vendor_1800_unlocks_profile = get_user_meta($suborder_authorid,'vendor_1800_unlocks_profile',1);

	$social_logo = '<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;">
		<tbody>
				<tr>
					<td>
						<a href="'.$vendor_fb_profile.'" target="_blank" ><img src="'.$sharing_fb_image.'" height="150" width="140"></a>       
						<a href="'.$vendor_google_plus_profile.'" target="_blank" ><img src="'.$sharing_google_image.'" height="150" width="140"></a>       
						<a href="'.$vendor_1800_unlocks_profile.'" target="_blank" ><img src="'.$sharing_unlocks_image.'" height="150" width="140"></a>       
					</td>
				</tr>
		</tbody>
	</table>
	</div>';

	$short_codes['vendor']['social_logo'] = $social_logo; 
	
	//job update
	$booking_orders = get_children( array('post_parent' => $orderid, 'post_type' => 'wc_booking' ) );
	foreach($booking_orders as $border){
		$border_id=$border->ID;
		$old_booking_details = get_post_meta($border_id, 'booking_update_details', true);
	}
	$additional_content_data=explode('||',$old_booking_details);
	$short_codes['booking']['before'] = $additional_content_data[0].' at '.$additional_content_data[1];
	$short_codes['booking']['after'] = $additional_content_data[2].' at '.$additional_content_data[3];

	return $short_codes;
}


function blsd_email_dispute_content($order, $customer_id, $title, $message_dispute, $username, $phone_number, $email, $order_number, $is_admin) {

	$order_id = $order->get_order_number();
	// echo "<br>";
	$body = '';
	$order_post = get_post($order_id);
	$parent_id = wp_get_post_parent_id($order_post);
	// echo "<br>";
	$product_table = '<table style="width:100%;border-collapse: collapse;" border="1">
	<tr>
	  <th>Order ID</th>
	  <th>Parent Order ID</th>
	  <th>Product</th>
	</tr>';
	$order_items = $order->get_items();
	foreach ($order->get_items() as $item_id => $item_data) {
		// Get an instance of corresponding the WC_Product object
		$product = $item_data->get_product();
		  if($product!=''){
			$product_name = $product->get_name(); // Get the product name
		}else{
			global $wpdb;
			$table_name = $wpdb->prefix.'woocommerce_order_items';
			 $query = "SELECT order_item_name FROM $table_name WHERE order_item_id=$item_id";
				$results_order_item = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
			   if(count($results_order_item)){
				   $product_name = $results_order_item[0]['order_item_name'];
			   }
		}

		$item_quantity = $item_data->get_quantity(); // Get the item quantity

		$item_total = $item_data->get_total(); // Get the item line total

		$product_table .= "<tr><td>$order_id</td>";
		$product_table .= "<td>$parent_id</td>";
		$product_table .= "<td> $product_name X $item_quantity = ". number_format((float)$item_total, 2, '.', ''). " </td></tr>";
	}
	$product_table .= '</table>';

	if($is_admin == '0'){
	//if customer 
		$body='';
		$body .='<tr><td>';
		$body .='<span>Name:'.$username.'</span>'.'<br>';
		$body .='<span>Phone Number:'.$phone_number.'</span>'.'<br>';
		$body .='<span>Email:'.$email.'</span>'.'<br>';
		$body .='<span>Order Number:'.$order_number.'</span>'.'<br>';
		$body .='<span>Dispute Title:'.$title.'</span>'.'<br>';
		$body .='<span>Dispute Message:'.$message_dispute.'</span>'.'<br>';
		$body .='</br></br>';
		$body .='Product detail:';
		$body .=$product_table;
		
		$body .='</td></tr>';
		$body .='Please visit in your account.';
			
	}else{

	//if admin 
		$body='';
		$body .='<tr><td>';
		$body .='<span>Name:'.$username.'</span>'.'<br>';
		$body .='<span>Phone Number:'.$phone_number.'</span>'.'<br>';
		$body .='<span>Email:'.$email.'</span>'.'<br>';
		$body .='<span>Order Number:'.$order_number.'</span>'.'<br>';
		$body .='<span>Dispute Title:'.$title.'</span>'.'<br>';
		$body .='<span>Dispute Message:'.$message_dispute.'</span>'.'<br>';
		$body .='</br></br>';
		$body .='Product detail:';
		$body .=$product_table;
		
		$body .='</td></tr>';
	}
?>
	<div style="margin-bottom: 40px;">
			<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" >
					<tbody>
						<?php echo $body; ?>
					</tbody>
			</table>
	</div>
<?php


}

function code_encrypt_new($string, $key) {
	$result = "";
	for ($i = 0; $i < strlen($string); $i++) {
		$char = substr($string, $i, 1);
		$keychar = substr($key, ($i % strlen($key)) - 1, 1);
		$char = chr(ord($char) + ord($keychar));
		$result .= $char;
	}
	$salt_string = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxys0123456789";
	$length = rand(1, 15);
	$salt = "";
	for ($i = 0; $i <= $length; $i++) {
		$salt .= substr($salt_string, rand(0, strlen($salt_string)), 1);
	}
	$salt_length = strlen($salt);
	$end_length = strlen(strval($salt_length));
	return base64_encode($result . $salt . $salt_length . $end_length);
}


/*****************************26-10-2020****************************************/

function wpdocs_dequeue_script() {
    wp_dequeue_script( 'shop-as-client' );
	wp_enqueue_script( 'shop-as-clientp', BUYLOCKSMITH_DEALS_ASSETS_PATH.'/js/functions.js', array( 'jquery' ), '1.3.0', true );
}
add_action( 'wp_print_scripts', 'avlabs_dequeue_script', 100 );

add_action( 'wp', 'avlabs_add_to_cart_on_custom_page');
 
function avlabs_add_to_cart_on_custom_page(){
 
	if( isset($_REQUEST['custom-add-cart']) &&  $_REQUEST['custom-add-cart'] == 'yes') {
		//if( ! WC()->cart->is_empty() ){
			WC()->cart->empty_cart();
			WC()->cart->add_to_cart( 3541 );
		//}
	}
 
}

function avlabs_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
 if( $product_id == 3541 ) {
 $cart_item_data['wnw-custom-data'] = '15';
 }
 return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'avlabs_add_cart_item_data', 10, 3 );

function avlabs_get_item_data( $item_data, $cart_item_data ) {
 if( isset( $cart_item_data['wnw-custom-data'] ) ) {
 $item_data[] = array(
 'key' => __( 'Materials', 'plugin-republic' ),
 'value' => wc_clean( $cart_item_data['wnw-custom-data'] )
 );
 }
 return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'avlabs_get_item_data', 10, 2 );

function avlabs_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
 if( isset( $values['wnw-custom-data'] ) ) {
 $item->add_meta_data(
 __( 'Material', 'plugin-republic' ),
 $values['wnw-custom-data'],
 true
 );
 }
}
add_action( 'woocommerce_checkout_create_order_line_item', 'avlabs_checkout_create_order_line_item', 10, 4 );


function avlabs_change_cart_item_price( $cart_object ) {  
    global $isProcessed;
    if( !WC()->session->__isset( "reload_checkout" )) {

        foreach ( $cart_object->get_cart() as $key => $value ) {

            
            if( isset( $value["wnw-custom-data"] ) && $value["wnw-custom-data"] == '15' ) {
               
                $additionCost = 200;
				$value['data']->set_price($additionCost);
            } 
          
        } 
        $isProcessed = true;  
    }
    //print('<pre>');print_r($cart_object);print('</pre>');
}

add_action( 'woocommerce_before_calculate_totals', 'avlabs_change_cart_item_price', 99 );

add_filter('shop_as_client_allow_checkout','av_shop_as_client_allow_checkout',10,1);
function av_shop_as_client_allow_checkout($val){
	if(is_user_logged_in()){
		$user = wp_get_current_user();
		$roles = ( array ) $user->roles;
		if(in_array('dc_vendor',$roles)){
			$val = true;
		}
	}
	return $val;
}

/* add_filter('stripe_marketplace_create_stripe_direct_charges','av_stripe_marketplace_create_stripe_direct_charges',999,1);
function av_stripe_marketplace_create_stripe_direct_charges($charge_data){
	$charge_data['application_fee_amount'] = 2000;
	wp_mail('vijay.webnware@gmail.com','stripe_charge_data',serialize($charge_data));
	return $charge_data;
} */

add_action('wp','avlabs_vendor_redirect');
function avlabs_vendor_redirect(){
	global $wp,$WCMp;
	if(is_user_logged_in()){
		if (is_vendor_dashboard() && is_user_logged_in() && (is_user_wcmp_vendor(get_current_user_id()))) {
			session_start();
			$author_id=get_current_vendor_id();
			$address1 = get_user_meta($author_id,'_vendor_address_1',true);
			$address2 = get_user_meta($author_id,'_vendor_address_2',true);
			$city = get_user_meta($author_id,'_vendor_city',true);
			$state = get_user_meta($author_id,'_vendor_state',true);
			$postcode = get_user_meta($author_id,'_vendor_postcode',true);
			$vendor_address=$address1.' '.$address2.' '.$city.' '.$state.' '.$postcode;
			if($address1=='' && $address2 == '' && $city== '' && $state == '' && $postcode ==''){
				ob_start();
				$panel = $WCMp->vendor_dashboard->dashboard_header_right_panel_nav();
				$url=$panel['storefront']['url'];
				$url_end=strtolower($panel['storefront']['label']);
				$current_url=home_url( $wp->request );
				$current_end=end(explode("/", $current_url));
				if($current_end != $url_end){
					
				  if(!isset($_SESSION['set_redirect_once']) || $_SESSION['set_redirect_once'] != 'redirected' ){
					   $_SESSION['set_redirect_once']='redirect'; 
					   $_SESSION['set_redirect_message']='Please set address!';
					   wp_redirect($url);
					   exit;
				  }
				  else{
				   $_SESSION['set_redirect_message']='';    
				  }
				}
				else{
				 $_SESSION['set_redirect_once']='redirected';  
				}
			}   
			else{
			  unset($_SESSION['set_redirect_message']);  
			  unset($_SESSION['set_redirect_once']);
			}
			/* echo '<pre>';
			print_r($_SESSION);
			echo '</pre>';
			echo "VIKAS"; */
		}
		
		/* echo '<pre>';
			print_r($_SESSION);
			echo '</pre>'; */
	}
}