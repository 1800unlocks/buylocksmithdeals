<?php

defined('ABSPATH') || exit;

/**
 * Main BuyLockSmithDealsCustomizationAdmin Class.
 *
 * @class BuyLockSmithDealsCustomizationAdmin
 */
final class BuyLockSmithDealsCustomizationAdminReport {

    protected static $_instance = null;
    public $vendor_class_obj = null;

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
        add_action('admin_menu', array($this, 'blsd_admin_theme_pages_register'), 101);

        add_action('admin_enqueue_scripts', array($this, 'blsd_load_custom_wp_admin_style'));
       
    }

    function blsd_admin_theme_pages_register() {


        add_menu_page('Reports', 'Reports', '', 'blsm_report_list', array($this, 'blsm_report_list'), 'dashicons-chart-bar', '2');

        add_submenu_page('blsm_report_list', 'Report Dashboard', 'Report Dashboard', 'manage_options', 'blsm-reports-dashboard', array($this, 'blsm_report_list'));
        add_submenu_page('blsm_report_list', 'Vendor Report', 'Vendor Report', 'manage_options', 'blsm-vendor-reports', array($this, 'blsmVendorReport'));
        add_submenu_page('blsm_report_list', 'Deal Stats', 'Deal Stats', 'manage_options', 'blsm-deals-stats', array($this, 'blsmDealsStats'));
        add_submenu_page('blsm_report_list', 'Deal City Stats', 'Deal City Stats', 'manage_options', 'blsm-deals-city-stats', array($this, 'blsmDealsCityStats'));
        add_submenu_page('blsm_report_list', 'Woocomerce Reports', 'Woocomerce Reports', 'manage_options', 'wc-reports',  '__return_false' );
        add_submenu_page('blsm_report_list', 'WooCommerce Abandoned Cart', 'WooCommerce Abandoned Cart', 'manage_options', 'sfa-abandoned-carts',  '__return_false' );
      
    }
 
    function blsm_report_list() {
        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/admin/pages/reports/report-main.php';
    }

    /*
     * To hide admin menu.
     */

    public static function totalVendors() {
        $args = array(
            'role' => 'dc_vendor',
            'orderby' => 'user_nicename',
            'order' => 'ASC'
        );
        $vendors = get_users($args);
        return count($vendors);
    }

    public static function totalVendorIdList() {
        $args = array(
            'role' => 'dc_vendor',
            'orderby' => 'user_nicename',
            'order' => 'ASC'
        );
        $vendors = get_users($args);
        $vendor_id_list = [];
        if (count($vendors)) {
            foreach ($vendors as $vendor) {
                $vendor_id_list[] = $vendor->ID;
            }
        }


        return $vendor_id_list;
    }

    public static function totalCustomerIdList() {
        $args = array(
            'role' => 'customer',
            'orderby' => 'user_nicename',
            'order' => 'ASC'
        );
        $customers = get_users($args);
        $customer_id_list = [];
        if (count($customers)) {
            foreach ($customers as $customer) {
                $customer_id_list[] = $customer->ID;
            }
        }


        return $customer_id_list;
    }

    public static function totalCustomer() {
        $args = array(
            'role' => 'customer',
            'orderby' => 'user_nicename',
            'order' => 'ASC'
        );
        $subscribers = get_users($args);
        return count($subscribers);
    }

    public static function totalDeals() {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_vendor_product_parent',
                    'compare' => 'NOT EXISTS'
                )
            )
        );

        $loop = new WP_Query($args);

        $count = 0;
        if (isset($loop->posts)) {
            $count = count($loop->posts);
        }
        return $count;
    }

    public static function totalDealsIds() {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_vendor_product_parent',
                    'compare' => 'NOT EXISTS'
                )
            )
        );

        $loop = new WP_Query($args);

        $ids = [];
        if (isset($loop->posts)) {
            $post_list = $loop->posts;
            foreach ($post_list as $list) {
                $ids[] = $list->ID;
            }
        }
        return $ids;
    }

    function totalVendorIdListByMonth($date = '') {
        $start = date("Y") . '-01';
        $end = date("Y") . '-12';

        if ($date != '') {

            $start = $date . '-01';
            $end = $date . '-12';
        }
        $totalVendorIdList = self::totalVendorIdList();
        $totalVendorIdList = implode(',', $totalVendorIdList);
        global $wpdb;
        $table_name = $wpdb->prefix . 'users';
        $query = "Select DATE_FORMAT(user_registered, '%m') AS Month,count(ID) as vendors_count from $table_name where ID in ($totalVendorIdList) and DATE_FORMAT(user_registered, '%Y-%m')>='$start' and DATE_FORMAT(user_registered, '%Y-%m')<='$end' GROUP BY DATE_FORMAT(user_registered, '%Y'),DATE_FORMAT(user_registered, '%m') ORDER by DATE_FORMAT(user_registered, '%Y'),DATE_FORMAT(user_registered, '%m')";

        $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
        $result_Data = [];
        $month_array = [];
        for ($i = 1; $i <= 12; $i++) {
            $result_Data[$i - 1] = 0;
            if (count($results)) {
                foreach ($results as $result) {
                    if ($i == $result['Month']) {
                        $result_Data[$i - 1] = (int) $result['vendors_count'];
                    }
                }
            }
        }
        return $result_Data;
    }

    function totalCustomerIdListByMonth($date = '') {
      $start = date("Y") . '-01';
        $end = date("Y") . '-12';

        if ($date != '') {

            $start = $date . '-01';
            $end = $date . '-12';
        }
        $totalVendorIdList = self::totalCustomerIdList();

        $totalVendorIdList = implode(',', $totalVendorIdList);
        global $wpdb;
        $table_name = $wpdb->prefix . 'users';
        $query = "Select DATE_FORMAT(user_registered, '%m') AS Month,count(ID) as vendors_count from $table_name where ID in ($totalVendorIdList) and DATE_FORMAT(user_registered, '%Y-%m')>='$start' and DATE_FORMAT(user_registered, '%Y-%m')<='$end' GROUP BY DATE_FORMAT(user_registered, '%Y'),DATE_FORMAT(user_registered, '%m') ORDER by DATE_FORMAT(user_registered, '%Y'),DATE_FORMAT(user_registered, '%m')";

        $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);

        $result_Data = [];
        $month_array = [];
        for ($i = 1; $i <= 12; $i++) {
            $result_Data[$i - 1] = 0;
            if (count($results)) {
                foreach ($results as $result) {
                    if ($i == $result['Month']) {
                        $result_Data[$i - 1] = (int) $result['vendors_count'];
                    }
                }
            }
        }

        return $result_Data;
    }

    public static function blsmVendorReport() {
        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/admin/pages/reports/vendor-report.php';
    }

    public static function blsmDealsStats() {
        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/admin/pages/reports/deals-stats.php';
    }

    function blsd_load_custom_wp_admin_style() {

        wp_register_style('custom_wp_admin_css_chart', BUYLOCKSMITH_DEALS_ASSETS_PATH . '/additions/chart.js-2.8.0/package/dist/Chart.css', false);
        wp_enqueue_style('custom_wp_admin_css_chart');




        wp_register_script('custom_wp_admin_js_chart', BUYLOCKSMITH_DEALS_ASSETS_PATH . '/additions/chart.js-2.8.0/package/dist/Chart.js', false);
        wp_enqueue_script('custom_wp_admin_js_chart');


//    wp_enqueue_style('custom_wp_adminselect2_css', BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/assets/additions/select2/select2.min.css');
//    wp_register_script('custom_wp_adminselect2_js', plugins_url() . '/' . NUJ_API__TEXT_DOMAIN . '/assets/additions/select2/select2.min.js');
//    wp_enqueue_script('custom_wp_adminselect2_js');
//
//    wp_enqueue_style('custom_wp_colorpicker_css', plugins_url() . '/' . NUJ_API__TEXT_DOMAIN . '/assets/additions/colorpicker/css/colorpicker.css');
//    wp_register_script('custom_wp_colorpicker_js', plugins_url() . '/' . NUJ_API__TEXT_DOMAIN . '/assets/additions/colorpicker/js/colorpicker.js');
//    wp_enqueue_script('custom_wp_colorpicker_js');
    }

    public static function blsmDealsCityStats() {
        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/admin/pages/reports/city-wise-deals.php';
    }

    public static function get_vendor_unique_city() {
        global $wpdb;
        $result = $wpdb->get_results("SELECT DISTINCT  meta_value as city from $wpdb->usermeta where meta_key='_vendor_city' and meta_value!='' ");
        return $result;
    }

    public static function get_all_vendors($city) {
        global $wpdb;
        if(!empty($city)){
        $result = $wpdb->get_col("SELECT  user_id from $wpdb->usermeta where meta_key='_vendor_city' and meta_value!='' and meta_value='$city' ");
        }
        else{
          $result = $wpdb->get_col("SELECT  user_id from $wpdb->usermeta where meta_key='wp_capabilities' and meta_value!='' and meta_value LIKE '%dc_vendor%' "); 
        }
        return $result;
    }
    public static function get_all_categories() {
       $args = array(
               'taxonomy' => 'product_cat',
               'orderby' => 'name',
               'order'   => 'ASC'
           );

  return  $cats = get_categories($args);
    }

    public static function blsd_yearList() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'users';
        $query = "Select min(ID), DATE_FORMAT(user_registered, '%Y') as year from $table_name limit 1";
        $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
        $current_year = $start_year = date('Y');

        if (isset($results[0]['year'])) {
            $start_year = $results[0]['year'];
        }
       
        $year_array = [];
        for ($i = $start_year; $i <= $current_year; $i++) {

            $year_array[] = $i;
        }
        return $year_array;
    }
     public static function get_all_deals($city) {
        global $wpdb;
        $admin_id = get_current_user_id();
        $result = $wpdb->get_col("SELECT  ID from $wpdb->posts where post_type='product' and post_author=$admin_id and (post_status='publish' OR post_status='private') ");
        return $result;
    }

}
