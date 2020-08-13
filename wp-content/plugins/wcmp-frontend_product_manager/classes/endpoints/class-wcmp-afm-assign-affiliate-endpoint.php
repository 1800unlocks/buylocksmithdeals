<?php

defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Assign_affiliate_Endpoint {

    public function output() {
        global $wp;
        ?>
        <div class="col-md-12">
        	<form method="post" name="vendor_staff_form" class="wcmp_vendor_staff_form form-horizontal">
        		<div class="panel panel-default panel-pading pannel-outer-heading">
        			<div class="panel-heading">
        				<?php
        				echo '<h3>' . __("Request Vendor Affiliate", "wcmp-afm") . '</h3>';
        				?>
        			</div>
        			<div class="wcmp_form1 panel-body panel-content-padding">
        				<div class="form-group">
        					<label class="control-label col-sm-3 col-md-3 "><?php _e('Email Id *', 'wcmp-afm'); ?></label>
        					<div class="col-md-6 col-sm-9">
        						<input type="text" name="_affwp_woocommerce_product_rate_type" id="_affwp_woocommerce_product_rate_type" class="form-control" value="" placeholder="abc@gmail.com" ></input>
        						<small><?php _e('(Add an existing user as your Affiliate.)', 'wcmp-afm'); ?></small>
        					</div>  
        				</div>
        			</div>
        		</div>
        		<div class="action_div_space"> </div>
        		<p class="error_wcmp"><?php _e('* This field is required, you must fill some information.', 'wcmp-afm'); ?></p>
        		<div class="wcmp-action-container-affiliate">
        			<button class="request_affiliate_vendor wcmp_orange_btn btn btn-default" name="request_affiliate_vendor"><?php _e('Request Affiliate', 'wcmp-afm'); ?></button>
        			<div class="clear"></div>
        		</div>
        	</form>
        </div>
        <?php
    }
}
