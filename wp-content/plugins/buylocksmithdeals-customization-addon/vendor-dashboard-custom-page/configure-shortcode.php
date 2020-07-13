<?php
global $wpdb;
 $api_credentials = BuyLockSmithDealsCustomizationVendor::generate_vendor_site_api_credentials();
 
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
    .blsd_shortcode_area{
       background-color: gray;
        color: white;
        padding: 10px;
        width: 90%
    }
	.blsd_shortcode_area_slider{
       background-color: gray;
        color: white;
        padding: 10px;
        width: 90%
    }
    .blsd_create_div{
       background-color: gray;
        color: white;
        padding: 10px;
        width: 90%;
        
    }
    .blsd_create_script{
        background-color: gray;
        color: white;
        padding: 10px;
        width: 90%;
    }
    
    #button_copy{
         background-color: #3f85b9;
        color: white;
        height: 40px;
        padding: 8px 5px 4px;
    }
    #button_copy_code{
        background-color: #3f85b9;
        color: white;
        height: 40px;
        padding: 8px 5px 4px;
    }
    #button_copy_script{
         background-color: #3f85b9;
        color: white;
        height: 40px;
        padding: 8px 5px 4px;
    }
	#button_copy_slider{
         background-color: #3f85b9;
        color: white;
        height: 40px;
        padding: 8px 5px 4px;
    }
    
    .style_deal .style_btn{
        background-color: #3f85b9;
        color: white;
        height: 40px;
        padding: 8px 5px 4px;
        margin-bottom: 10px;
    }
    
    form.form-inline {
     float: none !important;
    }    
   .select_option_area{
     width: 69% !important
    }
   .select_style_area{
     width: 60% !important
    }
   .select_unique_area{
     width: 69% !important
    }
    .wcmp_displaybox2 h4{
        width: 30%
    }
    .wcmp_displaybox2 h4.style-heading {
        width: 39%
    }
    .wcmp_displaybox2 h4.unique-heading {
        width: 30%
    }
    .unique-id{
        border-bottom: 0px solid #e6e7e8 !important; 
        margin: 0px !important;
    }
    .displaybox2-unique-id{
        margin: 0px !important;
    }
</style>
<?php
$last_id='';
$type='';
$result_array=[];
$current_vendor = get_current_user_id();
$vendor_store_url = trailingslashit(get_home_url()).trailingslashit('locksmith-store').get_user_meta($current_vendor, '_vendor_page_slug', true);
$style_parameter=[];
$shortcode_parameter=[];
$preview_url='';
$table_name= BuyLockSmithDealsCustomizationAddon::blsd_deals_custom_design_name();
$query = "SELECT unique_id from $table_name where vendor_id=$current_vendor";
$result_ids = $wpdb->get_results($query,ARRAY_A);

if(isset($_POST['unique_ids']) && !empty($_POST['unique_ids'])){
    $query = "SELECT id from $table_name WHERE unique_id='".$_POST['unique_ids']."'";
    $result = $wpdb->get_row($query);
    if(!empty($result) && isset($result->id)){
        $last_id=$result->id;
     }
}


