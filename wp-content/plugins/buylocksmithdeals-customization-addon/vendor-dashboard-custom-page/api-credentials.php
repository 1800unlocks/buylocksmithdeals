<?php

 $api_credentials = BuyLockSmithDealsCustomizationVendor::generate_vendor_site_api_credentials();
 
?>

<div class="wcmp_form1">
	<div class="col-md-12 add-product-wrapper">
        <!-- Top product highlight -->
                <!-- End of Top product highlight -->
                  <div class="product-primary-info custom-panel"> 
            
            <div class="right-primary-info"> 
                 
                <div class="form-group-wrapper">
                    
                 
                    <div class="form-group product-short-description">
                                      
            <div class="form-group-row pricing show_if_simple show_if_external"> 
              <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_sale_price">Api Url</label>
                    <div class="col-md-6 col-sm-9">
                        <input type="text" id="_sale_price" name="_sale_price" value="<?php echo home_url();?>/wp-json/blsd/" class="form-control">
                       
                    </div>
                </div> 
                <div class="form-group">
                    <label class="control-label col-sm-3 col-md-3" for="_regular_price">Api Key</label>
                    <div class="col-md-6 col-sm-9">
                        <input type="text" id="_regular_price" name="_regular_price" value="<?php echo $api_credentials;?>" class="form-control">
                    </div>
                </div>  
             
                <div class="form-group">
                   
                    <div class="col-md-12 col-sm-12">
                        <div class="btn-danger error_message_area"></div>
                       
                    </div>
                </div> 
                 
              
                 
            </div>
                        
                            </div>
                    
                   
                </div> 
            </div>
      
        </div>
       
       
    
    
</div>
	</div>
