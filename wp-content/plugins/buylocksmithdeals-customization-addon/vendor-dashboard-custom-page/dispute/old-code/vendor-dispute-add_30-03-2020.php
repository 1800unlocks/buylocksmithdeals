<div class="wcmp_form1">
	<div class="col-md-12 add-product-wrapper">
        <!-- Top product highlight -->
                <!-- End of Top product highlight -->
                  <div class="product-primary-info custom-panel"> 
            
            <div class="panel panel-default panel-pading">
<?php
$message = '';
global $wpdb;
$current_user = get_current_user_id();
$order_id = $_REQUEST['add'];

if (isset($_REQUEST['add_dispute_submit'])) {
     $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_table_name();
 $query = "SELECT * FROM $table_name WHERE order_id = $order_id";
                    $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
                    
if(count($results)<1){
    $title = $_REQUEST['title'];
    $message = $_REQUEST['message'];
    

    if ($title != '' && $message != '') {

        $order = new WC_Order($order_id);
        $user_id = $order->get_user_id(); // Get the costumer ID
 BuyLockSmithDealsCustomizationEmail::blsd_email_on_submit_dispute($order, $user_id, $title, $_REQUEST['message']);

        $data = [
            'user_id' => $current_user,
            'who_opose_user_id' => $user_id,
            'role' => 'vendor',
            'order_id' => $order_id,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_table_name();
        $wpdb->insert($table_name, $data);
        $lastid_dispute_id = $wpdb->insert_id;




        $data = [
            'dispute_id' => $lastid_dispute_id,
            'title' => $title,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_message_table_name();
        $wpdb->insert($table_name, $data);
        $lastid_dispute_message_id = $wpdb->insert_id;

        if (isset($_FILES['attachment'])) {
            $attachments = $_FILES['attachment'];
            //   echo BUYLOCKSMITH_DEALS_PLUGIN_DIR; exit;
            if (count($attachments > 0)) {

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
        }
       
        $message = 'Dispute Submited successfully! <a class="strong_link" href="' . home_url() . '/dashboard/vendor-dispute-list/">Go to List</a>';
    }
}else{
     $message = 'Dispute already submited on Order! <a class="strong_link" href="' . home_url() . '/dashboard/vendor-dispute-list/">Go to List</a>';
}
}
if ($message != '') {
    ?>
    <div class="success-message btn-success "><?php echo $message; ?></div>
    <?php
    $message = '';
    unset($_REQUEST);
} else {
    ?>

    <form method="post" id="add_dispute" enctype="multipart/form-data">
        <div class="form-group-row pricing"> 

            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="title"><?php echo __('Title', 'woocommerce'); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="text" id="title" name="title" value="" class="form-control">
                </div>
            </div>  
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="message"><?php echo __('Message', 'woocommerce'); ?></label>
                <div class="col-md-6 col-sm-9">

                    <textarea id="message" name="message" class="form-control"></textarea>

                </div>
            </div> 
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
                    <input type="submit" id="_sale_price" name="add_dispute_submit" value="Update" class="form-control btn btn-primary">

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

                jQuery(this).before('<div class="input_file_area_main"><div class="remove_btn"><i class="wcmp-font ico-delete-icon action-icon remove_action_icon"></i></div><input onchange="ValidateSize(this)"  type="file" id="attachment" name="attachment[]" value="" class="form-control"></div>');
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