<?php
/**
 * WCMp_AFM_Resources_Endpoint setup
 *
 * @package  WCMp_AFM/classes/endpoints
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Resources_Endpoint {

    public function output() {
        global $wp;
        $current_vendor_id = afm()->vendor_id;
        $resource_id = absint( $wp->query_vars['resources'] );
        $vendor_resource_ids = WCMp_AFM_Booking_Integration::get_bookable_product_resource_ids();
        if ( ! $current_vendor_id || ! current_vendor_can( 'manage_resources' ) || ( $resource_id && ! in_array( $resource_id, $vendor_resource_ids ) ) ) {
            ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <?php esc_html_e( 'You do not have permission to view this content. Please contact site administrator.', WCMp_AFM_TEXT_DOMAIN ); ?>
                </div>
            </div>
            <?php
            return;
        }

        if ( $resource_id || wc_clean( $wp->query_vars['resources'] ) === 'draft-resource' ) {
            $resource = $resource_id ? new WC_Product_Booking_Resource( $resource_id ) : null;
            wp_enqueue_script( 'afm-add-resource-js' );
            afm()->template->get_template( 'products/booking/html-endpoint-add-resource.php', array( 'id' => $resource_id, 'resource' => $resource ) );
        } else {
            $resources_params = array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'empty_table'  => esc_js( __( 'No resources found!', WCMp_AFM_TEXT_DOMAIN ) ),
                'processing'   => esc_js( __( 'Processing...', WCMp_AFM_TEXT_DOMAIN ) ),
                'info'         => esc_js( __( 'Showing _START_ to _END_ of _TOTAL_ resources', WCMp_AFM_TEXT_DOMAIN ) ),
                'info_empty'   => esc_js( __( 'Showing 0 to 0 of 0 resources', WCMp_AFM_TEXT_DOMAIN ) ),
                'length_menu'  => esc_js( __( 'Number of rows _MENU_', WCMp_AFM_TEXT_DOMAIN ) ),
                'zero_records' => esc_js( __( 'No matching resources found', WCMp_AFM_TEXT_DOMAIN ) ),
                'next'         => esc_js( __( 'Next', WCMp_AFM_TEXT_DOMAIN ) ),
                'previous'     => esc_js( __( 'Previous', WCMp_AFM_TEXT_DOMAIN ) ),
                'reload'       => esc_js( __( 'Reload', WCMp_AFM_TEXT_DOMAIN ) ),
            );
            wp_localize_script( 'afm-resources-js', 'resources_params', $resources_params );
            wp_enqueue_script( 'afm-resources-js' );

            afm()->template->get_template( 'products/booking/html-endpoint-resources.php' );
        }
    }

}
