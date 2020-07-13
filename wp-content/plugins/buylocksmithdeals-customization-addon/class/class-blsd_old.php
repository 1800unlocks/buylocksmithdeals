<?php

defined('ABSPATH') || exit;

/**
 * Main BuyLockSmithDealsCustomizationAddon Class.
 *
 * @class BuyLockSmithDealsCustomizationAddon
 */
class BuyLockSmithDealsCustomizationAddon {

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

        if (!function_exists('is_plugin_active_for_network')) {
            include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }
        $required_plugins_status = self::required_plugins_status();

        
        if ($required_plugins_status) {
            $this->init_hooks();
            $this->blsdEmail();
            $this->blsdAdmin();
            $this->blsdVendor();
            $this->BLSDRestApi();
            $this->BuyLockSmithDeals_onload();
            $this->blsdFrontEnd();
            $this->blsdClassForUserCustomer();
        } else {
            add_action('admin_notices', array(__CLASS__, 'admin_notices'), 10);
        }
	
        
		include_once 'class-inc-wnw.php';
    }

    /**
     * Check required plugins status
     * @return boolean
     */
    public static function required_plugins_status() {
        $required_plugins_status = true;
        if (!self::is_wcmp_active()) {
            $required_plugins_status = false;
        }
        if (!self::is_woocommerce_active()) {
            $required_plugins_status = false;
        }
        return $required_plugins_status;
    }

    /**
     * Check wcmp plugin installed and active
     * @return boolean
     */
    public static function is_wcmp_active() {
        $status = false;
        if (is_plugin_active('dc-woocommerce-multi-vendor/dc_product_vendor.php')) {
            $status = true;
        }
        return $status;
    }

    /**
     * Check woocommerce plugin installed and active
     * @return boolean
     */
    public static function is_woocommerce_active() {
        $status = false;
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            $status = true;
        }
        return $status;
    }

    /**
     * Activation check for required plugins.
     */
    function blsd_activate() { 
          
        if (!function_exists('is_plugin_active_for_network')) {
            include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }
        
        $required_plugins_status = self::required_plugins_status();
        if (!$required_plugins_status) { 
            // deactivate_plugins( plugin_basename( __FILE__ ) );
            deactivate_plugins(BUYLOCKSMITH_DEALS_PLUGIN_DIR_AND_FILE);
            wp_die(__('Please install and activate required plugins .', 'woocommerce-addon-slug'), 'Plugin dependency check', array('back_link' => true));
        }else{
       self::blsd_api_credentials_table();
       self::blsd_dispute_table();
       self::blsd_dispute_message_table();
       self::blsd_dispute_attachment_message_table();
       self::blsd_status_table();
       self::blsd_y_m_model_table();
       
        }
    }

    /**
     * Admin notice for required plugins.
     */
    public static function admin_notices() {
        if (!function_exists('is_plugin_active_for_network')) {
            include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }
        if (self::is_wcmp_active()) {
            $message = '';
            $message = sprintf(__('Buylocksmithdeals Customization Addon requires  %sWC Marketplace%s', 'buylocksmithdeals-addon'), '<a href="https://wordpress.org/plugins/dc-woocommerce-multi-vendor/" target="_blank">', '</a>'
            );
            echo '<div class="error"><p>' . $message . '</p></div>';
        }
        if (self::is_woocommerce_active()) {
            $message = '';
            $message = sprintf(__('Buylocksmithdeals Customization Addon requires  %sWoocommerce%s', 'buylocksmithdeals-addon'), '<a href="https://wordpress.org/plugins/dc-woocommerce-multi-vendor/" target="_blank">', '</a>'
            );
            echo '<div class="error"><p>' . $message . '</p></div>';
        }
    }
    /**
     * Filters and Actions are bundled.
     * @return boolean
     */
    function init_hooks() {
               
        add_filter('wcmp_locate_template', array($this, 'wcmp_locate_template'), 1000, 4);
        add_action('wp_head', array($this, 'blsd_add_stylesheet'));
        add_action('wp_enqueue_scripts', array($this, 'blsd_add_js'));
        
          if (defined('DOING_AJAX')) {
             if (!class_exists('BuyLockSmithDealsCustomizationAjax', false)) {
                   include_once 'class-blsd-ajax.php';
                   
            $this->ajax = new BuyLockSmithDealsCustomizationAjax();
             }
        }
        add_filter( 'theme_page_templates', array($this, 'blsd_theme_page_templates') );
        add_filter( 'page_template', array($this, 'blsd_theme_page__update_template_path') );
        add_action('comment_post', array($this, 'save_comment_review'), 10, 3);
        add_action('wp', array($this, 'import_y_m_model'));
        add_filter( 'woocommerce_locate_template', array($this,'myplugin_woocommerce_locate_template'), 100, 3 );
        
        
        add_action( 'init', array($this,'register_booking_expired_order_status') );
        add_filter( 'wc_order_statuses', array($this,'add_booking_expired_to_order_statuses') );
        add_filter('woocommerce_login_redirect', array($this,'login_redirect'), 10, 2);
        
        add_filter( 'woocommerce_bookings_calculated_booking_cost_success_output', array( $this, 'change_text_booking_cost_datetime' ),8, 3 );
	
        add_filter('woocommerce_email_subject_customer_processing_order', array($this,'change_processing_email_subject'), 1, 2);
        
         add_action( 'woocommerce_email_header', array($this,'email_header_before'), 1, 2 );
         
             }
    
        
             
    function change_processing_email_subject( $subject, $order ) {
        global $woocommerce;
        $suborder_authorname='';
        $sub_orders = get_children( array('post_parent' => $order->get_id(), 'post_type' => 'shop_order' ) );
        foreach($sub_orders as $sorder){
           $suborder_id=$sorder->ID;
           $suborder_authorid=$sorder->post_author;
           $suborder_authorname=get_author_name( $suborder_authorid);
        }
        //$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        //$subject = sprintf( 'Hi %s, thanks for your order on %s', $order->billing_first_name, $blogname );    
        $subject = sprintf( 'Your order confirmation from %s', $suborder_authorname);   
        return $subject;
    }
    function email_header_before( $email_heading, $email ){
        $GLOBALS['email'] = $email;
    }   
    function change_text_booking_cost_datetime( $output, $display_price, $product ) {
           remove_filter( 'woocommerce_bookings_calculated_booking_cost_success_output', array( 'WC_Bookings_Addons', 'filter_output_cost' ), 10, 3 );
		parse_str( $_POST['form'], $posted );
                $date_time=$posted['wc_bookings_field_start_date_time'];
                $output=date('F,d Y - h:i a',strtotime($date_time));
                $booking_data = wc_bookings_get_posted_data( $posted, $product );
		$cost         = WC_Bookings_Cost_Calculation::calculate_booking_cost( $booking_data, $product );

                wp_send_json( array(
			'result'    => 'SUCCESS',
			'html'      => $output,
			'raw_price' => (float) $cost,
		) );
        }
    
    function login_redirect( $redirect_to, $user ) {
        // WCV dashboard — Uncomment the 3 lines below if using WC Vendors Free instead of WC Vendors Pro
        if (class_exists('WCV_Vendors') && WCV_Vendors::is_vendor( $user->ID ) ) {
        $redirect_to = get_permalink( get_option( 'wcvendors_vendor_dashboard_page_id' ) );
        }

        return $redirect_to; 
        }
        function myplugin_woocommerce_locate_template( $template, $template_name, $template_path ) {
          global $woocommerce;
          $_template = $template;
           if ( ! $template_path ) $template_path = $woocommerce->template_url;
          $plugin_path  = BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/woocommerce/';
          // Look within passed path within the theme - this is priority
          $template = locate_template(
            array(
              $template_path . $template_name,
              $template_name
            )
          );
          // Modification: Get the template from this plugin, if it exists
          if ( file_exists( $plugin_path . $template_name ) )
            $template = $plugin_path . $template_name;
          // Use default template
          if ( ! $template )
            $template = $_template;
          // Return what we found
          return $template;
        }
    /**
     * Including the classes which are used in wordpress admin area.
     * @return void
     */
    function blsdAdmin() {
        if (!class_exists('BuyLockSmithDealsCustomizationAdmin', false)) {
            include_once 'class-blsd-admin.php';
        }
        if (is_admin()) {
            $processdata = BuyLockSmithDealsCustomizationAdmin::instance();
            $GLOBALS['BuyLockSmithDealsCustomizationAdmin'] = $processdata;
        }
        if (!class_exists('BuyLockSmithDealsCustomizationAdminReport', false)) {
            include_once 'class-blsd-admin-reports.php';
        }
        if (is_admin()) {
            $processdata = BuyLockSmithDealsCustomizationAdminReport::instance();
            $GLOBALS['BuyLockSmithDealsCustomizationAdminReport'] = $processdata;
            
        }
       
    }
    
    
    /**
     * Including the class which is used for wordpress front end customization.
     * @return void
     */
      function blsdFrontEnd() {
      
        if (!is_admin()) {
              if (!class_exists('BuyLockSmithDealsCustomizationFrontEnd', false)) {
            include_once 'class-blsd-front.php';
        }
            $processdata = BuyLockSmithDealsCustomizationFrontEnd::instance();
            $GLOBALS['BuyLockSmithDealsCustomizationFrontEnd'] = $processdata;
        }
    }
    /**
     * Including the class which is used for wordpress user customer role customization.
     * @return void
     */
      function blsdClassForUserCustomer() {
      
        if (!is_admin()) {
              if (!class_exists('BuyLockSmithDealsCustomizationCustomer', false)) {
            include_once 'class-blsd-customer.php';
        }
            $processdata = BuyLockSmithDealsCustomizationCustomer::instance();
            $GLOBALS['BuyLockSmithDealsCustomizationCustomer'] = $processdata;
        }
    }
    
     /**
     * Including the class which is used for wordpress email.
     * @return void
     */
      function blsdEmail() {
              if (!class_exists('BuyLockSmithDealsCustomizationEmail', false)) {
            include_once 'class-blsd-email.php';
        }
            $processdata = BuyLockSmithDealsCustomizationEmail::instance();
            $GLOBALS['BuyLockSmithDealsCustomizationEmail'] = $processdata;
    }
    
    
    /**
     * Including the classes which are used for vendor dashboard or product functionality customization.
     * @return void
     */
    function blsdVendor() {
        if (!class_exists('BuyLockSmithDealsCustomizationVendor', false)) {
            include_once 'class-blsd-vendor.php';
        }
     //   if (!is_admin()) {
            $processdata = BuyLockSmithDealsCustomizationVendor::instance();
            $GLOBALS['BuyLockSmithDealsCustomizationVendor'] = $processdata;
       // }
    }
    
    /**
     * Including the classes which are used for ajax function and action register.
     * @return void
     */
    function BLSDRestApi() {
        if (!class_exists('BLSDRestApi', false)) {
             require_once "class.rest-api.php";
        }
     //   if (!is_admin()) {
            $processdata = BLSDRestApi::init();
            $GLOBALS['BLSDRestApi'] = $processdata;
       // }
    }
    
      

    
    /**
     * Including the classes which are used to assign or duplicate the products for the vendors.
     * @return void
     */
    function BuyLockSmithDeals_onload() {
      

        if (!class_exists('BuyLockSmithDealsAssignProductToVendor', false)) {
            include_once 'class.blsd-vendor-product.php';
        }
        if (!is_admin()) {
            $processdata = BuyLockSmithDealsAssignProductToVendor::init();
            $GLOBALS['BuyLockSmithDealsAssignProductToVendor'] = $processdata;
        }
    }

    /**
     * Overriding the WCMP template path and using path of current plugin template folder.
     * @global type $wp
     * @global type $WCMp
     * @param type $template
     * @param type $template_name
     * @param type $template_path
     * @param type $default_path
     * @return string
     */
    function wcmp_locate_template($template, $template_name, $template_path, $default_path) {


        global $wp, $WCMp;
        foreach ($wp->query_vars as $key => $value) {
            if (in_array($key, array('page', 'pagename'))) {
                continue;
            }

            if (has_action('wcmp_vendor_dashboard_' . $key . '_endpoint')) {
                $file = BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/dc-woocommerce-multi-vendor/templates/' . $template_name;
                if (file_exists($file)) { 
                   // if ($template_name == 'vendor-dashboard/product-manager/' . $key . '.php') {
                        return BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/dc-woocommerce-multi-vendor/templates/' . $template_name;
                   // }
                }
            }
            else{
                $file = BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/dc-woocommerce-multi-vendor/templates/' . $template_name;
                if (file_exists($file)) { 
                   // if ($template_name == 'vendor-dashboard/product-manager/' . $key . '.php') {
                        return BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/dc-woocommerce-multi-vendor/templates/' . $template_name;
                   // }
                }
            }
        }
    }
    






