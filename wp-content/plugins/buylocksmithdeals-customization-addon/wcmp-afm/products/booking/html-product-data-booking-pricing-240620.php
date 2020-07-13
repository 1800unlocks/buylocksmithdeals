<?php
/**
 * Booking Cost product tab template
 *
 * Used by WCMp_AFM_Booking_Integration->booking_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-product-data-booking-pricing.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/booking
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
global $wpdb;
$post_id= $id;
$product_parent= get_post_meta($post_id,'_vendor_product_parent',true);
if(!empty($product_parent)){
	$post_parent_id=$product_parent;
}
$show_unserviceable_cars = get_post_meta($post_parent_id,'show_unserviceable_cars',true);
$where_need_service = get_post_meta($post_parent_id,'where_need_service',true);
$quantity_of_locks = get_post_meta($post_parent_id,'quantity_of_locks',true);
$house_keys_included = get_post_meta($post_parent_id,'house_keys_included',true);
$car_key_look_like = get_post_meta($post_parent_id,'car_key_look_like',true);
$want_more_than_one_key = get_post_meta($post_parent_id,'want_more_than_one_key',true);
$who_supplies_deadbolts = get_post_meta( $post_parent_id, 'who_supplies_deadbolts', true );
$type_of_installation = get_post_meta( $post_parent_id, 'type_of_installation', true );
$quantity_of_locks_install = get_post_meta( $post_parent_id, 'quantity_of_locks_install', true );
$deal_type = get_post_meta( $post_parent_id, 'deal_type', true );
$is_mobile_service = get_post_meta($post_parent_id, 'is_mobile_service', true);
$is_vats_programming = get_post_meta($post_parent_id, 'is_vats_programming', true);
$quantity_of_door_locks = get_post_meta($post_parent_id, 'quantity_of_door_locks', true);
$quantity_of_car_unlocks = get_post_meta($post_parent_id, 'quantity_of_car_unlocks', true);
$result=[];
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
	
		<!--------SERVICE CALL PRICING------->
		<?php if($where_need_service == 'yes'){ 
				$addon=get_post_meta($post_id, '_product_addons', true);
				$default_miles=  get_post_meta($post_id, 'default_miles', true);
				$extra_permile_price= get_post_meta($post_id, 'extra_permile_price', true);
				$maximum_miles= get_post_meta($post_id, 'maximum_miles', true);
		?>
		<div class="form-group-row service_call_pricing"> 
			<h2>Service Call Pricing</h2>
			<span><small>Fill out if you want to charge a service call fee to drive to the customers location.</small></span>
			<div class="form-group">
			<div class="select_option">
				<input type="hidden" name="service_title" id="title" value="Where do you need service?"> 
				<div>
					<table class="need_service">
						<thead>
							<tr>
						<th scope="col">Service call Price</th>
						<th scope="col">Normal Service Area(Miles)</th>
						<th scope="col">Extra Per Mile Price</th>
						<th scope="col">Maximum Travel Miles</th>
							</tr>
					</thead>
						<tr>
							<!-- <td scope="row"  data-label="label">
								<input type="text" readonly name="label_my_location" id="label" value="<?php // if(isset($addon[0]['options'][0]['label'])){ echo $addon[0]['options'][0]['label']; } else{ echo "Customer's Location"; }  ?>" placeholder="Customer's Location" >
							</td> -->
							<td data-label="Service call Price">
							  <input type="number" name="price_my_location" id="price_my_location" value="<?php if(isset($addon[0]['options'][0]['price'])){ echo $addon[0]['options'][0]['price']; }   ?>" min="0" >
							</td>
							<td data-label="Normal Service Area(Miles)">
							  <input type="number" name="default_miles" id="default_miles" value="<?php echo $default_miles; ?>" min="0" >
							</td>
							<td data-label="Extra Per Mile Price">
							  <input type="number" name="extra_permile_price" id="extra_permile_price" value="<?php echo $extra_permile_price; ?>" min="0" >
							</td>
							<td data-label="Maximum Travel Miles">
							  <input type="number" name="maximum_miles" id="maximum_miles" value="<?php echo $maximum_miles; ?>" min="0" >
							</td>
						</tr>
					</table>
				</div>
            </div>
			</div>
		</div>
		<?php } ?>
		<!-------END SERVICE CALL PRICING--------->
		
		<!-------MOBILE LOCKSMITH----------->
		<?php if($is_mobile_service == 'yes'){
			$mobile_locksmith= get_post_meta($post_id, 'mobile_locksmith', true);
		    $mobile_locksmith_address= get_post_meta($post_id, 'mobile_locksmith_address', true);
			if($mobile_locksmith == 'no' || $mobile_locksmith ==''){
			   $lock_service_area='disabled';
			}
			else{
				$lock_service_area='';
			}
			
		?>
		<div class="form-group-row mobile_locksmith"> 
			<h2>Is This Deal Only Offered At The Customer’s Location?</h2>
			<span><small>If you are a mobile-only locksmith, always click this. If selected, customers will only have the option for service at their location for this
deal. Customers will not be able to choose service at your office location. Therefore, the customer will always pay the service call price.</small></span> 
			<div class="form-group">
                    <input type="checkbox" class="form-control" id="mobile_locksmith" name="mobile_locksmith" <?php if($mobile_locksmith == 'yes'){ echo 'checked'; } ?> value="1">
                    <span>I will only offer mobile service for this deal.</span>
			</div>
			<!--<div class="form-group <?php // echo $lock_service_area; ?>" id="mob_lock_service_area">
                <label class="control-label col-sm-3 col-md-3" for="mobile_locksmith_address"><?php echo __( 'Enter your mobile locksmith service area', 'woocommerce-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="text" id="mobile_locksmith_address" name="mobile_locksmith_address" value="<?php if(isset($mobile_locksmith_address) && !empty($mobile_locksmith_address)){ echo $mobile_locksmith_address; } ?>" class="form-control">
                </div>
            </div> -->
		</div>
		<?php } ?>
		<!-------END MOBILE LOCKSMITH------->
		
		<!----------START deal_pricing----------->
        <div class="form-group-row deal_pricing"> 
		<h2>Deal Pricing For Customer</h2>
		<span><small>You can choose what is included and the pricing. Depending on what the customer selects, this amount will be combined with the
additional pricing amounts to calculate the grand total. Any material fees are not included. Once the customer has paid, you can call
them to provide pricing on materials. Pricing should be inclusive of any applicable taxes.</small></span>
            <!--<div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_cost"><?php echo __( 'Base Deal Price', 'woocommerce-bookings' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step=".01" id="_wc_booking_cost" name="_wc_booking_cost" value="<?php esc_attr_e( $bookable_product->get_cost( 'edit' ) ); ?>" class="form-control">
                </div>
            </div> -->
            <?php // do_action( 'afm_bookings_after_booking_base_cost', $id ); ?>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_block_cost"><?php echo __( 'Labor Price For The Deal', 'woocommerce-bookings' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step=".01" id="_wc_booking_block_cost" name="_wc_booking_block_cost" value="<?php esc_attr_e( $bookable_product->get_block_cost( 'edit' ) ); ?>" class="form-control">
                </div>
            </div>
            <?php do_action( 'afm_bookings_after_booking_block_cost', $id ); ?>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_display_cost"><?php echo __( 'Starting At Price For Marketing Purposes', 'woocommerce-bookings' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step=".01" id="_wc_display_cost" name="_wc_display_cost" value="<?php esc_attr_e( $bookable_product->get_display_cost( 'edit' ) ); ?>" class="form-control">
					<small>(It has no effect on pricing. It will display in the store. I.E. Starting at <?php echo get_woocommerce_currency_symbol(); ?>15)</small>
                </div>
            </div>
			<?php do_action( 'afm_bookings_after_display_cost', $id ); ?>
        </div>
		
		<!------------END deal_pricing---------------->
		
		<?php 
		if($deal_type == 'deadbolt'){
			if($quantity_of_locks_install == 'yes'){ 
			$quantity_customer_fresh_install_deadbolt=get_post_meta($post_id, 'quantity_customer_fresh_install_deadbolt', true);
			$quantity_customer_replaced_deadbolt=get_post_meta($post_id, 'quantity_customer_replaced_deadbolt', true);
			$quantity_locksmith_fresh_install_deadbolt=get_post_meta($post_id, 'quantity_locksmith_fresh_install_deadbolt', true);
			$quantity_locksmith_replaced_deadbolt=get_post_meta($post_id, 'quantity_locksmith_replaced_deadbolt', true);
			
			$customer_fresh_install_deadbolt=get_post_meta($post_id, 'customer_fresh_install_deadbolt', true);
			$customer_replaced_deadbolt=get_post_meta($post_id, 'customer_replaced_deadbolt', true);
			$locksmith_fresh_install_deadbolt=get_post_meta($post_id, 'locksmith_fresh_install_deadbolt', true);
			$locksmith_replaced_deadbolt=get_post_meta($post_id, 'locksmith_replaced_deadbolt', true);
			?>
				<div class="form-group-row"> 
					<h2>What’s Included In This Deal</h2>
					<span><small>Indicate what the customer gets for the above dollar amounts. Enter quantity of each type of deadbolt and installation type you will
install for the above labor plus service call price. Depending on what customer selects, the system will calculate only that amount.</small></span> 
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="quantity_customer_fresh_install_deadbolt"><?php echo __( 'Included Quantity of Fresh-Installation Of Each Customer Supplied Deadbolt', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="quantity_customer_fresh_install_deadbolt" name="quantity_customer_fresh_install_deadbolt" value="<?php if(isset($quantity_customer_fresh_install_deadbolt) && !empty($quantity_customer_fresh_install_deadbolt)){ echo $quantity_customer_fresh_install_deadbolt; } ?>" class="form-control">
							</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="quantity_locksmith_fresh_install_deadbolt"><?php echo __( 'Included Quantity of Fresh-Installation Of Each Locksmith Supplied Deadbolt', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="quantity_locksmith_fresh_install_deadbolt" name="quantity_locksmith_fresh_install_deadbolt" value="<?php if(isset($quantity_locksmith_fresh_install_deadbolt) && !empty($quantity_locksmith_fresh_install_deadbolt)){ echo $quantity_locksmith_fresh_install_deadbolt; } ?>" class="form-control">
							</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="quantity_customer_replaced_deadbolt"><?php echo __( 'Included Quantity of Replacement Installation of Each Customer Supplied Deadbolt', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="quantity_customer_replaced_deadbolt" name="quantity_customer_replaced_deadbolt" value="<?php if(isset($quantity_customer_replaced_deadbolt) && !empty($quantity_customer_replaced_deadbolt)){ echo $quantity_customer_replaced_deadbolt; } ?>" class="form-control">
							</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="quantity_locksmith_replaced_deadbolt"><?php echo __( 'Included Quantity of Replacement Installation of Each Locksmith Supplied Deadbolt', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="quantity_locksmith_replaced_deadbolt" name="quantity_locksmith_replaced_deadbolt" value="<?php if(isset($quantity_locksmith_replaced_deadbolt) && !empty($quantity_locksmith_replaced_deadbolt)){ echo $quantity_locksmith_replaced_deadbolt; } ?>" class="form-control">
							</div>
					</div>
				</div>
		
		<div class="form-group-row"> 
			<h2>Additional Deal Pricing For Customer</h2>
			<span><small>If the customer needs more work than what is included in the deal, this will offer customers the ability to indicate what they want done.
Any material fees are not included. Once the customer has paid, you can call them to provide pricing on materials. Pricing should be
inclusive of any applicable taxes.</small></span> 
			<div class="form-group">
				<label class="control-label col-sm-3 col-md-3" for="customer_fresh_install_deadbolt"><?php echo __( 'Additional Fresh-Installation Of Each Customer Supplied Deadbolt Price ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step="1" id="customer_fresh_install_deadbolt" name="customer_fresh_install_deadbolt" value="<?php if(isset($customer_fresh_install_deadbolt) && !empty($customer_fresh_install_deadbolt)){ echo $customer_fresh_install_deadbolt; } ?>" class="form-control">
                </div>
            </div>
			<div class="form-group">
				<label class="control-label col-sm-3 col-md-3" for="customer_replaced_deadbolt"><?php echo __( 'Additional Replacement Installation of Each Customer Supplied Deadbolt Price ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step="1" id="customer_replaced_deadbolt" name="customer_replaced_deadbolt" value="<?php if(isset($customer_replaced_deadbolt) && !empty($customer_replaced_deadbolt)){ echo $customer_replaced_deadbolt; } ?>" class="form-control">
                </div>
            </div>
			<div class="form-group">
				<label class="control-label col-sm-3 col-md-3" for="locksmith_fresh_install_deadbolt"><?php echo __( 'Additional Fresh-Installation Of Each Locksmith Supplied Deadbolt Price ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step="1" id="locksmith_fresh_install_deadbolt" name="locksmith_fresh_install_deadbolt" value="<?php if(isset($locksmith_fresh_install_deadbolt) && !empty($locksmith_fresh_install_deadbolt)){ echo $locksmith_fresh_install_deadbolt; } ?>" class="form-control">
                </div>
            </div>
			<div class="form-group">
				<label class="control-label col-sm-3 col-md-3" for="locksmith_replaced_deadbolt"><?php echo __( 'Additional Replacement Installation of Each Locksmith Supplied Deadbolt Prices ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step="1" id="locksmith_replaced_deadbolt" name="locksmith_replaced_deadbolt" value="<?php if(isset($locksmith_replaced_deadbolt) && !empty($locksmith_replaced_deadbolt)){ echo $locksmith_replaced_deadbolt; } ?>" class="form-control">
                </div>
            </div>

		</div>
		<?php }			
		}
		else if($deal_type == 'lock_rekeying'){
			if($house_keys_included == 'yes' || $quantity_of_locks == 'yes'){ ?>
			<div class="form-group-row"> 
				<h2>What’s Included In This Deal</h2>
				<span><small>Indicate what the customer gets for the above dollar amounts. Enter quantity of locks you will rekey and keys you will supply for the
	above labor plus service call price. Depending on what customer selects, the system will calculate only that amount.</small></span> 
				<?php
				if($house_keys_included == 'yes'){
					$house_keys_included_deal=get_post_meta($post_id, 'house_keys_included_deal', true);
					?>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="house_keys_included_deal"><?php echo __( 'Included Quantity of Standard Keys You Will Supply', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="house_keys_included_deal" name="house_keys_included_deal" value="<?php if(isset($house_keys_included_deal) && !empty($house_keys_included_deal)){ echo $house_keys_included_deal; } ?>" class="form-control">
							</div>
					</div>
					<?php
				} 
				if($quantity_of_locks == 'yes'){ 
					$cylinders_included=get_post_meta($post_id, 'cylinders_included', true);
				?>
					<div class="form-group">
							<label class="control-label col-sm-3 col-md-3" for="cylinders_included"><?php echo __( 'Included Quantity of Standard Cylinders You Will Rekey', 'woocommerce-bookings' ); ?></label>
								<div class="col-md-6 col-sm-9">
									<input type="number" min="0" step="1" id="cylinders_included" name="cylinders_included" value="<?php if(isset($cylinders_included) && !empty($cylinders_included)){ echo $cylinders_included; } ?>" class="form-control">
								</div>
					</div>
			<?php } ?>
		</div>
		<div class="form-group-row"> 
			<h2>Additional Deal Pricing For Customer</h2>
			<span><small>If the customer needs more work than what is included in the deal, this will offer customers the ability to indicate what they want done.
Any material fees are not included. Once the customer has paid, you can call them to provide pricing on materials. Pricing should be
inclusive of any applicable taxes.</small></span> 
			<?php
				if($house_keys_included == 'yes'){
					$extra_per_house_keys_included_price=get_post_meta($post_id, 'extra_per_house_keys_included_price', true);
				
					?>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="extra_per_house_keys_included_price"><?php echo __( 'Additional Standard Key Duplication Price Per Extra Key ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="extra_per_house_keys_included_price" name="extra_per_house_keys_included_price" value="<?php if(isset($extra_per_house_keys_included_price) && !empty($extra_per_house_keys_included_price)){ echo $extra_per_house_keys_included_price; } ?>" class="form-control">
							</div>
					</div>
					<?php
				} 
				if($quantity_of_locks == 'yes'){ 
					$extra_per_cylinders_included_price=get_post_meta($post_id, 'extra_per_cylinders_included_price', true);
				?>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="extra_per_cylinders_included_price"><?php echo __( 'Additional Standard Lock Rekeying Price Per Extra Cylinder ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="extra_per_cylinders_included_price" name="extra_per_cylinders_included_price" value="<?php if(isset($extra_per_cylinders_included_price) && !empty($extra_per_cylinders_included_price)){ echo $extra_per_cylinders_included_price; } ?>" class="form-control">
							</div>
					</div>
			<?php } ?>
			
			
		</div>
		<?php
			}		
			
		}
		else if($deal_type == 'home_lockout'){
			if($quantity_of_door_locks == 'yes'){ ?>
				<div class="form-group-row"> 
					<h2>What’s Included In This Deal</h2>
					<span><small>This deal will only includes 1 house unlock. This is hard coded for this deal and cannot be changed.</small></span> 
					<div class="form-group">
					<?php $quantity_of_door_locks_unlocks=get_post_meta($post_id, 'quantity_of_door_locks_unlocks', true); ?>
						<label class="control-label col-sm-3 col-md-3" for="quantity_of_door_locks_unlocks"><?php echo __( 'Included quantity of door locks you will unlock', 'woocommerce-bookings' ); ?></label>
						<div class="col-md-6 col-sm-9">
							<input type="number" readonly min="0" step="1" id="quantity_of_door_locks_unlocks" name="quantity_of_door_locks_unlocks" value="<?php if(isset($quantity_of_door_locks_unlocks) && !empty($quantity_of_door_locks_unlocks)){ echo $quantity_of_door_locks_unlocks; } else { echo '1'; } ?>" class="form-control">
						</div>
					</div>
				</div>
				
				<!-- <div class="form-group-row"> 
					<h2>Additional Deal Pricing For Customer</h2>
					<span><small>If the customer needs more work than what is included in the deal, this will offer customers the ability to indicate what they want done.
Any material fees are not included. Pricing should be inclusive of any applicable taxes.</small></span> 
					<div class="form-group">
					<?php // $price_of_additional_door_locks_unlocks=get_post_meta($post_id, 'price_of_additional_door_locks_unlocks', true); ?>
						<label class="control-label col-sm-3 col-md-3" for="price_of_additional_door_locks_unlocks"><?php echo __( 'Additional lock unlocking price per extra lock', 'woocommerce-bookings' ); ?></label>
						<div class="col-md-6 col-sm-9">
							<input type="number" min="0" step="1" id="price_of_additional_door_locks_unlocks" name="price_of_additional_door_locks_unlocks" value="<?php if(isset($price_of_additional_door_locks_unlocks) && !empty($price_of_additional_door_locks_unlocks)){ echo $price_of_additional_door_locks_unlocks; } ?>" class="form-control">
						</div>
					</div>
				</div>	-->
		<?php }
		} 
		else if($deal_type == 'car_lockout'){ 
			 if($show_unserviceable_cars == 'yes'){
				$result=[];
				$unserviceable_cars=get_post_meta($post_id,'unserviceable_cars',true);
				$table_name=BuyLockSmithDealsCustomizationAddon::blsd_y_m_model_table_name();
				$result=$wpdb->get_results("Select DISTINCT maker,model,year,id from $table_name",ARRAY_A);
			?>
			<div class="form-group-row"> 
				<h2>Select Unserviceable Cars</h2>
				<span><small>Type the Make or Model of cars you do not service. Therefore, these will not display to customers in your store.</small></span> 
				<div class="form-group">
				<input type="text" name="unserviceable_cars" id="unserviceable_cars" value="<?php  echo implode(',',$unserviceable_cars) ?>" >
                 
				</div>              
			</div>
			<?php } 
			if($quantity_of_car_unlocks == 'yes'){
			 ?>
				<div class="form-group-row"> 
					<h2>What’s Included In This Deal</h2>
					<span><small>This deal will only includes 1 car unlock. This is hard coded for this deal and cannot be changed.</small></span> 
					<div class="form-group">
					<?php $quantity_of_cars_unlocks=get_post_meta($post_id, 'quantity_of_cars_unlocks', true); ?>
						<label class="control-label col-sm-3 col-md-3" for="quantity_of_cars_unlocks"><?php echo __( 'Included quantity of cars you will unlock', 'woocommerce-bookings' ); ?></label>
						<div class="col-md-6 col-sm-9">
							<input type="number" readonly min="0" step="1" id="quantity_of_cars_unlocks" name="quantity_of_cars_unlocks" value="<?php if(isset($quantity_of_cars_unlocks) && !empty($quantity_of_cars_unlocks)){ echo $quantity_of_cars_unlocks; } else { echo '1'; } ?>" class="form-control">
						</div>
					</div>
				</div>
				
				<!-- <div class="form-group-row"> 
					<h2>Additional Deal Pricing For Customer</h2>
					<span><small>If the customer needs more work than what is included in the deal, this will offer customers the ability to indicate what they want done.
Any material fees are not included. Pricing should be inclusive of any applicable taxes.</small></span> 
					<div class="form-group">
					<?php // $price_of_additional_car_unlocks=get_post_meta($post_id, 'price_of_additional_car_unlocks', true); ?>
						<label class="control-label col-sm-3 col-md-3" for="price_of_additional_car_unlocks"><?php // echo __( 'Additional car unlocking price per extra car', 'woocommerce-bookings' ); ?></label>
						<div class="col-md-6 col-sm-9">
							<input type="number" min="0" step="1" id="price_of_additional_car_unlocks" name="price_of_additional_car_unlocks" value="<?php // if(isset($price_of_additional_car_unlocks) && !empty($price_of_additional_car_unlocks)){ echo $price_of_additional_car_unlocks; } ?>" class="form-control">
						</div>
					</div>
				</div>	-->
		<?php 
			}
		}
		else if($deal_type == 'car_key_programming'){ 
			if($is_vats_programming == 'yes'){ 
		?>
			<div class="form-group-row"> 
				<?php $works_on_vats_car=  get_post_meta($post_id, 'works_on_vats_car', true); ?>
				<h2>VATS Programming Question</h2>
				<span><small>If selected, customers will not be able to choose any cars with the VATS systems in your store.</small></span> 
				<div class="form-group">
					<input type="checkbox" class="form-control" id="works_on_vats_car" name="works_on_vats_car" <?php if($works_on_vats_car == 'yes'){ echo 'checked'; } ?> value="1">
						<span>I do not work on cars with VATS.</span>
				</div>
			</div>
			<?php } 
			if($show_unserviceable_cars == 'yes'){
				
				$unserviceable_cars=get_post_meta($post_id,'unserviceable_cars',true);
				$table_name=BuyLockSmithDealsCustomizationAddon::blsd_y_m_model_table_name();
				$result=$wpdb->get_results("Select DISTINCT maker,model,year,id from $table_name",ARRAY_A);
			?>
			<div class="form-group-row"> 
				<h2>Select Unserviceable Cars</h2>
				<span><small>Type the Make or Model of cars you do not service. Therefore, these will not display to customers in your store.</small></span> 
				<div class="form-group">
				
				<input type="text" name="unserviceable_cars" id="unserviceable_cars" value="<?php  echo implode(',',$unserviceable_cars) ?>" >
                            
				</div>              
			</div>
			<?php } 
			if($car_key_look_like == 'yes'){ 
					
					$quantity_double_sided_car_key=get_post_meta($post_id, 'quantity_double_sided_car_key', true);
					$quantity_high_security_car_key=get_post_meta($post_id, 'quantity_high_security_car_key', true);
					$quantity_tibbe_car_key=get_post_meta($post_id, 'quantity_tibbe_car_key', true);
					$quantity_car_key_programmed=get_post_meta($post_id, 'quantity_car_key_programmed', true);
					$quantity_vats_car_key_cut=get_post_meta($post_id, 'quantity_vats_car_key_cut', true);
					$quantity_vats_car_key_programmed=get_post_meta($post_id, 'quantity_vats_car_key_programmed', true);
					
					$car_programming_fee=  get_post_meta($post_id, 'car_programming_fee', true);
                    $car_vats_programming_fee=  get_post_meta($post_id, 'car_vats_programming_fee', true);
					$additional_double_sided_car_key_price=get_post_meta($post_id, 'additional_double_sided_car_key_price', true);
					$additional_high_security_car_key_price=get_post_meta($post_id, 'additional_high_security_car_key_price', true);
					$additional_tibbe_car_key_price=get_post_meta($post_id, 'additional_tibbe_car_key_price', true);
					$additional_car_key_programmed_price=get_post_meta($post_id, 'additional_car_key_programmed_price', true);
					$additional_vats_car_key_cut_price=get_post_meta($post_id, 'additional_vats_car_key_cut_price', true);
					$additional_vats_car_key_programmed_price=get_post_meta($post_id, 'additional_vats_car_key_programmed_price', true);
					
					$file_edge_cut=get_option('file_edge_cut');
					$file_high_security=get_option('file_high_security');
					$file_tibbe= get_option('file_tibbe');
					$file_vats= get_option('file_vats');
					$target_dir_img = WP_PLUGIN_URL  .'/'.BUYLOCKSMITH_DEALS_BASE_FOLDER_NAME . "/uploads/";
			?>
				<div class="form-group-row"> 
					<h2>What’s Included In This Deal</h2>
					<span><small>Indicate what the customer gets for the above dollar amounts. Enter quantity of each type of car key you will cut and program for the
above labor plus service call price. Depending on what customer selects, the system will calculate only that amount.</small></span> 
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="quantity_double_sided_car_key"><?php echo __( 'Included Quantity of Double-Sided Car Keys Cut', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="quantity_double_sided_car_key" name="quantity_double_sided_car_key" value="<?php if(isset($quantity_double_sided_car_key) && !empty($quantity_double_sided_car_key)){ echo $quantity_double_sided_car_key; } ?>" class="form-control">
							</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="quantity_high_security_car_key"><?php echo __( 'Included Quantity of High-Security Car Keys Cut', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="quantity_high_security_car_key" name="quantity_high_security_car_key" value="<?php if(isset($quantity_high_security_car_key) && !empty($quantity_high_security_car_key)){ echo $quantity_high_security_car_key; } ?>" class="form-control">
							</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="quantity_tibbe_car_key"><?php echo __( 'Included Quantity of Tibbe Car Key(s) Cut', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="quantity_tibbe_car_key" name="quantity_tibbe_car_key" value="<?php if(isset($quantity_tibbe_car_key) && !empty($quantity_tibbe_car_key)){ echo $quantity_tibbe_car_key; } ?>" class="form-control">
							</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="quantity_vats_car_key_cut"><?php echo __( 'Included Quantity of VATS Car Key(s) Cut', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="quantity_vats_car_key_cut" name="quantity_vats_car_key_cut" value="<?php if(isset($quantity_vats_car_key_cut) && !empty($quantity_vats_car_key_cut)){ echo $quantity_vats_car_key_cut; } ?>" class="form-control">
							</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="quantity_car_key_programmed"><?php echo __( 'Included Quantity of Car Key(s) Programmed', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="quantity_car_key_programmed" name="quantity_car_key_programmed" value="<?php if(isset($quantity_car_key_programmed) && !empty($quantity_car_key_programmed)){ echo $quantity_car_key_programmed; } ?>" class="form-control">
							</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="quantity_vats_car_key_programmed"><?php echo __( 'Included Quantity of VATS Car Key(s) Programmed', 'woocommerce-bookings' ); ?></label>
							<div class="col-md-6 col-sm-9">
								<input type="number" min="0" step="1" id="quantity_vats_car_key_programmed" name="quantity_vats_car_key_programmed" value="<?php if(isset($quantity_vats_car_key_programmed) && !empty($quantity_vats_car_key_programmed)){ echo $quantity_vats_car_key_programmed; } ?>" class="form-control">
							</div>
					</div>
				</div>	
				<div class="form-group-row"> 
					<h2>Additional Deal Pricing For Customer</h2>
					<span><small>If the customer needs more work than what is included in the deal, this will offer customers the ability to indicate what they want done.
Any material fees are not included. Once the customer has paid, you can call them to provide pricing on materials. Pricing should be
inclusive of any applicable taxes.</small></span> 
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="additional_double_sided_car_key_price"><?php echo __( 'Additional Double-Sided Car Key Cutting Price ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
						<div class="col-md-6 col-sm-9">
							<input type="number" min="0" step="1" id="additional_double_sided_car_key_price" name="additional_double_sided_car_key_price" value="<?php if(isset($additional_double_sided_car_key_price) && !empty($additional_double_sided_car_key_price)){ echo $additional_double_sided_car_key_price; } ?>" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="additional_high_security_car_key_price"><?php echo __( 'Additional High-Security Car Key Cutting Price ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
						<div class="col-md-6 col-sm-9">
							<input type="number" min="0" step="1" id="additional_high_security_car_key_price" name="additional_high_security_car_key_price" value="<?php if(isset($additional_high_security_car_key_price) && !empty($additional_high_security_car_key_price)){ echo $additional_high_security_car_key_price; } ?>" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="additional_tibbe_car_key_price"><?php echo __( 'Additional Tibbe Car Key Cutting Price ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
						<div class="col-md-6 col-sm-9">
							<input type="number" min="0" step="1" id="additional_tibbe_car_key_price" name="additional_tibbe_car_key_price" value="<?php if(isset($additional_tibbe_car_key_price) && !empty($additional_tibbe_car_key_price)){ echo $additional_tibbe_car_key_price; } ?>" class="form-control">
						</div>
					</div>
					
					<!--<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="car_programming_fee"><?php echo __( 'Car Programming Price ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
						<div class="col-md-6 col-sm-9">
							<input type="number" min="0" step="1" id="car_programming_fee" name="car_programming_fee" value="<?php if(isset($car_programming_fee) && !empty($car_programming_fee)){ echo $car_programming_fee; } ?>" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="car_vats_programming_fee"><?php echo __( 'Cost to do VATS programming ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
						<div class="col-md-6 col-sm-9">
							<input type="number" min="0" step="1" id="car_vats_programming_fee" name="car_vats_programming_fee" value="<?php if(isset($car_vats_programming_fee) && !empty($car_vats_programming_fee)){ echo $car_vats_programming_fee; } ?>" class="form-control">
						</div>
					</div> -->
					
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="additional_vats_car_key_cut_price"><?php echo __( 'Additional VATS Car Key Cutting Price ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
						<div class="col-md-6 col-sm-9">
							<input type="number" min="0" step="1" id="additional_vats_car_key_cut_price" name="additional_vats_car_key_cut_price" value="<?php if(isset($additional_vats_car_key_cut_price) && !empty($additional_vats_car_key_cut_price)){ echo $additional_vats_car_key_cut_price; } ?>" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="additional_car_key_programmed_price"><?php echo __( 'Additional Car Key Programming Price ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
						<div class="col-md-6 col-sm-9">
							<input type="number" min="0" step="1" id="additional_car_key_programmed_price" name="additional_car_key_programmed_price" value="<?php if(isset($additional_car_key_programmed_price) && !empty($additional_car_key_programmed_price)){ echo $additional_car_key_programmed_price; } ?>" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3 col-md-3" for="additional_vats_car_key_programmed_price"><?php echo __( 'Additional VATS Car Key Programming Price ('.get_woocommerce_currency_symbol().')', 'woocommerce-bookings' ); ?></label>
						<div class="col-md-6 col-sm-9">
							<input type="number" min="0" step="1" id="additional_vats_car_key_programmed_price" name="additional_vats_car_key_programmed_price" value="<?php if(isset($additional_vats_car_key_programmed_price) && !empty($additional_vats_car_key_programmed_price)){ echo $additional_vats_car_key_programmed_price; } ?>" class="form-control">
						</div>
					</div>
					
				</div>
				
			<?php }
		
			}
		?>
		
		<!-------DISCOUNT------->
		<div class="form-group-row"> 
			<h2>Offer Discounts To Customer</h2>
			<span><small>This is optional.</small></span> 
			<div class="form-group">
			<?php $discount_on_deal=get_post_meta($post_id, 'discount_on_deal', true); ?>
                <label class="control-label col-sm-3 col-md-3" for="discount_on_deal"><?php echo __( 'Enter Discount Percentage on the entire deal value (%)', 'woocommerce-bookings' ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step="1" id="discount_on_deal" name="discount_on_deal" value="<?php if(isset($discount_on_deal) && !empty($discount_on_deal)){ echo $discount_on_deal; } ?>" class="form-control">
                </div>
            </div>
		</div>
		<!-------END DISCOUNT------->
		
		
	<!-----------END CUSTOM PRICING------->
		
		
        <!-- <div class="form-group-row"> 
            <div class="form-group">
                <div class="col-md-12">
                    <div class="booking_range_pricing">
                        <table class="table table-outer-border">
                            <thead>
                                <tr>
                                    <th class="sort" width="1%">&nbsp;</th>
                                    <th><?php esc_html_e( 'Range type', 'woocommerce-bookings' ); ?></th>
                                    <th><?php esc_html_e( 'Range', 'woocommerce-bookings' ); ?></th>
                                    <th></th>
                                    <th></th>
                                    <th><?php esc_html_e( 'Base Service Price', 'woocommerce-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_attr_e( 'Enter a cost for this rule. Applied to the booking as a whole.', 'woocommerce-bookings' ); ?>">[?]</a></th>
                                    <th><?php esc_html_e( 'Labor Price', 'woocommerce-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_attr_e( 'Enter a cost for this rule. Applied to each booking block.', 'woocommerce-bookings' ); ?>">[?]</a></th>
                                    <th class="remove" width="1%">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody id="pricing_rows">
                                <?php
                                $values = $bookable_product->get_pricing( 'edit' );
                                if ( ! empty( $values ) && is_array( $values ) ) {
                                    foreach ( $values as $index => $pricing ) {
                                        include( 'html-booking-range-pricing.php' );

                                        /**
                                         * Fired just after pricing fields are rendered.
                                         *
                                         * @since 1.7.4
                                         *
                                         * @param array $pricing {
                                         * The pricing details for bookings
                                         *
                                         * @type string $type          The booking range type
                                         * @type string $from          The start value for the range
                                         * @type string $to            The end value for the range
                                         * @type string $modifier      The arithmetic modifier for block cost
                                         * @type string $cost          The booking block cost
                                         * @type string $base_modifier The arithmetic modifier for base cost
                                         * @type string $base_cost     The base cost
                                         * }
                                         */
                                        do_action( 'afm_bookings_pricing_fields', $pricing );
                                    }
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="9">
                                        <a href="#" class="btn btn-default insert" data-row="<?php
                                        ob_start();
                                        include( 'html-booking-range-pricing.php' );
                                        $html = ob_get_clean();
                                        echo esc_attr( $html );
                                        ?>"><?php esc_html_e( 'Add Range', 'woocommerce-bookings' ); ?></a>
                                        <span class="description"><?php esc_html_e( 'All matching rules will be applied to the booking.', 'woocommerce-bookings' ); ?></span>
                                    </th>
                                </tr>
                            </tfoot> 
                        </table>
                    </div>  
                </div>
            </div>
        </div> -->
        <?php do_action( 'afm_bookings_after_bookings_pricing', $id ); ?>
    </div>
