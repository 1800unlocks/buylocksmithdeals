<?php

defined('ABSPATH') || exit;

/**
 * Main BuyLockSmithDealsCustomizationAdmin Class.
 *
 * @class BuyLockSmithDealsCustomizationAdmin
 */
final class BuyLockSmithDealsCustomizationVendor {

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

    /**
     * Filters and Actions are bundled.
     * @return boolean
     */
    private function init_hooks() {
        add_filter('wcmp_vendor_dashboard_nav', array($this, 'callback_wcmp_vendor_dashboard_nav'), 99);
        add_filter('wcmp_vendor_dashboard_header_nav', array($this, 'wcmp_vendor_dashboard_header_nav'), 1, 1);
        add_filter('wcmp_vendor_dashboard_header_right_panel_nav', array($this, 'wcmp_vendor_dashboard_header_right_panel_nav'), 1, 1);

        add_action('woocommerce_before_shop_loop_item', array($this, 'frontend_product_edit'), 5);
        add_action('woocommerce_before_single_product_summary', array($this, 'frontend_product_edit'), 5);

        add_action('wcmp_init', array($this, 'after_wcmp_init'));
        add_action('save_post', array($this, 'blsd_update_detail_function'));
        add_action( 'wcmp_vendor_dash_after_order_itemmeta', array( $this, 'booking_display' ), 10, 3 );
        add_action( 'wcmp_process_product_object', array( $this, 'blsd_set_booking_props' ), 10 );
        add_filter( 'wcmp_frontend_dash_upload_script_params',array( $this, 'blsd_set_image_parameter' ),10,1);
        add_action('wcmp_dashboard_setup', array($this, 'blsd_wcmp_dashboard_setup'), 6);
		add_action( 'afm_enqueue_dashboard_scripts', array( $this, 'blsd_booking_endpoint_scripts' ), 20, 4 );
		
		
    }
	
	
	public function blsd_booking_endpoint_scripts($endpoint, $frontend_script_path, $lib_path, $suffix){
		global $WCMp;
		switch ( $endpoint ) {
            case 'bookings':
                if ( current_vendor_can( 'manage_bookings' ) ) {
                    $WCMp->library->load_dataTable_lib();
					$frontend_script_path = '/wp-content/plugins/'.BUYLOCKSMITH_DEALS_BASE_FOLDER_NAME . '/wcmp-afm/assets/frontend/js/';
                    wp_register_script( 'afm-blsd-bookings-js', $frontend_script_path . 'bookings.js', array( 'jquery', 'wcmp-datatable-script', 'wcmp-datatable-bs-script' ), afm()->version, true );
                }
                break;
		}
	}
    
    public function blsd_wcmp_dashboard_setup(){
        global $wcmp_dashboard_widget;
		unset($wcmp_dashboard_widget['side']['wcmp_vendor_products_cust_qna']);
        unset($wcmp_dashboard_widget['full']['wcmp_vendor_products_cust_qna']);
        unset($wcmp_dashboard_widget['normal']['wcmp_vendor_products_cust_qna']); 
        unset($wcmp_dashboard_widget['side']['wcmp_vendor_product_stats']['args']); 
    }
    
    public function blsd_set_image_parameter($image_script_params){
        $image_script_params['canSkipCrop']=true;
        return $image_script_params;
    }

    public function blsd_set_booking_props($product){
        $product->set_props( array(
            'last_block_time'           => isset( $_POST['_wc_booking_last_block_time'] ) ? wc_clean( $_POST['_wc_booking_last_block_time'] ) : null,
            ));
    }
    public function get_booking_ids_from_order_item_id_vendor( $order_item_id ) {
		global $wpdb;
		return wp_parse_id_list(
			$wpdb->get_col(
				$wpdb->prepare(
					"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_booking_order_item_id' AND meta_value = %d;",
					$order_item_id
				)
			)
		);
	}
    function booking_display($item_id, $item, $product){
        global $WCMp;
        //$panel = $WCMp->vendor_dashboard->dashboard_header_right_panel_nav();
        //print_r($panel);
        $vendor_order_id=$item->get_order_id();
        $order = wc_get_order( $vendor_order_id );
        $parent_order_id = $order->get_parent_id();
        $parent_order = wc_get_order( $parent_order_id );
        $line_items = $parent_order->get_items( apply_filters( 'wcmp_vendor_order_item_types', 'line_item' ) );
        $parent_item_id=array_keys($line_items)[0];
        $booking_ids = self::get_booking_ids_from_order_item_id_vendor( $parent_item_id );
        $url=home_url() . "/dashboard/vendor-orders/?order_id=".$vendor_order_id;
        wc_get_template( 'order/booking-display.php', array( 'booking_ids' => $booking_ids,'url' => $url), 'woocommerce-bookings', BUYLOCKSMITH_DEALS_WC_BOOKINGS_TEMPLATE_PATH );
    }
	
	
    
