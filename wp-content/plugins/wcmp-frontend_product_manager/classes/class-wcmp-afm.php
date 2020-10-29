<?php
/**
 * WCMp_AFM setup
 *
 * @package  WCMp_AFM/classes
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * Main WCMp_AFM Class.
 *
 * @class WCMp_AFM
 */
final class WCMp_AFM {

    /**
     * WCMp_AFM version.
     *
     * @var string
     */
    public $version = WCMp_AFM_VERSION;

    /**
     * The single instance of the class.
     *
     * @var WCMp_AFM
     * @since 3.0.0
     */
    protected static $_instance = null;

    /**
     * Holds instance of AFM License class.
     *
     * @since 3.0.0
     * @access public
     * @var WCMp_AFM_License $license Instance of License class.
     */
    public $license = null;

    /**
     * Holds instance of AFM Dependencies class.
     *
     * @since 3.0.0
     * @access public
     * @var Object Instance of WCMp_AFM_Dependencies class.
     */
    public $dependencies = null;

    /**
     * Third Party Integrations instance.
     *
     * @var WCMp_AFM_Integrations
     */
    public $integrations = null;

    /**
     * Vendor capabilities instance.
     *
     * @var WCMp_AFM_Capabilities
     */
    public $capabilities = null;

    /**
     * The library instance.
     *
     * @var WCMp_AFM_Library
     */
    public $library = null;

    /**
     * The admin instance.
     *
     * @var WCMp_AFM_Admin
     */
    public $admin = null;

    /**
     * The frontend instance.
     *
     * @var WCMp_AFM_Frontend
     */
    public $frontend = null;

    /**
     * The endpoints instance.
     *
     * @var WCMp_AFM_Endpoints
     */
    public $endpoints = null;

    /**
     * The template instance.
     *
     * @var WCMp_AFM_Template
     */
    public $template = null;

    /**
     * The ajax instance.
     *
     * @var WCMp_AFM_Ajax
     */
    public $ajax = null;

    /**
     * Current logged in vendor (if any).
     *
     * @var id user id
     */
    public $vendor_id = null;

    /**
     * Main WCMp_AFM Instance.
     *
     * Ensures only one instance of WCMp_AFM is loaded or can be loaded.
     *
     * @since 3.0.0
     * @static
     * @see afm()
     *
     * @return object The WCMp_AFM object.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Primary class constructor.
     *
     * @since 3.0.0
     * @access public
     */
    public function __construct() {
        if ( ! class_exists( 'WCMp_AFM_Dependencies' ) ) {
            require_once plugin_dir_path( WCMp_AFM_PLUGIN_FILE ) . 'includes/class-wcmp-afm-dependencies.php';
        }
        $this->dependencies = new WCMp_AFM_Dependencies();
        if ( ! $this->dependencies->can_plugin_activate() ) {
            return;
        }

        $this->define_constants();
        $this->includes();
        $this->init_hooks();

        do_action( 'wcmp_afm_loaded' );
    }

