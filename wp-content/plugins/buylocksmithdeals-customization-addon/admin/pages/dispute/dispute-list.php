<?php
 $vendor_id = $_REQUEST['vendor'];
  $productList = BuyLockSmithDealsCustomizationAdmin::getProductList();

  
  $array_unassign = [];
if (isset($_REQUEST['update_assigned_product'])) {
    $product_ids = [];
    if (count($_REQUEST['product_id']) > 0) {
        foreach($_REQUEST['product_id'] as $prodID){
       $productList_duplicated = BuyLockSmithDealsAssignProductToVendor::product_duplicate($prodID,$vendor_id); 
        }
        $product_ids = $_REQUEST['product_id'];
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
   $productList = BuyLockSmithDealsCustomizationAdmin::getProductList();
?>



<div class="wrap">
    <div class="container">
        <h1>Dispute List</h1>
        <div class="inner_area">
         <?php
          require_once 'model/displute-list.php';
//Prepare Table of elements
$wp_list_table = new Links_List_Table();
         ?>
            
                 <div id="post-body-content client_list_table nuj4_custom_admin_list_table">
                    
                        <form method="post" action="<?php echo home_url();?>/wp-admin/admin.php?page=blsm-dispute-list">
   
                            <?php
                            $wp_list_table->prepare_items();
                                                 ?>
   <p class="search-box">
	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
        <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="search" value="<?php echo $_REQUEST['search'] ?>" />
	
        <input type="submit" id="search-submit" class="button" value="Search">
</p>
   <?php
                            $wp_list_table->display(); ?>
                        </form>
                    
                </div>
        </div>
    </div>
</div>
