'use strict';
var geoMyWP = ( function ( $ ) {
    var $wrapper = null,
        $tabs = null,
        $firstTab = null

    var privateApi = {
        cacheDom: function cacheDom() {
            $wrapper = $( '#gmw-location-form-wrapper' );
            $tabs = $wrapper.find( '#gmw_location_nav_tabs' );
            $firstTab = $tabs.find( ' > li:first a' );

            return this;
        },
        setActiveTab: function setActiveTab( $tab ) {
            if ( typeof $tab.tab === 'function' ) {
                $tab.tab( 'show' );
            }

            return this;
        },
        setDefaultTab: function setDefaultTab() {
            //set first tab as active
            this.setActiveTab( $firstTab );

            return this;
        },
        setConfirmLocation: function() {
            $("#gmw-lf-confirm-location").fadeIn("fast");
        }
    };
    var publicApi = {
        init: function init() { 
            privateApi.cacheDom().setDefaultTab();
            $( '#gmw-lf-address, #gmw-lf-latitude, #gmw-lf-longitude' ).on( 'focus', privateApi.setConfirmLocation );
        }
    };
    return publicApi;
} )( jQuery );

geoMyWP.init();
