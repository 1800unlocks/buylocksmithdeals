<?php

defined('ABSPATH') || exit;

/**
 * Main BuyLockSmithDealsCustomizationAddon Class.
 *
 * @class BuyLockSmithDealsCustomizationAddon
 */
class BuyLockSmithDealsCustomizationFrontEnd {

    protected static $_instance = null;

    /**
     * provide class instance
     * @return type
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Initialize class action and filters.
     */
    public function __construct() {    
            $this->init_hooks();
    }
    
    
    
    public function init_hooks() { 
         // add_action('pre_get_posts', array($this, 'target_main_conditional_product_list'), 100, 1);
         // add_filter( 'woocommerce_related_products', array($this, 'woocommerce_related_products_function'),100,3 );
          
          add_filter( 'woocommerce_product_tabs', array($this, 'blsd_woo_TnC_tab'),50,1 );
          /**********25-10-2019************/
          add_action( 'woocommerce_before_add_to_cart_button', array($this, 'blsd_frontend_before_add_to_cart_btn') );
          add_filter( 'woocommerce_add_cart_item_data', array($this,'blsd_add_custom_data_to_cart_item'), 10, 3 );
          add_filter( 'woocommerce_get_item_data', array($this,'blsd_display_custom_text_cart'), 20, 2 );
          add_action( 'woocommerce_checkout_create_order_line_item', array($this,'blsd_add_custom_data_order_items'), 10, 4 );
          add_action( 'woocommerce_before_calculate_totals', array($this,'blsd_before_calculate_totals'), 10, 1 );
          add_filter( 'woocommerce_booking_single_add_to_cart_text', array($this,'woo_custom_single_add_to_cart_text'),100000 );  // 2.1 +
          
          add_action('wp_head',array($this,'blsd_Checkout_page_css'));
          add_action('wp_footer',array($this,'blsd_checkout_remove_item'));
          add_action( 'woocommerce_review_order_before_payment', array($this,'add_heading_payment') );
           add_action( 'woocommerce_checkout_order_review', array($this,'blsd_woocommerce_checkout_billing'), 15 );
           add_action( 'woocommerce_before_checkout_billing_form', array($this, 'add_service_location'),10,1); 
          //add_filter("wc_stripe_payment_request_supported_types", array($this,'platinum_prize_draws_wc_stripe_payment_request_supported_types'), 10);
          //add_shortcode('stripe_apple_pay',array($this,'show_apple_pay_button'));
           add_action('wp_footer',array($this,'dynamic_sidebar_front'),5);
           // add_filter('azexo_page_title',array($this,'custom_action_after_single_product_title'),10,1 );
            add_action( "get_template_part",array($this,'overwrite_general_title'),100,3 );
            add_filter('wp_nav_menu_items',array($this,'get_vendor_profile_in_menu'), 10, 2);
            add_filter( 'wpseo_breadcrumb_single_link', array($this,'ss_breadcrumb_single_link'), 10, 2 );
    }
    
    function ss_breadcrumb_single_link( $link_output, $link ) {
        $element = '';
        $element = esc_attr( apply_filters( 'wpseo_breadcrumb_single_link_wrapper', $element ) );
        $link_output =  $element;
        //print_r($link);
        $vendor_url=home_url().'/vendor/';
        if (strpos($link['url'],$vendor_url) !== false) {
          $link_output .= ' <a href="' . 

         esc_url( $link['url'] ) . '" class="vendor-breadcrumbs"> <i class="vendor-breadcrumbs-left"></i>' . 

         esc_html( $link['text'] ) . '</a>';
        }
        else{
        if ( isset( $link['url'] ) ) {
            $link_output .= '<a href="' . 

         esc_url( $link['url'] ) . '"class="other-breadcrumbs">' . 

         esc_html( $link['text'] ) . '</a>';
         } 
            
        }
         return $link_output;
    }
    
    function get_vendor_profile_in_menu($items, $args){
            global $WCMp, $post;
            if ( is_user_logged_in() ) {
            $vendor_id = get_current_user_id();
            $vendor = get_wcmp_vendor($vendor_id);
            if($vendor) {
                echo '<style>.myaccount_ourstore{ display: block;}</style>';
                    $user_meta_post_id =  get_user_meta( $vendor_id, '_vendor_profile_image' , true );
                    if($user_meta_post_id){
                            $user_meta_post_id_to_post_content = get_post($user_meta_post_id);
                            $get_user_profile_image_url = $user_meta_post_id_to_post_content->post_content;
                            $items = '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="'.$vendor->permalink.'" class="menu-link"><img style="height: 36px;" src="'.$get_user_profile_image_url.'"></a></li>'.$items;
                    }
                    else{
                            $items = '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="'.$vendor->permalink.'" class="menu-link"><img style="height: 36px;" src="'.$WCMp->plugin_url . 'assets/images/WP-stdavatar.png"></a></li>'.$items;
                    }
                    }
                    else{
                       if(is_product()){
                           $vendor_id= $post->post_author;
                           $vendor = get_wcmp_vendor($vendor_id);
                           if($vendor){
                           $user_meta_post_id =  get_user_meta( $vendor_id, '_vendor_profile_image' , true );
                            if($user_meta_post_id){
                                    $user_meta_post_id_to_post_content = get_post($user_meta_post_id);
                                    $get_user_profile_image_url = $user_meta_post_id_to_post_content->post_content;
                                    $items = '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="'.$vendor->permalink.'" class="menu-link"><img style="height: 36px;" src="'.$get_user_profile_image_url.'"></a></li>'.$items;
                            }
                            else{
                                //$items = '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="'.$vendor->permalink.'" class="menu-link">'.$vendor->user_data->data->display_name.'</a></li>'.$items;
                                   $items = '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="'.$vendor->permalink.'" class="menu-link"><img style="height: 36px;" src="'.$WCMp->plugin_url . 'assets/images/WP-stdavatar.png"></a></li>'.$items;
                            }
                           }
                       } 
                        
                    }
            }
            return $items;
    }
    
    function overwrite_general_title($slug, $name, $templates){
        if(is_product()){
        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR.'/'.$templates[0];
        }
     }
        
    function dynamic_sidebar_front(){
       remove_action('wp_footer', 'azexo_footer'); 
       if ( is_active_sidebar( 'dynamic_footer' ) ) : ?>
        <div class="sidebar new-sidebar">
            <?php dynamic_sidebar( 'dynamic_footer' ); ?>
        </div>
        <?php endif;         
    }
     function add_service_location($checkout){
        global $WCMp;
        $fields = $checkout->get_checkout_fields( 'billing' );
        foreach ( $fields as $key => $field ) {
           $values[$key]= $checkout->get_value( $key );
        }
       // print_r($values);
       echo '<input type="checkbox" class="service_location" id="same_as_service_location" name="same_as_service_location" value="1">Billing Same as Service location';
       echo '<style>.service_location{position: relative !important; opacity: 1 !important; }</style>';
       ?>
        <script>
          
                jQuery('#same_as_service_location').click(function(){
                    if(jQuery('#same_as_service_location').prop('checked') == true){
                        var service_location='checked';
                        
                    }
                    else if(jQuery('#same_as_service_location').prop('checked') == false){
                         var service_location='unchecked';
                    }
                    jQuery.ajax({
                         url: '<?php echo add_query_arg( 'action', 'blsd_location_same_service', $WCMp->ajax_url() ); ?>',
                         type: "post",
                         data: {status:service_location},
                         success: function(resultData) {
                            var array= JSON.parse(resultData);
                            console.log(array);
                            jQuery.each(array, function( index, value ) {
                                if(index == 'billing_country'){
                                    jQuery('#'+index).val(value).trigger('change');
                                }
                                else if(index == 'billing_state'){
                                    setTimeout(function(){ jQuery('#'+index).val(value).trigger('change');   }, 3000);
                                }
                                else{
                                    jQuery('#'+index).val(value);
                                }
                              });
                           
                         }
                    });
                });
                </script>
       <?php
    }
    