if(isset($_POST['style_preview']) || isset($_POST['style_submit'])){
    $id=(isset($_POST['id']) && !empty($_POST['id']))?$_POST['id']:'';
    
    $layout=(isset($_POST['layout']) && !empty($_POST['layout']))?$_POST['layout']:'';
    $sort=(isset($_POST['sort']) && !empty($_POST['sort']))?$_POST['sort']:'';
    $records=(isset($_POST['records']) && !empty($_POST['records']))?$_POST['records']:'';
    $see_more_deals=(isset($_POST['see_more_deals']) && !empty($_POST['see_more_deals']))?$_POST['see_more_deals']:0;
    $vendor_url=(isset($_POST['vendor_url']) && !empty($_POST['vendor_url']))?$_POST['vendor_url']:'';
    $category=(isset($_POST['category']) && !empty($_POST['category']))?$_POST['category']:'';
    $deal=(isset($_POST['deal']) && !empty($_POST['deal']))?$_POST['deal']:'';
    $api_key=(isset($_POST['api_key']) && !empty($_POST['api_key']))?$_POST['api_key']:'';
    $api_url=(isset($_POST['api_url']) && !empty($_POST['api_url']))?$_POST['api_url']:'';
    $shortcode_parameter=['layout'=>$layout,'sort'=>$sort,'records'=>$records,'see_more_deals'=>$see_more_deals,'vendor_url'=>$vendor_url,'category'=>$category,'deal'=>$deal,'api_key'=>$api_key,'api_url'=>$api_url];
    
    $btn_colors=(isset($_POST['btn_colors']) && !empty($_POST['btn_colors']))?$_POST['btn_colors']:'';
    $text_color=(isset($_POST['text_color']) && !empty($_POST['text_color']))?$_POST['text_color']:'';
    $btn_text_color=(isset($_POST['btn_text_color']) && !empty($_POST['btn_text_color']))?$_POST['btn_text_color']:'';
    $border_color=(isset($_POST['border_color']) && !empty($_POST['border_color']))?$_POST['border_color']:'';
    $price_color=(isset($_POST['price_color']) && !empty($_POST['price_color']))?$_POST['price_color']:'';
    $style_parameter=['btn_colors'=>$btn_colors,'text_color'=>$text_color,'btn_text_color'=>$btn_text_color,'border_color'=>$border_color,'price_color'=>$price_color];
    
}

if(isset($_POST['style_preview'])){
    
    $table_name= BuyLockSmithDealsCustomizationAddon::blsd_temp_deals_custom_design_name();
    
    $query = "SELECT id from $table_name WHERE unique_id='$id'";
    $result = $wpdb->get_row($query);
    
    if(!empty($result) && isset($result->id)){
     $wpdb->update($table_name, array('vendor_id'=>$current_vendor,'style_parameter'=> serialize($style_parameter),'shortcode_parameter'=> serialize($shortcode_parameter) ), ['unique_id' => $id]);
     $last_id=$result->id;
      }
    else{
        $rows_affected = $wpdb->insert($table_name, array('unique_id' => $id,'vendor_id'=>$current_vendor,'style_parameter'=> serialize($style_parameter),'shortcode_parameter'=> serialize($shortcode_parameter) ));
        $last_id=$wpdb->insert_id;
    }
    $type='preview';
}

if(isset($_POST['style_submit'])){
   
    $table_name= BuyLockSmithDealsCustomizationAddon::blsd_deals_custom_design_name();
    
    $query = "SELECT id from $table_name WHERE unique_id='$id'";
    $result = $wpdb->get_row($query);
    
    if(!empty($result) && isset($result->id)){
     $wpdb->update($table_name, array('vendor_id'=>$current_vendor,'style_parameter'=> serialize($style_parameter),'shortcode_parameter'=> serialize($shortcode_parameter) ), ['unique_id' => $id]);
     $last_id=$result->id;
      }
    else{
        $rows_affected = $wpdb->insert($table_name, array('unique_id' => $id,'vendor_id'=>$current_vendor,'style_parameter'=> serialize($style_parameter),'shortcode_parameter'=> serialize($shortcode_parameter) ));
        $last_id=$wpdb->insert_id;
    }
    $temp_table_name= BuyLockSmithDealsCustomizationAddon::blsd_temp_deals_custom_design_name();
    
    $temp_query = "SELECT id from $temp_table_name WHERE unique_id='$id'";
    $temp_result = $wpdb->get_row($temp_query);
    if(!empty($temp_result) && isset($temp_result->id)){
        $sql_delete = $wpdb->prepare('DELETE from `' . $temp_table_name . '` WHERE id=%d', array($temp_result->id));
        $wpdb->query($sql_delete);
    }
    $type='save';
}

if(!empty($last_id)){
$sql = "SELECT * from $table_name WHERE id=$last_id";
$result_array = $wpdb->get_row($sql,ARRAY_A);
}



