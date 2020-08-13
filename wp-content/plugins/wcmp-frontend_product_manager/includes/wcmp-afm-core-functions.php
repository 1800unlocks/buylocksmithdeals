<?php
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'current_vendor_can' ) ) {

    /**
     * Check if vendor has a certain capability 
     * @param string | ARRAY_N $capability 
     * @return boolean TRUE only if all passed capabilities are true for current vendor
     */
    function current_vendor_can( $capability ) {
        $current_vendor_id = afm()->vendor_id;
        if ( ! $current_vendor_id || empty( $capability ) ) {
            return false;
        }
        $vendor_role = get_role( 'dc_vendor' );
        $capabilities = isset( $vendor_role->capabilities ) ? $vendor_role->capabilities : array();

        if ( is_array( $capability ) ) {
            foreach ( $capability as $cap ) {
                if ( ! array_key_exists( $cap, $capabilities ) || ! $capabilities[$cap] ) {
                    return false;
                }
            }
            return true;
        }
        return array_key_exists( $capability, $capabilities ) && $capabilities[$capability];
    }

}
if ( ! function_exists( 'is_current_vendor_product' ) ) {

    function is_current_vendor_product( $product_id = 0, $vendor_id = 0 ) {
        global $WCMp;
        if ( ! $vendor_id ) {
            $vendor_id = afm()->vendor_id;
        }
        if ( $product_id && $vendor_id && is_user_wcmp_vendor( $vendor_id ) ) {
            $vendor_term = wp_get_object_terms( $product_id, $WCMp->taxonomy->taxonomy_name );
            $vendor_obj = null;
            if ( ! empty( $vendor_term->term_id ) ) {
                $vendor_obj = get_wcmp_vendor_by_term( $vendor_term->term_id );
            } else {
                $product_obj = get_post( $product_id );
                if ( is_object( $product_obj ) ) {
                    $author_id = $product_obj->post_author;
                    if ( $author_id ) {
                        $vendor_obj = get_wcmp_vendor( $author_id );
                    }
                }
            }
            if ( ! empty( $vendor_obj->id ) && $vendor_obj->id === $vendor_id ) {
                return true;
            }
        }
        return false;
    }

}
if ( ! function_exists( 'is_current_vendor_coupon' ) ) {

    function is_current_vendor_coupon( $coupon_id = 0, $vendor_id = 0 ) {
        global $WCMp;
        if ( ! $vendor_id ) {
            $vendor_id = afm()->vendor_id;
        }

        if ( ! ( $coupon_id && $vendor_id && is_user_wcmp_vendor( $vendor_id ) ) ) {
            return false;
        }

        $coupon = new WC_Coupon( $coupon_id );
        $coupon_post = get_post( $coupon_id );
        $coupon_author_id = absint( $coupon_post->post_author );

        if ( ! $coupon || $vendor_id !== $coupon_author_id ) {
            return false;
        }
        //the coupon is valid and belongs to the current vendor
        return true;
    }

}
if ( ! function_exists( 'get_current_vendor_shipping_classes' ) ) {

    function get_current_vendor_shipping_classes() {
        $current_vendor_id = afm()->vendor_id;
        $shipping_options = array();
        if ( $current_vendor_id ) {
            $shipping_classes = get_terms( 'product_shipping_class', array( 'hide_empty' => 0 ) );
            foreach ( $shipping_classes as $shipping_class ) {
                if ( apply_filters( 'wcmp_allowed_only_vendor_shipping_class', true ) ) {
                    $vendor_id = absint( get_woocommerce_term_meta( $shipping_class->term_id, 'vendor_id', true ) );
                    if ( $vendor_id === $current_vendor_id ) {
                        $shipping_options[$shipping_class->term_id] = array('slug'=> $shipping_class->slug, 'name'=> $shipping_class->name,);
                    }
                } else {
                    $shipping_options[$shipping_class->term_id] = array('slug'=> $shipping_class->slug, 'name'=> $shipping_class->name,);
                }
            }
        }
        return apply_filters( 'current_vendor_shipping_classes', $shipping_options );
    }

}
if ( ! function_exists( 'generate_hierarchical_taxonomy_html' ) ) {

    function generate_hierarchical_taxonomy_html( $taxonomy, $terms, $post_terms, $add_cap, $level = 0, $max_depth = 2 ) {
        $tax_html = '<ul class="taxonomy-widget ' . $taxonomy . ' level-' . $level . '">';
        foreach ( $terms as $term_id => $term_name ) {
            $child_html = '';
            if ( $max_depth > $level ) {
                $child_terms = get_terms( array(
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => false,
                    'orderby'    => 'name',
                    'parent'     => absint( $term_id ),
                    'fields'     => 'id=>name',
                    ) );
                if ( ! empty( $child_terms ) && ! is_wp_error( $child_terms ) ) {
                    $child_html = generate_hierarchical_taxonomy_html( $taxonomy, $child_terms, $post_terms, $add_cap, $level + 1 );
                }
            }

            $tax_html .= '<li><label><input type="checkbox" name="tax_input[' . $taxonomy . '][]" value="' . $term_id . '" ' . checked( in_array( $term_id, $post_terms ), true, false ) . '> ' . $term_name . $child_html . '</label></li>';
        }
        $tax_html .= '</ul>';
        if ( $add_cap ) {
            $label = '';
            switch ( $taxonomy ) {
                case 'product_cat':
                    $label = __( 'Add new product category', WCMp_AFM_TEXT_DOMAIN );
                    break;
                default:
                    $label = __( 'Add new item', WCMp_AFM_TEXT_DOMAIN );
            }
            $tax_html .= '<a href="#">' . $label . '</a>';
        }
        return $tax_html;
    }

}
if ( ! function_exists( 'afm_default_product_types' ) ) {

    function afm_default_product_types() {
        return array(
            'simple'   => __( 'Simple product', 'woocommerce' ),
            'grouped'  => __( 'Grouped product', 'woocommerce' ),
            'external' => __( 'External/Affiliate product', 'woocommerce' ),
            'variable' => __( 'Variable product', 'woocommerce' ),
        );
    }

}
if ( ! function_exists( 'afm_get_product_types' ) ) {

    function afm_get_product_types() {
        return apply_filters( 'afm_product_type_selector', afm_default_product_types() );
    }

}
if ( ! function_exists( 'afm_is_allowed_product_type' ) ) {
    /*
     * @params MIXED
     * string or array 
     * 
     */

    function afm_is_allowed_product_type() {
        $product_types = afm_get_product_types();
        foreach ( func_get_args() as $arg ) {
            //typecast normal string params to array
            $a_arg = (array) $arg;
            foreach ( $a_arg as $key ) {
                if ( apply_filters( 'afm_is_allowed_product_type_check', array_key_exists( $key, $product_types ), $key, $product_types ) ) {
                    return true;
                }
            }
        }
        return false;
    }

}
if ( ! function_exists( 'afm_is_allowed_virtual' ) ) {

    function afm_is_allowed_virtual() {
        global $WCMp;
        return $WCMp->vendor_caps->vendor_can( 'virtual' );
    }

}
if ( ! function_exists( 'afm_is_allowed_downloadable' ) ) {

    function afm_is_allowed_downloadable() {
        global $WCMp;
        return $WCMp->vendor_caps->vendor_can( 'downloadable' );
    }

}
if ( ! function_exists( 'afm_get_post_permalink_html' ) ) {

    function afm_get_post_permalink_html( $id ) {
        if ( ! $id )
            return '';
        $post = get_post( $id );
        if ( ! $post )
            return '';

        list($permalink, $post_name) = afm_get_post_permalink( $post->ID );

        $view_link = false;
        $preview_target = '';

        if ( current_user_can( 'read_post', $post->ID ) ) {
            if ( 'draft' === $post->post_status || empty( $post->post_name ) ) {
                $view_link = get_preview_post_link( $post );
                $preview_target = " target='wp-preview-{$post->ID}'";
            } else {
                if ( 'publish' === $post->post_status || 'attachment' === $post->post_type ) {
                    $view_link = get_permalink( $post );
                } else {
                    // Allow non-published (private, future) to be viewed at a pretty permalink, in case $post->post_name is set
                    $view_link = str_replace( array( '%pagename%', '%postname%' ), $post->post_name, $permalink );
                }
            }
        }

        if ( mb_strlen( $post_name ) > 34 ) {
            $post_name_abridged = mb_substr( $post_name, 0, 16 ) . '&hellip;' . mb_substr( $post_name, -16 );
        } else {
            $post_name_abridged = $post_name;
        }
        $post_type = get_post_type( $post );
        $post_name_html = '<span id="afm-' . $post_type . '-name">' . esc_html( $post_name_abridged ) . '</span>';
        $display_link = str_replace( array( '%pagename%', '%postname%' ), $post_name_html, esc_html( urldecode( $permalink ) ) );

        $return = '';
        if ( $post_type === 'shop_coupon' ) {
           $type = 'coupon';
        } else {
           $type = 'product';
        }
        if ( false === strpos( $view_link, 'preview=true' ) ) {
           $return .= '<label>' . __( sprintf( 'View %s:', $type ) ) . "</label>\n";
        } else {
           $return .= '<label>' . __( sprintf( 'View %s:', $type ) ) . "</label>\n";
        }
        $return .= '<span id="afm-' . $post_type . '-permalink"><a href="' . esc_url( $view_link ) . '"' . $preview_target . '>' . $display_link . "</a></span>";

        return $return;
    }

}
if ( ! function_exists( 'afm_get_post_permalink' ) ) {

    function afm_get_post_permalink( $id ) {
        $post = get_post( $id );
        if ( ! $post )
            return array( '', '' );

        $original_status = $post->post_status;
        $original_date = $post->post_date;
        $original_name = $post->post_name;

        // Hack: get_permalink() would return ugly permalink for drafts, so we will fake that our post is published.
        if ( in_array( $post->post_status, array( 'draft', 'pending', 'future' ) ) ) {
            $post->post_status = 'publish';
            $post->post_name = sanitize_title( $post->post_name ? $post->post_name : $post->post_title, $post->ID );
        }

        $post->post_name = wp_unique_post_slug( $post->post_name, $post->ID, $post->post_status, $post->post_type, $post->post_parent );

        $post->filter = 'sample';

        $permalink = get_permalink( $post, true );

        // Replace custom post_type Token with generic pagename token for ease of use.
        $permalink = str_replace( "%$post->post_type%", '%pagename%', $permalink );
        $permalink = array( $permalink, $post->post_name );

        $post->post_status = $original_status;
        $post->post_date = $original_date;
        $post->post_name = $original_name;
        unset( $post->filter );
        return $permalink;
    }

}
if ( ! function_exists( 'afm_is_allowed_vendor_shipping' ) ) {

    function afm_is_allowed_vendor_shipping() {
        global $WCMp;
        if ( version_compare( $WCMp->version, '3.1.6', '<' ) && ! get_wcmp_vendor_settings( 'is_vendor_shipping_on', 'general' ) ) {
            // new vendor shipping setting value based on payment shipping settings
            if ( 'Enable' === get_wcmp_vendor_settings( 'give_shipping', 'payment' ) ) {
                update_wcmp_vendor_settings( 'is_vendor_shipping_on', 'Enable', 'general' );
            }
        }
        return 'Enable' === get_wcmp_vendor_settings( 'is_vendor_shipping_on', 'general' );
    }

}
if ( ! function_exists( 'afm_is_enabled_vendor_tax' ) ) {

    function afm_is_enabled_vendor_tax() {
        return apply_filters( 'afm_can_vendor_configure_tax', wc_tax_enabled() );
    }

}
if ( ! function_exists( 'afm_is_allowed_vendor_feature_product' ) ) {

    function afm_is_allowed_vendor_feature_product() {
        return apply_filters( 'afm_can_vendor_set_feature_product', true );
    }

}
if ( ! function_exists( 'afm_is_allowed_vendor_manage_stock' ) ) {

    function afm_is_allowed_vendor_manage_stock() {
        return apply_filters( 'afm_can_vendor_manage_stock', 'yes' === get_option( 'woocommerce_manage_stock' ) );
    }

}
if ( ! function_exists( 'afm_can_vendor_add_product_category' ) ) {

    function afm_can_vendor_add_product_category() {
        return apply_filters( 'wcmp_vendor_can_add_product_category', false, get_current_user_id() );
    }

}
if ( ! function_exists( 'afm_can_vendor_add_product_tag' ) ) {

    function afm_can_vendor_add_product_tag() {
        return apply_filters( 'wcmp_vendor_can_add_product_tag', true, get_current_user_id() );
    }

}
if ( ! function_exists( 'afm_get_product_terms_HTML' ) ) {

    function afm_get_product_terms_HTML( $taxonomy, $id = null, $add_cap = false, $hierarchical = true ) {
        $terms = array();
        $product_terms = get_terms( apply_filters( "wcmp_get_product_terms_{$taxonomy}_query_args", array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'orderby'    => 'name',
            'parent'     => 0,
            'fields'     => 'id=>name',
            ) ) );
        if ( ( empty( $product_terms ) || is_wp_error( $product_terms ) ) && ! $add_cap ) {
            return false;
        }
        $term_id_list = wp_get_post_terms( $id, $taxonomy, array( 'fields' => 'ids' ) );
        if ( ! empty( $term_id_list ) && ! is_wp_error( $term_id_list ) ) {
            $terms = $term_id_list;
        } else {
            $terms = array();
        }
        $terms = apply_filters( 'wcmp_get_product_terms_html_selected_terms', $terms, $taxonomy, $id );
        if ( $hierarchical ) {
            return generate_hierarchical_taxonomy_html( $taxonomy, $product_terms, $terms, $add_cap );
        } else {
            return generate_non_hierarchical_taxonomy_html( $taxonomy, $product_terms, $terms, $add_cap );
        }
    }

}
if ( ! function_exists( 'generate_non_hierarchical_taxonomy_html' ) ) {

    function generate_non_hierarchical_taxonomy_html( $taxonomy, $product_terms, $seleted_terms, $add_cap ) {
        $html = '';
        if ( ! empty( $product_terms ) || $add_cap ) {
            ob_start();
            ?>
            <select multiple = "multiple" data-placeholder = "<?php esc_attr_e( 'Select', WCMp_AFM_TEXT_DOMAIN ); ?>" class = "multiselect form-control <?php echo $taxonomy; ?>" name = "tax_input[<?php echo $taxonomy; ?>][]">
                <?php
                foreach ( $product_terms as $term_id => $term_name ) {
                    echo '<option value="' . $term_id . '" ' . selected( in_array( $term_id, $seleted_terms ), true, false ) . '>' . $term_name . '</option>';
                }
                ?>
            </select>
            <?php
            $html = ob_get_clean();
        }
        return $html;
    }

}