    function blsd_woocommerce_checkout_billing(){
         if ( WC()->checkout()->get_checkout_fields() ) : 

		 do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="col2-set" id="customer_details">
			<div class="col-1">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="col-2">
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); 
                endif; 
    }
    function show_apple_pay_button(){
        ?>
        <script src="https://js.stripe.com/v3/"></script>
        <div id="payment-request-button">        
        </div>
        <script type="text/javascript">
var paymentRequest = stripe.paymentRequest({
  country: 'US',
  currency: 'usd',
  total: {
    label: 'Demo total',
    amount: 1000,
  },
  requestPayerName: true,
  requestPayerEmail: true,
});
var elements = stripe.elements();
var prButton = elements.create('paymentRequestButton', {
  paymentRequest: paymentRequest,
});

// Check the availability of the Payment Request API first.
paymentRequest.canMakePayment().then(function(result) {
  if (result) {
    prButton.mount('#payment-request-button');
  } else {
    document.getElementById('payment-request-button').style.display = 'none';
  }
});

paymentRequest.on('token', function(ev) {
  // Send the token to your server to charge it!
  fetch('/charges', {
    method: 'POST',
    body: JSON.stringify({token: ev.token.id}),
    headers: {'content-type': 'application/json'},
  })
  .then(function(response) {
    if (response.ok) {
      // Report to the browser that the payment was successful, prompting
      // it to close the browser payment interface.
      ev.complete('success');
    } else {
      // Report to the browser that the payment failed, prompting it to
      // re-show the payment interface, or show an error message and close
      // the payment interface.
      ev.complete('fail');
    }
  });
});

var paymentRequest = stripe.paymentRequest({
  country: 'US',
  currency: 'usd',
  total: {
    label: 'Demo total',
    amount: 1000,
  },

  requestShipping: true,
  // `shippingOptions` is optional at this point:
  shippingOptions: [
    // The first shipping option in this list appears as the default
    // option in the browser payment interface.
    {
      id: 'free-shipping',
      label: 'Free shipping',
      detail: 'Arrives in 5 to 7 days',
      amount: 0,
    },
  ],
});

paymentRequest.on('shippingaddresschange', function(ev) {
  if (ev.shippingAddress.country !== 'US') {
    ev.updateWith({status: 'invalid_shipping_address'});
  } else {
    // Perform server-side request to fetch shipping options
    fetch('/calculateShipping', {
      data: JSON.stringify({
        shippingAddress: ev.shippingAddress
      })
    }).then(function(response) {
      return response.json();
    }).then(function(result) {
      ev.updateWith({
        status: 'success',
        shippingOptions: result.supportedShippingOptions,
      });
    });
  }
});

elements.create('paymentRequestButton', {
  paymentRequest: paymentRequest,
  style: {
    paymentRequestButton: {
      type: 'default',
      // One of 'default', 'book', 'buy', or 'donate'
      // Defaults to 'default'

      theme: 'dark',
      // One of 'dark', 'light', or 'light-outline'
      // Defaults to 'dark'

      height: '64px'
      // Defaults to '40px'. The width is always '100%'.
    },
    },
});
   </script>
        <?php
        
    }
 //    function platinum_prize_draws_wc_stripe_payment_request_supported_types($supported_types) {
	// return $supported_types;
 //    }
    
    function add_heading_payment(){
       echo '<h3>Payment</h3>';
    }
    
    
    function woo_custom_single_add_to_cart_text() {

        return __( 'Claim This Deal', 'woocommerce' );

    }
    function target_main_conditional_product_list($query) {
            global $pagenow, $woocommerce_loop;
            if (is_shop() || (is_product() && $woocommerce_loop['name'] == 'related' )){
                $query_vars = $query->query_vars;
                    $query->set('meta_query', array(
                        array(
                            'key' => '_vendor_product_parent',
                            'compare' => 'NOT EXISTS'
                        )
                    ));
            }
        return $query;
        }
    function woocommerce_related_products_function( $related_posts, $product_id, $args ){
       
       // print_r($related_posts); exit;
        global $wpdb;
        $exclude = [];
        $query = "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND ID in (select post_id from wp_postmeta where meta_key='_vendor_product_parent')";
                    $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
        
        if(count($results)>0){
            foreach($results as $result){
                $exclude[] = $result['ID'];
            }
        }
        $related_posts = array_diff($related_posts, $exclude);
    shuffle( $related_posts );

  return array_slice( $related_posts, 0, $limit );
        
    }
    function blsd_woo_TnC_tab( $tabs ) {
    
     global $product;
    if(!empty($tabs)){
         foreach($tabs as $key=>$tab){
             if($key=='description'){
                 $tabs[$key]['title']='deal Details';
                 $tabs[$key]['priority']=20;
             }
             if($key=='vendor'){
                 $tabs[$key]['title']='Locksmith Info';
                 $tabs[$key]['priority']=10;
             }
         }
     }
    //get current product ID
    $product_id = $product->get_ID();
    $have_data =  get_post_meta($product_id, 'prod_vendor_TnC',true);
    
    // Adds the new tab
    if($have_data!=''){
        $tabs['TnC_tab'] = array(
            'title'     => __( 'Terms and conditions', 'woocommerce' ),
            'priority'  => 50,
            'callback'  => array($this,'woo_TnC_tab_tab_content')
        );
    }
    $tabs['faq_tab'] = array(
		'title'    => __( 'FAQ', 'textdomain' ),
		'callback' => array($this,'blsd_faq_tab_content'),
		'priority' => 50,
	);
    
    return $tabs;
}
    function blsd_faq_tab_content(){
        global $product;
        $product_id = $product->get_ID();
        $product_parent= get_post_meta($product_id,'_vendor_product_parent',true);
        if(!empty($product_parent)){
            $product_parent_id=$product_parent;
        }
        else{
          $product_parent_id=$product_id;  
        }
        $product_car_key = get_post_meta($product_parent_id,'product_car_key',true);
        $product_lock_rekeying = get_post_meta($product_parent_id,'product_lock_rekeying',true);
        
        $default_product_car_cat_id = get_post_meta($product_parent_id,'default_product_car_cat_id',true);
        $default_product_lock_rekeying_cat_id = get_post_meta($product_parent_id,'default_product_lock_rekeying_cat_id',true);
        if($product_car_key == 'yes'){
           echo do_shortcode('[faq cat_id="'.$default_product_car_cat_id.'"]');
        }
        if($product_lock_rekeying == 'yes'){
             echo do_shortcode('[faq cat_id="'.$default_product_lock_rekeying_cat_id.'"]');
        }
        //print_r($categories);
    }
    
    function woo_TnC_tab_tab_content(){
    global $product;
    //get current product ID
    $product_id = $product->get_ID();
    echo get_post_meta($product_id, 'prod_vendor_TnC',true);
}
    
    function blsd_frontend_before_add_to_cart_btn(){
        global $post,$WCMp;
        $post_id= $post->ID;
        $product_parent= get_post_meta($post_id,'_vendor_product_parent',true);
        if(!empty($product_parent)){
            $post_parent_id=$product_parent;
        }
        else{
          $post_parent_id=$post_id;  
        }
        $author_id=$post->post_author; 
        $user_meta=get_userdata($author_id);
        $user_roles=$user_meta->roles;
        if(!in_array('dc_vendor',$user_roles)){
            $phone = get_user_meta($author_id,'billing_phone',true);
            $address1 = get_user_meta($author_id,'billing_address_1',true);
            $address2 = get_user_meta($author_id,'billing_address_2',true);
            $city = get_user_meta($author_id,'billing_city',true);
            $state = get_user_meta($author_id,'billing_state',true);
            $postcode = get_user_meta($author_id,'billing_postcode',true);
        }
        else{
        $phone = get_user_meta($author_id,'_vendor_phone',true);
        $address1 = get_user_meta($author_id,'_vendor_address_1',true);
        $address2 = get_user_meta($author_id,'_vendor_address_2',true);
        $city = get_user_meta($author_id,'_vendor_city',true);
        $state = get_user_meta($author_id,'_vendor_state',true);
        $postcode = get_user_meta($author_id,'_vendor_postcode',true);
        }
        $vendor_address=$address1.' '.$address2.' '.$city.' '.$state.' '.$postcode;
        $coordinates=self::get_coordinates_from_address($vendor_address);
        $vendor_latitude=explode('/',$coordinates)[0];
        $vendor_longitude=explode('/',$coordinates)[1];
        $currenct_symbol=get_woocommerce_currency_symbol();
                      
        
        $show_unserviceable_cars = get_post_meta($post_parent_id,'show_unserviceable_cars',true);
        $where_need_service = get_post_meta($post_parent_id,'where_need_service',true);
        $where_car_located = get_post_meta($post_parent_id,'where_car_located',true);
        $have_any_working_keys = get_post_meta($post_parent_id,'have_any_working_keys',true);
        $when_start_car = get_post_meta($post_parent_id,'when_start_car',true);
        $is_car_currently_locked = get_post_meta($post_parent_id,'is_car_currently_locked',true);
        $will_owner_authorize_service = get_post_meta($post_parent_id,'will_owner_authorize_service',true);
        $ask_property_type = get_post_meta($post_parent_id,'ask_property_type',true);
        $where_property_located = get_post_meta($post_parent_id,'where_property_located',true);
        $quantity_of_locks = get_post_meta($post_parent_id,'quantity_of_locks',true); 
        $default_miles=  get_post_meta($post_id, 'default_miles', true);
        $extra_permile_price= get_post_meta($post_id, 'extra_permile_price', true);
        $maximum_miles= get_post_meta($post_id, 'maximum_miles', true);
        echo '<input type="hidden" name="amount" id="amount" value="">';
        echo '<input type="hidden" name="booking_price" id="booking_price" value="">';
        echo '<input type="hidden" name="booking_price_with_tax" id="booking_price_with_tax" value="">';
        if($where_car_located == 'yes'){
         echo '<div class="options_show select_address disabled"><label> Where is the car located? </label><button  type="button" name="select_address" id="select_address" class="btn_class">Select Address</button></div>';   
        }
        if($show_unserviceable_cars == 'yes'){
            $vendor_unserviceable_cars= get_post_meta($post_id,'unserviceable_cars',true);
            
            $all_cars= BuyLockSmithDealsCustomizationAddon::get_all_cars_frontend($vendor_unserviceable_cars,'maker','');
            $allcar_name=[];
            $optionss=[];
             $all=0;
            $option_maker='<option value="">Car</option>';
            $option_model='<option value="">Model</option>';
            $option_year='<option value="">Year</option>';
            if(!empty($all_cars)){
                $car_maker='';
                foreach($all_cars as $cars){
                     $option_maker .='<option value="'.$cars['maker'].'">'.$cars['maker'].'</option>'; 
                    }
                }
                echo '<div class="options_show" id="select_car"><label> Choose your Car </label><div id="loading" class="disabled" ><i class="fa fa-refresh fa-spin" style="font-size:24px"></i></div>'
                . '<div class="select_service_car"> <select name="serviceable_car_maker[]" class="serviceable_car_maker">'.$option_maker.'</select>'
                        . '<select name="serviceable_car_model[]" class="serviceable_car_model">'.$option_model.'</select>'
                        . '<select name="serviceable_car_year[]" class="serviceable_car_year">'.$option_year.'</select> '
                        //. '<div class="add_more">+</div> '
                        . '<span class="show_selected_address disabled" ></span>'
                        . '<div class="select_car_address disabled"><button type="button" name="vehical_location" class="btn_class" >Set Vehicle Service Location</button>'
                        . '<input type="hidden" name="my_location_address[]" class="my_location_address" value="">'
                        . '<input type="hidden" name="my_location_latitude[]" class="my_location_latitude" value="">'
                        . '<input type="hidden" name="my_location_longitude[]" class="my_location_longitude" value="">'
                        . '<input type="hidden" name="extra_amount[]" class="extra_amount" value="">'
                        . '<input type="hidden" name="total_miles[]" class="total_miles" value="">'
                        . '</div> '
                        . '</div> </div><span class="chat_support">*Any unserviceable vehicles are not listed. Start a live chat with us for more help.</span> ';
            }
        
        $service_location=0;
        if($where_need_service == 'yes'){
            $product_addons= get_post_meta($post_id,'_product_addons',true);
               if(!empty($product_addons)){
                $service_location=1;
                 //echo '<input type="hidden" name="my_location_address" id="my_location_address" value="">';
                    // echo '<input type="hidden" name="my_location_latitude" id="my_location_latitude" value="">';
                    // echo '<input type="hidden" name="my_location_longitude" id="my_location_longitude" value="">';
                }
             else{
              echo "<script>jQuery('.wc-pao-addon-where-do-you-need-service').addClass('disabled');</script>";  
            }
        }
         echo '<div class="modal" id="map_display">'
                . '<div class="modal-content">'
                . '<span class="close-button">&times;</span>'
                . '<div id="map_content">'
                . '<div class="pac-card" id="pac-card">
                    
                    <div id="pac-container" style="padding:5px;">
                      <input id="pac-input" name="pac-input" type="text"
                          placeholder="Enter a location" >
                    </div>
                  </div>
                  <div id="map" style="height:300px; display:block;"></div>
                  <div><button type="button" name="done" id="done" class="btn_class" style="float:right;" >Done</button></div>
                  <div id="infowindow-content">
                    <img src="" width="16" height="16" id="place-icon">
                    <span id="place-name"  class="title"></span><br>
                    <span id="place-address"></span>
                  </div>'
                        . '</div>'
                        . '</div></div>';
        
        if($have_any_working_keys == 'yes'){
            echo '<div class="options_show"><label class="working-keys"> Do you have any working keys? </label><input type="radio" name="working_keys" value="yes" checked="checked"> Yes <input type="radio" name="working_keys" value="no"> No </div>';   
        }
        if($when_start_car =='yes'){
          echo '<div class="options_show"><label> How do you start your car? </label>'
            . '<select name="when_start_car" id="when_start_car">'
                  . '<option value="" >Select</option>'
                  . '<option value="Turn key ignition" >Turn key ignition</option>'
                  . '<option value="Prox twist (twist nub)" >Prox twist (twist nub)</option>'
                  . '<option value="Push to start" >Push to start</option>'
                  . '</select> </div>';   
           
        }
        if($is_car_currently_locked == 'yes'){
             echo '<div class="options_show"><label class="working-keys"> Is the car currently locked? </label><input type="radio" name="car_currently_locked" value="yes" checked="checked"> Yes <input type="radio" name="car_currently_locked" value="no"> No </div>';   
       
        }
        if($will_owner_authorize_service == 'yes'){
            echo '<div class="options_show"><label class="working-keys"> Will the owner be able to authorize service? </label><input type="radio" name="will_owner_authorize_service" class="owner_authorize_service" value="yes" checked="checked"> Yes <input type="radio" name="will_owner_authorize_service" class="owner_authorize_service" value="no"> No </div>';   
       }
        if($ask_property_type == 'yes'){
             echo '<div class="options_show"><label> Type of property </label>'
            . '<select name="property_type" id="property_type">'
                  . '<option value="" >Select</option>'
                  . '<option value="Home" >Home</option>'
                  . '<option value="Business" >Business</option>'
                  . '<option value="Rental Property" >Rental Property</option>'
                  . '</select> </div>';   
        }
        
        if($where_property_located == 'yes'){
             echo '<div class="options_show"><label> Where is the property located? </label><button  type="button" name="select_property_address" id="select_property_address" class="btn_class">Select Address</button></div>';   
        
                echo '<input type="hidden" name="property_address" id="property_address" value="">';
                echo '<input type="hidden" name="property_latitude" id="property_latitude" value="">';
                echo '<input type="hidden" name="property_longitude" id="property_longitude" value="">';
        }
        if($quantity_of_locks =='yes'){
            $cylinders_included= get_post_meta($post_id,'cylinders_included',true);
            $option='';
            for($i=1;$i<=99;$i++){
               $option .= '<option value="'.$i.'" >'.$i.'</option>';
            }
             echo '<div class="options_show"><label> Quantity of locks to rekey </label>'
            . '<select name="quantity_of_locks_to_rekey" id="quantity_of_locks_to_rekey">'
                  .$option
                  . '</select> <span>'
                     . 'This deal includes up to '.$cylinders_included.' cylinders. If you have more that is OK, the locksmith will provide pricing details on site before the job starts.'
                     . '</span>'
                     . '</div>';   
             
        }
        
        echo '<div class="options_show">Total:<span id="show_total_amount_with_tax"><div class="currency_total">'.get_woocommerce_currency_symbol().'</div><div class="price_total">0.00</div><small>(approx)</small></span></div>';
        
        ?>
        <script>
            
            var address_parent_div;
            var address_selected=0;
           // jQuery(document).on("blur",".hasDatepicker,ul.block-picker .block", function(e) { console.log('aa') });
            jQuery('.wc-bookings-booking-form').on('change', 'input, select:not("#wc-bookings-form-start-time, #wc-bookings-form-end-time")', function (e) {
		 setTimeout(function(){ 
                       console.log('aaaaa');
                       if(jQuery('.wc-bookings-booking-cost').css('display') == 'none'){
                           var booking_price= 0;  
                       }
                       else{
                          var booking_price= parseFloat(jQuery('.wc-bookings-booking-cost').attr('data-raw-price'));  
                       }
                    if(booking_price != 0){
                    jQuery('#booking_price').val(booking_price);
                    
                    var service_location_val=jQuery('.wc-pao-addon-select').val();;
                    var service_location_price=jQuery('.wc-pao-addon-select').find(':selected').attr('data-price');
                    if(service_location_price > 0 && service_location_val != ''){
                        var amount=parseFloat(jQuery('#amount').val());
                         var booking_price_with_tax=parseFloat(jQuery('#booking_price_with_tax').val());
                        if(amount == ''){
                        amount=0;    
                        }
                        var total = 0;
                        service_location_price=parseFloat(service_location_price);
                        var total_amount=parseFloat(amount+total);
                        if(booking_price_with_tax == ''){
                            var total_service_booking=parseFloat(amount+total+booking_price);
                        }
                        else{
                            var total_service_booking=parseFloat(booking_price_with_tax+booking_price);
                        }
                        var html= '<span class="currency"><?php echo $currenct_symbol; ?></span>'+total_amount.toFixed(2);
                            jQuery('.product-addon-totals .wc-pao-col2 .amount').html('');
                            jQuery('.product-addon-totals .wc-pao-col2 .amount').html(html);
                        var subtotal_html= '<p class="price">Subtotal <span class="amount"><span class="currency"><?php echo $currenct_symbol; ?></span>'+total_service_booking.toFixed(2)+'</span></p>';
                        jQuery('.wc-pao-subtotal-line').html(''); 
                        jQuery('.wc-pao-subtotal-line').html(subtotal_html); 
                        jQuery('#show_total_amount_with_tax .price_total').html(total_service_booking.toFixed(2));
                    }
                } 
               }, 3000);
                    
            });
            
            jQuery(document).ready(function(){
                var show_alert;
                
                jQuery('.single_add_to_cart_button').click(function(e){
                    e.preventDefault();
                    //var serviceable_car_maker = jQuery('#serviceable_car_maker').val(); 
                    // var serviceable_car_model = jQuery('#serviceable_car_model').val(); 
                    //var serviceable_car_year = jQuery('#serviceable_car_year').val(); 
                    //var latitude=jQuery('#my_location_latitude').val();
                    // var longitude=jQuery('#my_location_longitude').val();
                    //var address=jQuery('#my_location_address').val();
                   var maker_val=1;
                   var model_val=1;
                   var year_val=1;
                   var address_val=1;
                   var latitude_val=1;
                   var longitude_val=1;
                   var redirect_flag=0;
                   var total_miles_val=1;
                    var serviceable_car_maker = jQuery('select[name="serviceable_car_maker[]"]').map(function () {
                            if(this.value == ''){
                                maker_val=0;
                            }
                            return this.value; // $(this).val()
                    }).get();
                    var serviceable_car_model = jQuery('select[name="serviceable_car_model[]"]').map(function () {
                            if(this.value == ''){
                                model_val=0;
                            }
                            return this.value; // $(this).val()
                        }).get();
                    var serviceable_car_year = jQuery('select[name="serviceable_car_year[]"]').map(function () {
                            if(this.value == ''){
                                year_val=0;
                            }
                            return this.value; // $(this).val()
                        }).get(); 
                     var address = jQuery('input[name="my_location_address[]"]').map(function () {
                            if(this.value == ''){
                                address_val=0;
                            }
                            return this.value; // $(this).val()
                        }).get(); 
                        
                      var latitude = jQuery('input[name="my_location_latitude[]"]').map(function () {
                            if(this.value == ''){
                                latitude_val=0;
                            }
                            return this.value; // $(this).val()
                        }).get(); 
                       var longitude = jQuery('input[name="my_location_longitude[]"]').map(function () {
                            if(this.value == ''){
                                longitude_val=0;
                            }
                            return this.value; // $(this).val()
                        }).get(); 
                       var totalmiles = jQuery('input[name="total_miles[]"]').map(function () {
                            if(this.value >0){
                                total_miles_val=0;
                            }
                            return this.value; // $(this).val()
                        }).get();  
                        
                    if(total_miles_val == 0){
                      alert("This address is outside of our service area. Please start a live chat for more help.");
                      redirect_flag=1;
                    }
                    
                    if(maker_val == 0 || model_val == 0 || year_val == 0 ){
                     alert('Please select your car type');
                     redirect_flag=1;
                    }
                    var service_location_val=jQuery('.wc-pao-addon-select').val();
                    var service_location_price=jQuery('.wc-pao-addon-select').find(':selected').attr('data-price');
                    if(service_location_price > 0 && service_location_val != ''){
                    
                        if(latitude_val ==0 || longitude_val ==0 || address_val == 0){
                           alert('Please Select your car location.');
                           redirect_flag=1;
                        }
                   
                    }   
                    
                    var authorized_service=jQuery("input[name='will_owner_authorize_service']:checked").val();
                    if(authorized_service == 'no'){
                        alert('Proof of ownership must be provided to the locksmith at the time of service. If this is possible, please select yes to continue. If not, please call <?php echo $phone; ?>');
                        redirect_flag=1;
                    }
                    var when_start_car=jQuery('#when_start_car').val();
                     if(when_start_car === ''){
                     alert('Please select when you start your car.');
                     redirect_flag=1;
                    }
                    var property_type=jQuery('#property_type').val();
                     if(property_type === ''){
                     alert('Please select property type.');
                    redirect_flag=1;
                    }
                    var that=jQuery(this);
                     jQuery.ajax({
                        url: '<?php echo add_query_arg( 'action', 'blsd_check_cart_vendor_product', $WCMp->ajax_url() ); ?>',
                        type: "post",
                        data: {post_id:'<?php echo $post_id; ?>'},
                        success: function(resultData) {
                            if(resultData == 'failed'){
                                alert('Please complete the checkout of your deal before purchasing deals from another locksmith.');
                                redirect_flag=1;
                            }
                            if(redirect_flag == 0){
                                 var form_class=that.parent('form').attr('class');
                                 jQuery('.'+form_class).submit();
                            }
                        }
                    });
                   
                    
                });
               

                jQuery('#serviceable_car').click(function(){
                   clearTimeout(show_alert);
               });
               jQuery('.serviceable_car_maker').focusout(function () {
                   var service_car=jQuery(this).val();
                   if(service_car === ''){
                        var show_alert= setTimeout(function(){ 
                           // alert("Don't see your Car Type? Please call <?php echo $phone; ?>"); 
                         }, 10000);
                    }
               });
               jQuery('.add_more').click(function(){
              var service_location_val= jQuery('.wc-pao-addon-select').val();
              var service_location_price=jQuery('.wc-pao-addon-select').find(':selected').attr('data-price');
                
                var html='<div class="select_service_car"> <select class="serviceable_car_maker" name="serviceable_car_maker[]" ><?php echo $option_maker; ?></select>';
                    html= html+'<select class="serviceable_car_model" name="serviceable_car_model[]" ><?php echo $option_model; ?></select>';
                    html= html+'<select class="serviceable_car_year" name="serviceable_car_year[]"><?php echo $option_year; ?></select><div class="remove_row">-</div>';  
					html= html+'<span class="show_selected_address disabled" ></span>';
                    html= html+'<div class="select_car_address disabled"><button type="button" name="vehical_location" class="btn_class" >Set Vehicle Service Location</button>';
                    html= html+'<input type="hidden" name="my_location_address[]" class="my_location_address" value="">';
                    html= html+'<input type="hidden" name="my_location_latitude[]" class="my_location_latitude" value="">';
                    html= html+'<input type="hidden" name="my_location_longitude[]" class="my_location_longitude" value="">';
                    html= html+'<input type="hidden" name="extra_amount[]" class="extra_amount" value="">';
                    html= html+'<input type="hidden" name="total_miles[]" class="total_miles" value="">';
                    html= html+'</div> </div>';    
                    jQuery('#select_car').append(html);
                    if(service_location_price > 0 && service_location_val != ''){
                        jQuery('.select_car_address').removeClass('disabled');   
                    }
               });
                jQuery(document).on('click','.remove_row',function(){
                    jQuery(this).parent().remove();
                });
               jQuery(document).on('change','.serviceable_car_maker',function () {
                    var serviceable_car_maker=jQuery(this).val();
                    var maker=jQuery(this);
                    jQuery('#loading').removeClass('disabled');
                    jQuery.ajax({
                        url: '<?php echo add_query_arg( 'action', 'blsd_get_car_model_year', $WCMp->ajax_url() ); ?>',
                        type: "post",
                        data: {maker:serviceable_car_maker,post_id:'<?php echo $post_id; ?>'},
                        success: function(resultData) {
                            maker.parent().children('.serviceable_car_model').html(resultData);
                            jQuery('#loading').addClass('disabled');
                         }
                    });
                    
               });
               
               jQuery(document).on('change','.serviceable_car_model',function () {
                    var serviceable_car_maker=jQuery(this).parent().children('.serviceable_car_maker').val();
                    var serviceable_car_model=jQuery(this).val();
                    var model=jQuery(this);
                    jQuery('#loading').removeClass('disabled');
                    jQuery.ajax({
                        url: '<?php echo add_query_arg( 'action', 'blsd_get_car_model_year', $WCMp->ajax_url() ); ?>',
                        type: "post",
                        data: {maker:serviceable_car_maker,model:serviceable_car_model,post_id:'<?php echo $post_id; ?>'},
                        success: function(resultData) {
                           model.parent().children('.serviceable_car_year').html(resultData); 
                           jQuery('#loading').addClass('disabled');
                         }
                    });
                    
               });
               
               jQuery('.wc-pao-addon-select').change(function(){
               
                var service_location_val=jQuery(this).val();
                var service_location_price=jQuery(this).find(':selected').attr('data-price');
                if(service_location_price > 0 && service_location_val != ''){
                    var amount=jQuery('#amount').val();
                    var booking_price=jQuery('#booking_price').val();
                    var booking_price_with_tax=parseFloat(jQuery('#booking_price_with_tax').val());
                    if(amount == ''){
                    amount=0;    
                    }
                    if(booking_price == ''){
                     booking_price= 0;    
                    }   
                    booking_price=parseFloat(booking_price);
                    var extra_amount = jQuery('input[name="extra_amount[]"]').map(function () {
                            if(this.value != ''){
                               return this.value;
                            }
                        }).get();
                    var total = 0;
                    for (var i = 0; i < extra_amount.length; i++) {
                        var extra=parseFloat(extra_amount[i]);
                        total = parseFloat(total + extra);
                    }
                    service_location_price=parseFloat(service_location_price);
                    console.log(amount,total,service_location_price);
                    var total_amount=parseFloat(amount+total+service_location_price);
                    if(booking_price_with_tax == ''){
                        var total_service_booking=parseFloat(amount+total+service_location_price+booking_price);
                    }
                    else{
                      var total_service_booking=parseFloat(booking_price_with_tax+booking_price);   
                    }
            
                        
                    jQuery('#amount').val(total_amount);
                    setTimeout(function(){ 
                        var html= '<span class="currency"><?php echo $currenct_symbol; ?></span>'+total_amount.toFixed(2);
                        jQuery('.product-addon-totals .wc-pao-col2 .amount').html('');
                        jQuery('.product-addon-totals .wc-pao-col2 .amount').html(html);
                        if(booking_price != 0){
                            var subtotal_html= '<p class="price">Subtotal <span class="amount"><span class="currency"><?php echo $currenct_symbol; ?></span>'+total_service_booking.toFixed(2)+'</span></p>';
                            jQuery('.wc-pao-subtotal-line').html(''); 
                            jQuery('.wc-pao-subtotal-line').html(subtotal_html);
                           }
                            jQuery('#show_total_amount_with_tax .price_total').html(total_service_booking.toFixed(2));
                        
                    }, 3000);
                   //jQuery('#map_display').addClass('show-modal');
                   jQuery('#map_display').addClass('car');
                   jQuery('#map_display').removeClass('property');  
                   //jQuery('#map_display').removeClass('disabled');  
                   jQuery('.select_car_address').removeClass('disabled');  
                }
                else{
                    jQuery('#amount').val('');
                    var total_service_booking=0.00;
                    jQuery('#show_total_amount_with_tax .price_total').html(total_service_booking.toFixed(2));
                    //jQuery('#map_display').addClass('disabled'); 
                 // jQuery('#map_display').removeClass('show-modal');
                  jQuery('#map_display').removeClass('car');
                  jQuery('#map_display').removeClass('property');
                  jQuery('.select_car_address').addClass('disabled');  
                } 
               }); 
               
                jQuery(document).on('click','.select_car_address',function(){
                    address_parent_div=jQuery(this);
                    jQuery('#map_display').addClass('show-modal');
                    jQuery('#map_display').addClass('car');
                    jQuery('#map_display').removeClass('property');
                    jQuery('#map_display').removeClass('disabled');
                    jQuery('#pac-input').val('');
                   
               });
               jQuery('#select_property_address').click(function(){
                    jQuery('#map_display').addClass('show-modal');
                    jQuery('#map_display').addClass('property');
                    jQuery('#map_display').removeClass('car');
                    jQuery('#map_display').removeClass('disabled');
                    jQuery('#pac-input').val('');
                   
               });
               
               jQuery('.close-button').on('click',function(){
                   jQuery('#map_display').addClass('disabled'); 
               });
               jQuery('#done').on('click',function(){
                    var car_latitude=address_parent_div.parent().children('.select_car_address').children('.my_location_latitude').val();
                    var car_longitude=address_parent_div.parent().children('.select_car_address').children('.my_location_longitude').val();
                    var address=GetAddress(car_latitude,car_longitude);
                    var extra_price=parseFloat(get_extra_price(car_latitude,car_longitude));
                     
                    address_parent_div.parent().children('.select_car_address').children('.extra_amount').val(extra_price);
                    var amount=parseFloat(jQuery('#amount').val());
                    var booking_price=jQuery('#booking_price').val();
                    var booking_price_with_tax=jQuery('#booking_price_with_tax').val();
                    if(booking_price == ''){
                        booking_price=0;
                    }
                    booking_price=parseFloat(booking_price);
                    jQuery('#amount').val(amount+extra_price);
                    var total_amount= amount+extra_price;
                    if(booking_price_with_tax == ''){
                    var total_service_booking= amount+extra_price+booking_price;
                    }
                    else{
                       var total_service_booking= booking_price_with_tax+booking_price; 
                    }
                    jQuery('#map_display').addClass('disabled');
                    setTimeout(function(){ 
                        var html= '<span class="currency"><?php echo $currenct_symbol; ?></span>'+total_amount.toFixed(2);
                        jQuery('.product-addon-totals .wc-pao-col2 .amount').html(html);
                        //jQuery('.wc-pao-addon-select').find(':selected').attr('data-price',total_amount.toFixed(2));
                        //jQuery('.wc-pao-addon-select').find(':selected').attr('data-raw-price',total_amount.toFixed(2));
                        if(booking_price != 0){
                            var subtotal_html= '<p class="price">Subtotal <span class="amount"><span class="currency"><?php echo $currenct_symbol; ?></span>'+total_service_booking.toFixed(2)+'</span></p>';
                            jQuery('.wc-pao-subtotal-line').html(subtotal_html); 
                            jQuery('#show_total_amount_with_tax .price_total').html(total_service_booking.toFixed(2));
                        }
                    }, 3000);
                     jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo add_query_arg( 'action', 'calculate_product_tax', $WCMp->ajax_url() ); ?>',
                        data: {
                            amount: total_service_booking,
                            latitude:car_latitude,
                            longitude:car_longitude
                            },
                        success: function(response) {
                            var array= JSON.parse(response);
                            jQuery('#booking_price_with_tax').val(array.total_amount);
                            jQuery('#show_total_amount_with_tax .price_total').html(parseFloat(array.total_amount).toFixed(2));

                        }
                    });
               });
               jQuery('.owner_authorize_service').click(function(){
                  var authorized_service=jQuery(this).val();
                  if(authorized_service == 'no'){
                      alert('Proof of ownership must be provided to the locksmith at the time of service. If this is possible, please select yes to continue. If not, please call <?php echo $phone; ?>');
                  }
               });
               });
               function GetAddress(latitude,longitude) {
                    var address='';
                    var lat = parseFloat(latitude);
                    var lng = parseFloat(longitude);
                    var latlng = new google.maps.LatLng(lat, lng);
                    var  geocoder = new google.maps.Geocoder();
                     geocoder.geocode({ 'latLng': latlng }, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                
                                console.log('function:',address);
                                if(address_selected==1){
                                    address= results[1].formatted_address;
                                }
                                else{
                                    address=address_parent_div.parent().children('.select_car_address').children('.my_location_address').val();
                                }
                                 address_parent_div.parent().children('.show_selected_address').removeClass('disabled');
                                 address_parent_div.parent().children('.show_selected_address').html(address);    
                               }
                        }
                    });
                    
               }
               function initMap() {
                   address_selected=0;
                    var map = new google.maps.Map(document.getElementById('map'), {
                      center: {lat: -33.8688, lng: 151.2195},
                      zoom: 13
                    });
                    var input = document.getElementById('pac-input');
                    var autocomplete = new google.maps.places.Autocomplete(input);
                    var infowindow = new google.maps.InfoWindow();
                    var infowindowContent = document.getElementById('infowindow-content');
                    
                    infowindow.setContent(infowindowContent);
                    
                    
                  
                    var marker = new google.maps.Marker({
                      map: map,
                      draggable: true,
                      anchorPoint: new google.maps.Point(0, -29),
                      animation: google.maps.Animation.DROP,
                      
                    });

                    autocomplete.addListener('place_changed', function() {
                        address_selected=0;
                      infowindow.close();
                      marker.setVisible(false);
                      var place = autocomplete.getPlace();
                      if (!place.geometry) {
                        window.alert("No details available for input: '" + place.name + "'");
                        return;
                      }
                        
                       
                      // If the place has a geometry, then present it on a map.
                      if (place.geometry.viewport) {
                        map.fitBounds(place.geometry.viewport);
                      } else {
                        map.setCenter(place.geometry.location);
                        map.setZoom(17);  // Why 17? Because it looks good.
                      }
                      marker.setPosition(place.geometry.location);
                      marker.setVisible(true);

                      var address = '';
                      console.log(place);
                      if (place.address_components) {
                        address = [
                          (place.address_components[0] && place.address_components[0].short_name || ''),
                          (place.address_components[1] && place.address_components[1].short_name || ''),
                          (place.address_components[2] && place.address_components[2].short_name || '')
                        ].join(' ');
                       var address_show=place.name+' '+address;
                      }
                      
                      var element = document.querySelector("#map_display");

                        if(element.classList.contains("property")){
                            jQuery('#property_latitude').val(place.geometry.location.lat());
                            jQuery('#property_longitude').val(place.geometry.location.lng());
                            jQuery('property_address').val(address_show);
                        }
                        else if(element.classList.contains("car")){
                            address_parent_div.parent().children('.select_car_address').children('.my_location_latitude').val(place.geometry.location.lat());
                            address_parent_div.parent().children('.select_car_address').children('.my_location_longitude').val(place.geometry.location.lng());
                            address_parent_div.parent().children('.select_car_address').children('.my_location_address').val(address_show);
                           // jQuery('#my_location_latitude').val(place.geometry.location.lat());
                           // jQuery('#my_location_longitude').val(place.geometry.location.lng());
                            // jQuery('#my_location_address').val(address);
                        }
                      
                     
                      
                      infowindowContent.children['place-icon'].src = place.icon;
                      infowindowContent.children['place-name'].textContent = place.name;
                      infowindowContent.children['place-address'].textContent = address;
                      infowindow.open(map, marker);
                  });
                  
                    google.maps.event.addListener(marker, 'dragend',
                        function(marker) {
                            address_selected=1;
                           var latLng = marker.latLng;
                          currentLatitude = latLng.lat();
                          currentLongitude = latLng.lng();
                          infowindowContent.children['place-icon'].src = '';
                          infowindowContent.children['place-name'].textContent = '';
                          infowindowContent.children['place-address'].textContent = currentLatitude.toFixed(4) + ','+currentLongitude.toFixed(4);
                          //infowindow.open(map, marker);
                         // infowindow.setContent(results[0].formatted_address);
                          //infowindow.open(map, marker);
                          var element = document.querySelector("#map_display");

                            if(element.classList.contains("property")){
                                jQuery('#property_latitude').val(currentLatitude);
                                jQuery('#property_longitude').val(currentLongitude);
                            }
                            else if(element.classList.contains("car")){
                                address_parent_div.parent().children('.select_car_address').children('.my_location_latitude').val(currentLatitude);
                                address_parent_div.parent().children('.select_car_address').children('.my_location_longitude').val(currentLongitude);
                                
                            }
                         });
                         google.maps.event.addListener(marker, 'click', function() {
                            infowindow.open(map,marker);
                          });
                         
                }
                
                function distance(lat1, lon1, lat2, lon2, unit) {
                    if ((lat1 == lat2) && (lon1 == lon2)) {
                            return 0;
                    }
                    else {
                            var radlat1 = Math.PI * lat1/180;
                            var radlat2 = Math.PI * lat2/180;
                            var theta = lon1-lon2;
                            var radtheta = Math.PI * theta/180;
                            var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
                            if (dist > 1) {
                                    dist = 1;
                            }
                            dist = Math.acos(dist);
                            dist = dist * 180/Math.PI;
                            dist = dist * 60 * 1.1515;
                            if (unit=="K") { dist = dist * 1.609344 }
                            if (unit=="N") { dist = dist * 0.8684 }
                            return dist;
                    }
            }
           
                function get_extra_price(car_latitude,car_longitude){
                   var vendor_latitude='<?php echo $vendor_latitude; ?>';        
                   var vendor_longitude='<?php echo $vendor_longitude; ?>';  
                   var miles=Math.ceil(parseFloat(distance(vendor_latitude, vendor_longitude, car_latitude, car_longitude, 'M')));
                   console.log('miles:'+miles);
                   var maximum_miles=parseFloat(<?php echo $maximum_miles; ?>);
                   var default_miles=parseFloat(<?php echo $default_miles; ?>);
                   var extra_permile_price='<?php echo $extra_permile_price; ?>';
                   if(miles>maximum_miles){
                       address_parent_div.parent().children('.select_car_address').children('.total_miles').val(miles);
                       return 0;
                   }
                   else{
                      address_parent_div.parent().children('.select_car_address').children('.total_miles').val(0);
                      if(miles > default_miles){
                        var difference= miles-default_miles;
                        var extra_price=difference*extra_permile_price;
                        return extra_price;
                      }
                      else{
                        return 0;      
                      }
                    }
                 }
            
            
        </script>
       <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCuC3PEVYk9RGDIIuLM1ur-sQ7y73ff3eY&libraries=places&callback=initMap"
        async defer></script>
        <style>
            #show_total_amount_with_tax{
                float:right;
            }
            .currency_total{
                float:left;
                margin-right:2px;
            }
            .price_total{
                float:left;
            }
            @media(max-width:520px){

                .modal-content {
                    width: 20rem !important;
                }
                }
                .show-modal {
                    z-index: 9;
                }
                @media (max-device-width:768px) and (orientation: landscape) {
                    .modal-content {
                              overflow: scroll;
                              height: 260px;
                          }
                  }
		span.chat_support {
			/*color: red; */
			font-size: 12px;
		}
        .options_show {
            padding: 10px;
            position: relative;
        }
        .disabled{
            display:none;
        }
        .add_more {
            font-size: 25px;
            margin-top: 7px;
            cursor: pointer;
        }
        .remove_row{
            font-size: 25px;
            margin-top: 7px;
             cursor: pointer;
        }
        .select_car_address{
            font-size: 18px;
			margin-bottom: 10px;
            /* margin-top: 8px;
            margin-left: 3px; */
			
        }
        
        div#loading {
            position: absolute;
            top: 6px;
            right: 160px;
        }
        .select_service_car select {
            flex: 1 1 30%;
            max-width: 28%;
            margin-right: 5px;
        }
        .select_service_car {
            display: flex;
            flex-wrap: wrap;
        }
        .btn_class{
            padding: 5px;
            margin: 5px;
            color: #fff;
            background: #0077dd;
        }
        .modal {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        opacity: 0;
        visibility: hidden;
        transform: scale(1.1);
        transition: visibility 0s linear 0.25s, opacity 0.25s 0s, transform 0.25s;
    }
    .modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 1rem 1.5rem;
        width: 24rem;
        border-radius: 0.5rem;
    }
    .close-button {
        float: right;
        width: 1.5rem;
        line-height: 1.5rem;
        text-align: center;
        cursor: pointer;
        border-radius: 0.25rem;
        background-color: lightgray;
    }
    .close-button:hover {
        background-color: darkgray;
    }
    .show-modal {
        opacity: 1;
        visibility: visible;
        transform: scale(1.0);
        transition: visibility 0s linear 0s, opacity 0.25s 0s, transform 0.25s;
    }
        
    .options_show input[type="radio"] {
        opacity: 1;
        position: relative;
    }
    .working-keys{
        display:block !important;
    }
    /*td.ui-datepicker-unselectable.ui-state-disabled.bookable span.ui-state-default {
        background: #dd122f;
        color: #fff;
    }
    td.ui-datepicker-unselectable.ui-state-disabled.bookable{
        opacity: 0.4;
    } */
        </style>
        <?php
    }
        
        function get_coordinates_from_address($address){
            $prepAddr = str_replace(' ','+',$address);
            $url = 'https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false&key=AIzaSyCuC3PEVYk9RGDIIuLM1ur-sQ7y73ff3eY';
            $geocode=@file_get_contents($url);
            $output= json_decode($geocode);
            $latitude = $output->results[0]->geometry->location->lat;
            $longitude = $output->results[0]->geometry->location->lng;
            return $latitude.'/'. $longitude;   
        }
        function get_all_cars(){
            global $wpdb;
            $table_name=BuyLockSmithDealsCustomizationAddon::blsd_y_m_model_table_name();
            return $result=$wpdb->get_results("Select DISTINCT maker,model,year from $table_name",ARRAY_A);
        }
       
   
        function blsd_add_custom_data_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
            
           $serviceable_car_maker = $_POST['serviceable_car_maker']; //filter_input( INPUT_POST, 'serviceable_car_maker' );
            $serviceable_car_model = $_POST['serviceable_car_model']; // filter_input( INPUT_POST, 'serviceable_car_model' );
            $serviceable_car_year =  $_POST['serviceable_car_year']; // filter_input( INPUT_POST, 'serviceable_car_year' );
            $my_location_latitude = $_POST['my_location_latitude']; // filter_input( INPUT_POST, 'my_location_latitude' );
            $my_location_longitude = $_POST['my_location_longitude']; //filter_input( INPUT_POST, 'my_location_longitude' );
            
            $total=count($serviceable_car_maker);
            $serviceable_car=[];
            $my_location_address=[];
            $my_location_coordinates=[];
            for($i=0;$i<$total;$i++){
                $serviceable_car[] = $serviceable_car_maker[$i].'-'.$serviceable_car_model[$i].'-'.$serviceable_car_year[$i];
                $my_location_address[]=self::get_address_from_coordinates($my_location_latitude[$i],$my_location_longitude[$i]);
                $my_location_coordinates[]=['latitude'=>$my_location_latitude[$i],'longitude'=>$my_location_longitude[$i]];
            }
            $working_keys = filter_input( INPUT_POST, 'working_keys' );
            $when_start_car = filter_input( INPUT_POST, 'when_start_car' );
            $car_currently_locked = filter_input( INPUT_POST, 'car_currently_locked' );
            $will_owner_authorize_service = filter_input( INPUT_POST, 'will_owner_authorize_service' );
            $property_type = filter_input( INPUT_POST, 'property_type' );
            $property_latitude = filter_input( INPUT_POST, 'property_latitude' );
            $property_longitude = filter_input( INPUT_POST, 'property_longitude' );
            $quantity_of_locks_to_rekey = filter_input( INPUT_POST, 'quantity_of_locks_to_rekey' );
            $service_charge = filter_input( INPUT_POST, 'amount' ); 
            $booking_charge = filter_input( INPUT_POST, 'booking_price' ); 
            $total_price=$service_charge+$booking_charge;
            $quantity=$total;
            $final_price=$total_price*$total;
            /*if ( empty( $serviceable_car ) ) {
                    return $cart_item_data;
            }*/
            $cart_item_data['quantity'] = $quantity;
            $cart_item_data['total_price'] = $final_price;
            $cart_item_data['service_charge'] = $service_charge;
            foreach($serviceable_car as $key=>$value){
                $cart_item_data['serviceable_car'][$key] = $value;
            }
            foreach($my_location_address as $key=>$value){
                $cart_item_data['my_location_address'][$key] = $value;
                $cart_item_data['my_location_coordinates'][$key] = $my_location_coordinates[$key];
            }
            if(!empty($working_keys)){
               $cart_item_data['working_keys'] = $working_keys; 
            }
            if(!empty($when_start_car)){
               $cart_item_data['when_start_car'] = $when_start_car; 
            }
            if(!empty($car_currently_locked)){
               $cart_item_data['car_currently_locked'] = $car_currently_locked; 
            }
            if(!empty($will_owner_authorize_service)){
               $cart_item_data['will_owner_authorize_service'] = $will_owner_authorize_service; 
            }
            if(!empty($property_type)){
               $cart_item_data['property_type'] = $property_type; 
            }
            if(!empty($quantity_of_locks_to_rekey)){
               $cart_item_data['quantity_of_locks_to_rekey'] = $quantity_of_locks_to_rekey; 
            }
            if(!empty($property_latitude) && !empty($property_longitude)){
                $property_address=self::get_address_from_coordinates($property_latitude,$property_longitude);
                $cart_item_data['property_address'] = $property_address;
            }
            
            return $cart_item_data;
        }
    
        function blsd_display_custom_text_cart($item_data, $cart_item ){
           /* if ( empty( $cart_item['serviceable_car'] ) ) {
                return $item_data;
            } */
            echo apply_filters( 
                            'woocommerce_cart_item_remove_link_custom',
                            sprintf(
                                    '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-cart_item_key="%s">X</a>',
                                    esc_url( '#' ),
                                    esc_html__( 'Remove this item', 'woocommerce' ),
                                    esc_attr( $cart_item['product_id'] ),
                                    esc_attr( '' ),
                                    esc_attr( $cart_item['key'] )
                            ),
                            $cart_item['key']
                    );
            if ( ! empty( $cart_item['addons'] ) ) {
                foreach ( $cart_item['addons'] as $addon ) {
                        $price = isset( $cart_item['addons_price_before_calc'] ) ? $cart_item['addons_price_before_calc'] : $addon['price'];
                        $name  = $addon['name'];
                        if ( 0 == $addon['price'] ) {
                                $name .= '';
                        } elseif ( 'percentage_based' === $addon['price_type'] && 0 == $price ) {
                                $name .= '';
                        } elseif ( 'percentage_based' !== $addon['price_type'] && $addon['price'] && apply_filters( 'woocommerce_addons_add_price_to_name', '__return_true' ) ) {
                                $name .= ' (' . wc_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( $cart_item['service_charge'], $cart_item['data'], true ) ) . ')';
                        } else {
                                $_product = new WC_Product( $cart_item['product_id'] );
                                $_product->set_price( $price * ( $addon['price'] / 100 ) );
                                $name .= ' (' . WC()->cart->get_product_price( $_product ) . ')';
                        }
                        foreach($item_data as $key=>$value){
                           if($addon['value']==$value['value']){
                              $item_data[$key]['name'] =$name;
                           }
                        }
                }
            }
            
             $total_car=count($cart_item['serviceable_car']);
             for($i=0;$i<$total_car;$i++){
                 $k='';
                 if(isset($cart_item['my_location_address'][$i])){
                    $item_data[] = array(
                        'key'     => __( 'My Location '.$k, 'iconic' ),
                        'value'   => wc_clean( $cart_item['my_location_address'][$i] ),
                        'display' => '',
                    );
                }
                if(isset($cart_item['serviceable_car'][$i])){
                 $item_data[] = array(
                        'key'     => __( 'Serviceable Car '.$k, 'iconic' ),
                        'value'   => wc_clean( $cart_item['serviceable_car'][$i] ),
                        'display' => '',
                    );
                } 
                
             }
            
                    
            if(isset($cart_item['working_keys'])){
                $item_data[] = array(
                    'key'     => __( 'Do you have any working keys?', 'iconic' ),
                    'value'   => wc_clean( $cart_item['working_keys'] ),
                    'display' => '',
                );
            }
            if(isset($cart_item['when_start_car'])){
                $item_data[] = array(
                    'key'     => __( 'When you start your car?', 'iconic' ),
                    'value'   => wc_clean( $cart_item['when_start_car'] ),
                    'display' => '',
                );
            }
             if(isset($cart_item['car_currently_locked'])){
                $item_data[] = array(
                    'key'     => __( 'Is the car currently locked?', 'iconic' ),
                    'value'   => wc_clean( $cart_item['car_currently_locked'] ),
                    'display' => '',
                );
             }
             if(isset($cart_item['will_owner_authorize_service'])){
                $item_data[] = array(
                    'key'     => __( 'Will the owner be able to authorize service? ', 'iconic' ),
                    'value'   => wc_clean( $cart_item['will_owner_authorize_service'] ),
                    'display' => '',
                );
             }
             if(isset($cart_item['property_type'])){
                $item_data[] = array(
                    'key'     => __( 'Type of property', 'iconic' ),
                    'value'   => wc_clean( $cart_item['property_type'] ),
                    'display' => '',
                );
             }
             if(isset($cart_item['property_address'])){
                $item_data[] = array(
                    'key'     => __( 'Property Address', 'iconic' ),
                    'value'   => wc_clean( $cart_item['property_address'] ),
                    'display' => '',
                );
             }
             if(isset($cart_item['quantity_of_locks_to_rekey'])){
                $item_data[] = array(
                    'key'     => __( 'Quantity of locks to rekey', 'iconic' ),
                    'value'   => wc_clean( $cart_item['quantity_of_locks_to_rekey'] ),
                    'display' => '',
                );
             }
           
           return $item_data;
        }

        function blsd_add_custom_data_order_items( $item, $cart_item_key, $values, $order ) {
            /*if ( empty( $values['serviceable_car'] ) ) {
                return;
            } */
            $total_car=count($values['serviceable_car']);
            for($i=0;$i<$total_car;$i++){
                $k='';
               $item->add_meta_data( __( 'Serviceable Car '.$k, 'blsd' ), $values['serviceable_car'][$i] ); 
               $item->add_meta_data( __( 'My Location '.$k, 'blsd' ), $values['my_location_address'][$i] );
            }
               
            $item->add_meta_data( __( 'Do you have any working keys?', 'blsd' ), $values['working_keys'] );
            $item->add_meta_data( __( 'When you start your car? ', 'blsd' ), $values['when_start_car'] );
            $item->add_meta_data( __( 'Is the car currently locked?', 'blsd' ), $values['car_currently_locked'] );
            $item->add_meta_data( __( 'Will the owner be able to authorize service? ', 'blsd' ), $values['will_owner_authorize_service'] );
            $item->add_meta_data( __( 'Type of property', 'blsd' ), $values['property_type'] );
            $item->add_meta_data( __( 'Property Address', 'blsd' ), $values['property_address'] );
            $item->add_meta_data( __( 'Quantity of locks to rekey', 'blsd' ), $values['quantity_of_locks_to_rekey'] );
        }
        
        function blsd_before_calculate_totals($cart_obj){
            global $woocommerce;
            foreach( $cart_obj->get_cart() as $key=>$value ) {
                if( isset( $value['total_price'] ) ) {
                    $price = $value['total_price'];
                    $value['data']->set_price( ( $price ) );
                }
            } 
        }
        
        
        function get_address_from_coordinates($lat,$lng){
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false&key=AIzaSyCuC3PEVYk9RGDIIuLM1ur-sQ7y73ff3eY';
           
            $json = @file_get_contents($url);
            $data=json_decode($json);
            $status = $data->status;
            if($status=="OK")
            {
              return $data->results[0]->formatted_address;
            }
            else
            {
              return false;
            }
        }
        
        function blsd_Checkout_page_css(){ ?>
        <style>   
        /***************13-12-2019***** styling deal page***********/
        .wc-bookings-date-picker-timezone-block {
           display: none;
        }
        .field .offers {
            display: none;
        }   
        .wc-bookings-booking-form fieldset .picker.hasDatepicker {
             margin-bottom: -2em;
        }
        .product-addon-totals .wc-pao-subtotal-line{
            display: none;
        }
        a.vendor-breadcrumbs {
            font-size: 20px;
            color: #000;
            font-weight: 500;
        }
        
        @media (max-width:767px) {
body.single.single-product .page-header {
    position: absolute;
    z-index: 999;
    margin-top: 15px;
    border: 0;
}
body.single.single-product p#breadcrumbs {
    color: #f2f5f7;
}
body.single.single-product p#breadcrumbs a {
    display: none;
    color: #000;
}
body.single.single-product a.vendor-breadcrumbs {
    display: inline-block !important;
    margin-left: -20px;
}

