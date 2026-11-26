/**
 * Floating loyalty widget / rewards launcher: open/close, Esc, focus handling,
 * referral copy, launcher tabs (ARIA tabs pattern), lazy activity loading and
 * hiding while the campaign popup is open.
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
		var tabs = Array.prototype.slice.call( root.querySelectorAll( '[data-wps-wpr-fw-tab]' ) );
		var activityLoaded = false;

		function isOpen() {
			return 'true' === launcher.getAttribute( 'aria-expanded' );
		}

		function open() {
			panel.hidden = false;
			launcher.setAttribute( 'aria-expanded', 'true' );
			root.classList.add( 'wps-wpr-fw--open' );
			panel.focus();
			var selected = getSelectedTab();
			if ( selected ) {
				maybeLoad( selected );
			}
		}

		function close( returnFocus ) {
			panel.hidden = true;
			launcher.setAttribute( 'aria-expanded', 'false' );
			root.classList.remove( 'wps-wpr-fw--open' );
			if ( returnFocus ) {
				launcher.focus();
			}
		}

		function getSelectedTab() {
			for ( var i = 0; i < tabs.length; i++ ) {
				if ( 'true' === tabs[ i ].getAttribute( 'aria-selected' ) ) {
					return tabs[ i ];
				}
			}
			return tabs[ 0 ] || null;
		}

		function loadActivity( container ) {
			if ( activityLoaded || ! i18n.ajaxUrl || ! i18n.activityNonce ) {
				return;
			}
			activityLoaded = true;
			var body = new FormData();
			body.append( 'action', 'wps_wpr_launcher_activity' );
			body.append( 'nonce', i18n.activityNonce );
			fetch( i18n.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( json ) {
					if ( json && json.success && json.data ) {
						container.innerHTML = json.data.html;
					} else {
						throw new Error( 'failed' );
					}
				} )
				.catch( function () {
					activityLoaded = false;
					container.textContent = i18n.activityError || '';
				} );
		}

		function maybeLoad( tab ) {
			if ( 'activity' !== tab.getAttribute( 'data-wps-wpr-fw-tab' ) ) {
				return;
			}
			var tabPanel = document.getElementById( tab.getAttribute( 'aria-controls' ) );
			var container = tabPanel ? tabPanel.querySelector( '[data-wps-wpr-fw-activity]' ) : null;
			if ( container ) {
				loadActivity( container );
			}
		}

		function selectTab( tab, setFocus ) {
			tabs.forEach( function ( item ) {
				var selected = item === tab;
				item.setAttribute( 'aria-selected', selected ? 'true' : 'false' );
				item.setAttribute( 'tabindex', selected ? '0' : '-1' );
				var tabPanel = document.getElementById( item.getAttribute( 'aria-controls' ) );
				if ( tabPanel ) {
					tabPanel.hidden = ! selected;
				}
			} );
			if ( setFocus ) {
				tab.focus();
			}
			maybeLoad( tab );
		}

		tabs.forEach( function ( tab, index ) {
			tab.addEventListener( 'click', function () {
				selectTab( tab, false );
			} );
			tab.addEventListener( 'keydown', function ( event ) {
				var next = null;
				if ( 'ArrowRight' === event.key || 'ArrowDown' === event.key ) {
					next = tabs[ ( index + 1 ) % tabs.length ];
				} else if ( 'ArrowLeft' === event.key || 'ArrowUp' === event.key ) {
					next = tabs[ ( index - 1 + tabs.length ) % tabs.length ];
				} else if ( 'Home' === event.key ) {
					next = tabs[ 0 ];
				} else if ( 'End' === event.key ) {
					next = tabs[ tabs.length - 1 ];
				}
				if ( next ) {
					event.preventDefault();
					selectTab( next, true );
				}
			} );
		} );

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

		function focusables() {
			var nodes = panel.querySelectorAll( 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])' );
			return Array.prototype.filter.call( nodes, function ( node ) {
				// Skip roving-tabindex items (inactive launcher tabs) and hidden nodes.
				if ( '-1' === node.getAttribute( 'tabindex' ) ) {
					return false;
				}
				return node.offsetWidth > 0 || node.offsetHeight > 0 || node.getClientRects().length > 0;
			} );
		}

		document.addEventListener( 'keydown', function ( event ) {
			if ( ! isOpen() ) {
				return;
			}
			if ( 'Escape' === event.key || 'Esc' === event.key ) {
				close( true );
				return;
			}
			if ( 'Tab' !== event.key ) {
				return;
			}
			// Keep Tab / Shift+Tab inside the open panel.
			var items = focusables();
			if ( ! items.length ) {
				event.preventDefault();
				panel.focus();
				return;
			}
			var first = items[ 0 ];
			var last = items[ items.length - 1 ];
			var active = document.activeElement;
			if ( event.shiftKey && ( active === first || active === panel || ! panel.contains( active ) ) ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && ( active === last || ! panel.contains( active ) ) ) {
				event.preventDefault();
				first.focus();
			}
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( isOpen() && ! root.contains( event.target ) ) {
				close( false );
			}
		} );

		// Stay out of the way while the campaign popup is open.
		var campaign = document.getElementById( 'wps-wpr-campaign-modal' );
		if ( campaign && window.MutationObserver ) {
			var sync = function () {
				var active = campaign.classList.contains( 'wps-wpr-campaign-modal--active' );
				root.classList.toggle( 'wps-wpr-fw--suppressed', active );
				if ( active && isOpen() ) {
					close( false );
				}
			};
			new MutationObserver( sync ).observe( campaign, { attributes: true, attributeFilter: [ 'class' ] } );
			sync();
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
