'use strict';
var ppsController = ( function ( $ ) {
  var $ppsOption, $variationsWrapper, $productShippingRules, $productShippingTable, $productInsertRow, $productImportCsv, $productExportCsv;
  var _private = {
    cacheDom: function cacheDom() {
      $ppsOption = $( '#_per_product_shipping' );
      $productShippingRules = $( '#shipping_product_data .per_product_shipping_rules' );
      $productShippingTable = $productShippingRules.find( 'table' );
      $productInsertRow = $productShippingTable.find( 'a.insert' );
      $productImportCsv = $productShippingTable.find( 'a.import' );
      $productExportCsv = $productShippingTable.find( 'a.export' );

      $variationsWrapper = $( '#variable_product_options' );
      return this;
    },
    eventListners: function eventListners() {
      $ppsOption.on( 'change', this.togglePerProductShippingOption );
      $productShippingTable
        .on( 'click', 'a.insert', this.insertRow )
        .on( 'click', 'a.delete', this.removeRow )
        .on( 'click', 'a.export', this.exportRow )
        .on( 'click', 'a.import', this.importRow );
      
      $( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', this.initVariationShippingTable.bind(this) );
      $( '#variable_product_options' ).on( 'woocommerce_variations_added', this.initVariationShippingTable.bind(this) );
      
      $variationsWrapper
        .on( 'change', '.woocommerce_variation .variation-content .enable_per_product_shipping', this.toggleVariationShippingOption )
        .on( 'click', '.woocommerce_variation .variation-content .per_product_shipping_rules a.insert', this.insertRow )
        .on( 'click', '.woocommerce_variation .variation-content .per_product_shipping_rules a.delete', this.removeRow )
        .on( 'click', '.woocommerce_variation .variation-content .per_product_shipping_rules a.export', this.exportRow )
        .on( 'click', '.woocommerce_variation .variation-content .per_product_shipping_rules a.import', this.importRow );
      
      return this;
    },
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
          if ( callback && typeof callback == "function" ) {
            callback( $elem );
          }
        }
      };
      $( $elem ).sortable( options );
    },
    initVariationShippingTable: function initVariationShippingTable() {
      $variationsWrapper.find( '.woocommerce_variation .variation-content .enable_per_product_shipping' ).trigger( 'change' );
      var $variationElem = $variationsWrapper.find( '.woocommerce_variation .per_product_shipping_rules table' ).find( 'tbody' );
      var items = 'tr';
      var handle = 'span.sortable-icon';
      this.sortable( $variationElem, items, handle );
    },
    togglePerProductShippingOption: function togglePerProductShippingOption() {
      if ( this.checked ) {
        $productShippingRules.show();
      } else {
        $productShippingRules.hide();
      }
    },
    toggleVariationShippingOption: function toggleVariationShippingOption() {
      if ( this.checked ) {
        $( this ).closest( '.woocommerce_variation' ).find( '.per_product_shipping_rules' ).show();
      } else {
        $( this ).closest( '.woocommerce_variation' ).find( '.per_product_shipping_rules' ).hide();
      }
    },
    insertRow: function insertRow() {
      $( this ).closest( 'table' ).append( $( this ).data( 'row' ) );
      return false;
    },
    removeRow: function removeRow() {
      $( this ).closest( 'tr' ).remove();
      return false;
    },
    exportRow: function exportRow() {
      var postid = $( this ).data( 'postid' );
      var csv_data = "data:application/csv;charset=utf-8," + wcmp_advance_product_params.i18n_product_id + "," + wcmp_advance_product_params.i18n_country_code + "," + wcmp_advance_product_params.i18n_state + "," + wcmp_advance_product_params.i18n_postcode + "," + wcmp_advance_product_params.i18n_cost + "," + wcmp_advance_product_params.i18n_item_cost + "\n";

      $( this ).closest( 'table' ).find( 'tbody tr' ).each( function () {
        var row = postid + ',';
        $( this ).find( 'input' ).each( function () {
          var val = $( this ).val();
          if ( !val )
            val = $( this ).attr( 'placeholder' );
          row = row + val + ',';
        } );
        row = row.substring( 0, row.length - 1 );
        csv_data = csv_data + row + "\n";
      } );

      $( this ).attr( 'href', encodeURI( csv_data ) );

      return true;
    },
    importRow: function importRow() {
      return true;
    },
    setupEnv: function setupEnv() {
      $ppsOption.trigger( 'change' );
      
      var $elem = $productShippingTable.find( 'tbody' );
      var $variationElem = $variationsWrapper.find( '.woocommerce_variation .per_product_shipping_rules table' ).find( 'tbody' );
      var items = 'tr';
      var handle = 'span.sortable-icon';
      this.sortable( $elem, items, handle );
      this.sortable( $variationElem, items, handle );
    }
  };
  var _public = {
    init: function init() {
      _private
        .cacheDom()
        .eventListners()
        .setupEnv();
    }
  };
  return _public;
} )( jQuery );
jQuery( ppsController.init.bind( ppsController ) );
