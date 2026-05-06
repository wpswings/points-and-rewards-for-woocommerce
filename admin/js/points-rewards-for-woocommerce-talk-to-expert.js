jQuery( function( $ ) {
	var modalSelector = '[data-wps-wpr-expert-modal]';
	var openTriggerSelector = '[data-wps-wpr-open-expert-modal]';
	var closeTriggerSelector = '[data-wps-wpr-expert-modal-close]';
	var formSelector = '[data-wps-wpr-expert-modal-form]';
	var statusSelector = '[data-wps-wpr-expert-modal-status]';
	var successSelector = '[data-wps-wpr-expert-modal-success]';
	var successMessageSelector = '[data-wps-wpr-expert-modal-success-message]';
	var bodyLockClass = 'wps-rma-expert-modal-open';
	var successCloseTimer = null;

	function wpsWprGetExpertModal() {
		return $( modalSelector ).first();
	}

	function wpsWprSetExpertStatus( $modal, message, statusType ) {
		var $status = $modal.find( statusSelector ).first();

		if ( ! $status.length ) {
			return;
		}

		if ( ! message ) {
			$status.attr( 'hidden', true ).removeClass( 'is-success is-error' ).text( '' );
			return;
		}

		$status.removeAttr( 'hidden' ).removeClass( 'is-success is-error' ).addClass( 'is-' + statusType ).text( message );
	}

	function wpsWprResetExpertModalState( $modal ) {
		var $form = $modal.find( formSelector ).first();
		var $success = $modal.find( successSelector ).first();
		var $successMessage = $modal.find( successMessageSelector ).first();
		var $submitButton = $form.find( 'button[type="submit"]' ).first();

		if ( $form.length ) {
			if ( $form.get( 0 ) && 'function' === typeof $form.get( 0 ).reset ) {
				$form.get( 0 ).reset();
			}

			$form.removeAttr( 'hidden' ).css( 'display', '' );
		}

		if ( $submitButton.length ) {
			$submitButton
				.prop( 'disabled', false )
				.text( $submitButton.attr( 'data-submit-label' ) || 'Submit Request' );
		}

		if ( $success.length ) {
			$success.attr( 'hidden', true ).removeClass( 'is-visible' );
		}

		if ( $successMessage.length ) {
			$successMessage.text( 'Thank you for submitting your request.' );
		}

		wpsWprSetExpertStatus( $modal, '', '' );
	}

		function wpsWprShowExpertSuccessState( $modal, message ) {
			var $form = $modal.find( formSelector ).first();
			var $success = $modal.find( successSelector ).first();
			var $successMessage = $modal.find( successMessageSelector ).first();

			if ( $form.length ) {
				$form.attr( 'hidden', true ).css( 'display', 'none' );
			}

			wpsWprSetExpertStatus( $modal, '', '' );

		if ( $successMessage.length ) {
			$successMessage.text( message );
		}

			if ( $success.length ) {
				$success.removeAttr( 'hidden' );

				window.setTimeout( function() {
					$success.addClass( 'is-visible' );
				}, 80 );
			}
		}

	function wpsWprToggleExpertModal( shouldOpen ) {
		var $modal = wpsWprGetExpertModal();

		if ( ! $modal.length ) {
			return;
		}

		if ( successCloseTimer ) {
			window.clearTimeout( successCloseTimer );
			successCloseTimer = null;
		}

		if ( shouldOpen ) {
			$modal.removeAttr( 'hidden' );
			$( 'body' ).addClass( bodyLockClass );
			wpsWprResetExpertModalState( $modal );
			return;
		}

		$modal.attr( 'hidden', true );
		$( 'body' ).removeClass( bodyLockClass );
		wpsWprResetExpertModalState( $modal );
	}

	function wpsWprNormalizeExpertPayload( formElement ) {
		var payload = {};
		var formData = new window.FormData( formElement );

		formData.forEach( function( value, key ) {
			var normalizedKey = key.replace( /\[\]$/, '' );

			if ( Object.prototype.hasOwnProperty.call( payload, normalizedKey ) ) {
				if ( ! Array.isArray( payload[ normalizedKey ] ) ) {
					payload[ normalizedKey ] = [ payload[ normalizedKey ] ];
				}

				payload[ normalizedKey ].push( value );
				return;
			}

			payload[ normalizedKey ] = value;
		} );

		return payload;
	}

	function wpsWprResolveExpertAjaxConfig( $form ) {
		var localizedObject = window.wps_wpr_object || {};
		var fallbackAction = 'wps_wpr_submit_talk_to_expert';

		return {
			ajaxurl: $form.attr( 'data-wps-wpr-expert-ajaxurl' ) || localizedObject.ajaxurl || window.ajaxurl || '',
			action: $form.attr( 'data-wps-wpr-expert-action' ) || localizedObject.wps_wpr_expert_action || fallbackAction,
			nonce: $form.attr( 'data-wps-wpr-expert-nonce' ) || localizedObject.wps_wpr_expert_nonce || ''
		};
	}

	$( document ).off( 'click.wpsWprExpertModalOpen', openTriggerSelector ).on( 'click.wpsWprExpertModalOpen', openTriggerSelector, function( event ) {
		event.preventDefault();
		wpsWprToggleExpertModal( true );
	} );

	$( document ).off( 'click.wpsWprExpertModalClose', closeTriggerSelector ).on( 'click.wpsWprExpertModalClose', closeTriggerSelector, function( event ) {
		event.preventDefault();
		wpsWprToggleExpertModal( false );
	} );

	$( document ).off( 'keydown.wpsWprExpertModal' ).on( 'keydown.wpsWprExpertModal', function( event ) {
		if ( 'Escape' === event.key ) {
			wpsWprToggleExpertModal( false );
		}
	} );

		$( document ).off( 'submit.wpsWprExpertModal', formSelector ).on( 'submit.wpsWprExpertModal', formSelector, function( event ) {
			var $form = $( this );
			var $modal = $form.closest( modalSelector );
			var $submitButton = $form.find( 'button[type="submit"]' ).first();
			var submitLabel = $submitButton.attr( 'data-submit-label' ) || $submitButton.text();
			var loadingLabel = $submitButton.attr( 'data-loading-label' ) || 'Sending...';
			var requestConfig = wpsWprResolveExpertAjaxConfig( $form );

			event.preventDefault();
			wpsWprSetExpertStatus( $modal, '', '' );
			$submitButton.prop( 'disabled', true ).text( loadingLabel );

			if ( ! requestConfig.ajaxurl || ! requestConfig.nonce ) {
				wpsWprSetExpertStatus( $modal, 'We could not submit your request right now. Please try again.', 'error' );
				$submitButton.prop( 'disabled', false ).text( submitLabel );
				return;
			}

			$.ajax( {
				url: requestConfig.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: requestConfig.action,
					nonce: requestConfig.nonce,
					form_data: JSON.stringify( wpsWprNormalizeExpertPayload( $form.get( 0 ) ) )
				}
			} ).done( function( response ) {
				var isSuccess = !! ( response && response.success );
				var message = response && response.data && response.data.message ? response.data.message : '';

				if ( ! message ) {
					message = isSuccess ? 'Thank you for submitting your request.' : 'We could not submit your request right now. Please try again.';
				}

				if ( isSuccess && message ) {
					wpsWprShowExpertSuccessState( $modal, message );

					if ( successCloseTimer ) {
						window.clearTimeout( successCloseTimer );
					}

					successCloseTimer = window.setTimeout( function() {
						wpsWprToggleExpertModal( false );
					}, 3000 );
					return;
				}

				wpsWprSetExpertStatus( $modal, message, 'error' );
			} ).fail( function( xhr ) {
				var message = 'We could not submit your request right now. Please try again.';

				if ( xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ) {
					message = xhr.responseJSON.data.message;
				}

				wpsWprSetExpertStatus( $modal, message, 'error' );
			} ).always( function() {
				$submitButton.prop( 'disabled', false ).text( submitLabel );
			} );
		} );
} );
