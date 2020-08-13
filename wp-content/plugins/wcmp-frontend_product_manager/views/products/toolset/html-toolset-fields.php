<?php
/**
 * Toolset fields template
 *
 * Used by WCMp_AFM_Toolset_Integration->toolset_fields_panel()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/toolset/html-toolset-fields.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/toolset
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
global $WCMp;
?>
<div class="panel panel-default pannel-outer-heading toolset-fields-panel">
    <div class="panel-heading">
        <h3 class="pull-left"><?php esc_html_e( $field_group['name'] ); ?></h3>
    </div>
    <div class="panel-body panel-content-padding gmw_post_location_panel">
        <?php
        if ( ! empty( $field_group['fields'] ) && ! empty( $id ) ) {
            foreach ( $field_group['fields'] as $field_group_field ) {
                $field_value = get_post_meta( $id, $field_group_field['meta_key'], true );
                switch ( $field_group_field['type'] ) {
                    case 'url':
                    case 'phone':
                    case 'textfield':
                    case 'google_address':
                        $WCMp->wcmp_frontend_fields->wcmp_generate_form_field( array( $field_group_field['meta_key'] => array( 'label' => $field_group_field['name'], 'desc' => $field_group_field['description'], 'name' => 'wpcf[' . $field_group_field['meta_key'] . ']', 'type' => 'text', 'class' => 'regular-text pro_ele simple variable external grouped booking', 'label_class' => 'pro_title', 'value' => $field_value ) ) );
                        break;

                    case 'numeric':
                        $WCMp->wcmp_frontend_fields->wcmp_generate_form_field( array( $field_group_field['meta_key'] => array( 'label' => $field_group_field['name'], 'desc' => $field_group_field['description'], 'name' => 'wpcf[' . $field_group_field['meta_key'] . ']', 'type' => 'number', 'class' => 'regular-text pro_ele simple variable external grouped booking', 'label_class' => 'pro_title', 'value' => $field_value ) ) );
                        break;

                    case 'wysiwyg':
                    case 'textarea':
                        $WCMp->wcmp_frontend_fields->wcmp_generate_form_field( array( $field_group_field['meta_key'] => array( 'label' => $field_group_field['name'], 'desc' => $field_group_field['description'], 'name' => 'wpcf[' . $field_group_field['meta_key'] . ']', 'type' => 'textarea', 'class' => 'regular-textarea pro_ele simple variable external grouped booking', 'label_class' => 'pro_title', 'value' => $field_value ) ) );
                        break;

                    case 'date':
                        $WCMp->wcmp_frontend_fields->wcmp_generate_form_field( array( $field_group_field['meta_key'] => array( 'label' => $field_group_field['name'], 'desc' => $field_group_field['description'], 'name' => 'wpcf[' . $field_group_field['meta_key'] . ']', 'type' => 'text', 'placeholder' => 'YYYY-MM-DD', 'class' => 'regular-text pro_ele dc_datepicker simple variable external grouped booking', 'label_class' => 'pro_title', 'value' => $field_value ) ) );
                        break;

                    case 'timepicker':
                        $WCMp->wcmp_frontend_fields->wcmp_generate_form_field( array( $field_group_field['meta_key'] => array( 'label' => $field_group_field['name'], 'desc' => $field_group_field['description'], 'name' => 'wpcf[' . $field_group_field['meta_key'] . ']', 'type' => 'time', 'class' => 'regular-text pro_ele simple variable external grouped booking', 'label_class' => 'pro_title', 'value' => $field_value ) ) );
                        break;

                    case 'checkbox':
                        $WCMp->wcmp_frontend_fields->wcmp_generate_form_field( array( $field_group_field['meta_key'] => array( 'label' => $field_group_field['name'], 'desc' => $field_group_field['description'], 'name' => 'wpcf[' . $field_group_field['meta_key'] . ']', 'type' => 'checkbox', 'class' => 'regular-checkbox pro_ele simple variable external grouped booking', 'label_class' => 'pro_title checkbox_title', 'value' => $field_group_field['data']['set_value'], 'dfvalue' => $field_value ) ) );
                        break;

                    case 'radio':
                        $radio_opt_vals = array();
                        if ( ! empty( $field_group_field['data']['options'] ) ) {
                            foreach ( $field_group_field['data']['options'] as $radio_option ) {
                                if ( ! empty( $radio_option ) && isset( $radio_option['value'] ) && isset( $radio_option['title'] ) ) {
                                    $radio_opt_vals[$radio_option['value']] = $radio_option['title'];
                                }
                            }
                        }
                        $WCMp->wcmp_frontend_fields->wcmp_generate_form_field( array( $field_group_field['meta_key'] => array( 'label' => $field_group_field['name'], 'desc' => $field_group_field['description'], 'name' => 'wpcf[' . $field_group_field['meta_key'] . ']', 'type' => 'radio', 'class' => 'regular-select pro_ele', 'label_class' => 'pro_title', 'options' => $radio_opt_vals, 'value' => $field_value ) ) );
                        break;

                    case 'select':
                        $select_opt_vals = array( '' => __( '--- not set ---', 'wcmp_frontend_product_manager' ) );
                        if ( ! empty( $field_group_field['data']['options'] ) ) {
                            foreach ( $field_group_field['data']['options'] as $select_option ) {
                                if ( ! empty( $select_option ) && isset( $select_option['value'] ) && isset( $select_option['title'] ) ) {
                                    $select_opt_vals[$select_option['value']] = $select_option['title'];
                                }
                            }
                        }

                        $WCMp->wcmp_frontend_fields->wcmp_generate_form_field( array( $field_group_field['meta_key'] => array( 'label' => $field_group_field['name'], 'desc' => $field_group_field['description'], 'name' => 'wpcf[' . $field_group_field['meta_key'] . ']', 'type' => 'select', 'class' => 'regular-select pro_ele simple variable external grouped booking', 'label_class' => 'pro_title', 'options' => $select_opt_vals, 'value' => $field_value ) ) );
                        break;

                    case 'image':
                        $WCMp->wcmp_frontend_fields->wcmp_generate_form_field( array( $field_group_field['meta_key'] => array( 'label' => $field_group_field['name'], 'desc' => $field_group_field['description'], 'name' => 'wpcf[' . $field_group_field['meta_key'] . ']', 'type' => 'upload', 'class' => 'pro_ele simple variable external grouped booking', 'label_class' => 'pro_title', 'value' => $field_value ) ) );
                        break;

                    case 'file':
                    case 'audio':
                    case 'video':
                        $WCMp->wcmp_frontend_fields->wcmp_generate_form_field( array( $field_group_field['meta_key'] => array( 'label' => $field_group_field['name'], 'desc' => $field_group_field['description'], 'name' => 'wpcf[' . $field_group_field['meta_key'] . ']', 'type' => 'upload', 'mime' => 'Uploads', 'class' => 'pro_ele simple variable external grouped booking', 'label_class' => 'pro_title', 'value' => $field_value ) ) );
                        break;
                }
            }
        }
        ?>
    </div>
</div>


