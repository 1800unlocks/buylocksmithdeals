jQuery( function( $ ) {

	$( document ).ready(function() {

		if ( $( 'form.checkout' ).length === 0 ) {
			return;
		}
		
		shop_as_client_show_hide_fields();
		$( '#billing_shop_as_client' ).change( function() {
			shop_as_client_show_hide_fields();
		} );
		
		//$( shop_as_client.txt_pro ).insertAfter( '#billing_shop_as_client_create_user_field' );

		function shop_as_client_show_hide_fields() {
			if ( $( '#billing_shop_as_client' ).val() == 'yes' ) {
				$( '#billing_shop_as_client_create_user_field' ).show();
			} else {
				$( '#billing_shop_as_client_create_user_field' ).hide();
			}
		}

	} );

});