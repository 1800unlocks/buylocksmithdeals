<?php

/**
 * WCMP_Product_Import_Controller setup
 *
 * @package  WCMp_AFM/classes
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

if (!class_exists('WC_Product_CSV_Importer_Controller')) {
    include_once( WC_ABSPATH . 'includes/admin/importers/class-wc-product-csv-importer-controller.php' );
}

if (!function_exists('wp_handle_upload')) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

class WCMP_Product_Import_Controller extends WC_Product_CSV_Importer_Controller {

    function __construct() {
        global $WCMp;
        parent::__construct();
        
        if( $WCMp->vendor_caps->vendor_can('bundle') ) { // For Porduct bundle
            add_filter('woocommerce_csv_product_import_mapping_options', array(&$this, 'dc_map_columns'));
            add_filter('woocommerce_csv_product_import_mapping_default_columns', array(&$this, 'dc_add_columns_to_mapping_screen'));
        }
    }
	
    protected function mapping_form() {
        $args = array(
            'lines' => 1,
            'delimiter' => $this->delimiter,
        );

        $importer = self::get_importer($this->file, $args);
        $headers = $importer->get_raw_keys();
        $mapped_items = $this->auto_map_columns($headers);
        $sample = current($importer->get_raw_data());
        if (($key = array_search('Vendor Username', $mapped_items)) !== false) {
            unset($mapped_items[$key]);
            unset($headers[$key]);
        }
        if (($key = array_search('Fixed Commission', $mapped_items)) !== false) {
            unset($mapped_items[$key]);
            unset($headers[$key]);
        }
        if (($key = array_search('Commission Percentage', $mapped_items)) !== false) {
            unset($mapped_items[$key]);
            unset($headers[$key]);
        }
        if (($key = array_search('Fixed Width Percentage', $mapped_items)) !== false) {
            unset($mapped_items[$key]);
            unset($headers[$key]);
        }
        if (($key = array_search('Fixed Width Percent Quantity', $mapped_items)) !== false) {
            unset($mapped_items[$key]);
            unset($headers[$key]);
        }
        if (empty($sample)) {                                                                                                      
            $this->add_error(__('The file is empty, please try again with a new file.', 'wcmp-afm'));
            return;
        }
        include_once( WC_ABSPATH . 'includes/admin/importers/views/html-csv-import-mapping.php' );
    }

    public static function dc_map_columns($options) {
        $options['wc_pb_bundled_items'] = __('Bundled Items (JSON-encoded)', 'wcmp-afm');
        $options['wc_pb_layout'] = __('Bundle Layout', 'wcmp-afm');
        $options['wc_pb_group_mode'] = __('Bundle Group Mode', 'wcmp-afm');
        $options['wc_pb_editable_in_cart'] = __('Bundle Cart Editing', 'wcmp-afm');
        $options['wc_pb_sold_individually_context'] = __('Bundle Sold Individually', 'wcmp-afm');

        return $options;
    }

    public static function dc_add_columns_to_mapping_screen($columns) {
        $columns[__('Bundled Items (JSON-encoded)', 'wcmp-afm')] = 'wc_pb_bundled_items';
        $columns[__('Bundle Layout', 'wcmp-afm')] = 'wc_pb_layout';
        $columns[__('Bundle Group Mode', 'wcmp-afm')] = 'wc_pb_group_mode';
        $columns[__('Bundle Cart Editing', 'wcmp-afm')] = 'wc_pb_editable_in_cart';
        $columns[__('Bundle Sold Individually', 'wcmp-afm')] = 'wc_pb_sold_individually_context';

        // Always add English mappings.
        $columns['Bundled Items (JSON-encoded)'] = 'wc_pb_bundled_items';
        $columns['Bundle Layout'] = 'wc_pb_layout';
        $columns['Bundle Group Mode'] = 'wc_pb_group_mode';
        $columns['Bundle Cart Editing'] = 'wc_pb_editable_in_cart';
        $columns['Bundle Sold Individually'] = 'wc_pb_sold_individually_context';

        return $columns;
    }

    public function import() {
        global $WCMP_Product_Import_Export_Bundle;
        if (!is_file($this->file)) {
            $this->add_error(__('The file does not exist, please try again.', 'wcmp-afm'));
            return;
        }

        // get the the role object
        $user = new WP_User( get_current_vendor_id() );
        // grant the manage_product_terms capability
        $user->add_cap( 'manage_product_terms', true );

        if (!empty($_POST['map_to'])) {
            $mapping_from = wp_unslash($_POST['map_from']);
            $mapping_to = wp_unslash($_POST['map_to']);
        } else {
            wp_redirect(esc_url_raw($this->get_next_step_link('upload')));
            exit;
        }
        wp_localize_script('dc-product-import', 'wc_product_import_params', array(
            'import_nonce' => wp_create_nonce('wc-product-import'), 'ajax_url' => admin_url('admin-ajax.php'),
            'mapping' => array(
                'from' => $mapping_from,
                'to' => $mapping_to,
            ),
            'file' => $this->file,
            'update_existing' => $this->update_existing,
            'delimiter' => $this->delimiter,
        ));
        wp_enqueue_script('dc-product-import');
        include_once( WC()->plugin_path() . '/includes/admin/importers/views/html-csv-import-progress.php' );

        // Remove the manage_product_terms capability
        $user->remove_cap( 'manage_product_terms' );
    }
    
    public function upload_form_handler() {
        global $WCMp, $WCMP_Product_Import_Export_Bundle;
        wp_verify_nonce('woocommerce-csv-importer');
        $file = $this->handle_upload();
        if (is_wp_error($file)) {
            $this->add_error($file->get_error_message());
            return;
        } else {
            $this->file = $file;
        }
        $this->wcmp_exoprt_redirect(esc_url_raw($this->get_next_step_link()));
    }

    public function wcmp_exoprt_redirect($link) {
        wp_add_inline_script('wcmp_new_vandor_dashboard_js', 'jQuery(document).ready(function (){ window.location.href =  "' . $link . '"});');
    }
}
