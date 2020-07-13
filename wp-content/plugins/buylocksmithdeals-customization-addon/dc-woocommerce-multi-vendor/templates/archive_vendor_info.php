<?php
/**
 * The template for displaying archive vendor info
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/archive_vendor_info.php
 *
 * @author      WC Marketplace
 * @package     WCMp/Templates
 * @version   2.2.0
 */
global $WCMp;
$vendor = get_wcmp_vendor($vendor_id);
$website=$vendor->user_data->data->user_url;
$vendor_hide_address = apply_filters('wcmp_vendor_store_header_hide_store_address', get_user_meta($vendor_id, '_vendor_hide_address', true), $vendor->id);
$vendor_hide_phone = apply_filters('wcmp_vendor_store_header_hide_store_phone', get_user_meta($vendor_id, '_vendor_hide_phone', true), $vendor->id);
$vendor_hide_email = apply_filters('wcmp_vendor_store_header_hide_store_email', get_user_meta($vendor_id, '_vendor_hide_email', true), $vendor->id);
$template_class = get_wcmp_vendor_settings('wcmp_vendor_shop_template', 'vendor', 'dashboard', 'template1');
$template_class = apply_filters('can_vendor_edit_shop_template', false) && get_user_meta($vendor_id, '_shop_template', true) ? get_user_meta($vendor_id, '_shop_template', true) : $template_class;
?>
<div class="vendor_description_background wcmp_vendor_banner_template <?php echo $template_class; ?>">
    <div class="wcmp_vendor_banner">
        <?php
            if($banner != ''){
        ?>
            <img src="<?php echo $banner; ?>" alt="<?php echo $vendor->page_title ?>">
        <?php
            } else{
        ?>
            <img src="<?php echo $WCMp->plugin_url . 'assets/images/banner_placeholder.jpg'; ?>" alt="<?php echo $vendor->page_title ?>">
        <?php        
            }
        ?>
        
        
        <?php if(apply_filters('wcmp_vendor_store_header_show_social_links', true, $vendor->id)) :?>
        <div class="wcmp_social_profile">
            <?php
            $vendor_fb_profile = get_user_meta($vendor_id, '_vendor_fb_profile', true);
            $vendor_twitter_profile = get_user_meta($vendor_id, '_vendor_twitter_profile', true);
            $vendor_linkdin_profile = get_user_meta($vendor_id, '_vendor_linkdin_profile', true);
            $vendor_google_plus_profile = get_user_meta($vendor_id, '_vendor_google_plus_profile', true);
            $vendor_youtube = get_user_meta($vendor_id, '_vendor_youtube', true);
            $vendor_instagram = get_user_meta($vendor_id, '_vendor_instagram', true);
            ?>
            <?php if ($vendor_fb_profile) { ?> <a target="_blank" href="<?php echo esc_url($vendor_fb_profile); ?>"><i class="wcmp-font ico-facebook-icon"></i></a><?php } ?>
            <?php if ($vendor_twitter_profile) { ?> <a target="_blank" href="<?php echo esc_url($vendor_twitter_profile); ?>"><i class="wcmp-font ico-twitter-icon"></i></a><?php } ?>
            <?php if ($vendor_linkdin_profile) { ?> <a target="_blank" href="<?php echo esc_url($vendor_linkdin_profile); ?>"><i class="wcmp-font ico-linkedin-icon"></i></a><?php } ?>
            <?php if ($vendor_google_plus_profile) { ?> <a class="gp-new-icon" target="_blank" href="<?php echo esc_url($vendor_google_plus_profile); ?>"><i class="wcmp-font ico-google-plus-icon-new aaaaa"></i></a><?php } ?>
            <?php if ($vendor_youtube) { ?> <a target="_blank" href="<?php echo esc_url($vendor_youtube); ?>"><i class="wcmp-font ico-youtube-icon"></i></a><?php } ?>
            <?php if ($vendor_instagram) { ?> <a target="_blank" href="<?php echo esc_url($vendor_instagram); ?>"><i class="wcmp-font ico-instagram-icon"></i></a><?php } ?>
        </div>
        <?php endif; ?>

        <?php
            if($template_class == 'template1'){
        ?>
        <div class="vendor_description">
            <div class="vendor_img_add">
                <div class="img_div"><img src=<?php echo $profile; ?> alt="<?php echo $vendor->page_title ?>"/></div>
                <div class="vendor_address">
                    <p class="wcmp_vendor_name"><?php echo $vendor->page_title ?></p>
                    <?php do_action('before_wcmp_vendor_information',$vendor_id);?>
                    <div class="see_deals"><input type="button" name="see_our_deals" id="see_our_deals" value="See Our Deals"></div>
                    <div class="wcmp_vendor_rating">
                        <?php
                        if (get_wcmp_vendor_settings('is_sellerreview', 'general') == 'Enable') {
                            $queried_object = get_queried_object();
                            if (isset($queried_object->term_id) && !empty($queried_object)) {
                                $rating_val_array = wcmp_get_vendor_review_info($queried_object->term_id);
                                $WCMp->template->get_template('review/rating.php', array('rating_val_array' => $rating_val_array));
                            }
                        }
                        ?>      
                    </div>  
                    
                    <div class="wcmp_mobile_contact_info contact_detail">
                    <?php if (!empty($location) && $vendor_hide_address != 'Enable') { ?><p class="wcmp_vendor_detail"><i class="wcmp-font ico-location-icon"></i><label><?php echo $location; ?></label></p><?php } ?>
                    <?php if (!empty($mobile) && $vendor_hide_phone != 'Enable') { ?><p class="wcmp_vendor_detail"><i class="wcmp-font ico-call-icon"></i><label><?php echo apply_filters('vendor_shop_page_contact', $mobile, $vendor_id); ?></label></p><?php } ?>
                    <?php if (!empty($email) && $vendor_hide_email != 'Enable') { ?><a href="mailto:<?php echo apply_filters('vendor_shop_page_email', $email, $vendor_id); ?>" class="wcmp_vendor_detail"><i class="wcmp-font ico-mail-icon"></i><?php echo apply_filters('vendor_shop_page_email', $email, $vendor_id); ?></a><?php } ?>
                    
					<?php if (!empty($website)) { ?><a href="<?php echo apply_filters('vendor_shop_page_website', $website, $vendor_id); ?>" target="_blank" class="wcmp_vendor_detail"><?php echo apply_filters('vendor_shop_page_website', $website, $vendor_id); ?></a><?php } ?>
                 
					
					</div>
                    <?php
                    if (apply_filters('is_vendor_add_external_url_field', true, $vendor->id)) {
                        $external_store_url = get_user_meta($vendor_id, '_vendor_external_store_url', true);
                        $external_store_label = get_user_meta($vendor_id, '_vendor_external_store_label', true);
                        if (empty($external_store_label))
                            $external_store_label = __('External Store URL', 'dc-woocommerce-multi-vendor');
                        if (isset($external_store_url) && !empty($external_store_url)) {
                            ?><p class="external_store_url"><label><a target="_blank" href="<?php echo apply_filters('vendor_shop_page_external_store', esc_url_raw($external_store_url), $vendor_id); ?>"><?php echo $external_store_label; ?></a></label></p><?php
                            }
                        }
                        ?>
                    <?php do_action('after_wcmp_vendor_information',$vendor_id);?>          
                    <?php
                        $vendor_hide_description = apply_filters('wcmp_vendor_store_header_hide_description', get_user_meta($vendor_id, '_vendor_hide_description', true), $vendor->id);
                        if (!$vendor_hide_description && !empty($description) && $template_class != 'template1') {
                    ?>
                    <div class="description_data"> 
                        <?php echo htmlspecialchars_decode( wpautop( $description ), ENT_QUOTES ); ?>
                    </div>
                    
                    <?php } ?>
                        
                            <div class="mobile_contact_detail"><button class="mobile_btn_contact" name="btn_contact_detail" id="btn_contact_detail" value="Contact Us">Contact Us<i class="fas fa-angle-down"></i></button></div>
                    <div class="wcmp_mobile_contact_info hide_detail show_desktop" id="contact_details">
                <?php if (!empty($location) && $vendor_hide_address != 'Enable') { ?><p class="wcmp_vendor_detail"><i class="wcmp-font ico-location-icon"></i><label><?php echo $location; ?></label></p><br /><?php } ?>
                <?php if (!empty($mobile) && $vendor_hide_phone != 'Enable') { ?><p class="wcmp_vendor_detail"><i class="wcmp-font ico-call-icon"></i><label><?php echo apply_filters('vendor_shop_page_contact', $mobile, $vendor_id); ?></label></p><?php } ?>
                <?php if (!empty($email) && $vendor_hide_email != 'Enable') { ?><a href="mailto:<?php echo apply_filters('vendor_shop_page_email', $email, $vendor_id); ?>" class="wcmp_vendor_detail"><i class="wcmp-font ico-mail-icon"></i><?php echo apply_filters('vendor_shop_page_email', $email, $vendor_id); ?></a><?php } ?>
                    <?php if (!empty($website)) { ?><a href="<?php echo apply_filters('vendor_shop_page_website', $website, $vendor_id); ?>" target="_blank" class="wcmp_vendor_detail"><?php echo apply_filters('vendor_shop_page_website', $website, $vendor_id); ?></a><?php } ?>
                 
					</div>
                </div>
            </div>
        </div>
        <?php
            }
        ?>
    </div>

    <?php
        if($template_class != 'template1'){
    ?>
    <div class="vendor_description">
        <div class="vendor_img_add">
            <div class="img_div"><img src=<?php echo $profile; ?> alt="<?php echo $vendor->page_title ?>"/></div>
            <div class="vendor_address">
                <p class="wcmp_vendor_name"><?php echo $vendor->page_title ?></p>
                <?php do_action('before_wcmp_vendor_information',$vendor_id);?>
                <div class="see_deals"><input type="button" name="see_our_deals" id="see_our_deals" value="See Our Deals"></div>
                    
                <div class="wcmp_vendor_rating">
                    <?php
                    if (get_wcmp_vendor_settings('is_sellerreview', 'general') == 'Enable') {
                        $queried_object = get_queried_object();
                        if (isset($queried_object->term_id) && !empty($queried_object)) {
                            $rating_val_array = wcmp_get_vendor_review_info($queried_object->term_id);
                            $WCMp->template->get_template('review/rating.php', array('rating_val_array' => $rating_val_array));
                        }
                    }
                    ?>      
                </div>  
                
                    <div class="wcmp_mobile_contact_info contact_detail">
                <?php if (!empty($location) && $vendor_hide_address != 'Enable') { ?><p class="wcmp_vendor_detail"><i class="wcmp-font ico-location-icon"></i><label><?php echo $location; ?></label></p><br /><?php } ?>
                <?php if (!empty($mobile) && $vendor_hide_phone != 'Enable') { ?><p class="wcmp_vendor_detail"><i class="wcmp-font ico-call-icon"></i><label><?php echo apply_filters('vendor_shop_page_contact', $mobile, $vendor_id); ?></label></p><?php } ?>
                <?php if (!empty($email) && $vendor_hide_email != 'Enable') { ?><a href="mailto:<?php echo apply_filters('vendor_shop_page_email', $email, $vendor_id); ?>" class="wcmp_vendor_detail"><i class="wcmp-font ico-mail-icon"></i><?php echo apply_filters('vendor_shop_page_email', $email, $vendor_id); ?></a><?php } ?>
				<?php if (!empty($website)) { ?><a href="<?php echo apply_filters('vendor_shop_page_website', $website, $vendor_id); ?>" target="_blank" class="wcmp_vendor_detail"><?php echo apply_filters('vendor_shop_page_website', $website, $vendor_id); ?></a><?php } ?>
                                      
				   </div>
                <?php
                if (apply_filters('is_vendor_add_external_url_field', true, $vendor->id)) {
                    $external_store_url = get_user_meta($vendor_id, '_vendor_external_store_url', true);
                    $external_store_label = get_user_meta($vendor_id, '_vendor_external_store_label', true);
                    if (empty($external_store_label))
                        $external_store_label = __('External Store URL', 'dc-woocommerce-multi-vendor');
                    if (isset($external_store_url) && !empty($external_store_url)) {
                        ?><p class="external_store_url"><label><a target="_blank" href="<?php echo apply_filters('vendor_shop_page_external_store', esc_url_raw($external_store_url), $vendor_id); ?>"><?php echo $external_store_label; ?></a></label></p><?php
                        }
                    }
                    ?>
                <?php do_action('after_wcmp_vendor_information',$vendor_id);?>          
                <?php
                    $vendor_hide_description = apply_filters('wcmp_vendor_store_header_hide_description', get_user_meta($vendor_id, '_vendor_hide_description', true), $vendor->id);
                    if (!$vendor_hide_description && !empty($description) && $template_class != 'template1') {
                ?>
                <div class="description_data"> 
                    <?php echo htmlspecialchars_decode( wpautop( $description ), ENT_QUOTES ); ?>
                </div>
                <?php } ?>
                <div class="mobile_contact_detail"><button class="mobile_btn_contact" name="btn_contact_detail" id="btn_contact_detail" value="Contact Us">Contact Us<i class="fas fa-angle-down"></i></button></div>
                    <div class="wcmp_mobile_contact_info hide_detail show_desktop" id="contact_details">
                <?php if (!empty($location) && $vendor_hide_address != 'Enable') { ?><p class="wcmp_vendor_detail"><i class="wcmp-font ico-location-icon"></i><label><?php echo $location; ?></label></p><br /><?php } ?>
                <?php if (!empty($mobile) && $vendor_hide_phone != 'Enable') { ?><p class="wcmp_vendor_detail"><i class="wcmp-font ico-call-icon"></i><label><?php echo apply_filters('vendor_shop_page_contact', $mobile, $vendor_id); ?></label></p><?php } ?>
                <?php if (!empty($email) && $vendor_hide_email != 'Enable') { ?><a href="mailto:<?php echo apply_filters('vendor_shop_page_email', $email, $vendor_id); ?>" class="wcmp_vendor_detail"><i class="wcmp-font ico-mail-icon"></i><?php echo apply_filters('vendor_shop_page_email', $email, $vendor_id); ?></a><?php } ?>
				<?php if (!empty($website)) { ?><a href="<?php echo apply_filters('vendor_shop_page_website', $website, $vendor_id); ?>" target="_blank" class="wcmp_vendor_detail"><?php echo apply_filters('vendor_shop_page_website', $website, $vendor_id); ?></a><?php } ?>
                 
				   </div>
            </div>
        </div>
    </div>
    <?php
        }
    ?>

    <?php
        $vendor_hide_description = apply_filters('wcmp_vendor_store_header_hide_description', get_user_meta($vendor_id, '_vendor_hide_description', true), $vendor->id);
        if (!$vendor_hide_description && !empty($description) && $template_class == 'template1') {
    ?>
    <div class="description_data"> 
        <?php echo htmlspecialchars_decode( wpautop( $description ), ENT_QUOTES ); ?>
    </div>
    <?php } ?>