public static function blsd_api_credentials_table_name() {
    global $wpdb;
    $table_name = $wpdb->prefix . "blsd_api_credentials";
    return $table_name;
}

public static  function blsd_api_credentials_table() {

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $blsd_api_credentials_table_name = self::blsd_api_credentials_table_name();
    // create the ECPT metabox database table
    if ($wpdb->get_var("show tables like '$blsd_api_credentials_table_name'") != $blsd_api_credentials_table_name) {
        $sql = "CREATE TABLE `" . $blsd_api_credentials_table_name . "` (
                 `id` int(11) NOT NULL AUTO_INCREMENT,
                 `vendor_id` int(11) NOT NULL,
                 `url` varchar(100) DEFAULT NULL,
                 `api_key` varchar(100) DEFAULT NULL,
                 `status` int(11) DEFAULT '1',
                 `created_at` DATETIME DEFAULT NULL,
                 `updated_at` DATETIME DEFAULT NULL,
                 PRIMARY KEY (`id`)
                ) {$charset_collate};";


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}



public static function blsd_dispute_table_name() {
    global $wpdb;
    $table_name = $wpdb->prefix . "blsd_dispute";
    return $table_name;
}



public static  function blsd_dispute_table() {

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $blsd_dispute_table_name = self::blsd_dispute_table_name();
    // create the ECPT metabox database table
    if ($wpdb->get_var("show tables like '$blsd_dispute_table_name'") != $blsd_dispute_table_name) {
        $sql = "CREATE TABLE `" . $blsd_dispute_table_name . "` (
                 `id` int(11) NOT NULL AUTO_INCREMENT,
                 `user_id` int(11) NOT NULL,
                 `who_opose_user_id` int(11) NOT NULL,
                 `who_won_user_id` int(11) NOT NULL,
                 `role` varchar(100) DEFAULT NULL,
                 `order_id` int(11) NOT NULL,
                 `product_id` int(11) NOT NULL,
                 `status` int(11) DEFAULT '1',
                 `created_at` DATETIME DEFAULT NULL,
                 `updated_at` DATETIME DEFAULT NULL,
                 PRIMARY KEY (`id`)
                ) {$charset_collate};";


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}



