'use strict';
( function ( $ ) {
     $( '#appointable_product_id' ).select2( {
    minimumResultsForSearch: 10,
    allowClear: true,
    placeholder: $( this ).data( 'placeholder' ),
    minimumInputLength: 3,
    escapeMarkup: function ( m ) {
      return m;
    },
    ajax: {
      //How long the user has to pause their typing before sending the next request
      quietMillis: 250,
      url: create_appointment_params.ajax_url,
      dataType: 'json',
      delay: 1000,
      data: function ( params ) {
        return {
          term: params.term,
          action: 'wcmp_afm_json_search_appointable_products',
          security: create_appointment_params.search_customers_nonce,
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

  $( '.tablenav select, .tablenav input' ).change(function() {
    $( '#mainform' ).submit();
  });
  
  $( '.calendar_day' ).datepicker({
    dateFormat: 'yy-mm-dd',
    numberOfMonths: 1,
    showOtherMonths: true,
    changeMonth: true,
    showButtonPanel: true,
    minDate: null
  });

 } )( jQuery );


