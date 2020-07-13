<?php
defined('ABSPATH') || exit;

/**
 * Main BuyLockSmithDealsCustomizationAddon Class.
 *
 * @class BuyLockSmithDealsCustomizationAddon
 */
class BuyLockSmithDealsCustomizationCustomer {

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

        add_action('init', array($this, 'my_custom_endpoints'), 15);
        add_filter('query_vars', array($this, 'my_custom_query_vars'), 0);
        add_action('wp_loaded', array($this, 'my_custom_flush_rewrite_rules'));
        add_filter('woocommerce_account_menu_items', array($this, 'my_custom_my_account_menu_items'));
        add_action('woocommerce_account_dispute-list_endpoint', array($this, 'my_custom_endpoint_content'));
        add_action('woocommerce_account_dispute-add_endpoint', array($this, 'my_custom_dispute_add_endpoint_content'));
        add_action('woocommerce_account_dispute-view_endpoint', array($this, 'my_custom_dispute_view_endpoint_content'));
        

        add_action('wp_head', array($this, 'blsd_update_dispute_count'));
        add_action('wp_head', array($this, 'blsd_customer_stylesheet'));
        
        add_filter('woocommerce_my_account_my_orders_columns', array($this, 'blsd_my_account_my_orders_columns'), 10, 1);
        add_action('woocommerce_my_account_my_orders_column_order-dispute', array($this, 'blsd_my_account_my_orders_columns_data'));
    }

    function my_custom_endpoints() {
        add_rewrite_endpoint('dispute-list', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('dispute-add', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('dispute-view', EP_ROOT | EP_PAGES);
    }

    function my_custom_query_vars($vars) {
        $vars[] = 'dispute-list';
        $vars[] = 'dispute-add';
        $vars[] = 'dispute-view';

        return $vars;
    }

    function my_custom_flush_rewrite_rules() {
        flush_rewrite_rules();
    }

    function my_custom_my_account_menu_items($items) {
		unset($items['downloads']);
        $logout_data = $items['customer-logout'];
        unset($items['customer-logout']);
        $items['dispute-list'] = 'Dispute List';
        $items['customer-logout'] = $logout_data;
        return $items;
    }

    function my_custom_endpoint_content() {

        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/custom-custom-page/dispute/dispute-list.php';
    }
    function my_custom_dispute_add_endpoint_content() {

        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/custom-custom-page/dispute/dispute-add.php';
    }
    function my_custom_dispute_view_endpoint_content() {

        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/custom-custom-page/dispute/dispute-view.php';
    }

    function blsd_update_dispute_count() {
        if (is_page('my-account')) {
            $notification_count = BuyLockSmithDealsCustomizationAddon::blsm_get_vendor_open_dispute_count();
            ?>

            <script>
                var notification_count = '<?php echo $notification_count; ?>';

                jQuery(document).ready(function () {
                    var disputeLinkText = jQuery('.woocommerce-MyAccount-navigation-link--dispute-list a').text();
                    jQuery('.woocommerce-MyAccount-navigation-link--dispute-list a').html(disputeLinkText + ' <span class="myaccount_dispute_count">' + notification_count + '</span>');
                })

            </script>
            <?php
        }
    }

    function blsd_customer_stylesheet() {
        global $wp_styles;
        $current_page = sanitize_post($GLOBALS['wp_the_query']->get_queried_object());
// Get the page slug
        $slug = $current_page->post_name;
        $srcs = [];
        if ($slug == 'my-account') {
            wp_enqueue_style('blsd-customer-myacc-style', BUYLOCKSMITH_DEALS_ASSETS_PATH . ( 'css/customer-myacc-style.css'));
        }
    }

    

    function blsd_my_account_my_orders_columns($fields) {
        
    $fields =    Array
(
    'order-number' => 'Order',
    'order-status' => 'Status',
    'order-date' => 'Date',
    'order-total' => 'Total',
    'order-actions' => 'Actions'
);
        $actions = $fields['order-actions'];
        unset($fields['order-actions']);
        $fields['order-dispute'] = 'Dispute';
        $fields['order-actions'] = $actions;
        return $fields;
    }

    function blsd_my_account_my_orders_columns_data($order) {
        
        $order_id = $order->get_order_number();
        echo '<a href="'.home_url().'/my-account/dispute-add?add='.$order_id.'" class="woocommerce-button button">Create</a>';
    }

}
?>