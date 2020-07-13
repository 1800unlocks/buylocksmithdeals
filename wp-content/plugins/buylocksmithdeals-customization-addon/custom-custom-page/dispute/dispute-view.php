<div class="wcmp_form1">
	<div class="col-md-12 add-product-wrapper">
        <!-- Top product highlight -->
                <!-- End of Top product highlight -->
                  <div class="product-primary-info custom-panel"> 

                      
                       


            <?php
            $message = '';
            global $wpdb;
            $vendor_id = $current_user = get_current_user_id();
            $id = $_REQUEST['view'];

            
//             $order = new WC_Order($order_id);
//        $user_id = $order->get_user_id(); // Get the costumer ID
//   $post_order = get_post($order_id);
//                   if(isset($post_order->post_author)){
//                       $vendor_id = $post_order->post_author;
//                   }




            $table_name_status = BuyLockSmithDealsCustomizationAddon::blsd_status_table_name();
            $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_table_name();
            $table_name_message_table = BuyLockSmithDealsCustomizationAddon::blsd_dispute_message_table_name();
            $table_name_attachment_table = BuyLockSmithDealsCustomizationAddon::blsd_dispute_attachment_table_name();


            $query = "SELECT $table_name.*,$table_name_message_table.title,$table_name_status.name as status_name, $table_name_message_table.message as message "
                     . " ,(select GROUP_CONCAT($table_name_attachment_table.file_name) AS attachment from $table_name_attachment_table where $table_name_attachment_table.dispute_message_id=$table_name_message_table.id) as attachment"
                    . " FROM $table_name"
                    . " inner join $table_name_message_table on $table_name_message_table.dispute_id=$table_name.id "
                    . " inner join $table_name_status on $table_name_status.id=$table_name.status "
                    . " left join $table_name_attachment_table on $table_name_attachment_table.dispute_message_id=$table_name_message_table.id "
                    . " WHERE (user_id=$current_user or who_opose_user_id=$current_user) and $table_name.id = $id";
            $results_dispute_data = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
           

