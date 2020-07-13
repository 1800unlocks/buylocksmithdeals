  <?php
  $BLSDCategorySettings = new BLSDCategorySettings;
  $categories = $BLSDCategorySettings->get_cat_data_arr($exclude = true);
  
  ?>
    <div id="tertiary" class="sidebar-container" role="complementary">
            <div class="sidebar-inner">
                <form method="get" action="">
                <div class="widget-area clearfix">
                    <div id="search-2" class="widget-odd widget-first widget-1 widget widget_search"><div class="widget-title"><h3>Category</h3></div>
    
    <div class="category-wrapper">        
        <select name="cat" class="category_list_sidebar">
            <?php
            if(count($categories)>0){
                foreach($categories as $category){
                   
                    $slug = $category['slug'];
                    $name = $category['name'];
                    $selected = '';
                    if($slug==$_REQUEST['cat']){
                        $selected = 'selected';
                    }
                    echo "<option $selected value='$slug'>$name</option>";
                }
            }
            ?>
        </select>
    </div>
</div>
        </div><!-- .widget-area -->
                <div class="widget-area clearfix">
                    <div id="search-2" class="widget-odd widget-first widget-1 widget widget_search"><div class="widget-title"><h3>Country</h3></div>
    
    <div class="category-wrapper">        
        
            
        <select id="blsd_regional_product_listing_country" class="blsd_regional_product_listing_country" placeholder="Choose Country" name="blsd_regional_product_listing_country" required="required">
							<option></option>
                                                   <?php     $BLSDRegionalProductListing = new  BLSDRegionalProductListing;?>
							<?php $country_list = $BLSDRegionalProductListing->get_country_arr(); 
                                                        $BuyLockSmithDealsCustomizationAddon = new BuyLockSmithDealsCustomizationAddon;
                                                        $BuyLockSmithDealsCustomizationAddon->blsd_update_user_country();
                                                        $blsd_country_alpha2code = trim($_REQUEST['blsd_regional_product_listing_country']);
                                                        ?>
							<?php foreach( $country_list as $cv ){ ?>
								<option value="<?php echo urlencode(utf8_encode($cv['alpha2Code'])); ?>" <?php echo selected($cv['alpha2Code'], $blsd_country_alpha2code); ?>>
								<?php echo utf8_encode($cv['name']); ?>
								</option>
							<?php } ?>
							</select>
           
       
    </div>
</div>
        </div><!-- .widget-area -->
                <div class="widget-area clearfix">
                    <div id="search-2" class="widget-odd widget-first widget-1 widget widget_search"><div class="widget-title"><h3>Zip Code</h3></div>
    
    <div class="zipcode-wrapper">     
        <?php
        $zip = '';
        if(isset($_REQUEST['blsd_regional_product_listing_zip'])){
         $zip = $_REQUEST['blsd_regional_product_listing_zip'];
        }
        ?>
        <input type="text" name="blsd_regional_product_listing_zip" class="zip" value="<?php echo $zip;?>">
    </div>
</div>
        </div><!-- .widget-area -->
                <div class="widget-area clearfix">
                    <div id="search-2" class="widget-odd widget-first widget-1 widget widget_search"><div class="widget-title"><h3>Distance(km)</h3></div>
    
    <div class="zipcode-wrapper">     
        <?php
        $distance = 15;
        if(isset($_REQUEST['blsd_regional_product_listing_proximity_km'])){
         $distance = $_REQUEST['blsd_regional_product_listing_proximity_km'];
        }
        ?>
        <input type="text" name="blsd_regional_product_listing_proximity_km" class="blsd_regional_product_listing_zip" value="<?php echo $distance;?>">
    </div>
</div>
        </div><!-- .widget-area -->
        <div class="widget-area clearfix">
            <input type="submit" value="Search" name="search">
        </div>
        </form>
            </div><!-- .sidebar-inner -->
        </div>