</div>  
<script>
    jQuery(document).ready(function(){
       jQuery(document).on('click','#btn_contact_detail',function(){
          if (jQuery('#contact_details').hasClass('hide_detail')){
            jQuery('#contact_details').removeClass("hide_detail");
            jQuery('#contact_details').addClass("show_details");
            jQuery('#btn_contact_detail i').removeClass("fa-angle-down");
            jQuery('#btn_contact_detail i').addClass("fa-angle-up");
			jQuery(".wcmp_social_profile").show();
        }
        else{
            jQuery('#btn_contact_detail i').removeClass("fa-angle-up");
            jQuery('#btn_contact_detail i').addClass("fa-angle-down");
            jQuery('#contact_details').removeClass("show_details");
            jQuery('#contact_details').addClass("hide_detail");
			jQuery(".wcmp_social_profile").hide();
        }
       }); 
        jQuery("#see_our_deals").click(function () { 
             jQuery('html, body').animate({
        scrollTop: jQuery(".products").offset().top
    }, 2000);
       
    }); 
    });
    </script>
<style>
.ico-website-icon {
	&:before {
		content: "\e901";
	}
}
    .gp-new-icon{
        background-image: url(<?php echo WP_PLUGIN_URL.'/'.BUYLOCKSMITH_DEALS_BASE_FOLDER_NAME; ?>/dc-woocommerce-multi-vendor/images/google-business.png);
        width: 32px;
        height: 30px;
        display: inline-block;
         vertical-align: bottom;
    }    
    .wcmp_social_profile i {
        font-size: 26px !important;
    }
    
    .wcmp_vendor_banner_template{
width: 100%;
display: grid;
grid-template-columns: 50% 50%;
background: #fff;
padding: 20px;
}
.vendor_description_background.wcmp_vendor_banner_template.template2 .vendor_description {
margin-top: 0px !important;
}
.vendor_img_add .img_div {
margin-bottom: 20px;
}


