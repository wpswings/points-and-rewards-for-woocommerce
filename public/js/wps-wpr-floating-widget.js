/**
 * Floating loyalty widget: open/close, Esc, focus handling and referral copy.
 */
( function () {
	'use strict';

	var i18n = window.wpsWprFloatingWidget || { copied: 'Copied!', copy: 'Copy' };

	function init() {
		var root = document.getElementById( 'wps-wpr-floating-widget' );
		if ( ! root ) {
			return;
		}
		var launcher = root.querySelector( '.wps-wpr-fw__launcher' );
		var panel = root.querySelector( '.wps-wpr-fw__panel' );
		if ( ! launcher || ! panel ) {
			return;
		}

		function isOpen() {
			return 'true' === launcher.getAttribute( 'aria-expanded' );
		}

		function open() {
			panel.hidden = false;
			launcher.setAttribute( 'aria-expanded', 'true' );
			root.classList.add( 'wps-wpr-fw--open' );
			panel.focus();
		}

		function close( returnFocus ) {
			panel.hidden = true;
			launcher.setAttribute( 'aria-expanded', 'false' );
			root.classList.remove( 'wps-wpr-fw--open' );
			if ( returnFocus ) {
				launcher.focus();
			}
		}

		launcher.addEventListener( 'click', function () {
			if ( isOpen() ) {
				close( true );
			} else {
				open();
			}
		} );

		root.addEventListener( 'click', function ( event ) {
			var target = event.target;
			if ( target.closest( '[data-wps-wpr-fw-close]' ) ) {
				close( true );
				return;
			}
			var copyBtn = target.closest( '[data-wps-wpr-fw-copy]' );
			if ( copyBtn ) {
				var input = root.querySelector( '.wps-wpr-fw__referral-input' );
				if ( ! input ) {
					return;
				}
				var done = function () {
					copyBtn.textContent = i18n.copied;
					setTimeout( function () {
						copyBtn.textContent = i18n.copy;
					}, 2000 );
				};
				if ( navigator.clipboard && window.isSecureContext ) {
					navigator.clipboard.writeText( input.value ).then( done, function () {
						input.select();
						document.execCommand( 'copy' );
						done();
					} );
				} else {
					input.select();
					document.execCommand( 'copy' );
					done();
				}
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( ( 'Escape' === event.key || 'Esc' === event.key ) && isOpen() ) {
				close( true );
			}
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( isOpen() && ! root.contains( event.target ) ) {
				close( false );
			}
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
