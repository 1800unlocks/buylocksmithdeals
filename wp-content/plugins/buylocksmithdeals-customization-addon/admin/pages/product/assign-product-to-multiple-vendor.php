<?php
global $wp;
$product_id = trim($_REQUEST['product_id']);

$current_url= add_query_arg( NULL, NULL ) ;

$search=isset($_REQUEST['search'])? $_REQUEST['search']:'';
$vendor= BuyLockSmithDealsCustomizationAddon::blsd_get_vendor_list($search);
$blsd_get_vendor_list=$vendor['data'];
$array_vendor = [];
 $temp_list = []; 
if (isset($_REQUEST['update_assigned_product'])) {
    $product_ids = [];
    if (count($_REQUEST['vendor_ids']) > 0) {
        foreach ($_REQUEST['vendor_ids'] as $prodVendorID) {
            BuyLockSmithDealsAssignProductToVendor::product_duplicate($product_id, $prodVendorID);
        }
       
        foreach($blsd_get_vendor_list as $tem_vendor_list){
            $temp_list[]=$tem_vendor_list->ID;
        }
        
        $array_vendor = array_diff($temp_list,$_REQUEST['vendor_ids']);
       
      
        
    }else{
     
        foreach($blsd_get_vendor_list as $tem_vendor_list){
            $temp_list[]=$tem_vendor_list->ID;
        }
        
    $array_vendor = $temp_list;  
}


}


if (count($array_vendor) > 0) {
    
    foreach($array_vendor as $vendor_unassing_id){
        
    BuyLockSmithDealsAssignProductToVendor::product_unassign([$product_id], $vendor_unassing_id);
    
    }
}

//echo '<pre>';
//print_r($blsd_get_vendor_list);
?>


<style>
    
/******* 11-10-2019 ********/  
    
.assign_protuct_to_vendor li.container_product {
    margin-bottom: 15px;
}
    
.assign_protuct_to_vendor{
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    font-family: 'Roboto', sans-serif;
}    
.assign_protuct_to_vendor  input[type="submit"] {
    background: #77c84e;
    padding: 10px 20px;
    color: #fff;
    font-size: 15px;
    border-radius: 4px;
    border: 0;
    font-weight: 500;
    text-transform: capitalize;
} 
.assign_protuct_to_vendor li.container_product {
    position: relative;
    padding-left: 25px;
}
.assign_protuct_to_vendor li.container_product input[type="checkbox"] {
    position: absolute;
    left: 0;
    top: 5px;
} 
    
@media screen and (max-width: 782px){
   .assign_protuct_to_vendor li.container_product {
        
        padding-left:30px;
    } 
   .assign_protuct_to_vendor li.container_product  input[type=checkbox]{
        height: 20px;
        width: 20px;
       top: 8px;
    }
}
    
/******* 11-10-2019 ********/  
    
    

</style>

<div class="wrap">
    <div class="container">
        <h1>Assign Product to Vendors</h1>
        <div class="inner_area">
            <form method="post" class="assign_protuct_to_vendor">
                  <div>
                <?php
                $product_title = get_the_title($product_id);
                
                   
                   echo "<h3>Deal Name: $product_title</h3>"
                ?>
                </div>
                
                    <div class="assign_search">
                        <input type="text" name="assign_search" id="assign_search" value="<?php echo $search; ?>"> <button type="button" name="search" id="search" >Search</button>
                    </div>
             
                <ul class="list_container">
<?php
if (count($blsd_get_vendor_list) > 0) {
    ?>
         <input type="checkbox" id="ckbCheckAll"  />  Select All
         <hr/>
    <?php
    foreach ($blsd_get_vendor_list as $vendor_data) {
      $user_nicename = $vendor_data->user_nicename;
      $vendor_id = $vendor_data->ID;
      $name = get_user_meta($vendor_id, 'first_name', true).' '.get_user_meta($vendor_id, 'last_name', true);
      if(trim($name)==''){
          $name = $user_nicename;
      }
      
      
        $assign_status = BuyLockSmithDealsAssignProductToVendor::get_product_by_meta_and_status($product_id, $vendor_id);
        

        if (count($assign_status->posts) > 0) {
            $assign_status = 1;
        } else {
            $assign_status = 0;
        }
        ?>
                            <li class="container_product">
                                <div class="form-group-row pricing"> 
                                    <div class="form-group">
                                        <div class="col-md-6 col-sm-9">
                                            <input type="checkbox" name="vendor_ids[]" <?php if ($assign_status == 1) {
                        echo 'checked="checked"';
                    } ?> value="<?php echo $vendor_id; ?>" class="checkBoxClass">
                                            <label class="control-label col-sm-3 col-md-3"><?php echo __($name, 'woocommerce'); ?></label>
                                        </div>

                                    </div> 
                                </div>


                            </li>
        <?php
    }
     echo BuyLockSmithDealsCustomizationAddon::render_pagination($current_url, $vendor['total_pages']); 
    ?>
                        <input type="submit" value="update" name="update_assigned_product">
                        <?php
                    } else {
                        echo '<li>Please start creating new product.</li>';
                    }
                    ?>
                </ul>
            </form>
            
        </div>
    </div>
</div>

<style>
    .assign_search{
        float:right;
        margin-top: -30px;
    }
    </style>
     <script>
    jQuery(document).ready(function(){
        jQuery("#ckbCheckAll").click(function () {
            jQuery(".checkBoxClass").prop('checked', jQuery(this).prop('checked'));
        });
       jQuery('#search').click(function(){
           var assign_search=jQuery('#assign_search').val();
            var  url = '<?php echo $current_url; ?>';
           if(assign_search != ''){
            var parameter='cpage';
             url= remove_param_url(url,parameter);
               location.href=url+"&search="+assign_search;
           }
           else{
              url= remove_param_url(url,'cpage'); 
              url= remove_param_url(url,'search'); 
              location.href=url;
           }
       });           
    });   
    function remove_param_url(url,parameter){
        var urlparts= url.split('?');   
        if (urlparts.length>=2) {
            var prefix= encodeURIComponent(parameter)+'=';
            var pars= urlparts[1].split(/[&;]/g);
            //reverse iteration as may be destructive
            for (var i= pars.length; i-- > 0;) {    
                //idiom for string.startsWith
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
                    pars.splice(i, 1);
                }
            }
            url= urlparts[0]+'?'+pars.join('&');

        } 
        return url;
    }
    </script>