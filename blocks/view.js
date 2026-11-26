/**
 * Points and Rewards blocks pack - front end (referral link copy button).
 */
( function () {
	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest ? event.target.closest( '.wps-wpr-block__copy-btn' ) : null;
		if ( ! button ) {
			return;
		}
		var input = button.parentNode.querySelector( '.wps-wpr-block__link' );
		if ( ! input ) {
			return;
		}
		var original = button.textContent;
		var done     = function () {
			button.textContent = button.getAttribute( 'data-copied' ) || original;
			setTimeout( function () {
				button.textContent = original;
			}, 2000 );
		};
		if ( navigator.clipboard && window.isSecureContext ) {
			navigator.clipboard.writeText( input.value ).then( done );
		} else {
			input.select();
			document.execCommand( 'copy' );
			done();
		}
	} );
} )();
