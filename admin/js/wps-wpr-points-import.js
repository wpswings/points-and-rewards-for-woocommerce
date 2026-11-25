/**
 * Points CSV import (Import / Export tab).
 *
 * The file is uploaded and parsed once, then applied in AJAX batches. The
 * server saves progress after every row, so an interrupted import can be
 * resumed without applying rows twice.
 *
 * @package points-and-rewards-for-wooCommerce
 */
( function ( $ ) {
	'use strict';

	var cfg = window.wpsWprPointsImport || {};
	var i18n = cfg.i18n || {};
	var running = false;
	var errors = [];

	function esc( text ) {
		return $( '<div>' ).text( text === undefined || text === null ? '' : String( text ) ).html();
	}

	function fmt( text, a, b ) {
		return String( text ).replace( '%1$s', a ).replace( '%2$s', b );
	}

	// Notices may be moved to the top of the page by the settings screen, so clear them by class.
	function showNotice( html ) {
		$( '.wps_wpr_import_notice' ).remove();
		$( '#wps_wpr_import_messages' ).append( html );
	}

	function showError( message, importId ) {
		var html = '<div class="notice notice-error wps_wpr_import_notice"><p>' + esc( message ) + '</p>';
		if ( importId ) {
			html += '<p><button type="button" class="button button-primary wps_wpr_import_resume_btn" data-import-id="' +
				esc( importId ) + '">' + esc( i18n.resume ) + '</button></p>';
		}
		showNotice( html + '</div>' );
	}

	function setProgress( processed, total, text ) {
		var $box = $( '.wps_wpr_import_progress' );
		$box.prop( 'hidden', false );
		$( '#wps_wpr_import_progress_bar' ).attr( { max: Math.max( 1, total ), value: processed } );
		$( '#wps_wpr_import_progress_text' ).text( text || fmt( i18n.progress, processed, total ) );
	}

	function setBusy( busy ) {
		running = busy;
		$( '#wps_wpr_import_points_csv, .wps_wpr_import_resume_btn, .wps_wpr_import_discard_btn' ).prop( 'disabled', busy );
	}

	function finish( data ) {
		var html = '<div class="notice notice-success wps_wpr_import_notice"><p id="wps_wpr_import_result">' +
			esc( fmt( i18n.done, data.imported, data.skipped ) ) + '</p>';
		if ( errors.length ) {
			html += '<ul class="wps_wpr_import_errors">';
			$.each( errors.slice( 0, 50 ), function ( i, err ) {
				html += '<li>' + esc( err ) + '</li>';
			} );
			html += '</ul>';
			if ( errors.length > 50 ) {
				html += '<p>' + esc( i18n.moreErrors ) + '</p>';
			}
		}
		$( '.wps_wpr_import_resume_notice' ).remove();
		showNotice( html + '</div>' );
	}

	function runBatches( importId, retries ) {
		$.post( cfg.ajaxUrl, { action: 'wps_wpr_points_import_batch', nonce: cfg.nonce, import_id: importId } )
			.done( function ( res ) {
				if ( ! res || ! res.success ) {
					var err = res && res.data ? res.data : {};
					if ( 'wps_wpr_import_busy' === err.code && retries < 5 ) {
						setTimeout( function () {
							runBatches( importId, retries + 1 );
						}, 3000 );
						return;
					}
					showError( err.message || i18n.batchFailed, 'wps_wpr_import_not_found' === err.code ? '' : importId );
					setBusy( false );
					return;
				}
				var d = res.data;
				errors = errors.concat( d.errors || [] );
				setProgress( d.processed, d.total );
				if ( d.done ) {
					finish( d );
					setBusy( false );
				} else {
					runBatches( importId, 0 );
				}
			} )
			.fail( function () {
				showError( i18n.batchFailed, importId );
				setBusy( false );
			} );
	}

	$( document ).on( 'click', '#wps_wpr_import_points_csv', function ( e ) {
		e.preventDefault();
		if ( running ) {
			return;
		}
		var input = $( '#wps_wpr_points_import_file' )[ 0 ];
		var file = input && input.files ? input.files[ 0 ] : null;
		if ( ! file ) {
			showError( i18n.noFile );
			return;
		}
		if ( ! /\.csv$/i.test( file.name ) ) {
			showError( i18n.notCsv );
			return;
		}
		if ( cfg.maxUpload && file.size > cfg.maxUpload ) {
			showError( i18n.tooLarge );
			return;
		}

		var data = new FormData();
		data.append( 'action', 'wps_wpr_points_import_start' );
		data.append( 'nonce', cfg.nonce );
		data.append( 'wps_wpr_points_import_file', file );
		$.each( [ 'wps_wpr_import_mode', 'wps_wpr_import_user_column', 'wps_wpr_import_points_column', 'wps_wpr_import_reason_column' ], function ( i, id ) {
			data.append( id, $( '#' + id ).val() || '' );
		} );

		errors = [];
		$( '.wps_wpr_import_notice' ).remove();
		setBusy( true );
		setProgress( 0, 1, i18n.uploading );

		$.ajax( { url: cfg.ajaxUrl, type: 'POST', data: data, processData: false, contentType: false } )
			.done( function ( res ) {
				if ( ! res || ! res.success ) {
					$( '.wps_wpr_import_progress' ).prop( 'hidden', true );
					showError( res && res.data && res.data.message ? res.data.message : i18n.uploadFailed );
					setBusy( false );
					return;
				}
				// Starting a new import replaces any unfinished one.
				$( '.wps_wpr_import_resume_notice' ).remove();
				setProgress( 0, res.data.total );
				runBatches( res.data.import_id, 0 );
			} )
			.fail( function () {
				$( '.wps_wpr_import_progress' ).prop( 'hidden', true );
				showError( i18n.uploadFailed );
				setBusy( false );
			} );
	} );

	$( document ).on( 'click', '.wps_wpr_import_resume_btn', function ( e ) {
		e.preventDefault();
		if ( running ) {
			return;
		}
		errors = [];
		setBusy( true );
		runBatches( String( $( this ).data( 'import-id' ) ), 0 );
	} );

	$( document ).on( 'click', '.wps_wpr_import_discard_btn', function ( e ) {
		e.preventDefault();
		if ( running || ! window.confirm( i18n.confirmDiscard ) ) {
			return;
		}
		$.post( cfg.ajaxUrl, { action: 'wps_wpr_points_import_discard', nonce: cfg.nonce } ).always( function () {
			$( '.wps_wpr_import_resume_notice, .wps_wpr_import_notice' ).remove();
		} );
	} );

	// Enter in an import field starts the import instead of submitting the page form (the export button comes first).
	$( document ).on( 'keydown', '.wps_wpr_points_import_fields input, .wps_wpr_points_import_fields select', function ( e ) {
		if ( 'Enter' === e.key ) {
			e.preventDefault();
			$( '#wps_wpr_import_points_csv' ).trigger( 'click' );
		}
	} );

	$( window ).on( 'beforeunload', function () {
		return running ? i18n.leave : undefined;
	} );
}( jQuery ) );
