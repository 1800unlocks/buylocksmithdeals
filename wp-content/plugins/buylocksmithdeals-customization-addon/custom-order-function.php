<?php
/*****************************26-10-2020****************************************/

function wpdocs_dequeue_script() {
    wp_dequeue_script( 'shop-as-client' );
	wp_enqueue_script( 'shop-as-clientp', BUYLOCKSMITH_DEALS_ASSETS_PATH.'/js/functions.js?vr='.time(), array( 'jquery' ), '1.3.0', true );
}
add_action( 'wp_print_scripts', 'avlabs_dequeue_script', 100 );

add_action( 'wp', 'avlabs_add_to_cart_on_custom_page');
 
function avlabs_add_to_cart_on_custom_page(){
 
	// if( isset($_REQUEST['custom-add-cart']) &&  $_REQUEST['custom-add-cart'] == 'yes') {

		global $wpdb;
		$user_id 					= 	get_current_user_id();
		
		$user = wp_get_current_user();
		if ((! in_array( 'dc_vendor', (array) $user->roles ))  || !is_user_logged_in()) {
			return;
		}
		
		$get_user_custom_product 	= 	get_user_meta($user_id,'_custom_product_id', true);
		 
		if($get_user_custom_product){
			$get_product_details	=	get_post_meta($get_user_custom_product,'_custom_product', true);
		} else{
			$get_user_custom_product = wp_insert_post( array(
				'post_title' => 'Custom Product By - '.$user_id,
				'post_content' => 'This Is custom prouct by '. $user_id,
				'post_status' => 'publish',
				'post_type' => "product",
				'post_author' => $user_id,
			) );
			update_post_meta( $get_user_custom_product, '_sale_price_dates_to', '' );
			update_post_meta( $get_user_custom_product, '_price', '50' );
			update_post_meta( $get_user_custom_product, '_sold_individually', '' );
			update_post_meta( $get_user_custom_product, '_manage_stock', 'no' );
			update_post_meta( $get_user_custom_product, '_backorders', 'no' );
			update_post_meta( $get_user_custom_product, '_stock', '' );
			update_post_meta( $get_user_custom_product, '_custom_product', 'yes' );
			update_user_meta( $user_id, '_custom_product_id',$get_user_custom_product );
		}
		// if($get_product_details == 'yes'){
		// 	$product_price 			=	get_post_meta($get_user_custom_product,'_price', true);
		// }	
		if(isset($_POST['submit_custom_order'])){
		
			$all_data_get = 	$wpdb->get_var(
				'Select id FROM `create_custom_order` WHERE product_id = "'.$get_user_custom_product.'"'
			);
			$all_data_get = 	$wpdb->query(
				'DELETE FROM `create_custom_order` WHERE id = "'.$all_data_get.'"'
			);
			$all_data_get = 	$wpdb->query(
				'DELETE FROM `create_custom_order_variation` WHERE create_custom_order_id = "'.$all_data_get.'"'
			);
		
			$saveFieldArray=array( 
									'customer_name' => 	$_POST['customer_name'],
									'address'		=>	$_POST['address'],
									'email' 		=> 	$_POST['email'], 
									'mobile' 		=> 	$_POST['number'],
									'subtotal' 		=> 	$_POST['subtotal'],
									'tax_rate' 		=> 	$_POST['taxrate'],
									'total' 		=> 	$_POST['total'],
									'today_date' 	=> 	$_POST['today_date'],
									'product_id'	=> $get_user_custom_product,
									'user_id' 		=> 	$user_id,
								);
		
			//	$create_auction = $wpdb->query($create_auction);
				$tableName 	= 	'create_custom_order';
				$wpdb->insert( $tableName, $saveFieldArray);
				$lastinserid = $wpdb->insert_id;
				 $tableName_vari 	= 	'create_custom_order_variation';
				foreach($_POST['item'] as $key=>$val) {
					
					
						$saveFieldArray_all=array( 
												'create_custom_order_id'=> $lastinserid,
												'itemname' 				=> 	$_POST['item'][$key],
												'description'			=>	$_POST['description'][$key],
												'qty' 					=> 	$_POST['qty'][$key], 
												'rate' 					=> 	$_POST['rate'][$key],
												'amount' 				=> 	$_POST['amount'][$key],
												'tax' 					=> 	$_POST['tax'][$key],
												'status'				=> 	'1',
						);
					$wpdb->insert( $tableName_vari, $saveFieldArray_all);
					}
	 
		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( $get_user_custom_product );
			session_start();
	 	$_SESSION['custom_product_id'] = $get_user_custom_product;
	 	$_SESSION['total_price'] = $_POST['total'];
			
 		wp_redirect('checkout');
		 
		}
		// }
 
}

