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

	$(".vendor_affiliate_delete_button").click(function(event) {
		var ids = $(this).attr('id');

		var data_affiliates = $(this).attr('data-affiliatedelete');
		var data_meta_key = $(this).attr('data-metakeyaffiliate');
		event.preventDefault();
		event.preventDefault();
		block( $('.vendor_affiliate_delete_button') );
		var data = {
			action: 'request_affiliate_delete_vendor',
			data_affiliates: $(this).attr('data-affiliatedelete'),
			data_meta_key: $(this).attr('data-metakeyaffiliate'),
		};
		jQuery.post( afm_manage_affiliate_js.ajax_url , data, function(response) {
			if( response ){
				alert( afm_manage_affiliate_js.remove_affiliate );
				unblock( $('.vendor_affiliate_delete_button') );
				window.location.reload();
			}
		});
	});

} )( jQuery );
