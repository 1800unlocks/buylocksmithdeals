'use strict';
( function ( $ ) {
    $(".vendor_affiliate_edit_button").click(function(event) {

      var data_affiliate = $(this).attr('data-affiliate');
      event.preventDefault();
      var data = {
        action: 'request_affiliate_status_changed',
        data_affiliate: $(this).attr('data-affiliate'),
      };
      jQuery.post( wcmp_admin_js_script_data.ajax_url , data, function(response) {
        if( response ){
          window.location.reload();
        }
      });
    });
    /**************** select2 js load  *********************************/
    $( ".multiselect.vendor-affiliate" ).select2( {
    });

} )( jQuery );
