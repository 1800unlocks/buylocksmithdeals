<?php global $WCMp,$wp; 
 $current_url=add_query_arg( NULL, NULL ) ;
 $posts_per_page= get_option('posts_per_page');
?>
<div class="wrap">
<h3>Vendors Products</h3>

<div class="container">
    <div class="row">
	<?php 
	$args = array(
	 'role' => 'dc_vendor',
	 'orderby' => 'user_nicename',
	 'order' => 'ASC'
	);
	 $all_vendors = get_users($args);
	?>
	<label>Select Vendors</label>
		<select name="vendor" id="vendor">
			<option value="">Select...</option>
			<?php foreach($all_vendors as $vendor){ ?>
				<option value="<?php echo $vendor->ID; ?>"><?php echo $vendor->display_name; ?></option>
			<?php } ?>
		</select>
		<input name="delete" id="delete" type="button" value="Delete" class="button action">
<br>
<br>
	<div class="vendor_products">
		<div class="vendor_products_table"></div>
	</div>
	</div>
</div>
<script>
jQuery(document).ready(function(){
 
	 jQuery('#delete').click(function(){
		var selected_vendor=jQuery('#vendor').val();
		var selected_products = jQuery("input[name='selected_products[]']:checkbox:checked")
              .map(function(){return jQuery(this).val();}).get();
	    var current_url='<?php echo $current_url; ?>';
		var posts_per_page='<?php echo $posts_per_page; ?>';
		var paged='1';
		if (confirm('Are you sure to delete that product')) {
			 jQuery.ajax({
				url: '<?php echo add_query_arg('action', 'blsd_vendor_products_delete', $WCMp->ajax_url()); ?>',
				type: "post",
				data: {products_ids: selected_products,vendor_id:selected_vendor,current_url:current_url,posts_per_page:posts_per_page,paged:paged},
				success: function (resultData) {
					jQuery('.vendor_products_table').html(resultData);
					jQuery("#vendor_product_table").addClass("wp-list-table widefat striped");
				}
			});
		}
	});

	jQuery('#vendor').change(function(){
		var selected_vendor=jQuery('#vendor').val();
		var current_url='<?php echo $current_url; ?>';
		var posts_per_page=<?php echo $posts_per_page; ?>;
		var paged=1;
		jQuery.ajax({
                    url: '<?php echo add_query_arg('action', 'blsd_get_vendor_products', $WCMp->ajax_url()); ?>',
                    type: "post",
                    data: {vendor_id: selected_vendor,current_url:current_url,posts_per_page:posts_per_page,paged:paged},
                    success: function (resultData) {
						jQuery('.vendor_products_table').html(resultData);
						jQuery("#vendor_product_table").addClass("wp-list-table widefat striped");
					}
			
		});
	});
	jQuery(document).on('click','.paginationCustom a',function(){
		
		var selected_vendor=jQuery('#vendor').val();
		var current_url='<?php echo $current_url; ?>';
		var posts_per_page=<?php echo $posts_per_page; ?>;
		var url=jQuery(this).attr('href');
		var paged=jQuery.urlParam('cpage',url);
		jQuery.ajax({
                    url: '<?php echo add_query_arg('action', 'blsd_get_vendor_products', $WCMp->ajax_url()); ?>',
                    type: "post",
                    data: {vendor_id: selected_vendor,current_url:current_url,posts_per_page:posts_per_page,paged:paged},
                    success: function (resultData) {
						jQuery('.vendor_products_table').html(resultData);
						jQuery("#vendor_product_table").addClass("wp-list-table widefat striped");
					}
			
		});
		return false;
	});
	
	jQuery.urlParam = function(name,url){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
    if (results==null) {
       return null;
    }
    return decodeURI(results[1]) || 0;
}
	});
</script>
<style>
    .button-submit {
    border-bottom: none !important;
}
    form#car-key-type {
        background: #fff;
        padding: 10px;
    }
    form#locks_example {
        background: #fff;
        padding: 10px;
        margin-top: 25px;
    }
    input#tibbe_image {
        margin-left: 45px;
    }
    input#vats_image {
        margin-left: 25px;
    }
    input#locks_image {
        margin-left: 125px;
    }
    #car-key-type label {
        margin-right: 84px;
    }
    input#edge_cut_image {
        margin-left: 25px;
    }
    
    form#car-key-type .form-control {
    padding: 10px 0;
    border-bottom: 1px solid #d3d3d3;
    } 
    form#locks_example .form-control {
    padding: 10px 0;
    border-bottom: 1px solid #d3d3d3;
    } 

form#car-key-type .form-control img {
    vertical-align: bottom;
}
input#edge_cut_image {
    margin-left: 0;
}

#car-key-type label {
    margin-right: 84px;
    vertical-align: top;
}

form#car-key-type .form-control input {
    vertical-align: top;
}
    </style>
	</div>