/**
 * "Redeem points" panel for the WooCommerce Cart and Checkout blocks.
 *
 * Renders in the order summary through the ExperimentalOrderMeta slot and talks
 * to the Store API through extensionCartUpdate (namespace "wps-wpr"), so the
 * cart totals refresh without reloading the page. Plain ES5, no build step.
 *
 * @package points-and-rewards-for-wooCommerce
 */
( function () {
	'use strict';

	var wc = window.wc || {};
	var wp = window.wp || {};
	var blocksCheckout = wc.blocksCheckout;

	if ( ! blocksCheckout || ! blocksCheckout.ExperimentalOrderMeta || ! wp.plugins || ! wp.element ) {
		return;
	}

	var el          = wp.element.createElement;
	var useState    = wp.element.useState;
	var getSetting  = wc.wcSettings && wc.wcSettings.getSetting ? wc.wcSettings.getSetting : function () { return {}; };
	var settings    = getSetting( 'wps-wpr-redeem-points_data', {} ) || {};
	var labels      = settings.labels || {};
	var namespace   = settings.namespace || 'wps-wpr';

	function sprintf( format ) {
		var args  = Array.prototype.slice.call( arguments, 1 );
		var index = 0;
		return String( format || '' ).replace( /%(\d+\$)?s/g, function ( match, position ) {
			var value = position ? args[ parseInt( position, 10 ) - 1 ] : args[ index++ ];
			return undefined === value ? '' : value;
		} );
	}

	function formatNumber( value ) {
		var number = parseInt( value, 10 ) || 0;
		return number.toLocaleString ? number.toLocaleString() : String( number );
	}

	function errorMessage( error ) {
		if ( error && error.message ) {
			var tmp       = document.createElement( 'div' );
			tmp.innerHTML = error.message;
			return tmp.textContent || tmp.innerText || labels.generic_fail;
		}
		return labels.generic_fail;
	}

	function RedeemPanel( props ) {
		var valueState = useState( '' );
		var busyState  = useState( false );
		var errorState = useState( '' );
		var value      = valueState[0];
		var setValue   = valueState[1];
		var busy       = busyState[0];
		var setBusy    = busyState[1];
		var error      = errorState[0];
		var setError   = errorState[1];

		var data    = props.extensions && props.extensions[ namespace ];
		var context = props.context || '';

		if ( ! data ) {
			return null;
		}

		var enabled = 'woocommerce/checkout' === context ? data.enabled_checkout : data.enabled_cart;
		if ( ! enabled ) {
			return null;
		}

		var children = [ el( 'div', { className: 'wps-wpr-block-redeem__title', key: 'title' }, labels.title ) ];

		if ( ! data.logged_in ) {
			if ( ! settings.show_guest_notice ) {
				return null;
			}
			children.push(
				el( 'p', { className: 'wps-wpr-block-redeem__text', key: 'login' },
					labels.login + ' ',
					el( 'a', { href: settings.login_url }, labels.login_link )
				)
			);
			return el( 'div', { className: 'wps-wpr-block-redeem', 'data-context': context }, children );
		}

		if ( ! data.can_redeem ) {
			return null;
		}

		function update( payload ) {
			setBusy( true );
			setError( '' );
			return blocksCheckout.extensionCartUpdate( { namespace: namespace, data: payload } )
				.then( function () {
					setValue( '' );
				} )
				.catch( function ( err ) {
					setError( errorMessage( err ) );
				} )
				.finally( function () {
					setBusy( false );
				} );
		}

		function onApply( event ) {
			event.preventDefault();
			var points = String( value ).trim();
			if ( ! /^\d+$/.test( points ) || parseInt( points, 10 ) <= 0 ) {
				setError( labels.invalid );
				return;
			}
			update( { action: 'apply', points: points } );
		}

		function onRemove( event ) {
			event.preventDefault();
			update( { action: 'remove' } );
		}

		children.push(
			el( 'p', { className: 'wps-wpr-block-redeem__text wps-wpr-block-redeem__balance', key: 'balance' },
				sprintf( labels.balance, formatNumber( data.balance ) ),
				data.rate_text ? el( 'span', { className: 'wps-wpr-block-redeem__rate' }, ' (' + data.rate_text + ')' ) : null
			)
		);

		if ( data.applied_points > 0 ) {
			children.push(
				el( 'div', { className: 'wps-wpr-block-redeem__applied', key: 'applied' },
					el( 'span', { className: 'wps-wpr-block-redeem__applied-text' },
						sprintf( labels.applied, formatNumber( data.applied_points ), data.applied_amount )
					),
					el( 'button', {
						type: 'button',
						className: 'wps-wpr-block-redeem__remove wc-block-components-button wp-element-button outlined',
						onClick: onRemove,
						disabled: busy
					}, labels.remove )
				)
			);
		} else if ( data.min_points > 0 && data.available < data.min_points ) {
			children.push(
				el( 'p', { className: 'wps-wpr-block-redeem__text wps-wpr-block-redeem__need', key: 'need' },
					sprintf( labels.need_more, formatNumber( data.min_points - data.available ) )
				)
			);
		} else if ( data.max_points > 0 ) {
			children.push(
				el( 'p', { className: 'wps-wpr-block-redeem__text wps-wpr-block-redeem__max', key: 'max' },
					sprintf( labels.max, formatNumber( data.max_points ) )
				)
			);
			children.push(
				el( 'form', { className: 'wps-wpr-block-redeem__form', onSubmit: onApply, noValidate: true, key: 'form' },
					el( 'input', {
						type: 'number',
						min: 1,
						max: data.max_points,
						step: 1,
						inputMode: 'numeric',
						className: 'wps-wpr-block-redeem__input',
						placeholder: labels.placeholder,
						'aria-label': labels.placeholder,
						value: value,
						disabled: busy,
						onChange: function ( event ) {
							setValue( event.target.value );
						}
					} ),
					el( 'button', {
						type: 'submit',
						className: 'wps-wpr-block-redeem__apply wc-block-components-button wp-element-button contained',
						disabled: busy
					}, labels.apply )
				)
			);
		}

		if ( error ) {
			children.push( el( 'p', { className: 'wps-wpr-block-redeem__error', role: 'alert', key: 'error' }, error ) );
		}

		return el( 'div', { className: 'wps-wpr-block-redeem', 'data-context': context, 'aria-busy': busy ? 'true' : 'false' }, children );
	}

	wp.plugins.registerPlugin( 'wps-wpr-redeem-points', {
		scope: 'woocommerce-checkout',
		render: function () {
			return el( blocksCheckout.ExperimentalOrderMeta, null, el( RedeemPanel, null ) );
		}
	} );
}() );
