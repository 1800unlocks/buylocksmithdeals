'use strict';
jQuery( function ( $ ) {
  var addMultiInputProperty = function ( multi_input_holder ) {
    var multi_input_limit = multi_input_holder.data( 'limit' );
    if ( typeof multi_input_limit == 'undefined' )
      multi_input_limit = -1;

    if ( multi_input_holder.children( '.multi_input_block' ).length == 1 )
      multi_input_holder.children( '.multi_input_block' ).children( '.remove_multi_input_block' ).css( 'display', 'none' );

    if ( multi_input_holder.children( '.multi_input_block' ).length == multi_input_limit )
      multi_input_holder.find( '.add_multi_input_block' ).hide();
    else
      multi_input_holder.find( '.add_multi_input_block' ).show();

    multi_input_holder.children( '.multi_input_block' ).each( function () {
      if ( $( this )[0] != multi_input_holder.children( '.multi_input_block:last' )[0] ) {
        $( this ).children( '.add_multi_input_block' ).remove();
      }
    } );

    multi_input_holder.children( '.multi_input_block' ).find( '.add_multi_input_block' ).off( 'click' ).on( 'click', function () {
      var holder_id = multi_input_holder.attr( 'id' );
      var holder_name = multi_input_holder.data( 'name' );
      var multi_input_blockCount = $( this ).parent().find( '.multi_input_block_element' ).attr( 'id' ).split( "_" ).pop();
      multi_input_blockCount++;
      var multi_input_blockEle = multi_input_holder.children( '.multi_input_block:first' ).clone( false );

      multi_input_blockEle.find( 'textarea,input:not(input[type=button],input[type=submit],input[type=checkbox],input[type=radio])' ).val( '' );
      multi_input_blockEle.find( 'input[type=checkbox]' ).attr( 'checked', false );

      multi_input_blockEle.find( '.dc-wp-fields-uploader,.multi_input_block_element:not(.multi_input_holder)' ).each( function () {
        var ele = $( this );
        var ele_name = ele.data( 'name' );
        if ( ele.hasClass( 'dc-wp-fields-uploader' ) ) {
          var uploadEle = ele;
          ele_name = uploadEle.find( '.multi_input_block_element' ).data( 'name' );
          uploadEle.find( 'a span' ).css( 'display', 'none' );
          uploadEle.find( 'a' ).attr( 'id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_display' );
          uploadEle.find( 'img' ).attr( 'src', '' ).attr( 'id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_display' ).addClass( 'placeHolder' ).css( 'display', 'inline-block' );
          uploadEle.find( '.multi_input_block_element' ).attr( 'id', holder_id + '_' + ele_name + '_' + multi_input_blockCount ).attr( 'name', holder_name + '[' + multi_input_blockCount + '][' + ele_name + ']' );
          uploadEle.find( '.upload_button' ).attr( 'id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_button' ).show();
          uploadEle.find( '.remove_button' ).attr( 'id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_remove_button' ).hide();
          addDCUploaderProperty( uploadEle );
        } else {
          ele.attr( 'name', holder_name + '[' + multi_input_blockCount + '][' + ele_name + ']' );
          ele.attr( 'id', holder_id + '_' + ele_name + '_' + multi_input_blockCount );
          ele.parent().parent().find( '.checkbox_title' ).attr( 'for', holder_name + '_' + ele_name + '_' + multi_input_blockCount );
        }

        if ( ele.hasClass( 'dc_datepicker' ) ) {
          ele.removeClass( 'hasDatepicker' ).datepicker( {
            dateFormat: ele.data( 'date_format' ),
            changeMonth: true,
            changeYear: true
          } );
        } else if ( ele.hasClass( 'time_picker' ) ) {
          $( '.time_picker' ).timepicker( 'remove' ).timepicker( { 'step': 15 } );
          ele.timepicker( 'remove' ).timepicker( { 'step': 15 } );
        }
      } );

      // Nested multi-input block property
      multi_input_blockEle.find( '.multi_input_holder' ).each( function () {
        setNestedMultiInputIndex( $( this ), holder_id, holder_name, multi_input_blockCount );
      } );


      multi_input_blockEle.children( '.remove_multi_input_block' ).off( 'click' ).on( 'click', function () {
        var remove_ele_parent = $( this ).parent().parent();
        var addEle = remove_ele_parent.children( '.multi_input_block' ).children( '.add_multi_input_block' ).clone( true );
        $( this ).parent().remove();
        remove_ele_parent.children( '.multi_input_block' ).children( '.add_multi_input_block' ).remove();
        remove_ele_parent.children( '.multi_input_block:last' ).append( addEle );
        if ( remove_ele_parent.children( '.multi_input_block' ).length == multi_input_limit )
          remove_ele_parent.find( '.add_multi_input_block' ).hide();
        else
          remove_ele_parent.find( '.add_multi_input_block' ).show();
        if ( remove_ele_parent.children( '.multi_input_block' ).length == 1 )
          remove_ele_parent.children( '.multi_input_block' ).children( '.remove_multi_input_block' ).css( 'display', 'none' );
      } );

      multi_input_blockEle.children( '.add_multi_input_block' ).remove();
      multi_input_holder.append( multi_input_blockEle );
      multi_input_holder.children( '.multi_input_block:last' ).append( $( this ) );

      if ( multi_input_holder.children( '.multi_input_block' ).length > 1 )
        multi_input_holder.children( '.multi_input_block' ).children( '.remove_multi_input_block' ).css( 'display', 'block' );
      if ( multi_input_holder.children( '.multi_input_block' ).length == multi_input_limit )
        multi_input_holder.find( '.add_multi_input_block' ).hide();
      else
        multi_input_holder.find( '.add_multi_input_block' ).show();

      multi_input_holder.data( 'length', multi_input_blockCount );

    } );

    if ( !multi_input_holder.hasClass( 'multi_input_block_element' ) ) {
      multi_input_holder.children( '.multi_input_block' ).css( 'padding-bottom', '40px' );
    }
    if ( multi_input_holder.children( '.multi_input_block' ).children( '.multi_input_holder' ).length > 0 ) {
      multi_input_holder.children( '.multi_input_block' ).css( 'padding-bottom', '40px' );
    }

    multi_input_holder.children( '.multi_input_block' ).children( '.remove_multi_input_block' ).off( 'click' ).on( 'click', function () {
      var remove_ele_parent = $( this ).parent().parent();
      var addEle = remove_ele_parent.children( '.multi_input_block' ).children( '.add_multi_input_block' ).clone( true );
      $( this ).parent().remove();
      remove_ele_parent.children( '.multi_input_block' ).children( '.add_multi_input_block' ).remove();
      remove_ele_parent.children( '.multi_input_block:last' ).append( addEle );
      if ( remove_ele_parent.children( '.multi_input_block' ).length == 1 )
        remove_ele_parent.children( '.multi_input_block' ).children( '.remove_multi_input_block' ).css( 'display', 'none' );
      if ( remove_ele_parent.children( '.multi_input_block' ).length == multi_input_limit )
        remove_ele_parent.find( '.add_multi_input_block' ).hide();
      else
        remove_ele_parent.find( '.add_multi_input_block' ).show();
    } );
  }
  function setNestedMultiInputIndex( nested_multi_input, holder_id, holder_name, multi_input_blockCount ) {
    nested_multi_input.children( '.multi_input_block:not(:last)' ).remove();
    var multi_input_id = nested_multi_input.attr( 'id' );
    multi_input_id = multi_input_id.replace( holder_id + '_', '' );
    var multi_input_id_splited = multi_input_id.split( '_' );
    var multi_input_name = '';
    for ( var i = 0, j = multi_input_id_splited.length - 1; i < j; i++ ) {
      if ( multi_input_name != '' )
        multi_input_name += '_';
      multi_input_name += multi_input_id_splited[i];
    }
    nested_multi_input.attr( 'data-name', holder_name + '[' + multi_input_blockCount + '][' + multi_input_name + ']' );
    nested_multi_input.attr( 'id', holder_id + '_' + multi_input_name + '_' + multi_input_blockCount );
    nested_multi_input.children( '.multi_input_block' ).find( '.dc-wp-fields-uploader,.multi_input_block_element:not(.multi_input_holder)' ).each( function () {
      var ele = $( this );
      var ele_name = ele.data( 'name' );
      if ( ele.hasClass( 'dc-wp-fields-uploader' ) ) {
        var uploadEle = ele;
        ele_name = uploadEle.find( '.multi_input_block_element' ).data( 'name' );
        uploadEle.find( 'img' ).attr( 'src', '' ).attr( 'id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_display' ).addClass( 'placeHolder' );
        uploadEle.find( '.multi_input_block_element' ).attr( 'id', holder_id + '_' + multi_input_name + '_' + multi_input_blockCount + '_' + ele_name + '_0' ).attr( 'name', holder_name + '[' + multi_input_blockCount + '][' + multi_input_name + '][0][' + ele_name + ']' );
        uploadEle.find( '.upload_button' ).attr( 'id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_button' ).show();
        uploadEle.find( '.remove_button' ).attr( 'id', holder_id + '_' + ele_name + '_' + multi_input_blockCount + '_remove_button' ).hide();
        addDCUploaderProperty( uploadEle );
      } else {
        var multiple = ele.attr( 'multiple' );
        if ( typeof multiple !== typeof undefined && multiple !== false ) {
          ele.attr( 'name', holder_name + '[' + multi_input_blockCount + '][' + multi_input_name + '][0][' + ele_name + '][]' );
        } else {
          ele.attr( 'name', holder_name + '[' + multi_input_blockCount + '][' + multi_input_name + '][0][' + ele_name + ']' );
        }
        ele.attr( 'id', holder_id + '_' + multi_input_name + '_' + multi_input_blockCount + '_' + ele_name + '_0' );
      }

      if ( ele.hasClass( 'dc_datepicker' ) ) {
        ele.removeClass( 'hasDatepicker' ).datepicker( {
          dateFormat: ele.data( 'date_format' ),
          changeMonth: true,
          changeYear: true
        } );
      } else if ( ele.hasClass( 'time_picker' ) ) {
        $( '.time_picker' ).timepicker( 'remove' ).timepicker( { 'step': 15 } );
        ele.timepicker( 'remove' ).timepicker( { 'step': 15 } );
      }
    } );

    addMultiInputProperty( nested_multi_input );

    if ( nested_multi_input.children( '.multi_input_block' ).children( '.multi_input_holder' ).length > 0 )
      nested_multi_input.children( '.multi_input_block' ).css( 'padding-bottom', '40px' );

    nested_multi_input.children( '.multi_input_block' ).children( '.multi_input_holder' ).each( function () {
      setNestedMultiInputIndex( $( this ), holder_id + '_' + multi_input_name + '_0', holder_name + '[' + multi_input_blockCount + '][' + multi_input_name + ']', 0 );
    } );
  }
  
  $( '.multi_input_holder' ).each( function () {
    var multi_input_holder = $( this );
    addMultiInputProperty( multi_input_holder );
  } );

  $( '.dc_datepicker' ).removeClass( 'hasDatepicker' ).datepicker( {
    dateFormat: $( this ).data( 'date_format' ),
    changeMonth: true,
    changeYear: true
  } );
  
} );