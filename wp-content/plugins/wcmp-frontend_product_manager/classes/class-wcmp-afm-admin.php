<?php
/**
 * WCMp_AFM_Admin class
 *
 * @package  WCMp_AFM/classes
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Admin {

    public $settings;

    public function __construct() {
        // admin script and style
        // add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_admin_script' ), 30 );
        // enable vendor backend access capability checkbox for admin user
        add_filter('is_wcmp_backend_disabled',array(&$this,'is_wcmp_backend_disabled'));
        // Remove backend restriction text under `Capabilities` tab, `Product Types` section
        add_filter( 'settings_capabilities_product_tab_options', array( $this, 'settings_capabilities_product_tab_options_callback' ) );
        // Remove AFM promotional text from `Product Types` section under `Capabilities` tab
        add_filter( 'capabilities_default_settings_section_types_info_display', '__return_false' );
        // Enable capability checkboxes(for vendor) for import export in admin settings capabilites products section
        add_filter('settings_capabilities_product_tab_options', array($this, 'enable_import_export_capabilites'), 10, 1);
		//filter for saving capability checkboxes for import export in admin settings capabilites products section
		add_filter("settings_capabilities_product_tab_new_input", array($this, 'save_import_export_capabilites'), 10, 2);
    }

    public function load_class( $class_name = '' ) {
        if ( '' != $class_name ) {
            require_once (WCMp_AFM_PLUGIN_DIR . '/admin/class-' . esc_attr( WCMp_AFM_PLUGIN_TOKEN ) . '-' . esc_attr( $class_name ) . '.php');
        }
    }

    /**
     * Admin Scripts
     */
    public function enqueue_admin_script() {
//        $screen = get_current_screen();
        // Enqueue admin script and stylesheet from here
//        if ( in_array( $screen->id, array( 'wcmp_page_wcmp-setting-admin' ) ) ) :
//            afm()->library->load_qtip_lib();
//            afm()->library->load_upload_lib();
//            afm()->library->load_colorpicker_lib();
//            afm()->library->load_datepicker_lib();
//            wp_enqueue_script( 'admin_js', WCMp_AFM_PLUGIN_URL . 'assets/admin/js/admin.js', array( 'jquery' ), WCMp_AFM_VERSION, true );
//            wp_enqueue_style( 'admin_css', WCMp_AFM_PLUGIN_URL . 'assets/admin/css/admin.css', array(), WCMp_AFM_VERSION );
//        endif;
    }

    /*
     * enable wcmp backend enable / disabled function
     */

    public function is_wcmp_backend_disabled( $option ) {
        $option['custom_tags'] = array();
        $option['text'] = __( 'Offer a single frontend dashboard for all vendor purpose and eliminate their backend access requirement.', 'wcmp-afm' );
        return $option;
    }

    public function settings_capabilities_product_tab_options_callback( $tab_options ) {
        $tab_options['sections']['default_settings_section_types']['fields']['simple']['text'] = '';
        $tab_options['sections']['default_settings_section_types']['fields']['variable']['text'] = '';
        $tab_options['sections']['default_settings_section_types']['fields']['grouped']['text'] = '';
        $tab_options['sections']['default_settings_section_types']['fields']['external']['text'] = '';
        return $tab_options;
    }

    /**
	*adds capability checkboxes for import export in admin settings capabilites products section
	*@access public
	*@return array
	*/
	public function enable_import_export_capabilites($settings_tab_options) {
		$settings_tab_options["sections"]["products_capability"]["fields"]["vendor_import_capability"] = array('title' => __('Vendor Import Capability', 'wcmp-afm'), 'type' => 'checkbox', 'id' => 'vendor_import_capability', 'label_for' => 'vendor_import_capability', 'text' => __('Allow vendors to import products.', 'wcmp-afm'), 'name' => 'vendor_import_capability', 'value' => 'Enable');
		$settings_tab_options["sections"]["products_capability"]["fields"]["vendor_export_capability"] = array('title' => __('Vendor Export Capability', 'wcmp-afm'), 'type' => 'checkbox', 'id' => 'vendor_export_capability', 'label_for' => 'vendor_export_capability', 'text' => __('Allow vendors to export products.', 'wcmp-afm'), 'name' => 'vendor_export_capability', 'value' => 'Enable');
		return $settings_tab_options;
	}
	
	/**
	*saves capability checkboxes for import export in admin settings capabilites products section
	*@access public
	*@return array
	*/
	public function save_import_export_capabilites($new_input, $input) {
		$vendor_role = get_role( 'dc_vendor' );
		if (isset($input['vendor_import_capability'])) {
			$new_input['vendor_import_capability'] = sanitize_text_field($input['vendor_import_capability']);
		}
		if (isset($input['vendor_export_capability'])) {
			$new_input['vendor_export_capability'] = sanitize_text_field($input['vendor_export_capability']);
		}
		 return $new_input;
	}
}
