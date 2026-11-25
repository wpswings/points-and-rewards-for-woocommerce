/* Reward catalog settings: add / remove rows and toggle type-specific fields. */
( function ( $ ) {
	'use strict';

	function initProductSearch( $scope ) {
		$scope.find( 'select.wps-wpr-rc-product' ).each( function () {
			var $select = $( this );
			if ( $select.data( 'select2' ) || ! $.fn.selectWoo || typeof wc_enhanced_select_params === 'undefined' ) {
				return;
			}
			$select.selectWoo( {
				allowClear: true,
				placeholder: $select.data( 'placeholder' ),
				minimumInputLength: 3,
				ajax: {
					url: wc_enhanced_select_params.ajax_url,
					dataType: 'json',
					delay: 250,
					data: function ( params ) {
						return {
							term: params.term,
							action: $select.data( 'action' ),
							security: wc_enhanced_select_params.search_products_nonce
						};
					},
					processResults: function ( data ) {
						var terms = [];
						$.each( data || {}, function ( id, text ) {
							terms.push( { id: id, text: text } );
						} );
						return { results: terms };
					},
					cache: true
				}
			} );
		} );
	}

	$( function () {
		var $table = $( '#wps-wpr-rc-table' );
		if ( ! $table.length ) {
			return;
		}
		initProductSearch( $table );

		$( '#wps-wpr-rc-add' ).on( 'click', function () {
			var $btn  = $( this );
			var index = parseInt( $btn.attr( 'data-next-index' ), 10 ) || 0;
			var html  = $( '#wps-wpr-rc-row-template' ).html().replace( /__INDEX__/g, index );
			var $row  = $( $.trim( html ) );
			$table.find( 'tbody' ).append( $row );
			$btn.attr( 'data-next-index', index + 1 );
			initProductSearch( $row );
			$row.find( '.wps-wpr-rc-title' ).trigger( 'focus' );
		} );

		$table.on( 'click', '.wps-wpr-rc-remove', function () {
			$( this ).closest( 'tr' ).remove();
		} );

		$table.on( 'change', '.wps-wpr-rc-type', function () {
			$( this ).closest( 'tr' ).attr( 'data-type', $( this ).val() );
		} );
	} );
}( jQuery ) );