    /**
     * Cloning is forbidden.
     *
     * @since 3.0.0
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'wcmp-afm' ), '3.0.0' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 3.0.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'wcmp-afm' ), '3.0.0' );
    }

    private function define_constants() {

        if ( ! defined( 'WCMp_AFM_VERSION' ) ) {
            define( 'WCMp_AFM_VERSION', $this->version );
        }

        if ( ! defined( 'WCMp_AFM_PLUGIN_TOKEN' ) ) {
            define( 'WCMp_AFM_PLUGIN_TOKEN', 'wcmp-afm' );
        }

        if ( ! defined( 'WCMp_AFM_CLASS_PREFIX' ) ) {
            define( 'WCMp_AFM_CLASS_PREFIX', 'WCMp_AFM_' );
        }

        if ( ! defined( 'WCMp_AFM_TEXT_DOMAIN' ) ) {
            define( 'WCMp_AFM_TEXT_DOMAIN', 'wcmp-afm' );
        }

        if ( ! defined( 'WCMp_AFM_SERVER_URL' ) ) {
            define( 'WCMp_AFM_SERVER_URL', 'https://wc-marketplace.com' );
        }

        if ( ! defined( 'WCMp_AFM_PLUGIN_DIR' ) ) {
            define( 'WCMp_AFM_PLUGIN_DIR', plugin_dir_path( WCMp_AFM_PLUGIN_FILE ) );
        }

        if ( ! defined( 'WCMp_AFM_PLUGIN_URL' ) ) {
            define( 'WCMp_AFM_PLUGIN_URL', plugin_dir_url( WCMp_AFM_PLUGIN_FILE ) );
        }
        
        if ( ! defined( 'WCMp_AFM_PRODUCT_ID' ) ) {
            define( 'WCMp_AFM_PRODUCT_ID', '704' );
        }
        
        if ( ! defined( 'WCMp_AFM_SOFTWARE_TITLE' ) ) {
            define( 'WCMp_AFM_SOFTWARE_TITLE', 'Advanced Frontend Manager' );
        }
    }

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined( 'DOING_AJAX' );
            case 'cron':
                return defined( 'DOING_CRON' );
            case 'frontend':
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    private function includes() {
        //include Library @TODO
        //$this->load_class( 'library' );
        
        // DC License Activation
		$this->load_class('license');
		$this->license = new WCMp_AFM_License( WCMp_AFM_PLUGIN_FILE, WCMp_AFM_PLUGIN_DIR, WCMp_AFM_PRODUCT_ID, WCMp_AFM_VERSION, 'plugin', WCMp_AFM_SERVER_URL, WCMp_AFM_SOFTWARE_TITLE, WCMp_AFM_TEXT_DOMAIN );

        if ( $this->is_request( 'admin' ) ) { //Admin pages
            $this->load_class( 'admin' );
        }
        //common woo methods
        if ( ! class_exists( 'WCMp_AFM_Woo_Helper_Functions' ) ) {
            require_once plugin_dir_path( WCMp_AFM_PLUGIN_FILE ) . 'includes/class-wcmp-afm-woo-helper-functions.php';
        }
        //third party integrations
        $this->load_class( 'integrations' );
        $this->load_class( 'capabilities' );
        /*
         * We are not loading frontend files here.
         * We will use init hook for that
         * Need to only load if we are in vendor dashboard 
         */