    /**
     * Removing unused links from vendor dashboard sidebar navigation.
     * @param type $vendor_nav
     * @return type
     */
    function callback_wcmp_vendor_dashboard_nav($vendor_nav) {
        unset($vendor_nav['store-settings']['submenu']['vendor-policies']);
      //  unset($vendor_nav['vendor-payments']); // for payments
        unset($vendor_nav['vendor-tools']); // for Stats / Reports
        unset($vendor_nav['vendor-report']); // for Stats / Reports
    //    unset($vendor_nav['store-settings']); // for Stats / Reports
        unset($vendor_nav['vendor-knowledgebase']); // for Stats / Reports
        unset($vendor_nav['vendor-products']['submenu']['add-product']); // for Stats / Reports
        return $vendor_nav;
    }

    /**
     * Removing the vendor dashboard header navigation links.
     * @param type $header_nav
     * @return array
     */
    function wcmp_vendor_dashboard_header_nav($header_nav) {
        return [];
    }

    /**
     * Removing WP Admin link from vendor dashboard header right panel navigation.
     * @param type $panel_nav
     * @return array
     */
    function wcmp_vendor_dashboard_header_right_panel_nav($panel_nav) {

        unset($panel_nav['wp-admin']);


        return $panel_nav;
    }

    /*
     * Hiding front end edit link after vendor login.
     */
    function frontend_product_edit() {
        ?>
        <style>
            .wcmp_fpm_buttons{display: none !important};

        </style>
        <?php

    }

    // Add menu items to Vendor dashboard

    function after_wcmp_init() {
        // add a setting field to wcmp endpoint settings page
        add_action('settings_vendor_general_tab_options', array($this, 'add_custom_endpoint_option'));
        // save setting option for custom endpoint
        add_filter('settings_vendor_general_tab_new_input', array($this, 'save_custom_endpoint_option'), 10, 2);
        // add custom endpoint
        add_filter('wcmp_endpoints_query_vars', array($this, 'add_wcmp_endpoints_query_vars'));
        // add custom menu to vendor dashboard
        add_filter('wcmp_vendor_dashboard_nav', array($this, 'add_tab_to_vendor_dashboard'));
        // display content of custom endpoint
        add_action('wcmp_vendor_dashboard_api-credentials_endpoint', array($this, 'custom_menu_endpoint_content'));
        add_action('wcmp_vendor_dashboard_configure-shortcode_endpoint', array($this, 'custom_menu_configure_shortcode_endpoint_content'));
        add_action('wcmp_vendor_dashboard_vendor-dispute-list_endpoint', array($this, 'custom_menu_vendor_dispute_list_endpoint_content'));
    }
    
