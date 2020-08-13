<?php
/**
 * GMW Location panel template
 *
 * Used by WCMp_AFM_Geo_My_Wp_Integration->gmw_location_panel()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/gmw/html-gwm-location.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/gmw
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
$form_args = apply_filters(
    'wcmp_afm_gmw_product_location_form_args', array(
        'object_id' => $id,
        'form_element' => '#wcmp-afm-add-product',
        'stand_alone'    => 0,
        'submit_enabled'   => 0,
        'ajax_enabled'   => 0,
        'location_mandatory' => gmw_get_option( 'post_types_settings', 'location_mandatory', 0 ),
        'location_required'  => gmw_get_option( 'post_types_settings', 'location_mandatory', 0 ),
    )
);
$form_args_str = '';
foreach ( $form_args as $key => $value ) {
    $form_args_str .= "$key=$value ";
}
?>
<div class="panel panel-default pannel-outer-heading gmw-location-panel">
    <div class="panel-heading">
        <h3 class="pull-left"><?php esc_html_e( 'Location', 'geo-my-wp' ); ?></h3>
    </div>
    <div class="panel-body panel-content-padding gmw_post_location_panel">
        <?php echo do_shortcode( "[gmw_post_location_form $form_args_str]" ); ?>
    </div>
</div>