public static function blsd_dispute_message_table_name() {
    global $wpdb;
    $table_name = $wpdb->prefix . "blsd_dispute_message";
    return $table_name;
}



public static  function blsd_dispute_message_table() {

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $blsd_dispute_message_table_name = self::blsd_dispute_message_table_name();
    // create the ECPT metabox database table
    if ($wpdb->get_var("show tables like '$blsd_dispute_message_table_name'") != $blsd_dispute_message_table_name) {
        $sql = "CREATE TABLE `" . $blsd_dispute_message_table_name . "` (
                 `id` int(11) NOT NULL AUTO_INCREMENT,
                 `dispute_id` int(11) NOT NULL,
                 `title` text DEFAULT NULL,
                 `message` text DEFAULT NULL,
                 `sender_id` int(11) NOT NULL,
                 `status` int(11) DEFAULT '1',
                 `accept_status` int(11) DEFAULT '0',
                 `created_at` DATETIME DEFAULT NULL,
                 `updated_at` DATETIME DEFAULT NULL,
                 PRIMARY KEY (`id`)
                ) {$charset_collate};";


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

public static function blsd_dispute_attachment_table_name() {
    global $wpdb;
    $table_name = $wpdb->prefix . "blsd_dispute_attachment";
    return $table_name;
}



public static  function blsd_dispute_attachment_message_table() {

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $blsd_dispute_attachment_table_name = self::blsd_dispute_attachment_table_name();
    // create the ECPT metabox database table
    if ($wpdb->get_var("show tables like '$blsd_dispute_attachment_table_name'") != $blsd_dispute_attachment_table_name) {
        $sql = "CREATE TABLE `" . $blsd_dispute_attachment_table_name . "` (
                 `id` int(11) NOT NULL AUTO_INCREMENT,
                 `dispute_id` int(11) NOT NULL,
                 `dispute_message_id` int(11) NOT NULL,
                 `file_name` text DEFAULT NULL,
                 `uploaded_at` DATETIME DEFAULT NULL,
                 PRIMARY KEY (`id`)
                ) {$charset_collate};";


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
public static function blsd_status_table_name() {
    global $wpdb;
    $table_name = $wpdb->prefix . "blsd_status";
    return $table_name;
}



public static  function blsd_status_table() {

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $blsd_status_table_name = self::blsd_status_table_name();
    // create the ECPT metabox database table
    if ($wpdb->get_var("show tables like '$blsd_status_table_name'") != $blsd_status_table_name) {
        $sql = "CREATE TABLE `" . $blsd_status_table_name . "` (
                 `id` int(11) NOT NULL AUTO_INCREMENT,
                 `name` text DEFAULT NULL,
                 PRIMARY KEY (`id`)
                ) {$charset_collate};";


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
     //   $this->blsd_insert_status_table();
    }
	
	
}


function blsd_insert_status_table(){

        global $wpdb;
$table_name = self::blsd_status_table_name();
 
     if ( ! function_exists('dbDelta') ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
      $rows_affected = $wpdb->insert( $table_name, array( 'name' => 'Open' ));
  //    dbDelta( $rows_affected );
      $rows_affected = $wpdb->insert( $table_name, array( 'name' => 'Processing' ));
    //  dbDelta( $rows_affected );
      $rows_affected = $wpdb->insert( $table_name, array( 'name' => 'Closed' ));
    //  dbDelta( $rows_affected );

}



function blsd_add_stylesheet() 
{
     global $wp_styles;
        $current_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );
// Get the page slug
 $slug = $current_page->post_name;
 $srcs=[];
 if($slug=='dashboard'){
    wp_enqueue_style( 'blsd_front_style', BUYLOCKSMITH_DEALS_ASSETS_PATH.( 'css/vendor_dashoard.css') );
    wp_enqueue_style('wcmp-datatable-bs-style', WP_PLUGIN_URL . '/dc-woocommerce-multi-vendor/lib/dataTable/dataTables.bootstrap.min.css' );
  
  }
  wp_enqueue_style( 'blsd_front_style_common', BUYLOCKSMITH_DEALS_ASSETS_PATH.( 'css/blsd-common.css') );
  
  
  
 
 
}

function blsd_add_js(){
    wp_enqueue_script( 'dataTables-js', WP_PLUGIN_URL . '/dc-woocommerce-multi-vendor/lib/dataTable/jquery.dataTables.min.js', array( 'jquery' ) );
    wp_enqueue_script( 'dataTables-fixedHeader-js', WP_PLUGIN_URL . '/dc-woocommerce-multi-vendor/lib/dataTable/dataTables.fixedHeader.min.js', array( 'jquery' ) );
} 






public static function blsd_get_status(){
    global $wpdb;
  $table_name_status = self::blsd_status_table_name();
                    $query = "SELECT * FROM $table_name_status";
            return    $results_status = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);   
}


