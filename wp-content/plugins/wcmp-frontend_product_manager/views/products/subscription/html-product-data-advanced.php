<?php
/**
 * Advanced product tab subscription template
 *
 * Used by WCMp_AFM_Subscription_Integration->woocommerce_subscription_advanced_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/subscription/html-product-data-advanced.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/subscription
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
if ( wcmp_is_allowed_product_type( 'subscription' ) || wcmp_is_allowed_product_type( 'variable-subscription' ) ) :
    $subscription_limit_options = array(
        'no'     => __( 'Do not limit', 'woocommerce-subscriptions' ),
        'active' => __( 'Limit to one active subscription', 'woocommerce-subscriptions' ),
        'any'    => __( 'Limit to one of any status', 'woocommerce-subscriptions' ),
    );
    $chosen_subscription_limit = get_post_meta( $id, '_subscription_limit', true );
    ?>
    <div class="subscription_limit show_if_subscription show_if_variable-subscription"> 
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_subscription_limit"><?php esc_html_e( 'Limit subscription', 'woocommerce-subscriptions' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <select id="_subscription_limit" name="_subscription_limit" class="wc_input_subscription_limit form-control">
    <?php foreach ( $subscription_limit_options as $value => $label ) { ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $chosen_subscription_limit, true ) ?>><?php echo esc_html( $label ); ?></option>
                    <?php } ?>
                </select>
                <span class="form-text"><?php printf( esc_html__( 'Only allow a customer to have one subscription to this product. %sLearn more%s.', 'woocommerce-subscriptions' ), '<a href="http://docs.woocommerce.com/document/subscriptions/store-manager-guide/#limit-subscription">', '</a>' ); ?></span>
            </div>
        </div>
    </div>
    <?php
    do_action( 'wcmp_afm_subscriptions_product_options_advanced' );
endif;
