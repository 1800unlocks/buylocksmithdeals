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

global $WCMp;
?> 
<div class="col-md-12 add-product-wrapper">
        <!-- Top product highlight -->
        <?php
        $success_message = '';
          
          $status = get_post_meta($post->ID, 'status_vendor',true);
       if(isset($_REQUEST['price_update'])){
           
           if(isset($_REQUEST['product_id'])){
               if($_REQUEST['product_id']!=''){
                   $current_post = get_post($post->ID);
                   
              
               
                   
                   
if(isset($current_post->post_author)){
    if($current_post->post_author == $post->post_author){
        
   
        update_post_meta($post->ID, 'status_vendor',$_REQUEST['status']);
        
        $regular_price = $_REQUEST['_regular_price'];
        $sale_price = $_REQUEST['_sale_price'];
        $price = $_REQUEST['_sale_price'];
        $prod_vendor_TnC = $_REQUEST['prod_vendor_TnC'];
        $featured_img = $_REQUEST['featured_img'];
        $product_image_gallery = $_REQUEST['product_image_gallery'];
        $_tax_status = $_REQUEST['_tax_status'];
        $_tax_class = $_REQUEST['_tax_class'];
        
        if($sale_price==''){
        $price = $_REQUEST['_regular_price'];
        }
        $product_object->get_regular_price( 'edit' ).$regular_price;
         if($product_object->get_regular_price( 'edit' )!=$regular_price || $product_object->get_sale_price( 'edit' )!=$sale_price ||  $status != $_REQUEST['status']){
             $status = $_REQUEST['status'];
                      $success_message = 'Updated Successfully!';
         }
        
        update_post_meta($post->ID, '_regular_price', $regular_price);
        update_post_meta($post->ID, '_sale_price', $sale_price);
        update_post_meta($post->ID, '_price', $price);
        update_post_meta($post->ID, 'prod_vendor_TnC', $prod_vendor_TnC);
        update_post_meta($post->ID, '_thumbnail_id', $featured_img);
        update_post_meta($post->ID, '_product_image_gallery', $product_image_gallery);
        update_post_meta($post->ID, '_tax_status', $_tax_status);
        update_post_meta($post->ID, '_tax_class', $_tax_class);
       // update_post_meta($post->ID, '_price', $price);
          $product_id = $post->ID;
                       $product_object; $post;
                      $product_object = new WC_Product( $product_id );
                     
    }
}                   
               }
           }
           
      
       }
       
       
       if(isset($_REQUEST['price_update_variation'])){
           
           if(isset($_REQUEST['product_id']) && is_array($_REQUEST['product_id'])){
         
         
             $status = $_REQUEST['status'];
                      $success_message = 'Updated Successfully!';
         
        update_post_meta($post->ID, 'status_vendor',$status);
        //  echo '<pre>';
//           print_r($_REQUEST);
//           exit;    
               $product_ids = $_REQUEST['product_id'];
               $regular_price = $_REQUEST['_regular_price'];
               $sale_price_arr = $_REQUEST['_sale_price'];
//               print_r($product_ids);
//               print_r($regular_price);
               foreach($product_ids as $p_id){
                   //echo $p_id;
                   $p_id = trim($p_id);
                   $regular_price_single = $regular_price[$p_id];
                 
        $sale_price = $sale_price_arr[$p_id];
        $price =  $sale_price_arr[$p_id];
        if($sale_price==''){
        $price = $regular_price_single;
        } 
             
         update_post_meta($p_id, '_price', $price);
         update_post_meta($p_id, '_regular_price', $regular_price_single);
        update_post_meta($p_id, '_sale_price', $sale_price);
               }
            //  exit;   
           }
           
       }
       
       
        ?>
        <!-- End of Top product highlight -->
          <?php
                    if($success_message!=''){
                        ?>
                    <div class="success-message btn-success"><?php echo  $success_message;?></div>
                    <?php
                    $success_message = '';
                    }
                    ?>
        <div class="product-primary-info custom-panel"> 
            
            <div class="right-primary-info-custom"> 
                 
                <div class="form-group-wrapper1">
                    
                 
                    <div class="form-group product-short-description">
              <?php
               $product_id = $post->ID;
                       $product_object; $post;
                      $product = new WC_Product( $product_id );
                      
                      $terms = get_the_terms($product->get_id(), 'product_type');
                     
                      $name = '';
                      if(isset($terms[0]->name)){
                         $name =  $terms[0]->name;
                      }
            
        if($name=='simple') {
                           $pricing_visibility = apply_filters( 'general_tab_pricing_section', array( 'simple', 'external' ) );
        if ( call_user_func_array( "wcmp_is_allowed_product_type", $pricing_visibility ) ) {
            $show_classes = implode( ' ', preg_filter( '/^/', 'show_if_', $pricing_visibility ) );
        }
            ?>
                        <form method="post" id="update_price">
            <div class="form-group-row pricing <?php echo $show_classes; ?>"> 
                <input type="hidden" name="product_id" value="<?php echo $product_id;?>">
                    <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_regular_price"><?php echo __( 'Status', 'woocommerce' ); ?></label>
                    <div class="col-md-6 col-sm-9">
                        <select class="status" name="status">
                            <option value="publish" <?php if($status=='publish') echo 'selected="selecetd"';?>>Publish</option>
                            <option value="draft" <?php if($status=='draft') echo 'selected="selecetd"';?>>Draft</option>
                            
                        </select>
                    </div>
                </div> 
                <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_regular_price"><?php echo __( 'Regular price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                    <div class="col-md-6 col-sm-9">
                        <input type="text" id="_regular_price" name="_regular_price" value="<?php echo $product_object->get_regular_price( 'edit' ); ?>" class="form-control">
                    </div>
                </div>  
                  <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_sale_price"><?php echo __( 'Sale price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                    <div class="col-md-6 col-sm-9">
                        <input type="text" id="_sale_price" name="_sale_price" value="<?php echo $product_object->get_sale_price( 'edit' ); ?>" class="form-control">
                       
                    </div>
                </div>
              <?php    $tax_visibility = apply_filters( 'general_tab_tax_section', array( 'simple', 'external', 'variable' ) );
        if ( apply_filters( 'wcmp_can_vendor_configure_tax', wc_tax_enabled() ) && call_user_func_array( "wcmp_is_allowed_product_type", $tax_visibility ) ) :
            $show_classes = implode( ' ', preg_filter( '/^/', 'show_if_', $tax_visibility ) );
            ?>
            <div class="form-group-row <?php echo $show_classes; ?>"> 
                <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_tax_status"><?php _e( 'Tax status', 'woocommerce' ); ?></label>
                    <div class="col-md-6 col-sm-9">
                        <select class="form-control" id="_tax_status" name="_tax_status">
                            <option value="taxable" <?php selected( $product_object->get_tax_status( 'edit' ), 'taxable' ); ?>><?php _e( 'Taxable', 'woocommerce' ); ?></option>
                            <option value="shipping" <?php selected( $product_object->get_tax_status( 'edit' ), 'shipping' ); ?>><?php _e( 'Shipping only', 'woocommerce' ); ?></option>
                            <option value="none" <?php selected( $product_object->get_tax_status( 'edit' ), 'none' ); ?>><?php _e( 'None', 'woocommerce' ); ?></option>
                        </select>
                    </div>
                </div> 
                <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_tax_class"><?php _e( 'Tax class', 'woocommerce' ); ?></label>
                    <div class="col-md-6 col-sm-9">
                        <select class="form-control" id="_tax_class" name="_tax_class">
                            <?php foreach ( wc_get_product_tax_class_options() as $class => $class_label ) : ?>
                                <option value="<?php echo $class; ?>" <?php selected( $product_object->get_tax_class( 'edit' ), $class ); ?>><?php echo $class_label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>  
            </div>
        <?php endif; ?>
                            
                
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
                            wp_editor( htmlspecialchars_decode( get_post_meta($post->ID, 'prod_vendor_TnC',true) ), 'product_excerpt', $settings );
                ?>
                       
                    </div>
                </div> 
                <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="prod_vendor_TnC"><?php echo __( 'Feature Image', 'woocommerce' ); ?></label>
                    <div class="col-md-9 col-sm-6">
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
                 
                </div>
                       
                    </div>
                </div> 
                <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="prod_vendor_TnC"><?php echo __( 'Product Gallery Image', 'woocommerce' ); ?></label>
                    <div class="col-md-9 col-sm-6">
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
                
                   
                <div class="form-group">
                   
                    <div class="col-md-12 col-sm-12">
                        <div class="btn-danger error_message_area"></div>
                       
                    </div>
                </div> 
                <div class="form-group">
                   
                    <div class="col-md-2 col-sm-3">
                        <input type="submit" id="_sale_price" name="price_update" value="Update" class="form-control btn btn-primary">
                       
                    </div>
                </div> 
                <?php
                //$sale_price_dates_from = $product_object->get_date_on_sale_from( 'edit' ) && ( $date = $product_object->get_date_on_sale_from( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
                //$sale_price_dates_to = $product_object->get_date_on_sale_to( 'edit' ) && ( $date = $product_object->get_date_on_sale_to( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
                ?> 
              
                <?php //do_action( 'wcmp_afm_product_options_pricing', $post->ID, $product_object, $post ); ?> 
            </div>
                            </form>
        <?php } 
        if($name=='variable') {
         
    
    $product = new WC_Product_Variable( $product_id );
$variations = $product->get_available_variations();
//echo '<pre>';
//    print_r($variations);
                           $pricing_visibility = apply_filters( 'general_tab_pricing_section', array( 'simple', 'external' ) );
        if ( call_user_func_array( "wcmp_is_allowed_product_type", $pricing_visibility ) ) {
            $show_classes = implode( ' ', preg_filter( '/^/', 'show_if_', $pricing_visibility ) );
        }
        if(count($variations)>0){
            
            ?>
                        <form method="post" id="update_price_variation">
                                 <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_regular_price"><?php echo __( 'Status', 'woocommerce' ); ?></label>
                    <div class="col-md-6 col-sm-9">
                        <select class="status" name="status">
                            <option value="publish" <?php if($status=='publish') echo 'selected="selecetd"';?>>Publish</option>
                            <option value="draft" <?php if($status=='draft') echo 'selected="selecetd"';?>>Draft</option>
                            
                        </select>
                    </div>
                </div> 
                            <?php
                            foreach($variations as $variation){
                            ?>
            <div class="form-group-row pricing <?php echo $show_classes; ?>"> 
                <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_regular_price"><?php echo __( 'Variation Name', 'woocommerce' ); ?></label>
                    <div class="col-md-6 col-sm-9">
                        <b>
                        <?php
                                                    echo implode(', ', $variation['attributes']);
                        ?>
                        </b>
                    </div>
                </div>  
                <input type="hidden" name="product_id[<?php echo $show_classes->variation_id;?>]" class="product_id" dataAttr="product_id_<?php echo $show_classes->variation_id; ?>" value="<?php echo $variation['variation_id'];?>">
                <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_regular_price"><?php echo __( 'Regular price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                    <div class="col-md-6 col-sm-9">
                        <input type="text" id="_regular_price" name="_regular_price[<?php echo $variation['variation_id']?>]" value="<?php echo $variation['display_regular_price']; ?>" class="form-control regular_price_<?php echo $variation['variation_id']; ?>">
                    </div>
                </div>  
                <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_sale_price"><?php echo __( 'Sale price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                    <div class="col-md-6 col-sm-9">
                        <input type="text" id="_sale_price" name="_sale_price[<?php echo $variation['variation_id'];?>]" value="<?php echo $variation['display_price']; ?>" class="form-control display_price_<?php echo $variation['variation_id']; ?>">
                       
                    </div>
                </div> 
                <div class="form-group">
                   
                    <div class="col-md-12 col-sm-12">
                        <div class="btn-danger error_message_area error_<?php echo $variation['variation_id']; ?>"></div>
                       
                    </div>
                </div> 
               
                <?php
                //$sale_price_dates_from = $product_object->get_date_on_sale_from( 'edit' ) && ( $date = $product_object->get_date_on_sale_from( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
                //$sale_price_dates_to = $product_object->get_date_on_sale_to( 'edit' ) && ( $date = $product_object->get_date_on_sale_to( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
                ?> 
              
                <?php //do_action( 'wcmp_afm_product_options_pricing', $post->ID, $product_object, $post ); ?> 
            </div>
                            <?php 
                            }
                            ?>
                            
                             <div class="form-group">
                   
                    <div class="col-md-2 col-sm-3">
                        <input type="submit" id="_sale_price" name="price_update_variation" value="Update" class="form-control btn btn-primary">
                       
                    </div>
                </div> 
                            </form>
        <?php }else{
            echo 'Contact to admin.';
        }
        }
        ?>
                    </div>
                    
                   
                </div> 
            </div>
      
        </div>
       
       
    
    
