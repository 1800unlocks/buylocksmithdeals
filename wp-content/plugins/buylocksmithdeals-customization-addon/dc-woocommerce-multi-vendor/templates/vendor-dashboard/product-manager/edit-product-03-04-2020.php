<?php
/**
 * WCMp edit product template
 *
 * Used by WCMp_Products_Edit_Product->output()
 *
 * This template can be overridden by copying it to yourtheme/dc-product-vendor/vendor-dashboard/product-manager/edit-product.php.
 *
 * HOWEVER, on occasion WCMp will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp/templates/vendor dashboard/product manager
 * @version     3.3.0
 */
defined( 'ABSPATH' ) || exit;

global $WCMp,$wpdb;
?> 
<style>
    #wcmp_afm_product_draft{
        display: none;
    }
    </style>
<div class="col-md-12 add-product-wrapper">
    <?php //do_action( 'before_wcmp_add_product_form' ); ?>
    <form id="wcmp-edit-product-form" class="woocommerce form-horizontal" method="post">
        <input type="hidden" name="post_ID" value="<?php echo $post->ID ;?>">
        <?php //do_action( 'wcmp_add_product_form_start' ); ?>
        <!-- Top product highlight -->
        <div style="display:none">
        <?php
        $WCMp->template->get_template( 'vendor-dashboard/product-manager/views/html-product-highlights.php', array( 'self' => $self, 'product_object' => $product_object, 'post' => $post, 'is_update' => $is_update ) );
        ?>
        </div>
        <!-- End of Top product highlight -->
        <div class="product-primary-info custom-panel"> 
            <div class="post_title_detail_page">
            <h1><?php echo $post->post_title;?></h1>
            </div>
            <div class="right-primary-info"> 
                   <?php  $status = get_post_meta($post->ID, 'status_vendor',true); ?>
                    <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_regular_price"><?php echo __( 'Status', 'woocommerce' ); ?></label>
                    <div class="col-md-6 col-sm-9">
                        <select class="status" name="status_vendor">
                            <option value="publish" <?php if($status=='publish') echo 'selected="selecetd"';?>>Publish</option>
                            <option value="draft" <?php if($status=='draft') echo 'selected="selecetd"';?>>Draft</option>
                            
                        </select>
                    </div>
                </div> 
                     <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="prod_vendor_TnC"><?php echo __( 'Terms and Conditions', 'woocommerce' ); ?></label>
                    <div class="col-md-9 col-sm-9">
                <?php
                
                $settings = array(
                                'textarea_name' => 'prod_vendor_TnC',
                                'textarea_rows' => get_option('default_post_edit_rows', 10),
                                'quicktags'     => array( 'buttons' => 'em,strong,link' ),
                                'tinymce'       => array(
                                    'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                                    'theme_advanced_buttons2' => '',
                                ),
                                'editor_css'    => '<style>#wp-product_excerpt-editor-container .wp-editor-area{height:200px; width:100%;}</style>',
                            );
                            wp_editor( htmlspecialchars_decode( get_post_meta($post->ID, 'prod_vendor_TnC',true) ), '', $settings );
                ?>
                       
                    </div>
                </div> 
                <div class="form-group-wrapper"  style="display:none">
                    <div class="form-group product-short-description">
                        <label class="control-label col-md-12 pt-0" for="product_short_description"><?php esc_html_e( 'Product short description', 'woocommerce' ); ?></label>
                        <div class="col-md-12">
                            <?php
                            $settings = array(
                                'textarea_name' => 'product_excerpt',
                                'textarea_rows' => get_option('default_post_edit_rows', 10),
                                'quicktags'     => array( 'buttons' => 'em,strong,link' ),
                                'tinymce'       => array(
                                    'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                                    'theme_advanced_buttons2' => '',
                                ),
                                'editor_css'    => '<style>#wp-product_excerpt-editor-container .wp-editor-area{height:100px; width:100%;}</style>',
                            );
                            wp_editor( htmlspecialchars_decode( $product_object->get_short_description( 'edit' ) ), 'product_excerpt', $settings );
                            ?>  
                        </div>
                    </div>
                    
                    <div class="form-group product-description">
                        <label class="control-label col-md-12" for="product_description"><?php esc_attr_e( 'Product description', 'woocommerce' ); ?></label>
                        <div class="col-md-12">
                            <?php
                            $settings = array(
                                'textarea_name' => 'product_description',
                                'textarea_rows' => get_option('default_post_edit_rows', 10),
                                'quicktags'     => array( 'buttons' => 'em,strong,link' ),
                                'tinymce'       => array(
                                    'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                                    'theme_advanced_buttons2' => '',
                                ),
                                'editor_css'    => '<style>#wp-product_description-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
                            );
                            wp_editor( $product_object->get_description( 'edit' ), 'product_description', $settings );
                            ?>
                        </div>
                    </div>
                </div> 
            </div>