if(!empty($result_array)){
    $shortCode_ID = $result_array['unique_id']; 
    $style_parameter = unserialize($result_array['style_parameter']); 
    $shortcode_parameter = unserialize($result_array['shortcode_parameter']); 
    if($type=='preview'){
        $preview_url=home_url().'/preview/?id='.$shortCode_ID;
    }
}
else{
    $uniqueKey = time().'_'.$current_vendor;
    $shortCode_ID =  md5($uniqueKey);
}

$productCategories = BLSDIncWnW::blsd_get_all_categories();
$vendorDeals = BLSDIncWnW::blsd_get_deals_of_vendor();
$api_credentials = BuyLockSmithDealsCustomizationVendor::generate_vendor_site_api_credentials();
$api_url=home_url().'/wp-json/blsd/';

?>
<div class="wcmp_form1">
	<div class="col-md-12 add-product-wrapper">
        <!-- Top product highlight -->
                <!-- End of Top product highlight -->
                  <div class="product-primary-info custom-panel"> 
                      
                       <div class="panel panel-default panel-pading">
                           <form id="unique_id_form" name="unique_id_form" method="post">
                           <div class="panel-heading unique-id">
                            <div class="col-md-6">
                                <div class="wcmp_displaybox2 text-center displaybox2-unique-id">
                                    <h4  class='unique-heading'>Unique Ids</h4>
                                    <select class="select_unique_area" name="unique_ids" id="unique_ids" onchange="change_unique_id()">
                                        <option value="">Select ID</option>
                                        <?php
                                        if(count($result_ids)>0){
                                        foreach($result_ids as $ids){
                                            ?>
                                        <option <?php if($shortCode_ID == $ids['unique_id']){ echo 'selected';  } ?> value="<?php echo $ids['unique_id']; ?>"><?php echo $ids['unique_id']; ?></option>
                                        <?php 
                                        }
                                        }
                                        ?>
                                    </select>
                                    
                                    
                                </div>
                            </div>   
                               <div class="col-md-6"> 
                                <div class="style_deal">
                            <button class="style_btn" type='button' name="add_new" id="add_new">Add New</button>
                                </div>
                            </div>
                               
                           </div>
                           </form>
                       </div>
                      
                      
            <div class="panel panel-default panel-pading">
                            
                            
                            
                
        <form name="wcmp_vendor_dashboard_stat_report" method="POST" class="stat-date-range form-inline">
            <div class="wcmp_form1 ">
                
                <div class="panel-body">
                    <div class="wcmp_ass_holder_box">
                        <div class="row">
                            <div class="col-md-12"> 
                                <div class="style_deal">
                            <button class="style_btn" type='button' name="reset" id="reset">Reset</button>
                                </div>
                            </div>
                            <div class="col-md-6"> 
                                <div class="wcmp_displaybox2 text-center">
                                    <h4>Layout columns</h4>
                                    <?php if(!empty($shortcode_parameter) && !empty($shortcode_parameter['layout'])){ $shortcode_layout=$shortcode_parameter['layout']; }else{ $shortcode_layout=''; } ?>
                                    <select class="select_option_area" name="layout" id="layout" attr-data="layout" attr-type="select" onchange="update_shortcode()">
                                        <option value="">Select Layout</option>
                                        <option <?php if($shortcode_layout == 1){ echo 'selected'; } ?> value="1">1</option>
                                        <option <?php if($shortcode_layout == 2){ echo 'selected'; } ?> value="2">2</option>
                                        <option <?php if($shortcode_layout == 3){ echo 'selected'; } ?> value="3">3</option>
                                    </select>
                                    
                                    
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wcmp_displaybox2 text-center">
                                    <h4>Sorting options</h4>
                                    <?php if(!empty($shortcode_parameter) && !empty($shortcode_parameter['sort'])){ $shortcode_sort=$shortcode_parameter['sort']; }else{ $shortcode_sort=''; } ?>
                                    <select class="select_option_area" name="sort" id="sort" attr-data="sort" attr-type="select" onchange="update_shortcode()">
                                        <option value="">Select sort by</option>
                                        <option <?php if($shortcode_sort == 'title'){ echo 'selected'; } ?> value="title">By Title</option>
                                        <option <?php if($shortcode_sort == 'price'){ echo 'selected'; } ?> value="price">By Price</option>
                                        <option <?php if($shortcode_sort == 'date'){ echo 'selected'; } ?> value="date">By Date</option>
                                    </select>
                                    
                                    
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wcmp_displaybox2 text-center">
                                    <h4>Limit options</h4>
                                    <?php if(!empty($shortcode_parameter) && !empty($shortcode_parameter['records'])){ $shortcode_records=$shortcode_parameter['records']; }else{ $shortcode_records=''; } ?>
                                    <select class="select_option_area" name="records" id="records"  attr-data="records" attr-type="select" onchange="update_shortcode()">
                                        <option value="">Select records</option>
                                        <?php for($i=1;$i<10;$i++){ ?>
                                        <option <?php if($shortcode_records == $i){ echo 'selected'; } ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php } ?>
                                        <option <?php if($shortcode_records == 10){ echo 'selected'; } ?> value="10">10</option>
                                        <option <?php if($shortcode_records == 15){ echo 'selected'; } ?> value="15">15</option>
                                        <option <?php if($shortcode_records == 20){ echo 'selected'; } ?> value="20">20</option>
                                        <option <?php if($shortcode_records == 30){ echo 'selected'; } ?> value="30">30</option>
                                        <option <?php if($shortcode_records == 40){ echo 'selected'; } ?> value="40">40</option>
                                        <option <?php if($shortcode_records == 50){ echo 'selected'; } ?> value="50">50</option>
                                    </select>
                                    
                                    
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="wcmp_displaybox2 text-center">
                                    <?php if(!empty($shortcode_parameter) && isset($shortcode_parameter['see_more_deals'])){ $shortcode_see_more_deals=$shortcode_parameter['see_more_deals']; }else{ $shortcode_see_more_deals=''; } ?>
                                    
                                    <input type="checkbox" name="see_more_deals" id="see_more_deals" value="1" onclick="update_shortcode()" <?php if($shortcode_see_more_deals == 1){ echo 'checked'; } ?> ><h4>See More Deals</h4>
                                    <input type="hidden" name="vendor_url" id="vendor_url" value="<?php echo $vendor_store_url; ?>">
                                </div>
                            </div>
                              <div class="col-md-6">
                                <div class="wcmp_displaybox2 text-center">
                                    <h4>Categories</h4>
                                    <?php if(!empty($shortcode_parameter) && !empty($shortcode_parameter['category'])){ $shortcode_category=$shortcode_parameter['category']; }else{ $shortcode_category=''; } ?>
                                   
                                    <select class="select_option_area categories_change_action" name="category" id="category" attr-data="category" attr-type="select" onchange="update_shortcode()">
                                        <option value="">Select category</option>
                                        <?php
                                        if(count($productCategories)>0){
                                        foreach($productCategories as $category){ ?>
                                        <option <?php if($shortcode_category == $category->term_id){ echo 'selected'; } ?> value="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></option>
                                        <?php } 
                                        }
                                        ?>
                                    </select>
                                    
                                    
                                </div>
                            </div>
                             
                             <div class="col-md-6">
                                <div class="wcmp_displaybox2 text-center">
                                    <h4>Deals</h4>
                                    <?php if(!empty($shortcode_parameter) && !empty($shortcode_parameter['deal'])){ $shortcode_deal=$shortcode_parameter['deal']; }else{ $shortcode_deal=''; } ?>
                                   
                                    <select class="select_option_area deal_select" name="deal" id="deal" attr-data="deal" attr-type="select" onchange="update_shortcode()">
                                        <option value="">Select Deal</option>
                                        <?php
                                        if(count($vendorDeals)>0){
                                        foreach($vendorDeals as $deal){
                                            if($deal->post_status =='publish'){
                                            ?>
                                        <option <?php if($shortcode_deal == $deal->ID){ echo 'selected'; } ?> value="<?php echo $deal->ID; ?>"><?php echo $deal->post_title; ?></option>
                                        <?php } 
                                        }
                                        }
                                        ?>
                                    </select>
                                    
                                    
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                
                
            </div>
       
    </div>
                      <div class="panel panel-default panel-pading">
                          <div class="panel-heading">
                    <h2>Styling of deal page:</h2> 
                    <hr>
                    <input type="hidden" name="id" value="<?php echo $shortCode_ID ?>">
                    <input type="hidden" name="api_key" value="<?php echo $api_credentials ?>">
                    <input type="hidden" name="api_url" value="<?php echo $api_url ?>">
                     <div class="col-md-12"> 
                                <div class="style_deal">
                            <button class="style_btn" type='button' name="reset_styling" id="reset_styling">Reset</button>
                                </div>
                            </div>
                    
                        <div class="col-md-6">
                            <div class="wcmp_displaybox2 text-center">
                                <h4 class='style-heading'>Button color:</h4>
                                <?php if(!empty($style_parameter) && !empty($style_parameter['btn_colors'])){ $style_btn_colors=$style_parameter['btn_colors']; }else{ $style_btn_colors=''; } ?>
                                <input class="select_style_area" type="color" name="btn_colors" id="btn_colors" value="<?php echo $style_btn_colors; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wcmp_displaybox2 text-center">
                                <h4 class='style-heading'>Deal Title color:</h4>
                                <?php if(!empty($style_parameter) && !empty($style_parameter['text_color'])){ $style_text_color=$style_parameter['text_color']; }else{ $style_text_color=''; } ?>
                                <input class="select_style_area" type="color" name="text_color" id="text_color" value="<?php echo $style_text_color; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wcmp_displaybox2 text-center">
                                <h4 class='style-heading'>Button Text color:</h4>
                                <?php if(!empty($style_parameter) && !empty($style_parameter['btn_text_color'])){ $style_btn_text_color=$style_parameter['btn_text_color']; }else{ $style_btn_text_color=''; } ?>
                                <input class="select_style_area" type="color" name="btn_text_color" id="btn_text_color" value="<?php echo $style_btn_text_color; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wcmp_displaybox2 text-center">
                                <h4 class='style-heading'>Border color:</h4>
                                <?php if(!empty($style_parameter) && !empty($style_parameter['border_color'])){ $style_border_color=$style_parameter['border_color']; }else{ $style_border_color=''; } ?>
                                <input class="select_style_area" type="color" name="border_color" id="border_color" value="<?php echo $style_border_color; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wcmp_displaybox2 text-center">
                                <h4 class='style-heading'>Price Text color:</h4>
                                <?php if(!empty($style_parameter) && !empty($style_parameter['price_color'])){ $style_price_color=$style_parameter['price_color']; }else{ $style_price_color=''; } ?>
                                <input class="select_style_area" type="color" name="price_color" id="price_color" value="<?php echo $style_price_color; ?>">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="style_deal">
                                <input class="style_btn" type="Submit" name="style_preview" id="style_preview" value="Preview">
                                 <input class="style_btn" type="Submit" name="style_submit" id="style_submit" value="Save">
                            </div>
                        </div>
                    </form>
                    
                </div>
                      </div>  
                      
                      
                      <div class="panel panel-default panel-pading">
                          <div class="panel-heading">
                    <h2>Copy Shortcode for wordpress site to display products list:</h2> 
                    <hr>
                    <input type="text" class="form-group blsd_shortcode_area" id="blsd_shortcode_area" value='[blsd_display_products id="<?php echo $shortCode_ID;?>"]'>
                    <button type="button" id="button_copy" onclick="myFunction()">Copy Code</button>
                    </div>
                      </div>
					  <div class="panel panel-default panel-pading">
                          <div class="panel-heading">
                    <h2>Copy Shortcode for wordpress site to display products slider:</h2> 
                    <hr>
                    <input type="text" class="form-group blsd_shortcode_area_slider" id="blsd_shortcode_area_slider" value='[blsd_display_vendor_products id="<?php echo $shortCode_ID;?>"]'>
                    <button type="button" id="button_copy_slider" onclick="myFunction_slider()">Copy Code</button>
                    </div>
                      </div>
                      <div class="panel panel-default panel-pading">
                          <div class="panel-heading">
                    <h2>Copy Code for display products list using script:</h2> 
                    <hr>
                    <h4><b>Step 1:</b> Copy Script and paste in header section of the site.</h4>
                    <?php $script_url= BUYLOCKSMITH_DEALS_ASSETS_PATH.'js/blsd-product-script.js'; ?>
                    <input type="text" class="form-group blsd_create_script" id="blsd_create_script" value='<script type="text/javascript" src="<?php echo $script_url; ?>"></script>'>
                    <button type="button" id="button_copy_script" onclick="myFunction_script()">Copy Code</button>
                    <hr>
                    <h4><b>Step 2:</b> Copy element and paste it where you want to show product lists.</h4>
                    <input type="text" class="form-group blsd_create_div" id="blsd_create_div" value='<div class="blsd_pro_list" id="<?php echo $shortCode_ID;?>" api_url="<?php echo $api_url; ?>" api_key="<?php echo $api_credentials; ?>" ></div>'>
                    <button type="button" id="button_copy_code" onclick="myFunction_code()">Copy Code</button>
                    
                </div>
                      </div>  
                      
                      
         </div>
       
       
    
    
