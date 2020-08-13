// jQuery( function ( $ ) {
// 	$( '.woocommerce-progress-form-wrapper .button-next' ).on( 'click', function() {
// 		$('.wc-progress-form-content').block({
// 			message: null,
// 			overlayCSS: {
// 				background: '#fff',
// 				opacity: 0.6
// 			}
// 		});
// 		return true;
// 	} );
// });

/*global ajaxurl, wc_product_import_params */
/*global ajaxurl, wc_product_import_params */
;(function ( $, window ) {

	/**
	 * productImportForm handles the import process.
	 */
	var productImportForm = function( $form ) {
		this.$form           = $form;
		this.xhr             = false;
		this.mapping         = wc_product_import_params.mapping;
		this.position        = 0;
		this.file            = wc_product_import_params.file;
		this.update_existing = wc_product_import_params.update_existing;
		this.delimiter       = wc_product_import_params.delimiter;
		this.security        = wc_product_import_params.import_nonce;

		// Number of import successes/failures.
		this.imported = 0;
		this.failed   = 0;
		this.updated  = 0;
		this.skipped  = 0;

		// Initial state.
		this.$form.find('.woocommerce-importer-progress').val( 0 );

		this.run_import = this.run_import.bind( this );

		// Start importing.
		this.run_import();
	};

	/**
	 * Run the import in batches until finished.
	 */
	productImportForm.prototype.run_import = function() {
		var $this = this;
		$.ajax( {
			type: 'POST',
			url: wc_product_import_params.ajax_url,
			data: {
				action          : 'woocommerce_do_ajax_product_import',
				position        : $this.position,
				mapping         : $this.mapping,
				file            : $this.file,
				update_existing : $this.update_existing,
				delimiter       : $this.delimiter,
				security        : $this.security
			},
			dataType: 'json',
			success: function( response ) {
				if ( response.success ) {
					$this.position  = response.data.position;
					$this.imported += response.data.imported;
					$this.failed   += response.data.failed;
					$this.updated  += response.data.updated;
					$this.skipped  += response.data.skipped;
					$this.$form.find('.woocommerce-importer-progress').val( response.data.percentage );

					if ( 'done' === response.data.position ) {
						$.urlParam = function(name){
						var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(response.data.url);
						   	if (results==null){
						      	return null;
						   	}
						   	else {
						      	return decodeURI(results[1]) || 0;
						  	}
						}

						$.pageIDparamWithEndpoint = function(name){
						var results = new RegExp('[\?&]' + name + '=([^&#]*\&[^&#]*)').exec(location.search);
						   	if (results==null){
						      	return null;
						   	}
						   	else {
						      	return decodeURI(results[1]) || 0;
						  	}
						}
						var redirect_url = location.protocol + '//' + location.host + location.pathname+'?step='+$.urlParam('step')+'&_wpnonce='+$.urlParam('_wpnonce')+ '&products-imported=' + parseInt( $this.imported, 10 ) + '&products-failed=' + parseInt( $this.failed, 10 ) + '&products-updated=' + parseInt( $this.updated, 10 ) + '&products-skipped=' + parseInt( $this.skipped, 10 );
						var page_id = $.pageIDparamWithEndpoint('page_id');
						if(page_id !== null && page_id != "") {
							redirect_url += '&page_id='+page_id;
						}

						window.location = redirect_url;
					        
					} else {
						$this.run_import();
					}
				}
			}
		} ).fail( function( response ) {
			window.console.log( response );
		} );
	};

	/**
	 * Function to call productImportForm on jQuery selector.
	 */
	$.fn.wc_product_importer = function() {
		new productImportForm( this );
		return this;
	};

	$( '.woocommerce-importer' ).wc_product_importer();

})( jQuery, window );