        // if ( $this->is_request( 'frontend' ) ) {}
    }

    private function init_hooks() {
        // add_filter( 'wcmp_vendor_capabilities', array( $this, 'add_vendor_capability' ), 10, 1 );

        add_action( 'wc_am_after_plugin_activation', array( &$this, 'register_endpoints' ) );
        add_action( 'wc_am_after_plugin_deactivation', array( &$this, 'remove_capabilities' ) );
        add_action( 'wcmp_init', array( &$this, 'init' ) );
        
        /******************* Wp affiliate update in admin **********************/
        add_action( 'affwp_pre_update_affiliate',array($this, 'update_data'), 10 , 3 );
    }
    
    /**
     * Install AFM
     * since @3.0.0
     */
    public function register_endpoints() {
        global $WCMp;
        afm()->dependencies->register_endpoints();
    }
    
    /**
     * Remove vendor capabilities added by AFM
     * since @3.0.0
     */
    public function remove_capabilities( ) {
    	if ( defined( 'WC_REMOVE_ALL_DATA' ) && true === WC_REMOVE_ALL_DATA ) {
    		remove_vendor_capabilities();
    		
    		// Clear any cached data that has been removed
    		wp_cache_flush();
    	}
        return;
    }
    
    public function remove_vendor_capabilities( $param ) {
        return;
    }

    public function is_vendor_dashboard_page() {
        $request_link = (isset( $_SERVER['HTTPS'] ) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $dashboard_link = get_permalink( wcmp_vendor_dashboard_page_id() );
        if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
            $pattern = '|\??' . $_SERVER['QUERY_STRING'] . '|';
            $request_link = preg_replace($pattern, '', $request_link);
            $dashboard_link = preg_replace($pattern, '', $dashboard_link);
        }
        return ( substr( $request_link, 0, strlen( $dashboard_link ) ) === $dashboard_link ) && get_current_vendor() !== false;
    }

    /**
     * initilize plugin on WP init
     */
    function init() {
        global $WCMp;

        $current_user_id = apply_filters( 'wcmp_current_loggedin_vendor_id', get_current_user_id() );

        if ( is_user_logged_in() && is_user_wcmp_vendor( $current_user_id ) ) {
            $this->vendor_id = absint( $current_user_id );
        } else {
            $this->vendor_id = null;
        }
        // Init Text Domain
        $this->load_plugin_textdomain();

        // Init library
        //$this->library = new WCMp_AFM_Library();
        //$this->wcmp_wp_fields = $WCMp->library->load_wcmp_frontend_fields();

        if ( $this->is_request( 'admin' ) ) {
            $this->admin = new WCMp_AFM_Admin();
        }

        //include core functionality
        require_once plugin_dir_path( WCMp_AFM_PLUGIN_FILE ) . 'includes/wcmp-afm-core-functions.php';
        $this->integrations = new WCMp_AFM_Integrations();
        $this->capabilities = new WCMp_AFM_Capabilities();

        // Init ajax
        if ( $this->is_request( 'ajax' ) ) { // DOING_AJAX is available in init
            $this->load_class( 'ajax' );
            $this->ajax = new WCMp_AFM_Ajax();
        }

        if ( $this->is_request( 'frontend' ) && $this->is_vendor_dashboard_page() ) { //load only on vendor dashboard
            // init frontend
            $this->load_class( 'frontend' );
            $this->frontend = new WCMp_AFM_Frontend();

            // init vendor dashboard added endpoints
            $this->load_class( 'endpoint' );
            $this->endpoints = new WCMp_AFM_Endpoint();

            // init library
            $this->load_class( 'library' );
            $this->library = new WCMp_AFM_Library();
        }

        if ( $this->is_request( 'ajax' ) || ( $this->is_request( 'frontend' ) && $this->is_vendor_dashboard_page() ) ) {
            // init templates
            $this->load_class( 'template' );
            $this->template = new WCMp_AFM_Template();
        }
        if (!is_admin() || defined('DOING_AJAX')) {
            // Enable vendor Import & Exports
            $this->enable_vendor_import_export();
        }

        // restrict vendors backend access, everything will be managed from frontend vendors dashboard
        $this->restrict_wp_backend();
    }

    public function update_data( $affiliate, $args, $data ){affwp_update_affiliate_meta( $data['affiliate_id'], 'affiliate_assign_vendor', $data['affiliate_assign_vendor'] );
    }

    /**
     * Load Localization files.
     *
     * Note: the first-loaded translation file overrides any following ones if the same translation is present
     *
     * @access public
     * @return void
     */
    public function load_plugin_textdomain() {
        $locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
        $locale = apply_filters( 'plugin_locale', $locale, 'wcmp-afm' );

        unload_textdomain( 'wcmp-afm' );
        load_textdomain( 'wcmp-afm', WP_LANG_DIR . "/wcmp-frontend_product_manager/wcmp-frontend_product_manager-$locale.mo" );
        load_textdomain( 'wcmp-afm', false, dirname( plugin_basename( WCMp_AFM_PLUGIN_FILE ) ) . '/languages/' );
    }

    public function load_class( $class_name = '' ) {
        if ( '' != $class_name && '' != WCMp_AFM_PLUGIN_TOKEN ) {
            require_once ('class-' . esc_attr( WCMp_AFM_PLUGIN_TOKEN ) . '-' . esc_attr( $class_name ) . '.php');
        }
    }
    
    /**
     * Enables import export query vars.
     *
     * @access public
     * @return void
     */
    public function enable_vendor_import_export() {
    	global $WCMp;
    	
		 //filter for adding wcmp endpoint query vars
        add_filter('wcmp_endpoints_query_vars', array(&$this, 'add_wcmp_endpoints_query_vars'));
        
        //filter for adding import export menu to vendor dashboard according to the capabilities
        $user_id = get_current_user_id();
        $vendor = get_wcmp_vendor($user_id);
        $user = new WP_User($user_id);
        if ($vendor && $user->has_cap('edit_products')) {
            add_action('before_wcmp_vendor_dash_product_list_page_header_action_btn', array(&$this, 'add_import_export_button_on_header'));
        }
        
        if ($WCMp->vendor_caps->vendor_can('vendor_import_capability')) {
            $this->load_class('wcimporter');
            $this->wc_importer = new WCMp_Admin_Importers();
        }
        
        if ($WCMp->vendor_caps->vendor_can('vendor_export_capability')) {
            $this->load_class('wcexporter');
            $this->wc_exporter = new WCMp_Admin_Exporters();
        }
    }

    /**
     * AFM feature
     * Restricting wp-admin (backend) access for vendor role
     *
     * @access public
     * @return void
     */
    public function restrict_wp_backend() {
        if ( is_user_logged_in() ) {
            $general_settings = get_option( 'wcmp_general_settings_name' );
            if ( $this->vendor_id && is_admin() && isset( $general_settings['is_backend_diabled'] ) && ! defined( 'DOING_AJAX' ) && ( ! isset( $_GET['action'] ) || $_GET['action'] != 'download_product_csv') ) {
                wp_redirect( get_permalink( wcmp_vendor_dashboard_page_id() ) );
                exit;
            }
        }
    }

    /** Cache Helpers ******************************************************** */

    /**
     * Sets a constant preventing some caching plugins from caching a page. Used on dynamic pages
     *
     * @access public
     * @return void
     */
    function nocache() {
        if ( ! defined( 'DONOTCACHEPAGE' ) )
            define( "DONOTCACHEPAGE", "true" );
        // WP Super Cache constant
    }
    
    /**
     * adds query vars
     * @return array
     */
    public function add_wcmp_endpoints_query_vars( $endpoints ) {
        $endpoints['product-import'] = apply_filters( 'wcmp_afm_product_import_endpoints', array(
            'label' => __('Import', 'wcmp-afm'),
            'endpoint' => 'product-import'
        ) );
        $endpoints['product-export'] = apply_filters( 'wcmp_afm_product_export_endpoints', array(
            'label' => __('Export', 'wcmp-afm'),
            'endpoint' => 'product-export'
        ) );
        
        return $endpoints;
    }

    
    /**
     * adds import export menu to vendor dashboard according to the capabilities
     * @return array
     */
    public function add_import_export_button_on_header() {
        global $WCMp;
        if( isset( $WCMp->endpoints->wcmp_query_vars['product-import'] ) && $WCMp->vendor_caps->vendor_can('vendor_import_capability') ){
            echo '<a href="' . wcmp_get_vendor_dashboard_endpoint_url($WCMp->endpoints->wcmp_query_vars['product-import']['endpoint']) . '" class="btn btn-default"><i class="wcmp-font ico-import"></i><span>' . $WCMp->endpoints->wcmp_query_vars['product-import']['label'] . '<span></a>';
        }
        if( isset( $WCMp->endpoints->wcmp_query_vars['product-export'] ) && $WCMp->vendor_caps->vendor_can('vendor_export_capability') ){
            echo '<a href="' . wcmp_get_vendor_dashboard_endpoint_url($WCMp->endpoints->wcmp_query_vars['product-export']['endpoint']) . '" class="btn btn-default"><i class="wcmp-font ico-export"></i><span>' . $WCMp->endpoints->wcmp_query_vars['product-export']['label'] . '<span></a>';
        }
        
    }
}