</div>
	</div>


 <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  
  
<script>
jQuery(document).ready(function(){
   <?php if(!empty($shortcode_parameter)){ ?>
        update_shortcode();
   <?php } ?>
       
       <?php if(!empty($preview_url)){ ?>
           var win = window.open('<?php echo $preview_url; ?>', '_blank');
            if (win) {
                win.focus();
            } else {
                alert('Please allow popups for this website');
            }
       <?php } ?>
      
   jQuery('#button_copy').on({
  click: function() {
    jQuery(this).tooltip({ items: "#button_copy", content: "Copied",
        position: {
   my: "center bottom-10", // the "anchor point" in the tooltip element
        at: "center top",
}
});
jQuery( "#button_copy" ).tooltip( "option", "classes.ui-tooltip", "highlight" );
    jQuery(this).tooltip("open");
  },
  mouseout: function() {    
      
     jQuery(this).tooltip("disable");   
  }
}); 

jQuery('#button_copy_slider').on({
  click: function() {
    jQuery(this).tooltip({ items: "#button_copy_slider", content: "Copied",
        position: {
   my: "center bottom-10", // the "anchor point" in the tooltip element
        at: "center top",
}
});
jQuery( "#button_copy_slider" ).tooltip( "option", "classes.ui-tooltip", "highlight" );
    jQuery(this).tooltip("open");
  },
  mouseout: function() {    
      
     jQuery(this).tooltip("disable");   
  }
});

 jQuery('#button_copy_code').on({
  click: function() {
    jQuery(this).tooltip({ items: "#button_copy_code", content: "Copied",
        position: {
   my: "center bottom-10", // the "anchor point" in the tooltip element
        at: "center top",
}
});
jQuery( "#button_copy_code" ).tooltip( "option", "classes.ui-tooltip", "highlight" );
    jQuery(this).tooltip("open");
  },
  mouseout: function() {    
      
     jQuery(this).tooltip("disable");   
  }
});
jQuery('#add_new').click(function(){
    window.location.href=window.location.href;
});
jQuery('#reset').click(function(){
    jQuery('.select_option_area').val('');
    jQuery('#see_more_deals').prop('checked', false);
    update_shortcode();
});
jQuery('#reset_styling').click(function(){
    jQuery('.select_style_area').val('');
    
});
    
});
 var selected_item = '';
 var attr_data = '';
        var attr_type = '';
        var final_shortcode = '';
        var final_div = '';
        var final_shortcode_slider = '';
