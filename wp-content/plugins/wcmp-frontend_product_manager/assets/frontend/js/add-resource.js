/* All resources data table */
'use strict';
( function ( $ ) {
  var resource = ( function () {
    var utility = null;
    return {
      init: function init() {
        utility = this.defineUtilities();
        $( '#wcmp-afm-add-resource' )
          .on( 'change', '.wc_booking_availability_type select', this.availabilityTypeChanged.bind( this ) )
          .on( 'click', '.booking_availability a.insert', this.insertRangeCost.bind( this ) )
          .on( 'click', '.booking_availability a.delete', this.deleteRangeCost.bind( this ) )
          ;
        $( '#wcmp-afm-add-resource' ).find( '.wc_booking_availability_type select' ).change();
      },
      defineUtilities: function () {
        return {
          clean: function ( selector ) {
            //sanitize invalid selectors
            return $( selector ).length;
          },
          sortable: function ( $elem, items, handle, callback ) {
            if ( !this.clean( $elem ) || !this.clean( items ) || !this.clean( handle ) ) {
              return;
            }
            var placeholder = items.substr( 1 ) + '-sortable-placeholder';
            var options = {
              items: items,
              cursor: 'move',
              axis: 'y',
              handle: handle,
              scrollSensitivity: 40,
              forcePlaceholderSize: true,
              helper: 'clone',
              opacity: 0.65,
              placeholder: placeholder,
              start: function ( event, ui ) {
                ui.item.css( 'background-color', '#f6f6f6' );
              },
              stop: function ( event, ui ) {
                ui.item.removeAttr( 'style' );
                callback( $elem );
              }
            };
            $( $elem ).sortable( options );
          },
          // Date picker fields.
          datePickerSelect: function ( datepicker ) {
            var $td = $( datepicker ).closest( 'td' ),
              $tr = $td.closest( 'tr' ),
              option = $td.children().hasClass( 'bookings-datetime-select-from' ) ? 'minDate' : 'maxDate',
              $otherDateField = $tr.find( '.date-picker' ).not( datepicker ),
              date = $( datepicker ).datepicker( 'getDate' );
            $( $otherDateField ).datepicker( 'option', option, date );
            $( datepicker ).change();
            return;
          }
        };
      },
      availabilityTypeChanged: function ( e ) {
        var value = $( e.target ).val(),
          $row = $( e.target ).closest( 'tr' );
        // cleanup
        $row.find( '.bookings-datetime-select-from, .bookings-datetime-select-to' ).children( 'div' ).hide();
        //row.find( '.repeating-label' ).hide();
        $row.find( '.bookings-datetime-select-from, .bookings-datetime-select-to' ).removeClass( 'bookings-datetime-select-both' );
        //row.find( '.bookings-datetime-select-from' ).removeClass( 'bookings-datetime-select-both' );
        $row.find( '.bookings-to-label-row .bookings-datetimerange-second-label' ).hide();

        if ( value === 'custom' ) {
          $row.find( '.from_date, .to_date' ).show();
        } else if ( value === 'months' ) {
          $row.find( '.from_month, .to_month' ).show();
        } else if ( value === 'weeks' ) {
          $row.find( '.from_week, .to_week' ).show();
        } else if ( value === 'days' ) {
          $row.find( '.from_day_of_week, .to_day_of_week' ).show();
        } else if ( value.match( "^time" ) ) {
          $row.find( '.from_time, .to_time' ).show();
          // Show the date range as well if "time range for custom dates" is selected
          if ( 'time:range' === value ) {
            $row.find( '.from_date, .to_date' ).show();
            //$row.find( '.repeating-label' ).show();
            $row.find( '.bookings-datetime-select-from, .bookings-datetime-select-to' ).addClass( 'bookings-datetime-select-both' );
            //$row.find( '.bookings-datetime-select-from' ).addClass( 'bookings-datetime-select-both' );
            $row.find( '.bookings-to-label-row .bookings-datetimerange-second-label' ).show();
          }
        } else if ( value === 'persons' || value === 'duration' || value === 'blocks' ) {
          $row.find( '.from, .to' ).show();
        }
        this.updateDisplay( $row );
      },
      updateDisplay: function ( $row ) {
        var $elem = $row.closest( 'tbody' );
        var items = 'tr';
        var handle = 'td.sort';
        utility.sortable( $elem, items, handle, this.updateRowIndices );

        $( '.date-picker', $elem ).datepicker( {
          defaultDate: '',
          dateFormat: 'm/d/yy',
          numberOfMonths: 1,
          showButtonPanel: true,
          onSelect: function () {
            utility.datePickerSelect( $( this ) );
          }
        } ).on( 'change', function () {
          if ( !$( this ).datepicker( 'getDate' ) ) {
            var $td = $( this ).closest( 'td' ),
              $tr = $td.closest( 'tr' ),
              option = $td.children().hasClass( 'bookings-datetime-select-from' ) ? 'minDate' : 'maxDate',
              $otherDateField = $tr.find( '.date-picker' ).not( this );
            $( $otherDateField ).datepicker( 'option', option, null );
          }
          return false;
        } );
        $( '.date-picker', $elem ).each( function () {
          utility.datePickerSelect( $( this ) );
        } );
      },
//            updateRowIndices: function ( $tbody ) {
//                $tbody.find( 'tr' ).each( function ( index, el ) {
//                    $( '[name*="wc_booking"]', el ).each( function () {
//                        var oldName = $( this ).attr( 'name' ),
//                            newName = oldName.replace( /[\d+]/g, index );
//                        if ( oldName !== newName ) {
//                            $( this ).attr( 'name', newName );
//                        }
//                    } );
//                } );
//            },
      insertRangeCost: function ( e ) {
        var $table = $( e.target ).closest( 'table' );
        var newRowIndex = $table.find( 'tbody tr' ).length;
        var newRow = $( e.target ).data( 'row' );
        var $wrap = $( e.target ).closest( 'table' ).find( 'tbody' );
        $wrap.append( newRow );
        //this.updateRowIndices( $wrap );
        $wrap.find( 'tr:last select:first' ).change();
        return false;
      },
      deleteRangeCost: function ( e ) {
        var $tbody = $( e.target ).closest( 'tbody' );
        $( e.target ).closest( 'tr' ).remove();
        //this.updateRowIndices( $tbody );
        return false;
      },
    };
  } )();
  $( resource.init.bind( resource ) );
} )( jQuery );
