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
    
   
    public static function send_sms($phone_no,$message_body){
      include  BUYLOCKSMITH_DEALS_PLUGIN_DIR.'/page_template/twillo-sms.php';
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
        } else {
            self::blsd_api_credentials_table();
            self::blsd_dispute_table();
            self::blsd_dispute_message_table();
            self::blsd_dispute_attachment_message_table();
            self::blsd_status_table();
            self::blsd_y_m_model_table();
            self::blsd_deals_custom_design();
            self::blsd_phone_country();
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
        add_filter('wcmp_afm_locate_template', array($this, 'wcmp_afm_locate_template'),10,4);
		
        add_filter('wcmp_locate_template', array($this, 'wcmp_locate_template'), 1000, 4);
        add_action('wp_head', array($this, 'blsd_add_stylesheet'));
        add_action('wp_enqueue_scripts', array($this, 'blsd_add_js'));
        if (defined('DOING_AJAX')) {
            if (!class_exists('BuyLockSmithDealsCustomizationAjax', false)) {
                include_once 'class-blsd-ajax.php';

                $this->ajax = new BuyLockSmithDealsCustomizationAjax();
                $obj=new WC_Bookings_Ajax();
                
            }
        }
        add_filter('theme_page_templates', array($this, 'blsd_theme_page_templates'));
        add_filter('page_template', array($this, 'blsd_theme_page__update_template_path'));
        add_action('comment_post', array($this, 'save_comment_review'), 10, 3);
        add_action('wp', array($this, 'import_y_m_model'));
        add_filter('woocommerce_locate_template', array($this, 'myplugin_woocommerce_locate_template'), 100, 3);
        

        add_action('init', array($this, 'register_booking_expired_order_status'));
        add_filter('wc_order_statuses', array($this, 'add_booking_expired_to_order_statuses'));
        add_filter('woocommerce_login_redirect', array($this, 'login_redirect'), 10, 2);

        add_filter('woocommerce_bookings_calculated_booking_cost_success_output', array($this, 'change_text_booking_cost_datetime'), 8, 3);

        add_filter('woocommerce_email_subject_customer_processing_order', array($this, 'change_processing_email_subject'), 1, 2);

		add_action('woocommerce_email_header', array($this, 'email_header_before'), 1, 2);
        add_filter('comment_notification_text', array($this, 'update_comment_notification_text'), 99999, 2);
        add_filter('comment_moderation_text', array($this, 'update_comment_notification_text'), 99999, 2);
        add_filter('wp_new_user_notification_email', array($this, 'update_wp_new_user_notification_email'), 1000, 3);
        add_filter('wp_new_user_notification_email_admin', array($this, 'update_wp_new_user_notification_email_admin'), 1000, 3);
        add_filter('password_change_email', array($this, 'update_password_change_email'), 1000, 3);
        add_filter('email_change_email', array($this, 'update_email_change_email'), 999, 3);
    }
    

    function update_comment_notification_text($notify_message, $comment_ID) {
        $comment_id_7 = get_comment($comment_ID);
        $comment_post_id = $comment_id_7->comment_post_ID;
        $postData = get_post($comment_post_id);
        $vendor_id = $postData->post_author;
        $notify_message = explode(PHP_EOL, $notify_message);

        $notify_message = implode('</br>', $notify_message);

        $body = '';
        $body .= '<tr><td>';
        $body .= '<p>Message : ' . $notify_message . '</p>';
        $body .= '</br></br>';
        $body .= '</td></tr>';

        /*         * ********************************** */
        
        $mailer = WC()->mailer();
        $template_html = '/emails/all_custom_mail_template.php';
        $recipient = $to;
        $name = '';
        $subject = __($subject);
        $attachments = [];
        $notify_message = wc_get_template_html(
                $template_html, array(
            'user_id' => $vendor_id,
            'email_heading' => '{vendor_logo}',
            'additional_content' => '',
            'sent_to_admin' => false,
            'plain_text' => false,
            'email' => $mailer,
            'name' => $name,
            'mail_heading' => '',
            'body' => $body,
                )
        );


        return $notify_message;
    }

    function update_wp_new_user_notification_email($wp_new_user_notification_email, $user, $blogname) {

        if (isset($wp_new_user_notification_email['message'])) {

            $vendor_id = $user->ID;
            $notify_message = $wp_new_user_notification_email['message'];
            $notify_message = explode(PHP_EOL, $notify_message);

            $notify_message = implode('</br>', $notify_message);

            $body = '';
            $body .= '<tr><td>';
            $body .= '<p>Message : ' . $notify_message . '</p>';
            $body .= '</br></br>';
            $body .= '</td></tr>';

            /*             * ********************************** */
            $name = self::blsd_get_userFullName($vendor_id);
            $mailer = WC()->mailer();
            $template_html = '/emails/all_custom_mail_template.php';
            $recipient = $to;
            $subject = __($subject);
            $attachments = [];
            $notify_message = wc_get_template_html(
                    $template_html, array(
                'user_id' => $vendor_id,
                'email_heading' => '{vendor_logo}',
                'additional_content' => '',
                'sent_to_admin' => false,
                'plain_text' => false,
                'email' => $mailer,
                'name' => $name,
                'mail_heading' => '',
                'body' => $body,
                    )
            );
            $wp_new_user_notification_email['message'] = $notify_message;
        }

        return $wp_new_user_notification_email;
    }
