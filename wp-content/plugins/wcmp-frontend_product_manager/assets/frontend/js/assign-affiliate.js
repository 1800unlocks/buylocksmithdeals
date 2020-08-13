'use strict';
( function ( $ ) {

    var block = function( $node ) {
	    if ( ! is_blocked( $node ) ) {
	        $node.addClass( 'processing' ).block( {
	            message: null,
	            overlayCSS: {
	                background: '#fff',
	                opacity: 0.6
	            }
	        } );
	    }
    };
    var is_blocked = function( $node ) {
        return $node.is( '.processing' ) || $node.parents( '.processing' ).length;
    };

    var unblock = function( $node ) {
        $node.removeClass( 'processing' ).unblock();
    };

	$(".wcmp-action-container-affiliate .request_affiliate_vendor").click(function(event) {
		var request_affiliate = $('#_affwp_woocommerce_product_rate_type').val()
		event.preventDefault();
		block( $('.wcmp-action-container-affiliate') );
		var data = {
			action: 'request_affiliate_vendor_action',
			request_affiliate: $('#_affwp_woocommerce_product_rate_type').val(),            
		};
		if( request_affiliate == '' ){
			alert( afm_assign_affiliate_js.email_empty );
			unblock( $('.wcmp-action-container-affiliate') );
			window.location.reload();
		} else {
			jQuery.post( afm_assign_affiliate_js.ajax_url , data, function(response) {
				if( response ){
					if( response.error ){
						alert( afm_assign_affiliate_js.already_assign );
						unblock( $('.wcmp-action-container-affiliate') );
						window.location.reload();
					} else if( response.no_user ){
						alert( response.no_user );
						unblock( $('.wcmp-action-container-affiliate') );
                    	window.location.reload();
					}else{
						alert( afm_assign_affiliate_js.success_apply );
						unblock( $('.wcmp-action-container-affiliate') );
                    	window.location.reload();
					}
					
				}
			});
		}

	});

} )( jQuery );
