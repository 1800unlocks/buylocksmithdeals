'use strict';
( function ( $ ) {
  $( document ).find( 'input:not(hidden, [type="submit"]), select' ).filter( ':not(.form-control)' ).addClass( 'form-control' );
  $( '#customer_id' ).select2( {
    minimumResultsForSearch: 10,
    allowClear: true,
    placeholder: $( this ).data( 'placeholder' ),
    minimumInputLength: 3,
    escapeMarkup: function ( m ) {
      return m;
    },
    ajax: {
      //How long the user has to pause their typing before sending the next request
      quietMillis: 150,
      url: create_booking_params.ajax_url,
      dataType: 'json',
      delay: 1000,
      data: function ( params ) {
        return {
          term: params.term,
          action: 'wcmp_afm_json_search_customers',
          security: create_booking_params.search_customers_nonce,
          exclude: $( this ).data( 'exclude' )
        };
      },
      processResults: function ( data ) {
        var terms = [ ];
        if ( data ) {
          $.each( data, function ( id, text ) {
            terms.push( {
              id: id,
              text: text
            } );
          } );
        }
        return {
          results: terms
        };
      },
      cache: true
    }
  } );
} )( jQuery );