public static function blsd_get_userFullName($user_id){
    $user_data = get_user_by('ID', $user_id);
                    $name = get_user_meta($user_id, 'first_name', true).' '.get_user_meta($user_id, 'last_name', true);
                    if(trim($name)==''){
                    $name = $user_data->user_login;
                    }
                    return $name;
}


function blsm_get_vendor_open_dispute_count($user_id = 0){
             global $wpdb;
             if($user_id==0){
                 $user_id = get_current_user_id();
             }
          $table_name = self::blsd_dispute_table_name();

                        $query = "SELECT count(id) as total_open_dispute from $table_name WHERE $table_name.status = 1 and (user_id=$user_id or who_opose_user_id=$user_id) ";
                        $results_dispute_data = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
                        
                        return $results_dispute_data[0]['total_open_dispute'];
            
        }
        
        
function blsd_theme_page_templates( $page_template )
{   
    $page_template['/blsd_page_template/search_step.php']='Search Steps';
    return $page_template;
}
        
function blsd_theme_page__update_template_path( $page_template )
{ 
  $template_path =  get_page_template_slug( get_the_ID() );
  $template_path_array = explode('/blsd_page_template/', $template_path);
    
    if ( isset($template_path_array[1])) { 
        $template = BUYLOCKSMITH_DEALS_PLUGIN_DIR.'/page_template/'.$template_path_array[1];
        $page_template = $template;
         $this->blsd_update_user_country();
    }
  
    return $page_template;
}
function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function blsd_update_user_country(){
    if(!isset($_REQUEST['blsd_regional_product_listing_country'])){ 
    $ip = $this->getRealIpAddr(); // This will contain the ip of the request

// You can use a more sophisticated method to retrieve the content of a webpage with php using a library or something
// We will retrieve quickly with the file_get_contents
$dataArray = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));

