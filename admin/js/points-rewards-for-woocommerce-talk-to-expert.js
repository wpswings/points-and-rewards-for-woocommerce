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

	/**
	 * Normalize notification sections so each title controls only its own content.
	 *
	 * @return {void}
	 */
	function wpsWprNormalizeNotificationSections() {
		var $wrapper = $( '#wps_rwpr_setting_wrapper[data-wps-rma-active-tab="points-notification"]' ).first();
		if ( ! $wrapper.length ) {
			return;
		}

		var $container = $wrapper.find( '.wps_wpr_notifications_table .wps_wpr_general_wrapper' ).first();
		if ( ! $container.length || $container.data( 'wpsWprNotificationNormalized' ) ) {
			return;
		}

		var $nodes = $container.find( '.wps_wpr_general_sign_title, .wps_wpr_general_row' );
		if ( ! $nodes.length ) {
			return;
		}

		var sections = [];
		var currentSection = null;

		$nodes.each( function() {
			var $node = $( this );
			if ( $node.hasClass( 'wps_wpr_general_sign_title' ) ) {
				var $existingSection = $node.closest( '.wps_wpr_notification_section_wrap' );
				currentSection = {
					title: $node,
					rows: [],
					id: $existingSection.attr( 'id' ) || ''
				};
				sections.push( currentSection );
				return;
			}

			if ( currentSection ) {
				currentSection.rows.push( $node );
			}
		} );

		if ( ! sections.length ) {
			return;
		}

		$nodes.detach();
		$container.find( '.wps_wpr_general_row_wrap, .wps_wpr_section_content' ).remove();

		sections.forEach( function( section, index ) {
			var $wrap = $( '<div class="wps_wpr_general_row_wrap wps_wpr_notification_section_wrap"></div>' );
			var $content = $( '<div class="wps_wpr_section_content"></div>' );

			if ( section.id ) {
				$wrap.attr( 'id', section.id );
			}

			section.title.removeClass( 'wps_wpr_section_active' );
			$wrap.append( section.title );

			section.rows.forEach( function( $row ) {
				$content.append( $row );
			} );

			if ( 0 === index ) {
				section.title.addClass( 'wps_wpr_section_active' );
				$content.show();
			} else {
				$content.hide();
			}

			$wrap.append( $content );
			$container.append( $wrap );
		} );

		$container.data( 'wpsWprNotificationNormalized', true );
	}

	/**
	 * Keep accordion toggling scoped to the section that was clicked.
	 *
	 * @return {void}
	 */
	function wpsWprRebindSectionAccordion() {
		$( document ).off( 'click', '.wps_wpr_general_sign_title' );
		$( document ).on( 'click', '.wps_wpr_general_sign_title', function( event ) {
			if ( $( event.target ).closest( 'a' ).length ) {
				return;
			}

			var $title = $( this );
			var $content = $title.next( '.wps_wpr_section_content' );

			if ( ! $content.length ) {
				var $wrap = $title.closest( '.wps_wpr_general_row_wrap' );
				$content = $wrap.children( '.wps_wpr_section_content' ).first();
			}

			if ( ! $content.length ) {
				var $rows = $title.nextUntil( '.wps_wpr_general_sign_title' );
				if ( $rows.length ) {
					$rows.wrapAll( '<div class="wps_wpr_section_content"></div>' );
					$content = $title.next( '.wps_wpr_section_content' );
				}
			}

			if ( $content.length ) {
				$content.stop( true, true ).slideToggle( 300 );
				$title.toggleClass( 'wps_wpr_section_active' );
			}
		} );
	}

	/**
	 * Force notification dashboard layout to be mobile-safe on small screens.
	 * Keeps desktop two-column layout intact and preserves all tab behavior.
	 *
	 * @return {void}
	 */
	function wpsWprApplyNotificationResponsiveLayout() {
		var $wrapper = $( '#wps_rwpr_setting_wrapper[data-wps-rma-active-tab="points-notification"]' ).first();
		if ( ! $wrapper.length ) {
			return;
		}

		var $layout = $wrapper.find( '.wps_rma_dashboard_layout' ).first();
		if ( ! $layout.length ) {
			return;
		}

		var $content = $layout.find( '> .wps_rwpr_content_template' ).first();
		var $sidebar = $layout.find( '> .wps_rma_right_sidebar' ).first();
		var isMobile = window.matchMedia( '(max-width: 900px)' ).matches;

		$layout[0].style.setProperty( 'display', 'grid', 'important' );
		$layout[0].style.setProperty( 'align-items', 'start', 'important' );
		$layout[0].style.setProperty( 'gap', isMobile ? '12px' : '14px', 'important' );
		$layout[0].style.setProperty( 'grid-template-columns', isMobile ? 'minmax(0,1fr)' : 'minmax(0,1fr) 275px', 'important' );

		if ( $content.length ) {
			$content[0].style.setProperty( 'grid-column', '1', 'important' );
			$content[0].style.setProperty( 'grid-row', '1', 'important' );
			$content[0].style.setProperty( 'max-width', '100%', 'important' );
			$content[0].style.setProperty( 'width', 'auto', 'important' );
			$content[0].style.setProperty( 'min-width', '0', 'important' );
		}

		if ( $sidebar.length ) {
			$sidebar[0].style.setProperty( 'display', 'grid', 'important' );
			$sidebar[0].style.setProperty( 'gap', '12px', 'important' );
			$sidebar[0].style.setProperty( 'align-content', 'start', 'important' );
			$sidebar[0].style.setProperty( 'grid-column', isMobile ? '1' : '2', 'important' );
			$sidebar[0].style.setProperty( 'grid-row', isMobile ? '2' : '1', 'important' );
			$sidebar[0].style.setProperty( 'max-width', '100%', 'important' );
			$sidebar[0].style.setProperty( 'width', '100%', 'important' );
			$sidebar[0].style.setProperty( 'min-width', '0', 'important' );
		}
	}

	/**
	 * Ensure points-notification save button stays visible even if global
	 * admin floating-button rules offset it off-canvas.
	 *
	 * @return {void}
	 */
	function wpsWprEnsureNotificationSaveButtonVisible() {
		var $saveBtn = $( '#wps_rwpr_setting_wrapper input[name="wps_wpr_save_notification"].wps_wpr_save_changes' ).first();
		if ( ! $saveBtn.length ) {
			return;
		}

		var $submitWrap = $saveBtn.closest( '.submit' );
		if ( $submitWrap.length ) {
			$submitWrap[0].style.setProperty( 'display', 'block', 'important' );
			$submitWrap[0].style.setProperty( 'visibility', 'visible', 'important' );
			$submitWrap[0].style.setProperty( 'opacity', '1', 'important' );
			$submitWrap[0].style.setProperty( 'position', 'sticky', 'important' );
			$submitWrap[0].style.setProperty( 'bottom', '0', 'important' );
		}

		$saveBtn[0].style.setProperty( 'display', 'inline-flex', 'important' );
		$saveBtn[0].style.setProperty( 'visibility', 'visible', 'important' );
		$saveBtn[0].style.setProperty( 'opacity', '1', 'important' );
		$saveBtn[0].style.setProperty( 'pointer-events', 'auto', 'important' );
		$saveBtn[0].style.setProperty( 'position', 'fixed', 'important' );
		if ( window.matchMedia( '(max-width: 782px)' ).matches ) {
			$saveBtn[0].style.setProperty( 'left', '12px', 'important' );
		} else if ( window.matchMedia( '(max-width: 960px)' ).matches ) {
			$saveBtn[0].style.setProperty( 'left', '16px', 'important' );
		} else if ( $( 'body' ).hasClass( 'folded' ) ) {
			$saveBtn[0].style.setProperty( 'left', 'calc(36px + 24px)', 'important' );
		} else {
			$saveBtn[0].style.setProperty( 'left', 'calc(160px + 24px)', 'important' );
		}
		$saveBtn[0].style.setProperty( 'right', 'auto', 'important' );
		$saveBtn[0].style.setProperty( 'top', 'auto', 'important' );
		$saveBtn[0].style.setProperty( 'bottom', window.matchMedia( '(max-width: 782px)' ).matches ? '12px' : '20px', 'important' );
		$saveBtn[0].style.setProperty( 'transform', 'none', 'important' );
		$saveBtn[0].style.setProperty( 'z-index', '100100', 'important' );
	}

	/**
	 * Persist visibility styles because other delayed scripts/styles can
	 * override/hide the save button after initial paint.
	 *
	 * @return {void}
	 */
	function wpsWprLockNotificationSaveButtonVisibility() {
		wpsWprEnsureNotificationSaveButtonVisible();

		if ( ! window.wpsWprNotificationSaveBtnInterval ) {
			window.wpsWprNotificationSaveBtnInterval = window.setInterval( function() {
				wpsWprEnsureNotificationSaveButtonVisible();
			}, 300 );
		}

		if ( ! window.wpsWprNotificationSaveBtnObserver ) {
			window.wpsWprNotificationSaveBtnObserver = new window.MutationObserver( function() {
				wpsWprEnsureNotificationSaveButtonVisible();
			} );

			window.wpsWprNotificationSaveBtnObserver.observe( document.body, {
				childList: true,
				subtree: true,
				attributes: true,
				attributeFilter: [ 'style', 'class' ]
			} );
		}
	}

	/*
	 * Notification tab DOM/style manipulation is intentionally disabled here.
	 * It was conflicting with save button rendering in this tab.
	 */
	function wpsWprNormalizeNotificationVisualState() {
		var $wrapper = $( '#wps_rwpr_setting_wrapper[data-wps-rma-active-tab="points-notification"]' ).first();
		if ( ! $wrapper.length ) {
			return;
		}

		var $sections = $wrapper.find( '.wps_wpr_notifications_table .wps_wpr_notification_section_wrap' );
		if ( ! $sections.length ) {
			return;
		}

		$sections.each( function() {
			var $section = $( this );
			var $title = $section.children( '.wps_wpr_general_sign_title' ).first();
			var $content = $section.children( '.wps_wpr_section_content' ).first();

			if ( $content.length ) {
				$content.hide();
			}

			if ( $title.length ) {
				$title.removeClass( 'wps_wpr_section_active' );
			}
			} );
	}

	function wpsWprEnsureNotificationSectionWrappers() {
		var $wrapper = $( '#wps_rwpr_setting_wrapper[data-wps-rma-active-tab="points-notification"]' ).first();
		if ( ! $wrapper.length ) {
			return;
		}

		var $container = $wrapper.find( '.wps_wpr_notifications_table .wps_wpr_general_wrapper' ).first();
		if ( ! $container.length ) {
			return;
		}

		$container.children( '.wps_wpr_general_sign_title' ).each( function() {
			var $title = $( this );
			if ( $title.closest( '.wps_wpr_notification_section_wrap' ).length ) {
				return;
			}

			var $wrap = $( '<div class="wps_wpr_general_row_wrap wps_wpr_notification_section_wrap"></div>' );
			var $next = $title.next();
			var $content = $( '<div class="wps_wpr_section_content"></div>' );

			$title.before( $wrap );
			$wrap.append( $title );

			if ( $next.hasClass( 'wps_wpr_section_content' ) ) {
				$wrap.append( $next );
				return;
			}

			var $rows = $title.nextUntil( '.wps_wpr_general_sign_title, .wps_wpr_general_row_wrap' );
			if ( $rows.length ) {
				$content.append( $rows );
			}
			$wrap.append( $content );
		} );
	}

	wpsWprEnsureNotificationSectionWrappers();
	wpsWprNormalizeNotificationVisualState();
	wpsWprLockNotificationSaveButtonVisibility();
	setTimeout( wpsWprEnsureNotificationSectionWrappers, 120 );
	setTimeout( wpsWprNormalizeNotificationVisualState, 120 );
	setTimeout( wpsWprLockNotificationSaveButtonVisibility, 120 );
	setTimeout( wpsWprEnsureNotificationSectionWrappers, 420 );
	setTimeout( wpsWprNormalizeNotificationVisualState, 420 );
	setTimeout( wpsWprLockNotificationSaveButtonVisibility, 420 );

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
