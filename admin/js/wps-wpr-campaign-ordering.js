/**
 * Campaign display order: drag-and-drop list on the Campaigning tab.
 *
 * Dragging renumbers every item 1..n so the saved order matches the list.
 */
( function ( $ ) {
	'use strict';

	function renumber( $list ) {
		$list.children( '.wps_wpr_campaign_order_item' ).each( function ( index ) {
			$( this ).find( '.wps_wpr_campaign_priority_input' ).val( index + 1 );
		} );
	}

	$( function () {
		var $list = $( '#wps_wpr_campaign_order_list' );
		if ( ! $list.length || 'function' !== typeof $list.sortable ) {
			return;
		}

		$list.sortable( {
			items: '> .wps_wpr_campaign_order_item',
			axis: 'y',
			cancel: 'input,label',
			placeholder: 'wps_wpr_campaign_order_placeholder',
			forcePlaceholderSize: true,
			update: function () {
				renumber( $list );
			},
		} );

		// Keyboard support: Alt + Arrow Up / Down moves the focused item.
		$list.on( 'keydown', '.wps_wpr_campaign_priority_input', function ( event ) {
			if ( ! event.altKey || ( 38 !== event.which && 40 !== event.which ) ) {
				return;
			}
			event.preventDefault();
			var $item = $( this ).closest( '.wps_wpr_campaign_order_item' );
			if ( 38 === event.which && $item.prev().length ) {
				$item.insertBefore( $item.prev() );
			} else if ( 40 === event.which && $item.next().length ) {
				$item.insertAfter( $item.next() );
			}
			renumber( $list );
			$( this ).trigger( 'focus' );
		} );
	} );
}( jQuery ) );