function update_shortcode(){
    
    final_shortcode = 'blsd_display_products';
    final_shortcode_slider = 'blsd_display_vendor_products';
    final_div = 'div';
    final_shortcode =   final_shortcode+' id="'+'<?php echo $shortCode_ID;?>"';
    final_shortcode_slider =   final_shortcode_slider+' id="'+'<?php echo $shortCode_ID;?>"';
    final_div =   final_div+' class="blsd_pro_list" id="'+'<?php echo $shortCode_ID;?>" api_key=" <?php echo $api_credentials; ?>" api_url="<?php echo $api_url; ?>"';
    jQuery('.select_option_area').each(function(index,value){
            console.log(value);
            attr_data = jQuery(value).attr('attr-data');
            attr_type = jQuery(value).attr('attr-type');
        if(attr_type=='select'){
            selected_item =  jQuery(value).find(":selected").val() ;
        }
        
        if(selected_item!=''){
            
            final_shortcode = final_shortcode+' '+attr_data+'="'+selected_item+'"';
            final_shortcode_slider = final_shortcode_slider+' '+attr_data+'="'+selected_item+'"';
            final_div = final_div+' '+attr_data+'="'+selected_item+'"';
        }
        
      
        
    });
    if(jQuery('#see_more_deals').prop('checked') == true){
        var vendor_url=jQuery('#vendor_url').val();
      final_shortcode = final_shortcode+' '+'see_more_deals=1 vendor_url="'+vendor_url+'"'; 
      final_div = final_div+' '+'see_more_deals=1 vendor_url="'+vendor_url+'"'; 
    }
   
    //console.log(final_shortcode);
    
    final_shortcode = '['+final_shortcode+']';
    final_shortcode_slider = '['+final_shortcode_slider+']';
    final_div = '<'+final_div+'></div>';
      
      jQuery('.blsd_shortcode_area').val(final_shortcode);
      jQuery('.blsd_shortcode_area_slider').val(final_shortcode_slider);
      jQuery('.blsd_create_div').val(final_div);
       
}