</div>
<?php if(!empty($result)){ ?>
<script>
jQuery(document).ready(function(){
	var car_data=JSON.parse('<?php echo json_encode($result) ?>');
	console.log(mockData(),'mockData()');
function mockData() {
        var result_data=[];
        var display='';
        return car_data.map(function(value,i) {
            if(value.year == 0){
                display=value.maker;
            }
            else{
            display=value.maker+'-'+value.model+'-'+value.year;
            }
            return {
              id: value.id,
              text:display,
            };
         })
}

  (function() {
  // init select 2
  jQuery('#unserviceable_cars').select2({
    data: mockData(),
    placeholder: 'search',
    multiple: true,
    // query with pagination
    query: function(q) {
      var pageSize,
        results,
        that = this;
      pageSize = 20; // or whatever pagesize
      results = [];
      if (q.term && q.term !== '') {
        // HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
        results = _.filter(that.data, function(e) {
          return e.text.toUpperCase().indexOf(q.term.toUpperCase()) >= 0;
        });
      } else if (q.term === '') {
        results = that.data;
      }
      q.callback({
        results: results.slice((q.page - 1) * pageSize, q.page * pageSize),
        more: results.length >= q.page * pageSize,
      });
    },
  });
})();

});
    

</script>

<?php } ?>
<script>
jQuery(document).ready(function(){
	check_vats_car();
	jQuery(document).on('click','#mobile_locksmith',function(){
		if(jQuery("#mobile_locksmith").prop('checked') == true){
			jQuery("#mob_lock_service_area").removeClass('disabled');
			
		}
		else
		{ 
			jQuery("#mobile_locksmith_address").val('');
			jQuery("#mob_lock_service_area").addClass('disabled');
		   
		}
	});
	jQuery('#works_on_vats_car').click(function(){
		check_vats_car();
	});
	function check_vats_car(){
		if(jQuery("#works_on_vats_car").prop('checked') == true){
			jQuery("#quantity_vats_car_key_cut").val(0);
			jQuery("#quantity_vats_car_key_cut").attr('disabled','disabled');
			jQuery("#quantity_vats_car_key_programmed").val(0);
			jQuery("#quantity_vats_car_key_programmed").attr('disabled','disabled');
			jQuery("#car_vats_programming_fee").val(0);
			jQuery("#car_vats_programming_fee").attr('disabled','disabled');
			jQuery("#additional_vats_car_key_cut_price").val(0);
			jQuery("#additional_vats_car_key_cut_price").attr('disabled','disabled');
			jQuery("#additional_vats_car_key_programmed_price").val(0);
			jQuery("#additional_vats_car_key_programmed_price").attr('disabled','disabled');
		}
		else{
			jQuery("#quantity_vats_car_key_cut").val('');
			jQuery("#quantity_vats_car_key_cut").removeAttr("disabled");
			jQuery("#quantity_vats_car_key_programmed").val('');
			jQuery("#quantity_vats_car_key_programmed").removeAttr("disabled");
			jQuery("#car_vats_programming_fee").val('');
			jQuery("#car_vats_programming_fee").removeAttr("disabled");
			jQuery("#additional_vats_car_key_cut_price").val('');
			jQuery("#additional_vats_car_key_cut_price").removeAttr("disabled");
			jQuery("#additional_vats_car_key_programmed_price").val();
			jQuery("#additional_vats_car_key_programmed_price").removeAttr("disabled");
		}
	}
});
</script>
<style>
table.need_service td, table.need_service th {
    padding: 2px 4px;
}
table.need_service th {
    font-weight: 900;
    color: #333b3d;
}
table.need_service th {
    font-weight: 900;
}
 input[type=number] {
    height: auto;
    min-height: 34px;
    width: 100%;
    border: 1px solid #c5c5c5;
    max-height: 76px;
    overflow: auto;
    border-radius: 4px;
    padding: 0px 10px;
}
.select_option{
    padding: 5px 10px !Important;
}
.form-group {
    margin-bottom: 20px;
    overflow: hidden;
    margin-top: 20px;
}
label.control-label.col-sm-3.col-md-3 {
    font-size: 13px;
}
.content-padding.gray-bkg.edit-product-single div#woocommerce-product-data .tab-content .form-group-row {
    border: 0;
    padding: 15px;
    box-shadow: 0px 1px 2px 2px #e1e1e1;
    margin-bottom: 15px;
    border-radius: 5px;
}
</style>