function avlabs_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
 global $wpdb;
 session_start();
	$get_user_custom_product = $_SESSION['custom_product_id'];

	if( $product_id == $get_user_custom_product ) {
		//echo '<style>th.product-quantity, td.product-quantity {display: none;}</style>'; 
		$sql = "SELECT * FROM `create_custom_order` AS cco LEFT JOIN create_custom_order_variation AS ccov ON ccov.create_custom_order_id = cco.id WHERE product_id = $get_user_custom_product ORDER BY create_custom_order_id DESC";
	 
			$get_all_variation = $wpdb->get_results($sql);
		//  echo '<pre>'; print_r($get_all_variation);
		//  exit;
			$cart_item_data['user_info'] 	=	'Customer Name : '.$get_all_variation[0]->customer_name.'<br>'.'Customer Address : '.$get_all_variation[0]->address.'<br>'.'Customer Email : '.$get_all_variation[0]->email.'<br>'.'Customer Mobile : '.$get_all_variation[0]->mobile.'<br>'.'Date : '.$get_all_variation[0]->today_date.'<br>'.'Tax Rate : '.$get_all_variation[0]->tax_rate.'(%)<br>';
			foreach($get_all_variation as $key => $All_Data){
					//$itemname = $get_all_variation_date['itemname'];
				$checkservice = $All_Data->itemname;
				
				if($checkservice == 'service_call'){
					$service_call[] 	=	$All_Data->description.' : '.$All_Data->qty.' x '.$All_Data->rate. ' = $'.$All_Data->amount;
				}
				if($checkservice == 'labor'){
					$labor[] 	=	$All_Data->description.' : '.$All_Data->qty.' x '.$All_Data->rate. ' = $'.$All_Data->amount;
				}
				if($checkservice == 'materials'){
					$materials[] 	=	$All_Data->description.' : '.$All_Data->qty.' x '.$All_Data->rate. ' = $'.$All_Data->amount;
					$total_matertial_price += $All_Data->amount;
				}
			}
			$_SESSION['total_matertial_price'] = $total_matertial_price;
			$cart_item_data['seavice_call'] = 	$service_call;
			$cart_item_data['labor'] 		= 	$labor;
			$cart_item_data['materials']	= 	$materials;
		 
	}
	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'avlabs_add_cart_item_data', 99999, 3 );

function avlabs_get_item_data( $item_data, $cart_item_data ) {
 
if( isset( $cart_item_data['user_info'] ) ) {
	 
	$item_data[] = array(
		'key' => __( 'Customer Information', 'plugin-republic' ),
		'value' => '</br>'.$cart_item_data['user_info'] 
	);
 
}
 if( isset( $cart_item_data['seavice_call'] ) ) {
	 
	 foreach($cart_item_data['seavice_call'] as $service_call){
		$item_data[] = array(
			'key' => __( 'Service Call', 'plugin-republic' ),
			'value' => wc_clean( $service_call )
		);
}
 }

if( isset( $cart_item_data['labor'] ) ) {
	foreach($cart_item_data['labor'] as $labor){
		$item_data[] = array(
			'key' => __( 'Labor', 'plugin-republic' ),
			'value' => wc_clean( $labor )
		);
	}
}

if( isset( $cart_item_data['materials'] ) ) {
	foreach($cart_item_data['materials'] as $materials){
		$item_data[] = array(
			'key' => __( 'Materials', 'plugin-republic' ),
			'value' => wc_clean( $materials )
		);
	}
}



 return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'avlabs_get_item_data', 10, 2 );

