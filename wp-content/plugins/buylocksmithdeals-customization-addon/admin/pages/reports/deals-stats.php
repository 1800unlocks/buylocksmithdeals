
<div class="wrap">
    <div class="container">
        <h1>Deals Stats</h1>
        <div class="inner_area report_inner_area">
             <div class="select_number_of_records">
                <label>Show Records</label>
                <select id="updateRecordsCount" onchange="updateRecords(jQuery(this))">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    
                </select>
            </div>
            
            <div class="deal_table_list_main">
            <div class="deal_table_list">
              <h3>Top Selling Deals</h3>  
              <table class="deal_table_list_table product_deal_table_list_table wp-list-table widefat fixed striped">
  <thead>
    <tr>
      <th>Deal Name</th>
      <th>Total</th>
    </tr>
  </thead>
  <tbody class="top_selling_deals">
  </tbody>
  <tfoot>
     <tr>
      <th>Deal Name</th>
      <th>Total</th>
    </tr>
  </tfoot>
</table>
              <div class="nodata_message product_nodata_message">No record found to show.</div>
            </div>
           
            <div class="deal_table_list">
              <h3>Top Selling Deal Categories</h3>  
               <table class="deal_table_list_table category_deal_table_list_table wp-list-table widefat fixed striped">
  <thead>
    <tr>
      <th>Category Name</th>
      <th>Total</th>
    </tr>
  </thead>
  <tbody class="category_list_data">
  </tbody>
  <tfoot>
    <tr>
      <th>Deal Name</th>
      <th>Total</th>
    </tr>
  </tfoot>
</table>
                <div class="nodata_message category_nodata_message">No record found to show.</div>
            </div>
            </div>
            <div class="image_loader">
            <img src="<?php echo BUYLOCKSMITH_DEALS_ASSETS_PATH;?>/img/loader.gif">
            </div>
        </div>
        
    </div>
</div>


<script>
    jQuery(document).ready(function(){
        
       updateRecords(jQuery('#updateRecordsCount')); 
    });
function updateRecords(that){
    jQuery('.image_loader').show();
    selectedlistLength = that.val();
     jQuery.ajax({
      type: 'POST',
      url: "admin-ajax.php?action=get_stats_top_selling",
      data: 'selectedlistLength='+selectedlistLength,
      dataType: "text",
      success: function(resultData) { 
          
       resultData =   JSON.parse(resultData);
         console.log(resultData);
          var productHtml = '';
          var productCategory = '';
          var products = resultData.products;
          var categories = resultData.category;
          if(products.length>0){
             for(let product of products){
              productHtml=productHtml+'<tr><td>'+product.title+'</td><td>'+product.total_sales+'</td></tr>';
                 
             }
             jQuery('.product_deal_table_list_table').show();
             jQuery('.top_selling_deals').html(productHtml);
          }else{
          jQuery('.product_deal_table_list_table').hide();
          jQuery('.product_nodata_message').show();
          }
          
          
          if(categories.length>0){
             for(let category of categories){
              productCategory=productCategory+'<tr><td>'+category.name+'</td><td>'+category.total_sales+'</td></tr>';
                 
             }
             jQuery('.category_deal_table_list_table').show();
             jQuery('.category_list_data').html(productCategory);
          }else{
          jQuery('.category_deal_table_list_table').hide();
          jQuery('.category_nodata_message').show();
          }
           
         
       jQuery('.image_loader').hide();   
      
    }
});
}
</script>