//( 'email_change_email', $email_change_email, $user, $userdata )
    function update_email_change_email($email_change_email, $user, $userdata) {

        if (isset($email_change_email['message'])) {

            $vendor_id = $user->ID;
            $pass_change_text = __(
				'This notice confirms that your password was changed on ###SITENAME###.

If you did not change your password, please contact the Site Administrator at
###ADMIN_EMAIL###

This email has been sent to ###EMAIL###

Regards,
All at ###SITENAME###
###SITEURL###'
			);
            $notify_message =  $pass_change_text;
            $notify_message = explode(PHP_EOL, $notify_message);

            $notify_message = implode('</br>', $notify_message);

            $body = '';
            $body .= '<tr><td>';
            $body .= '<p>Message : ' . $notify_message . '</p>';
            $body .= '</br></br>';
            $body .= '</td></tr>';

            /*             * ********************************** */
            $name = '###USERNAME###';
            $mailer = WC()->mailer();
            $template_html = '/emails/all_custom_mail_template.php';
            $recipient = $to;
            $subject = __($subject);
            $attachments = [];
            $notify_message = wc_get_template_html(
                    $template_html, array(
                'user_id' => $vendor_id,
                'email_heading' => '{vendor_logo}',
                'additional_content' => '',
                'sent_to_admin' => false,
                'plain_text' => false,
                'email' => $mailer,
                'name' => $name,
                'mail_heading' => '',
                'body' => $body,
                    )
            );
            $email_change_email['message'] = $notify_message;
        }

        return $email_change_email;
    }
    function update_wp_new_user_notification_email_admin($wp_new_user_notification_email, $user, $blogname) {

        if (isset($wp_new_user_notification_email['message'])) {

            $vendor_id = $user->ID;
            $notify_message = $wp_new_user_notification_email['message'];
            $notify_message = explode(PHP_EOL, $notify_message);

            $notify_message = implode('</br>', $notify_message);

            $body = '';
            $body .= '<tr><td>';
            $body .= '<p>Message : ' . $notify_message . '</p>';
            $body .= '</br></br>';
            $body .= '</td></tr>';

            /*             * ********************************** */
            $name = '';
            $mailer = WC()->mailer();
            $template_html = '/emails/all_custom_mail_template.php';
            $recipient = $to;
            $subject = __($subject);
            $attachments = [];
            $notify_message = wc_get_template_html(
                    $template_html, array(
                'user_id' => $vendor_id,
                'email_heading' => '{vendor_logo}',
                'additional_content' => '',
                'sent_to_admin' => false,
                'plain_text' => false,
                'email' => $mailer,
                'name' => $name,
                'mail_heading' => '',
                'body' => $body,
                    )
            );
            $wp_new_user_notification_email['message'] = $notify_message;
        }

        return $wp_new_user_notification_email;
    }

    function update_password_change_email($wp_new_user_notification_email, $user, $blogname) {
       
        if (isset($wp_new_user_notification_email['message'])) {

            $vendor_id = $user->ID;
            $notify_message = $wp_new_user_notification_email['message'];
            
            		$notify_message = __(
				'
This notice confirms that your password was changed on ###SITENAME###.

If you did not change your password, please contact the Site Administrator at
###ADMIN_EMAIL###

This email has been sent to ###EMAIL###

Regards,
All at ###SITENAME###
###SITEURL###'
			);
            
            
            $notify_message = explode(PHP_EOL, $notify_message);

            $notify_message = implode('</br>', $notify_message);

            $body = '';
            $body .= '<tr><td>';
            $body .= '<p>' . $notify_message . '</p>';
            $body .= '</br></br>';
            $body .= '</td></tr>';

            /*             * ********************************** */
            $name = '###USERNAME###';
            $mailer = WC()->mailer();
            $template_html = '/emails/all_custom_mail_template.php';
            $recipient = $to;
            $subject = __($subject);
            $attachments = [];
            $notify_message = wc_get_template_html(
                    $template_html, array(
                'user_id' => $vendor_id,
                'email_heading' => '{vendor_logo}',
                'additional_content' => '',
                'sent_to_admin' => false,
                'plain_text' => false,
                'email' => $mailer,
                'name' => $name,
                'mail_heading' => '',
                'body' => $body,
                    )
            );
            $wp_new_user_notification_email['message'] = $notify_message;
        }

        return $wp_new_user_notification_email;
    }

    function change_processing_email_subject($subject, $order) {
        global $woocommerce;
        $suborder_authorname = '';
        $sub_orders = get_children(array('post_parent' => $order->get_id(), 'post_type' => 'shop_order'));
        foreach ($sub_orders as $sorder) {
            $suborder_id = $sorder->ID;
            $suborder_authorid = $sorder->post_author;
            $suborder_authorname = get_author_name($suborder_authorid);
        }
        //$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        //$subject = sprintf( 'Hi %s, thanks for your order on %s', $order->billing_first_name, $blogname );    
        $subject = sprintf('Your order confirmation from %s', $suborder_authorname);
        return $subject;
    }

    function email_header_before($email_heading, $email) {
        $GLOBALS['email'] = $email;
    }

    function change_text_booking_cost_datetime($output, $display_price, $product) {
        remove_filter('woocommerce_bookings_calculated_booking_cost_success_output', array('WC_Bookings_Addons', 'filter_output_cost'), 10, 3);
        parse_str($_POST['form'], $posted);
        $date_time = $posted['wc_bookings_field_start_date_time'];
        $output = '<h6 style="font-size: 14px;color: #000; font-weight: 500; font-family: inherit;">Your preferred appointment time is:</h6>';
        $output .= '<h6 style="font-size: 14px;color: #000; font-weight: 400; font-family: inherit;">'.date('F, d Y - h:i a', strtotime($date_time)).'</h6>';
        $output .= '<h6 style="font-size: 14px;color: #000; font-weight: 500; font-family: inherit;">Once the job is booked, the locksmith will confirm the appointment with you.</h6>';
        $booking_data = wc_bookings_get_posted_data($posted, $product);
        $cost = WC_Bookings_Cost_Calculation::calculate_booking_cost($booking_data, $product);

        wp_send_json(array(
            'result' => 'SUCCESS',
            'html' => $output,
            'raw_price' => (float) $cost,
        ));
    }

    function login_redirect($redirect_to, $user) {
        // WCV dashboard — Uncomment the 3 lines below if using WC Vendors Free instead of WC Vendors Pro
        if (class_exists('WCV_Vendors') && WCV_Vendors::is_vendor($user->ID)) {
            $redirect_to = get_permalink(get_option('wcvendors_vendor_dashboard_page_id'));
        }

        return $redirect_to;
    }

    function myplugin_woocommerce_locate_template($template, $template_name, $template_path) {
        global $woocommerce;
        $_template = $template;
        if (!$template_path)
            $template_path = $woocommerce->template_url;
        $plugin_path = BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/woocommerce/';
        // Look within passed path within the theme - this is priority
        $template = locate_template(
                array(
                    $template_path . $template_name,
                    $template_name
                )
        );
        // Modification: Get the template from this plugin, if it exists
        if (file_exists($plugin_path . $template_name))
            $template = $plugin_path . $template_name;
        // Use default template
        if (!$template)
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
            } else {
                $file = BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/dc-woocommerce-multi-vendor/templates/' . $template_name;
                if (file_exists($file)) {
                    // if ($template_name == 'vendor-dashboard/product-manager/' . $key . '.php') {
                    return BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/dc-woocommerce-multi-vendor/templates/' . $template_name;
                    // }
                }
            }
        }
    }
    
    function wcmp_afm_locate_template($template, $template_name, $template_path, $default_path){
        //echo $template_name;
        $file = BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/wcmp-afm/' . $template_name;
         if (file_exists($file)) {
             return BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/wcmp-afm/' . $template_name;
         }
        return $template;
    }

    public static function blsd_api_credentials_table_name() {
        global $wpdb;
        $table_name = $wpdb->prefix . "blsd_api_credentials";
        return $table_name;
    }

    public static function blsd_api_credentials_table() {

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
    public static function blsd_phone_country_table_name(){
         global $wpdb;
        $table_name = $wpdb->prefix . "phone_country_code";
        return $table_name;
    }
    public static function blsd_phone_country(){
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $blsd_phone_country_table_name = self::blsd_phone_country_table_name();
        // create the ECPT metabox database table
        if ($wpdb->get_var("show tables like '$blsd_phone_country_table_name'") != $blsd_phone_country_table_name) {
            $sql = "CREATE TABLE `" . $blsd_phone_country_table_name . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `iso` char(2) NOT NULL,
                `name` varchar(80) NOT NULL,
                `nicename` varchar(80) NOT NULL,
                `iso3` char(3) DEFAULT NULL,
                `numcode` smallint(6) DEFAULT NULL,
                `phonecode` int(5) NOT NULL,
                PRIMARY KEY (`id`)
                ) {$charset_collate};";


            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
           self::insert_phone_country();
        }
         
    }
    public static function insert_phone_country(){
        global $wpdb;
         $blsd_phone_country_table_name = self::blsd_phone_country_table_name();
        $sql= "INSERT INTO $blsd_phone_country_table_name (`id`, `iso`, `name`, `nicename`, `iso3`, `numcode`, `phonecode`) VALUES
(1, 'AF', 'AFGHANISTAN', 'Afghanistan', 'AFG', 4, 93),
(2, 'AL', 'ALBANIA', 'Albania', 'ALB', 8, 355),
(3, 'DZ', 'ALGERIA', 'Algeria', 'DZA', 12, 213),
(4, 'AS', 'AMERICAN SAMOA', 'American Samoa', 'ASM', 16, 1684),
(5, 'AD', 'ANDORRA', 'Andorra', 'AND', 20, 376),
(6, 'AO', 'ANGOLA', 'Angola', 'AGO', 24, 244),
(7, 'AI', 'ANGUILLA', 'Anguilla', 'AIA', 660, 1264),
(8, 'AQ', 'ANTARCTICA', 'Antarctica', NULL, NULL, 0),
(9, 'AG', 'ANTIGUA AND BARBUDA', 'Antigua and Barbuda', 'ATG', 28, 1268),
(10, 'AR', 'ARGENTINA', 'Argentina', 'ARG', 32, 54),
(11, 'AM', 'ARMENIA', 'Armenia', 'ARM', 51, 374),
(12, 'AW', 'ARUBA', 'Aruba', 'ABW', 533, 297),
(13, 'AU', 'AUSTRALIA', 'Australia', 'AUS', 36, 61),
(14, 'AT', 'AUSTRIA', 'Austria', 'AUT', 40, 43),
(15, 'AZ', 'AZERBAIJAN', 'Azerbaijan', 'AZE', 31, 994),
(16, 'BS', 'BAHAMAS', 'Bahamas', 'BHS', 44, 1242),
(17, 'BH', 'BAHRAIN', 'Bahrain', 'BHR', 48, 973),
(18, 'BD', 'BANGLADESH', 'Bangladesh', 'BGD', 50, 880),
(19, 'BB', 'BARBADOS', 'Barbados', 'BRB', 52, 1246),
(20, 'BY', 'BELARUS', 'Belarus', 'BLR', 112, 375),
(21, 'BE', 'BELGIUM', 'Belgium', 'BEL', 56, 32),
(22, 'BZ', 'BELIZE', 'Belize', 'BLZ', 84, 501),
(23, 'BJ', 'BENIN', 'Benin', 'BEN', 204, 229),
(24, 'BM', 'BERMUDA', 'Bermuda', 'BMU', 60, 1441),
(25, 'BT', 'BHUTAN', 'Bhutan', 'BTN', 64, 975),
(26, 'BO', 'BOLIVIA', 'Bolivia', 'BOL', 68, 591),
(27, 'BA', 'BOSNIA AND HERZEGOVINA', 'Bosnia and Herzegovina', 'BIH', 70, 387),
(28, 'BW', 'BOTSWANA', 'Botswana', 'BWA', 72, 267),
(29, 'BV', 'BOUVET ISLAND', 'Bouvet Island', NULL, NULL, 0),
(30, 'BR', 'BRAZIL', 'Brazil', 'BRA', 76, 55),
(31, 'IO', 'BRITISH INDIAN OCEAN TERRITORY', 'British Indian Ocean Territory', NULL, NULL, 246),
(32, 'BN', 'BRUNEI DARUSSALAM', 'Brunei Darussalam', 'BRN', 96, 673),
(33, 'BG', 'BULGARIA', 'Bulgaria', 'BGR', 100, 359),
(34, 'BF', 'BURKINA FASO', 'Burkina Faso', 'BFA', 854, 226),
(35, 'BI', 'BURUNDI', 'Burundi', 'BDI', 108, 257),
(36, 'KH', 'CAMBODIA', 'Cambodia', 'KHM', 116, 855),
(37, 'CM', 'CAMEROON', 'Cameroon', 'CMR', 120, 237),
(38, 'CA', 'CANADA', 'Canada', 'CAN', 124, 1),
(39, 'CV', 'CAPE VERDE', 'Cape Verde', 'CPV', 132, 238),
(40, 'KY', 'CAYMAN ISLANDS', 'Cayman Islands', 'CYM', 136, 1345),
(41, 'CF', 'CENTRAL AFRICAN REPUBLIC', 'Central African Republic', 'CAF', 140, 236),
(42, 'TD', 'CHAD', 'Chad', 'TCD', 148, 235),
(43, 'CL', 'CHILE', 'Chile', 'CHL', 152, 56),
(44, 'CN', 'CHINA', 'China', 'CHN', 156, 86),
(45, 'CX', 'CHRISTMAS ISLAND', 'Christmas Island', NULL, NULL, 61),
(46, 'CC', 'COCOS (KEELING) ISLANDS', 'Cocos (Keeling) Islands', NULL, NULL, 672),
(47, 'CO', 'COLOMBIA', 'Colombia', 'COL', 170, 57),
(48, 'KM', 'COMOROS', 'Comoros', 'COM', 174, 269),
(49, 'CG', 'CONGO', 'Congo', 'COG', 178, 242),
(50, 'CD', 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'Congo, the Democratic Republic of the', 'COD', 180, 242),
(51, 'CK', 'COOK ISLANDS', 'Cook Islands', 'COK', 184, 682),
(52, 'CR', 'COSTA RICA', 'Costa Rica', 'CRI', 188, 506),
(53, 'CI', 'COTE D''IVOIRE', 'Cote D''Ivoire', 'CIV', 384, 225),
(54, 'HR', 'CROATIA', 'Croatia', 'HRV', 191, 385),
(55, 'CU', 'CUBA', 'Cuba', 'CUB', 192, 53),
(56, 'CY', 'CYPRUS', 'Cyprus', 'CYP', 196, 357),
(57, 'CZ', 'CZECH REPUBLIC', 'Czech Republic', 'CZE', 203, 420),
(58, 'DK', 'DENMARK', 'Denmark', 'DNK', 208, 45),
(59, 'DJ', 'DJIBOUTI', 'Djibouti', 'DJI', 262, 253),
(60, 'DM', 'DOMINICA', 'Dominica', 'DMA', 212, 1767),
(61, 'DO', 'DOMINICAN REPUBLIC', 'Dominican Republic', 'DOM', 214, 1809),
(62, 'EC', 'ECUADOR', 'Ecuador', 'ECU', 218, 593),
(63, 'EG', 'EGYPT', 'Egypt', 'EGY', 818, 20),
(64, 'SV', 'EL SALVADOR', 'El Salvador', 'SLV', 222, 503),
(65, 'GQ', 'EQUATORIAL GUINEA', 'Equatorial Guinea', 'GNQ', 226, 240),
(66, 'ER', 'ERITREA', 'Eritrea', 'ERI', 232, 291),
(67, 'EE', 'ESTONIA', 'Estonia', 'EST', 233, 372),
(68, 'ET', 'ETHIOPIA', 'Ethiopia', 'ETH', 231, 251),
(69, 'FK', 'FALKLAND ISLANDS (MALVINAS)', 'Falkland Islands (Malvinas)', 'FLK', 238, 500),
(70, 'FO', 'FAROE ISLANDS', 'Faroe Islands', 'FRO', 234, 298),
(71, 'FJ', 'FIJI', 'Fiji', 'FJI', 242, 679),
(72, 'FI', 'FINLAND', 'Finland', 'FIN', 246, 358),
(73, 'FR', 'FRANCE', 'France', 'FRA', 250, 33),
(74, 'GF', 'FRENCH GUIANA', 'French Guiana', 'GUF', 254, 594),
(75, 'PF', 'FRENCH POLYNESIA', 'French Polynesia', 'PYF', 258, 689),
(76, 'TF', 'FRENCH SOUTHERN TERRITORIES', 'French Southern Territories', NULL, NULL, 0),
(77, 'GA', 'GABON', 'Gabon', 'GAB', 266, 241),
(78, 'GM', 'GAMBIA', 'Gambia', 'GMB', 270, 220),
(79, 'GE', 'GEORGIA', 'Georgia', 'GEO', 268, 995),
(80, 'DE', 'GERMANY', 'Germany', 'DEU', 276, 49),
(81, 'GH', 'GHANA', 'Ghana', 'GHA', 288, 233),
(82, 'GI', 'GIBRALTAR', 'Gibraltar', 'GIB', 292, 350),
(83, 'GR', 'GREECE', 'Greece', 'GRC', 300, 30),
(84, 'GL', 'GREENLAND', 'Greenland', 'GRL', 304, 299),
(85, 'GD', 'GRENADA', 'Grenada', 'GRD', 308, 1473),
(86, 'GP', 'GUADELOUPE', 'Guadeloupe', 'GLP', 312, 590),
(87, 'GU', 'GUAM', 'Guam', 'GUM', 316, 1671),
(88, 'GT', 'GUATEMALA', 'Guatemala', 'GTM', 320, 502),
(89, 'GN', 'GUINEA', 'Guinea', 'GIN', 324, 224),
(90, 'GW', 'GUINEA-BISSAU', 'Guinea-Bissau', 'GNB', 624, 245),
(91, 'GY', 'GUYANA', 'Guyana', 'GUY', 328, 592),
(92, 'HT', 'HAITI', 'Haiti', 'HTI', 332, 509),
(93, 'HM', 'HEARD ISLAND AND MCDONALD ISLANDS', 'Heard Island and Mcdonald Islands', NULL, NULL, 0),
(94, 'VA', 'HOLY SEE (VATICAN CITY STATE)', 'Holy See (Vatican City State)', 'VAT', 336, 39),
(95, 'HN', 'HONDURAS', 'Honduras', 'HND', 340, 504),
(96, 'HK', 'HONG KONG', 'Hong Kong', 'HKG', 344, 852),
(97, 'HU', 'HUNGARY', 'Hungary', 'HUN', 348, 36),
(98, 'IS', 'ICELAND', 'Iceland', 'ISL', 352, 354),
(99, 'IN', 'INDIA', 'India', 'IND', 356, 91),
(100, 'ID', 'INDONESIA', 'Indonesia', 'IDN', 360, 62),
(101, 'IR', 'IRAN, ISLAMIC REPUBLIC OF', 'Iran, Islamic Republic of', 'IRN', 364, 98),
(102, 'IQ', 'IRAQ', 'Iraq', 'IRQ', 368, 964),
(103, 'IE', 'IRELAND', 'Ireland', 'IRL', 372, 353),
(104, 'IL', 'ISRAEL', 'Israel', 'ISR', 376, 972),
(105, 'IT', 'ITALY', 'Italy', 'ITA', 380, 39),
(106, 'JM', 'JAMAICA', 'Jamaica', 'JAM', 388, 1876),
(107, 'JP', 'JAPAN', 'Japan', 'JPN', 392, 81),
(108, 'JO', 'JORDAN', 'Jordan', 'JOR', 400, 962),
(109, 'KZ', 'KAZAKHSTAN', 'Kazakhstan', 'KAZ', 398, 7),
(110, 'KE', 'KENYA', 'Kenya', 'KEN', 404, 254),
(111, 'KI', 'KIRIBATI', 'Kiribati', 'KIR', 296, 686),
(112, 'KP', 'KOREA, DEMOCRATIC PEOPLE''S REPUBLIC OF', 'Korea, Democratic People''s Republic of', 'PRK', 408, 850),
(113, 'KR', 'KOREA, REPUBLIC OF', 'Korea, Republic of', 'KOR', 410, 82),
(114, 'KW', 'KUWAIT', 'Kuwait', 'KWT', 414, 965),
(115, 'KG', 'KYRGYZSTAN', 'Kyrgyzstan', 'KGZ', 417, 996),
(116, 'LA', 'LAO PEOPLE''S DEMOCRATIC REPUBLIC', 'Lao People''s Democratic Republic', 'LAO', 418, 856),
(117, 'LV', 'LATVIA', 'Latvia', 'LVA', 428, 371),
(118, 'LB', 'LEBANON', 'Lebanon', 'LBN', 422, 961),
(119, 'LS', 'LESOTHO', 'Lesotho', 'LSO', 426, 266),
(120, 'LR', 'LIBERIA', 'Liberia', 'LBR', 430, 231),
(121, 'LY', 'LIBYAN ARAB JAMAHIRIYA', 'Libyan Arab Jamahiriya', 'LBY', 434, 218),
(122, 'LI', 'LIECHTENSTEIN', 'Liechtenstein', 'LIE', 438, 423),
(123, 'LT', 'LITHUANIA', 'Lithuania', 'LTU', 440, 370),
(124, 'LU', 'LUXEMBOURG', 'Luxembourg', 'LUX', 442, 352),
(125, 'MO', 'MACAO', 'Macao', 'MAC', 446, 853),
(126, 'MK', 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'Macedonia, the Former Yugoslav Republic of', 'MKD', 807, 389),
(127, 'MG', 'MADAGASCAR', 'Madagascar', 'MDG', 450, 261),
(128, 'MW', 'MALAWI', 'Malawi', 'MWI', 454, 265),
(129, 'MY', 'MALAYSIA', 'Malaysia', 'MYS', 458, 60),
(130, 'MV', 'MALDIVES', 'Maldives', 'MDV', 462, 960),
(131, 'ML', 'MALI', 'Mali', 'MLI', 466, 223),
(132, 'MT', 'MALTA', 'Malta', 'MLT', 470, 356),
(133, 'MH', 'MARSHALL ISLANDS', 'Marshall Islands', 'MHL', 584, 692),
(134, 'MQ', 'MARTINIQUE', 'Martinique', 'MTQ', 474, 596),
(135, 'MR', 'MAURITANIA', 'Mauritania', 'MRT', 478, 222),
(136, 'MU', 'MAURITIUS', 'Mauritius', 'MUS', 480, 230),
(137, 'YT', 'MAYOTTE', 'Mayotte', NULL, NULL, 269),
(138, 'MX', 'MEXICO', 'Mexico', 'MEX', 484, 52),
(139, 'FM', 'MICRONESIA, FEDERATED STATES OF', 'Micronesia, Federated States of', 'FSM', 583, 691),
(140, 'MD', 'MOLDOVA, REPUBLIC OF', 'Moldova, Republic of', 'MDA', 498, 373),
(141, 'MC', 'MONACO', 'Monaco', 'MCO', 492, 377),
(142, 'MN', 'MONGOLIA', 'Mongolia', 'MNG', 496, 976),
(143, 'MS', 'MONTSERRAT', 'Montserrat', 'MSR', 500, 1664),
(144, 'MA', 'MOROCCO', 'Morocco', 'MAR', 504, 212),
(145, 'MZ', 'MOZAMBIQUE', 'Mozambique', 'MOZ', 508, 258),
(146, 'MM', 'MYANMAR', 'Myanmar', 'MMR', 104, 95),
(147, 'NA', 'NAMIBIA', 'Namibia', 'NAM', 516, 264),
(148, 'NR', 'NAURU', 'Nauru', 'NRU', 520, 674),
(149, 'NP', 'NEPAL', 'Nepal', 'NPL', 524, 977),
(150, 'NL', 'NETHERLANDS', 'Netherlands', 'NLD', 528, 31),
(151, 'AN', 'NETHERLANDS ANTILLES', 'Netherlands Antilles', 'ANT', 530, 599),
(152, 'NC', 'NEW CALEDONIA', 'New Caledonia', 'NCL', 540, 687),
(153, 'NZ', 'NEW ZEALAND', 'New Zealand', 'NZL', 554, 64),
(154, 'NI', 'NICARAGUA', 'Nicaragua', 'NIC', 558, 505),
(155, 'NE', 'NIGER', 'Niger', 'NER', 562, 227),
(156, 'NG', 'NIGERIA', 'Nigeria', 'NGA', 566, 234),
(157, 'NU', 'NIUE', 'Niue', 'NIU', 570, 683),
(158, 'NF', 'NORFOLK ISLAND', 'Norfolk Island', 'NFK', 574, 672),
(159, 'MP', 'NORTHERN MARIANA ISLANDS', 'Northern Mariana Islands', 'MNP', 580, 1670),
(160, 'NO', 'NORWAY', 'Norway', 'NOR', 578, 47),
(161, 'OM', 'OMAN', 'Oman', 'OMN', 512, 968),
(162, 'PK', 'PAKISTAN', 'Pakistan', 'PAK', 586, 92),
(163, 'PW', 'PALAU', 'Palau', 'PLW', 585, 680),
(164, 'PS', 'PALESTINIAN TERRITORY, OCCUPIED', 'Palestinian Territory, Occupied', NULL, NULL, 970),
(165, 'PA', 'PANAMA', 'Panama', 'PAN', 591, 507),
(166, 'PG', 'PAPUA NEW GUINEA', 'Papua New Guinea', 'PNG', 598, 675),
(167, 'PY', 'PARAGUAY', 'Paraguay', 'PRY', 600, 595),
(168, 'PE', 'PERU', 'Peru', 'PER', 604, 51),
(169, 'PH', 'PHILIPPINES', 'Philippines', 'PHL', 608, 63),
(170, 'PN', 'PITCAIRN', 'Pitcairn', 'PCN', 612, 0),
(171, 'PL', 'POLAND', 'Poland', 'POL', 616, 48),
(172, 'PT', 'PORTUGAL', 'Portugal', 'PRT', 620, 351),
(173, 'PR', 'PUERTO RICO', 'Puerto Rico', 'PRI', 630, 1787),
(174, 'QA', 'QATAR', 'Qatar', 'QAT', 634, 974),
(175, 'RE', 'REUNION', 'Reunion', 'REU', 638, 262),
(176, 'RO', 'ROMANIA', 'Romania', 'ROM', 642, 40),
(177, 'RU', 'RUSSIAN FEDERATION', 'Russian Federation', 'RUS', 643, 70),
(178, 'RW', 'RWANDA', 'Rwanda', 'RWA', 646, 250),
(179, 'SH', 'SAINT HELENA', 'Saint Helena', 'SHN', 654, 290),
(180, 'KN', 'SAINT KITTS AND NEVIS', 'Saint Kitts and Nevis', 'KNA', 659, 1869),
(181, 'LC', 'SAINT LUCIA', 'Saint Lucia', 'LCA', 662, 1758),
(182, 'PM', 'SAINT PIERRE AND MIQUELON', 'Saint Pierre and Miquelon', 'SPM', 666, 508),
(183, 'VC', 'SAINT VINCENT AND THE GRENADINES', 'Saint Vincent and the Grenadines', 'VCT', 670, 1784),
(184, 'WS', 'SAMOA', 'Samoa', 'WSM', 882, 684),
(185, 'SM', 'SAN MARINO', 'San Marino', 'SMR', 674, 378),
(186, 'ST', 'SAO TOME AND PRINCIPE', 'Sao Tome and Principe', 'STP', 678, 239),
(187, 'SA', 'SAUDI ARABIA', 'Saudi Arabia', 'SAU', 682, 966),
(188, 'SN', 'SENEGAL', 'Senegal', 'SEN', 686, 221),
(189, 'CS', 'SERBIA AND MONTENEGRO', 'Serbia and Montenegro', NULL, NULL, 381),
(190, 'SC', 'SEYCHELLES', 'Seychelles', 'SYC', 690, 248),
(191, 'SL', 'SIERRA LEONE', 'Sierra Leone', 'SLE', 694, 232),
(192, 'SG', 'SINGAPORE', 'Singapore', 'SGP', 702, 65),
(193, 'SK', 'SLOVAKIA', 'Slovakia', 'SVK', 703, 421),
(194, 'SI', 'SLOVENIA', 'Slovenia', 'SVN', 705, 386),
(195, 'SB', 'SOLOMON ISLANDS', 'Solomon Islands', 'SLB', 90, 677),
(196, 'SO', 'SOMALIA', 'Somalia', 'SOM', 706, 252),
(197, 'ZA', 'SOUTH AFRICA', 'South Africa', 'ZAF', 710, 27),
(198, 'GS', 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', 'South Georgia and the South Sandwich Islands', NULL, NULL, 0),
(199, 'ES', 'SPAIN', 'Spain', 'ESP', 724, 34),
(200, 'LK', 'SRI LANKA', 'Sri Lanka', 'LKA', 144, 94),
(201, 'SD', 'SUDAN', 'Sudan', 'SDN', 736, 249),
(202, 'SR', 'SURINAME', 'Suriname', 'SUR', 740, 597),
(203, 'SJ', 'SVALBARD AND JAN MAYEN', 'Svalbard and Jan Mayen', 'SJM', 744, 47),
(204, 'SZ', 'SWAZILAND', 'Swaziland', 'SWZ', 748, 268),
(205, 'SE', 'SWEDEN', 'Sweden', 'SWE', 752, 46),
(206, 'CH', 'SWITZERLAND', 'Switzerland', 'CHE', 756, 41),
(207, 'SY', 'SYRIAN ARAB REPUBLIC', 'Syrian Arab Republic', 'SYR', 760, 963),
(208, 'TW', 'TAIWAN, PROVINCE OF CHINA', 'Taiwan, Province of China', 'TWN', 158, 886),
(209, 'TJ', 'TAJIKISTAN', 'Tajikistan', 'TJK', 762, 992),
(210, 'TZ', 'TANZANIA, UNITED REPUBLIC OF', 'Tanzania, United Republic of', 'TZA', 834, 255),
(211, 'TH', 'THAILAND', 'Thailand', 'THA', 764, 66),
(212, 'TL', 'TIMOR-LESTE', 'Timor-Leste', NULL, NULL, 670),
(213, 'TG', 'TOGO', 'Togo', 'TGO', 768, 228),
(214, 'TK', 'TOKELAU', 'Tokelau', 'TKL', 772, 690),
(215, 'TO', 'TONGA', 'Tonga', 'TON', 776, 676),
(216, 'TT', 'TRINIDAD AND TOBAGO', 'Trinidad and Tobago', 'TTO', 780, 1868),
(217, 'TN', 'TUNISIA', 'Tunisia', 'TUN', 788, 216),
(218, 'TR', 'TURKEY', 'Turkey', 'TUR', 792, 90),
(219, 'TM', 'TURKMENISTAN', 'Turkmenistan', 'TKM', 795, 7370),
(220, 'TC', 'TURKS AND CAICOS ISLANDS', 'Turks and Caicos Islands', 'TCA', 796, 1649),
(221, 'TV', 'TUVALU', 'Tuvalu', 'TUV', 798, 688),
(222, 'UG', 'UGANDA', 'Uganda', 'UGA', 800, 256),
(223, 'UA', 'UKRAINE', 'Ukraine', 'UKR', 804, 380),
(224, 'AE', 'UNITED ARAB EMIRATES', 'United Arab Emirates', 'ARE', 784, 971),
(225, 'GB', 'UNITED KINGDOM', 'United Kingdom', 'GBR', 826, 44),
(226, 'US', 'UNITED STATES', 'United States', 'USA', 840, 1),
(227, 'UM', 'UNITED STATES MINOR OUTLYING ISLANDS', 'United States Minor Outlying Islands', NULL, NULL, 1),
(228, 'UY', 'URUGUAY', 'Uruguay', 'URY', 858, 598),
(229, 'UZ', 'UZBEKISTAN', 'Uzbekistan', 'UZB', 860, 998),
(230, 'VU', 'VANUATU', 'Vanuatu', 'VUT', 548, 678),
(231, 'VE', 'VENEZUELA', 'Venezuela', 'VEN', 862, 58),
(232, 'VN', 'VIET NAM', 'Viet Nam', 'VNM', 704, 84),
(233, 'VG', 'VIRGIN ISLANDS, BRITISH', 'Virgin Islands, British', 'VGB', 92, 1284),
(234, 'VI', 'VIRGIN ISLANDS, U.S.', 'Virgin Islands, U.s.', 'VIR', 850, 1340),
(235, 'WF', 'WALLIS AND FUTUNA', 'Wallis and Futuna', 'WLF', 876, 681),
(236, 'EH', 'WESTERN SAHARA', 'Western Sahara', 'ESH', 732, 212),
(237, 'YE', 'YEMEN', 'Yemen', 'YEM', 887, 967),
(238, 'ZM', 'ZAMBIA', 'Zambia', 'ZMB', 894, 260),
(239, 'ZW', 'ZIMBABWE', 'Zimbabwe', 'ZWE', 716, 263)";
        $wpdb->query($sql);
    }

    public static function blsd_dispute_table_name() {
        global $wpdb;
        $table_name = $wpdb->prefix . "blsd_dispute";
        return $table_name;
    }

    public static function blsd_dispute_table() {

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

    public static function blsd_dispute_message_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $blsd_dispute_message_table_name = self::blsd_dispute_message_table_name();
        // create the ECPT metabox database table
        if ($wpdb->get_var("show tables like '$blsd_dispute_message_table_name'") != $blsd_dispute_message_table_name) {
            $sql = "CREATE TABLE `" . $blsd_dispute_message_table_name . "` (
                 `id` int(11) NOT NULL AUTO_INCREMENT,
                 `dispute_id` int(11) NOT NULL,
                 `username` varchar(255) DEFAULT NULL,
                 `phone_number` varchar(255) DEFAULT NULL,
                 `email` varchar(255) DEFAULT NULL,
                 `order_number` varchar(100) DEFAULT NULL,
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

    public static function blsd_dispute_attachment_message_table() {

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

    public static function blsd_status_table() {

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

    function blsd_insert_status_table() {

        global $wpdb;
        $table_name = self::blsd_status_table_name();

        if (!function_exists('dbDelta')) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
        $rows_affected = $wpdb->insert($table_name, array('name' => 'Open'));
        //    dbDelta( $rows_affected );
        $rows_affected = $wpdb->insert($table_name, array('name' => 'Processing'));
        //  dbDelta( $rows_affected );
        $rows_affected = $wpdb->insert($table_name, array('name' => 'Closed'));
        //  dbDelta( $rows_affected );
    }

    function blsd_add_stylesheet() {
        global $wp_styles;
        $current_page = sanitize_post($GLOBALS['wp_the_query']->get_queried_object());
// Get the page slug
        $slug = $current_page->post_name;
        $srcs = [];
        if ($slug == 'dashboard') {
            wp_enqueue_style('blsd_front_style', BUYLOCKSMITH_DEALS_ASSETS_PATH . ( 'css/vendor_dashoard.css'));
            wp_enqueue_style('wcmp-datatable-bs-style', WP_PLUGIN_URL . '/dc-woocommerce-multi-vendor/lib/dataTable/dataTables.bootstrap.min.css');
        }
        wp_enqueue_style('blsd_front_style_common', BUYLOCKSMITH_DEALS_ASSETS_PATH . ( 'css/blsd-common.css'));
    }

    function blsd_add_js() {
        wp_enqueue_script('dataTables-js', WP_PLUGIN_URL . '/dc-woocommerce-multi-vendor/lib/dataTable/jquery.dataTables.min.js', array('jquery'));
        wp_enqueue_script('dataTables-fixedHeader-js', WP_PLUGIN_URL . '/dc-woocommerce-multi-vendor/lib/dataTable/dataTables.fixedHeader.min.js', array('jquery'));
    }

    public static function blsd_get_status() {
        global $wpdb;
        $table_name_status = self::blsd_status_table_name();
        $query = "SELECT * FROM $table_name_status";
        return $results_status = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
    }

    public static function blsd_get_userFullName($user_id) {
        $user_data = get_user_by('ID', $user_id);
        $name = get_user_meta($user_id, 'first_name', true) . ' ' . get_user_meta($user_id, 'last_name', true);
        if (trim($name) == '') {
            $name = $user_data->user_login;
        }
        return $name;
    }

    function blsm_get_vendor_open_dispute_count($user_id = 0) {
        global $wpdb;
        if ($user_id == 0) {
            $user_id = get_current_user_id();
        }
        $table_name = self::blsd_dispute_table_name();

        $query = "SELECT count(id) as total_open_dispute from $table_name WHERE $table_name.status = 1 and (user_id=$user_id or who_opose_user_id=$user_id) ";
        $results_dispute_data = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);

        return $results_dispute_data[0]['total_open_dispute'];
    }

    function blsd_theme_page_templates($page_template) {
        $page_template['/blsd_page_template/search_step.php'] = 'Search Steps';
        return $page_template;
    }

    function blsd_theme_page__update_template_path($page_template) {
        $template_path = get_page_template_slug(get_the_ID());
        $template_path_array = explode('/blsd_page_template/', $template_path);

        if (isset($template_path_array[1])) {
            $template = BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/page_template/' . $template_path_array[1];
            $page_template = $template;
            $this->blsd_update_user_country();
        }

        return $page_template;
    }

    function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    function blsd_update_user_country() {
        if (!isset($_REQUEST['blsd_regional_product_listing_country'])) {
            $ip = $this->getRealIpAddr(); // This will contain the ip of the request
// You can use a more sophisticated method to retrieve the content of a webpage with php using a library or something
// We will retrieve quickly with the file_get_contents
            $dataArray = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));

            if (isset($dataArray->geoplugin_countryCode)) {
                if ($dataArray->geoplugin_countryCode != '' && $dataArray->geoplugin_countryCode != NULL && $dataArray->geoplugin_countryCode != null) {
                    $_REQUEST['blsd_regional_product_listing_country'] = $dataArray->geoplugin_countryCode;
                }
            }
        }
    }

    public static function blsd_get_vendor_list($search = '') {
        global $wpdb;
        /* $args = array(
          'role'    => 'dc_vendor',
          'orderby' => 'user_nicename',
          'order'   => 'ASC'
          );
          return $users = get_users( $args ); */
        $where = '';
        if ($search != '') {
            $where .= "AND (m.meta_key LIKE 'first_name' AND m.meta_value LIKE '%$search%' OR m.meta_key LIKE 'last_name' AND m.meta_value LIKE '%$search%' OR u.display_name LIKE '%$search%')";
        }
        $where .= "AND m.meta_key LIKE 'wp_capabilities' AND m.meta_value LIKE '%dc_vendor%'";


        $per_page = self::get_record_limit();

        $current_page = self::get_current_page();
        $offset = ($current_page - 1) * $per_page;

        $order_limit = " order by u.user_nicename ASC LIMIT $offset ,$per_page";

        $sql = "Select * From {$wpdb->prefix}users u,{$wpdb->prefix}usermeta m WHERE 1=1 AND u.ID = m.user_id $where $order_limit";
        $data = $wpdb->get_results($sql);

        $sql_total = "Select * From {$wpdb->prefix}users u,{$wpdb->prefix}usermeta m WHERE 1=1 AND u.ID = m.user_id $where";
        $data_total = $wpdb->get_results($sql_total);
        $total = count($data_total);
        return array(
            "data" => $data,
            "total_items" => $total,
            "total_pages" => ceil($total / $per_page),
            "per_page" => $per_page,
        );
    }

    function save_comment_review($commentID, $comment, $status) {
        $social_link = [];

        if (isset($_REQUEST['rating']) && isset($_REQUEST['comment_post_ID'])) {
            if ($_REQUEST['rating'] > 3 && $_REQUEST['comment_post_ID'] != '' && $_REQUEST['comment_post_ID'] != 0) {
                $product_id = $_REQUEST['comment_post_ID'];
                $post_data = get_post($product_id);
                if (isset($post_data->post_type)) {
                    if ($post_data->post_type == 'product') {
                        $post_author = $post_data->post_author;
                        $social_link['facebook'] = get_user_meta($post_author, '_vendor_fb_profile', true);
                        $social_link['twitter'] = get_user_meta($post_author, '_vendor_twitter_profile', true);
                        $social_link['linkedIn'] = get_user_meta($post_author, '_vendor_linkdin_profile', true);
                        $social_link['google_plus'] = get_user_meta($post_author, '_vendor_google_plus_profile', true);
                        $social_link['youtube'] = get_user_meta($post_author, '_vendor_youtube', true);
                        $social_link['instagram'] = get_user_meta($post_author, '_vendor_instagram', true);
                    }
                }
                if (count($social_link) > 0) {
                    $customer_id = get_current_user_id();
                    BuyLockSmithDealsCustomizationEmail::blsd_email_send_vendor_social_links_for_review($social_link, $customer_id);
                }
            }
        }
    }

    public static function blsd_get_status_name_by_id($status_id) {
        global $wpdb;
        $table_name_status = self::blsd_status_table_name();
        $query = "SELECT name FROM $table_name_status where id=$status_id";
        $status_result = $results_status = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);

        $status = '';
        if (isset($status_result[0]['name'])) {
            $status = $status_result[0]['name'];
        }
        return $status;
    }

    public static function blsd_y_m_model_table_name() {
        global $wpdb;
        $table_name = $wpdb->prefix . "blsd_y_m_model";
        return $table_name;
    }

    public static function blsd_y_m_model_table() {
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
                     `programming` varchar(255) DEFAULT NULL,
                     `type` varchar(255) DEFAULT NULL,
                     `message` text DEFAULT NULL,
                     PRIMARY KEY (`id`)
                    ) {$charset_collate};";


            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            //   $this->blsd_insert_status_table();
        }
    }
    
    public static function blsd_deals_custom_design_name(){
         global $wpdb;
        $table_name = $wpdb->prefix . "blsd_deals_custom_design";
        return $table_name;
    }
    public static function blsd_temp_deals_custom_design_name(){
         global $wpdb;
        $table_name = $wpdb->prefix . "blsd_temp_deals_custom_design";
        return $table_name;
    }
    public static function blsd_deals_custom_design(){
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $blsd_deals_custom_design_name = self::blsd_deals_custom_design_name();
        // create the ECPT metabox database table
        if ($wpdb->get_var("show tables like '$blsd_deals_custom_design_name'") != $blsd_deals_custom_design_name) {
            $sql = "CREATE TABLE `" . $blsd_deals_custom_design_name . "` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `unique_id` text DEFAULT NULL,
                     `vendor_id` int(11) NOT NULL,
                     `style_parameter` text DEFAULT NULL,
                     `shortcode_parameter` text DEFAULT NULL,
                     `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                     PRIMARY KEY (`id`)
                    ) {$charset_collate};";


            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            //   $this->blsd_insert_status_table();
        }
        $blsd_temp_deals_custom_design_name = self::blsd_temp_deals_custom_design_name();
        // create the ECPT metabox database table
        if ($wpdb->get_var("show tables like '$blsd_temp_deals_custom_design_name'") != $blsd_temp_deals_custom_design_name) {
            $sql = "CREATE TABLE `" . $blsd_temp_deals_custom_design_name . "` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `unique_id` text DEFAULT NULL,
                     `vendor_id` int(11) NOT NULL,
                     `style_parameter` text DEFAULT NULL,
                     `shortcode_parameter` text DEFAULT NULL,
                     `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                     PRIMARY KEY (`id`)
                    ) {$charset_collate};";


            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            //   $this->blsd_insert_status_table();
        }
    }

    public static function import_y_m_model() {
        global $wpdb;
        if (isset($_REQUEST['import_y_m_csv'])) {
            $table_name = self::blsd_y_m_model_table_name();
            $uploadPath = wp_upload_dir();
            $fileName = 'y_m_model.csv';
            $uploadPath_file = $uploadPath[baseurl] . '/csv/' . $fileName;
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $totalInserted = 0;
            $csvFile = fopen($uploadPath_file, 'r');
            fgetcsv($csvFile);
            while (($csvData = fgetcsv($csvFile)) !== FALSE) {
                $csvData = array_map("utf8_encode", $csvData);
                $maker = trim($csvData[0]);
                $model = trim($csvData[1]);
                $year = trim($csvData[2]);
                $programming = trim($csvData[3]);
                $type = trim($csvData[4]);
                $message = trim($csvData[5]);
                // Insert Record
                $wpdb->insert($table_name, array(
                    'maker' => $maker,
                    'model' => $model,
                    'year' => $year,
                    'programming' => $programming,
                    'type' => $type,
                    'message' => $message,
                ));
            }
        }
		
    }

    public static function get_all_cars_frontend($brand_array = [], $select_field = '', $where_field = '',$post_id) {
        global $wpdb;
        $not_works_on_vats_car=  get_post_meta($post_id, 'works_on_vats_car', true);
        $where = '';
        $car_list = [];
        $all = [];
        $table_name = BuyLockSmithDealsCustomizationAddon::blsd_y_m_model_table_name();
        if (!empty($brand_array)) {
            foreach ($brand_array as $brand) {
                $row = $wpdb->get_row("Select * from $table_name where id=$brand", ARRAY_A);
                if ($row['year'] == 0) {
                    $brand_name = explode('All ', $row['maker']);
                    $all[] = $brand_name[1];
                } else {
                    $car_list[] = $row['id'];
                }
            }
            if($not_works_on_vats_car == 'yes'){
                $vats_car_result = $wpdb->get_results("Select * from $table_name where type='Vats'", ARRAY_A);
                if(!empty($vats_car_result)){
                    foreach($vats_car_result as $vats_car){
                       $car_list[] = $vats_car['id']; 
                    }
                }
            }
            $allcarlist = implode(',', $car_list);
            $allmaker_brand = implode("','", $all);
            $condition = '';
            if ($where_field != '') {
                $condition = " AND $where_field";
            }
            if (!empty($allcarlist)) {
                $where .= " AND (id NOT IN ($allcarlist))";
            }
            if (!empty($allmaker_brand)) {
                $where .= " AND maker NOT IN  ('$allmaker_brand')";
            }
            $where .= " AND maker NOT LIKE 'All %' $condition ";
        } else {
            if($not_works_on_vats_car == 'yes'){
                $vats_car_result = $wpdb->get_results("Select * from $table_name where type='Vats'", ARRAY_A);
                if(!empty($vats_car_result)){
                    foreach($vats_car_result as $vats_car){
                       $car_list[] = $vats_car['id']; 
                    }
                }
            }
            $allcarlist = implode(',', $car_list);
            $condition = '';
            if ($where_field != '') {
                $condition = " AND $where_field";
            }
            
            $where = " AND maker NOT LIKE 'All %' $condition ";
            if (!empty($allcarlist)) {
                $where .= " AND (id NOT IN ($allcarlist))";
            }
        }
        // echo "Select DISTINCT $select_field from $table_name where 1=1 $where";
        return $result = $wpdb->get_results("Select DISTINCT $select_field from $table_name where 1=1 $where", ARRAY_A);
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
	
	public static function render_pagination_ajax($current_page_url, $pages = '', $range = 3, $paged ='') {

        $paged = !empty($paged) ? $paged : 1;

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

        register_post_status('wc-awaiting-shipment', array(
            'label' => 'Expired',
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>')
        ));
    }

// Add to list of WC Order statuses
    function add_booking_expired_to_order_statuses($order_statuses) {
        $new_order_statuses = array();
        // add new order status after processing
        foreach ($order_statuses as $key => $status) {
            $new_order_statuses[$key] = $status;
            if ('wc-processing' === $key) {
                $new_order_statuses['wc-awaiting-shipment'] = 'Expired';
            }
        }
        return $new_order_statuses;
    }

    function get_address_from_coordinates_global($lat, $lng) {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lat) . ',' . trim($lng) . '&sensor=false&key=AIzaSyCuC3PEVYk9RGDIIuLM1ur-sQ7y73ff3eY';

        $json = @file_get_contents($url);
        $data = json_decode($json);
        $status = $data->status;
        if ($status == "OK") {
            return json_encode($data->results[0]);
        } else {
            return false;
        }
    }

    function code_encrypt($string, $key) {
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

    public static function code_encrypt_static($string, $key) {
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

    function code_decrypt($string, $key) {
        $result = "";
        $string = str_replace(' ', '+', $string);
        $string = base64_decode($string);
        $end_length = intval(substr($string, -1, 1));
        $string = substr($string, 0, -1);
        $salt_length = intval(substr($string, $end_length * -1, $end_length));
        $string = substr($string, 0, $end_length * -1 + $salt_length * -1);
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }

    public static function code_decrypt_static($string, $key) {
     
        $result = "";
        $string = str_replace(' ', '+', $string);
        $string = base64_decode($string);
        $end_length = intval(substr($string, -1, 1));
        $string = substr($string, 0, -1);
        $salt_length = intval(substr($string, $end_length * -1, $end_length));
        $string = substr($string, 0, $end_length * -1 + $salt_length * -1);
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
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
	public static function get_product_details($products){
			$tp = $products;
			$p = array();
			foreach( $tp as $k => $v ){
				$product = array();
				$product['ID'] = $v->get_id();
				$product['id'] = $product['ID'];
				$product['type'] = $v->get_type();
				$product['name'] = $v->get_name();
				$product['slug'] = $v->get_slug();
				$product['date_created'] = $v->get_date_created();
				$product['date_modified'] = $v->get_date_modified();
				$product['status'] = $v->get_status();
				$product['featured'] = $v->get_featured();
				$product['catalog_visibility'] = $v->get_catalog_visibility();
				$product['description'] = $v->get_description();
				$product['short_description'] = $v->get_short_description();
				$product['sku'] = $v->get_sku();
				$product['price'] = $v->get_price();
				$product['regular_price'] = $v->get_regular_price();
				$product['sale_price'] = $v->get_sale_price();
				$product['date_on_sale_from'] = $v->get_date_on_sale_from();
				$product['date_on_sale_to'] = $v->get_date_on_sale_to();
				$product['total_sales'] = $v->get_total_sales();
				$product['manage_stock'] = $v->get_manage_stock();
				$product['stock_quantity'] = $v->get_stock_quantity();
				$product['stock_status'] = $v->get_stock_status();
				$product['backorders'] = $v->get_backorders();
				$product['low_stock_amount'] = $v->get_low_stock_amount();
				$product['sold_individually'] = $v->get_sold_individually();
				$product['category_ids'] = $v->get_category_ids();
				$product['tag_ids'] = $v->get_tag_ids();
				$product['virtual'] = $v->get_virtual();
				$product['gallery_image_ids'] = $v->get_gallery_image_ids();
				$product['shipping_class_id'] = $v->get_shipping_class_id();
				$product['downloads'] = $v->get_downloads();
				$product['download_expiry'] = $v->get_download_expiry();
				$product['downloadable'] = $v->get_downloadable();
				$product['download_limit'] = $v->get_download_limit();
				$product['image_id'] = $v->get_image_id();
				$product['rating_counts'] = $v->get_rating_counts();
				$product['average_rating'] = $v->get_average_rating();
				$product['review_count'] = $v->get_review_count();
				$product['title'] = $v->get_title();
				$product['permalink'] = $v->get_permalink(); 
				$product['get_children'] = $v->get_children();
				$product['stock_managed_by_id'] = $v->get_stock_managed_by_id();
				$product['price_html'] = $v->get_price_html();
				$product['formatted_name'] = $v->get_formatted_name();
				$product['min_purchase_quantity'] = $v->get_min_purchase_quantity();
				$product['max_purchase_quantity'] = $v->get_max_purchase_quantity();
				//$product['add_to_cart_url'] = $v->add_to_cart_url();
				//$product['single_add_to_cart_text'] = $v->single_add_to_cart_text();
				//$product['add_to_cart_text'] = $v->add_to_cart_text();
				$product['add_to_cart_description'] = $v->add_to_cart_description();
				$product['image'] = $v->get_image();
				$product['shipping_class'] = $v->get_shipping_class();
				$product['rating_count'] = $v->get_rating_count();
				$product['file'] = $v->get_file();
				$product['price_suffix'] = $v->get_price_suffix();
				$product['availability'] = $v->get_availability();
				
				$fimg_arr = array(
							'url'				=>	BUYLOCKSMITH_DEALS_ASSETS_PATH . 'img/no_image_available.jpeg',
							'width'				=>	1200,
							'height'			=>	1200,
							'is_intermediate'	=>	null,
						);
					
				$product['featured_image']['thumbnail'] = $fimg_arr;
				$product['featured_image']['medium'] = $fimg_arr;
				$product['featured_image']['large'] = $fimg_arr;
				
				if( $product['image_id'] ){
					
					$fimg = wp_get_attachment_image_src( $product['image_id'], 'thumbnail');
					if( $fimg ){
						$fimg_arr = array(
							'url'				=>	$fimg[0],
							'width'				=>	$fimg[1],
							'height'			=>	$fimg[2],
							'is_intermediate'	=>	$fimg[3],
						);
						$product['featured_image']['thumbnail'] = $fimg_arr;
					}
					
					$fimg = wp_get_attachment_image_src( $product['image_id'], 'medium');
					if( $fimg ){
						$fimg_arr = array(
							'url'				=>	$fimg[0],
							'width'				=>	$fimg[1],
							'height'			=>	$fimg[2],
							'is_intermediate'	=>	$fimg[3],
						);
						$product['featured_image']['medium'] = $fimg_arr;
					}
					
					$fimg = wp_get_attachment_image_src( $product['image_id'], 'large');
					if( $fimg ){
						$fimg_arr = array(
							'url'				=>	$fimg[0],
							'width'				=>	$fimg[1],
							'height'			=>	$fimg[2],
							'is_intermediate'	=>	$fimg[3],
						);
						$product['featured_image']['large'] = $fimg_arr;
					}
				}
				
				$product['vendor'] = '';
				$product['vendor_permalink'] = '';
				$product['vendor_display_name'] = '';
				$product['vendor_formatted_address'] = '';
				$vendor = get_wcmp_product_vendors( $product['id'] );
				if( $vendor ) {
					$term_vendor = wp_get_post_terms( $product['id'],  'dc_vendor_shop' );
					$product['vendor'] = $vendor;
					$product['vendor_permalink'] = $vendor->permalink;
					$product['vendor_display_name'] = $vendor->user_data->display_name;
					$product['vendor_formatted_address'] = $vendor->get_formatted_address();
                                        
                                        
                                           
                                              $city = get_user_meta($vendor->id, '_vendor_city', true);
                                              $state = get_user_meta($vendor->id, '_vendor_state', true);
                                              $postcode = get_user_meta($vendor->id, '_vendor_postcode', true);
                                              $address = $city?$city:'';
                                              $address = $state?$address.' '.$state:'';
                                           $product['vendor_short_formatted_address'] =   $address = $postcode?$address.' '.$postcode:'';
				}
				
				$p[] = $product;
			}
			
			return $p;
		}

}

?>