/**
 * Points migration from other loyalty plugins (Import / Export tab).
 *
 * @package points-and-rewards-for-wooCommerce
 */
( function ( $ ) {
	'use strict';

	var cfg = window.wpsWprMigration || {};

	function post( action, data ) {
		return $.post( cfg.ajaxUrl, $.extend( { action: action, nonce: cfg.nonce }, data ) );
	}

	function esc( text ) {
		return $( '<div>' ).text( text === undefined || text === null ? '' : String( text ) ).html();
	}

	function setStatus( $box, html, isError ) {
		$box.find( '.wps_wpr_migration_status' )
			.toggleClass( 'wps_wpr_migration_error', !! isError )
			.html( html );
	}

	function renderPreview( $box, data ) {
		var rows = '';
		$.each( data.sample || [], function ( i, row ) {
			rows += '<tr><td>' + esc( row.email || ( '#' + row.user_id ) ) + '</td><td>' + esc( row.points ) + '</td><td>' +
				( row.matched ? esc( cfg.i18n.matched ) : esc( cfg.i18n.noMatch ) ) + '</td></tr>';
		} );
		var html = '<p class="wps_wpr_migration_totals">' + esc( cfg.i18n.totals.replace( '%1$s', data.customers ).replace( '%2$s', data.points ) ) + '</p>';
		if ( data.unmatched > 0 ) {
			html += '<p class="wps_wpr_migration_unmatched">' + esc( cfg.i18n.unmatched.replace( '%1$s', data.unmatched ).replace( '%2$s', data.unmatched_points ) ) + '</p>';
		}
		if ( rows ) {
			html += '<table class="widefat striped wps_wpr_migration_sample"><thead><tr><th>' + esc( cfg.i18n.customer ) + '</th><th>' +
				esc( cfg.i18n.points ) + '</th><th>' + esc( cfg.i18n.match ) + '</th></tr></thead><tbody>' + rows + '</tbody></table>';
		}
		$box.find( '.wps_wpr_migration_preview' ).html( html );
	}

	$( document ).on( 'click', '.wps_wpr_migration_preview_btn', function ( e ) {
		e.preventDefault();
		var $box = $( this ).closest( '.wps_wpr_migration_source' );
		setStatus( $box, esc( cfg.i18n.loading ) );
		post( 'wps_wpr_migration_preview', { source: $box.data( 'source' ), include_banned: includeBanned( $box ) } )
			.done( function ( res ) {
				if ( res && res.success ) {
					renderPreview( $box, res.data );
					if ( res.data.run ) {
						showResume( $box, res.data.run );
					}
					setStatus( $box, '' );
					$box.find( '.wps_wpr_migration_run_btn' ).prop( 'disabled', false );
				} else {
					setStatus( $box, esc( res && res.data ? res.data.message : cfg.i18n.failed ), true );
				}
			} )
			.fail( function () {
				setStatus( $box, esc( cfg.i18n.failed ), true );
			} );
	} );

	function includeBanned( $box ) {
		return $box.find( '.wps_wpr_migration_include_banned' ).is( ':checked' ) ? 'yes' : 'no';
	}

	// Offer Resume / Start over for an unfinished run.
	function showResume( $box, run ) {
		$box.find( '.wps_wpr_migration_resume' ).remove();
		var text = cfg.i18n.resume.replace( '%1$s', run.processed ).replace( '%2$s', run.total );
		$box.find( '.wps_wpr_migration_status' ).before(
			'<div class="wps_wpr_migration_resume" data-run="' + esc( run.run_id ) + '"><p>' + esc( text ) + '</p><p>' +
			'<button type="button" class="button button-primary wps_wpr_migration_resume_btn">' + esc( cfg.i18n.resumeBtn ) + '</button> ' +
			'<button type="button" class="button wps_wpr_migration_restart_btn">' + esc( cfg.i18n.restart ) + '</button></p></div>'
		);
	}

	function summary( run, errors ) {
		var html = '<span class="wps_wpr_migration_summary">' + esc(
			cfg.i18n.done.replace( '%1$s', run.imported ).replace( '%2$s', run.skipped ).replace( '%3$s', run.already )
		) + '</span>';
		if ( errors.length ) {
			html += '<ul class="wps_wpr_migration_errors">';
			$.each( errors.slice( 0, 50 ), function ( i, err ) {
				html += '<li>' + esc( err ) + '</li>';
			} );
			html += '</ul>';
		}
		return html;
	}

	// Process batches of a run until it is done. The server keeps the offset and totals,
	// so a failed request can be resumed without applying any customer twice.
	function runBatches( $box, run ) {
		var $buttons = $box.find( 'button' );
		var errors = [];
		$box.find( '.wps_wpr_migration_resume' ).remove();
		$buttons.prop( 'disabled', true );

		function fail( message ) {
			setStatus( $box, esc( message ), true );
			$buttons.prop( 'disabled', false );
			showResume( $box, run );
		}

		function step() {
			setStatus( $box, esc( cfg.i18n.running.replace( '%s', run.processed ) ) );
			post( 'wps_wpr_migration_run', { source: $box.data( 'source' ), run_id: run.run_id } )
				.done( function ( res ) {
					if ( ! res || ! res.success ) {
						setStatus( $box, esc( res && res.data ? res.data.message : cfg.i18n.failed ), true );
						$buttons.prop( 'disabled', false );
						return;
					}
					run = res.data.run;
					errors = errors.concat( res.data.errors || [] );
					if ( res.data.done ) {
						setStatus( $box, summary( run, errors ) );
						$buttons.prop( 'disabled', false );
					} else {
						step();
					}
				} )
				.fail( function () {
					fail( cfg.i18n.failed );
				} );
		}

		step();
	}

	function start( $box, restart ) {
		post( 'wps_wpr_migration_start', {
			source: $box.data( 'source' ),
			mode: $( '#wps_wpr_migration_mode' ).val(),
			allow_reimport: $( '#wps_wpr_migration_allow_reimport' ).is( ':checked' ) ? 'yes' : 'no',
			include_banned: includeBanned( $box ),
			restart: restart ? 'yes' : 'no'
		} )
			.done( function ( res ) {
				if ( res && res.success ) {
					runBatches( $box, res.data.run );
				} else if ( res && res.data && res.data.run ) {
					setStatus( $box, esc( res.data.message ), true );
					showResume( $box, res.data.run );
				} else {
					setStatus( $box, esc( res && res.data ? res.data.message : cfg.i18n.failed ), true );
				}
			} )
			.fail( function () {
				setStatus( $box, esc( cfg.i18n.failed ), true );
			} );
	}

	$( document ).on( 'click', '.wps_wpr_migration_run_btn', function ( e ) {
		e.preventDefault();
		var $box = $( this ).closest( '.wps_wpr_migration_source' );
		if ( ! window.confirm( cfg.i18n.confirm ) ) {
			return;
		}
		start( $box, false );
	} );

	$( document ).on( 'click', '.wps_wpr_migration_resume_btn', function ( e ) {
		e.preventDefault();
		var $box = $( this ).closest( '.wps_wpr_migration_source' );
		var runId = $( this ).closest( '.wps_wpr_migration_resume' ).data( 'run' );
		runBatches( $box, { run_id: runId, processed: 0, imported: 0, skipped: 0, already: 0 } );
	} );

	$( document ).on( 'click', '.wps_wpr_migration_restart_btn', function ( e ) {
		e.preventDefault();
		var $box = $( this ).closest( '.wps_wpr_migration_source' );
		if ( ! window.confirm( cfg.i18n.restartConfirm ) ) {
			return;
		}
		start( $box, true );
	} );
}( jQuery ) );
