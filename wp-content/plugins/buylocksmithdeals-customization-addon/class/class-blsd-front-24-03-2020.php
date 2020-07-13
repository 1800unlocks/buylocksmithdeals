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
        
        add_action('pre_get_posts', array($this, 'disable_product_after_vendor_action'), 1000, 1);

        add_filter('woocommerce_product_tabs', array($this, 'blsd_woo_TnC_tab'), 50, 1);
        /*         * ********25-10-2019*********** */
        add_action('woocommerce_before_add_to_cart_button', array($this, 'blsd_frontend_before_add_to_cart_btn'));
        add_filter('woocommerce_add_cart_item_data', array($this, 'blsd_add_custom_data_to_cart_item'), 10, 3);
        add_filter('woocommerce_get_item_data', array($this, 'blsd_display_custom_text_cart'), 20, 2);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'blsd_add_custom_data_order_items'), 10, 4);
        add_action('woocommerce_before_calculate_totals', array($this, 'blsd_before_calculate_totals'), 10, 1);
        add_filter('woocommerce_booking_single_add_to_cart_text', array($this, 'woo_custom_single_add_to_cart_text'), 100000);  // 2.1 +

        add_action('wp_head', array($this, 'blsd_Checkout_page_css'));
        add_action('wp_footer', array($this, 'blsd_checkout_remove_item'));
        add_action('woocommerce_review_order_before_payment', array($this, 'add_heading_payment'));
        add_action('woocommerce_checkout_order_review', array($this, 'blsd_woocommerce_checkout_billing'), 15);
        add_action('woocommerce_before_checkout_billing_form', array($this, 'add_service_location'),10,1);
        add_filter("wc_stripe_payment_request_supported_types", array($this,'platinum_prize_draws_wc_stripe_payment_request_supported_types'), 10);
        //add_shortcode('stripe_apple_pay',array($this,'show_apple_pay_button'));
        add_action('wp_footer', array($this, 'dynamic_sidebar_front'), 5);
        // add_filter('azexo_page_title',array($this,'custom_action_after_single_product_title'),10,1 );
        add_action("get_template_part", array($this, 'overwrite_general_title'), 100, 3);
        add_filter('wp_nav_menu_items', array($this, 'get_vendor_profile_in_menu'), 10, 2);
        add_filter('wpseo_breadcrumb_single_link', array($this, 'ss_breadcrumb_single_link'), 10, 2);
        
        add_action('wp_enqueue_scripts', array($this, 'blsd_add_js_frontend'));
        add_action( 'woocommerce_thankyou', array($this, 'blsd_vendor_new_order'),10,1 ); 
    
        add_filter( 'woocommerce_product_add_to_cart_text', array($this, 'blsd_vendor_view_change_text'),10,2 );
        add_filter( 'woocommerce_product_addons_option_price', array($this,'blsd_hide_addon_price'),10,4);
        add_action('woocommerce_after_single_product_summary', array($this, 'blsd_add_buy_now_deal_page'));
        
       }
    function blsd_add_buy_now_deal_page(){
        echo '<div class="mobile_btn_buy_now"><button type="button" name="buy_now" id="mobile_buy_now">Buy Now</button></div>';
       echo '<script>
        jQuery("document").ready(function () {     
            jQuery("#mobile_buy_now").click(function () { 
                jQuery("html, body").animate({
                    scrollTop: jQuery(".product-summary").offset().top
                }, 2000);
            }); 
        });
        </script>'; 
        echo '<style>'
       . 'button#mobile_buy_now {
    background: #77c84e !important;
    border: none;
    padding: 10px 24px;
    text-align: center;
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    box-shadow: 1px 2px 1px 1px #83b968;
    border-radius: 4px;
}