</div>

<script>
jQuery(document).ready(function(){
    jQuery('#update_price').on('submit',function(){
var regular_price = parseInt(jQuery('#_regular_price').val());
var sale_price = parseInt(jQuery('#_sale_price').val());


if(jQuery('#_regular_price').val()==''){
    jQuery('.error_message_area').html('Regular price is required.');
    return false;
}else if(!isNaN(regular_price) || !isNaN(sale_price) ){
    if(regular_price<=sale_price){
      jQuery('.error_message_area').html('Regular price should be greater then Sale price.');
      return false;
    }else{
      jQuery('.error_message_area').html('');  
    }
    
}


    });
    
});
</script>
<script>
    var is_error = 0;
jQuery(document).ready(function(){
    jQuery('#update_price_variation').on('submit',function(){
        
        is_error = 0;
        jQuery('.product_id').each(function(index,value){
          var ids =   jQuery(value).val();
           
var regular_price = parseInt(jQuery('.regular_price_'+ids).val());
var sale_price = parseInt(jQuery('.display_price_'+ids).val());


if(jQuery('.regular_price_'+ids).val()==''){
    jQuery('.error_'+ids).html('Regular price is required.');
    is_error = 1;
  
}else if(!isNaN(regular_price) || !isNaN(sale_price) ){
    if(regular_price<=sale_price){
      jQuery('.error_'+ids).html('Regular price should be greater then Sale price.');
      is_error = 1;
   
    }else{
      jQuery('.error_'+ids).html('');  
      
    }
    
}
        });
         if(is_error==1)     {
          return false;
      } 

 
    });
    
});
</script>