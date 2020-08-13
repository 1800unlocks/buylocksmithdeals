'use_strict';
( function ( $ ) {
    var events = [ ];
    for ( var key in rental_calendar_params ) {
        events.push( rental_calendar_params[key] );
    }

    var initialLocaleCode = 'en';
    var qtipDescription = '';
    $( '#redq-rental-calendar' ).fullCalendar( {
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        locale: initialLocaleCode,
        events: events,
        eventRender: function ( event, element ) {
            if ( event.post_status === 'wc-pending' ) {
                qtipDescription = 'Pending Payment';
            }
            if ( event.post_status === 'wc-processing' ) {
                qtipDescription = 'Processing';
            }
            if ( event.post_status === 'wc-on-hold' ) {
                qtipDescription = 'On Hold';
            }
            if ( event.post_status === 'wc-completed' ) {
                qtipDescription = 'Completed';
            }
            if ( event.post_status === 'wc-cancelled' ) {
                qtipDescription = 'Cancelled';
            }
            if ( event.post_status === 'wc-refunded' ) {
                qtipDescription = 'Refunded';
            }
            if ( event.post_status === 'wc-failed' ) {
                qtipDescription = 'Failed';
            }
            element.qtip( {
                content: qtipDescription,
                style: {
                    classes: 'qtip-youtube'
                },
                position: {
                    my: 'bottom left', // Position my top left...
                    at: 'top right', // at the bottom right of...
                    target: element // my target
                },
            } );
            element.attr( 'href', 'javascript:void(0);' );
            element.click( function () {
                $( "#eventProduct" ).html( event.title );
                $( "#eventProduct" ).attr( 'href', event.link );
                $( "#startTime" ).html( moment( event.start ).format( 'MMM Do h:mm A' ) );
                $( "#endTime" ).html( moment( event.end ).format( 'MMM Do h:mm A' ) );
                $( "#eventInfo" ).html( event.description );
                $( "#eventLink" ).attr( 'href', event.url );
                $.magnificPopup.open( {
                    items: {
                        src: '#eventContent',
                        type: 'inline'
                    }
                } );
            } );
        },
        eventAfterRender: function ( event, element, view ) {

            if ( event.post_status === 'wc-pending' ) {
                element.css( 'background-color', '#7266BA' );
            }
            if ( event.post_status === 'wc-processing' ) {
                element.css( 'background-color', '#23B7E5' );
            }
            if ( event.post_status === 'wc-on-hold' ) {
                element.css( 'background-color', '#FAD733' );
                element.css( 'color', '#000' );
            }
            if ( event.post_status === 'wc-completed' ) {
                element.css( 'background-color', '#27C24C' );
            }
            if ( event.post_status === 'wc-cancelled' ) {
                element.css( 'background-color', '#a00' );
            }
            if ( event.post_status === 'wc-refunded' ) {
                element.css( 'background-color', '#DDD' );
            }
            if ( event.post_status === 'wc-failed' ) {
                element.css( 'background-color', '#EE3939' );
            }
        },
    } );

} )( jQuery );
