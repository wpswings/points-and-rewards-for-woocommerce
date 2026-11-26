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

	// Tell screen reader users where an item ended up (aria-live region in the template).
	function announce( $list, $item ) {
		var $live = $( '#wps_wpr_campaign_order_live' );
		var l10n = window.wps_wpr_campaign_ordering || {};
		if ( ! $live.length || ! $item.length ) {
			return;
		}
		var $items = $list.children( '.wps_wpr_campaign_order_item' );
		var message = ( l10n.moved || '%1$s moved to position %2$d of %3$d.' )
			.replace( '%1$s', $.trim( $item.find( '.wps_wpr_campaign_order_label' ).text() ) )
			.replace( '%2$d', $items.index( $item ) + 1 )
			.replace( '%3$d', $items.length );
		$live.text( message );
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
			update: function ( event, ui ) {
				renumber( $list );
				announce( $list, ui.item );
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
			announce( $list, $item );
			$( this ).trigger( 'focus' );
		} );
	} );
}( jQuery ) );
