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
				( row.matched ? esc( cfg.i18n.matched ) : esc( cfg.i18n.unmatched ) ) + '</td></tr>';
		} );
		var html = '<p class="wps_wpr_migration_totals">' + esc( cfg.i18n.totals.replace( '%1$s', data.customers ).replace( '%2$s', data.points ) ) + '</p>';
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
		post( 'wps_wpr_migration_preview', { source: $box.data( 'source' ) } )
			.done( function ( res ) {
				if ( res && res.success ) {
					renderPreview( $box, res.data );
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

	$( document ).on( 'click', '.wps_wpr_migration_run_btn', function ( e ) {
		e.preventDefault();
		var $btn = $( this );
		var $box = $btn.closest( '.wps_wpr_migration_source' );
		var mode = $( '#wps_wpr_migration_mode' ).val();
		var again = $( '#wps_wpr_migration_allow_reimport' ).is( ':checked' ) ? 'yes' : 'no';
		var totals = { processed: 0, imported: 0, skipped: 0, already: 0, errors: [] };

		if ( ! window.confirm( cfg.i18n.confirm ) ) {
			return;
		}
		$btn.prop( 'disabled', true );

		function step( offset ) {
			setStatus( $box, esc( cfg.i18n.running.replace( '%s', totals.processed ) ) );
			post( 'wps_wpr_migration_run', { source: $box.data( 'source' ), offset: offset, mode: mode, allow_reimport: again } )
				.done( function ( res ) {
					if ( ! res || ! res.success ) {
						setStatus( $box, esc( res && res.data ? res.data.message : cfg.i18n.failed ), true );
						$btn.prop( 'disabled', false );
						return;
					}
					var d = res.data;
					totals.processed += d.processed;
					totals.imported += d.imported;
					totals.skipped += d.skipped;
					totals.already += d.already;
					totals.errors = totals.errors.concat( d.errors || [] );
					if ( d.done ) {
						var html = '<span class="wps_wpr_migration_summary">' + esc(
							cfg.i18n.done.replace( '%1$s', totals.imported ).replace( '%2$s', totals.skipped ).replace( '%3$s', totals.already )
						) + '</span>';
						if ( totals.errors.length ) {
							html += '<ul class="wps_wpr_migration_errors">';
							$.each( totals.errors.slice( 0, 50 ), function ( i, err ) {
								html += '<li>' + esc( err ) + '</li>';
							} );
							html += '</ul>';
						}
						setStatus( $box, html );
						$btn.prop( 'disabled', false );
					} else {
						step( d.next_offset );
					}
				} )
				.fail( function () {
					setStatus( $box, esc( cfg.i18n.failed ), true );
					$btn.prop( 'disabled', false );
				} );
		}

		step( 0 );
	} );
}( jQuery ) );