if(isset($dataArray->geoplugin_countryCode)){
    if($dataArray->geoplugin_countryCode!='' && $dataArray->geoplugin_countryCode!=NULL && $dataArray->geoplugin_countryCode!=null){
        $_REQUEST['blsd_regional_product_listing_country'] = $dataArray->geoplugin_countryCode;
    }
}  
}

}


public static function blsd_get_vendor_list($search =''){ 
    global $wpdb;
        /* $args = array(
         'role'    => 'dc_vendor',
         'orderby' => 'user_nicename',
         'order'   => 'ASC'
     );
     return $users = get_users( $args ); */
     $where='';
    if($search != ''){
        $where .="AND (m.meta_key LIKE 'first_name' AND m.meta_value LIKE '%$search%' OR m.meta_key LIKE 'last_name' AND m.meta_value LIKE '%$search%' OR u.display_name LIKE '%$search%')";
    }
    $where .="AND m.meta_key LIKE 'wp_capabilities' AND m.meta_value LIKE '%dc_vendor%'";
    
    
    $per_page = self::get_record_limit();

    $current_page = self::get_current_page();
    $offset = ($current_page - 1) * $per_page;
    
    $order_limit = " order by u.user_nicename ASC LIMIT $offset ,$per_page";
    
    $sql="Select * From {$wpdb->prefix}users u,{$wpdb->prefix}usermeta m WHERE 1=1 AND u.ID = m.user_id $where $order_limit";
    $data= $wpdb->get_results($sql);
    
    $sql_total="Select * From {$wpdb->prefix}users u,{$wpdb->prefix}usermeta m WHERE 1=1 AND u.ID = m.user_id $where";
    $data_total= $wpdb->get_results($sql_total);
    $total=count($data_total);
     return array(
            "data" =>$data,
            "total_items" => $total,
            "total_pages" => ceil($total / $per_page),
            "per_page" => $per_page,
        );
    
}


