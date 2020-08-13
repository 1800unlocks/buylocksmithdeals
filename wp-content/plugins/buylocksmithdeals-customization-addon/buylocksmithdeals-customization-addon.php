<?php

/**
 * Plugin Name: Buy Locksmith Deals Store
 * Description: This plugin connects your website to the Buy Locksmith Deals website so you can embed your store deals directly onto your website through  API credentials.
 * Version: 0.0.1
 * Author: The Locksmith Agency
 * Author URI: https://buylocksmithdeals.com/
 * Domain Path: /languages/
 *
 */
// =============================================
// Define Constants
// =============================================
if (!defined('BUYLOCKSMITH_DEALS_BASE_PATH')) {
    define('BUYLOCKSMITH_DEALS_BASE_PATH', __FILE__);
}
if (!defined('BUYLOCKSMITH_DEALS_BASE_FOLDER_NAME')) {
    define('BUYLOCKSMITH_DEALS_BASE_FOLDER_NAME', 'buylocksmithdeals-customization-addon');
}
if (!defined('BUYLOCKSMITH_DEALS_ASSETS_PATH')) {
    define('BUYLOCKSMITH_DEALS_ASSETS_PATH', WP_PLUGIN_URL  .'/'.BUYLOCKSMITH_DEALS_BASE_FOLDER_NAME.'/assets/');
}

if (!defined('BUYLOCKSMITH_DEALS_PATH')) {
    define('BUYLOCKSMITH_DEALS_PATH', untrailingslashit(plugins_url('', BUYLOCKSMITH_DEALS_BASE_PATH)));
}

if (!defined('BUYLOCKSMITH_DEALS_PLUGIN_DIR')) {
    define('BUYLOCKSMITH_DEALS_PLUGIN_DIR', untrailingslashit(dirname(BUYLOCKSMITH_DEALS_BASE_PATH)));
}

if (!defined('BUYLOCKSMITH_DEALS_PLUGIN_SLUG')) {
    define('BUYLOCKSMITH_DEALS_PLUGIN_SLUG', basename(dirname(BUYLOCKSMITH_DEALS_BASE_PATH)));
}

if (!defined('BUYLOCKSMITH_DEALS_PLUGIN_DIR_AND_FILE')) {
    define('BUYLOCKSMITH_DEALS_PLUGIN_DIR_AND_FILE', BUYLOCKSMITH_DEALS_PLUGIN_SLUG . '/' . basename(BUYLOCKSMITH_DEALS_BASE_PATH));
}

if (!defined('BUYLOCKSMITH_DEALS_PLUGIN_ROOT_URL')) {
    define('BUYLOCKSMITH_DEALS_PLUGIN_ROOT_URL', WP_PLUGIN_URL  .'/'.BUYLOCKSMITH_DEALS_BASE_FOLDER_NAME.'/');
}
if (!defined('BUYLOCKSMITH_DEALS_PLUGIN_UPLOADS')) {
    define('BUYLOCKSMITH_DEALS_PLUGIN_UPLOADS', BUYLOCKSMITH_DEALS_PLUGIN_ROOT_URL.'/uploads/');
}

define( 'BUYLOCKSMITH_DEALS_WC_BOOKINGS_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/woocommerce-bookings/templates/' );
define( 'BUYLOCKSMITH_DEALS_DIR' , __DIR__  );

include('blsd_wnw_customization.php');
// Include the main WooCommerce class.
if (!class_exists('BuyLockSmithDealsCustomizationAddon', false)) {

    include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/class/class-blsd.php';
     register_activation_hook(BUYLOCKSMITH_DEALS_BASE_PATH, array('BuyLockSmithDealsCustomizationAddon', 'blsd_activate'));
}

add_action('init', 'blsd_set_global_class_instance');

/**
 * Initializing plugin action for customizations.
 * @return boolean
 */
function blsd_set_global_class_instance() {
    if (class_exists('BuyLockSmithDealsCustomizationAddon', false)) {
        $GLOBALS['BuyLockSmithDealsCustomization'] = BuyLockSmithDealsCustomizationAddon::instance();
    }
}