function avlabs_checkout_create_order_line_item( $item, $cart_item_key, $cart_item_data, $order ) {
		if( isset( $cart_item_data['user_info'] ) ) {			 
				 	$item->add_meta_data(
						__( 'Service Call', 'plugin-republic' ),
						'</br>'.$cart_item_data['user_info'],
						true
					);
		 }
		 if( isset( $cart_item_data['seavice_call'] ) ) {			 
				foreach($cart_item_data['seavice_call'] as $service_call){					
					$item->add_meta_data(
						__( 'Service Call', 'plugin-republic' ),
						wc_clean( $service_call ),
						true
					);
				}
		 }

		if( isset( $cart_item_data['labor'] ) ) {
			foreach($cart_item_data['labor'] as $labor){
				$item->add_meta_data(
									 __( 'Labor', 'plugin-republic' ),
									 wc_clean( $labor ),
									 true
									);
			}
		}

		if( isset( $cart_item_data['materials'] ) ) {
			foreach($cart_item_data['materials'] as $materials){
				$item->add_meta_data(
									 __( 'Materials', 'plugin-republic' ),
									 wc_clean( $materials ),
									 true
									);
			}
		}
		
		if( isset( $cart_item_data['user_info'] ) ) {
			foreach($cart_item_data['user_info'] as $user_info){
				$item->add_meta_data(
									 __( 'User Info', 'plugin-republic' ),
									 wc_clean( $user_info ),
									 true
									);
			}
		}
 
 
}
add_action( 'woocommerce_checkout_create_order_line_item', 'avlabs_checkout_create_order_line_item', 10, 4 );


