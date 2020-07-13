<?php

add_action('wp_enqueue_scripts', 'azexo_less_default_variables', 5);

function azexo_less_default_variables() {
    if (class_exists('WPLessPlugin')) {
        $less = WPLessPlugin::getInstance();
        $vars = array(
            'brand-color',
            'accent-1-color',
            'accent-2-color',
            'main-google-font',
            'main-border-color',
            'main-border-radius',
            'main-border-width',
            'control-border-width',
            'main-shadow-color',
            'header-google-font',
            'header-color',
            'header-font-size',
            'header-line-height',
            'header-font-weight',
            'paragraph-color',
            'paragraph-font-size',
            'paragraph-line-height',
            'paragraph-font-weight',
            'paragraph-bold-weight',
        );
        foreach ($vars as $var) {
            $less->addVariable($var, '');
        }
    }
}

add_action('wp_enqueue_scripts', 'azexo_less_variables');

function azexo_less_variables() {
    if (class_exists('WPLessPlugin')) {
        $less = WPLessPlugin::getInstance();
        $options = get_option(AZEXO_FRAMEWORK);

        if (isset($options['brand-color'])) {
            $less->addVariable('brand-color', $options['brand-color']);
        }
        if (isset($options['accent-1-color'])) {
            $less->addVariable('accent-1-color', $options['accent-1-color']);
        }
        if (isset($options['accent-2-color'])) {
            $less->addVariable('accent-2-color', $options['accent-2-color']);
        }

        if (isset($options['google_font_families']) && is_array($options['google_font_families'])) {
            $font_families = array();
            $i = 0;
            foreach ($options['google_font_families'] as $font_family) {
                $i++;
                $less->addVariable('google-font-family-' . $i, str_replace('+', ' ', $font_family));
            }
            $font_family = reset($options['google_font_families']);
            while ($i < 10) {
                $i++;
                $less->addVariable('google-font-family-' . $i, str_replace('+', ' ', $font_family));
            }
        }
    }
}

add_filter('content_url', 'azexo_content_url_for_less', 10, 2);

function azexo_content_url_for_less($url, $path = '') {
    $siteurl = site_url();
    if (stripos($url, $siteurl) === false) { //multi site with domain mapping
        $url = $siteurl . '/wp-content';
        if ($path && is_string($path))
            $url .= '/' . ltrim($path, '/');
    }

    return $url;
}