function save_comment_review($commentID, $comment, $status) {
    $social_link = [];
    
    if(isset($_REQUEST['rating']) && isset($_REQUEST['comment_post_ID'])){
        if($_REQUEST['rating']>3 && $_REQUEST['comment_post_ID'] !='' && $_REQUEST['comment_post_ID']!=0){
            $product_id = $_REQUEST['comment_post_ID'];
            $post_data = get_post($product_id);
            if(isset($post_data->post_type)){
                if($post_data->post_type=='product'){
                   $post_author = $post_data->post_author;
                   $social_link['facebook'] = get_user_meta($post_author,'_vendor_fb_profile', true);
                   $social_link['twitter'] = get_user_meta($post_author,'_vendor_twitter_profile', true);
                   $social_link['linkedIn'] = get_user_meta($post_author,'_vendor_linkdin_profile', true);
                   $social_link['google_plus'] = get_user_meta($post_author,'_vendor_google_plus_profile', true);
                   $social_link['youtube'] = get_user_meta($post_author,'_vendor_youtube', true);
                   $social_link['instagram'] = get_user_meta($post_author,'_vendor_instagram', true);
                }
            }
            if(count($social_link)>0){
            $customer_id = get_current_user_id();
            BuyLockSmithDealsCustomizationEmail::blsd_email_send_vendor_social_links_for_review($social_link,$customer_id);
        } 
        }
       
    }
    
  
    }
    
    public static function blsd_get_status_name_by_id($status_id){
        global $wpdb;
  $table_name_status = self::blsd_status_table_name();
                    $query = "SELECT name FROM $table_name_status where id=$status_id";
            $status_result = $results_status = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);  
            
            $status = '';
            if(isset($status_result[0]['name'])){
                $status =  $status_result[0]['name'];
            }
            return $status;
    }
    public static function blsd_y_m_model_table_name() {
        global $wpdb;
        $table_name = $wpdb->prefix . "blsd_y_m_model";
        return $table_name;
    }
    public static function blsd_y_m_model_table(){
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $blsd_y_m_model_table_name = self::blsd_y_m_model_table_name();
        // create the ECPT metabox database table
        if ($wpdb->get_var("show tables like '$blsd_y_m_model_table_name'") != $blsd_y_m_model_table_name) {
            $sql = "CREATE TABLE `" . $blsd_y_m_model_table_name . "` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `maker` varchar(255) DEFAULT NULL,
                     `model` varchar(255) DEFAULT NULL,
                     `year` int(11) DEFAULT NULL,
                     PRIMARY KEY (`id`)
                    ) {$charset_collate};";


            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
         //   $this->blsd_insert_status_table();
        }
    }
    
    public static function import_y_m_model(){
        global $wpdb;
        if(isset($_REQUEST['import_y_m_csv'])){
            $table_name=self::blsd_y_m_model_table_name();
            $uploadPath=wp_upload_dir(); 
            $fileName='y_m_model.csv';
            $uploadPath_file = $uploadPath[baseurl] .'/csv/'.$fileName; 
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $totalInserted = 0;
            $csvFile = fopen($uploadPath_file, 'r');
            fgetcsv($csvFile); 
            while(($csvData = fgetcsv($csvFile)) !== FALSE){
                $csvData = array_map("utf8_encode", $csvData);
                $maker = trim($csvData[0]);
                $model = trim($csvData[1]);
                $year = trim($csvData[2]);
                // Insert Record
                $wpdb->insert($table_name, array(
                    'maker' =>$maker,
                    'model' =>$model,
                    'year' =>$year,
                ));
            }
        }
    }
    
     public static function get_all_cars_frontend($brand_array=[],$select_field='',$where_field=''){
            global $wpdb;
            $where='';
            $car_list=[];
            $all=[];
            $table_name=BuyLockSmithDealsCustomizationAddon::blsd_y_m_model_table_name();
            if(!empty($brand_array)){
                foreach($brand_array as $brand){
                  $row=$wpdb->get_row("Select * from $table_name where id=$brand",ARRAY_A);
                  if($row['year'] == 0){
                      $brand_name=explode('All ',$row['maker']);
                      $all[]=$brand_name[1];
                  }
                  else{
                      $car_list[]=$row['id'];
                  }
                }
                $allcarlist=implode(',',$car_list);
                $allmaker_brand=implode("','",$all);
                $condition ='';
                if($where_field !=''){
                  $condition=" AND $where_field";
                }
                if(!empty($allcarlist)){
                    $where .=" AND (id NOT IN ($allcarlist))";
                }
                if(!empty($allmaker_brand)){
                    $where .=" AND maker NOT IN  ('$allmaker_brand')";
                }
                $where .=" AND maker NOT LIKE 'All %' $condition ";
            }
            else{
                
            $condition ='';
                if($where_field !=''){
                  $condition=" AND $where_field";
                }
                $where=" AND maker NOT LIKE 'All %' $condition ";
           
            }
           // echo "Select DISTINCT $select_field from $table_name where 1=1 $where";
            return $result=$wpdb->get_results("Select DISTINCT $select_field from $table_name where 1=1 $where",ARRAY_A);
            
        }
        
        public static function render_pagination($current_page_url, $pages = '', $range = 3) {

        if (get_query_var('paged')) {
            $paged = get_query_var('paged');
        } elseif (get_query_var('page')) {
            $paged = get_query_var('page');
        } elseif (get_query_var('cpage')) {
            $paged = get_query_var('cpage');
        } else {
            $paged = isset($_GET['cpage']) ? $_GET['cpage'] : 1;
        }

        $showitems = ($range * 2) + 1;

        if (empty($paged))
            $paged = 1;

        if ($pages == '' && $pages != 0) {
            if (!$pages) {
                $pages = 1;
            }
        }

        if (1 != $pages) {
            echo "<div class='paginationCustom'>";
            if ($paged > 2 && $paged > $range + 1 && $showitems < $pages)
                echo "<a href='" . self::get_add_paged_query_arg($current_page_url, 1) . "'>&laquo;</a>";
            if ($paged > 1 && $showitems < $pages)
                echo "<a href='" . self::get_add_paged_query_arg($current_page_url, ($paged - 1)) . "'>&lsaquo;</a>";

            for ($i = 1; $i <= $pages; $i++) {
                if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems )) {
                    echo ($paged == $i) ? "<span class='current'>" . $i . "</span>" : "<a href='" . self::get_add_paged_query_arg($current_page_url, $i) . "' class='inactive' >" . $i . "</a>";
                }
            }

            if ($paged < $pages && $showitems < $pages)
                echo "<a href='" . self::get_add_paged_query_arg($current_page_url, ($paged + 1)) . "'>&rsaquo;</a>";
            if ($paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages)
                echo "<a href='" . self::get_add_paged_query_arg($current_page_url, $pages) . "'>&raquo;</a>";
            echo "</div>\n";
        }
    }
    public static function get_add_paged_query_arg($current_page_url, $page) {
        return add_query_arg('cpage', $page, $current_page_url);
    }
    
    public static function get_current_page() {
        if (get_query_var('cpage')) {
            $paged = get_query_var('cpage');
        } else {
            $paged = isset($_GET['cpage']) ? $_GET['cpage'] : 1;
        }
        return $paged;
    }
    
    public static function get_record_limit() {
        $record_limit = get_option('posts_per_page');
        // $record_limit = 1;	 
        return $record_limit;
    }
    
    
    
    
    function register_booking_expired_order_status() {
               
    register_post_status( 'wc-awaiting-shipment', array(
        'label'                     => 'Expired',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>' )
    ) );
}