    /*
     * Adding custom end point to super admin to manage the new vendor dashboard page.
     * * @return Array
     */
    function add_custom_endpoint_option($settings_tab_options) {
        $settings_tab_options['sections']['wcmp_vendor_general_settings_endpoint_ssection']['fields']['wcmp_custom_vendor_endpoint'] = array('title' => __('Api Credentials', 'dc-woocommerce-multi-vendor'), 'type' => 'text', 'id' => 'wcmp_custom_vendor_endpoint', 'label_for' => 'wcmp_custom_vendor_endpoint', 'name' => 'wcmp_custom_vendor_endpoint', 'hints' => __('Set endpoint for custom menu page', 'dc-woocommerce-multi-vendor'), 'placeholder' => 'api-credentials');
        $settings_tab_options['sections']['wcmp_vendor_general_settings_endpoint_ssection']['fields']['wcmp_custom_configure_shortcode_endpoint'] = array('title' => __('Configure Shortcode', 'dc-woocommerce-multi-vendor'), 'type' => 'text', 'id' => 'wcmp_custom_configure_shortcode_endpoint', 'label_for' => 'wcmp_custom_configure_shortcode_endpoint', 'name' => 'wcmp_custom_configure_shortcode_endpoint', 'hints' => __('Set endpoint for custom menu page', 'dc-woocommerce-multi-vendor'), 'placeholder' => 'configure_shortcode');
        $settings_tab_options['sections']['wcmp_vendor_general_settings_endpoint_ssection']['fields']['wcmp_custom_dispute_endpoint'] = array('title' => __('Dispute', 'dc-woocommerce-multi-vendor'), 'type' => 'text', 'id' => 'wcmp_custom_dispute_endpoint', 'label_for' => 'wcmp_custom_dispute_endpoint', 'name' => 'wcmp_custom_dispute_endpoint', 'hints' => __('Set endpoint for custom menu page', 'dc-woocommerce-multi-vendor'), 'placeholder' => 'vendor-dispute-list');
        return $settings_tab_options;
    }

    /*
     * Saving custom end point to super admin to manage the new vendor dashboard page.
     * * @return Array
     */
    function save_custom_endpoint_option($new_input, $input) {
        if (isset($input['wcmp_custom_vendor_endpoint']) && !empty($input['wcmp_custom_vendor_endpoint'])) {
            $new_input['wcmp_custom_vendor_endpoint'] = sanitize_text_field($input['wcmp_custom_vendor_endpoint']);
        }
        if (isset($input['wcmp_custom_configure_shortcode_endpoint']) && !empty($input['wcmp_custom_configure_shortcode_endpoint'])) {
            $new_input['wcmp_custom_configure_shortcode_endpoint'] = sanitize_text_field($input['wcmp_custom_configure_shortcode_endpoint']);
        }
        if (isset($input['wcmp_custom_dispute_endpoint']) && !empty($input['wcmp_custom_dispute_endpoint'])) {
            $new_input['wcmp_custom_dispute_endpoint'] = sanitize_text_field($input['wcmp_custom_dispute_endpoint']);
        }
        return $new_input;
    }

    /*
     * adding in query to show active menu item.
     * * @return Array
     */
    function add_wcmp_endpoints_query_vars($endpoints) {
        $endpoints['api-credentials'] = array(
            'label' => __('Api Credentials', 'dc-woocommerce-multi-vendor'),
            'endpoint' => get_wcmp_vendor_settings('wcmp_custom_vendor_endpoint', 'vendor', 'general', 'api-credentials')
        );
        $endpoints['configure-shortcode'] = array(
            'label' => __('Configure Shortcode for Display products on site', 'dc-woocommerce-multi-vendor'),
            'endpoint' => get_wcmp_vendor_settings('wcmp_custom_configure_shortcode_endpoint', 'vendor', 'general', 'configure-shortcode')
        );
        $endpoints['vendor-dispute-list'] = array(
            'label' => __('dispute list for vendor', 'dc-woocommerce-multi-vendor'),
            'endpoint' => get_wcmp_vendor_settings('wcmp_custom_dispute_endpoint', 'vendor', 'general', 'vendor-dispute-list')
        );
        return $endpoints;
    }

    
    /*
     * Registring vendor dashboard sidebar menu item.
     * * @return Array
     */
    function add_tab_to_vendor_dashboard($nav) {
        unset($nav['booking']['submenu']['resources']);
        unset($nav['booking']['submenu']['create-booking']);
        unset($nav['booking']['submenu']['booking-calendar']);
        unset($nav['booking']['submenu']['booking-notification']);
        $nav['api-credentials'] = array(
            'label' => __('Api Credentials', 'dc-woocommerce-multi-vendor'), // menu label
            'url' => wcmp_get_vendor_dashboard_endpoint_url('api-credentials'), // menu url
            'capability' => true, // capability if any
            'position' => 100, // position of the menu (moves beneath Dashboard link)
            'submenu' => array(), // submenu if any
            'link_target' => '_self',
            'nav_icon' => 'glyphicon glyphicon-leaf', // menu icon
        );
        $nav['configure-shortcode'] = array(
            'label' => __('Configure Shortcode', 'dc-woocommerce-multi-vendor'), // menu label
            'url' => wcmp_get_vendor_dashboard_endpoint_url('configure-shortcode'), // menu url
            'capability' => true, // capability if any
            'position' => 99, // position of the menu (moves beneath Dashboard link)
            'submenu' => array(), // submenu if any
            'link_target' => '_self',
            'nav_icon' => 'glyphicon glyphicon-wrench', // menu icon
        );
        $nav['vendor-dispute-list'] = array(
            'label' => __('Dispute', 'dc-woocommerce-multi-vendor'), // menu label
            'url' => wcmp_get_vendor_dashboard_endpoint_url('vendor-dispute-list'), // menu url
            'capability' => true, // capability if any
            'position' => 99, // position of the menu (moves beneath Dashboard link)
            'submenu' => array(), // submenu if any
            'link_target' => '_self',
            //'nav_icon' => 'glyphicon glyphicon-thumbs-down', // menu icon
            'nav_icon' => ' wcmp-font ico-failed-status-icon', // menu icon
        );
        return $nav;
    }