$who_won_user_id = 0;
$who_opose_user_id = 0;

            
            $order_id = '';
            if (count($results_dispute_data)) {
                $order_id = $results_dispute_data[0]['order_id'];
                $who_won_user_id = $results_dispute_data[0]['who_won_user_id'];
                if($current_user == $results_dispute_data[0]['who_opose_user_id']){
                    $who_opose_user_id = $results_dispute_data[0]['user_id'];
                }
                else if($current_user == $results_dispute_data[0]['user_id']){
                    $who_opose_user_id = $results_dispute_data[0]['who_opose_user_id'];
                }
            }


            if (isset($_REQUEST['add_dispute_submit'])) {

                $query = "SELECT * FROM $table_name WHERE order_id = $order_id";
                $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);

              
                    
                    $message = $_REQUEST['message'];
                    

                    if ($message != '') {

                        $order = new WC_Order($order_id);
                        $user_id = $order->get_user_id(); // Get the costumer ID

                        $lastid_dispute_id = $id;

                        $data = [
                            'dispute_id' => $lastid_dispute_id,
                            'message' => $message,
                            'sender_id'=>$current_user,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_message_table_name();
                        $wpdb->insert($table_name, $data);
                        
                        BuyLockSmithDealsCustomizationEmail::blsd_email_on_new_message_dispute($order, $who_opose_user_id, $_REQUEST['message'], $lastid_dispute_id);
                        
                        $lastid_dispute_message_id = $wpdb->insert_id;

                        if (isset($_FILES['attachment'])) {
                            $attachments = $_FILES['attachment'];
                            //   echo BUYLOCKSMITH_DEALS_PLUGIN_DIR; exit;
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
                        $message = 'Dispute Submited successfully! <a class="strong_link" href="' . home_url() . '/dashboard/vendor-dispute-list/">Go to List</a>';
                    }
               
            }
            
                 if (count($results_dispute_data)) {
                  $query = "SELECT $table_name_message_table.* "
                          . " ,(select GROUP_CONCAT($table_name_attachment_table.file_name) AS attachment from $table_name_attachment_table where $table_name_attachment_table.dispute_message_id=$table_name_message_table.id) as attachment"
                    //. " ,GROUP_CONCAT($table_name_attachment_table.file_name) AS attachment "
                    . " FROM $table_name_message_table"
                    //. " left join $table_name_attachment_table on $table_name_attachment_table.dispute_message_id=$table_name_message_table.id "
                    . " WHERE $table_name_message_table.dispute_id = $id";
            $results_dispute_message = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
            
          
            
                
            }
            if ($message != '') {
                ?>
                <div class="success-message btn-success "><?php echo $message; ?></div>
                <?php
                $message = '';
                
            }
            if (count($results_dispute_data)) {
                $data = $results_dispute_data[0];
              
                ?>

                <div class="panel panel-default panel-pading">
                    <div class="dispute_detail_area">
                        <div class="form-group-row"> 
                            <div class="dispute_header_detail">
                            <div class="dispute_id_area">
                                Dispute ID: <?php echo $id; ?>
                            </div>
                            <div class="dispute_date_area">
                             <?php echo date('F d, Y H:i: A', strtotime($data['created_at'])); ?>
                                <p>Status: <b><?php echo $data['status_name'];?></b></p>
                            </div>
                            </div>
                            <div class="detail_area_dispute_area">
                            <div class="form-group">
                                <label class="control-label col-sm-3 col-md-3" for="title"><?php echo __('Title', 'woocommerce'); ?></label>
                                <div class="col-md-6 col-sm-9 dispute_show_field">
                                    <?php echo $data['title']; ?>
                                </div>
                            </div>  
                            <div class="form-group">
                                <label class="control-label col-sm-3 col-md-3" for="message"><?php echo __('Message', 'woocommerce'); ?></label>
                                <div class="col-md-12 col-sm-9 dispute_show_field">

                                    <?php echo $data['message']; ?>

                                </div>
                            </div> 

                            <div class="form-group">
                                <label class="control-label col-sm-3 col-md-3" for="message"><?php echo __('Attachment', 'woocommerce'); ?></label>

                                <div class="col-md-6 col-sm-9 dispute_show_field">
                                    <ul class="list_attachment">
                                    <?php
                                    
                                    $attachment = $data['attachment'];
                                    if($attachment!=''){
                                        $attachments = explode(',', $attachment);
                                        
                                        foreach($attachments as $attachFile){
                                            $attachFile_check =   $target_dir = BUYLOCKSMITH_DEALS_PLUGIN_DIR . "/uploads/".$attachFile;
                                            if(file_exists($attachFile_check)){
                                               $file_url =  BUYLOCKSMITH_DEALS_PLUGIN_UPLOADS.$attachFile;
                                             //$attachFile =  explode('_'.$current_user.'_', $attachFile);
                                             
                                               echo '<li><a target="_blank" href="'.$file_url.'">'.$attachFile.'</a></li>';
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                    ?>
</ul>
                                </div>
                                
                                
                            </div> 

                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="panel panel-default panel-pading">
                    <?php if(count($results_dispute_message)>0){?>
                    <div class="message_list_area">
                        <?php foreach($results_dispute_message as $dispute_message){?>
                        <div class="message_main_area  <?php if($dispute_message['sender_id']==$current_user){ echo 'message_main_area_self'; }?>">
                            <div class="message_title_area">
                                <?php
                                if($dispute_message['sender_id']!=$current_user){
                                ?>
                                <div class="message_sender_name">
                                    <?php
                                        
                                        $name = get_user_meta($dispute_message['sender_id'], 'first_name',true).' '.get_user_meta($dispute_message['sender_id'], 'last_name',true);
                                        if(trim($name) == ''){
                                            $user = get_user_by('ID',$dispute_message['sender_id']);
                                        if(isset($user->user_login)){
                                           $name =  $user->user_login;
                                        }
                                        }
                                        
                                        
                                        echo ucfirst($name);
                                    ?>
                                    
                                </div>
                                <?php
                                }
                                ?>
                                <div class="message_date_area <?php if($dispute_message['sender_id']==$current_user){ echo 'message_date_area_botton_down'; }?>">  <?php echo date('F d, Y H:i A', strtotime($dispute_message['created_at'])); ?></div>
                                
                                
                            </div>
                            <div class="message_area"><?php echo $dispute_message['message'];?></div>
                           
                                <?php
                                     $attachment = $dispute_message['attachment'];
                                    if($attachment!=''){
                                        $attachments = explode(',', $attachment);
                                       echo '<div class="attachment_area_in_message">';
                                        foreach($attachments as $attachFile){
                                            $attachFile_check =   $target_dir = BUYLOCKSMITH_DEALS_PLUGIN_DIR . "/uploads/".$attachFile;
                                            if(file_exists($attachFile_check)){
                                               $file_url =  BUYLOCKSMITH_DEALS_PLUGIN_UPLOADS.$attachFile;
                                          //   $attachFile =  explode('_'.$current_user.'_', $attachFile);
                                            
                                               echo '<p><a target="_blank" href="'.$file_url.'"><i class=" glyphicon glyphicon-link"></i> '.$attachFile.'</a></p>';
                                            }
                                            
                                        }
                                       echo '</div>';
                                    }
                                ?>
                           
                        </div>
                        <?php } ?>
                    </div>
                    <?php } if($who_won_user_id==0){ ?>
                    
                    <form method="post" id="add_dispute" enctype="multipart/form-data">
                        <div class="form-group-row pricing"> 

                       
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

                                if (jQuery('#message').val() == '') {
                                
                                    if (jQuery('#message').val() == '') {
                                        jQuery('.error_message_area').html('Message field is required.');
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
jQuery(".message_list_area").animate({ scrollTop: jQuery(".message_list_area").prop("scrollHeight")}, 1000);

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
                    }else{
                        $name = 'you';
                        if($who_won_user_id!=$current_user && $who_won_user_id!=0){
                            $name = BuyLockSmithDealsCustomizationAddon::blsd_get_userFullName($who_won_user_id);
                        }
                        ?>
                         <form method="post" id="add_dispute" enctype="multipart/form-data">
                        <div class="form-group-row pricing"> 

                       
                            <div class="form-group">
                                <label class="control-label col-sm-12 col-md-12 height_label"><?php echo __('Dispute is in favor of '.$name.'.', 'woocommerce'); ?></label>
                             
                            </div> 
                        </div>
                         </form>
                    <?php
                    }
                    ?>
            </div>
                <?php
                }
                ?>
                      
                  </div>
        </div>
</div>