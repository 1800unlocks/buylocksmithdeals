<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

get_header(); 
global $WCMp,$wpdb;
if(isset($_REQUEST['code'])){
$key='buylocksmithdeals';
$code= BuyLockSmithDealsCustomizationAddon::code_decrypt($_REQUEST['code'], $key);
$user_order=explode('&',$code);
$main_order_id=$user_order[0];
$sub_order_id=$user_order[1];
$vendor_id=$user_order[2];
$suborder_authorname=get_author_name($vendor_id);
$customer_id=get_post_meta($sub_order_id,'_customer_user',true);
$current_user = get_user_by('ID', $customer_id);
$comments = get_comments (array ('meta_key'=> '_rating_on_order', 'meta_value'=> $sub_order_id));    
$given_rating=0;
$suborder = wc_get_order( $sub_order_id );
if(!empty($comments)){
    foreach($comments as $comment){
        $comment_id=$comment->comment_ID;
        $vendorid = get_comment_meta($comment_id, 'vendor_rating_id', true);
        if($vendorid ==$vendor_id){
           $given_rating=1; 
        }
    }
}

?>

<div id="main" class="site-main">
    <div class="container active-sidebar right">
        <div class="page-header">
            <h1 class="page-title">Review Ratings</h1>
        </div>
        <div class="confirmation-box">
            
            
            <?php
            if(empty($suborder)){
                echo '<div class="error-msg"><span>You are not authorised to give rating on this order.</span></div>';
            }
            else{
            if($given_rating){
                 echo '<div class="error-msg"><span>You have already given rating to this vendor</span></div>';
            }
            else{ ?>
            <div class="wocommerce" >
                <div id="reviews" >
                    <div id="wcmp_vendor_reviews">
                        <div id="review_form_wrapper">
                            <div id="review_form">
                                <div id="respond" class="comment-respond">
                            
                                <h3 id="reply-title" class="comment-reply-title"><?php
                                  
                                        echo sprintf(__('Add a review to %s', 'dc-woocommerce-multi-vendor'), $suborder_authorname);
                                    
                                    ?> </h3>				
                                <form action="" method="post" id="commentform" class="comment-form" novalidate="">
                                    <p id="wcmp_seller_review_rating"></p>
                                    <p class="comment-form-rating"><label for="rating"><?php echo __('Your Rating', 'dc-woocommerce-multi-vendor'); ?></label>					
                                     <p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p>   
                                        
                                        <select name="rating" id="rating">
                                            <option value=""><?php echo __('Rate...', 'dc-woocommerce-multi-vendor'); ?></option>
                                            <option value="5"><?php echo __('Perfect', 'dc-woocommerce-multi-vendor'); ?></option>
                                            <option value="4"><?php echo __('Good', 'dc-woocommerce-multi-vendor'); ?></option>
                                            <option value="3"><?php echo __('Average', 'dc-woocommerce-multi-vendor'); ?></option>
                                            <option value="2"><?php echo __('Not that bad', 'dc-woocommerce-multi-vendor'); ?></option>
                                            <option value="1"><?php echo __('Very Poor', 'dc-woocommerce-multi-vendor'); ?></option>
                                        </select></p>
                                    <p class="form-submit">
                                        <input id="wcmp_vendor_for_rating" name="wcmp_vendor_for_rating" type="hidden" value="<?php echo $vendor_id; ?>"  >
                                        <input id="author" name="author" type="hidden" value="<?php echo $current_user->display_name; ?>" size="30" aria-required="true">					 
                                        <input id="email" name="email" type="hidden" value="<?php echo $current_user->user_email; ?>" size="30" aria-required="true">
                                        <input name="submit" type="button" id="submit" class="submit" value="<?php _e('Submit', 'dc-woocommerce-multi-vendor') ?>">

                                    </p>				
                                </form>
                                
                        </div><!-- #respond -->
                    </div>
</div>
                        </div>
                    </div>
                </div>
            <?php } } ?>
            </div>
            
</div>
</div>

<style>
   .error-msg {
        width: 100%;
        background-color: red;
        color: #fff;
        text-align: center;
        height: 25px;
        font-size: 20px;
    }
.confirmation-box {
    box-shadow: 0 2px 3px -1px rgba(186, 188, 190, 0.7);
    background-color: #ffffff;
    padding: 60px 30px;
    margin-bottom: 60px;
}
.comment-form-rating,
.comment-form-comment {
  width: 100%;
}
.stars span a {
  display: inline-block;
  font-weight: 700;
  margin-right: 1em;
  text-indent: -9999px;
  position: relative;
  border-bottom: 0 !important;
  outline: 0;
  border: none;
  line-height: 14px;
  font-family: star;
  color: rgba(0, 0, 0, 0.2);
}
.stars span a.star-1::after,
.stars span a.star-2::after,
.stars span a.star-3::after,
.stars span a.star-4::after,
.stars span a.star-5::after {
  text-indent: 0;
  position: absolute;
  top: 0;
  left: 0;
}
.stars span a.star-1.active::after,
.stars span a.star-2.active::after,
.stars span a.star-3.active::after,
.stars span a.star-4.active::after,
.stars span a.star-5.active::after {
  color: #F4D819;
}
.stars span a.star-1 {
  width: 2em;
}
.stars span a.star-1::after {
  content: "\73";
}
.stars span a.star-2 {
  width: 3em;
}
.stars span a.star-2::after {
  content: "\73\73";
}
.stars span a.star-3 {
  width: 4em;
}
.stars span a.star-3::after {
  content: "\73\73\73";
}
.stars span a.star-4 {
  width: 5em;
}
.stars span a.star-4::after {
  content: "\73\73\73\73";
}
.stars span a.star-5 {
  width: 6em;
}
.stars span a.star-5::after {
  content: "\73\73\73\73\73";
}

</style>
<script>
    /* global wcmp_seller_review_rating_js_script_data */

jQuery(document).ready(function () {
    jQuery('#wcmp_vendor_reviews #respond #rating').hide();
    jQuery('body')
            .on('click', '.stars a', function () {
                var $star = jQuery(this),
                        $rating = jQuery(this).closest('#respond').find('#rating'),
                        $container = jQuery(this).closest('.stars');
                $rating.val($star.text());
                $star.siblings('a').removeClass('active');
                $star.addClass('active');
                $container.addClass('selected');
                return false;
            })
            .on('click', '#wcmp_vendor_reviews #respond #submit', function () {
                var $rating = jQuery(this).closest('#respond').find('#rating'),
                rating = $rating.val();
                if ($rating.size() > 0 && !rating) {
                    window.alert(wcmp_seller_review_rating_js_script_data.messages.rating_error_msg_txt);
                    return false;
                }
                var comment='';
                var vendor_id = jQuery('#wcmp_vendor_reviews #respond #wcmp_vendor_for_rating').val();
                var data = {
                    rating: rating,
                    comment: comment,
                    vendor_id: jQuery('#wcmp_vendor_for_rating').val(),
                    customer_id: '<?php echo $customer_id; ?>',
                    order_id: '<?php echo $sub_order_id; ?>',
                }
                
                jQuery.post('<?php echo add_query_arg( 'action', 'blsd_update_vendor_rating', $WCMp->ajax_url() ); ?>', data, function (response) {
                    if (response == 1) {
                        
                        $rating.val('');
                        //jQuery('#wcmp_vendor_reviews #respond #comment').val('');
                        jQuery(".stars").removeClass('selected');
                        setTimeout(location.reload(), 2000);
                    } else {
                       alert('fail');
                    }
                });

            });
});
   
</script>
<?php } ?>
<?php

get_footer();