    /*
     * Html for api credentials page
     * * @return void
     */
    function custom_menu_endpoint_content() {
        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/vendor-dashboard-custom-page/api-credentials.php';
    }

    /*
     * Html for configure shortcode page
     * * @return void
     */
    function custom_menu_configure_shortcode_endpoint_content() {
        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/vendor-dashboard-custom-page/configure-shortcode.php';
    }
    /*
     * Html for configure vendor dispute list page
     * * @return void
     */
    function custom_menu_vendor_dispute_list_endpoint_content() {
        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/vendor-dashboard-custom-page/dispute/vendor-dispute-list.php';
    }

    
    /*
     * generate or get the vendor site api credentials to use in his site for product list.
     * * @return string
     */
    function generate_vendor_site_api_credentials($vendor_id = 0) {
        global $wpdb;

        if ($vendor_id == 0) {
            $vendor_id = get_current_user_id();
        }

        $table_name = BuyLockSmithDealsCustomizationAddon::blsd_api_credentials_table_name();

        $query = "SELECT api_key FROM $table_name where vendor_id=$vendor_id";
        $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);

        if (count($results) == 0) {
            $i = 1;
            while ($i != 0) {

                $api_key = $this->random_strings(16);
                $query = "SELECT * FROM $table_name whare api_key=$api_key";
                $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
                // print_r($results); exit;
                if (count($results) == 0) {
                    $data = [
                        'vendor_id' => $vendor_id,
                        'api_key' => $api_key,
                        'url' => home_url(),
                        'created_at' => date('Y-m-d')
                    ];

                    $wpdb->insert($table_name, $data);

                    $i = 0;
                }
            }
        } else {
            $api_key = $results[0]['api_key'];
        }

        return $api_key;
    }

    /*
     * Generating the alphanumeric code.
     */
    function random_strings($length_of_string) {

        // String of all alphanumeric character 
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        // Shufle the $str_result and returns substring 
        // of specified length 
        return substr(str_shuffle($str_result), 0, $length_of_string);
    }
    
       
function blsd_update_detail_function( $post_id ){

	if ( ! wp_is_post_revision( $post_id ) ){
            global $wp_styles;
        $current_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );

 $slug = $current_page->post_name;
 
 if($slug=='dashboard'){
  
    if(isset($_REQUEST['status_vendor'])){
     update_post_meta($post_id, 'status_vendor',$_REQUEST['status_vendor']);
     
       
        if(trim($_REQUEST['status_vendor'])!='publish'){
             
     update_post_meta($post_id, 'status_vendor_hide',1);
        }else{
            delete_post_meta( $post_id, 'status_vendor_hide');
        }
   
    }
 
     if(isset($_REQUEST['prod_vendor_TnC'])){
     update_post_meta($post_id, 'prod_vendor_TnC', $_REQUEST['prod_vendor_TnC']);
    }
    
 }
	    
		
		
	}
}
        

}