@media (max-width:767px){
.wcmp_vendor_banner_template {
width: 100%;
display: block;
	padding:20px 0px;
}
.vendor_description_background.wcmp_vendor_banner_template.template2 .vendor_description {
margin-top:-50px !important;
}

}



.wcmp_vendor_banner img {
   
    object-fit: initial !important;
}
@media (min-width:767px){
.vendor_description_background .wcmp_social_profile {
    position: absolute;
    right: -65%;
    right: -60%;
    top: 67%;
    z-index: 1;
}
	.description_data {
    margin-top: 8%;
}
	.show_desktop {
    display: none;
}
	
	.mobile_contact_detail {
    
		display:none;
}
	
.see_deals {
    display: none;
}
	
}
.wcmp_social_profile{
	display:block;
}

@media (max-width:767px){
.vendor_description_background .wcmp_social_profile {
    position: absolute;
    right: 15px;
    bottom: 15px;
    z-index: 1;
    bottom: 0px !important;
   margin: 6px auto;
    right: 0% !important;
    left: 0;
    text-align: center;
}
.wcmp_social_profile{
	display:none;
}
.wcmp_vendor_banner {
    width: 100%;
    background-size: cover;
    background-repeat: no-repeat;
    position: unset;
}
	
		.mobile_contact_detail {
    margin-top: 0%;
		display:block;
}
	.wcmp_mobile_contact_info.contact_detail {
    display: none;
}


	
}

