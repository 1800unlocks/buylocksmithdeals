'use strict';
var bulk_actions = ( function ( $, D ) {
  var privateApi = {
    updateIsRunning: false,
    dom: {
      $productTable: null,
      $bulkActions: null,
      $bulkEdit: null,
      $bulkModal: null,
      $bulkModalTitle: null,
      $bulkModalBody: null,
      $bulkModalForm: null,
      $productsToEdit: null,
      $quickEdit: null,
      $quickModal: null,
      $quickModalTitle: null,
      $quickModalBody: null
    },
    setupEnv: function setupEnv( ) {
      this.cacheDom( ).addEventListners( );
      this.dom.$bulkModal.find( '.change-input' ).hide( );
      this.dom.$productsToEdit.select2();
      return this;
    },
    cacheDom: function cacheDom( ) {
      this.dom.$productTable = $( '#product_table' );
      this.dom.$bulkActions = $( '#product_bulk_actions' );
      this.dom.$bulkEdit = $( '#product_list_do_bulk_action' );
      this.dom.$bulkModal = $( '#edit_action_modal' );
      this.dom.$bulkModalTitle = $( '#edit_action_modal #modal_title' );
      this.dom.$bulkModalBody = $( '#edit_action_modal #modal_body' );
      this.dom.$bulkModalForm = $( '#wcmp-afm-bulk-edit-form' );
      this.dom.$productsToEdit = this.dom.$bulkModal.find( '.edit_product_list' );
      return this;
    },
    addEventListners: function addEventListners( ) {
      this.dom.$productTable.on( 'change', 'input[name^=selected_products]', this.destroyEditProductsList.bind( this ) );
      this.dom.$bulkEdit.on( 'click', this.editInBulk.bind( this ) );
      this.dom.$productsToEdit.on( 'select2:select select2:unselect', this.updateSelection.bind( this ) );
      this.dom.$bulkModal.on( 'change', 'select.change_to', this.showChangeToField );
      this.dom.$bulkModal.on( 'click', '#do_bulk_update', this.doBulkUpdate.bind( this ) );
      this.dom.$bulkModal.on( 'hide.bs.modal', this.shouldModalClose.bind( this ) );
      return this;
    },
    destroyEditProductsList: function destroyEditProductsList() {
      if ( this.dom.$productsToEdit.length > 0 )
        this.dom.$productsToEdit[0].options.length = 0;
    },
    editInBulk: function editInBulk( ) {
      if ( this.dom.$bulkActions.val( ) === 'edit' ) {
        if ( this.isValidOperation( ) ) {
          this.initBulkEditModal();
        } else {
          alert( products_params.i18n_no_selection );
          this.dom.$bulkActions.val( '' );
        }
        return false;
      }
    },
    isValidOperation: function isValidOperation( ) {
      var self = this;
      return isProductsToEditDomExists() && !isEmptySelectionList( );

      function isProductsToEditDomExists() {
        return self.dom.$productsToEdit.length > 0;
      }
      function isEmptySelectionList( ) {
        return Object.keys( self.getSelectedProducts( ) ).length === 0;
      }
    },
    getSelectedProducts: function getSelectedProducts( ) {
      var $items = this.dom.$productTable.find( 'input[name^=selected_products]:checked' );
      return this.getProductObjFromjQueryObj( $items );
    },
    getProductObjFromjQueryObj: function getProductObjFromjQueryObj( $items ) {
      var products = { };
      $items.each( function () {
        products[this.value] = $( this ).data( 'title' ) || $( this ).closest( 'tr' ).find( 'td.name>a' ).text();
      } );
      return products;
    },
    initBulkEditModal: function initBulkEditModal( ) {
      if ( this.dom.$productsToEdit[0].options.length === 0 ) {
        this.createOptionsFromSelectedProductsObj( this.dom.$productsToEdit[0], this.getSelectedProducts() );
      }
      this.dom.$bulkModal.modal( 'show' );
    },
    createOptionsFromSelectedProductsObj: function createOptionsFromSelectedProductsObj( selectDom, selProducts ) {
      for ( var index in selProducts ) {
        if ( selProducts.hasOwnProperty( index ) ) {
          selectDom.options[selectDom.options.length] = new Option( selProducts[index], index, true, true );
        }
      }
    },
    updateSelection: function updateSelection( e ) {
      var productId = e.params.data.id;
      var type = e.params._type;
      this.dom.$productTable.find( 'input[name="selected_products[' + productId + ']"]' ).prop( "checked", type === 'select' );
      return false;
    },
    showChangeToField: function showChangeToField( ) {
      if ( this.value ) {
        $( this ).next( '.change-input' ).show( );
      } else {
        $( this ).next( '.change-input' ).hide( );
      }
    },
    doBulkUpdate: function doBulkUpdate( ) {
      var self = this;
      var dom = self.dom;
      var productIds = dom.$productsToEdit.val();
      if ( !productIds || productIds.length === 0 ) {
        alert( products_params.i18n_no_selection );
        return false;
      }
      self.updateIsRunning = true;
      var $wrapper = dom.$bulkModal.find( '.modal-content' );
      var data = {
        action: 'wcmp_afm_bulk_product_edit',
        product_ids: JSON.stringify( productIds ),
        form_data: dom.$bulkModalForm.serialize( ),
        security: products_params.bulk_edit_nonce
      };
      $wrapper.block( {
        message: null,
        overlayCSS: {
          background: '#fff',
          opacity: 0.6
        }
      } );
      $.post( products_params.ajax_url, data, function ( response ) {
        dom.$productTable.find( 'input[name^=selected_products], .select_all_all' ).prop( "checked", false );
        dom.$bulkActions.val( '' );
        self.updateIsRunning = false;
        dom.$bulkModal.modal( 'hide' );
        $wrapper.unblock();
        dom.$productTable.DataTable( ).ajax.reload( );
        return false;
      } );
    },
    shouldModalClose: function shouldModalClose() {
      return !this.updateIsRunning;
    }
  };
  var publicApi = {
    init: function init( ) {
      privateApi.setupEnv( );
    }
  };
  return publicApi;
} )( jQuery, document );
jQuery( function () {
  bulk_actions.init( );
} );