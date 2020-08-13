<?php
/**
 * Toolset fields template
 *
 * Used by WCMp_AFM_Advanced_Custom_Fields_Integration->Advance_custom_fields_panel_for_vendor()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/acf/html-acf-fields.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/acf
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
global $WCMp;

if ( ! empty( acf_get_fields( $field_group['ID'] ) && ! empty( $id ) ) ) {
	foreach ( acf_get_fields( $field_group['ID'] ) as $field_group_field ) {
		acf_form_head();
		$display_fields_post = apply_filters( 'acf_fields_display_wcmp_post', array(
			'post_id' => $id, // Unique identifier for the form
			'field_groups' => array( $field_group['ID'] ) ,  // Create post field group ID(s)
			'form' => false,
			'return' => '%post_url%' , // Redirect to new post url
			'fields' => array( $field_group_field['ID'] ),
			'uploader' => 'wp',
			), $field_group['ID'] , $field_group_field['ID'] );
		// Display acf fields at vendor dashboard
		acf_form( $display_fields_post);	
	}
}