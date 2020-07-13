<?php
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <!--<![endif]-->
    <head>
        <?php $options = get_option(AZEXO_FRAMEWORK); ?>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width">
        <link rel="profile" href="//gmpg.org/xfn/11">
        <link rel="pingback" href="<?php esc_url(bloginfo('pingback_url')); ?>">        
        <?php wp_head(); ?>
    </head>

    <body <?php body_class(); ?>>        
        <div id="preloader"><div id="status"></div></div>
        <div id="page" class="hfeed site">
            <header id="masthead" class="site-header clearfix">
                <?php
                get_sidebar('header');
                ?>                
                <div class="header-main clearfix">
                    <div class="header-parts <?php print ((isset($options['header_parts_fullwidth']) && $options['header_parts_fullwidth']) ? '' : 'container'); ?>">
                        <?php
                        azexo_header_parts();
                        ?>                        
                    </div>
                </div>
                <?php
                $default_template = isset($options['default_' . get_post_type() . '_template']) ? $options['default_' . get_post_type() . '_template'] : 'post';
                if (is_single() && isset($options['single_' . get_post_type() . '_template']) && !empty($options['single_' . get_post_type() . '_template'])) {
                    $default_template = $options['single_' . get_post_type() . '_template'];
                }
                if (!isset($template_name)) {
                    $template_name = apply_filters('azexo_template_name', $default_template);
                }
                if (!post_password_required() && !is_attachment() && is_singular()) :
                    ?>
                    <?php if (isset($options['header_gallery']) && $options['header_gallery'] && ((get_post_type() == 'post' && has_post_format('gallery')) || get_post_meta(get_the_ID(), '_gallery')) && !$image_thumbnail) : ?>
                        <div class="header-gallery">
                            <?php
                            azexo_post_gallery_field($template_name);
                            ?>
                        </div>
                    <?php elseif (isset($options['header_video']) && $options['header_video'] && ((get_post_type() == 'post' && has_post_format('video')) || get_post_meta(get_the_ID(), '_video')) && !$image_thumbnail && $post_video_field = azexo_post_video_field()) : ?>
                        <div class="header-video">
                            <?php
                            print $post_video_field;
                            ?>
                        </div>
                    <?php else: ?>
                        <?php if (isset($options['header_thumbnail']) && $options['header_thumbnail'] && has_post_thumbnail()) : ?>
                            <div class="header-thumbnail">
                                <?php
                                azexo_post_thumbnail_field($template_name);
                                ?>                
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php
                get_sidebar('middle');
                ?>                                
            </header><!-- #masthead -->
            <div id="main" class="site-main">
