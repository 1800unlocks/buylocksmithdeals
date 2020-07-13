<?php
$all_cities = BuyLockSmithDealsCustomizationAdminReport::get_vendor_unique_city();
$current_url= add_query_arg( NULL, NULL ) ; 
?>
<div class="wrap">
    <form id="posts-filter" method="get">
        <input type="hidden" name="page" value="blsm-deals-city-stats">
        <select class="class_selected" id="bulk-action-selector-top" name="city">
            <option value="">Select City</option>
            <?php
            foreach ($all_cities as $val) {
                $selected = isset($_REQUEST['city']) && $_REQUEST['city'] == $val->city ? "selected" : '';
                ?>
                <option <?php echo $selected; ?> value="<?php echo $val->city; ?>"><?php echo $val->city; ?></option>
            <?php } ?>
        </select>
        <?php
        if (isset($_REQUEST['city']) && isset($_REQUEST['page']) && $_REQUEST['page'] == 'blsm-deals-city-stats') {
            $all_vendors = BuyLockSmithDealsCustomizationAdminReport::get_all_vendors($_REQUEST['city']);
            $all_deals = BuyLockSmithDealsCustomizationAdminReport::get_all_deals($_REQUEST['city']);
            $all_categories = BuyLockSmithDealsCustomizationAdminReport::get_all_categories();
            //  print_r($all_categories);
            ?>
            <select class="class_selected" id="bulk-action-selector-top" name="user_id">
                <option value="">All Vendors</option>
                <?php
                foreach ($all_vendors as $val) {
                    $user = get_userdata($val);
                    $selected = isset($_REQUEST['user_id']) && $_REQUEST['user_id'] == $val ? "selected" : '';
                    ?>
                    <option <?php echo $selected; ?> value="<?php echo $val; ?>"><?php echo $user->display_name; ?></option>
                <?php } ?>
            </select>
            <select class="class_selected" id="bulk-action-selector-top" name="deal_id">
                <option value="">All Deals</option>
                <?php
                foreach ($all_deals as $val) {
                    $product = wp_get_single_post($val);
                    $selected = isset($_REQUEST['deal_id']) && $_REQUEST['deal_id'] == $val ? "selected" : '';
                    ?>
                    <option <?php echo $selected; ?> value="<?php echo $val; ?>"><?php echo $product->post_title; ?></option>
                <?php } ?>
            </select>
            <select class="class_selected" id="bulk-action-selector-top" name="cat_id">
                <option value="">All Categories</option>
                <?php
                foreach ($all_categories as $val) {

                    $selected = isset($_REQUEST['cat_id']) && $_REQUEST['cat_id'] == $val->term_id ? "selected" : '';
                    ?>
                    <option <?php echo $selected; ?> value="<?php echo $val->term_id; ?>"><?php echo $val->name; ?></option>
                <?php } ?>
            </select>
        <?php } ?>
        <?php
        if(isset($_REQUEST['cpage'])){$cpage=$_REQUEST['cpage'];}else{ $cpage=1;} ?>
        <input type="hidden" name="cpage" id="cpage" value="<?php echo $cpage; ?>">
        <button type="submit" class="btn button btn-success">Find Deals</button>
        
        
        <button type="submit" class="btn button btn-success export" name="export" id="export">Export</button>
        
        <?php
        if (isset($_REQUEST['city'])  && isset($_REQUEST['page']) && $_REQUEST['page'] == 'blsm-deals-city-stats') {
            global $wpdb;
            ?>
                <table class="wp-list-table widefat striped clients_page_blsm-dispute-lists sortable">
                 <?php   
            if (count($all_vendors) > 0) {
                if (isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id'])) {
                    if(in_array($_REQUEST['user_id'],$all_vendors)){
                    $all_vendors = array($_REQUEST['user_id']);
                    }
                }
                $tax_query = [];
                if (isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id'])) {
                    $cat_id = array($_REQUEST['cat_id']);

                    $tax_query['tax_query'] = [
                        [
                            'taxonomy' => 'product_cat',
                            'terms' => $cat_id,
                            'include_children' => false // Remove if you need posts from term 7 child terms
                        ]
                    ];
                }
                $all_child_deals=[];
                if (isset($_REQUEST['deal_id']) && !empty($_REQUEST['deal_id'])) {
                    
                $result = $wpdb->get_col("SELECT  post_id from $wpdb->postmeta where meta_key='_vendor_product_parent' and meta_value!='' and meta_value='".$_REQUEST['deal_id']."' ");
                $all_child_deals=$result;
                }
                
                $args_total = array('author__in' => $all_vendors, 'post_type' => 'product', 'posts_per_page' => -1,'tax_query' => $tax_query);
                if(!empty($all_child_deals)){ 
                    $args_total['post__in']=$all_child_deals;
                }
                $all_posts = query_posts($args_total); 
                
                
                $per_page = BuyLockSmithDealsCustomizationAddon::get_record_limit();
                $total=count($all_posts);
                $total_items = $total;
                $total_pages = ceil($total / $per_page);
                $current_page = BuyLockSmithDealsCustomizationAddon::get_current_page();
                $offset = ($current_page - 1) * $per_page;
                
                $args = array('author__in' => $all_vendors, 'post_type' => 'product', 'posts_per_page' => $per_page,'offset'=>$offset, 'tax_query' => $tax_query);
                if(!empty($all_child_deals)){ 
                    $args['post__in']=$all_child_deals;
                }
                $posts = query_posts($args); 
                 if(isset($_REQUEST['export'])){
                     export_data($posts);
                }
                $this_page_posts=count($posts);
                
                if (count($posts) > 0) {
                    ?>
                        <tr>
                            <th >Sr no.</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Vendor name</th>
                        </tr>
                        <?php
                        $inc = 1;
                        $min_price=0;
                        $max_price=0;
                        $average_price=0;
                        foreach ($posts as $value) {
                            ?>

                            <tr class="item">
                                <td><?php echo $inc; ?></td>
                                <td>
                                    <?php
                                    $image_url = '';
                                    $image = wp_get_attachment_image_src(get_post_thumbnail_id($value->ID), 'thumbnail');
                                    $image_url = $image[0];
                                    if ($image_url == '') {
                                        $image_url = wc_placeholder_img_src('thumbnail');
                                    }
                                    ?>
                                    <a target="_blank" href="<?php echo get_edit_post_link($value->ID); ?>">   <img src="<?php echo $image_url; ?>" style="height: 100px"></a>
                                </td>


                                <td><a target="_blank" href="<?php echo get_edit_post_link($value->ID); ?>"> <?php echo $value->post_title; ?></a></td>

                <?php
                $price = get_post_meta($value->ID, '_price', true);

                $terms = get_the_terms($value->ID, 'product_type');
                $name = '';
                if (isset($terms[0]->name)) {
                    $name = $terms[0]->name;
                }

                if ($name == 'booking') {
                    $price = get_post_meta($value->ID, '_wc_booking_cost', true);
                }
                if ($price == '') {
                    $price = 0;
                }
                if($inc == 1){ 
                    $min_price=$price; 
                    $max_price=$price;
                    $average_price=$price;
                }
                else{
                    if($price<$min_price){ $min_price=$price; }
                    if($price>$max_price){ $max_price=$price; }
                    $average_price=$average_price+$price;
                }
                ?>
                <td><?php echo get_woocommerce_currency_symbol(get_option('woocommerce_currency')) . number_format($price, 2); ?></td>
                <td><?php echo ucfirst($value->post_status); ?></td>
                <td><a target="_blank" href="<?php echo get_edit_user_link($value->post_author); ?>"> <?php echo get_userdata($value->post_author)->display_name; ?></a></td>
                </tr>
                

                <?php
                $inc++;
            }
            echo '<div class="main-deals">';
            echo '<label class="deals-label">Total Deals: </label>'.$total;
            echo '<label class="deals-label" >This page Deals: </label>'.$this_page_posts;
            echo '<label class="deals-label">Min Price: </label>'.get_woocommerce_currency_symbol(get_option('woocommerce_currency')).$min_price;
            echo '<label class="deals-label">Max Price: </label>'.get_woocommerce_currency_symbol(get_option('woocommerce_currency')).$max_price;
            echo '<label class="deals-label" >Average Price: </label>'.get_woocommerce_currency_symbol(get_option('woocommerce_currency')).($average_price/$this_page_posts);
            echo '</div>';
             
            
                    } else {
                        ?>
                            <tr> <td> Deals not found  </td>    </tr>
                    <?php
                }
            } else {
                ?>
                            <tr> <td> Vendor not found  </td>    </tr>
                
                <?php
            }
            ?> </table> <?php
            echo BuyLockSmithDealsCustomizationAddon::render_pagination($current_url, $total_pages); 
           
        }
        ?>
    </form>
    
    <script src="<?php echo BUYLOCKSMITH_DEALS_ASSETS_PATH; ?>js/sorttable.js"></script>