function avlabs_change_cart_item_price( $cart_object ) {  
	global $isProcessed;
	session_start();
    if( !WC()->session->__isset( "reload_checkout" )) {
//echo '<pre>'; print_r($_SESSION['total_matertial_price']);

        foreach ( $cart_object->get_cart() as $key => $value ) {

            
            if( isset( $value["seavice_call"] ) ||  isset( $value["labor"] ) ||  isset( $value["materials"] ) ) {
               
                $additionCost = $_SESSION['total_price'];
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


add_filter('stripe_marketplace_create_stripe_direct_charges','av_stripe_marketplace_create_stripe_direct_charges',999,1);
function av_stripe_marketplace_create_stripe_direct_charges($charge_data){
	$stripe_marketplace_obj = new WCMp_Stripe_Marketplace_Gateway();
	$materials_total_amount = $_SESSION['total_matertial_price'];
	$total_price 	=	$_SESSION['total_price'];
	$after_remove_materials = ($total_price-$materials_total_amount);
	$payment_options = get_option('wcmp_payment_settings_name');	
	$percantace = $payment_options['app_fee_amount_commission'];
	$admin_total_price 	=	($after_remove_materials*$percantace)/100;
	//$final_price = $after_remove_materials-$admin_total_price;
	$charge_data['application_fee_amount'] = $stripe_marketplace_obj->get_stripe_amount($admin_total_price);
	wp_mail('vijay.webnware@gmail.com','stripe_charge_data',serialize($charge_data));
	return $charge_data;
} 


/* add_filter('stripe_marketplace_create_stripe_direct_charges','av_stripe_marketplace_create_stripe_direct_charges',999,1);
function av_stripe_marketplace_create_stripe_direct_charges($charge_data){
	$charge_data['application_fee_amount'] = 2000;
	wp_mail('vijay.webnware@gmail.com','stripe_charge_data',serialize($charge_data));
	return $charge_data;
} */

/********************* 27-10-2020 START *************************/



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



add_action( 'user_register', 'avlabs_registarto_create_product', 10, 1 );
 
function avlabs_registarto_create_product( $user_id ) {
	$post_id = wp_insert_post( array(
		'post_title' => 'Custom Product By - '.$user_id,
		'post_content' => 'This Is custom prouct by '. $user_id,
		'post_status' => 'publish',
		'post_type' => "product",
		'post_author' => $user_id,
	) );
	update_post_meta( $post_id, '_sale_price_dates_to', '' );
	update_post_meta( $post_id, '_price', '50' );
	update_post_meta( $post_id, '_sold_individually', '' );
	update_post_meta( $post_id, '_manage_stock', 'no' );
	update_post_meta( $post_id, '_backorders', 'no' );
	update_post_meta( $post_id, '_stock', '' );
	update_post_meta( $post_id, '_custom_product', 'yes' );
	update_user_meta( $user_id, '_custom_product_id', $post_id );
 
}


/********************* 27-10-2020 END *************************/

add_action('wcmp_init', 'after_wcmp_init');
function after_wcmp_init() {

add_action('settings_vendor_general_tab_options', 'add_custom_order_endpoint_option');

add_filter('settings_vendor_general_tab_new_input', 'save_custom_order_endpoint_options', 10, 2);

add_filter('wcmp_endpoints_query_vars', 'add_wcmp_endpoints_query_varsa');

add_filter('wcmp_vendor_dashboard_nav', 'add_tab_to_vendor_dashboard');

add_action('wcmp_vendor_dashboard_custom_order_endpoint', 'custom_order_menu_endpoint_content');
add_action('wcmp_vendor_dashboard_custom_order_endpoint', 'custom_order_menu_endpoint_contents');
}

function add_custom_order_endpoint_option($settings_tab_options) {
    $settings_tab_options['sections']['wcmp_vendor_general_settings_endpoint_ssection']['fields']['wcmp_custom_order_vendor_endpoint'] = array('title' => __('custom_order Menu', 'dc-woocommerce-multi-vendor'), 'type' => 'text', 'id' => 'wcmp_custom_order_vendor_endpoint', 'label_for' => 'wcmp_custom_order_vendor_endpoint', 'name' => 'wcmp_custom_order_vendor_endpoint', 'hints' => __('Set endpoint for custom_order menu page', 'dc-woocommerce-multi-vendor'), 'placeholder' => 'custom_order');
    return $settings_tab_options;
}

function save_custom_order_endpoint_options($new_input, $input) {
    if (isset($input['wcmp_custom_order_vendor_endpoint']) && !empty($input['wcmp_custom_order_vendor_endpoint'])) {
        $new_input['wcmp_custom_order_vendor_endpoint'] = sanitize_text_field($input['wcmp_custom_order_vendor_endpoint']);
    }
    return $new_input;
}

function add_wcmp_endpoints_query_varsa($endpoints) {
    $endpoints['custom_order'] = array(
    'label' => __('custom_order Menu', 'dc-woocommerce-multi-vendor'),
    'endpoint' => get_wcmp_vendor_settings('wcmp_custom_order_vendor_endpoint', 'vendor', 'general', 'custom_order')
    );
    $endpoints['custom_order'] = array(
        'label' => __('Custom Order', 'dc-woocommerce-multi-vendor'),
        'endpoint' => get_wcmp_vendor_settings('wcmp_custom_order_vendor_endpoint', 'vendor', 'general', 'custom_order')
        );
    return $endpoints;
}

function add_tab_to_vendor_dashboard($nav) {
$nav['custom_order_wcmp'] = array(
'label' => __('Custom Order', 'dc-woocommerce-multi-vendor'), // menu label
'url' => wcmp_get_vendor_dashboard_endpoint_url('custom_order'), // menu url
'capability' => true, // capability if any
'position' => 120, // position of the menu

'link_target' => '_self',
'nav_icon' => 'wcmp-font ico-orders-icon',
'submenu'     => array()
);
return $nav;
}

function custom_order_menu_endpoint_contents(){
 
 ?>
<form method="POST">
<div class="container">
<div class="row">
	<div class="col-md-12">
		<div class="col-md-3">Date</div>
		<div class="col-md-9"><input type="date" name="today_date" class="form-control" >	</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="col-md-3">Customer Name	</div>
		<div class="col-md-9"><input type="text" name="customer_name" class="form-control">	</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="col-md-3">Address	</div>
		<div class="col-md-9"><input type="text" name="address" class="form-control">	</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="col-md-3">Email	</div>
		<div class="col-md-9"><input type="email" name="email" class="form-control">	</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="col-md-3">Mobile </div>
		<div class="col-md-9"><input type="number" step="0.01" name="number" class="form-control">	</div>
	</div>
</div>
<div class="row">
<div class="col-md-12">
	<table class="table">
		<tr>
			<th>
					Item Name
			</th>
			<th>
					Description
			</th>
			<th>
					QTY
			</th>
			<th>
					Rate
			</th>
			<th>
					Amount
			</th>
			<th>
					Tax
			</th>
		</tr>
<tbody class="input_fields_wrap">
		<tr class="">
			<td><button style="background-color:green;display: inline-block;padding: 2px 8px;border-radius: 50%;margin-right: 5px;border: none;width: 22px;" class="add_field_button btn btn-info active">+</button>
					<select name="item[]" class="form-control" style="display: inline-block;width: auto;">
						<option value="">Select Item</option>
						<option value="service_call">Service Call</option>
						<option value="labor">Labor</option>
						<option value="materials">Materials</option>
					</select>
			</td>
			<td>
					<textarea class="description form-control" name="description[]"></textarea>
			</td>
			<td>
					<input type="number" class="qty form-control" name="qty[]">
			</td>
			<td>
					<input type="number" step="0.01" class="rate form-control" name="rate[]">
					<label for="stuff" class="input-icon"><span>&#36;</span>
</label>
			</td>
			<td>
					<input type="number" step="0.01" class="amount form-control" name="amount[]">
					<label for="stuff" class="input-icon"><span>&#36;</span>
			</td>
			<td>
					<select name="tax[]" class="form-control">
						<option value="yes">Yes</option>
						<option value="no">No</option>
					</select>
			</td>
		</tr>
</tbody>
	</table>
</div>
</div>
<div class="row iconinside">
<div class="col-md-12">
	<div class="col-md-6">Subtotal </div>
	<div class="col-md-6"><input type="number" step="0.01" name="subtotal" class="form-control subtotal"><label for="stuff" class="input-icon"><span>&#36;</span>	</div>
</div>
</div>
<div class="row ">
<div class="col-md-12">
	<div class="col-md-6">Enter Tax Rate (%)</div>
	<div class="col-md-6"><input type="number" step="0.01" name="taxrate" value="0" class="form-control taxrate">	</div>
</div>
</div>
<div class="row iconinside">
<div class="col-md-12">
	<div class="col-md-6">Total </div>
	<div class="col-md-6"><input type="number" step="0.01" name="total" class="form-control total"><label for="stuff" class="input-icon"><span>&#36;</span>	</div>
</div>
</div>

<div class="row iconinside">
<div class="col-md-12">
	<div class="col-md-6"></div>
	<div class="col-md-6"><input type="submit" name="submit_custom_order" class="btn btn-primary form-control">	</div>
</div>
</div>
</div>
</form>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
jQuery(document).ready(function() {
var max_fields = 15; //maximum input boxes allowed
var wrapper = jQuery(".input_fields_wrap"); //Fields wrapper
var add_button = jQuery(".add_field_button"); //Add button ID
var x = 1; //initlal text box count
jQuery(add_button).click(function(e){ //on add input button click
e.preventDefault();
if(x < max_fields){ //max input box allowed
x++; //text box increment
jQuery(wrapper).append('<tr class="newrow"><td><div style="cursor:pointer;background-color:red;display: inline-block;padding: 2px 8px;border-radius: 50%;margin-right: 5px;border: none;" class="remove_field btn btn-info">x</div><select name="item[]" class="form-control" style="display: inline-block;width: auto;"><option value="">Select Item</option><option value="service_call">Service Call</option><option value="labor">Labor</option><option value="materials">Materials</option></select></td><td><textarea class="description form-control" name="description[]"></textarea></td><td><input type="number" class="qty form-control" name="qty[]"></td><td><input type="number" class="rate form-control" step="0.01" name="rate[]"><label for="stuff" class="input-icon"><span>&#36;</span></td><td><input type="number" class="amount form-control" step="0.01" name="amount[]"><label for="stuff" class="input-icon"><span>&#36;</span></td><td><select name="tax[]" class="form-control"><option value="yes">Yes</option><option value="no">No</option></select></td></tr>'); //add input box
}
});
jQuery(wrapper).on("click",".remove_field", function(e){
 

	e.preventDefault(); jQuery(this).parent().parent().remove(); x--;

	var grandTotal = Gettotal();

})
});



</script>
<script>
  
  

  (function() {
    "use strict";

    jQuery("table").on("blur", "input", function() {
      var row = jQuery(this).closest("tr");
      var qty = parseFloat(row.find("input:eq(0)").val());
      var price = parseFloat(row.find("input:eq(1)").val());
      var total = qty * price;
	  
	 	total = isNaN(total) ? "" : total;
		total = parseFloat(total).toFixed(2);

      row.find("input:eq(2)").val(total);
	//parseFloat(row.find("input:eq(2)").val(isNaN(total) ? "" : total).toFixed(2)
	var grandTotal = 0;

		// $(".amount").each(function () {
		// 	var stval = parseFloat($(this).val());
		 
		// 	grandTotal += isNaN(stval) ? 0 : stval;
		// });
		var grandTotal = Gettotal();
	


		var qty = jQuery(".taxrate").val();
		var subtotal = jQuery(".subtotal").val();
		var total =parseFloat(subtotal) + (subtotal * qty)/100;
		total = isNaN(total) ? "" : total;
	 	total = parseFloat(total).toFixed(2);
		jQuery(".total").val(total);


	});
  })();
  



  getAlltotal();
 function getAlltotal(){
    jQuery(".taxrate").on("blur",  function() {
		Gettotal();
	  var qty = jQuery(".taxrate").val();
	  console.log('qty_qty',qty);
      var subtotal = jQuery('.subtotal').val();
	  console.log("subtotal!!",subtotal);
	  var total =  parseInt(subtotal) + (subtotal * qty)/100;
	 // var grandTotal = Gettotal();
	
	 total = isNaN(total) ? "" : total;
	 total = parseFloat(total).toFixed(2);

	 console.log('total_total', total);
	  jQuery(".total").val(total);
	});
 }
 
function Gettotal(){
	var grandTotal = 0;
	jQuery(".amount").each(function () {
		var stval = parseFloat($(this).val());
		grandTotal += isNaN(stval) ? 0 : stval;	
	});

	grandTotal = isNaN(grandTotal) ? "" : grandTotal;
	grandTotal = parseFloat(grandTotal).toFixed(2);
		 
	jQuery('.subtotal').val(grandTotal);
	jQuery('.total').val(grandTotal);

	var qty = jQuery(".taxrate").val();
	
	var total =parseInt(grandTotal) + (grandTotal * qty)/100;
	 // var grandTotal = Gettotal();
	 //df gf sgfdgfsdf
	 total = isNaN(total) ? "" : total;
	 total = parseFloat(total).toFixed(2);
	 console.log('total~~',total);
	jQuery(".total").val(total);
	
	 
	return grandTotal;

}

</script>

<script>

	
// jQuery(document).ready(function() {
// 	    function compute() {
//           var a =jQuery('.qty').val();
//           var b = jQuery('.rate').val();
//           var total = a * b;
//           jQuery('.amount').val(total);
//         }

//         jQuery('.qty, .rate').change(compute);
		
// 	  });
</script>
<style>
.row {
    padding: 10px 0;
}
textarea {
    resize: auto !Important;
}
body textarea.form-control {
    height: auto;
    overflow-x: hidden;
}
.input_fields_wrap td{
	position: relative;
}
.iconinside{
	position: relative;
}
.iconinside .input-icon{
    position: absolute;
    left: 25px;
    top:7.5px; 
}
.input_fields_wrap .input-icon{
    position: absolute;
    left: 25px;
    top: 27px; 
}
.iconinside input{
  padding-left: 17px;
}
.input_fields_wrap input{
  padding-left: 12px;
}
	</style>
<?php  
}

//add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
 
function custom_override_checkout_fields( $fields ) {
	
	if(is_user_logged_in()){
			   $user_id = get_current_user_id();
			   $get_user_custom_product 	= 	get_user_meta($user_id,'_custom_product_id', true);
			   $product_exist = 0;
			   if(!WC()->cart->is_empty()){
				   foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					    $product_id = $cart_item['product_id'];
						if($product_id == $get_user_custom_product){
							$product_exist = 1;
							break;
						}
				   }
			   }
			   if($product_exist == 1){
				    unset($fields['billing']['billing_first_name']);
					unset($fields['billing']['billing_last_name']);
					unset($fields['billing']['billing_company']);
					unset($fields['billing']['billing_address_1']);
					unset($fields['billing']['billing_address_2']);
					unset($fields['billing']['billing_city']);
					unset($fields['billing']['billing_postcode']);
					unset($fields['billing']['billing_country']);
					unset($fields['billing']['billing_state']);
					unset($fields['billing']['billing_phone']);
					unset($fields['order']['order_comments']);
					unset($fields['billing']['billing_address_2']);
					unset($fields['billing']['billing_postcode']);
					unset($fields['billing']['billing_company']);
					unset($fields['billing']['billing_last_name']);
					unset($fields['billing']['billing_email']);
					unset($fields['billing']['billing_city']);
			   }
	}
    
    return $fields;
}



add_filter('settings_payment_tab_options', 'avlabs_fee_amount_field');

function avlabs_fee_amount_field($settings_tab_options) {
	
$settings_tab_options['sections'][] = array("title" => __('Custom Application Fee Amount', 'dc-woocommerce-multi-vendor'), // Section one
                    "fields" => array(
                        "app_fee_amount_commission" => array('title' => 'Commission Percentage', 'type' => 'text', 'id' => 'app_fee_amount_commissionn', 'label_for' => 'default_commissionn', 'name' => 'app_fee_amount_commission', 'desc' => _('Please Application Fee Amount For Admin In Stripe Direct Charges.', 'dc-woocommerce-multi-vendor')), // Text
                    )
                );
 
return $settings_tab_options;
}
add_filter('settings_payment_tab_new_input', 'avlabs_settings_payment_tab_new_input',10,2);
function avlabs_settings_payment_tab_new_input($new_input, $input){
	if(isset($input['app_fee_amount_commission'])){
            $new_input['app_fee_amount_commission'] = sanitize_text_field($input['app_fee_amount_commission']);
        }
	return $new_input;
}