.mobile_btn_buy_now {
    display: none;
}
@media(max-width:767px){
.mobile_btn_buy_now {
    text-align: center;
    padding: 10px 0 0 0;
    display: block;
}
}'
                . '</style>';
    }
    function blsd_hide_addon_price($price_for_display,$option,$i,$type){
        $price_for_display='';
        return $price_for_display;
    }
    function blsd_vendor_view_change_text($text, $obj ){
            $text='Buy Now';
        return $text;
        
    }
    function blsd_vendor_new_order($order_id){
       
        $sub_orders = get_children( array('post_parent' => $order_id, 'post_type' => 'shop_order' ) );
           
        foreach($sub_orders as $sorder){
           $suborder_id=$sorder->ID;
            $suborder_authorid=$sorder->post_author;
            $vendor_phone_prefix = get_user_meta($suborder_authorid, '_vendor_phone_prefix', true);
            $vendor_phone = get_user_meta($suborder_authorid, '_vendor_phone', true);
            if(!empty($vendor_phone)){
            $phone_no=$vendor_phone_prefix.$vendor_phone;
            $message='You have just sold a deal. Check your email for more details. Ref Order #'.$suborder_id;
            $sms=BuyLockSmithDealsCustomizationAddon::send_sms($phone_no,$message);
            }
        }
    }
    
   function blsd_add_js_frontend(){
       global $WCMp;
       if (!is_admin()) {
           $vendor = get_wcmp_vendor(get_current_vendor_id());
           if($vendor && !is_vendor_dashboard()){
               wp_dequeue_script('wc-bookings-booking-form');
                wp_enqueue_script('wc-bookings-booking-form',BUYLOCKSMITH_DEALS_ASSETS_PATH . 'js/frontend.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-datepicker', 'underscore' ), WC_BOOKINGS_VERSION, true );
       
           }
           }
   }

    function ss_breadcrumb_single_link($link_output, $link) {
        $element = '';
        $element = esc_attr(apply_filters('wpseo_breadcrumb_single_link_wrapper', $element));
        $link_output = $element;
        //print_r($link);
        $vendor_url = home_url() . '/vendor/';
        if (strpos($link['url'], $vendor_url) !== false) {
            $link_output .= ' <a href="' .
                    esc_url($link['url']) . '" class="vendor-breadcrumbs"> <i class="vendor-breadcrumbs-left"></i>' .
                    esc_html($link['text']) . '</a>';
        } else {
            if (isset($link['url'])) {
                $link_output .= '<a href="' .
                        esc_url($link['url']) . '"class="other-breadcrumbs">' .
                        esc_html($link['text']) . '</a>';
            }
        }
        return $link_output;
    }

    function get_vendor_profile_in_menu($items, $args) {
        global $WCMp, $post;
        if (is_user_logged_in()) {
            $vendor_id = get_current_user_id();
            $vendor = get_wcmp_vendor($vendor_id);
            if ($vendor) {
                echo '<style>.myaccount_ourstore{ display: block;}</style>';
                $user_meta_post_id = get_user_meta($vendor_id, '_vendor_profile_image', true);
                if ($user_meta_post_id) {
                    $user_meta_post_id_to_post_content = get_post($user_meta_post_id);
                    $get_user_profile_image_url = $user_meta_post_id_to_post_content->post_content;
                    $items = '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . $vendor->permalink . '" class="menu-link"><img style="height: 36px;" src="' . $get_user_profile_image_url . '"></a></li>' . $items;
                } else {
                    $items = '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . $vendor->permalink . '" class="menu-link"><img style="height: 36px;" src="' . $WCMp->plugin_url . 'assets/images/WP-stdavatar.png"></a></li>' . $items;
                }
            } else {
                if (is_product()) {
                    $vendor_id = $post->post_author;
                    $vendor = get_wcmp_vendor($vendor_id);
                    if ($vendor) {
                        $user_meta_post_id = get_user_meta($vendor_id, '_vendor_profile_image', true);
                        if ($user_meta_post_id) {
                            $user_meta_post_id_to_post_content = get_post($user_meta_post_id);
                            $get_user_profile_image_url = $user_meta_post_id_to_post_content->post_content;
                            $items = '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . $vendor->permalink . '" class="menu-link"><img style="height: 36px;" src="' . $get_user_profile_image_url . '"></a></li>' . $items;
                        } else {
                            //$items = '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="'.$vendor->permalink.'" class="menu-link">'.$vendor->user_data->data->display_name.'</a></li>'.$items;
                            $items = '<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="' . $vendor->permalink . '" class="menu-link"><img style="height: 36px;" src="' . $WCMp->plugin_url . 'assets/images/WP-stdavatar.png"></a></li>' . $items;
                        }
                    }
                }
            }
        }
        return $items;
    }

    function overwrite_general_title($slug, $name, $templates) {
        if (is_product()) {
            include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/' . $templates[0];
        }
    }

    function dynamic_sidebar_front() {
        remove_action('wp_footer', 'azexo_footer');
        if (is_active_sidebar('dynamic_footer')) :
            ?>
            <div class="sidebar new-sidebar">
                <?php dynamic_sidebar('dynamic_footer'); ?>
            </div>
            <?php
        endif;
    }

    function add_service_location($checkout) {
        global $WCMp;
        $fields = $checkout->get_checkout_fields('billing');
        foreach ($fields as $key => $field) {
            $values[$key] = $checkout->get_value($key);
        }
        // print_r($values);
        echo '<input type="checkbox" class="service_location" id="same_as_service_location" name="same_as_service_location" value="1">Billing Same as Service location';
        echo '<style>.service_location{position: relative !important; opacity: 1 !important; }</style>';
        ?>
        <script>

            jQuery('#same_as_service_location').click(function () {
                if (jQuery('#same_as_service_location').prop('checked') == true) {
                    var service_location = 'checked';

                } else if (jQuery('#same_as_service_location').prop('checked') == false) {
                    var service_location = 'unchecked';
                }
                jQuery.ajax({
                    url: '<?php echo add_query_arg('action', 'blsd_location_same_service', $WCMp->ajax_url()); ?>',
                    type: "post",
                    data: {status: service_location},
                    success: function (resultData) {
                        var array = JSON.parse(resultData);
                        console.log(array);
                        jQuery.each(array, function (index, value) {
                            if (index == 'billing_country') {
                                jQuery('#' + index).val(value).trigger('change');
                            } else if (index == 'billing_state') {
                                setTimeout(function () {
                                    jQuery('#' + index).val(value).trigger('change');
                                }, 3000);
                            } else {
                                jQuery('#' + index).val(value);
                            }
                        });

                    }
                });
            });
        </script>
        <?php
    }

    function blsd_woocommerce_checkout_billing() {
        if (WC()->checkout()->get_checkout_fields()) :

            do_action('woocommerce_checkout_before_customer_details');
            ?>

            <div class="col2-set" id="customer_details">
                <div class="col-1">
                    <?php do_action('woocommerce_checkout_billing'); ?>
                </div>

                <div class="col-2">
                    <?php do_action('woocommerce_checkout_shipping'); ?>
                </div>
            </div>

            <?php
            do_action('woocommerce_checkout_after_customer_details');
        endif;
    }

    function show_apple_pay_button() {
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
            paymentRequest.canMakePayment().then(function (result) {
                if (result) {
                    prButton.mount('#payment-request-button');
                } else {
                    document.getElementById('payment-request-button').style.display = 'none';
                }
            });

            paymentRequest.on('token', function (ev) {
        // Send the token to your server to charge it!
                fetch('/charges', {
                    method: 'POST',
                    body: JSON.stringify({token: ev.token.id}),
                    headers: {'content-type': 'application/json'},
                })
                        .then(function (response) {
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

            paymentRequest.on('shippingaddresschange', function (ev) {
                if (ev.shippingAddress.country !== 'US') {
                    ev.updateWith({status: 'invalid_shipping_address'});
                } else {
        // Perform server-side request to fetch shipping options
                    fetch('/calculateShipping', {
                        data: JSON.stringify({
                            shippingAddress: ev.shippingAddress
                        })
                    }).then(function (response) {
                        return response.json();
                    }).then(function (result) {
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

    function platinum_prize_draws_wc_stripe_payment_request_supported_types($supported_types) {
     return $supported_types;
    }

    function add_heading_payment() {
        echo '<h3>Payment</h3>';
    }

    function woo_custom_single_add_to_cart_text() {

        return __('Claim This Deal', 'woocommerce');
    }

    function target_main_conditional_product_list($query) {
        global $pagenow, $woocommerce_loop;
        if (is_shop() || (is_product() && $woocommerce_loop['name'] == 'related' )) {
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

    function woocommerce_related_products_function($related_posts, $product_id, $args) {

        // print_r($related_posts); exit;
        global $wpdb;
        $exclude = [];
        $query = "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND ID in (select post_id from wp_postmeta where meta_key='_vendor_product_parent')";
        $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);

        if (count($results) > 0) {
            foreach ($results as $result) {
                $exclude[] = $result['ID'];
            }
        }
        $related_posts = array_diff($related_posts, $exclude);
        shuffle($related_posts);

        return array_slice($related_posts, 0, $limit);
    }

    function blsd_woo_TnC_tab($tabs) {

        global $product;
        unset($tabs['wcmp_customer_qna']);
        unset($tabs['policies']);
        if (!empty($tabs)) {
            foreach ($tabs as $key => $tab) {
                if ($key == 'description') {
                    $tabs[$key]['title'] = 'deal Details';
                    $tabs[$key]['priority'] = 20;
                }
                if ($key == 'vendor') {
                    $tabs[$key]['title'] = 'Locksmith Info';
                    $tabs[$key]['priority'] = 10;
                }
            }
        }
        //get current product ID
        $product_id = $product->get_ID();
        $have_data = get_post_meta($product_id, 'prod_vendor_TnC', true);

        // Adds the new tab
        if ($have_data != '') {
            $tabs['TnC_tab'] = array(
                'title' => __('Terms and conditions', 'woocommerce'),
                'priority' => 50,
                'callback' => array($this, 'woo_TnC_tab_tab_content')
            );
        }
        $tabs['faq_tab'] = array(
            'title' => __('FAQ', 'textdomain'),
            'callback' => array($this, 'blsd_faq_tab_content'),
            'priority' => 50,
        );

        return $tabs;
    }

    function blsd_faq_tab_content() {
        global $product;
        $product_id = $product->get_ID();
        $product_parent = get_post_meta($product_id, '_vendor_product_parent', true);
        if (!empty($product_parent)) {
            $product_parent_id = $product_parent;
        } else {
            $product_parent_id = $product_id;
        }
        $product_car_key = get_post_meta($product_parent_id, 'product_car_key', true);
        $product_lock_rekeying = get_post_meta($product_parent_id, 'product_lock_rekeying', true);

        $default_product_car_cat_id = get_post_meta($product_parent_id, 'default_product_car_cat_id', true);
        $default_product_lock_rekeying_cat_id = get_post_meta($product_parent_id, 'default_product_lock_rekeying_cat_id', true);
        if ($product_car_key == 'yes') {
            echo do_shortcode('[faq cat_id="' . $default_product_car_cat_id . '"]');
        }
        /*if ($product_lock_rekeying == 'yes') {
            echo do_shortcode('[faq cat_id="' . $default_product_lock_rekeying_cat_id . '"]');
        }*/
        //print_r($categories);
    }

    function woo_TnC_tab_tab_content() {
        global $product;
        //get current product ID
        $product_id = $product->get_ID();
        echo get_post_meta($product_id, 'prod_vendor_TnC', true);
    }

    function blsd_frontend_before_add_to_cart_btn() {
        global $post, $WCMp;
        $post_id = $post->ID;
        $product_parent = get_post_meta($post_id, '_vendor_product_parent', true);
        if (!empty($product_parent)) {
            $post_parent_id = $product_parent;
        } else {
            $post_parent_id = $post_id;
        }
        $author_id = $post->post_author;
        $user_meta = get_userdata($author_id);
        $user_roles = $user_meta->roles;
        if (!in_array('dc_vendor', $user_roles)) {
            $phone = get_user_meta($author_id, 'billing_phone', true);
            $address1 = get_user_meta($author_id, 'billing_address_1', true);
            $address2 = get_user_meta($author_id, 'billing_address_2', true);
            $city = get_user_meta($author_id, 'billing_city', true);
            $state = get_user_meta($author_id, 'billing_state', true);
            $postcode = get_user_meta($author_id, 'billing_postcode', true);
        } else {
            $phone = get_user_meta($author_id, '_vendor_phone', true);
            $address1 = get_user_meta($author_id, '_vendor_address_1', true);
            $address2 = get_user_meta($author_id, '_vendor_address_2', true);
            $city = get_user_meta($author_id, '_vendor_city', true);
            $state = get_user_meta($author_id, '_vendor_state', true);
            $postcode = get_user_meta($author_id, '_vendor_postcode', true);
        }
        
        $mobile_locksmith = get_post_meta($post_id, 'mobile_locksmith', true);
        $mobile_locksmith_address = get_post_meta($post_id, 'mobile_locksmith_address', true);
        if($mobile_locksmith == 'yes'){
            if(!empty($mobile_locksmith_address)){
                $vendor_address = $mobile_locksmith_address;
            }
            else {
                $vendor_address = $address1 . ' ' . $address2 . ' ' . $city . ' ' . $state . ' ' . $postcode;
            }
        }else{
            $vendor_address = $address1 . ' ' . $address2 . ' ' . $city . ' ' . $state . ' ' . $postcode;
        }
        $coordinates = self::get_coordinates_from_address($vendor_address);
        $vendor_latitude = explode('/', $coordinates)[0];
        $vendor_longitude = explode('/', $coordinates)[1];
        $currenct_symbol = get_woocommerce_currency_symbol();


        $show_unserviceable_cars = get_post_meta($post_parent_id, 'show_unserviceable_cars', true);
        $where_need_service = get_post_meta($post_parent_id, 'where_need_service', true);
        $where_car_located = get_post_meta($post_parent_id, 'where_car_located', true);
        $have_any_working_keys = get_post_meta($post_parent_id, 'have_any_working_keys', true);
        $have_any_working_keys_locks = get_post_meta($post_parent_id, 'have_any_working_keys_locks', true);
        $when_start_car = get_post_meta($post_parent_id, 'when_start_car', true);
        $is_car_currently_locked = get_post_meta($post_parent_id, 'is_car_currently_locked', true);
        $will_owner_authorize_service = get_post_meta($post_parent_id, 'will_owner_authorize_service', true);
        $need_key_to_work = get_post_meta($post_parent_id, 'need_key_to_work', true);
        $ask_property_type = get_post_meta($post_parent_id, 'ask_property_type', true);
        $where_property_located = get_post_meta($post_parent_id, 'where_property_located', true);
        $quantity_of_locks = get_post_meta($post_parent_id, 'quantity_of_locks', true);
        
        /*************Deadbolts Installation************/
        $who_supplies_deadbolts = get_post_meta($post_parent_id, 'who_supplies_deadbolts', true);
        $type_of_installation = get_post_meta($post_parent_id, 'type_of_installation', true);
        $door_and_frame_type = get_post_meta($post_parent_id, 'door_and_frame_type', true);
        $quantity_of_locks_install = get_post_meta($post_parent_id, 'quantity_of_locks_install', true);
        $customer_fresh_install_deadbolt = get_post_meta($post_id, 'customer_fresh_install_deadbolt', true);
        $customer_replaced_deadbolt = get_post_meta($post_id, 'customer_replaced_deadbolt', true);
        $locksmith_fresh_install_deadbolt = get_post_meta($post_id, 'locksmith_fresh_install_deadbolt', true);
        $locksmith_replaced_deadbolt = get_post_meta($post_id, 'locksmith_replaced_deadbolt', true);
        
        /*********************************/
        
        $default_miles = get_post_meta($post_id, 'default_miles', true);
        $extra_permile_price = get_post_meta($post_id, 'extra_permile_price', true);
        $maximum_miles = get_post_meta($post_id, 'maximum_miles', true);
        $discount_on_deal=get_post_meta($post_id, 'discount_on_deal', true);
        $car_programming_fee=  get_post_meta($post_id, 'car_programming_fee', true);
        $car_vats_programming_fee=  get_post_meta($post_id, 'car_vats_programming_fee', true);
        if($car_vats_programming_fee==''){
            $car_vats_programming_fee =0;    
        }
        if($car_programming_fee ==''){
            $car_programming_fee =0;    
        }
        $car_key_look_like = get_post_meta($post_parent_id, 'car_key_look_like', true);
        $edge_cut_price = get_post_meta($post_id, 'edge_cut_price', true);
        $high_security_price = get_post_meta($post_id, 'high_security_price', true);
        $tibbe_price = get_post_meta($post_id, 'tibbe_price', true);
        $vats_price = get_post_meta($post_id, 'vats_price', true);
        $file_edge_cut=get_option('file_edge_cut');
        $file_high_security=get_option('file_high_security');
        $file_tibbe= get_option('file_tibbe');
        $file_vats= get_option('file_vats');
        $file_locks= get_option('file_locks');
        $target_dir_img = WP_PLUGIN_URL  .'/'.BUYLOCKSMITH_DEALS_BASE_FOLDER_NAME . "/uploads/";
        $loader_img = BUYLOCKSMITH_DEALS_ASSETS_PATH  .'img/loader.gif';
        
        $want_more_than_one_key = get_post_meta($post_parent_id, 'want_more_than_one_key', true);
        //$cost_to_cut_additional_key = get_post_meta($post_id, 'cost_to_cut_additional_key', true);
        $cost_to_cut_additional_key = 0;
        $cost_to_program_additional_key = get_post_meta($post_id, 'cost_to_program_additional_key', true);
        echo '<div class="main_loader disabled"><img class="loader_img" src="'.$loader_img.'"></div>';
        echo '<input type="hidden" name="amount" id="amount" value="">';
        echo '<input type="hidden" name="booking_price" id="booking_price" value="">';
        echo '<input type="hidden" name="booking_price_with_tax" id="booking_price_with_tax" value="">';
        echo '<input type="hidden" name="total_price" id="total_price" value="">';
        echo '<input type="hidden" name="discount_price" id="discount_price" value="">';
        echo '<input type="hidden" name="sub_total_price" id="sub_total_price" value="">';
        echo '<input type="hidden" name="car_programming_fees" id="car_programming_fees" value="">';
        echo '<input type="hidden" name="cprogramming_fees" id="cprogramming_fees" value="">';
        echo '<input type="hidden" name="cvats_fees" id="cvats_fees" value="">';
        echo '<input type="hidden" name="booking_servicable" id="booking_servicable" value="1">';
        echo '<input type="hidden" name="deadbolt_install_price" id="deadbolt_install_price" value="">';
		echo '<input type="hidden" name="maximum_miles" id="maximum_miles" value="'.$maximum_miles.'">';
        echo '<input type="hidden" name="default_miles" id="default_miles" value="'.$default_miles.'">';
        echo '<input type="hidden" name="extra_permile_price" id="extra_permile_price" value="'.$extra_permile_price.'">';
        if ($where_car_located == 'yes') {
            echo '<div class="options_show select_address disabled"><label> Where is the car located? </label><button  type="button" name="select_address" id="select_address" class="btn_class">Select Address</button></div>';
        }
        
        if ($show_unserviceable_cars == 'yes') {
            $vendor_unserviceable_cars = get_post_meta($post_id, 'unserviceable_cars', true);

            $all_cars = BuyLockSmithDealsCustomizationAddon::get_all_cars_frontend($vendor_unserviceable_cars, 'maker', '',$post_id);
            $allcar_name = [];
            $optionss = [];
            $all = 0;
            $option_maker = '<option value="">Make</option>';
            $option_model = '<option value="">Model</option>';
            $option_year = '<option value="">Year</option>';
            if (!empty($all_cars)) {
                $car_maker = '';
                foreach ($all_cars as $cars) {
                    $option_maker .= '<option value="' . $cars['maker'] . '">' . $cars['maker'] . '</option>';
                }
            }
            if ($where_car_located == 'yes') {
                $vehical_located='vehical_located_show';
            }
            else{
                $vehical_located='vehical_located_hide';
            }
            
            echo '<div class="options_show" id="select_car"><label> Choose your Car </label><div id="loading" class="disabled" ><i class="fa fa-refresh fa-spin" style="font-size:24px"></i></div>'
            . '<div class="select_service_car"> <select name="serviceable_car_maker[]" class="serviceable_car_maker">' . $option_maker . '</select>'
            . '<select name="serviceable_car_model[]" class="serviceable_car_model">' . $option_model . '</select>'
            . '<select name="serviceable_car_year[]" class="serviceable_car_year">' . $option_year . '</select> '
            . '<input type="hidden" name="car_programming[]" class="car_programming" value="">'
            . '<input type="hidden" name="car_type[]" class="car_type" value="">'
            . '<input type="hidden" name="car_message[]" class="car_message" value="">'
          //. '<div class="add_more">+</div> '
            . '<span class="show_selected_address disabled" ></span>'
            . '<div class="select_car_address select_car_address_label disabled '.$vehical_located.'"><label>Where is the vehical located?</label></div>'
            . '<div class="select_car_address disabled"><button type="button" name="vehical_location" class="btn_class" >Set Vehicle Service Location</button>'
            . '<input type="hidden" name="my_location_address[]" class="my_location_address" value="">'
            . '<input type="hidden" name="my_location_latitude[]" class="my_location_latitude" value="">'
            . '<input type="hidden" name="my_location_longitude[]" class="my_location_longitude" value="">'
            . '<input type="hidden" name="extra_amount[]" class="extra_amount" value="">'
            . '<input type="hidden" name="total_miles[]" class="total_miles" value="">'
            . '</div> '
            . '</div> </div><span class="chat_support">*Any unserviceable vehicles are not listed. Start a live chat with us for more help.</span> ';
        }

        $service_location = 0;
        if ($where_need_service == 'yes') {
            $product_addons = get_post_meta($post_id, '_product_addons', true);
            if (!empty($product_addons)) {
                $service_location = 1;
                //echo '<input type="hidden" name="my_location_address" id="my_location_address" value="">';
                // echo '<input type="hidden" name="my_location_latitude" id="my_location_latitude" value="">';
                // echo '<input type="hidden" name="my_location_longitude" id="my_location_longitude" value="">';
            } else {
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

        if ($have_any_working_keys == 'yes') {
            echo '<div class="options_show"><label class="working-keys">Do you have any working keys that start the car? </label><input type="radio" name="working_keys" value="yes" checked="checked"> Yes <input type="radio" name="working_keys" value="no"> No </div>';
        }
        if ($have_any_working_keys_locks == 'yes') {
            echo '<div class="options_show"><label class="working-keys">Do you have any working keys for the locks? </label><input type="radio" name="working_keys_locks" value="yes" checked="checked"> Yes <input type="radio" name="working_keys_locks" value="no"> No </div>';
        }
        if ($when_start_car == 'yes') {
            echo '<div class="options_show"><label> How do you start your car? </label>'
            . '<select name="when_start_car" id="when_start_car">'
            . '<option value="" >Select</option>'
            . '<option value="Turn key ignition" >Turn key ignition</option>'
            . '<option value="Prox twist (twist nub)" >Prox twist (twist nub)</option>'
            . '<option value="Push to start" >Push to start</option>'
            . '</select> </div>';
        }
        if ($is_car_currently_locked == 'yes') {
            echo '<div class="options_show"><label class="working-keys"> Is the car currently locked? </label><input type="radio" name="car_currently_locked" value="yes" checked="checked"> Yes <input type="radio" name="car_currently_locked" value="no"> No </div>';
        }
        if ($need_key_to_work == 'yes') {
           echo '<div class="options_show"><label> Do you need a key to work? </label>'
            . '<select name="need_key_to_work" id="need_key_to_work">'
            . '<option value="" >Select</option>'
            . '<option value="Start the car" >Start the car</option>'
            . '<option value="Unlock the doors" >Unlock the doors</option>'
            . '<option value="Both" >Both</option>'
            . '</select> </div>';
           
        }
        if($car_key_look_like == 'yes'){
             echo '<div class="options_show"><label> What does your car key look like?<span class="chat_support">(Click to select)</span> </label>';
            echo '<input type="hidden" name="car_key_type" id="car_key_type" value="">';
            echo '<input type="hidden" name="car_key_price" id="car_key_price" value="">';
            if(!empty($file_edge_cut)){
                echo '<div class="car-key-img"><span class="car_key_img" data-id="edge_cut"><img src="'.$target_dir_img.$file_edge_cut.'" height="50" width="50"></span></div>';
            }
            else{
                echo '<div class="car-key-img car_key_name"><span class="car_key_img" data-id="edge_cut">Double-Sided</span></div>';
           }
            if(!empty($file_high_security)){
                echo '<div class="car-key-img"><span class="car_key_img" data-id="high_security"><img src="'.$target_dir_img.$file_high_security.'" height="50" width="50"></span></div>';
            }
            else{
                echo '<div class="car-key-img car_key_name"><span class="car_key_img" data-id="high_security">High Security</span></div>';
            }
            if(!empty($file_tibbe)){
                echo '<div class="car-key-img"><span class="car_key_img" data-id="tibbe"><img src="'.$target_dir_img.$file_tibbe.'" height="50" width="50"></span></div>';
            }
            else{
                echo '<div class="car-key-img car_key_name"><span class="car_key_img" data-id="tibbe">Tibbe</span></div>';
            }
            if(!empty($file_vats)){
                echo '<div class="car-key-img"><span class="car_key_img" data-id="vats"><img src="'.$target_dir_img.$file_vats.'" height="50" width="50"></span></div>';
            }
            else{
                echo '<div class="car-key-img car_key_name"><span class="car_key_img" data-id="vats">Vats</span></div>';
            }
            
            //echo '<div class="car-key-img car_key_name"><span class="car_key_img" data-id="clear">Clear All</span></div>';
            echo '<span class="chat_support">*Unsure? Start a live chat with support to help determine which key blade fits your car.</span>';
            echo '</div>';
        }
        
        if($want_more_than_one_key == 'yes'){
            echo '<input type="hidden" name="cost_to_cut_key" id="cost_to_cut_key" value="">';
            echo '<input type="hidden" name="cost_to_program_key" id="cost_to_program_key" value="">';
            
            
          $option = '<option value="">Choose number of keys</option>';
            for ($i =1; $i <= 5; $i++) {
                $option .= '<option value="' . $i . '" >' . $i . '</option>';
            }
            echo '<div id="keys_count" class="options_show"><label> How many car keys do you want made?  </label>'
            . '<select name="no_of_keys_want_to_made" id="no_of_keys_want_to_made">'
            . $option
            . '</select><span class="chat_support">*The locksmith will provide a quote for materials.</span></div>';
           
           
        }
        
        if ($will_owner_authorize_service == 'yes') {
            echo '<div class="options_show"><label class="working-keys"> Will the owner be able to authorize service? </label><input type="radio" name="will_owner_authorize_service" class="owner_authorize_service" value="yes" checked="checked"> Yes <input type="radio" name="will_owner_authorize_service" class="owner_authorize_service" value="no"> No </div>';
        }
        if ($ask_property_type == 'yes') {
            echo '<div class="options_show"><label> Type of property </label>'
            . '<select name="property_type" id="property_type">'
            . '<option value="" >Select</option>'
            . '<option value="Home" >Home</option>'
            . '<option value="Business" >Business</option>'
            . '<option value="Rental Property" >Rental Property</option>'
            . '</select> </div>';
        }

        if ($where_property_located == 'yes') {
            echo '<div class="options_show disabled" id="property_located"><label> Where is the property located? </label>'
            . '<span class="show_selected_property_address disabled" ></span>'
            . '<button  type="button" name="select_property_address" id="select_property_address" class="btn_class">Select Address</button></div>';

            echo '<input type="hidden" name="property_address" id="property_address" value="">';
            echo '<input type="hidden" name="property_latitude" id="property_latitude" value="">';
            echo '<input type="hidden" name="property_longitude" id="property_longitude" value="">'
            . '<input type="hidden" name="total_miles_property" class="total_miles_property" value="">'
            . '<input type="hidden" name="extra_amount_property" id="extra_amount_property" class="extra_amount_property" value="">';
        }
        if ($quantity_of_locks == 'yes') {
            $cylinders_included = get_post_meta($post_id, 'cylinders_included', true);
            $extra_per_cylinders_included_price = get_post_meta($post_id, 'extra_per_cylinders_included_price', true);
            $option = '<option value="">Choose Quantity of locks to rekey</option>';
            for ($i = 1; $i <= 99; $i++) {
                $option .= '<option value="' . $i . '" >' . $i . '</option>';
            }
            echo '<div class="options_show"><label> Quantity of locks to rekey </label>';
            if(!empty($file_locks)){
                echo '<div class="locks-img"><span class="locks_img" data-id="locks-example"><img src="'.$target_dir_img.$file_locks.'" height="50" width="50"></span></div>';
                echo '<span class="chat_support">*This is a lock cylinder example. Please check both sides of your door to get an accurate cylinder count. Note this deal only applies to standard locks, not high-security locks.</span>';
                echo '<br><br>';
            }
            
            
            echo '<select name="quantity_of_locks_to_rekey" id="quantity_of_locks_to_rekey">'
            . $option
            . '</select> <span class="chat_support">'
            . '*This deal includes up to ' . $cylinders_included . ' cylinders. If you have more that is OK, the locksmith will provide pricing details on site before the job starts.'
            . '</span>'
            . '<input type="hidden" name="extra_cylinder_price" id="extra_cylinder_price" class="extra_cylinder_price" value="">'
            . '<input type="hidden" name="extra_cylinder" id="extra_cylinder" class="extra_cylinder" value="">'
            . '<input type="hidden" name="extra_per_cylinders_price" id="extra_per_cylinders_price" class="extra_per_cylinders_price" value="">'
            . '</div>';
            echo '<div class="options_show cylinder_show_extra_price disabled">Extra cylinder price:<span id="show_total_amount_of_extra_cylinder"><div class="currency_total">' . get_woocommerce_currency_symbol() . '</div><div class="cylinder_price_total">0.00</div></span></div>';
        
        }
        /**********************Deadbolt Installation**************/
        if($type_of_installation == 'yes'){
            $option = '<option value="">Select</option>';
            $option .= '<option value="Fresh Install">Fresh Install</option>';
            $option .= '<option value="Replacement">Replacement</option>';
            echo '<div class="options_show"><label> Choose Type of Installation </label>'
            . '<select name="type_of_installation" id="type_of_installation">'
            . $option
            . '</select></div>';
        }
        if($who_supplies_deadbolts == 'yes'){
            $option = '<option value="">Select</option>';
            $option .= '<option value="I am">I am</option>';
            $option .= '<option value="I want locksmith grade deadbolts">I want locksmith grade deadbolts</option>';
            
            echo '<div class="options_show"><label>Choose Who Is Supplying Deadbolts</label>'
            . '<select name="who_is_supplying_deadbolts" id="who_is_supplying_deadbolts">'
            . $option
            . '</select><span class="chat_support">*The Locksmith can provide a quote for materials.</span></div>';
        }
        if($door_and_frame_type == 'yes'){
            $option = '<option value="">Select</option>';
            $option .= '<option value="Metal">Metal</option>';
            $option .= '<option value="Wood">Wood</option>';
            echo '<div class="options_show"><label>  Choose Door & Frame Type </label>'
            . '<select name="door_frame_type" id="door_frame_type">'
            . $option
            . '</select></div>';
        }
        if($quantity_of_locks_install == 'yes'){
            $option = '<option value="">Select</option>';
            for ($i =1; $i <= 20; $i++) {
                $option .= '<option value="' . $i . '" >' . $i . '</option>';
            }
            echo '<div class="options_show"><label> Choose Quantity Of Locks To Install  </label>'
            . '<select name="quantity_locks_to_install" id="quantity_locks_to_install">'
            . $option
            . '</select></div>';
        }
        
        
        echo '<div class="options_show disable" id="show_discount_price">Discount:<span id="show_discount_amount"><div class="currency_total">' . get_woocommerce_currency_symbol() . '</div><div class="price_discount">0.00</div></span></div>';
        echo '<div class="options_show">Total:<span id="show_total_amount_with_tax"><div class="currency_total">' . get_woocommerce_currency_symbol() . '</div><div class="price_total">0.00</div></span></div>';
        ?>
       <script>
               jQuery('.wc-bookings-date-picker').prepend('<span style="border: 0;color: #000;font-size: 14px;font-weight: 500;display:block;text-align:center;font-family: inherit;">Please Select Your Preferred Appointment Time</span>');
            var address_parent_div;
            var address_selected = 0;
            // jQuery(document).on("blur",".hasDatepicker,ul.block-picker .block", function(e) { console.log('aa') });
            jQuery('.wc-bookings-booking-form').on('change', 'input, select:not("#wc-bookings-form-start-time, #wc-bookings-form-end-time")', function (e) {
                setTimeout(function () {
                    console.log('vvvvvvvv');
                    if (jQuery('.wc-bookings-booking-cost').css('display') == 'none') {
                        var booking_price = 0;
                    } else {
                        var booking_price = parseFloat(jQuery('.wc-bookings-booking-cost').attr('data-raw-price'));
                    }
                     if (Number.isNaN(booking_price)) {
                            booking_price = 0;
                        }
                        if(booking_price!=''){
                       booking_price = parseFloat(booking_price);
                        }
                    if (booking_price != 0) {
                        jQuery('#booking_price').val(booking_price);

                        var service_location_val = jQuery('.wc-pao-addon-select').val();
                        ;
                        var service_location_price = jQuery('.wc-pao-addon-select').find(':selected').attr('data-price');
                        if (service_location_price > 0 && service_location_val != '') {
                            var amount = parseFloat(jQuery('#amount').val());
                             if (Number.isNaN(amount)) {
                            amount = 0;
                        }
                            var booking_price_with_tax = parseFloat(jQuery('#booking_price_with_tax').val());
                            var car_key_price = parseFloat(jQuery('#car_key_price').val());
                            var deadbolt_install_price=parseFloat(jQuery('#deadbolt_install_price').val());
                            var car_programming_fees = parseFloat(jQuery('#car_programming_fees').val());
                            var no_of_keys_want_to_made=jQuery('#no_of_keys_want_to_made').val();
                            var cost_to_cut_additional_key=parseFloat('<?php echo $cost_to_cut_additional_key; ?>');
                            var cost_to_program_additional_key=parseFloat('<?php echo $cost_to_program_additional_key; ?>');
                            var total_key_cost=parseFloat(cost_to_cut_additional_key+cost_to_program_additional_key);
                            var key_cost=parseFloat(no_of_keys_want_to_made*total_key_cost);
                                
                            if (amount == '') {
                                amount = 0;
                            }
                              if (Number.isNaN(booking_price_with_tax)) {
                            booking_price_with_tax = 0;
                        }
                            var element = document.querySelector("#map_display");
                        console.log(element);
                        var total = 0;
                        if (element.classList.contains("property")) {
                             var extra_amount =  jQuery('#extra_amount_property').val();
                             if (Number.isNaN(extra_amount)) {
                                extra_amount = 0;
                             }
                             var extra = parseFloat(extra_amount);
                             total = parseFloat(total + extra);
                             
                         }
                        else if (element.classList.contains("car")){
                            var extra_amount = jQuery('input[name="extra_amount[]"]').map(function () {
                                if (this.value != '') {
                                    return this.value;
                                }
                            }).get();
                            
                            if (Number.isNaN(extra_amount)) {
                                extra_amount = 0;
                            }
                            
                            for (var i = 0; i < extra_amount.length; i++) {
                                var extra = parseFloat(extra_amount[i]);
                                total = parseFloat(total + extra);
                            }
                        }
                            if (Number.isNaN(key_cost)) {
                                key_cost = 0;
                            }
                            if (Number.isNaN(car_key_price)) {
                                car_key_price = 0;
                            }
                            if (Number.isNaN(deadbolt_install_price)) {
                                deadbolt_install_price = 0;
                            }
                            if (Number.isNaN(car_programming_fees)) {
                                car_programming_fees = 0;
                            }
                            
                            var extra_cylinder_price =  parseFloat(jQuery('#extra_cylinder_price').val());
                             if (Number.isNaN(extra_cylinder_price)) {
                                  extra_cylinder_price = 0;
                               }
                            service_location_price = parseFloat(service_location_price);
                            var total_amount = parseFloat(amount + total);
                            var total_service_booking_amount = parseFloat(amount + total + booking_price +car_key_price + key_cost +extra_cylinder_price+car_programming_fees + deadbolt_install_price);
                            
                            if (booking_price_with_tax == '') {
                                var tax =0;
                            } else {
                                var tax = (total_service_booking_amount*booking_price_with_tax)/100;
                            }
                            jQuery('.main_loader').removeClass('disabled');
                            var total_service_booking = parseFloat(amount + total + booking_price + car_key_price + key_cost +extra_cylinder_price + car_programming_fees + deadbolt_install_price + tax);
                            console.log('calender change',total_service_booking);
                            var html = '<span class="currency"><?php echo $currenct_symbol; ?></span>' + total_amount.toFixed(2);
                            jQuery('.product-addon-totals .wc-pao-col2 .amount').html('');
                            jQuery('.product-addon-totals .wc-pao-col2 .amount').html(html);
                            var subtotal_html = '<p class="price">Subtotal <span class="amount"><span class="currency"><?php echo $currenct_symbol; ?></span>' + ( parseFloat(total_service_booking)).toFixed(2) + '</span></p>';
                            jQuery('.wc-pao-subtotal-line').html('');
                            jQuery('.wc-pao-subtotal-line').html(subtotal_html);
                            
                            
                             var discount_percentage=parseFloat('<?php echo $discount_on_deal; ?>');
                            if (Number.isNaN(discount_percentage)) {
                                discount_percentage = 0;
                            }
                            if(discount_percentage >0){
                                var discount_price=(total_service_booking_amount*discount_percentage)/100;
                                var show_total_price=total_service_booking_amount-discount_price;
                                total_service_booking=parseFloat(show_total_price+tax);
                                jQuery('#show_discount_price').removeClass('disable');
                            }
                            else{
                                jQuery('#show_discount_price').addClass('disable');
                                var discount_price=0;
                                var show_total_price=total_service_booking_amount;
                                total_service_booking=parseFloat(show_total_price+tax);
                            }
                            
                            jQuery('#show_discount_amount .price_discount').html(( parseFloat(discount_price)).toFixed(2));
                            jQuery('#show_total_amount_with_tax .price_total').html(( parseFloat(show_total_price)).toFixed(2));
                            jQuery('#total_price').val(parseFloat(show_total_price).toFixed(2));
                            jQuery('#discount_price').val(parseFloat(discount_price).toFixed(2));
                            jQuery('#sub_total_price').val(parseFloat(total_service_booking_amount).toFixed(2));
                            jQuery('.main_loader').addClass('disabled');
                        }
                        else{
                            jQuery('.main_loader').removeClass('disabled');
                            var car_key_price = parseFloat(jQuery('#car_key_price').val());
                            var deadbolt_install_price=parseFloat(jQuery('#deadbolt_install_price').val());
                            var car_programming_fees = parseFloat(jQuery('#car_programming_fees').val());
                            var no_of_keys_want_to_made=jQuery('#no_of_keys_want_to_made').val();
                            var cost_to_cut_additional_key=parseFloat('<?php echo $cost_to_cut_additional_key; ?>');
                            var cost_to_program_additional_key=parseFloat('<?php echo $cost_to_program_additional_key; ?>');
                            var total_key_cost=parseFloat(cost_to_cut_additional_key+cost_to_program_additional_key);
                            var key_cost=parseFloat(no_of_keys_want_to_made*total_key_cost);
                               
                            
                             var extra_cylinder_price =  parseFloat(jQuery('#extra_cylinder_price').val());
                             if (Number.isNaN(extra_cylinder_price)) {
                                  extra_cylinder_price = 0;
                               }
                             
                             if (Number.isNaN(car_key_price)) {
                                car_key_price = 0;
                             }
                             if (Number.isNaN(deadbolt_install_price)) {
                                deadbolt_install_price = 0;
                             }
                             if (Number.isNaN(car_programming_fees)) {
                                car_programming_fees = 0;
                             }
                             if (Number.isNaN(key_cost)) {
                                key_cost = 0;
                             }
                             var total_service_booking_amount=parseFloat(booking_price+car_key_price+key_cost+extra_cylinder_price+car_programming_fees+deadbolt_install_price);
                            
                            var discount_percentage=parseFloat('<?php echo $discount_on_deal; ?>');
                            if (Number.isNaN(discount_percentage)) {
                                discount_percentage = 0;
                            }
                            if(discount_percentage >0){
                                var discount_price=(total_service_booking_amount*discount_percentage)/100;
                                var show_total_price=parseFloat(total_service_booking_amount-discount_price);
                                var total_service_booking=parseFloat(show_total_price);
                                jQuery('#show_discount_price').removeClass('disable');
                            }
                            else{
                                jQuery('#show_discount_price').addClass('disable');
                                var discount_price=0;
                                var show_total_price=parseFloat(total_service_booking_amount);
                                var total_service_booking=parseFloat(show_total_price);
                            }
                            
                            jQuery('#show_discount_amount .price_discount').html(( parseFloat(discount_price)).toFixed(2));
                            jQuery('#show_total_amount_with_tax .price_total').html(( parseFloat(show_total_price)).toFixed(2));
                            jQuery('#total_price').val(parseFloat(show_total_price).toFixed(2));
                            jQuery('#discount_price').val(parseFloat(discount_price).toFixed(2));
                            jQuery('#sub_total_price').val(parseFloat(total_service_booking_amount).toFixed(2));
                            jQuery('.main_loader').addClass('disabled');
                            }
                    }
                }, 3000);

            });

            jQuery(document).ready(function () {
            jQuery('.wc-pao-addon-select option:contains("None")').text('Choose service location');
                var show_alert;
               
                jQuery('#no_of_keys_want_to_made').change(function(){
                    var cost_to_cut_additional_key=parseFloat('<?php echo $cost_to_cut_additional_key; ?>');
                    var cost_to_program_additional_key=parseFloat('<?php echo $cost_to_program_additional_key; ?>');
                    jQuery("#cost_to_cut_key").val(cost_to_cut_additional_key);
                    jQuery("#cost_to_program_key").val(cost_to_program_additional_key);
                    calculate_amount_car_key_price();
                });
                jQuery(document).on('change','#type_of_installation , #who_is_supplying_deadbolts',function(){
                    var type_of_installation=jQuery('#type_of_installation').val();
                    var who_is_supplying_deadbolts=jQuery('#who_is_supplying_deadbolts').val();
                    if(type_of_installation !='' && who_is_supplying_deadbolts !=''){
                        if(type_of_installation == 'Fresh Install' && who_is_supplying_deadbolts == 'I am'){
                            var deadbolt_install_price='<?php echo $customer_fresh_install_deadbolt ?>';
                        }
                        else if(type_of_installation == 'Fresh Install' && who_is_supplying_deadbolts == 'I want locksmith grade deadbolts'){
                            var deadbolt_install_price='<?php echo $locksmith_fresh_install_deadbolt ?>';
                        }
                        else if(type_of_installation == 'Replacement' && who_is_supplying_deadbolts == 'I am'){
                            var deadbolt_install_price='<?php echo $customer_replaced_deadbolt ?>';
                        }
                        else if(type_of_installation == 'Replacement' && who_is_supplying_deadbolts == 'I want locksmith grade deadbolts'){
                            var deadbolt_install_price='<?php echo $locksmith_replaced_deadbolt ?>';
                        }
                        jQuery('#deadbolt_install_price').val(deadbolt_install_price);
                    }
                    else{
                        jQuery('#deadbolt_install_price').val(0);  
                    }
                    calculate_amount_car_key_price();
                });
                jQuery('.single_add_to_cart_button').click(function (e) {
                    e.preventDefault();
                    //var serviceable_car_maker = jQuery('#serviceable_car_maker').val(); 
                    // var serviceable_car_model = jQuery('#serviceable_car_model').val(); 
                    //var serviceable_car_year = jQuery('#serviceable_car_year').val(); 
                    //var latitude=jQuery('#my_location_latitude').val();
                    // var longitude=jQuery('#my_location_longitude').val();
                    //var address=jQuery('#my_location_address').val();
                    var maker_val = 1;
                    var model_val = 1;
                    var year_val = 1;
                    var address_val = 1;
                    var latitude_val = 1;
                    var longitude_val = 1;
                    var redirect_flag = 0;
                    var total_miles_val = 1;
                    var serviceable_car_maker = jQuery('select[name="serviceable_car_maker[]"]').map(function () {
                        if (this.value == '') {
                            maker_val = 0;
                        }
                        return this.value; // $(this).val()
                    }).get();
                    var serviceable_car_model = jQuery('select[name="serviceable_car_model[]"]').map(function () {
                        if (this.value == '') {
                            model_val = 0;
                        }
                        return this.value; // $(this).val()
                    }).get();
                    var serviceable_car_year = jQuery('select[name="serviceable_car_year[]"]').map(function () {
                        if (this.value == '') {
                            year_val = 0;
                        }
                        return this.value; // $(this).val()
                    }).get();
                    var address = jQuery('input[name="my_location_address[]"]').map(function () {
                        if (this.value == '') {
                            address_val = 0;
                        }
                        return this.value; // $(this).val()
                    }).get();

                    var latitude = jQuery('input[name="my_location_latitude[]"]').map(function () {
                        if (this.value == '') {
                            latitude_val = 0;
                        }
                        return this.value; // $(this).val()
                    }).get();
                    var longitude = jQuery('input[name="my_location_longitude[]"]').map(function () {
                        if (this.value == '') {
                            longitude_val = 0;
                        }
                        return this.value; // $(this).val()
                    }).get();
                    var totalmiles = jQuery('input[name="total_miles[]"]').map(function () {
                        if (this.value > 0) {
                            total_miles_val = 0;
                        }
                        return this.value; // $(this).val()
                    }).get();

                    if (total_miles_val == 0) {
                        alert("This address is outside of our service area. Please start a live chat for more help.");
                        redirect_flag = 1;
                    } else {
                        var booking_servicable = jQuery('#booking_servicable').val();
                        if (booking_servicable == '0') {
                            alert("This address is outside of our service area. Please start a live chat for more help.");
                            redirect_flag = 1;
                        }
                    }

                    if (maker_val == 0 || model_val == 0 || year_val == 0) {
                        alert('Please select your car type');
                        redirect_flag = 1;
                    }
                    var service_location_val = jQuery('.wc-pao-addon-select').val();
                    var service_location_price = jQuery('.wc-pao-addon-select').find(':selected').attr('data-price');
                    if (service_location_price > 0 && service_location_val != '') {

                        if (latitude_val == 0 || longitude_val == 0 || address_val == 0) {
                            alert('Please Select your car location.');
                            redirect_flag = 1;
                        }

                    }

                    var authorized_service = jQuery("input[name='will_owner_authorize_service']:checked").val();
                    if (authorized_service == 'no') {
                        alert('Proof of ownership must be provided to the locksmith at the time of service. If this is possible, please select yes to continue. If not, please call <?php echo $phone; ?>');
                        redirect_flag = 1;
                    }
                    var when_start_car = jQuery('#when_start_car').val();
                    if (when_start_car === '') {
                        alert('Please select when you start your car.');
                        redirect_flag = 1;
                    }
                    var need_key_to_work = jQuery('#need_key_to_work').val();
                    if (need_key_to_work === '') {
                        alert('Please select Do you need a key to work.');
                        redirect_flag = 1;
                    }
                    var car_key_type = jQuery('#car_key_type').val();
                    var car_key_price = jQuery('#car_key_price').val();
                    if (car_key_type === '' && car_key_price ==='') {
                         alert('Please select car key look like.');
                         redirect_flag = 1;
                    }
                    
                    
                    var no_of_keys_want_to_made=jQuery('#no_of_keys_want_to_made').val();
                    if(no_of_keys_want_to_made == ''){
                        alert('Please select how many car keys do you want made?');
                        redirect_flag = 1; 
                    }
                     
                    
                    
                    var property_type = jQuery('#property_type').val();
                    if (property_type === '') {
                        alert('Please select property type.');
                        redirect_flag = 1;
                    }
                    var quantity_locks = jQuery('#quantity_of_locks_to_rekey').val();
                    if (quantity_locks === '') {
                        alert('Please select quantity of locks to rekey.');
                        redirect_flag = 1;
                    }
                    var service_location_val = jQuery('.wc-pao-addon-select').val();
                    var service_location_price = jQuery('.wc-pao-addon-select').find(':selected').attr('data-price');
                    if (service_location_price > 0 && service_location_val != '') {

                        var property_latitude = jQuery('#property_latitude').val();
                        var property_longitude = jQuery('#property_longitude').val();
                        if (property_latitude === '' && property_longitude === '') {
                            alert('Please select property address.');
                            redirect_flag = 1;
                        }
                    }
                    
                    var type_of_installation = jQuery('#type_of_installation').val();
                    if (type_of_installation === '') {
                        alert('Please Choose Type of Insatallation.');
                        redirect_flag = 1;
                    }
                    var who_is_supplying_deadbolts = jQuery('#who_is_supplying_deadbolts').val();
                    if (who_is_supplying_deadbolts === '') {
                        alert('Please Choose Who is Supplying Deadbolts.');
                        redirect_flag = 1;
                    }
                    var door_frame_type = jQuery('#door_frame_type').val();
                    if (door_frame_type === '') {
                        alert('Please Choose Door & frame Type.');
                        redirect_flag = 1;
                    }
                    var quantity_locks_to_install = jQuery('#quantity_locks_to_install').val();
                    if (quantity_locks_to_install === '') {
                        alert('Please Choose Quantity of locks to Install.');
                        redirect_flag = 1;
                    }
                    
                    
                    var that = jQuery(this);
                    jQuery.ajax({
                        url: '<?php echo add_query_arg('action', 'blsd_check_cart_vendor_product', $WCMp->ajax_url()); ?>',
                        type: "post",
                        data: {post_id: '<?php echo $post_id; ?>'},
                        success: function (resultData) {
                            if (resultData == 'failed') {
                                alert('Please complete the checkout of your deal before purchasing deals from another locksmith.');
                                redirect_flag = 1;
                            }
                            if (redirect_flag == 0) {
                                var form_class = that.parent('form').attr('class');
                                jQuery('.' + form_class).submit();
                            }
                        }
                    });


                });

                jQuery('#quantity_of_locks_to_rekey').change(function(){
                    var quantity_of_locks_to_rekey=parseInt(jQuery('#quantity_of_locks_to_rekey').val());
                    var included_cylinders='<?php echo $cylinders_included; ?>';
                    var extra_per_cylinders_price='<?php echo $extra_per_cylinders_included_price; ?>';
                    if(included_cylinders < quantity_of_locks_to_rekey){
                        var extra_cylinders=parseInt(quantity_of_locks_to_rekey - included_cylinders);
                        var extra_cylinders_price=parseInt(extra_cylinders*extra_per_cylinders_price);
                        console.log('extra_cylinders', extra_cylinders);
                        console.log('extra_cylinders_price', extra_cylinders_price);
                        
                        calculate_final_amount(extra_cylinders,extra_per_cylinders_price);
                        
                        
                    }
                    else{
                     calculate_final_amount(0,extra_per_cylinders_price);   
                    }
                });
                function calculate_final_amount(extra_cylinders,extra_per_cylinders_price){
                    var service_location_val = jQuery('.wc-pao-addon-select').val();
                    var service_location_price = jQuery('.wc-pao-addon-select').find(':selected').attr('data-price');
                    if (Number.isNaN(service_location_price)) {
                         service_location_price = 0;
                     }
                    var amount = parseFloat(jQuery('#amount').val());
                    var booking_price = parseFloat(jQuery('#booking_price').val());
                    if (Number.isNaN(booking_price)) {
                      booking_price = 0;
                    }
                    var booking_price_with_tax = parseFloat(jQuery('#booking_price_with_tax').val());
                    if (booking_price === '') {
                        booking_price = 0;
                    }
                    booking_price = parseFloat(booking_price);
                    var element = document.querySelector("#map_display");
                    var total = 0;
                    if (element.classList.contains("property")) {
                         var extra_amount =  jQuery('#extra_amount_property').val();
                         if (Number.isNaN(extra_amount)) {
                            extra_amount = 0;
                         }
                         var extra = parseFloat(extra_amount);
                         total = parseFloat(total + extra);
                         
                    }
                        
                        service_location_price = parseFloat(service_location_price);
                        if (Number.isNaN(amount)) {
                            amount = 0;
                        }
                        if (Number.isNaN(total)) {
                            total = 0;
                        }
                        if (Number.isNaN(service_location_price)) {
                            service_location_price = 0;
                        }
                        
                         if (Number.isNaN(booking_price_with_tax)) {
                            booking_price_with_tax = 0;
                        }
                        var extra_cylinder_price=extra_cylinders*extra_per_cylinders_price;
                        jQuery('#extra_cylinder_price').val(extra_cylinder_price);
                        jQuery('#extra_cylinder').val(extra_cylinders);
                        jQuery('#extra_per_cylinders_price').val(extra_per_cylinders_price);
                        console.log(amount, total, service_location_price, booking_price, booking_price_with_tax,extra_cylinder_price);
                        var total_amount = parseFloat(total + service_location_price);
                        if(service_location_price >0){
                          var total_service_booking_amount = parseFloat( total + service_location_price + booking_price+extra_cylinder_price);
                          console.log('total_service_booking_amount',total_service_booking_amount);
                            if (booking_price_with_tax === '') {
                                var tax=0;
                            } else {
                                var tax = (total_service_booking_amount*booking_price_with_tax)/100;
                            }
                            var total_service_booking = parseFloat( total + service_location_price + booking_price +extra_cylinder_price +tax);

                        }
                        else{
                           var total_service_booking_amount = parseFloat(booking_price+extra_cylinder_price);
                           var total_service_booking = parseFloat(booking_price +extra_cylinder_price);
                        }
                        
                        
                         jQuery('.main_loader').removeClass('disabled');
                        setTimeout(function () {
                            
                            var html = '<span class="currency"><?php echo $currenct_symbol; ?></span>' + total_amount.toFixed(2);
                            jQuery('.product-addon-totals .wc-pao-col2 .amount').html('');
                            jQuery('.product-addon-totals .wc-pao-col2 .amount').html(html);
                            if (booking_price != 0) {
                                var subtotal_html = '<p class="price">Subtotal <span class="amount"><span class="currency"><?php echo $currenct_symbol; ?></span>' + ( parseFloat(total_service_booking)).toFixed(2) + '</span></p>';
                                jQuery('.wc-pao-subtotal-line').html('');
                                jQuery('.wc-pao-subtotal-line').html(subtotal_html);
                            }
                            console.log(total_service_booking,'total_service_booking');
                            
                            
                            var discount_percentage=parseFloat('<?php echo $discount_on_deal; ?>');
                            if (Number.isNaN(discount_percentage)) {
                                discount_percentage = 0;
                            }
                            if(discount_percentage >0){
                                var discount_price=(total_service_booking_amount*discount_percentage)/100;
                                var show_total_price=total_service_booking_amount-discount_price;
                                if(service_location_price >0){
                                    total_service_booking=parseFloat(show_total_price+tax);
                                }
                                else{
                                    total_service_booking=parseFloat(show_total_price);
                                }
                                jQuery('#show_discount_price').removeClass('disable');
                            }
                            else{
                                jQuery('#show_discount_price').addClass('disable');
                                var discount_price=0;
                                var show_total_price=total_service_booking_amount;
                                if(service_location_price >0){
                                    total_service_booking=parseFloat(show_total_price+tax);
                                }
                                else{
                                    total_service_booking=parseFloat(show_total_price);
                                }
                            }
                            
                            jQuery('#show_discount_amount .price_discount').html(( parseFloat(discount_price)).toFixed(2));
                            jQuery('#show_total_amount_with_tax .price_total').html(( parseFloat(show_total_price)).toFixed(2));
                            jQuery('#total_price').val(parseFloat(show_total_price).toFixed(2));
                            jQuery('#discount_price').val(parseFloat(discount_price).toFixed(2));
                            jQuery('#sub_total_price').val(parseFloat(total_service_booking_amount).toFixed(2));
                            if(extra_cylinder_price >0){
                                jQuery('.cylinder_show_extra_price').removeClass('disabled');
                                jQuery('#show_total_amount_of_extra_cylinder .cylinder_price_total').html(( parseFloat(extra_cylinder_price)).toFixed(2));
                            }
                            else{
                                jQuery('.cylinder_show_extra_price').addClass('disabled');
                            }
                            jQuery('.main_loader').addClass('disabled');
                        }, 3000);
                }
                jQuery('#serviceable_car').click(function () {
                    clearTimeout(show_alert);
                });
                jQuery('.serviceable_car_maker').focusout(function () {
                    var service_car = jQuery(this).val();
                    if (service_car === '') {
                        var show_alert = setTimeout(function () {
                             alert("If you don't see your car listed, start a live chat for help."); 
                        }, 10000);
                    }
                });
                jQuery('.add_more').click(function () {
                    var service_location_val = jQuery('.wc-pao-addon-select').val();
                    var service_location_price = jQuery('.wc-pao-addon-select').find(':selected').attr('data-price');

                    var html = '<div class="select_service_car"> <select class="serviceable_car_maker" name="serviceable_car_maker[]" ><?php echo $option_maker; ?></select>';
                    html = html + '<select class="serviceable_car_model" name="serviceable_car_model[]" ><?php echo $option_model; ?></select>';
                    html = html + '<select class="serviceable_car_year" name="serviceable_car_year[]"><?php echo $option_year; ?></select><div class="remove_row">-</div>';
                    html = html + '<input type="hidden" name="car_programming[]" class="car_programming" value="">';
                    html = html + '<input type="hidden" name="car_type[]" class="car_type" value="">';
                    html = html + '<input type="hidden" name="car_message[]" class="car_message" value="">';
                    
                    html = html + '<span class="show_selected_address disabled" ></span>';
                    html = html + '<div class="select_car_address disabled"><button type="button" name="vehical_location" class="btn_class" >Set Vehicle Service Location</button>';
                    html = html + '<input type="hidden" name="my_location_address[]" class="my_location_address" value="">';
                    html = html + '<input type="hidden" name="my_location_latitude[]" class="my_location_latitude" value="">';
                    html = html + '<input type="hidden" name="my_location_longitude[]" class="my_location_longitude" value="">';
                    html = html + '<input type="hidden" name="extra_amount[]" class="extra_amount" value="">';
                    html = html + '<input type="hidden" name="total_miles[]" class="total_miles" value="">';
                    html = html + '</div> </div>';
                    jQuery('#select_car').append(html);
                    if (service_location_price > 0 && service_location_val != '') {
                        jQuery('.select_car_address').removeClass('disabled');
                    }
                });
                jQuery(document).on('click', '.remove_row', function () {
                    jQuery(this).parent().remove();
                });
                jQuery(document).on('change', '.serviceable_car_maker', function () {
                    var serviceable_car_maker = jQuery(this).val();
                    var maker = jQuery(this);
                    jQuery('#loading').removeClass('disabled');
                    jQuery.ajax({
                        url: '<?php echo add_query_arg('action', 'blsd_get_car_model_year', $WCMp->ajax_url()); ?>',
                        type: "post",
                        data: {maker: serviceable_car_maker, post_id: '<?php echo $post_id; ?>'},
                        success: function (resultData) {
                            maker.parent().children('.serviceable_car_model').html(resultData);
                            jQuery('#loading').addClass('disabled');
                        }
                    });

                });

                jQuery(document).on('change', '.serviceable_car_model', function () {
                    var serviceable_car_maker = jQuery(this).parent().children('.serviceable_car_maker').val();
                    var serviceable_car_model = jQuery(this).val();
                    var model = jQuery(this);
                    jQuery('#loading').removeClass('disabled');
                    jQuery.ajax({
                        url: '<?php echo add_query_arg('action', 'blsd_get_car_model_year', $WCMp->ajax_url()); ?>',
                        type: "post",
                        data: {maker: serviceable_car_maker, model: serviceable_car_model, post_id: '<?php echo $post_id; ?>'},
                        success: function (resultData) {
                            model.parent().children('.serviceable_car_year').html(resultData);
                            jQuery('#loading').addClass('disabled');
                        }
                    });

                });
                jQuery(document).on('change', '.serviceable_car_year', function () {
                var serviceable_car_maker = jQuery(this).parent().children('.serviceable_car_maker').val();
                var serviceable_car_model = jQuery(this).parent().children('.serviceable_car_model').val();    
                var serviceable_car_year = jQuery(this).val();  
                var year_this = jQuery(this);
                 jQuery('#loading').removeClass('disabled');
                    jQuery.ajax({
                        url: '<?php echo add_query_arg('action', 'blsd_get_car_fee', $WCMp->ajax_url()); ?>',
                        type: "post",
                        data: {maker: serviceable_car_maker, model: serviceable_car_model, year:serviceable_car_year, post_id: '<?php echo $post_id; ?>'},
                        success: function (resultData) {
                           var array = JSON.parse(resultData);
                           var programming=array.programming;
                           var type=array.type;
                           var message=array.message;
                           year_this.parent().children('.car_programming').val(programming);
                           year_this.parent().children('.car_type').val(type);
                           year_this.parent().children('.car_message').val(message);
                           calculate_programming_fee();
                           jQuery('#loading').addClass('disabled');
                        }
                    });
                });
                
                function calculate_programming_fee(){
                
                
                
                var car_programming = jQuery('input[name="car_programming[]"]').map(function () {
                    return this.value;
                }).get();
                var car_type = jQuery('input[name="car_type[]"]').map(function () {
                    return this.value;
                }).get();
                var car_message = jQuery('input[name="car_message[]"]').map(function () {
                    return this.value;
                }).get();
                var car_programming_fees=0;
                var programming_fees=0;
                var vats_fees=0;
                for (var i = 0; i < car_programming.length; i++) {
                    if(car_programming[i] == 'Yes'){
                        programming_fees=parseFloat(programming_fees+<?php echo $car_programming_fee; ?>);
                        car_programming_fees=parseFloat(car_programming_fees+<?php echo $car_programming_fee; ?>);
                    }
                    else if(car_programming[i] == 'No'){
                        car_programming_fees=parseFloat(car_programming_fees+0);
                        programming_fees=parseFloat(programming_fees+0);
                    }
                    else if(car_programming[i] == 'Split'){
                        programming_fees=parseFloat(programming_fees+0);
                        car_programming_fees=parseFloat(car_programming_fees+0);
                        alert(car_message[i]);
                    }
                    if(car_type[i] == 'Vats'){
                        vats_fees=parseFloat(vats_fees+<?php echo $car_vats_programming_fee; ?>);
                        car_programming_fees=parseFloat(car_programming_fees+<?php echo $car_vats_programming_fee; ?>);
                    }
                    if(car_type[i] == 'Transponder optional' || car_type[i] == 'Transponder Optional'){
                        var message='The transponder system was an option when your car was built. Please chat with support to determine the right deal for you.';
                        if(car_message[i] == ''){
                            alert(message);
                        }
                        else{
                            alert(car_message[i]);
                        }
                    }
                    
                  
                }
                if (Number.isNaN(car_programming_fees)) {
                    car_programming_fees = 0;
                }
                if (Number.isNaN(programming_fees)) {
                    programming_fees = 0;
                }
                if (Number.isNaN(vats_fees)) {
                    vats_fees = 0;
                }
                
                jQuery('#car_programming_fees').val(car_programming_fees); 
                jQuery('#cprogramming_fees').val(programming_fees); 
                jQuery('#cvats_fees').val(vats_fees); 
                calculate_amount_car_key_price();
                }

                jQuery('.wc-pao-addon-select').change(function () {
                    var service_location_val = jQuery(this).val();
                    var service_location_price = jQuery(this).find(':selected').attr('data-price');
                    if (Number.isNaN(service_location_price)) {
                         service_location_price = 0;
                     }
                    if (service_location_price > 0 && service_location_val != '') {
                         jQuery('.main_loader').removeClass('disabled');
                        var amount = parseFloat(jQuery('#amount').val());
                        var booking_price = parseFloat(jQuery('#booking_price').val());
                        var car_key_price = parseFloat(jQuery('#car_key_price').val());
                        var deadbolt_install_price=parseFloat(jQuery('#deadbolt_install_price').val());
                        var car_programming_fees = parseFloat(jQuery('#car_programming_fees').val());
                        var no_of_keys_want_to_made=jQuery('#no_of_keys_want_to_made').val();
                        var cost_to_cut_additional_key=parseFloat('<?php echo $cost_to_cut_additional_key; ?>');
                        var cost_to_program_additional_key=parseFloat('<?php echo $cost_to_program_additional_key; ?>');
                        var total_key_cost=parseFloat(cost_to_cut_additional_key+cost_to_program_additional_key);
                        var key_cost=parseFloat(no_of_keys_want_to_made*total_key_cost);
                            
                          if (Number.isNaN(booking_price)) {
                            booking_price = 0;
                          }
                          if (Number.isNaN(car_key_price)) {
                            car_key_price = 0;
                          }
                          if (Number.isNaN(deadbolt_install_price)) {
                            deadbolt_install_price = 0;
                          }
                          if (Number.isNaN(car_programming_fees)) {
                            car_programming_fees = 0;
                          }
                          if (Number.isNaN(key_cost)) {
                            key_cost = 0;
                          }
                          var booking_price_with_tax = parseFloat(jQuery('#booking_price_with_tax').val());
                        
                        if (booking_price == '') {
                            booking_price = 0;
                        }
                        booking_price = parseFloat(booking_price);
                        
                        var element = document.querySelector("#map_display");
                        console.log(element);
                       var total = 0;
                        if (element.classList.contains("property")) {
                             var extra_amount =  jQuery('#extra_amount_property').val();
                             if (Number.isNaN(extra_amount)) {
                                extra_amount = 0;
                             }
                             var extra = parseFloat(extra_amount);
                             total = parseFloat(total + extra);
                        }
                        else if (element.classList.contains("car")){
                            var extra_amount = jQuery('input[name="extra_amount[]"]').map(function () {
                                if (this.value != '') {
                                    return this.value;
                                }
                            }).get();
                            
                            if (Number.isNaN(extra_amount)) {
                                extra_amount = 0;
                            }
                            for (var i = 0; i < extra_amount.length; i++) {
                                var extra = parseFloat(extra_amount[i]);
                                total = parseFloat(total + extra);
                            }
                        }
                        
                        
                        var extra_cylinder_price =  parseFloat(jQuery('#extra_cylinder_price').val());
                        if (Number.isNaN(extra_cylinder_price)) {
                             extra_cylinder_price = 0;
                          }
                          
                        service_location_price = parseFloat(service_location_price);
                        if (Number.isNaN(amount)) {
                            amount = 0;
                        }
                        if (Number.isNaN(total)) {
                            total = 0;
                        }
                        if (Number.isNaN(service_location_price)) {
                            service_location_price = 0;
                        }
                        
                         if (Number.isNaN(booking_price_with_tax)) {
                            booking_price_with_tax = 0;
                        }
                        
                        console.log(amount, total, service_location_price, booking_price, booking_price_with_tax);
                        var total_amount = parseFloat(amount + total + service_location_price);
                        var total_service_booking_amount = parseFloat(amount + total + service_location_price + booking_price + car_key_price + key_cost + extra_cylinder_price + car_programming_fees + deadbolt_install_price);
                        console.log('total_service_booking_amount',total_service_booking_amount);
                        if (booking_price_with_tax == '') {
                            var tax=0;
                        } else {
                            var tax = (total_service_booking_amount*booking_price_with_tax)/100;
                        }
                        var total_service_booking = parseFloat(amount + total + service_location_price + booking_price + car_key_price +key_cost +extra_cylinder_price +car_programming_fees + deadbolt_install_price +tax);

                        jQuery('#amount').val(total_amount);
                        setTimeout(function () {
                            var html = '<span class="currency"><?php echo $currenct_symbol; ?></span>' + total_amount.toFixed(2);
                            jQuery('.product-addon-totals .wc-pao-col2 .amount').html('');
                            jQuery('.product-addon-totals .wc-pao-col2 .amount').html(html);
                            if (booking_price != 0) {
                                var subtotal_html = '<p class="price">Subtotal <span class="amount"><span class="currency"><?php echo $currenct_symbol; ?></span>' + ( parseFloat(total_service_booking)).toFixed(2) + '</span></p>';
                                jQuery('.wc-pao-subtotal-line').html('');
                                jQuery('.wc-pao-subtotal-line').html(subtotal_html);
                            }
                            console.log(total_service_booking,'total_service_booking');
                            
                            var discount_percentage=parseFloat('<?php echo $discount_on_deal; ?>');
                            if (Number.isNaN(discount_percentage)) {
                                discount_percentage = 0;
                            }
                            if(discount_percentage >0){
                                var discount_price=(total_service_booking_amount*discount_percentage)/100;
                                var show_total_price=total_service_booking_amount-discount_price;
                                total_service_booking=parseFloat(show_total_price+tax);
                                jQuery('#show_discount_price').removeClass('disable');
                            }
                            else{
                                jQuery('#show_discount_price').addClass('disable');
                                var discount_price=0;
                                var show_total_price=total_service_booking_amount;
                                total_service_booking=parseFloat(show_total_price+tax);
                            }
                            
                            jQuery('#show_discount_amount .price_discount').html(( parseFloat(discount_price)).toFixed(2));
                            jQuery('#show_total_amount_with_tax .price_total').html(( parseFloat(show_total_price)).toFixed(2));
                            jQuery('#total_price').val(parseFloat(show_total_price).toFixed(2));
                            jQuery('#discount_price').val(parseFloat(discount_price).toFixed(2));
                            jQuery('#sub_total_price').val(parseFloat(total_service_booking_amount).toFixed(2));
                            
                             jQuery('.main_loader').addClass('disabled');
                        }, 3000);
                        //jQuery('#map_display').addClass('show-modal');
                        //jQuery('#map_display').addClass('car');
                       // jQuery('#map_display').removeClass('property');
                        //jQuery('#map_display').removeClass('disabled');  
                        jQuery('.select_car_address').removeClass('disabled');
                        jQuery('#property_located').removeClass('disabled');
                    } else {
                        jQuery('.main_loader').removeClass('disabled');
                        jQuery('#amount').val('');
                        var booking_price = jQuery('#booking_price').val();
                        var car_key_price = parseFloat(jQuery('#car_key_price').val());
                        var deadbolt_install_price = parseFloat(jQuery('#deadbolt_install_price').val());
                        var car_programming_fees = parseFloat(jQuery('#car_programming_fees').val());
                        var no_of_keys_want_to_made=jQuery('#no_of_keys_want_to_made').val();
                        var cost_to_cut_additional_key=parseFloat('<?php echo $cost_to_cut_additional_key; ?>');
                        var cost_to_program_additional_key=parseFloat('<?php echo $cost_to_program_additional_key; ?>');
                        var total_key_cost=parseFloat(cost_to_cut_additional_key+cost_to_program_additional_key);
                        var key_cost=parseFloat(no_of_keys_want_to_made*total_key_cost);
                        if (Number.isNaN(booking_price)) {
                            booking_price = 0;
                        }
                         if (Number.isNaN(car_key_price)) {
                            car_key_price = 0;
                        }
                         if (Number.isNaN(deadbolt_install_price)) {
                            deadbolt_install_price = 0;
                        }
                         if (Number.isNaN(car_programming_fees)) {
                            car_programming_fees = 0;
                        }
                         if (Number.isNaN(key_cost)) {
                            key_cost = 0;
                        }
                         if (booking_price == '') {
                            booking_price = 0;
                        }
                        var total_service_booking = 0.00;
                        if(booking_price != ''){
                        total_service_booking=parseFloat(total_service_booking+booking_price);
                        }
                        var extra_cylinder_price =  parseFloat(jQuery('#extra_cylinder_price').val());
                        if (Number.isNaN(extra_cylinder_price)) {
                             extra_cylinder_price = 0;
                          }
                          total_service_booking=parseFloat(total_service_booking);
                         total_service_booking=parseFloat(total_service_booking+extra_cylinder_price+car_key_price+key_cost+car_programming_fees+deadbolt_install_price);
                        
                        var discount_percentage=parseFloat('<?php echo $discount_on_deal; ?>');
                            if (Number.isNaN(discount_percentage)) {
                                discount_percentage = 0;
                            }
                            if(discount_percentage >0){
                                var discount_price=(total_service_booking*discount_percentage)/100;
                                var show_total_price=total_service_booking-discount_price;
                                if(total_service_booking >0){
                                jQuery('#show_discount_price').removeClass('disable');
                                }
                            }
                            else{
                                jQuery('#show_discount_price').addClass('disable');
                                var discount_price=0;
                                var show_total_price=total_service_booking;
                            }
                            
                            jQuery('#show_discount_amount .price_discount').html(( parseFloat(discount_price)).toFixed(2));
                            jQuery('#show_total_amount_with_tax .price_total').html(( parseFloat(show_total_price)).toFixed(2));
                            jQuery('#total_price').val(parseFloat(show_total_price).toFixed(2));
                            jQuery('#discount_price').val(parseFloat(discount_price).toFixed(2));
                            jQuery('#sub_total_price').val(parseFloat(total_service_booking).toFixed(2));
                            jQuery('.select_car_address').addClass('disabled');
                            jQuery('.main_loader').addClass('disabled');
                            jQuery('#property_located').addClass('disabled');
                    }
                });

                jQuery(document).on('click', '.select_car_address', function () {
                    address_parent_div = jQuery(this);
                    jQuery('#map_display').addClass('show-modal');
                    jQuery('#map_display').addClass('car');
                    jQuery('#map_display').removeClass('property');
                    jQuery('#map_display').removeClass('disabled');
                    jQuery('#pac-input').val('');

                });
                jQuery('#select_property_address').click(function () {
                    console.log('property add');
                    address_parent_div = jQuery(this);
                    jQuery('#map_display').addClass('show-modal');
                    jQuery('#map_display').addClass('property');
                    jQuery('#map_display').removeClass('car');
                    jQuery('#map_display').removeClass('disabled');
                    jQuery('#pac-input').val('');

                });

                jQuery('.close-button').on('click', function () {
                    jQuery('#map_display').addClass('disabled');
                });
                jQuery('#done').on('click', function () {

                    var element = document.querySelector("#map_display");

                    if (element.classList.contains("property")) {

                        workForProperty();
                        // var car_latitude =jQuery('#property_address').val();
                    } else {
                         jQuery('.main_loader').removeClass('disabled');
                        var total_service_booking_amount=0;
                        var car_latitude = address_parent_div.parent().children('.select_car_address').children('.my_location_latitude').val();
                        var car_longitude = address_parent_div.parent().children('.select_car_address').children('.my_location_longitude').val();


                        if (car_latitude == '' || car_longitude == '') {
                            jQuery('#map_display').addClass('disabled');
                            return false;
                        }

                        var address = GetAddress(car_latitude, car_longitude);
                        var extra_price = parseFloat(get_extra_price(car_latitude, car_longitude));

                        address_parent_div.parent().children('.select_car_address').children('.extra_amount').val(extra_price);
                        var amount = parseFloat(jQuery('#amount').val());
                        var booking_price = parseFloat(jQuery('#booking_price').val());
                        var booking_price_with_tax = parseFloat(jQuery('#booking_price_with_tax').val());
                        var car_key_price = parseFloat(jQuery('#car_key_price').val());
                        var deadbolt_install_price = parseFloat(jQuery('#deadbolt_install_price').val());
                        var car_programming_fees = parseFloat(jQuery('#car_programming_fees').val());
                        
                        var no_of_keys_want_to_made=jQuery('#no_of_keys_want_to_made').val();
                        var cost_to_cut_additional_key=parseFloat('<?php echo $cost_to_cut_additional_key; ?>');
                        var cost_to_program_additional_key=parseFloat('<?php echo $cost_to_program_additional_key; ?>');
                        var total_key_cost=parseFloat(cost_to_cut_additional_key+cost_to_program_additional_key);
                        var key_cost=parseFloat(no_of_keys_want_to_made*total_key_cost);
                             
                        if (Number.isNaN(booking_price)) {
                            booking_price = 0;
                        }
                        if (Number.isNaN(car_key_price)) {
                            car_key_price = 0;
                        }
                        if (Number.isNaN(deadbolt_install_price)) {
                            deadbolt_install_price = 0;
                        }
                        if (Number.isNaN(car_programming_fees)) {
                            car_programming_fees = 0;
                        }
                        if (Number.isNaN(key_cost)) {
                            key_cost = 0;
                        }
                       
                        booking_price = parseFloat(booking_price);

                        if (Number.isNaN(amount)) {
                            amount = 0;
                        }
                        if (Number.isNaN(extra_price)) {
                            extra_price = 0;
                        }
                        //jQuery('#amount').val(amount + extra_price);
                        var total_amount = amount + extra_price;




                          total_service_booking_amount = amount + extra_price + booking_price + car_key_price + key_cost +car_programming_fees+deadbolt_install_price;
                        if (booking_price_with_tax == '' ) {
                            var tax=0;
                        } else {
                            var tax=(total_service_booking_amount*booking_price_with_tax)/100;
                            
                        }
                        var total_service_booking = total_service_booking_amount+tax;
                        jQuery('#map_display').addClass('disabled');
                        setTimeout(function () {
                            total_amount = parseFloat(total_amount);
                            var html = '<span class="currency"><?php echo $currenct_symbol; ?></span>' + (total_amount).toFixed(2);
                            jQuery('.product-addon-totals .wc-pao-col2 .amount').html(html);
                            //jQuery('.wc-pao-addon-select').find(':selected').attr('data-price',total_amount.toFixed(2));
                            //jQuery('.wc-pao-addon-select').find(':selected').attr('data-raw-price',total_amount.toFixed(2));
                            if (booking_price != 0) {
                                var subtotal_html = '<p class="price">Subtotal <span class="amount"><span class="currency"><?php echo $currenct_symbol; ?></span>' + ( parseFloat(total_service_booking)).toFixed(2) + '</span></p>';
                                jQuery('.wc-pao-subtotal-line').html(subtotal_html);
                                
                               // jQuery('#show_total_amount_with_tax .price_total').html(( parseFloat(total_service_booking)).toFixed(2));
                            }
                        }, 3000);
                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo add_query_arg('action', 'calculate_product_tax', $WCMp->ajax_url()); ?>',
                            data: {
                                amount: total_service_booking_amount,
                                latitude: car_latitude,
                                longitude: car_longitude
                            },
                            success: function (response) {
                                var array = JSON.parse(response);
                                jQuery('#booking_price_with_tax').val(array.rate);
                            
                            var rate=array.rate;
                            console.log(rate,'rate');
                            console.log(total_service_booking_amount,'total_service_booking_amount_ajax');
                            if(rate == ''){
                                var tax_ajax=0;
                            }
                            else{
                                
                             var tax_ajax=(total_service_booking_amount*rate)/100;
                            }
                            // var total_service_booking=total_service_booking_amount+tax_ajax;
                             
                              var discount_percentage=parseFloat('<?php echo $discount_on_deal; ?>');
                            if (Number.isNaN(discount_percentage)) {
                                discount_percentage = 0;
                            }
                            if(discount_percentage >0){
                                var discount_price=(total_service_booking_amount*discount_percentage)/100;
                                var show_total_price=total_service_booking_amount-discount_price;
                                var total_service_booking=parseFloat(show_total_price+tax_ajax);
                                jQuery('#show_discount_price').removeClass('disable');
                            }
                            else{
                                jQuery('#show_discount_price').addClass('disable');
                                var discount_price=0;
                                var show_total_price=total_service_booking_amount;
                                var total_service_booking=parseFloat(show_total_price+tax_ajax);
                            }
                            
                            jQuery('#show_discount_amount .price_discount').html(( parseFloat(discount_price)).toFixed(2));
                            jQuery('#show_total_amount_with_tax .price_total').html(( parseFloat(show_total_price)).toFixed(2));
                            jQuery('#total_price').val(parseFloat(show_total_price).toFixed(2));
                            jQuery('#discount_price').val(parseFloat(discount_price).toFixed(2));
                            jQuery('#sub_total_price').val(parseFloat(total_service_booking_amount).toFixed(2));
                            jQuery('.main_loader').addClass('disabled');
                                
                            }
                        });
                    }
                });


                function workForProperty() {

                    console.log('property');
                     jQuery('.main_loader').removeClass('disabled');
                    var total_service_booking_amount=0;
                    var car_latitude = jQuery('#property_latitude').val();
                    var car_longitude = jQuery('#property_longitude').val();
                    var property_address = jQuery('#property_address').val();

                    if (car_latitude == '' || car_longitude == '') {
                        jQuery('#map_display').addClass('disabled');
                        return false;
                    }

                    var address = GetAddress(car_latitude, car_longitude);
                    var extra_price = parseFloat(get_extra_price(car_latitude, car_longitude));
                    console.log(extra_price, 'extra_price');
                    jQuery('#extra_amount_property').val(extra_price);
                    var amount = parseFloat(jQuery('#amount').val());
                    var booking_price = jQuery('#booking_price').val();
                    var booking_price_with_tax = parseFloat(jQuery('#booking_price_with_tax').val());
                    if (Number.isNaN(booking_price)) {
                        booking_price = 0;
                    }
                    if (booking_price == '') {
                        booking_price = 0;
                    }
                    booking_price = parseFloat(booking_price);
                    if (Number.isNaN(amount)) {
                        amount = 0;
                    }
                    if (Number.isNaN(extra_price)) {
                        extra_price = 0;
                    }
                    //jQuery('#amount').val(amount + extra_price);
                    var total_amount = amount + extra_price;


                    console.log('total_amount',total_amount);
                       var extra_cylinder_price =  parseFloat(jQuery('#extra_cylinder_price').val());
                        if (Number.isNaN(extra_cylinder_price)) {
                             extra_cylinder_price = 0;
                          }


                    console.log('amount,extra_price, booking_price', amount, extra_price, booking_price);
                     total_service_booking_amount = amount + extra_price + booking_price+extra_cylinder_price;
                     console.log(booking_price_with_tax,'booking_price_with_taxbooking_price_with_tax');
                        if (booking_price_with_tax == '' ) {
                            var tax=0;
                        } else {
                            var tax=(total_service_booking_amount*booking_price_with_tax)/100;
                            
                        }
                        var total_service_booking = total_service_booking_amount+tax;
                    jQuery('#map_display').addClass('disabled');
                    setTimeout(function () {
                        var html = '<span class="currency"><?php echo $currenct_symbol; ?></span>' + total_amount.toFixed(2);
                        jQuery('.product-addon-totals .wc-pao-col2 .amount').html(html);
                        //jQuery('.wc-pao-addon-select').find(':selected').attr('data-price',total_amount.toFixed(2));
                        //jQuery('.wc-pao-addon-select').find(':selected').attr('data-raw-price',total_amount.toFixed(2));
                        if (booking_price != 0) {
                            var subtotal_html = '<p class="price">Subtotal <span class="amount"><span class="currency"><?php echo $currenct_symbol; ?></span>' + ( parseFloat(total_service_booking)).toFixed(2) + '</span></p>';
                            jQuery('.wc-pao-subtotal-line').html(subtotal_html);
                           // jQuery('#show_total_amount_with_tax .price_total').html(( parseFloat(total_service_booking)).toFixed(2));
                        }
                    }, 3000);
                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo add_query_arg('action', 'calculate_product_tax', $WCMp->ajax_url()); ?>',
                        data: {
                            amount: total_service_booking_amount,
                            latitude: car_latitude,
                            longitude: car_longitude
                        },
                        success: function (response) {
                            var array = JSON.parse(response);
                            jQuery('#booking_price_with_tax').val(array.rate);
                            
                            var rate=array.rate;
                            console.log(rate,'rate');
                            console.log(total_service_booking_amount,'total_service_booking_amount_ajax');
                            console.log(booking_price,'booking_price_ajax');
                            if(rate == ''){
                                var tax_ajax=0;
                            }
                            else{
                                
                             var tax_ajax=(total_service_booking_amount*rate)/100;
                            }
                            var total_service_booking=total_service_booking_amount+tax_ajax;
                            
                            var service_location_val = jQuery('.wc-pao-addon-select').val();
                            var service_location_price = jQuery('.wc-pao-addon-select').find(':selected').attr('data-price');
                            if (Number.isNaN(service_location_price)) {
                                 service_location_price = 0;
                             }
                            
                            if (service_location_price == 0 && service_location_val != ''){
                                var total_service_booking=booking_price;
                                total_service_booking_amount=booking_price;
                            }
                            
                            
                             var discount_percentage=parseFloat('<?php echo $discount_on_deal; ?>');
                            if (Number.isNaN(discount_percentage)) {
                                discount_percentage = 0;
                            }
                            if(discount_percentage >0){
                                var discount_price=(total_service_booking_amount*discount_percentage)/100;
                                var show_total_price=total_service_booking_amount-discount_price;
                                if (service_location_price == 0 && service_location_val != ''){
                                    var total_service_booking=parseFloat(show_total_price);
                                }
                                else{
                                    var total_service_booking=parseFloat(show_total_price+tax_ajax);
                                }
                                jQuery('#show_discount_price').removeClass('disable');
                            }
                            else{
                                jQuery('#show_discount_price').addClass('disable');
                                var discount_price=0;
                                var show_total_price=total_service_booking_amount;
                                if (service_location_price == 0 && service_location_val != ''){
                                    var total_service_booking=parseFloat(show_total_price);
                                }
                                else{
                                    var total_service_booking=parseFloat(show_total_price+tax_ajax);
                                }
                            }
                            
                            jQuery('#show_discount_amount .price_discount').html(( parseFloat(discount_price)).toFixed(2));
                            jQuery('#show_total_amount_with_tax .price_total').html(( parseFloat(show_total_price)).toFixed(2));
                            jQuery('#total_price').val(parseFloat(show_total_price).toFixed(2));
                            jQuery('#discount_price').val(parseFloat(discount_price).toFixed(2));
                            jQuery('#sub_total_price').val(parseFloat(total_service_booking_amount).toFixed(2));
                            jQuery('.main_loader').addClass('disabled');
                        }
                    });
                }

                jQuery('.owner_authorize_service').click(function () {
                    var authorized_service = jQuery(this).val();
                    if (authorized_service == 'no') {
                        alert('Proof of ownership must be provided to the locksmith at the time of service. If this is possible, please select yes to continue. If not, please call <?php echo $phone; ?>');
                    }
                });
                jQuery('.car_key_img').click(function () {
                    var car_key_type = jQuery(this).attr('data-id');
                    if(car_key_type === 'edge_cut'){
                        var car_key_price='<?php echo $edge_cut_price; ?>';
                        var car_type='Double-Sided';
                    }
                    else if(car_key_type === 'high_security'){
                        var car_key_price='<?php echo $high_security_price; ?>';
                        var car_type='High-Security';
                    }
                    else if(car_key_type === 'tibbe'){
                        var car_key_price='<?php echo $tibbe_price; ?>';
                        var car_type='Tibbe';
                    }
                    else if(car_key_type === 'vats'){
                        var car_key_price='<?php echo $vats_price; ?>';
                        var car_type='Vats';
                    }
                    else if(car_key_type === 'clear'){
                        var car_key_price='';
                        var car_type='';
                    }
                    
                    jQuery('.car_key_img').removeClass('car_key-select');
                    jQuery(this).addClass('car_key-select');
                    jQuery('#car_key_type').val(car_type);
                    jQuery('#car_key_price').val(car_key_price);
                    calculate_amount_car_key_price();
                    
                });
                
            });
            function calculate_amount_car_key_price(){
                jQuery('.main_loader').removeClass('disabled');
                var total=0;
                var amount = parseFloat(jQuery('#amount').val());
                var booking_price = parseFloat(jQuery('#booking_price').val());
                var booking_price_with_tax = parseFloat(jQuery('#booking_price_with_tax').val());
                var car_key_price = parseFloat(jQuery('#car_key_price').val());
                var car_programming_fees = parseFloat(jQuery('#car_programming_fees').val());
                var service_location_val = jQuery('.wc-pao-addon-select').val();
                var service_location_price = jQuery('.wc-pao-addon-select').find(':selected').attr('data-price');
                service_location_price = parseFloat(service_location_price);
                var no_of_keys_want_to_made=jQuery('#no_of_keys_want_to_made').val();
                var deadbolt_install_price=parseFloat(jQuery('#deadbolt_install_price').val());
                
                var cost_to_cut_additional_key=parseFloat('<?php echo $cost_to_cut_additional_key; ?>');
                var cost_to_program_additional_key=parseFloat('<?php echo $cost_to_program_additional_key; ?>');
                var total_key_cost=parseFloat(cost_to_cut_additional_key+cost_to_program_additional_key);
                var key_cost=parseFloat(no_of_keys_want_to_made*total_key_cost);
                    
                var extra_amount = jQuery('input[name="extra_amount[]"]').map(function () {
                    if (this.value != '') {
                        return this.value;
                    }
                }).get();
                if (Number.isNaN(amount)) {
                    amount = 0;
                }
                if (Number.isNaN(deadbolt_install_price)) {
                    deadbolt_install_price = 0;
                }
                if (Number.isNaN(booking_price)) {
                    booking_price = 0;
                }
                if (Number.isNaN(extra_amount)) {
                    extra_amount = 0;
                }
                if (Number.isNaN(service_location_price)) {
                    service_location_price = 0;
                }
                for (var i = 0; i < extra_amount.length; i++) {
                    var extra = parseFloat(extra_amount[i]);
                    total = parseFloat(total + extra);
                }
                if (Number.isNaN(booking_price_with_tax)) {
                    booking_price_with_tax = 0;
                }       
                if (Number.isNaN(car_key_price)) {
                    car_key_price = 0;
                }
                if (Number.isNaN(car_programming_fees)) {
                    car_programming_fees = 0;
                }
                if (Number.isNaN(key_cost)) {
                    key_cost = 0;
                }
                if (Number.isNaN(total)) {
                    total = 0;
                }
                console.log(amount, total, service_location_price, booking_price, booking_price_with_tax, car_key_price,key_cost,car_programming_fees,deadbolt_install_price);
                var total_amount = parseFloat(amount + total );
                var total_service_booking_amount = parseFloat(amount + total + booking_price + car_key_price + key_cost +car_programming_fees + deadbolt_install_price);
                console.log('total_service_booking_amount',total_service_booking_amount);
                if (booking_price_with_tax == '') {
                    var tax=0;
                } else {
                    var tax = (total_service_booking_amount*booking_price_with_tax)/100;
                }
                var total_service_booking = parseFloat(amount + total + booking_price +car_key_price + key_cost + tax + car_programming_fees + deadbolt_install_price);
                setTimeout(function () {
                    var html = '<span class="currency"><?php echo $currenct_symbol; ?></span>' + total_amount.toFixed(2);
                    jQuery('.product-addon-totals .wc-pao-col2 .amount').html('');
                    jQuery('.product-addon-totals .wc-pao-col2 .amount').html(html);
                    if (booking_price != 0) {
                        var subtotal_html = '<p class="price">Subtotal <span class="amount"><span class="currency"><?php echo $currenct_symbol; ?></span>' + ( parseFloat(total_service_booking)).toFixed(2) + '</span></p>';
                        jQuery('.wc-pao-subtotal-line').html('');
                        jQuery('.wc-pao-subtotal-line').html(subtotal_html);
                    }
                    console.log(total_service_booking,'total_service_booking');
                    
                    var discount_percentage=parseFloat('<?php echo $discount_on_deal; ?>');
                            if (Number.isNaN(discount_percentage)) {
                                discount_percentage = 0;
                            }
                            if(discount_percentage >0){
                                var discount_price=(total_service_booking_amount*discount_percentage)/100;
                                var show_total_price=total_service_booking_amount-discount_price;
                                var total_service_booking=parseFloat(show_total_price+tax);
                                jQuery('#show_discount_price').removeClass('disable');
                            }
                            else{
                                jQuery('#show_discount_price').addClass('disable');
                                var discount_price=0;
                                var show_total_price=total_service_booking_amount;
                                var total_service_booking=parseFloat(show_total_price+tax);
                            }
                            
                            jQuery('#show_discount_amount .price_discount').html(( parseFloat(discount_price)).toFixed(2));
                            jQuery('#show_total_amount_with_tax .price_total').html(( parseFloat(show_total_price)).toFixed(2));
                            jQuery('#total_price').val(parseFloat(show_total_price).toFixed(2));
                            jQuery('#discount_price').val(parseFloat(discount_price).toFixed(2));
                            jQuery('#sub_total_price').val(parseFloat(total_service_booking_amount).toFixed(2));
                            jQuery('.main_loader').addClass('disabled');
                }, 3000);
                
                
                        
            }
            
            function GetAddress(latitude, longitude) {
                var address = '';
                var lat = parseFloat(latitude);
                var lng = parseFloat(longitude);
                var latlng = new google.maps.LatLng(lat, lng);
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({'latLng': latlng}, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        if (results[1]) {
                                
                            var element = document.querySelector("#map_display");

                            if (address_selected == 1) {
                                address = results[1].formatted_address;
                            } 
                            else {
                                if (element.classList.contains("property")) {
                                    address = jQuery("#property_address").val();
                                }   
                                else if (element.classList.contains("car")){
                                    address = address_parent_div.parent().children('.select_car_address').children('.my_location_address').val();
                                }          
                            }
                             
                            console.log('function:', address);
                             
                            jQuery('.show_selected_property_address').removeClass('disabled');
                            jQuery('.show_selected_property_address').html(address);
                            address_parent_div.parent().children('.show_selected_address').removeClass('disabled');
                            address_parent_div.parent().children('.show_selected_address').html(address);
                        }
                    }
                });

            }
            function initMap() {
                address_selected = 0;
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

                autocomplete.addListener('place_changed', function () {
                    address_selected = 0;
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
                        var address_show = address;
                    }

                    var element = document.querySelector("#map_display");

                    if (element.classList.contains("property")) {
                        jQuery('#property_latitude').val(place.geometry.location.lat());
                        jQuery('#property_longitude').val(place.geometry.location.lng());
                        jQuery('#property_address').val(address_show);
                    } else if (element.classList.contains("car")) {
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
                        function (marker) {
                            address_selected = 1;
                            var latLng = marker.latLng;
                            currentLatitude = latLng.lat();
                            currentLongitude = latLng.lng();
                            infowindowContent.children['place-icon'].src = '';
                            infowindowContent.children['place-name'].textContent = '';
                            infowindowContent.children['place-address'].textContent = currentLatitude.toFixed(4) + ',' + currentLongitude.toFixed(4);
                            //infowindow.open(map, marker);
                            // infowindow.setContent(results[0].formatted_address);
                            //infowindow.open(map, marker);
                            var element = document.querySelector("#map_display");

                            if (element.classList.contains("property")) {
                                jQuery('#property_latitude').val(currentLatitude);
                                jQuery('#property_longitude').val(currentLongitude);
                            } else if (element.classList.contains("car")) {
                                address_parent_div.parent().children('.select_car_address').children('.my_location_latitude').val(currentLatitude);
                                address_parent_div.parent().children('.select_car_address').children('.my_location_longitude').val(currentLongitude);

                            }
                        });
                google.maps.event.addListener(marker, 'click', function () {
                    infowindow.open(map, marker);
                });

            }

            function distance(lat1, lon1, lat2, lon2, unit) {
                if ((lat1 == lat2) && (lon1 == lon2)) {
                    return 0;
                } else {
                    var radlat1 = Math.PI * lat1 / 180;
                    var radlat2 = Math.PI * lat2 / 180;
                    var theta = lon1 - lon2;
                    var radtheta = Math.PI * theta / 180;
                    var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
                    if (dist > 1) {
                        dist = 1;
                    }
                    dist = Math.acos(dist);
                    dist = dist * 180 / Math.PI;
                    dist = dist * 60 * 1.1515;
                    if (unit == "K") {
                        dist = dist * 1.609344
                    }
                    if (unit == "N") {
                        dist = dist * 0.8684
                    }
                    return dist;
                }
            }

            function get_extra_price(car_latitude, car_longitude) {
                var vendor_latitude = '<?php echo $vendor_latitude; ?>';
                var vendor_longitude = '<?php echo $vendor_longitude; ?>';
                var miles = Math.ceil(parseFloat(distance(vendor_latitude, vendor_longitude, car_latitude, car_longitude, 'M')));
                console.log('miles:' + miles);
                var maximum_miles = parseFloat(<?php echo $maximum_miles; ?>);
                var default_miles = parseFloat(<?php echo $default_miles; ?>);
                var extra_permile_price = '<?php echo $extra_permile_price; ?>';
                var element = document.querySelector("#map_display");
                if (miles > maximum_miles) {
                    if (element.classList.contains("property")) {
                        jQuery('.total_miles_property').val(miles);
                    }
                    else if (element.classList.contains("car")) {
                        address_parent_div.parent().children('.select_car_address').children('.total_miles').val(miles);
                    }
                    jQuery('#booking_servicable').val('0');
                    return 0;
                } else {
                    jQuery('#booking_servicable').val('1');
                    if (element.classList.contains("property")) {
                        jQuery('.total_miles_property').val(0);
                    }
                    else if (element.classList.contains("car")) {
                        address_parent_div.parent().children('.select_car_address').children('.total_miles').val(0);
                    }
                    if (miles > default_miles) {
                        var difference = miles - default_miles;
                        var extra_price = difference * extra_permile_price;
                        return extra_price;
                    } else {
                        return 0;
                    }
                }
            }


        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCuC3PEVYk9RGDIIuLM1ur-sQ7y73ff3eY&libraries=places&callback=initMap"
        async defer></script>
        <style>
            span#show_discount_amount {
                float: right;
            }
            .price_discount {
                float: left;
            }
            .product-addon-totals{
                display:none;
            }
            .options_show label {
                width: 100%;
            }
            .options_show .car-key-img {
                /*display: inline-block;*/
            }
            body.single.single-product .site-main{   
                z-index: inherit;
            }

            .main_loader {
                position: fixed;
                left: 0;
                right: 0;
                width: 100%;
                height: 100%;
                background-color: hsla(0, 0%, 100%, 0.80);
                z-index: 9999999 !important;
                top: 0;
                bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }
            .locks_img img{
                height: 150px;
                width: auto;
            }
            .locks-img {
                text-align: center;
            }
            .main_loader img {
                max-width: 60px;
            }

            .car_key-select img {
                border: 3px solid #8BC34A !important;
              /*  padding: 2px; */
            }
            .car-key-img img {
               /* border: 1px solid #c4a33c;
                padding: 2px;*/
                height: 107px;
                max-width: 100%;
                width: 100%;
            }
            .car_key_img img {
                border: 1px solid;
            }
            .car_key_name span {
                border: 1px solid #ddd;
                padding:8px 4px;
                display: inline-block;
            }
            
            .car_key_name .car_key-select {
                border-color: #c4a33c;
                color: #c4a33c;
                font-weight: 500;
            }
            
            .car-key-img {
                margin: 7px;
                cursor: pointer;
               /* border: 1px solid; */
            }
            span.show_selected_property_address {
                display: flex;
            }
            #show_total_amount_of_extra_cylinder{
                float:right;
            }
           .cylinder_price_total {
                float: left;
            }
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
                font-weight: 400;
            }
            .options_show {
                padding: 10px;
                position: relative;
            }
            .select_car_address_label{
               padding: 3px;
               position: relative;
               font-size: 14px !important;
               margin-bottom: -10px !important;
               margin-top: 5px;
             }
             .vehical_located_hide{
                display:none; 
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
            .woocommerce-tabs ul.tabs li:hover a {
                border: 1px solid #77c84e;
                background-color: #77c84e;
                color: #ffffff;
            }
            .woocommerce-tabs ul.tabs li.active a {
                border-top: 1px solid #77c84e;
            }
            .disable{
                display:none;
            }
        </style>
        <?php
    }

    function get_coordinates_from_address($address) {
        $prepAddr = str_replace(' ', '+', $address);
        $url = 'https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false&key=AIzaSyCuC3PEVYk9RGDIIuLM1ur-sQ7y73ff3eY';
        $geocode = @file_get_contents($url);
        $output = json_decode($geocode);
        $latitude = $output->results[0]->geometry->location->lat;
        $longitude = $output->results[0]->geometry->location->lng;
        return $latitude . '/' . $longitude;
    }

    function get_all_cars() {
        global $wpdb;
        $table_name = BuyLockSmithDealsCustomizationAddon::blsd_y_m_model_table_name();
        return $result = $wpdb->get_results("Select DISTINCT maker,model,year from $table_name", ARRAY_A);
    }

    function blsd_add_custom_data_to_cart_item($cart_item_data, $product_id, $variation_id) {
        
        $serviceable_car_maker = $_POST['serviceable_car_maker']; //filter_input( INPUT_POST, 'serviceable_car_maker' );
        $serviceable_car_model = $_POST['serviceable_car_model']; // filter_input( INPUT_POST, 'serviceable_car_model' );
        $serviceable_car_year = $_POST['serviceable_car_year']; // filter_input( INPUT_POST, 'serviceable_car_year' );
        $my_location_latitude = $_POST['my_location_latitude']; // filter_input( INPUT_POST, 'my_location_latitude' );
        $my_location_longitude = $_POST['my_location_longitude']; //filter_input( INPUT_POST, 'my_location_longitude' );
        if(!empty($serviceable_car_maker)){
        $total = count($serviceable_car_maker);
        }
        else{
          $total =1;  
        }
        $serviceable_car = [];
        $my_location_address = [];
        $my_location_coordinates = [];
        $property_coordinates = [];
        for ($i = 0; $i < $total; $i++) {
            if(!empty($serviceable_car_maker[$i]) && !empty($serviceable_car_model[$i]) && !empty($serviceable_car_year[$i])){
                $serviceable_car[] = $serviceable_car_maker[$i] . '-' . $serviceable_car_model[$i] . '-' . $serviceable_car_year[$i];
            }
            $my_location_address[] = self::get_address_from_coordinates($my_location_latitude[$i], $my_location_longitude[$i]);
            $my_location_coordinates[] = ['latitude' => $my_location_latitude[$i], 'longitude' => $my_location_longitude[$i]];
        }
        $working_keys = filter_input(INPUT_POST, 'working_keys');
        $working_keys_locks = filter_input(INPUT_POST, 'working_keys_locks');
        $when_start_car = filter_input(INPUT_POST, 'when_start_car');
        $car_currently_locked = filter_input(INPUT_POST, 'car_currently_locked');
        $will_owner_authorize_service = filter_input(INPUT_POST, 'will_owner_authorize_service');
        $need_key_to_work = filter_input(INPUT_POST, 'need_key_to_work');
        $property_type = filter_input(INPUT_POST, 'property_type');
        $property_latitude = filter_input(INPUT_POST, 'property_latitude');
        $property_longitude = filter_input(INPUT_POST, 'property_longitude');
        
        $quantity_of_locks_to_rekey = filter_input(INPUT_POST, 'quantity_of_locks_to_rekey');
        
        $extra_cylinder_price = filter_input(INPUT_POST, 'extra_cylinder_price');
        $extra_cylinder = filter_input(INPUT_POST, 'extra_cylinder');
        $extra_per_cylinders_price = filter_input(INPUT_POST, 'extra_per_cylinders_price');
        $service_charge = filter_input(INPUT_POST, 'amount');
        $booking_charge = filter_input(INPUT_POST, 'booking_price');
        $total_price = filter_input(INPUT_POST, 'total_price');
        $discount_price = filter_input(INPUT_POST, 'discount_price');
        $sub_total_price = filter_input(INPUT_POST, 'sub_total_price');
        $car_key_type = filter_input(INPUT_POST, 'car_key_type');
        $car_key_price = filter_input(INPUT_POST, 'car_key_price');
        $car_programming_fees = filter_input(INPUT_POST, 'cprogramming_fees');
        $car_vats_fees = filter_input(INPUT_POST, 'cvats_fees');
        $no_of_keys_want_to_made = filter_input(INPUT_POST, 'no_of_keys_want_to_made');
        $cost_to_cut_key = filter_input(INPUT_POST, 'cost_to_cut_key');
        $cost_to_program_key = filter_input(INPUT_POST, 'cost_to_program_key');
        
        /***********************/
        $deadbolt_install_price = filter_input(INPUT_POST, 'deadbolt_install_price');
        $type_of_installation = filter_input(INPUT_POST, 'type_of_installation');
        $who_is_supplying_deadbolts = filter_input(INPUT_POST, 'who_is_supplying_deadbolts');
        $door_frame_type = filter_input(INPUT_POST, 'door_frame_type');
        $quantity_locks_to_install = filter_input(INPUT_POST, 'quantity_locks_to_install');
        /**********************/
        
        //$total_price = $service_charge + $booking_charge;
        $quantity = $total;
        $final_price = $total_price * $total;
        $$discount_price = $discount_price;
        /* if ( empty( $serviceable_car ) ) {
          return $cart_item_data;
          } */
        $cart_item_data['quantity'] = $quantity;
        $cart_item_data['total_price'] = $final_price;
        if(!empty($discount_price)){
        $cart_item_data['discount_price'] = $discount_price;
        $cart_item_data['sub_total_price'] = $sub_total_price;
        }
        $cart_item_data['service_charge'] = $service_charge;
        foreach ($serviceable_car as $key => $value) {
            $cart_item_data['serviceable_car'][$key] = $value;
        }
        if($car_programming_fees >0){
        $cart_item_data['car_programming_fee']= $car_programming_fees;
        }
        if($car_vats_fees >0){
        $cart_item_data['car_vats_fee']= $car_vats_fees;
        }
        
        foreach ($my_location_address as $key => $value) {
            $cart_item_data['my_location_address'][$key] = $value;
            $cart_item_data['my_location_coordinates'][$key] = $my_location_coordinates[$key];
        }
        if (!empty($working_keys)) {
            $cart_item_data['working_keys'] = $working_keys;
        }
        if (!empty($working_keys_locks)) {
            $cart_item_data['working_keys_locks'] = $working_keys_locks;
        }
        if (!empty($when_start_car)) {
            $cart_item_data['when_start_car'] = $when_start_car;
        }
        if (!empty($need_key_to_work)) {
            $cart_item_data['need_key_to_work'] = $need_key_to_work;
        }
        if (!empty($car_key_type) && !empty($car_key_price)) {
            $cart_item_data['car_key_type'] = $car_key_type;
            $cart_item_data['car_key_price'] = $car_key_price;
        }
        
        $cart_item_data['no_of_keys_want_to_made'] = $no_of_keys_want_to_made;
        $cart_item_data['cost_to_cut_key'] = $cost_to_cut_key;
        $cart_item_data['cost_to_program_key'] = $cost_to_program_key;
        
       
        
        if (!empty($car_currently_locked)) {
            $cart_item_data['car_currently_locked'] = $car_currently_locked;
        }
        if (!empty($will_owner_authorize_service)) {
            $cart_item_data['will_owner_authorize_service'] = $will_owner_authorize_service;
        }
        if (!empty($property_type)) {
            $cart_item_data['property_type'] = $property_type;
        }
        if (!empty($quantity_of_locks_to_rekey)) {
            $cart_item_data['quantity_of_locks_to_rekey'] = $quantity_of_locks_to_rekey;
            $cart_item_data['extra_cylinder_price'] = $extra_cylinder_price;
            $cart_item_data['extra_cylinder'] = $extra_cylinder;
            $cart_item_data['extra_per_cylinders_price'] = $extra_per_cylinders_price;
         }
        if (!empty($property_latitude) && !empty($property_longitude)) {
            $property_address = self::get_address_from_coordinates($property_latitude, $property_longitude);
            $cart_item_data['property_address'] = $property_address;
            $cart_item_data['my_location_coordinates'][0] = ['latitude' => $property_latitude, 'longitude' => $property_longitude];
        }
        $cart_item_data['type_of_installation'] = $type_of_installation;
        $cart_item_data['who_is_supplying_deadbolts'] = $who_is_supplying_deadbolts;
        $cart_item_data['door_frame_type'] = $door_frame_type;
        $cart_item_data['quantity_locks_to_install'] = $quantity_locks_to_install;
        $cart_item_data['deadbolt_install_price'] = $deadbolt_install_price;
        
        
        return $cart_item_data;
    }

    function blsd_display_custom_text_cart($item_data, $cart_item) {
        /* if ( empty( $cart_item['serviceable_car'] ) ) {
          return $item_data;
          } */
        echo apply_filters(
                'woocommerce_cart_item_remove_link_custom', sprintf(
                        '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-cart_item_key="%s">X</a>', esc_url('#'), esc_html__('Remove this item', 'woocommerce'), esc_attr($cart_item['product_id']), esc_attr(''), esc_attr($cart_item['key'])
                ), $cart_item['key']
        );
        if (!empty($cart_item['addons'])) {
            foreach ($cart_item['addons'] as $addon) {
                $price = isset($cart_item['addons_price_before_calc']) ? $cart_item['addons_price_before_calc'] : $addon['price'];
                $name = $addon['name'];
                if (0 == $addon['price']) {
                    $name .= '';
                } elseif ('percentage_based' === $addon['price_type'] && 0 == $price) {
                    $name .= '';
                } elseif ('percentage_based' !== $addon['price_type'] && $addon['price'] && apply_filters('woocommerce_addons_add_price_to_name', '__return_true')) {
                    $name .= ' (' . wc_price(WC_Product_Addons_Helper::get_product_addon_price_for_display($cart_item['service_charge'], $cart_item['data'], true)) . ')';
                } else {
                    $_product = new WC_Product($cart_item['product_id']);
                    $_product->set_price($price * ( $addon['price'] / 100 ));
                    $name .= ' (' . WC()->cart->get_product_price($_product) . ')';
                }
                foreach ($item_data as $key => $value) {
                    if ($addon['value'] == $value['value']) {
                        $item_data[$key]['name'] = $name;
                    }
                }
            }
        }
        if (isset($cart_item['serviceable_car'])) {
            if (is_array($cart_item['serviceable_car'])) {
                $total_car = count($cart_item['serviceable_car']);
            } else {
                $total_car = 0;
            }
        } else {
            $total_car = 0;
        }
        for ($i = 0; $i < $total_car; $i++) {
            $k = '';
            if (isset($cart_item['my_location_address'][$i]) && !empty($cart_item['my_location_address'][$i])) {
                $item_data[] = array(
                    'key' => __('My Location ' . $k, 'iconic'),
                    'value' => wc_clean($cart_item['my_location_address'][$i]),
                    'display' => '',
                );
            }
            if (isset($cart_item['serviceable_car'][$i]) && !empty($cart_item['serviceable_car'][$i])) {
                $item_data[] = array(
                    'key' => __('Serviceable Car ' . $k, 'iconic'),
                    'value' => wc_clean($cart_item['serviceable_car'][$i]),
                    'display' => '',
                );
            }
        }
        if(isset($cart_item['car_programming_fee']) && $cart_item['car_programming_fee'] >0  ){
            $item_data[] = array(
                'key' => __('Car Programming Fee', 'iconic'),
                'value' => wc_clean(get_woocommerce_currency_symbol().$cart_item['car_programming_fee']),
                'display' => '',
            );
        }
        if(isset($cart_item['car_vats_fee']) && $cart_item['car_vats_fee'] >0  ){
            $item_data[] = array(
                'key' => __('Car Vats Fee', 'iconic'),
                'value' => wc_clean(get_woocommerce_currency_symbol().$cart_item['car_vats_fee']),
                'display' => '',
            );
        }
        if (isset($cart_item['working_keys'])) {
            $item_data[] = array(
                'key' => __(' Do you have any working keys that start the car?', 'iconic'),
                'value' => wc_clean($cart_item['working_keys']),
                'display' => '',
            );
        }
        if (isset($cart_item['working_keys_locks'])) {
            $item_data[] = array(
                'key' => __(' Do you have any working keys for the locks?', 'iconic'),
                'value' => wc_clean($cart_item['working_keys_locks']),
                'display' => '',
            );
        }
        if (isset($cart_item['when_start_car'])) {
            $item_data[] = array(
                'key' => __('When you start your car?', 'iconic'),
                'value' => wc_clean($cart_item['when_start_car']),
                'display' => '',
            );
        }
        if (isset($cart_item['need_key_to_work'])) {
            $item_data[] = array(
                'key' => __('Do you need a key to work?', 'iconic'),
                'value' => wc_clean($cart_item['need_key_to_work']),
                'display' => '',
            );
        }
        if (isset($cart_item['car_currently_locked'])) {
            $item_data[] = array(
                'key' => __('Is the car currently locked?', 'iconic'),
                'value' => wc_clean($cart_item['car_currently_locked']),
                'display' => '',
            );
        }
        if (isset($cart_item['will_owner_authorize_service'])) {
            $item_data[] = array(
                'key' => __('Will the owner be able to authorize service? ', 'iconic'),
                'value' => wc_clean($cart_item['will_owner_authorize_service']),
                'display' => '',
            );
        }
        if (isset($cart_item['property_type'])) {
            $item_data[] = array(
                'key' => __('Type of property', 'iconic'),
                'value' => wc_clean($cart_item['property_type']),
                'display' => '',
            );
        }
        if (isset($cart_item['property_address'])) {
            $item_data[] = array(
                'key' => __('Property Address', 'iconic'),
                'value' => wc_clean($cart_item['property_address']),
                'display' => '',
            );
        }
        if (isset($cart_item['quantity_of_locks_to_rekey'])) {
            $item_data[] = array(
                'key' => __('Quantity of locks to rekey', 'iconic'),
                'value' => wc_clean($cart_item['quantity_of_locks_to_rekey']),
                'display' => '',
            );
         }
          if (isset($cart_item['extra_cylinder']) && isset($cart_item['extra_per_cylinders_price']) && !empty($cart_item['extra_cylinder'])) {
              $value_total=$cart_item['extra_cylinder']*$cart_item['extra_per_cylinders_price'];
              $item_data[] = array(
                'key' => __($cart_item['extra_cylinder'].' (extra cylinder)'.' X '.get_woocommerce_currency_symbol().$cart_item['extra_per_cylinders_price'].' (per cylinder) =', 'iconic'),
                'value' => wc_clean(get_woocommerce_currency_symbol().$value_total),
                'display' => '',
            );
           
          }
         if (isset($cart_item['car_key_type']) && isset($cart_item['car_key_price'])) {
             $item_data[] = array(
                'key' => __('Your car key look like '.$cart_item['car_key_type'], 'iconic'),
                'value' => wc_clean(get_woocommerce_currency_symbol().$cart_item['car_key_price']),
                'display' => '',
            );  
             
         }
    
        if(isset($cart_item['no_of_keys_want_to_made'])){    
            $item_data[] = array(
                'key' => __('How many car keys do you want made?', 'iconic'),
                'value' => wc_clean($cart_item['no_of_keys_want_to_made']),
                'display' => '',
            ); 
            $key_total=($cart_item['cost_to_cut_key']+$cart_item['cost_to_program_key'])*$cart_item['no_of_keys_want_to_made'];
            $item_data[] = array(
               'key' => __('(Programming Cost:'.get_woocommerce_currency_symbol().$cart_item['cost_to_program_key'].') X '.$cart_item['no_of_keys_want_to_made'], 'iconic'),
               'value' => wc_clean(get_woocommerce_currency_symbol().$key_total),
               'display' => '',

            );
        }
        
        if (isset($cart_item['discount_price']) && !empty($cart_item['discount_price'])) {
            $item_data[] = array(
                'key' => __('SubTotal('.get_woocommerce_currency_symbol().$cart_item['sub_total_price'].') - Discount('.get_woocommerce_currency_symbol().$cart_item['discount_price'].')', 'iconic'),
                'value' => wc_clean(get_woocommerce_currency_symbol().$cart_item['total_price']),
                'display' => '',
            );
        }
        if (isset($cart_item['door_frame_type'])) {
            $item_data[] = array(
                'key' => __('Door & Frame Type', 'iconic'),
                'value' => wc_clean($cart_item['door_frame_type']),
                'display' => '',
            ); 
        }
        if (isset($cart_item['quantity_locks_to_install'])) {
            $item_data[] = array(
                'key' => __('Quantity Of Locks To Install', 'iconic'),
                'value' => wc_clean($cart_item['quantity_locks_to_install']),
                'display' => '',
            ); 
        }
        if (isset($cart_item['type_of_installation'])) {
            $item_data[] = array(
                'key' => __('Type of Installation', 'iconic'),
                'value' => wc_clean($cart_item['type_of_installation']),
                'display' => '',
            ); 
        }
        if (isset($cart_item['who_is_supplying_deadbolts'])) {
            $item_data[] = array(
                'key' => __('Who Is Supplying Deadbolts', 'iconic'),
                'value' => wc_clean($cart_item['who_is_supplying_deadbolts']),
                'display' => '',
            ); 
        }
        if (isset($cart_item['deadbolt_install_price']) && !empty($cart_item['deadbolt_install_price'])) {
            if($cart_item['who_is_supplying_deadbolts'] == 'I am'){
                $type='Customer';
            }
            else if($cart_item['who_is_supplying_deadbolts'] == 'I want locksmith grade deadbolts'){
                $type='Locksmith';
            }
            $key=$type.' Supplied '.$cart_item['type_of_installation'].' Deadbolt';
            $item_data[] = array(
                'key' => __($key, 'iconic'),
                'value' => wc_clean(get_woocommerce_currency_symbol().$cart_item['deadbolt_install_price']),
                'display' => '',
            ); 
        }
        return $item_data;
    }

    function blsd_add_custom_data_order_items($item, $cart_item_key, $values, $order) {
        /* if ( empty( $values['serviceable_car'] ) ) {
          return;
          } */
        $total_car = count($values['serviceable_car']);
        for ($i = 0; $i < $total_car; $i++) {
            $k = '';
            $item->add_meta_data(__('Serviceable Car ' . $k, 'blsd'), $values['serviceable_car'][$i]);
            
            $item->add_meta_data(__('My Location ' . $k, 'blsd'), $values['my_location_address'][$i]);
        }

        $item->add_meta_data(__(' Do you have any working keys that start the car?', 'blsd'), $values['working_keys']);
        $item->add_meta_data(__(' Do you have any working keys for the locks?', 'blsd'), $values['working_keys_locks']);
        $item->add_meta_data(__('When you start your car? ', 'blsd'), $values['when_start_car']);
        $item->add_meta_data(__('Do you need a key to work? ', 'blsd'), $values['need_key_to_work']);
        $item->add_meta_data(__('Is the car currently locked?', 'blsd'), $values['car_currently_locked']);
        $item->add_meta_data(__('Will the owner be able to authorize service? ', 'blsd'), $values['will_owner_authorize_service']);
        $item->add_meta_data(__('Type of property', 'blsd'), $values['property_type']);
        $item->add_meta_data(__('Property Address', 'blsd'), $values['property_address']);
        $item->add_meta_data(__('Quantity of locks to rekey', 'blsd'), $values['quantity_of_locks_to_rekey']);
        $item->add_meta_data(__('Your car key look like:'.$values['car_key_type'], 'blsd'), $values['car_key_price']);
        if(!empty($values['extra_cylinder'])){
            $value_total=$values['extra_cylinder']*$values['extra_per_cylinders_price'];
            $item->add_meta_data(__($values['extra_cylinder'].' (extra cylinder)'.' X '.$values['extra_per_cylinders_price'].' = ', 'blsd'), $value_total);
        }
        if(!empty($values['no_of_keys_want_to_made'])){
            $item->add_meta_data(__('How many car keys do you want made?', 'blsd'), $values['no_of_keys_want_to_made']);
            $key_total=($values['cost_to_cut_key']+$values['cost_to_program_key'])*$values['no_of_keys_want_to_made'];
            $item->add_meta_data(__('(Programming Cost:'.get_woocommerce_currency_symbol().$values['cost_to_program_key'].') X '.$values['no_of_keys_want_to_made'], 'blsd'), $key_total);
        }
        if(!empty($values['car_programming_fee'])){
            $item->add_meta_data(__('Car Programming Fee', 'blsd'), get_woocommerce_currency_symbol().$values['car_programming_fee']);
        }
        if(!empty($values['car_vats_fee'])){
            $item->add_meta_data(__('Car Vats Fee', 'blsd'), get_woocommerce_currency_symbol().$values['car_vats_fee']);
        }
        if(!empty($values['discount_price'])){
           $item->add_meta_data(__('SubTotal('.get_woocommerce_currency_symbol().$values['sub_total_price'].') - Discount('.get_woocommerce_currency_symbol().$values['discount_price'].')', 'blsd'), get_woocommerce_currency_symbol().$values['total_price']);
        }
        $item->add_meta_data(__('Door & Frame Type', 'blsd'), $values['door_frame_type']);
        $item->add_meta_data(__('Quantity Of Locks To Install', 'blsd'), $values['quantity_locks_to_install']);
        $item->add_meta_data(__('Type of Installation', 'blsd'), $values['type_of_installation']);
        $item->add_meta_data(__('Who Is Supplying Deadbolts', 'blsd'), $values['who_is_supplying_deadbolts']);
        
        if(!empty($values['deadbolt_install_price'])){
            
            if($values['who_is_supplying_deadbolts'] == 'I am'){
                $type='Customer';
            }
            else if($values['who_is_supplying_deadbolts'] == 'I want locksmith grade deadbolts'){
                $type='Locksmith';
            }
            $key=$type.' Supplied '.$values['type_of_installation'].' Deadbolt';
            $item->add_meta_data(__($key, 'blsd'), get_woocommerce_currency_symbol().$values['deadbolt_install_price']);
        }
        
    }

    function blsd_before_calculate_totals($cart_obj) {
        global $woocommerce;
        foreach ($cart_obj->get_cart() as $key => $value) {
            if (isset($value['total_price'])) {
                $price = $value['total_price'];
                $value['data']->set_price(( $price));
            }
        }
    }

    function get_address_from_coordinates($lat, $lng) {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lat) . ',' . trim($lng) . '&sensor=false&key=AIzaSyCuC3PEVYk9RGDIIuLM1ur-sQ7y73ff3eY';

        $json = @file_get_contents($url);
        $data = json_decode($json);
        $status = $data->status;
        if ($status == "OK") {
            return $data->results[0]->formatted_address;
        } else {
            return false;
        }
    }

    function blsd_Checkout_page_css() {
        ?>
        <style>   
            .elementor-invisible {
    visibility: visible !important;
}
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
                font-size: 16px;
                /*color: #000;
                font-weight: 200;*/
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

        <?php if (is_checkout()) { ?>

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

    function blsd_checkout_remove_item() {
        global $post, $WCMp;
        if (is_checkout()) {
            ?>
            <script>
                jQuery(document).ready(function () {
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
                            url: '<?php echo add_query_arg('action', 'product_remove', $WCMp->ajax_url()); ?>',
                            data: {
                                product_id: product_id,
                                cart_item_key: cart_item_key
                            },
                            success: function (response) {
                                if (!response || response.error)
                                    return;

                                var fragments = response.fragments;
                                if (fragments) {
                                    jQuery.each(fragments, function (key, value) {
                                        jQuery(key).replaceWith(value);
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

    
    
    function disable_product_after_vendor_action($query) {
       
        global $pagenow, $wp_styles;
    
          
        $current_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );
        if(!empty($current_page)){
        $slug = $current_page->post_name;
    
 
        if (!is_admin() && $slug!='dashboard' && !is_cart() && !is_checkout()) {
            $applyQuery = 0;
            $query_vars = $query->query_vars;
             if (is_archive() || is_product() || is_single() || (is_product() && $woocommerce_loop['name'] == 'related' )){
                 $applyQuery = 1;
             }
             
             if($applyQuery){
                $query->set('meta_query', array(
                    array(
                        'key' => 'status_vendor_hide',
                        'compare' => 'NOT EXISTS'
                    )
                ));
             }
       
        }
        }
     
        return $query;
    }
    
}
?>