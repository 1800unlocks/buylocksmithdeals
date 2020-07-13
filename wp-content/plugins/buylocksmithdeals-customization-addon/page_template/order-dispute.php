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
$order_id=$user_order[1];
$current_user=get_post_meta($order_id,'_customer_user',true);

if (isset($_REQUEST['add_dispute_submit'])) {
     $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_table_name();
 $query = "SELECT * FROM $table_name WHERE order_id = $order_id";
 
                    $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
                    
if(count($results)<1){
    $title = $_REQUEST['title'];
    $dispute_message = $_REQUEST['message'];
    

    if ($title != '' && $dispute_message != '') {

        $order = new WC_Order($order_id);
        $user_id = $order->get_user_id(); // Get the costumer ID
        $post_order = get_post($order_id);
        if(isset($post_order->post_author)){
            $vendor_id = $post_order->post_author;
        }

        $data = [
            'user_id' => $current_user,
            'who_opose_user_id' => $vendor_id,
            'role' => 'customer',
            'order_id' => $order_id,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_table_name();
        $wpdb->insert($table_name, $data);
        $lastid_dispute_id = $wpdb->insert_id;

		if($dispute_message == 'Something Else'){
			$dispute_message = $_REQUEST['something_else_message'];
		}
		$username = $_REQUEST['username'];
		$phone_number = $_REQUEST['phone_number'];
		$email = $_REQUEST['email'];
		$order_number = $_REQUEST['order_number'];
        $data = [
            'dispute_id' => $lastid_dispute_id,
            'username' => $username,
            'phone_number' => $phone_number,
            'email' => $email,
            'order_number' => $order_number,
            'title' => $title,
            'message' => $dispute_message,
            'sender_id' => $current_user,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_message_table_name();
        $wpdb->insert($table_name, $data);
        $lastid_dispute_message_id = $wpdb->insert_id;
        if (isset($_FILES['attachment'])) {
            
            $attachments = $_FILES['attachment'];
            
                foreach ($attachments['name'] as $key => $attachment) {

                    $target_dir = BUYLOCKSMITH_DEALS_PLUGIN_DIR . "/uploads/";
                    $fileName = time() . '_' . $current_user . '_' . basename($attachments["name"][$key]);
                    $target_file = $target_dir . $fileName;

                    if (move_uploaded_file($attachments["tmp_name"][$key], $target_file)) {

                        $data = [
                            'dispute_id' => $lastid_dispute_id,
                            'dispute_message_id' => $lastid_dispute_message_id,
                            'file_name' => $fileName,
                            'uploaded_at' => date('Y-m-d H:i:s')
                        ];
                        $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_attachment_table_name();
                        $wpdb->insert($table_name, $data);
                    }
                }
            
        }
        $message = 'Dispute Submited successfully!';
		BuyLockSmithDealsCustomizationEmail::blsd_email_on_submit_dispute($order, $vendor_id, $title, $dispute_message, $username, $phone_number, $email, $order_number );
    }
}else{
     $message = 'Dispute already submited on Order!';
}
}
?>

<div id="main" class="site-main">
    <div class="container active-sidebar right">
        <div class="page-header">
            <h1 class="page-title">Order Dispute</h1>
        </div>
        <div class="confirmation-box">
            <div class="wcmp_form1">
                <div class="col-md-12 add-product-wrapper">
                    <!-- Top product highlight -->
                    <!-- End of Top product highlight -->
                    <div class="product-primary-info custom-panel"> 
                        <div class="panel panel-default panel-pading">
            <?php 
             
                if ($message != '') {
                        ?>
                        <div class="success-message btn-success "><?php echo $message; ?></div>
                        <?php
                        $message = '';
                        unset($_REQUEST);
                    }else{
                        $order = wc_get_order( $order_id );
                        $post_order = get_post($order_id);
                        if(isset($post_order->post_author)){
                            $vendor = $post_order->post_author;
                        }
                        $items = $order->get_items(); 
                        foreach ( $items as $order_item_id => $item ) {
                            $order_item_id_to_get_sold_by = $order_item_id;
                           //    $product_id = $item['product_id'];

                          }
                          $table_name_sold_by = $wpdb->prefix.'woocommerce_order_itemmeta';
                          $query_soldBy = "SELECT meta_value FROM $table_name_sold_by WHERE order_item_id = $order_item_id_to_get_sold_by and meta_key='Sold By'";
                                      $results_sold_by = (array) $wpdb->get_results($wpdb->prepare($query_soldBy, $type), ARRAY_A);
                                      $saller_name = '';
                        if($results_sold_by[0]['meta_value']!=''){
                      $saller_name = $results_sold_by[0]['meta_value']; 
                        }else{
                          $saller_name =   BuyLockSmithDealsCustomizationAddon::blsd_get_userFullName($vendor);
                        }
                ?>
               
                <form method="post" id="add_dispute" enctype="multipart/form-data">
                    <div class="area_dispute_by_sub_order">
                    <div class="text_area_dispute">
                    Dispute on Sold by: <?php echo $saller_name;?>
                    </div>
                </div>
                    <div class="form-group-row pricing"> 
						<div class="form-group">
                            <label class="control-label col-sm-3 col-md-3" for="username"><?php echo __('Name', 'woocommerce'); ?></label>
                            <div class="col-md-6 col-sm-9">
                                <input type="text" id="username" name="username" value="" class="form-control">
                            </div>
                        </div>
						<div class="form-group">
                            <label class="control-label col-sm-3 col-md-3" for="phone_number"><?php echo __('Phone Number', 'woocommerce'); ?></label>
                            <div class="col-md-6 col-sm-9">
                                <input type="text" id="phone_number" name="phone_number" value="" class="form-control">
                            </div>
                        </div>
						<div class="form-group">
                            <label class="control-label col-sm-3 col-md-3" for="email"><?php echo __('Email', 'woocommerce'); ?></label>
                            <div class="col-md-6 col-sm-9">
                                <input type="text" id="email" name="email" value="" class="form-control">
                            </div>
                        </div>
						<div class="form-group">
                            <label class="control-label col-sm-3 col-md-3" for="order_number"><?php echo __('Order Number', 'woocommerce'); ?></label>
                            <div class="col-md-6 col-sm-9">
                                <input type="text" id="order_number" name="order_number" value="<?php echo $order_id; ?>" class="form-control">
                            </div>
                        </div> 
                        <div class="form-group">
                            <label class="control-label col-sm-3 col-md-3" for="title"><?php echo __('Title', 'woocommerce'); ?></label>
                            <div class="col-md-6 col-sm-9">
                                <input type="text" id="title" name="title" value="" class="form-control">
                            </div>
                        </div>  
						<div class="form-group">
                            <label class="control-label col-sm-3 col-md-3" for="message"><?php echo __('Reason For Dispute', 'woocommerce'); ?></label>
                            <div class="col-md-6 col-sm-9">
								<select name="message" id="message" class="form-control">
									<option value="Pricing">Pricing</option>
									<option value="Customer Service">Customer Service</option>
									<option value="Product malfunction">Product malfunction</option>
									<option value="Property damage">Property damage</option>
									<option value="Something Else">Something Else</option>
								</select>
                            </div>
                        </div> 
						<div class="form-group disabled" id="something_else">
                            <label class="control-label col-sm-3 col-md-3" for="something_else_message"><?php echo __('Message', 'woocommerce'); ?></label>
                            <div class="col-md-6 col-sm-9">
								<textarea id="something_else_message" name="something_else_message" class="form-control"></textarea>
							</div>
                        </div> 
                        <!--<div class="form-group">
                            <label class="control-label col-sm-3 col-md-3" for="message"><?php // echo __('Message', 'woocommerce'); ?></label>
                            <div class="col-md-6 col-sm-9">

                                <textarea id="message" name="message" class="form-control"></textarea>

                            </div>
                        </div> -->
                        <div class="form-group">

                            <div class="col-md-12 col-sm-12">
                                <div class="btn-danger error_message_area"></div>

                            </div>
                        </div> 

                        <div class="form-group">
                            <label class="control-label col-sm-3 col-md-3" for="message"><?php echo __('Attachment', 'woocommerce'); ?></label>
                            <div class="col-md-6 col-sm-9">
                                <input onchange="ValidateSize(this)"  type="file" id="attachment" name="attachment[]" value="" class="form-control">

                                <div><button type="button" class="btn btn-primary margin-10 add_more">Add More +</button></div>

                            </div>
                        </div> 
                        <div class="form-group">

                            <div class="col-md-2 col-sm-3">
                                <input type="submit" id="_sale_price" name="add_dispute_submit" value="Update" class="btn btn-primary">

                            </div>
                        </div> 

                    </div>
                </form>
            
            
                <script>
                    jQuery(document).ready(function () {

                        jQuery('#add_dispute').on('submit', function () {
                            jQuery('.error_message_area').html('');

                            if (jQuery('#title').val() == '' || jQuery('#message').val() == '') {
                                if (jQuery('#title').val() == '') {
                                    jQuery('.error_message_area').html('Please provide title of dispute.');
                                    return false;

                                }
                                if (jQuery('#message').val() == '') {
                                    jQuery('.error_message_area').html('Please provide message of dispute.');
                                    return false;
                                }


                            }
                        });


                        jQuery('.add_more').click(function () {

                            jQuery(this).before('<div class="input_file_area_main"><div class="remove_btn"><i class="fa fa-trash action-icon remove_action_icon"></i></div><input onchange="ValidateSize(this)"  type="file" id="attachment" name="attachment[]" value="" class="form-control"></div>');
                        });
                        var selectedInputItemValue = '';
                        var confirm_action = false;
                        jQuery(document).on('click', '.remove_btn', function () {
                            selectedInputItemValue = '';

                            selectedInputItemValue = jQuery(this).next('input').val();
                            if (selectedInputItemValue == '') {
                                confirm_action = true;
                            } else {
                                confirm_action = confirm('Are you sure to remove attachment?');
                            }
                            if (confirm_action) {
                                jQuery(this).parent().remove();
                            }
                        });
						jQuery(document).on('change', '#message', function () {
							var message=jQuery('#message').val();
							if(message == 'Something Else'){
								jQuery('#something_else_message').val('');
								jQuery('#something_else').removeClass('disabled');
							}
							else{
								jQuery('#something_else_message').val('');
								jQuery('#something_else').addClass('disabled');
							}
						});

                    });

                    function ValidateSize(file) {
                        var FileSize = file.files[0].size / 1024 / 1024; // in MB
                        if (FileSize > 2) {
                            alert('File size more then 2 MB not allowed to as attachment.');
                            jQuery(file).val('');
                        }
                    }

                </script>
              <?php  
                    }
             
             ?>
        </div>
    </div>
</div>
</div>
</div>
</div>
</div>
<?php } ?>
<style>
.disabled{
	display:none;
}
   form#add_dispute {
    width: 50%;
    margin: 0 auto;
    background: #f2f5f7;
    padding: 47px;
}
    .confirmation-box {
        box-shadow: 0 2px 3px -1px rgba(186, 188, 190, 0.7);
        background-color: #ffffff;
        padding: 60px 30px;
        margin-bottom: 60px;
    }
    .control-label{
    display: inline-block;
    max-width: 100%;
    margin-bottom: 5px;
    font-weight: 400;
    color: #000000;
}    
.form-control{
display: block;
    padding: 5px 10px;
    width: 98%;
    height: 43px;
    border: 1px solid #e8eaed;
    border-radius: 0;
    background-color: #ffffff;
    background-image: none;
    box-shadow: none;
    color: #000000;
    font-size: 14px;
    line-height: 1.42857143;
    font-family: 'Roboto', sans-serif;
    font-weight: 300;
    box-sizing: border-box;
}
.btn-primary:hover, #_sale_price:hover {
    margin-bottom: 10px;
    color: white;
    background: #eb3515;
    border: 1px solid #eb3515;
    height: 30px;
    box-shadow: 0px 2px 7px 1px #888888;
    border-radius: 3px;
    background-color: #bfc3c6;
}
.add_more {
    margin-top: 10px;
}

.btn-primary {
    margin-bottom: 10px;
    color: white;
    background: #d9452b;
    border: 1px solid #d9452b;
    height: 30px;
    box-shadow: 0px 2px 7px #ddd;
    border-radius: 3px;
    cursor: pointer;
}
#_sale_price {
    margin-top: 15px;
}
#_sale_price {
    margin-bottom: 10px;
    color: white;
    background: #77c84e;
    border: 1px solid #77c84e;
    height: 30px;
    box-shadow: 0px 2px 7px #ddd;
    border-radius: 3px;
}
.remove_btn {
    float: right !important;
    position: relative !important;
    top: 10px !important;
    color: #d9452b !important;
    cursor: pointer;
}
.input_file_area_main {
    margin-top: 10px;
}
.text_area_dispute {
    display: inline-block;
    margin-right: 10px;
    font-weight: 500;
    color: #000;
}
.area_dispute_by_sub_order {
    text-align: center;
}
.success-message.btn-success {
    text-align: center;
}
@media(max-width:767px){
    form#add_dispute {
    width: 100%;
    margin: 0 auto;
    background: unset;
    padding: 0;
}
}

</style>


<?php
get_footer();