@media (max-width: 460px){
/*.vendor_description_background .wcmp_social_profile {
    position: absolute;
    right: 15px;
    bottom: 15px;
    z-index: 1;
    bottom: 0px !important;
  
	margin: 6px auto;
    right: 40% !important;
}*/
.wcmp_vendor_banner {
    width: 100%;
    background-size: cover;
    background-repeat: no-repeat;
    position: unset;
	    text-align: center;
}

}
@media (max-width: 388px){
/*.vendor_description_background .wcmp_social_profile {
    position: absolute;
    right: 15px;
    bottom: 15px;
    z-index: 1;
    bottom: 0px !important;
   margin: 6px auto;
    right: 35% !important;
}*/
.wcmp_vendor_banner {
    width: 100%;
    background-size: cover;
    background-repeat: no-repeat;
    position: unset;
}
}

.hide_detail {
    display: none;
}

input#see_our_deals {
    background: #13c313;
    width: 80%;
    font-weight: 700;
    font-size: 15px;
}





button#btn_contact_detail .fas {
    float: right;
    color: #827e7e;
    font-size: 18px;
}
button#btn_contact_detail {
    background: #efefef;
    border: none;
    border: 1px solid #d2d2d2;
    width: 80%;
    font-size: 14px;
    text-transform: uppercase;
    padding: 3px 5px;
}
/*button#btn_contact_detail {
    background: none;
    border: none;
    border: 1px solid #9f9898;
    width: 200px;
    font-size: 14px;
    text-transform: uppercase;
    padding: 3px 5px;
}*/
.wcmp_vendor_banner img {
    
    max-height: 304px !important;
    width: 540px !important;
}/*
@media(max-width:1024px){
	.wcmp_vendor_banner img {
    min-height: 350px !important;
    max-height: 350px !important;
    width: 100% !important;
	    padding: 0 20px;
}
}*/



@media(min-width:1200px){
	.vendor_description_background.wcmp_vendor_banner_template.template2 .vendor_description {
    max-width: 540px;
}
.vendor_description_background .wcmp_social_profile {
    position: absolute;
    right: -65%;
    right: -540px !important;
    left: 0% !important;
    text-align: center;
    top: 70%;
    z-index: 1;
    max-width: 541px;
    margin-left: auto;
}
}

@media (min-width:768px) and (max-width:1024px){ 
	.vendor_description_background .wcmp_social_profile {	 
		position: absolute;
		right: -65%;
		right: -453px !important;
		left: 0% !important;
		text-align: center;
		top: 78%;
		z-index: 1;
		max-width: 541px;
		margin-left: auto;
	}
}


</style>