add_action( 'widgets_init', 'my_register_footer', 200 );
function my_register_footer(){

        register_sidebar(
            array(
                'id' => 'dynamic_footer',
                'name' => __( 'Dynamic Footer' ),
                'description' => __( 'A short description of the sidebar.' ),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title">',
                'after_title'   => '</h3>',
            )
        );
    }

add_filter( 'page_template', 'wpa3396_page_template' );
function wpa3396_page_template( $page_template )
{
    if ( is_page( 'vendor-confirmation' ) ) {
        $page_template = BUYLOCKSMITH_DEALS_PLUGIN_DIR.'/page_template/vendor-confirmation.php';
    }
    if ( is_page( 'order-dispute' ) ) {
        $page_template = BUYLOCKSMITH_DEALS_PLUGIN_DIR.'/page_template/order-dispute.php';
    }
    if ( is_page( 'review-rating' ) ) {
        $page_template = BUYLOCKSMITH_DEALS_PLUGIN_DIR.'/page_template/review-rating.php';
    }
    if ( is_page( 'preview' ) ) {
        $page_template = BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/vendor-dashboard-custom-page/preview-shortcode.php';;

    }

    return $page_template;
}

add_action('wp','test_commission');

function test_commission(){
    global $WCMp;
    $commission_to_pay = array();
    $commissions = WCMp_Cron_Job::get_query_commission();
    if ($commissions && is_array($commissions)) {
        foreach ($commissions as $commission) {
            $commission_id = $commission->ID;
            $vendor_term_id = get_post_meta($commission_id, '_commission_vendor', true);
            $commission_to_pay[$vendor_term_id][] = $commission_id;
        }
    }
    foreach ($commission_to_pay as $vendor_term_id => $commissions) {
            $vendor = get_wcmp_vendor_by_term($vendor_term_id);
            if ($vendor) {
                $payment_method = get_user_meta($vendor->id, '_vendor_payment_mode', true);
                if(!isset($_REQUEST) && $_REQUEST['test'] == '123456')
                {
                    echo $vendor->id."<br>";
                }
                if($vendor->id == '8'){
                    if ($payment_method && $payment_method != 'direct_bank') {
                        if (array_key_exists($payment_method, $WCMp->payment_gateway->payment_gateways)) {
                            // $resposne = $WCMp->payment_gateway->payment_gateways[$payment_method]->process_payment($vendor, $commissions);
                            // print_r($resposne);
                        }
                    }
                }
            }
        }
    if(!isset($_REQUEST) && $_REQUEST['test'] == '123456')
    {
        echo "<pre>";
        print_r($commission_to_pay);
        echo "</pre>";
    }
}

/**************************29-07-2020**************************************/
//add_action( 'woocommerce_order_status_processing', 'avlabs_woocommerce_order_status_processing', 10, 1 );

function avlabs_woocommerce_order_status_processing($order_id){
	
		global $WCMp;
		$is_withdrawed = get_post_meta($order_id,'is_withdrawed',true);
		 if($is_withdrawed == ''){
			
			   $suborder_details = get_wcmp_suborders($order_id);
			   foreach ($suborder_details as $key => $value) {
				  $suborderid = $value->get_id();
				  $commission_id = get_post_meta($suborderid,'_commission_id',true);
				  $vendor_id = get_post_meta($suborderid,'_vendor_id',true);
				  $vendor = get_wcmp_vendor($vendor_id);
				  $commission = array($commission_id);
				  $payment_method = get_user_meta($vendor_id, '_vendor_payment_mode', true);
				  
				  if($commission_id != '' && $payment_method == 'stripe_masspay' && !empty($vendor)){
					$response = $WCMp->payment_gateway->payment_gateways[$payment_method]->process_payment($vendor,$commission,'manual');
					 if ($response) {
							if (isset($response['transaction_id'])) {
								do_action( 'wcmp_after_vendor_withdrawal_transaction_success', $response['transaction_id'] );
								update_post_meta($order_id,'is_withdrawed',1);
								
							}
					 }
					
				  }
				
				 
			  }
		 }
		 
}