// Add to list of WC Order statuses
function add_booking_expired_to_order_statuses( $order_statuses ) {
    $new_order_statuses = array();
    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {
        $new_order_statuses[ $key ] = $status;
        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-awaiting-shipment'] = 'Expired';
        }
    }
    return $new_order_statuses;
}
         function get_address_from_coordinates_global($lat,$lng){
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false&key=AIzaSyCuC3PEVYk9RGDIIuLM1ur-sQ7y73ff3eY';
           
            $json = @file_get_contents($url);
            $data=json_decode($json);
            $status = $data->status;
            if($status=="OK")
            {
             return json_encode($data->results[0]);
            }
            else
            {
              return false;
            }
        }
        
        
        
         function code_encrypt($string, $key){
        $result = "";
        for($i=0; $i<strlen($string); $i++){
                $char = substr($string, $i, 1);
                $keychar = substr($key, ($i % strlen($key))-1, 1);
                $char = chr(ord($char)+ord($keychar));
                $result.=$char;
        }
        $salt_string = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxys0123456789~!@#$^&*()_+`-={}|:<>?[]\;',./";
        $length = rand(1, 15);
        $salt = "";
        for($i=0; $i<=$length; $i++){
                $salt .= substr($salt_string, rand(0, strlen($salt_string)), 1);
        }
        $salt_length = strlen($salt);
        $end_length = strlen(strval($salt_length));
        return base64_encode($result.$salt.$salt_length.$end_length);
    }
    function code_decrypt($string, $key){
        $result = "";
        $string = base64_decode($string);
        $end_length = intval(substr($string, -1, 1));
        $string = substr($string, 0, -1);
        $salt_length = intval(substr($string, $end_length*-1, $end_length));
        $string = substr($string, 0, $end_length*-1+$salt_length*-1);
        for($i=0; $i<strlen($string); $i++){
                $char = substr($string, $i, 1);
                $keychar = substr($key, ($i % strlen($key))-1, 1);
                $char = chr(ord($char)-ord($keychar));
                $result.=$char;
        }
        return $result;
    }
    
     function blsd_wcmp_vendor_dashboard_page_id() {
            if (get_wcmp_vendor_settings('wcmp_vendor', 'vendor', 'general')) {
                if (function_exists('icl_object_id')) {
                    return icl_object_id((int) self::blsd_get_wcmp_vendor_settings('wcmp_vendor', 'vendor', 'general'), 'page', false, ICL_LANGUAGE_CODE);
                }
                return (int) self::blsd_get_wcmp_vendor_settings('wcmp_vendor', 'vendor', 'general');
            }
            return false;
        }
        
        function blsd_get_wcmp_vendor_settings($name = '', $tab = '', $subtab = '', $default = false) {
        if (empty($tab) && empty($name)) {
            return $default;
        }
        if (empty($tab)) {
            return get_wcmp_global_settings($name, $default);
        }
        if (empty($name)) {
            return get_option("wcmp_{$tab}_settings_name", $default);
        }
        if (!empty($subtab)) {
            $settings = get_option("wcmp_{$tab}_{$subtab}_settings_name", $default);
        } else {
            $settings = get_option("wcmp_{$tab}_settings_name", $default);
        }
        if (!isset($settings[$name]) || empty($settings[$name])) {
            return $default;
        }
        return $settings[$name];
    }
    
    public static function get_all_admin_list_global() {
        $args = array(
            'role' => 'administrator',
            'orderby' => 'user_nicename',
            'order' => 'ASC',
        );
        return $administrator = get_users($args);
    }

    
    


}

?>