<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

get_header(); 
global $WCMp;
?>
<div id="main" class="site-main">

<div class="container active-sidebar right">
    <div class="page-header">
        <h1 class="page-title">Code Confirmation</h1>
    </div>
    <div class="confirmation-box">
    <?php 
    $key='buylocksmithdeals';
      $code= BuyLockSmithDealsCustomizationAddon::code_decrypt($_REQUEST['code'], $key);
     $user_order=explode('&',$code);
     $user_id=$user_order[0];
      $order_id=$user_order[1];
     $vendor = get_wcmp_vendor($user_id);
     if(!empty($vendor)){
         $order = wc_get_order( $order_id );
         if(!empty($order)){
             $order_status  = $order->get_status(); 
             if($order_status == 'completed'){ ?>
                <div class="btn-box">
                <button type="button" id="code_confirmation_btn_completed" class="btn btn-primary">Order Completed</button>
                </div>
            <?php   
             }
             else{
                 $order_code=get_post_meta($order_id,'unique_token',true); 
                 ?>
                <div class="btn-box">
                    <span id="error_message" style="display:none;"></span>
                    <input type="hidden" name="confirmation_code" id="confirmation_code" value="<?php echo $order_code; ?>">
                    <button type="button" id="code_confirmation_btn" class="btn btn-primary"><?php echo $order_code; ?></button>
                    <div class="image_loader_code show-loader">
                        <img src="<?php echo BUYLOCKSMITH_DEALS_ASSETS_PATH;?>/img/loader.gif">
                    </div>
                </div>
            <?php
             }
         }
         else{
             
            echo '<div class="error-msg"><span>You are not authorised to access the order to complete.</span></div>'; 
         }
     }
     else{
         echo '<div class="error-msg"><span>You are not authorised to access this page.</span></div>';
     }
     
    ?>
    </div>
</div>
</div>
<style>
    #error_message{
        color:red;
    }
    .confirmation-box {
        box-shadow: 0 2px 3px -1px rgba(186, 188, 190, 0.7);
        background-color: #ffffff;
        padding: 60px 30px;
        margin-bottom: 60px;
    }
    #code_confirmation_btn_completed {
        background-color: red;
        color: #fff;
        width: 250px;
        height: 50px;
        font-size: 25px;
    }
    #code_confirmation_btn{
        background-color: #52cf52;
        color: #fff;
        width: 263px;
        height: 55px;
        font-size: 40px;
    }
    .btn-box {
        width: 26%;
        margin-left: 370px;
    }
    .error-msg {
        width: 80%;
        background-color: red;
        color: #fff;
        text-align: center;
        height: 35px;
        font-size: 20px;
    }
    .image_loader_code {
        width: 20%;
        display: none;
        vertical-align: top;
    }

@media(nax-width:420px){
.image_loader_code {
    width: 20%;
    display: inline-block;
    margin-top: 3%;
}
.show-loader{
display:inline !important;
}

}

</style>
<script>
    jQuery(document).ready(function(){
       jQuery('#code_confirmation_btn').click(function(){
          var code=jQuery('#confirmation_code').val();
        var order_id='<?php echo $order_id; ?>';
        if(code != ''){
             jQuery('.image_loader_code').show();
            jQuery.ajax({
                 url: '<?php echo add_query_arg( 'action', 'blsd_update_vendor_order_status', $WCMp->ajax_url() ); ?>',
                 type: "post",
                 data: {code:code,order_id:order_id},
                 success: function(resultData) {
                     if(resultData == 'success'){
                        window.location.href=window.location.href;
                     }
                     else{
                         jQuery('.image_loader_code').hide();
                         jQuery('#error_message').show();
                          jQuery('#error_message').text('Please enter valid code');
                         setTimeout(function() {
                           jQuery('#error_message').fadeOut('fast');
                        }, 3000);
                    }
                 }
            });
        }
       }); 
    });
    </script>

<?php
get_footer();