function change_unique_id(){
    var unique_ids=jQuery('#unique_ids').val();
    if(unique_ids !=''){
        jQuery('#unique_id_form').submit();
    }
}

function myFunction() { 
  /* Get the text field */
  var copyText = document.getElementById("blsd_shortcode_area");

  /* Select the text field */
  copyText.select();
  copyText.setSelectionRange(0, 99999); /*For mobile devices*/

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Alert the copied text */
  //alert("Copied the text: " + copyText.value);
}
function myFunction_code() { 
  /* Get the text field */
  var copyText = document.getElementById("blsd_create_div");

  /* Select the text field */
  copyText.select();
  copyText.setSelectionRange(0, 99999); /*For mobile devices*/

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Alert the copied text */
  //alert("Copied the text: " + copyText.value);
}
function myFunction_script() { 
  /* Get the text field */
  var copyText = document.getElementById("blsd_create_script");

  /* Select the text field */
  copyText.select();
  copyText.setSelectionRange(0, 99999); /*For mobile devices*/

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Alert the copied text */
  //alert("Copied the text: " + copyText.value);
}
function myFunction_slider() { 
  /* Get the text field */
  var copyText = document.getElementById("blsd_shortcode_area_slider");

  /* Select the text field */
  copyText.select();
  copyText.setSelectionRange(0, 99999); /*For mobile devices*/

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Alert the copied text */
  //alert("Copied the text: " + copyText.value);
}


jQuery('.categories_change_action').on('change',function(){
  var category = jQuery(this).val();
  
   
      jQuery.ajax({
      type: 'POST',
      url: "<?php echo home_url();?>/wp-admin/admin-ajax.php?action=blsd_get_deals_by_category",
      data: 'category='+category,
      success: function(resultData) {
          resultData = JSON.parse(resultData);
          console.log(resultData);
          var option = '<option selected="selected" value="">Select Deal</option>';
          if(resultData.length>0){
              for(var optionData of resultData){
                  if(optionData.post_status=='publish'){
              option = option+'<option value="'+optionData.ID+'">'+optionData.post_title+'</option>';    
          }
            }
        }
          jQuery('.deal_select')
    .empty()
    .append(option);
          update_shortcode();
    }
});
  
});

</script>