</div>

<style>
    button.export {
    float: right;
}
.paginationCustom {
   margin: 12px;
}
label.deals-label:first-child {
    margin-left: 0;
}
label.deals-label {
    font-weight: 600;
    margin-left: 22px;
}
.main-deals {
    margin: 10px;
}
.clients_page_blsm-dispute-lists.sortable thead th:after{
color: #ccc;
content: "\f229";
font-family: dashicons;
transform: rotateZ(90deg) !important;
position: absolute;
margin-top: 2px;
}
.clients_page_blsm-dispute-lists.sortable thead th.sorttable_sorted:after{
color: #ccc;
content: "\f140";
font-family: dashicons;
transform: rotateZ(360deg) !important;
position: absolute;
margin-top: 2px;
font-size: 18px;
}
.clients_page_blsm-dispute-lists.sortable thead span#sorttable_sortfwdind,
.clients_page_blsm-dispute-lists.sortable thead span#sorttable_sortrevind {
    display: none;
}
    </style>
    <script>
        jQuery(document).ready(function(){
           jQuery('.class_selected').change(function(){
             jQuery('#cpage').removeAttr('name');
             jQuery('#cpage').val('');
           });
        });
        </script>
   <?php 
    function export_data($data)
    {
        $i=1;
        $post_data=[];
        foreach($data as $value){
            //$post_data['Sr no.'][]=$i;
           // $post_data['Title'][]=$value->post_title;
            $price = get_post_meta($value->ID, '_price', true);
            $terms = get_the_terms($value->ID, 'product_type');
            $name = '';
            if (isset($terms[0]->name)) {
                $name = $terms[0]->name;
            }
            if ($name == 'booking') {
                $price = get_post_meta($value->ID, '_wc_booking_cost', true);
            }
            if ($price == '') {
                $price = 0;
            }
            $currency=get_woocommerce_currency_symbol(get_option('woocommerce_currency'));
            $currency= html_entity_decode($currency, ENT_HTML5, 'utf-8');
            $final_price=   $currency.number_format($price, 2);
            //$post_data['Price'][]=$final_price;
            $vendor_name=get_userdata($value->post_author)->display_name;
            $status=ucfirst($value->post_status);
           // $post_data['Vendor name'][]=$vendor_name;
            $post_data[]=['Sr no.'=>$i,'Title'=>$value->post_title,'Price'=>$final_price,'Status'=>$status,'Vendor name'=>$vendor_name];
            $i++;
        }
        ob_end_clean();
        
            $fh = fopen( 'php://output', 'w' );
            $heading = false;
            $filename = "export-data".time().".csv";		 
            header("Content-Encoding: UTF-8");
            header("Content-type: text/csv; charset=UTF-8");
            header('Content-Disposition: attachment; filename='.$filename);
            //header("Content-Disposition: attachment; filename=\"$filename\"");
                if(!empty($post_data)){
		  foreach($post_data as $row) {
			if(!$heading) {
			  // output the column headings
			  fputcsv($fh, array_keys($row));
			  $heading = true;
			}
			// loop over the rows, outputting them
			 fputcsv($fh, array_values($row));
                    }
                }
           
            fclose($fh);
            
            //ExportCSVFile($post_data);
            //$_POST["ExportType"] = '';
            exit();
        
    
}
function ExportCSVFile($records) {
	// create a file pointer connected to the output stream
	$fh = fopen( 'php://output', 'w' );
	$heading = false;
		if(!empty($records))
		  foreach($records as $row) {
			if(!$heading) {
			  // output the column headings
			  fputcsv($fh, array_keys($row));
			  $heading = true;
			}
			// loop over the rows, outputting them
			 fputcsv($fh, array_values($row));
                    }
		  fclose($fh);
}

?>