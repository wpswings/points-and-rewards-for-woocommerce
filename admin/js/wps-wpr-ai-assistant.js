/**
 * AI settings assistant chat panel.
 *
 * @package points-and-rewards-for-woocommerce
 */

jQuery( document ).ready( function( $ ) {
	var $messages   = $( '#wps_wpr_ai_messages' );
	var $input      = $( '#wps_wpr_ai_input' );
	var $send       = $( '#wps_wpr_ai_send' );
	var history     = [];
	var transcript  = [];
	var busy        = false;
	var storageKey  = 'wps_wpr_ai_chat';
	var maxHistory  = parseInt( wps_wpr_ai.maxHistory, 10 ) || 12;
	var maxLength   = parseInt( wps_wpr_ai.maxLength, 10 ) || 4000;
	var maxSaved    = 100;

	if ( ! $messages.length ) {
		return;
	}

	// Append inline markdown (code, bold, italic, http(s) links) to a node as DOM nodes.
	function wps_wpr_append_inline( parent, text ) {
		var pattern = /(`[^`\n]+`)|(\*\*[^\n]+?\*\*)|(\[[^\]\n]+\]\([^)\s]+\))|(\*[^*\s][^*\n]*\*)/g;
		var last    = 0;
		var match, token, node, link;

		while ( null !== ( match = pattern.exec( text ) ) ) {
			if ( match.index > last ) {
				parent.appendChild( document.createTextNode( text.slice( last, match.index ) ) );
			}
			token = match[0];
			if ( match[1] ) {
				node = document.createElement( 'code' );
				node.textContent = token.slice( 1, -1 );
			} else if ( match[2] ) {
				node = document.createElement( 'strong' );
				wps_wpr_append_inline( node, token.slice( 2, -2 ) );
			} else if ( match[3] ) {
				link = /^\[([^\]]+)\]\(([^)\s]+)\)$/.exec( token );
				if ( /^https?:\/\//i.test( link[2] ) ) {
					node = document.createElement( 'a' );
					node.href = link[2];
					node.target = '_blank';
					node.rel = 'noopener noreferrer';
					node.textContent = link[1];
				} else {
					node = document.createTextNode( link[1] );
				}
			} else {
				node = document.createElement( 'em' );
				wps_wpr_append_inline( node, token.slice( 1, -1 ) );
			}
			parent.appendChild( node );
			last = pattern.lastIndex;
		}
		if ( last < text.length ) {
			parent.appendChild( document.createTextNode( text.slice( last ) ) );
		}
	}

	// Render a safe markdown subset as DOM nodes. Model text is never inserted as HTML.
	function wps_wpr_render_markdown( text ) {
		var fragment  = document.createDocumentFragment();
		var lines     = String( text ).replace( /\r\n?/g, '\n' ).split( '\n' );
		var paragraph = null;
		var list      = null;
		var code      = null;
		var i, line, heading, item, node;

		function close_blocks() {
			paragraph = null;
			list      = null;
		}

		for ( i = 0; i < lines.length; i++ ) {
			line = lines[ i ];

			if ( /^\s*```/.test( line ) ) {
				if ( code ) {
					code = null;
				} else {
					close_blocks();
					node = document.createElement( 'pre' );
					code = document.createElement( 'code' );
					node.appendChild( code );
					fragment.appendChild( node );
				}
				continue;
			}
			if ( code ) {
				code.textContent += ( code.textContent ? '\n' : '' ) + line;
				continue;
			}
			if ( ! $.trim( line ) ) {
				close_blocks();
				continue;
			}

			heading = /^\s*(#{1,6})\s+(.*)$/.exec( line );
			if ( heading ) {
				close_blocks();
				node = document.createElement( 'h' + Math.min( 6, heading[1].length + 3 ) );
				wps_wpr_append_inline( node, heading[2] );
				fragment.appendChild( node );
				continue;
			}

			item = /^\s*(?:([-*+])|(\d+)[.)])\s+(.*)$/.exec( line );
			if ( item ) {
				paragraph = null;
				if ( ! list || list.tagName.toLowerCase() !== ( item[1] ? 'ul' : 'ol' ) ) {
					list = document.createElement( item[1] ? 'ul' : 'ol' );
					fragment.appendChild( list );
				}
				node = document.createElement( 'li' );
				wps_wpr_append_inline( node, item[3] );
				list.appendChild( node );
				continue;
			}

			list = null;
			if ( paragraph ) {
				paragraph.appendChild( document.createElement( 'br' ) );
			} else {
				paragraph = document.createElement( 'p' );
				fragment.appendChild( paragraph );
			}
			wps_wpr_append_inline( paragraph, line );
		}
		return fragment;
	}

	function wps_wpr_scroll() {
		$messages.scrollTop( $messages[0].scrollHeight );
	}

	function wps_wpr_add_message( role, text ) {
		var $bubble = $( '<div>' ).addClass( 'wps_wpr_ai_message wps_wpr_ai_message--' + role );
		if ( 'assistant' === role ) {
			$bubble[0].appendChild( wps_wpr_render_markdown( text ) );
		} else {
			$bubble.text( text );
		}
		$messages.append( $bubble );
		wps_wpr_scroll();
		return $bubble;
	}

	// Keep the conversation for this browser tab so a reload does not lose it.
	function wps_wpr_save() {
		try {
			window.sessionStorage.setItem( storageKey, JSON.stringify( { history: history, transcript: transcript.slice( -maxSaved ) } ) );
		} catch ( e ) {}
	}

	// Trim history to what the server uses, starting with a user turn.
	function wps_wpr_trim_history() {
		history = history.slice( -maxHistory );
		while ( history.length && 'user' !== history[0].role ) {
			history.shift();
		}
	}

	function wps_wpr_format_value( value ) {
		if ( true === value ) {
			return wps_wpr_ai.i18n.on;
		}
		if ( false === value ) {
			return wps_wpr_ai.i18n.off;
		}
		if ( null === value || '' === value ) {
			return wps_wpr_ai.i18n.empty;
		}
		return String( value );
	}

	function wps_wpr_set_proposal_status( $card, entry ) {
		$card.find( '.wps_wpr_ai_proposal_actions' ).replaceWith(
			$( '<p>' ).addClass( 'wps_wpr_ai_proposal_status' + ( entry.success ? '' : ' is-error' ) ).text( entry.message )
		);
		if ( entry.success ) {
			$card.addClass( 'apply' === entry.decision ? 'is-applied' : 'is-canceled' );
		}
	}

	function wps_wpr_add_proposal( proposal, entry ) {
		var $card = $( '<div>' ).addClass( 'wps_wpr_ai_proposal' ).attr( 'data-proposal', proposal.id );
		var $list = $( '<ul>' );

		$card.data( 'entry', entry );
		$card.append( $( '<strong>' ).text( wps_wpr_ai.i18n.proposed ) );
		if ( proposal.summary ) {
			$card.append( $( '<p>' ).text( proposal.summary ) );
		}
		$.each( proposal.changes, function( i, change ) {
			$list.append(
				$( '<li>' ).append(
					$( '<span>' ).addClass( 'wps_wpr_ai_proposal_label' ).text( change.label + ' (' + change.tab + '): ' ),
					$( '<span>' ).addClass( 'wps_wpr_ai_proposal_from' ).text( wps_wpr_format_value( change.from ) ),
					' → ',
					$( '<span>' ).addClass( 'wps_wpr_ai_proposal_to' ).text( wps_wpr_format_value( change.to ) )
				)
			);
		} );
		$card.append(
			$list,
			$( '<div>' ).addClass( 'wps_wpr_ai_proposal_actions' ).append(
				$( '<button type="button">' ).addClass( 'button button-primary wps_wpr_ai_resolve' ).attr( 'data-decision', 'apply' ).text( wps_wpr_ai.i18n.apply ),
				$( '<button type="button">' ).addClass( 'button wps_wpr_ai_resolve' ).attr( 'data-decision', 'cancel' ).text( wps_wpr_ai.i18n.cancel )
			)
		);
		if ( entry.resolved ) {
			wps_wpr_set_proposal_status( $card, entry.resolved );
		}
		$messages.append( $card );
		wps_wpr_scroll();
	}

	// Show an error; for failed sends, offer to send the same text again.
	function wps_wpr_add_error( message, retryText ) {
		var $bubble = wps_wpr_add_message( 'error', message );
		if ( retryText ) {
			$bubble.append(
				' ',
				$( '<button type="button">' ).addClass( 'button button-small wps_wpr_ai_retry' ).text( wps_wpr_ai.i18n.retry ).on( 'click', function() {
					if ( busy ) {
						return;
					}
					$bubble.remove();
					if ( $input.val() === retryText ) {
						$input.val( '' );
					}
					wps_wpr_send( retryText );
				} )
			);
		}
	}

	function wps_wpr_send( text ) {
		text = $.trim( text );
		if ( ! text || busy ) {
			return;
		}
		if ( text.length > maxLength ) {
			text = text.slice( 0, maxLength );
		}

		busy = true;
		$send.prop( 'disabled', true );
		$( '#wps_wpr_ai_suggestions' ).hide();
		$messages.find( '.wps_wpr_ai_retry' ).closest( '.wps_wpr_ai_message' ).remove();
		var $userBubble = wps_wpr_add_message( 'user', text );
		$input.val( '' );
		var $thinking = wps_wpr_add_message( 'assistant', wps_wpr_ai.i18n.thinking ).addClass( 'wps_wpr_ai_message--pending' );

		// Put the unsent text back in the composer so it is not lost.
		function restore_text() {
			$userBubble.remove();
			if ( ! $.trim( $input.val() ) ) {
				$input.val( text );
			}
		}

		wps_wpr_trim_history();
		$.post(
			wps_wpr_ai.ajaxurl,
			{
				action: 'wps_wpr_ai_chat',
				nonce: wps_wpr_ai.nonce,
				message: text,
				history: JSON.stringify( history )
			}
		).done( function( response ) {
			$thinking.remove();
			if ( ! response || ! response.success ) {
				restore_text();
				wps_wpr_add_error( response && response.data && response.data.message ? response.data.message : wps_wpr_ai.i18n.error, text );
				return;
			}
			var reply = response.data.reply ? String( response.data.reply ) : '';
			var proposals = response.data.proposals || [];

			if ( ! reply && ! proposals.length ) {
				reply = wps_wpr_ai.i18n.noReply;
			}
			history.push( { role: 'user', text: text } );
			transcript.push( { type: 'message', role: 'user', text: text } );
			if ( reply ) {
				wps_wpr_add_message( 'assistant', reply );
				history.push( { role: 'assistant', text: reply } );
				transcript.push( { type: 'message', role: 'assistant', text: reply } );
			}
			$.each( proposals, function( i, proposal ) {
				var entry = { type: 'proposal', proposal: proposal };
				transcript.push( entry );
				wps_wpr_add_proposal( proposal, entry );
			} );
			wps_wpr_trim_history();
			wps_wpr_save();
		} ).fail( function() {
			$thinking.remove();
			restore_text();
			wps_wpr_add_error( wps_wpr_ai.i18n.network, text );
		} ).always( function() {
			busy = false;
			$send.prop( 'disabled', false );
			$input.trigger( 'focus' );
		} );
	}

	// Restore the conversation saved for this tab.
	( function() {
		var saved = null;
		try {
			saved = JSON.parse( window.sessionStorage.getItem( storageKey ) || 'null' );
		} catch ( e ) {
			saved = null;
		}
		if ( ! saved || ! $.isArray( saved.transcript ) || ! saved.transcript.length ) {
			return;
		}
		history    = $.isArray( saved.history ) ? saved.history : [];
		transcript = saved.transcript;
		$.each( transcript, function( i, entry ) {
			if ( ! entry ) {
				return;
			}
			if ( 'proposal' === entry.type && entry.proposal && $.isArray( entry.proposal.changes ) ) {
				wps_wpr_add_proposal( entry.proposal, entry );
			} else if ( 'message' === entry.type && 'string' === typeof entry.text ) {
				wps_wpr_add_message( 'assistant' === entry.role ? 'assistant' : 'user', entry.text );
			}
		} );
		$( '#wps_wpr_ai_suggestions' ).hide();
	}() );

	$send.on( 'click', function() {
		wps_wpr_send( $input.val() );
	} );

	// Enter sends, Shift+Enter adds a new line; never submit the settings form.
	$input.on( 'keydown', function( e ) {
		if ( 13 === e.which && ! e.shiftKey ) {
			e.preventDefault();
			wps_wpr_send( $input.val() );
		}
	} );

	$( document ).on( 'click', '.wps_wpr_ai_suggestion', function() {
		wps_wpr_send( $( this ).text() );
	} );

	$( document ).on( 'click', '.wps_wpr_ai_resolve', function() {
		var $card    = $( this ).closest( '.wps_wpr_ai_proposal' );
		var decision = $( this ).data( 'decision' );

		$card.find( 'button' ).prop( 'disabled', true );
		$.post(
			wps_wpr_ai.ajaxurl,
			{
				action: 'wps_wpr_ai_resolve_proposal',
				nonce: wps_wpr_ai.nonce,
				proposal_id: $card.data( 'proposal' ),
				decision: decision
			}
		).done( function( response ) {
			var resolved = {
				success: !! ( response && response.success ),
				decision: decision,
				message: response && response.data && response.data.message ? response.data.message : wps_wpr_ai.i18n.error
			};
			var entry = $card.data( 'entry' );

			wps_wpr_set_proposal_status( $card, resolved );
			if ( entry ) {
				entry.resolved = resolved;
			}
			if ( resolved.success ) {
				history.push( { role: 'user', text: ( 'apply' === decision ? wps_wpr_ai.i18n.applied : wps_wpr_ai.i18n.canceled ) + ': ' + $card.find( 'li' ).text() } );
				wps_wpr_trim_history();
			}
			wps_wpr_save();
		} ).fail( function() {
			$card.find( 'button' ).prop( 'disabled', false );
			wps_wpr_add_message( 'error', wps_wpr_ai.i18n.error );
		} );
	} );
} );
