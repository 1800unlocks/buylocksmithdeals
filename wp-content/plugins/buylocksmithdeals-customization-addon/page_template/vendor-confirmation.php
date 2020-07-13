<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

get_header(); 
global $WCMp;
$redirect_url = site_url().'/thank-you-job-complete';
?>
<div id="main" class="site-main">

<div class="container active-sidebar right">
    <div class="page-header">
        <h1 class="page-title">Code Confirmation</h1>
    </div>
    <div class="confirmation-box">
    <?php 

     $key='buylocksmithdeals';
     $code= BuyLockSmithDealsCustomizationAddon::code_decrypt_static($_REQUEST['code'], $key);
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
                    <input type="hidden"  name="confirmation_code" id="confirmation_code" value="<?php echo $order_code; ?>">
                    <span class="confirmation_code_span"><?php echo $order_code; ?></span><button type="button" id="code_confirmation_btn" class="btn btn-primary">Submit</button>
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
    .image_loader_code.show-loader {
        text-align: center;
        margin: 0 auto;
        width: 49px;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
		display:none;
    }
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
        width: auto;
        height: auto;
        font-size: 20px;
        border: none;
        border-radius: 5px;
        padding: 10px 15px;
    }
    #code_confirmation_btn{
            background-color: #52cf52;
            color: #fff;
            width: 263px;
            font-size: 22px;
            width: auto;
            display: inline-block;
            padding: 11px 34px;
            border: none;
            border-radius: 3px;
    }
    .btn-box {
            width: 50%;
            /* margin-left: 370px; */
            margin: 0 auto;
            text-align: center;
            position: relative;
            
        }
        .add_extra{
            display:block !Important;
        }
    .error-msg {
        width: 80%;
        background-color: red;
        color: #fff;
        text-align: center;
        /*height: 35px;*/
        font-size: 20px;
        margin: 0 auto;
        padding: 10px 0;
    }
    .image_loader_code {
        width: 20%;
        display: none;
        vertical-align: top;
    }
    span.confirmation_code_span {
        padding: 12px 15px;
        font-size: 20px;
        color: #000;
        background: #f2f5f7;
        margin-right: 1%;
        border-radius: 3px;
        font-weight: 500;
    }
@media(max-width:620px){
.image_loader_code {
    width: 20%;
    display: inline-block;
    margin-top: 3%;
}

.btn-box {
    width: 100%;
}
#code_confirmation_btn_completed {
    background-color: red;
    color: #fff;
    width: auto;
    height: auto;
    font-size: 20px;
    border: none;
    border-radius: 5px;
    padding: 10px 15px;
}
#code_confirmation_btn{
    margin-top: 5%;
}
  .add_extra{
            display:block !Important;
            margin-bottom:6% !important;
        }
}


</style>
<script>
    jQuery(document).ready(function(){
       jQuery('#code_confirmation_btn').click(function(){
          var code=jQuery('#confirmation_code').val();
          jQuery('#code_confirmation_btn').attr('disabled',true);
        var order_id='<?php echo $order_id; ?>';
        if(code != ''){
             jQuery('.image_loader_code').show();
            jQuery.ajax({
                 url: '<?php echo add_query_arg( 'action', 'blsd_update_vendor_order_status', $WCMp->ajax_url() ); ?>',
                 type: "post",
                 data: {code:code,order_id:order_id},
                 success: function(resultData) {
                     if(resultData == 'success'){
                        // window.location.href=window.location.href+'&success=true';
                        window.location.href='<?php echo $redirect_url; ?>';
                     }
                     else{
                         jQuery('.image_loader_code').hide();
                         jQuery('#error_message').show();
                         jQuery( "#error_message" ).addClass( "add_extra" );
                          jQuery('#error_message').text('Please enter valid code');
                         setTimeout(function() {
                           jQuery('#error_message').fadeOut('fast');
                           jQuery( "#error_message" ).removeClass( "add_extra" );
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