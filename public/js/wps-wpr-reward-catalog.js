/* Reward catalog: redeem a reward from My Account. */
( function () {
	'use strict';

	var cfg = window.wpsWprRewardCatalog;
	if ( ! cfg ) {
		return;
	}

	function notice( root, message, isError, applyUrl ) {
		var box = root.querySelector( '.wps-wpr-reward-catalog__notice' );
		if ( ! box ) {
			return;
		}
		box.className = 'wps-wpr-reward-catalog__notice ' + ( isError ? 'woocommerce-error' : 'woocommerce-message' );
		box.textContent = message;
		if ( applyUrl ) {
			var link = document.createElement( 'a' );
			link.href = applyUrl;
			link.className = 'button wps-wpr-reward-catalog__apply';
			link.textContent = cfg.i18n.apply;
			box.appendChild( document.createTextNode( ' ' ) );
			box.appendChild( link );
		}
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest ? event.target.closest( '.wps-wpr-reward-card__redeem' ) : null;
		if ( ! button || button.disabled ) {
			return;
		}
		var root = button.closest( '.wps-wpr-reward-catalog' );
		if ( ! window.confirm( cfg.i18n.confirm.replace( '%s', button.getAttribute( 'data-points' ) ) ) ) {
			return;
		}

		button.disabled = true;
		var body = new URLSearchParams();
		body.append( 'action', cfg.action );
		body.append( 'nonce', cfg.nonce );
		body.append( 'reward_id', button.getAttribute( 'data-reward' ) );

		fetch( cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( json ) {
				var data = json && json.data ? json.data : {};
				if ( json && json.success ) {
					notice( root, data.message, false, data.apply_url );
					root.setAttribute( 'data-balance', data.balance );
				} else {
					notice( root, data.message || cfg.i18n.error, true );
					button.disabled = false;
				}
			} )
			.catch( function () {
				notice( root, cfg.i18n.error, true );
				button.disabled = false;
			} );
	} );
}() );
