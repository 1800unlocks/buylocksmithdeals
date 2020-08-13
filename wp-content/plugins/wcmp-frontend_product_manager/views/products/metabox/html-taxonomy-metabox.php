<?php

/**
 * Taxonomy metabox template - add product page
 *
 * Used by WCMp_AFM_Add_Product_Endpoint->add_taxonomy_metaboxes()
 * Used by html-bulk-edit-product.php template
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/metabox/html-taxonomy-metabox.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/metabox
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

$product_terms = afm_get_product_terms_HTML( $product_taxonomy->name, $product_id );
if ( $product_terms ) {
    ?>
    <div class="panel panel-default pannel-outer-heading <?php esc_attr_e( $product_taxonomy->label . '_widget_wrap' ); ?>">
        <div class="panel-heading">
            <h3 class="pull-left"><?php esc_html_e( $product_taxonomy->label ); ?></h3>
        </div>
        <div class="panel-body panel-content-padding form-group-wrapper"> 
            <?php
                echo $product_terms;
            ?>
        </div>
    </div>
    <?php
}