i.vendor-breadcrumbs-left {
    border: solid black;
    border-width: 0 2px 2px 0;
    display: inline-block !important;
    padding: 5px;
    transform: rotate(135deg);
    -webkit-transform: rotate(135deg);
}
.single-product .page-header {
    overflow: hidden;
    width: 88%;
    left: 0;
    right: 0;
    background: linear-gradient(rgba(231, 231, 231, 0.5),rgba(182, 180, 180, 0.5));
}

}
i.vendor-breadcrumbs-left{
    display:none;
}
        </style>
    
        <?php if(is_checkout()){ ?>
        
    <style>
    table.shop_table tbody tr td.product-name dl.variation {
        margin: 10px 0 0 0;
         color: #000; 
    }
    a.remove {
         left: auto;
         border: 1px solid red;
         padding: 0px 7px;
         border-radius: 56px;
         background: #e10404ad;
         color: #fff !important;
         font-size: 14px;
         float:right;
    }
    td.product-name {
        position: relative;
    }
</style>
<?php
        }
}

function blsd_checkout_remove_item(){
     global $post,$WCMp;
      if(is_checkout()){
    ?>
    <script>
    jQuery(document).ready(function(){
        jQuery(document).on('click', 'tr.cart_item a.remove', function (e)
        {
            e.preventDefault();
            var product_id = jQuery(this).attr("data-product_id"),
            cart_item_key = jQuery(this).attr("data-cart_item_key"),
            product_container = jQuery(this).parents('.shop_table');
            product_container.block({
                message: null,
                overlayCSS: {
                    cursor: 'none'
                }
            });

            jQuery.ajax({
                type: 'POST',
                url: '<?php echo add_query_arg( 'action', 'product_remove', $WCMp->ajax_url() ); ?>',
                data: {
                    product_id: product_id,
                    cart_item_key: cart_item_key
                },
                success: function(response) {
                    if ( ! response || response.error )
                        return;

                    var fragments = response.fragments;
                    if ( fragments ) {
                        jQuery.each( fragments, function( key, value ) {
                            jQuery( key ).replaceWith( value );
                        });
                    }
                    jQuery('body').trigger('update_checkout');
                    jQuery('.woocommerce-message').hide();

                }
            });
        });
    }); 
  </script>
  <?php
      }
  
}


}


?>