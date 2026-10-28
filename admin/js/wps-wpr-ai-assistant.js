/**
 * AI settings assistant chat panel.
 *
 * @package points-and-rewards-for-woocommerce
 */

jQuery( document ).ready( function( $ ) {
	var $messages = $( '#wps_wpr_ai_messages' );
	var $input    = $( '#wps_wpr_ai_input' );
	var $send     = $( '#wps_wpr_ai_send' );
	var history   = [];
	var busy      = false;

	if ( ! $messages.length ) {
		return;
	}

	// Escape model text and apply minimal formatting (bold, line breaks).
	function wps_wpr_format_text( text ) {
		var escaped = $( '<div>' ).text( text ).html();
		return escaped.replace( /\*\*(.+?)\*\*/g, '<strong>$1</strong>' ).replace( /\n/g, '<br>' );
	}

	function wps_wpr_add_message( role, text ) {
		var $bubble = $( '<div>' ).addClass( 'wps_wpr_ai_message wps_wpr_ai_message--' + role ).html( wps_wpr_format_text( text ) );
		$messages.append( $bubble );
		$messages.scrollTop( $messages[0].scrollHeight );
		return $bubble;
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

	function wps_wpr_add_proposal( proposal ) {
		var $card = $( '<div>' ).addClass( 'wps_wpr_ai_proposal' ).attr( 'data-proposal', proposal.id );
		var $list = $( '<ul>' );

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
		$messages.append( $card );
		$messages.scrollTop( $messages[0].scrollHeight );
	}

	function wps_wpr_send( text ) {
		text = $.trim( text );
		if ( ! text || busy ) {
			return;
		}

		busy = true;
		$send.prop( 'disabled', true );
		$( '#wps_wpr_ai_suggestions' ).hide();
		wps_wpr_add_message( 'user', text );
		$input.val( '' );
		var $thinking = wps_wpr_add_message( 'assistant', wps_wpr_ai.i18n.thinking ).addClass( 'wps_wpr_ai_message--pending' );

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
			if ( ! response.success ) {
				wps_wpr_add_message( 'error', response.data && response.data.message ? response.data.message : wps_wpr_ai.i18n.error );
				return;
			}
			history.push( { role: 'user', text: text } );
			if ( response.data.reply ) {
				wps_wpr_add_message( 'assistant', response.data.reply );
				history.push( { role: 'assistant', text: response.data.reply } );
			}
			$.each( response.data.proposals || [], function( i, proposal ) {
				wps_wpr_add_proposal( proposal );
			} );
		} ).fail( function() {
			$thinking.remove();
			wps_wpr_add_message( 'error', wps_wpr_ai.i18n.error );
		} ).always( function() {
			busy = false;
			$send.prop( 'disabled', false );
			$input.trigger( 'focus' );
		} );
	}

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
			var message = response.data && response.data.message ? response.data.message : wps_wpr_ai.i18n.error;
			$card.find( '.wps_wpr_ai_proposal_actions' ).replaceWith(
				$( '<p>' ).addClass( 'wps_wpr_ai_proposal_status' + ( response.success ? '' : ' is-error' ) ).text( message )
			);
			if ( response.success ) {
				$card.addClass( 'apply' === decision ? 'is-applied' : 'is-canceled' );
				history.push( { role: 'user', text: ( 'apply' === decision ? wps_wpr_ai.i18n.applied : wps_wpr_ai.i18n.canceled ) + ': ' + $card.find( 'li' ).text() } );
			}
		} ).fail( function() {
			$card.find( 'button' ).prop( 'disabled', false );
			wps_wpr_add_message( 'error', wps_wpr_ai.i18n.error );
		} );
	} );
} );