<!--            <div class="left-primary-info">-->
            <div class="left-primary-info">
             
                <div class="product-gallery-wrapper">
                    <div class="featured-img upload_image"><?php $featured_img = $product_object->get_image_id( 'edit' ) ? $product_object->get_image_id( 'edit' ) : ''; ?>
                        <a href="#" class="upload_image_button tips <?php echo $featured_img ? 'remove' : ''; ?>" <?php echo current_user_can( 'upload_files' ) ? '' : 'data-nocaps="true" '; ?>data-title="<?php esc_attr_e( 'Product image', 'woocommerce' ); ?>" data-button="<?php esc_attr_e( 'Set product image', 'woocommerce' ); ?>" rel="<?php echo esc_attr( $post->ID ); ?>">
                            <div class="upload-placeholder pos-middle">
                                <i class="wcmp-font ico-image-icon"></i>
                                <p><?php _e( 'Click to upload Image', 'dc-woocommerce-multi-vendor' );?></p>
                            </div>
                            <img src="<?php echo $featured_img ? esc_url( wp_get_attachment_image_src( $featured_img, 'medium' )[0] ) : esc_url( wc_placeholder_img_src() ); ?>" />
                            <input type="hidden" name="featured_img" class="upload_image_id" value="<?php echo esc_attr( $featured_img ); ?>" />
                        </a>
                    </div>
                    <div id="product_images_container" class="custom-panel">
                        <h3><?php _e( 'Product gallery', 'dc-woocommerce-multi-vendor' );?></h3>
                        <ul class="product_images">
                            <?php
                            if ( metadata_exists( 'post', $post->ID, '_product_image_gallery' ) ) {
                                $product_image_gallery = get_post_meta( $post->ID, '_product_image_gallery', true );
                            } else {
                                // Backwards compatibility.
                                $attachment_ids = get_posts( 'post_parent=' . $post->ID . '&numberposts=-1&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids&meta_key=_woocommerce_exclude_image&meta_value=0' );
                                $attachment_ids = array_diff( $attachment_ids, array( get_post_thumbnail_id() ) );
                                $product_image_gallery = implode( ',', $attachment_ids );
                            }

                            $attachments = array_filter( explode( ',', $product_image_gallery ) );
                            $update_meta = false;
                            $updated_gallery_ids = array();

                            if ( ! empty( $attachments ) ) {
                                foreach ( $attachments as $attachment_id ) {
                                    $attachment = wp_get_attachment_image( $attachment_id, 'thumbnail' );

                                    // if attachment is empty skip
                                    if ( empty( $attachment ) ) {
                                        $update_meta = true;
                                        continue;
                                    }

                                    echo '<li class="image" data-attachment_id="' . esc_attr( $attachment_id ) . '">
                                            ' . $attachment . '
                                            <ul class="actions">
                                                <li><a href="#" class="delete tips" data-tip="' . esc_attr__( 'Delete image', 'woocommerce' ) . '">' . __( 'Delete', 'woocommerce' ) . '</a></li>
                                            </ul>
                                        </li>';

                                    // rebuild ids to be saved
                                    $updated_gallery_ids[] = $attachment_id;
                                }

                                // need to update product meta to set new gallery ids
                                if ( $update_meta ) {
                                    update_post_meta( $post->ID, '_product_image_gallery', implode( ',', $updated_gallery_ids ) );
                                }
                            }
                            ?>    
                        </ul>
                        <input type="hidden" id="product_image_gallery" name="product_image_gallery" value="<?php esc_attr_e( $product_image_gallery ); ?>" />
                        <p class="add_product_images">
                            <a href="#" <?php echo current_user_can( 'upload_files' ) ? '' : 'data-nocaps="true" '; ?>data-choose="<?php esc_attr_e( 'Add images to product gallery', 'woocommerce' ); ?>" data-update="<?php esc_attr_e( 'Add to gallery', 'woocommerce' ); ?>" data-delete="<?php esc_attr_e( 'Delete image', 'woocommerce' ); ?>" data-text="<?php esc_attr_e( 'Delete', 'woocommerce' ); ?>"><?php _e( 'Add product gallery images', 'woocommerce' ); ?></a>
                        </p>
                    </div>
                </div>
            </div> 
        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="woocommerce-product-data" class="add-product-info-holder">   

                    <div class="add-product-info-header row-padding">
                        <div class="select-group">
                            <label for="product-type"><?php esc_html_e( 'Product Type', 'woocommerce' ); ?></label>
                            <select class="form-control inline-select" id="product-type" name="product-type">
                                <?php foreach ( wcmp_get_product_types() as $value => $label ) : ?>
                                    <option value="<?php esc_attr_e( $value ); ?>" <?php echo selected( $product_object->get_type(), $value, false ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php
                        $product_type_options = $self->get_product_type_options();
                        $required_types = array();
                        foreach ( $product_type_options as $type ) {
                            if ( isset( $type['wrapper_class'] ) ) {
                                $classes = explode( ' ', str_replace( 'show_if_', '', $type['wrapper_class'] ) );
                                foreach ( $classes as $class ) {
                                    $required_types[$class] = true;
                                }
                            }
                        }
                        ?>
                        <?php if ( wcmp_is_allowed_product_type( array_keys( $required_types ) ) ) :
                            ?>
<!--                            <div class="pull-right">
                                <?php //foreach ( $self->get_product_type_options() as $key => $option ) : ?>
                                    <?php
                                    //if ( ! empty( $post->ID ) && metadata_exists( 'post', $post->ID, '_' . $key ) ) {
                                     //   $selected_value = is_callable( array( $product_object, "is_$key" ) ) ? $product_object->{"is_$key"}() : 'yes' === get_post_meta( $post->ID, '_' . $key, true );
                                    //} else {
                                    //    $selected_value = 'yes' === ( isset( $option['default'] ) ? $option['default'] : 'no' );
                                   // }
                                    ?>
                                    <label for="<?php //esc_attr_e( $option['id'] ); ?>" class="<?php //esc_attr_e( $option['wrapper_class'] ); ?> tips" data-tip="<?php //echo esc_attr( $option['description'] ); ?>"><input type="checkbox" name="<?php //echo esc_attr( $option['id'] ); ?>" id="<?php //echo esc_attr( $option['id'] ); ?>" <?php //echo checked( $selected_value, true, false ); ?> /> <?php //echo esc_html( $option['label'] ); ?></label>
                                <?php //endforeach; ?>
                            </div>-->
                        <?php endif; ?>
                    </div>

                    <!-- product Info Tab start -->
                    <div class="product-info-tab-wrapper" role="tabpanel">
                        <!-- Nav tabs start -->
                        <div class="product-tab-nav-holder">
                            <div class="tab-nav-direction-wrapper"></div>
                            <ul class="nav nav-tabs" role="tablist" id="product_data_tabs">
                                <?php foreach ( $self->get_product_data_tabs() as $key => $tab ) : ?>
                                    <?php if ( apply_filters( 'wcmp_afm_product_data_tabs_filter', ( ! isset( $tab['p_type'] ) || array_key_exists( $tab['p_type'], wcmp_get_product_types() ) && $WCMp->vendor_caps->vendor_can( $tab['p_type'] ) ), $key, $tab ) ) : ?>
                                <?php
                                $is_hidden = 'style="display:none"';
                                if($tab['label']=='General' 
                                        || $tab['label']=='Costs'
                                        || $tab['label']=='Availability'
                                        ){
                                    $is_hidden = '';
                                        }
                                ?>
                                <li <?php echo $is_hidden;?> role="presentation" class=" <?php esc_attr_e( $key ); ?>_options <?php esc_attr_e( $key ); ?>_tab <?php echo esc_attr( isset( $tab['class'] ) ? implode( ' ', (array) $tab['class'] ) : ''  ); ?>">
                                            <a href="#<?php esc_attr_e( $tab['target'] ); ?>" aria-controls="<?php echo $tab['target']; ?>" role="tab" data-toggle="tab"><span><?php echo esc_html( $tab['label'] ); ?></span></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php do_action( 'wcmp_product_write_panel_tabs', $post->ID ); ?>
                            </ul>
                        </div>
                        <!-- Nav tabs End -->

                        <!-- Tab content start -->
                        <div class="tab-content">
                            <?php
                            $WCMp->template->get_template( 'vendor-dashboard/product-manager/views/html-product-data-general.php', array( 'self' => $self, 'product_object' => $product_object, 'post' => $post ) );
                            //$WCMp->template->get_template( 'vendor-dashboard/product-manager/views/html-product-data-inventory.php', array( 'self' => $self, 'product_object' => $product_object, 'post' => $post ) );
                            if ( wcmp_is_allowed_vendor_shipping() ) {
                                $WCMp->template->get_template( 'vendor-dashboard/product-manager/views/html-product-data-shipping.php', array( 'self' => $self, 'product_object' => $product_object, 'post' => $post ) );
                            }
                            $WCMp->template->get_template( 'vendor-dashboard/product-manager/views/html-product-data-linked-products.php', array( 'self' => $self, 'product_object' => $product_object, 'post' => $post ) );
                            $WCMp->template->get_template( 'vendor-dashboard/product-manager/views/html-product-data-attributes.php', array( 'self' => $self, 'product_object' => $product_object, 'post' => $post ) );
                            do_action( 'wcmp_after_attribute_product_tabs_content', $self, $product_object, $post );
                            $WCMp->template->get_template( 'vendor-dashboard/product-manager/views/html-product-data-advanced.php', array( 'self' => $self, 'product_object' => $product_object, 'post' => $post ) );
                            ?>
                            <?php do_action( 'wcmp_product_tabs_content', $self, $product_object, $post ); ?>
                        </div>
                         <!-- Tab content End -->
                    </div>   
                    <?php $post_id= $post->ID;
                        $product_parent= get_post_meta($post_id,'_vendor_product_parent',true);
                        if(!empty($product_parent)){
                            $post_parent_id=$product_parent;
                        }
                        $show_unserviceable_cars = get_post_meta($post_parent_id,'show_unserviceable_cars',true);
                        $where_need_service = get_post_meta($post_parent_id,'where_need_service',true);
                        $quantity_of_locks = get_post_meta($post_parent_id,'quantity_of_locks',true);
                        $house_keys_included = get_post_meta($post_parent_id,'house_keys_included',true);
                        $car_key_look_like = get_post_meta($post_parent_id,'car_key_look_like',true);
                        $want_more_than_one_key = get_post_meta($post_parent_id,'want_more_than_one_key',true);
                        $who_supplies_deadbolts = get_post_meta( $post_id, 'who_supplies_deadbolts', true );
                        $type_of_installation = get_post_meta( $post_id, 'type_of_installation', true );
                        $quantity_of_locks_install = get_post_meta( $post_id, 'quantity_of_locks_install', true );
                        
                        if($show_unserviceable_cars == 'yes'){
                    ?>
                    <div class="unserviceable-cars">
                        <?php 
                       $unserviceable_cars=get_post_meta($post->ID,'unserviceable_cars',true);
                       
                       $table_name=BuyLockSmithDealsCustomizationAddon::blsd_y_m_model_table_name();
                        $result=$wpdb->get_results("Select DISTINCT maker,model,year,id from $table_name",ARRAY_A);
                        if(!empty($result)){
                            ?>
                        <div class="quantity_locks main_heading">
                            <label for="unserviceable_cars">Select Unserviceable Cars</label> </div>
                        <!--<select  multiple name="unserviceable_cars[]" id="unserviceable_cars" >
                        <?php
                           /* foreach($result as $row){
                                if($row['year'] == 0){
                                    $display=$row['maker'];
                                }
                                else{
                                $display=$row['maker'].'-'.$row['model'].'-'.$row['year'];
                                }*/
                                ?>
                            <option <?php // if(!empty($unserviceable_cars) && in_array($row['id'],$unserviceable_cars)){ echo "selected"; } ?>  value="<?php echo $row['id']; ?>"><?php echo $display; ?></option>
                                <?php
                           // } ?>
                        </select>-->
                        <input type="text" name="unserviceable_cars" id="unserviceable_cars" value="<?php echo implode(',',$unserviceable_cars) ?>" >
                            <?php
                        }
                        $car_programming_fee=  get_post_meta($post_id, 'car_programming_fee', true);
                        $car_vats_programming_fee=  get_post_meta($post_id, 'car_vats_programming_fee', true);
                        $works_on_vats_car=  get_post_meta($post_id, 'works_on_vats_car', true);
                        ?>
                        
                    </div>
                    <div class="quantity_locks">
                        <label>Enter Car Programming Fee</label><input type="number" name="car_programming_fee" id="car_programming_fee" value="<?php if(isset($car_programming_fee)){ echo $car_programming_fee; } ?>" min="0">
                    </div>
                    <div class="quantity_locks">    
                        <label>Enter cost to do VATS programming of each additional car key (less cutting price)</label><input type="number" name="car_vats_programming_fee" id="car_vats_programming_fee" value="<?php if(isset($car_vats_programming_fee)){ echo $car_vats_programming_fee; } ?>" min="0">
                    </div>
                    <div class="quantity_locks ">    
                        <label>Not Works on cars that are VATS</label><input type="checkbox" class="chk_box" name="works_on_vats_car" id="works_on_vats_car" <?php if($works_on_vats_car == 'yes'){ echo 'checked'; } ?> value="1" >
                    </div>
                        <?php } 
                        if($where_need_service == 'yes'){
                         ?>
                        
                    <div class="service_location">
                        <?php $addon=get_post_meta($post_id, '_product_addons', true);
                              $default_miles=  get_post_meta($post_id, 'default_miles', true);
                               $extra_permile_price= get_post_meta($post_id, 'extra_permile_price', true);
                               $maximum_miles= get_post_meta($post_id, 'maximum_miles', true);
                               $mobile_locksmith= get_post_meta($post_id, 'mobile_locksmith', true);
                               $mobile_locksmith_address= get_post_meta($post_id, 'mobile_locksmith_address', true);
                                if($mobile_locksmith == 'no' || $mobile_locksmith ==''){
                                   $lock_service_area='disabled';
                                }
                                else{
                                    $lock_service_area='';
                                }
                        ?>
                        <div class="quantity_locks main_heading">
                            <label>Where do you need service?</label></div>
                        <div class="quantity_locks">    
                            <label>Are you a mobile locksmith</label><input type="checkbox" class="chk_box" name="mobile_locksmith" id="mobile_locksmith" <?php if($mobile_locksmith == 'yes'){ echo 'checked'; } ?> value="1" >
                        </div>
                        <div class="quantity_locks <?php echo $lock_service_area; ?>" id="mob_lock_service_area">    
                            <label>Enter your mobile locksmith service area</label><input type="text" name="mobile_locksmith_address" id="mobile_locksmith_address"  value="<?php if(isset($mobile_locksmith_address) && !empty($mobile_locksmith_address)){ echo $mobile_locksmith_address; } ?>" >
                        </div>
                        
                        
                        <div class="select_option">
                            <input type="hidden" name="service_title" id="title" value="Where do you need service?"> 
                            <div>
                                <table class="need_service">
                                    <thead>
                                        <tr>
                                            
                                    <!--<th scope="col">Service Call Fee</th> -->
                                    <th scope="col">Service call Price</th>
                                    <th scope="col">Normal Service Area(Miles)</th>
                                    <th scope="col">Extra Per Mile Price</th>
                                    <th scope="col">Maximum Travel Miles</th>
                                        </tr>
                                </thead>
                                    <tr>
                                        <!-- <td scope="row"  data-label="label">
                                            <input type="text" readonly name="label_my_location" id="label" value="<?php // if(isset($addon[0]['options'][0]['label'])){ echo $addon[0]['options'][0]['label']; } else{ echo "Customer's Location"; }  ?>" placeholder="Customer's Location" >
                                        </td> -->
                                        <td data-label="Service call Price">
                                          <input type="number" name="price_my_location" id="price_my_location" value="<?php if(isset($addon[0]['options'][0]['price'])){ echo $addon[0]['options'][0]['price']; }   ?>" min="0" >
                                        </td>
                                        <td data-label="Normal Service Area(Miles)">
                                          <input type="number" name="default_miles" id="default_miles" value="<?php echo $default_miles; ?>" min="0" >
                                        </td>
                                        <td data-label="Extra Per Mile Price">
                                          <input type="number" name="extra_permile_price" id="extra_permile_price" value="<?php echo $extra_permile_price; ?>" min="0" >
                                        </td>
                                        <td data-label="Maximum Travel Miles">
                                          <input type="number" name="maximum_miles" id="maximum_miles" value="<?php echo $maximum_miles; ?>" min="0" >
                                        </td>
                                    </tr>
                                </table>
                                </div>
                            
                        </div>
                    </div>
                        <?php } ?>
                    <?php if($quantity_of_locks == 'yes'){ 
                        $cylinders_included=get_post_meta($post->ID, 'cylinders_included', true);
                        $extra_per_cylinders_included_price=get_post_meta($post->ID, 'extra_per_cylinders_included_price', true);
                        $cost_cut_standard_house_key=  get_post_meta($post->ID, 'cost_cut_standard_house_key', true);
                    ?>
                    <div class="quantity_locks">
                        <label> Enter No of cylinders included</label><input type="number" name="cylinders_included" id="cylinders_included" value="<?php if(isset($cylinders_included)){ echo $cylinders_included; } ?>" min="0">
                    </div>
                    <div class="quantity_locks">
                        <label> Enter Extra per cylinder price</label><input type="number" name="extra_per_cylinders_included_price" id="extra_per_cylinders_included_price" value="<?php if(isset($extra_per_cylinders_included_price)){ echo $extra_per_cylinders_included_price; } ?>" min="0">
                    </div>
                    <div class="quantity_locks">   
                        <label> Enter cost to cut additional standard house keys</label><input type="number" name="cost_cut_standard_house_key" id="cost_cut_standard_house_key" value="<?php if(isset($cost_cut_standard_house_key)){ echo $cost_cut_standard_house_key; } ?>" min="0">
                    </div>
                    <?php } ?>
                    <?php if($car_key_look_like == 'yes'){
                            $edge_cut_price=get_post_meta($post->ID, 'edge_cut_price', true);
                            $high_security_price=get_post_meta($post->ID, 'high_security_price', true);
                            $tibbe_price=get_post_meta($post->ID, 'tibbe_price', true);
                            $vats_price=get_post_meta($post->ID, 'vats_price', true);
                            $file_edge_cut=get_option('file_edge_cut');
                            $file_high_security=get_option('file_high_security');
                            $file_tibbe= get_option('file_tibbe');
                            $file_vats= get_option('file_vats');
                            $target_dir_img = WP_PLUGIN_URL  .'/'.BUYLOCKSMITH_DEALS_BASE_FOLDER_NAME . "/uploads/";
                    ?>
                    <div class="quantity_locks main_heading">
                    <label>What does your car key look like?</label>
                    </div>
                    <div class="quantity_locks">
                        <label> Enter Double-Sided Car Key Cutting Price</label><input type="number" name="edge_cut_price" id="edge_cut_price" value="<?php if(isset($edge_cut_price)){ echo $edge_cut_price; } ?>" min="0">
                         <?php if(!empty($file_edge_cut)){ ?>
                        <img src="<?php echo $target_dir_img.$file_edge_cut; ?>" height="50" width="50">
                        <?php } ?>
                    </div>
                    <div class="quantity_locks">
                        <label> Enter High-Security Car Key Cutting Price</label><input type="number" name="high_security_price" id="high_security_price" value="<?php if(isset($high_security_price)){ echo $high_security_price; } ?>" min="0">
                    <?php if(!empty($file_high_security)){ ?>
                <img src="<?php echo $target_dir_img.$file_high_security; ?>" height="50" width="50">
                <?php } ?>
                    </div>
                    <div class="quantity_locks">
                        <label>Enter Tibee Car Key Cutting Price</label><input type="number" name="tibbe_price" id="tibbe_price" value="<?php if(isset($tibbe_price)){ echo $tibbe_price; } ?>" min="0">
                    <?php if(!empty($file_tibbe)){ ?>
                <img src="<?php echo $target_dir_img.$file_tibbe; ?>" height="50" width="50">
                <?php } ?>
                    </div>
                    <div class="quantity_locks">
                        <label>Enter Vats Car Key Cutting Price</label><input type="number" name="vats_price" id="vats_price" value="<?php if(isset($vats_price)){ echo $vats_price; } ?>" min="0">
                    <?php if(!empty($file_vats)){ ?>
                <img src="<?php echo $target_dir_img.$file_vats; ?>" height="50" width="50">
                <?php } ?>
                    </div>
                    <?php
                    }
                    if($want_more_than_one_key == 'yes'){
                        $cost_to_cut_additional_key=get_post_meta($post->ID, 'cost_to_cut_additional_key', true);
                        $cost_to_program_additional_key=get_post_meta($post->ID, 'cost_to_program_additional_key', true);
                        ?>
                    <div class="quantity_locks main_heading">
                        <label>Do you want more than 1 key made?</label>
                    </div>
                     <!--<div class="quantity_locks">
                        <label> Enter Cost to Cut Each Additional Key</label><input type="number" name="cost_to_cut_additional_key" id="cost_to_cut_additional_key" value="<?php  //if(isset($cost_to_cut_additional_key)){ echo $cost_to_cut_additional_key; } ?>" min="0">
                     </div> -->
                     <div class="quantity_locks">
                        <label> Enter cost to do On-Board program of each additional car key (less cutting price)</label><input type="number" name="cost_to_program_additional_key" id="cost_to_program_additional_key" value="<?php if(isset($cost_to_program_additional_key)){ echo $cost_to_program_additional_key; } ?>" min="0">
                     </div>
                    <?php
                    }
                    
					
					 if($house_keys_included == 'yes'){ 
                        $house_keys_included_deal=get_post_meta($post->ID, 'house_keys_included_deal', true);
                        $extra_per_house_keys_included_price=get_post_meta($post->ID, 'extra_per_house_keys_included_price', true);
                     ?>
                    <div class="quantity_locks">
                        <label> Enter No of house keys included</label><input type="number" name="house_keys_included_deal" id="house_keys_included_deal" value="<?php if(isset($house_keys_included_deal)){ echo $house_keys_included_deal; } ?>" min="0">
                    </div>
                    <div class="quantity_locks">
                        <label> Enter Extra per house key price</label><input type="number" name="extra_per_house_keys_included_price" id="extra_per_house_keys_included_price" value="<?php if(isset($extra_per_house_keys_included_price)){ echo $extra_per_house_keys_included_price; } ?>" min="0">
                    </div>
                    <?php } 
                    
                    if($who_supplies_deadbolts == 'yes' && $type_of_installation == 'yes'){
                        $customer_fresh_install_deadbolt=get_post_meta($post->ID, 'customer_fresh_install_deadbolt', true);
                        $customer_replaced_deadbolt=get_post_meta($post->ID, 'customer_replaced_deadbolt', true);
                        $locksmith_fresh_install_deadbolt=get_post_meta($post->ID, 'locksmith_fresh_install_deadbolt', true);
                        $locksmith_replaced_deadbolt=get_post_meta($post->ID, 'locksmith_replaced_deadbolt', true);
                    ?>
                      <div class="quantity_locks">
                        <label> Enter Price For Each Customer Supplied Fresh-Install Deadbolt</label><input type="number" name="customer_fresh_install_deadbolt" id="customer_fresh_install_deadbolt" value="<?php  if(isset($customer_fresh_install_deadbolt)){ echo $customer_fresh_install_deadbolt; } ?>" min="0">
                     </div>
                      <div class="quantity_locks">
                        <label>Enter Price For Each Customer Supplied Replaced Deadbolt</label><input type="number" name="customer_replaced_deadbolt" id="customer_replaced_deadbolt" value="<?php  if(isset($customer_replaced_deadbolt)){ echo $customer_replaced_deadbolt; } ?>" min="0">
                     </div>
                      <div class="quantity_locks">
                        <label> Enter Price For Each Locksmith Supplied Fresh-Install Deadbolt </label><input type="number" name="locksmith_fresh_install_deadbolt" id="locksmith_fresh_install_deadbolt" value="<?php  if(isset($locksmith_fresh_install_deadbolt)){ echo $locksmith_fresh_install_deadbolt; } ?>" min="0">
                     </div>
                      <div class="quantity_locks">
                        <label>Enter Price For Each Locksmith Supplied Replaced Deadbolt</label><input type="number" name="locksmith_replaced_deadbolt" id="locksmith_replaced_deadbolt" value="<?php  if(isset($locksmith_replaced_deadbolt)){ echo $locksmith_replaced_deadbolt; } ?>" min="0">
                     </div>
                    <?php
                    }
                    if($quantity_of_locks_install == 'yes'){
						$deadbolt_cylinders_included=get_post_meta($post->ID, 'deadbolt_cylinders_included', true);
                        $extra_per_deadbolt_cylinders_included_price=get_post_meta($post->ID, 'extra_per_deadbolt_cylinders_included_price', true);
                        
                    ?>
                    <div class="quantity_locks">
                        <label> Enter No of Deadbolt cylinders included</label><input type="number" name="deadbolt_cylinders_included" id="deadbolt_cylinders_included" value="<?php if(isset($deadbolt_cylinders_included)){ echo $deadbolt_cylinders_included; } ?>" min="0">
                    </div>
                    <div class="quantity_locks">
                        <label> Enter Extra per deadbolt cylinder price</label><input type="number" name="extra_per_deadbolt_cylinders_included_price" id="extra_per_deadbolt_cylinders_included_price" value="<?php if(isset($extra_per_deadbolt_cylinders_included_price)){ echo $extra_per_deadbolt_cylinders_included_price; } ?>" min="0">
                    </div>
					<?php
					}
                    
                     $discount_on_deal=get_post_meta($post->ID, 'discount_on_deal', true);
                    ?>
                    <div class="quantity_locks">
                        <label> Enter Discount % on this deal</label><input type="number" name="discount_on_deal" id="discount_on_deal" value="<?php  if(isset($discount_on_deal)){ echo $discount_on_deal; } ?>" min="0">
                     </div>
                     
                    
                     
           
                    <!-- product Info Tab End -->
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
            <?php do_action( 'wcmp_after_product_excerpt_metabox_panel', $post->ID ); ?>
            <?php do_action( 'wcmp_afm_after_product_excerpt_metabox_panel', $post->ID ); ?>
            </div>
            <div class="col-md-4">
                <?php if( ( get_wcmp_vendor_settings('is_disable_marketplace_plisting', 'general') == 'Enable' ) ) :
                $product_categories = wcmp_get_product_terms_HTML( 'product_cat', $post->ID, apply_filters( 'wcmp_vendor_can_add_product_category', false, get_current_user_id() ) ); ?>
                <?php if ( $product_categories ) : ?>
                    <div class="panel panel-default pannel-outer-heading">
                        <div class="panel-heading">
                            <h3 class="pull-left"><?php esc_html_e( 'Product categories', 'woocommerce' ); ?></h3>
                        </div>
                        <div class="panel-body panel-content-padding form-group-wrapper"> 
                            <?php
                            echo $product_categories;
                            ?>
                        </div>
                    </div>
                <?php endif;
                endif; ?>
                <?php //$product_tags = wcmp_get_product_terms_HTML( 'product_tag', $post->ID, apply_filters( 'wcmp_vendor_can_add_product_tag', true, get_current_user_id() ), false ); ?>
                <?php $product_tags = ''; ?>
                <?php if ( $product_tags ) : ?>
                    <div class="panel panel-default pannel-outer-heading">
                        <div class="panel-heading">
                            <h3 class="pull-left"><?php esc_html_e( 'Product tags', 'woocommerce' ); ?></h3>
                        </div>
                        <div class="panel-body panel-content-padding form-group-wrapper">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <?php
                                    echo $product_tags;
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php 
                //$custom_taxonomies = get_object_taxonomies( 'product', 'objects' );
                $custom_taxonomies = [];
                if( $custom_taxonomies ){
                    foreach ( $custom_taxonomies as $taxonomy ) {
                        if ( in_array( $taxonomy->name, array( 'product_cat', 'product_tag' ) ) ) continue;
                        if ( $taxonomy->public && $taxonomy->show_ui && $taxonomy->meta_box_cb ) { ?>
                            <div class="panel panel-default pannel-outer-heading">
                                <div class="panel-heading">
                                    <h3 class="pull-left"><?php echo $taxonomy->label; ?></h3>
                                </div>
                                <div class="panel-body panel-content-padding form-group-wrapper">
                                    <div class="form-group">
                                        <div class="col-md-12">
                                            <?php
                                            echo wcmp_get_product_terms_HTML( $taxonomy->name, $post->ID, apply_filters( 'wcmp_vendor_can_add_'.$taxonomy->name, false, get_current_user_id() ) );
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }
                    }
                }
                ?>
                <?php // do_action( 'after_wcmp_product_tags_metabox_panel', $post->ID ); ?>
            </div>
        </div>
        <?php if ( ! empty( wcmp_get_product_types() ) ) : ?>
            <div class="wcmp-action-container">
                <?php
                $primary_action = __( 'Submit', 'dc-woocommerce-multi-vendor' );    //default value
                if ( current_vendor_can( 'publish_products' ) ) {
                    if ( ! empty( $product_object->get_id() ) && get_post_status( $product_object->get_id() ) === 'publish' ) {
                        $primary_action = __( 'Update', 'dc-woocommerce-multi-vendor' );
                    } else {
                        $primary_action = __( 'Publish', 'dc-woocommerce-multi-vendor' );
                         $primary_action = __( 'Update', 'dc-woocommerce-multi-vendor' );
                    }
                }
                $status_button = '';
                if(get_post_status( $post ) == 'draft'){
                  $status_button = 'disabled="true"';
                }
                ?>
                <input type="submit" class="btn btn-default" name="submit-data" <?php echo $status_button; ?> value="<?php esc_attr_e( $primary_action ); ?>" id="wcmp_afm_product_submit" />
                <input type="submit" class="btn btn-default" name="draft-data" value="<?php esc_attr_e( 'Draft', 'dc-woocommerce-multi-vendor' ); ?>" id="wcmp_afm_product_draft" />
                
                
                <input type="hidden" name="status" value="<?php esc_attr_e( get_post_status( $post ) ); ?>">
                <?php wp_nonce_field( 'wcmp-product', 'wcmp_product_nonce' ); ?>
            </div>
        <?php endif; ?>
        <?php do_action( 'wcmp_add_product_form_end' ); ?>
    </form>
    <?php do_action( 'after_wcmp_add_product_form' ); ?>
</div>
    <script>
        jQuery(document).ready(function(){
          // jQuery("#unserviceable_cars").select2(); 
          
var car_data=JSON.parse('<?php echo json_encode($result) ?>');
function mockData() {
        var result_data=[];
        var display='';
        return car_data.map(function(value,i) {
            if(value.year == 0){
                display=value.maker;
            }
            else{
            display=value.maker+'-'+value.model+'-'+value.year;
            }
            return {
              id: value.id,
              text:display,
            };
         })
}
(function() {
  // init select 2
  jQuery('#unserviceable_cars').select2({
    data: mockData(),
    placeholder: 'search',
    multiple: true,
    // query with pagination
    query: function(q) {
      var pageSize,
        results,
        that = this;
      pageSize = 20; // or whatever pagesize
      results = [];
      if (q.term && q.term !== '') {
        // HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
        results = _.filter(that.data, function(e) {
          return e.text.toUpperCase().indexOf(q.term.toUpperCase()) >= 0;
        });
      } else if (q.term === '') {
        results = that.data;
      }
      q.callback({
        results: results.slice((q.page - 1) * pageSize, q.page * pageSize),
        more: results.length >= q.page * pageSize,
      });
    },
  });
})();
           
           
           disabled_update();
           jQuery(document).on('focusout','#maximum_miles, #default_miles',function(){
              disabled_update();
          });
          function disabled_update(){
              var maximum_miles =parseFloat(jQuery('#maximum_miles').val());
              var default_miles =parseFloat(jQuery('#default_miles').val());
              console.log('default_miles',default_miles);
              console.log('maximum_miles',maximum_miles);
              if(default_miles > maximum_miles){
                  alert('Default miles should be less than Maximum miles');
                  jQuery("#wcmp_afm_product_submit").attr("disabled", true);
              }
              else{
                 jQuery("#wcmp_afm_product_submit").attr("disabled", false); 
              }
          }
            jQuery(document).on('click','#mobile_locksmith',function(){
                if(jQuery("#mobile_locksmith").prop('checked') == true){
                    jQuery("#mob_lock_service_area").removeClass('disabled');
                    
                }
                else
                { 
                    jQuery("#mobile_locksmith_address").val('');
                    jQuery("#mob_lock_service_area").addClass('disabled');
                   
                }
            });
          
        });
        
        </script>
        
        <style>
            
            @media screen and (max-width: 600px) {
  table {
    border: 0;
  }

  table caption {
    font-size: 1.3em;
  }
  
  table thead {
    border: none;
    clip: rect(0 0 0 0);
    height: 1px;
    margin: -1px;
    overflow: hidden;
    padding: 0;
    position: absolute;
    width: 1px;
  }
  
  table tr {
    border-bottom: 3px solid #ddd;
    display: block;
    margin-bottom: .625em;
  }
  
  table td {
    border-bottom: 1px solid #ddd;
    display: block;
    font-size: .8em;
    text-align: right;
  }
  
  table td::before {
    /*
    * aria-label has no advantage, it won't be read inside a table
    content: attr(aria-label);
    */
    content: attr(data-label);
    float: left;
    font-weight: bold;
    text-transform: uppercase;
  }
  
  table td:last-child {
    border-bottom: 0;
  }
}
            @media (max-width:767px){
            .quantity_locks label {
                font-weight: 600;
                width: 100%;
            }
            }
            .service_location input {
    height: auto;
    min-height: 34px;
    width: 100%;
    border: 1px solid #c5c5c5;
    max-height: 76px;
    overflow: auto;
    border-radius: 4px;
    padding: 0px 10px;
}
table.need_service th {
    font-weight: 900;
}
.service_location label {
    font-weight: 900;
    font-size: 16px;
}
.service_location .quantity_locks label {
    font-weight: 600;
    font-size: 14px;
}
.service_location .quantity_locks.main_heading label {
    font-weight: 600;
    font-size: 16px;
}
.quantity_locks label {
    font-weight: 600;
    width: 35%;
    color: #333b3d;
}
.quantity_locks input {
    height: auto;
    min-height: 34px;
    width: auto;
    border: 1px solid #c5c5c5;
    max-height: 76px;
    overflow: auto;
    border-radius: 4px;
    padding: 0px 10px;
}
.quantity_locks .chk_box {
    height: 100%;
    min-height: 20px;
    width: auto;
    border: 1px solid #c5c5c5;
    max-height: 76px;
    overflow: auto;
    border-radius: 4px;
    padding: 0px 10px;
}
.select_option, .unserviceable-cars, .quantity_locks {
    padding: 5px 20px;
}
.unserviceable-cars .quantity_locks {
    padding: 0;
}
table.need_service th {
    font-weight: 900;
    color: #333b3d;
}
.quantity_locks.main_heading {
    font-weight: 600;
    color: #000;
    font-size: 16px;
}

.quantity_locks.main_heading label {
    color: #000;
    width:100%;
}
table.need_service td, table.need_service th {
    padding: 2px 4px;
}
input#mobile_locksmith_address {
    width: 55%;
}
.disabled{
    display:none;
}
            </style>