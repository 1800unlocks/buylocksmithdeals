<?php
 $vendor_id = $_REQUEST['vendor'];
 $current_url= add_query_arg( NULL, NULL ) ; 
 $search=isset($_REQUEST['search'])? $_REQUEST['search']:''; 
  $products = BuyLockSmithDealsCustomizationAdmin::getProductList(0,$search);
 $productList=$products['data'];
  $array_unassign = [];
if (isset($_REQUEST['update_assigned_product'])) {
    $product_ids = [];
    if(isset($_REQUEST['product_id'])){
    if (count($_REQUEST['product_id']) > 0) {
        foreach($_REQUEST['product_id'] as $prodID){
       $productList_duplicated = BuyLockSmithDealsAssignProductToVendor::product_duplicate($prodID,$vendor_id); 
        }
        $product_ids = $_REQUEST['product_id'];
          }
    }
      
   
          if(count($productList)>0){
      foreach($productList as $list){
          
          $list_id = $list->ID;
           if(!in_array($list_id, $product_ids)){
           $array_unassign[]=$list_id;
       }
      }
    
  }
  
}
 
  if(count($array_unassign)>0){
      $productList_duplicated = BuyLockSmithDealsAssignProductToVendor::product_unassign($array_unassign,$vendor_id); 
  }
   $products = BuyLockSmithDealsCustomizationAdmin::getProductList(0,$search);
   $productList=$products['data'];
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
        <h1>Assign Products to Vendor</h1>
        <div class="inner_area">
            <form method="post" class="assign_protuct_to_vendor">
                <div>
                <?php
                   $saller_name =   BuyLockSmithDealsCustomizationAddon::blsd_get_userFullName($vendor_id);
                   echo "<h3>Vendor: $saller_name</h3>"
                ?>
                </div>
                <div class="assign_search">
                        <input type="text" name="assign_search" id="assign_search" value="<?php echo $search; ?>"> <button type="button" name="search" id="search" >Search</button>
                    </div>
            <ul class="list_container">
                <?php
               
               
                if (count($productList) > 0) {
                    ?>
         <input type="checkbox" id="ckbCheckAll"  />  Select All
         <hr/>
    <?php
                    foreach ($productList as $prodDetail) {
                      $assign_status = BuyLockSmithDealsAssignProductToVendor::get_product_by_meta_and_status($prodDetail->ID, $vendor_id); 
                      
                      if(count($assign_status->posts)>0){
                         $assign_status = 1; 
                      }else{
                          $assign_status = 0;
                      }
                        ?>
                        <li class="container_product">
                            <div class="form-group-row pricing"> 
                                <div class="form-group">
                                    <div class="col-md-6 col-sm-9">
                                        <input type="checkbox" name="product_id[]" <?php if($assign_status==1){ echo 'checked="checked"';} ?> value="<?php echo $prodDetail->ID; ?>" class="checkBoxClass">
                                        <label class="control-label col-sm-3 col-md-3"><?php echo __($prodDetail->post_title, 'woocommerce'); ?></label>
                                    </div>

                                </div> 
                            </div>


                        </li>
                    <?php
                    }
                    echo BuyLockSmithDealsCustomizationAddon::render_pagination($current_url, $products['total_pages']); 
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