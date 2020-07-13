<?php

function azexo_tgmpa_register() {

    $plugins = array();
    if (file_exists(get_stylesheet_directory() . '/plugins/' . azexo_get_skin() . '-page-builder.zip')) {
        $plugins[] = array(
            'name' => esc_html__('Core theme plugin', 'AZEXO'),
            'slug' => azexo_get_skin() . '-page-builder',
            'source' => get_stylesheet_directory() . '/plugins/' . azexo_get_skin() . '-page-builder.zip',
            'required' => true,
            'version' => AZEXO_FRAMEWORK_VERSION,
        );
    }
    $plugins[] = array(
        'name' => esc_html__('Redux Framework', 'AZEXO'),
        'slug' => 'redux-framework',
        'required' => true,
    );
    $plugins[] = array(
        'name' => esc_html__('WordPress Importer', 'AZEXO'),
        'slug' => 'wordpress-importer',
        'required' => true,
    );
    $plugins[] = array(
        'name' => esc_html__('WP-LESS', 'AZEXO'),
        'slug' => 'wp-less',
    );
    $plugins[] = array(
        'name' => esc_html__('Infinite scroll', 'AZEXO'),
        'slug' => 'infinite-scroll',
    );
    $plugins[] = array(
        'name' => esc_html__('Widget CSS Classes', 'AZEXO'),
        'slug' => 'widget-css-classes',
    );
    $plugins[] = array(
        'name' => esc_html__('Contact Form 7', 'AZEXO'),
        'slug' => 'contact-form-7',
    );



    if (in_array(azexo_get_skin(), array('foodpicky', 'loocal', 'wisem', 'sportak', 'kuponhub', 'medican'))) {
        $plugins[] = array(
            'name' => esc_html__('Custom Sidebars', 'AZEXO'),
            'slug' => 'custom-sidebars',
            'required' => true,
        );
    }

    if (in_array(azexo_get_skin(), array('foodpicky', 'loocal', 'kuponhub', 'medican'))) {
        $plugins[] = array(
            'name' => esc_html__('Custom Post Type UI', 'AZEXO'),
            'slug' => 'custom-post-type-ui',
            'required' => true,
        );
    }

    if (file_exists(get_stylesheet_directory() . '/plugins/custom-classes.zip')) {
        $plugins[] = array(
            'name' => esc_html__('Custom classes for page/post', 'AZEXO'),
            'slug' => 'custom-classes',
            'source' => get_stylesheet_directory() . '/plugins/custom-classes.zip',
            'required' => true,
            'version' => '0.1',
        );
    }
    $plugin_path = get_stylesheet_directory() . '/plugins/js_composer.zip';
    if (file_exists($plugin_path)) {
        $plugins[] = array(
            'name' => esc_html__('WPBakery Page Builder', 'AZEXO'),
            'slug' => 'js_composer',
            'source' => get_stylesheet_directory() . '/plugins/js_composer.zip',
            'required' => true,
            'version' => '5.7',
            'external_url' => '',
        );
    }
    $plugins = apply_filters('azexo_plugins', $plugins);
    if (!empty($plugins)) {
        tgmpa($plugins, array());
    }


    $additional_plugins = array(
	'jetpack-widget-visibility' => esc_html__('JP Widget Visibility', 'AZEXO'),
        'vc_widgets' => esc_html__('Visual Composer Widgets', 'AZEXO'),
        'azexo_vc_elements' => esc_html__('AZEXO Visual Composer elements', 'AZEXO'),
        'az_social_login' => esc_html__('AZEXO Social Login', 'AZEXO'),
        'az_email_verification' => esc_html__('AZEXO Email Verification', 'AZEXO'),
        'az_likes' => esc_html__('AZEXO Post/Comments likes', 'AZEXO'),
        'az_voting' => esc_html__('AZEXO Voting', 'AZEXO'),
        'azexo_html' => esc_html__('AZEXO HTML Customizer', 'AZEXO'),
        'azh_extension' => esc_html__('AZEXO HTML Library', 'AZEXO'),
        'page-builder-by-azexo' => esc_html__('Page builder by AZEXO', 'AZEXO'),
        'elements-library-for-azexo-builder' => esc_html__('Elements Library for AZEXO Builder', 'AZEXO'),
        'az_listings' => esc_html__('AZEXO Listings', 'AZEXO'),
        'az_query_form' => esc_html__('AZEXO Query Form', 'AZEXO'),
        'az_group_buying' => esc_html__('AZEXO Group Buying', 'AZEXO'),
        'az_vouchers' => esc_html__('AZEXO Vouchers', 'AZEXO'),
        'az_bookings' => esc_html__('AZEXO Bookings', 'AZEXO'),
        'az_deals' => esc_html__('AZEXO Deals', 'AZEXO'),
        'az_sport_club' => esc_html__('AZEXO Sport Club', 'AZEXO'),
        'az_locations' => esc_html__('AZEXO Locations', 'AZEXO'),
        'circular_countdown' => esc_html__('Circular CountDown', 'AZEXO'),
    );
    $plugins = array();
    foreach ($additional_plugins as $additional_plugin_slug => $additional_plugin_name) {
        $plugin_path = get_stylesheet_directory() . '/plugins/' . $additional_plugin_slug . '.zip';
        if (file_exists($plugin_path)) {
            $plugins[] = array(
                'name' => $additional_plugin_name,
                'slug' => $additional_plugin_slug,
                'source' => $plugin_path,
                'required' => true,
                'version' => AZEXO_FRAMEWORK_VERSION,
            );
        }
    }
    $plugins = apply_filters('azexo_plugins', $plugins);
    if (!empty($plugins)) {
        tgmpa($plugins, array(
//            'is_automatic' => true,
        ));
    }
}

add_action('tgmpa_register', 'azexo_tgmpa_register');
