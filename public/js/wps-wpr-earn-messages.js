/**
 * Earn-points messages.
 *
 * - Product page: updates the message when a variation is selected.
 * - Block Cart / Checkout: refreshes the "points you will earn" notice when the cart total changes.
 *
 * @package points-and-rewards-for-wooCommerce
 */
( function ( $ ) {
	'use strict';

	function initProduct() {
		var $message = $( '.wps_wpr_earn_message_product' );
		if ( ! $message.length ) {
			return;
		}
		var $points  = $message.find( '.wps_wpr_earn_message_points' );
		var fallback = $message.data( 'default-points' );

		$( document.body ).on( 'found_variation', 'form.variations_form', function ( event, variation ) {
			if ( variation && typeof variation.wps_wpr_earn_points !== 'undefined' ) {
				$points.text( variation.wps_wpr_earn_points );
				$message.toggle( '0' !== String( variation.wps_wpr_earn_points ) );
			}
		} );

		$( document.body ).on( 'reset_data', 'form.variations_form', function () {
			$points.text( fallback );
			$message.show();
		} );
	}

	function initCartBlock() {
		var $notice = $( '.wps_wpr_earn_message_cart_block' );
		var config  = window.wps_wpr_earn_messages;
		if ( ! $notice.length || ! config || ! window.wp || ! wp.data || ! wp.data.select( 'wc/store/cart' ) ) {
			return;
		}
		var lastTotal = null;
		var timer     = null;

		function refresh() {
			$.post( config.ajax_url, { action: config.action, context: $notice.data( 'context' ) || 'cart' } ).done( function ( response ) {
				if ( response && response.success ) {
					$notice.find( '.wps_wpr_earn_message_points' ).text( response.data.text );
					$notice.toggle( response.data.points > 0 );
				}
			} );
		}

		wp.data.subscribe( function () {
			var totals = wp.data.select( 'wc/store/cart' ).getCartTotals();
			var total  = totals ? totals.total_price : null;
			if ( null === total || total === lastTotal ) {
				return;
			}
			var first = null === lastTotal;
			lastTotal = total;
			if ( first ) {
				return;
			}
			clearTimeout( timer );
			timer = setTimeout( refresh, 300 );
		} );
	}

	$( function () {
		initProduct();
		initCartBlock();
	} );
}( jQuery ) );
