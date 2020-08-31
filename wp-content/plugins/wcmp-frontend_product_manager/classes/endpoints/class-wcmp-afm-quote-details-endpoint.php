<?php
/**
 * WCMp_AFM_Quote_Details_Endpoint setup
 *
 * @package  WCMp_AFM/classes/endpoints
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Quote_Details_Endpoint {

    public function output() {
        $current_vendor_id = afm()->vendor_id;

        if ( ! $current_vendor_id || ! apply_filters( 'vendor_can_access_request_quote', true, $current_vendor_id ) ) {
            ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <?php esc_html_e( 'You do not have permission to view this content. Please contact site administrator.', 'wcmp-afm' ); ?>
                </div>
            </div>
            <?php
            return;
        }
        global $wp;
        $quote_post_obj = '';
        $quote_for_product_id = '';
        if ( isset( $wp->query_vars['quote-details'] ) && ! empty( $wp->query_vars['quote-details'] ) ) {
            $quote_post = get_post( absint( $wp->query_vars['quote-details'] ) );
            if ( $quote_post ) {
                $quote_id = $quote_post->ID;
                $product_id = get_post_meta( $quote_id, 'add-to-cart', true );
                if ( $product_id ) {
                    $product_vendor = get_wcmp_product_vendors( $product_id );
                    if ( ! empty( $product_vendor ) && $product_vendor->id === $current_vendor_id ) {
                        $quote_post_obj = $quote_post;
                        $quote_for_product_id = $product_id;
                    }
                }
            }
            $quote_details_params = array(
                'ajax_url'           => admin_url( 'admin-ajax.php' ),
                'quote_id'           => $quote_id,
                'add_message_nonce'  => wp_create_nonce( 'add-message' ),
                'update_quote_nonce' => wp_create_nonce( 'update-quote' ),
            );
            wp_localize_script( 'afm-quote-details-js', 'quote_details_params', $quote_details_params );
            wp_enqueue_script( 'afm-quote-details-js' );
            afm()->template->get_template( 'products/rental/html-endpoint-quote-details.php', array( 'quote_post' => $quote_post_obj, 'product_id' => $quote_for_product_id ) );
        }
    }

}
