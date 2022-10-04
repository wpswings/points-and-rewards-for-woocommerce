jQuery(document).ready( function($) {

	const ajaxUrl  		 = localised.ajaxurl;
	const nonce    		 = localised.nonce;
	const action          = localised.callback;
	const pending_count  = localised.pending_count;
	const pending_orders = localised.pending_orders;
	const completed_orders = localised.completed_orders;
	
	const users	           = localised.completed_users;
	const users_count	   = localised.completed_users_count;
	const searchHTML = '<style>input[type=number], select, numberarea{width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-top: 6px; margin-bottom: 16px; resize: vertical;}input[type=submit]{background-color: #04AA6D; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer;}.container{border-radius: 5px; background-color: #f2f2f2; padding: 20px;}</style></head><div class="container"> <label for="ordername">Order Id</label> <input type="number" id="ordername" name="firstname" placeholder="Order ID to search.."></div>';

	
	/* Close Button Click */
	jQuery( document ).on( 'click','.treat-button', '.swal-button--confirm',function(e) {

		e.preventDefault();
		Swal.fire({
			icon: 'warning',
			title: 'We Have got ' + pending_count + ' Orders Data <br/> <br/> And  '  + users_count +' users Data',
			text: 'Click to start import',
			footer: 'Please do not reload/close this page until prompted',
			showCloseButton: true,
			showCancelButton: true,
			focusConfirm: false,
			confirmButtonText:
			  '<i class="fa fa-thumbs-up"></i> Click to Start!',
			confirmButtonAriaLabel: 'Thumbs up, great!',
			cancelButtonText:
			  '<i class="fa fa-thumbs-down"></i> Stop',
			cancelButtonAriaLabel: 'Thumbs down'
		}).then((result) => {
			if (result.isConfirmed) {

				Swal.fire({
					title   : 'Orders are being imported!',
					html    : 'Do not reload/close this tab.',
					footer  : '<span class="order-progress-report">' + pending_count + ' are left to import',
					didOpen: () => {
						Swal.showLoading()
					}
				});
			
				startImport( pending_orders );
			} else if (result.isDismissed) {
			  Swal.fire('Import Stopped', '', 'info');
			}
		})


	const startImport = ( orders ) => {
		var event   = 'import_single_order';
		var request = { action, event, nonce, orders };
		jQuery.post( ajaxUrl , request ).done(function( response ){
			orders = JSON.parse( response );
		}).then(
		function( orders ) {

			orders = JSON.parse( orders ).orders;
			if ( jQuery.isEmptyObject(orders) ) {
				count = 0;
			} else {
				count = Object.keys(orders).length;
			}
			
			// console.log(count);
			jQuery('.order-progress-report').text( count + ' are left to import' );
	
			if( ! jQuery.isEmptyObject(orders) ) {
				startImport(orders);
			} else {
				// All orders imported!
				Swal.fire({
					title   : 'Users Data are being imported!',
					html    : 'Do not reload/close this tab.',
					footer  : '<span class="order-progress-report">' + users_count + ' are left to import',
					didOpen: () => {
						Swal.showLoading()
					}
				});
				startUser( users );
			}
		}, function(error) {
			console.error(error);
		});
	}
	const startUser = ( users ) => {
		var event   = 'import_users_wps';
		var request = { action, event, nonce, users };
		jQuery.post( ajaxUrl , request ).done(function( response ){
			users = JSON.parse( response );
		}).then(
		function( users ) {
			users = JSON.parse( users ).users;
		
			if ( jQuery.isEmptyObject(users) ) {
				count = 0;
			} else {
				count = Object.keys(users).length;
			}
			jQuery('.order-progress-report').text( count + ' are left to import' );
			if( ! jQuery.isEmptyObject( users ) ) {
				startUser( users );
			} else {
				// All orders imported!
				Swal.fire(' All of the Data are Migrated Successfully !', '', 'success').then(() => {
					window.location.reload();
				});
			}
		}, function(error) {
			console.error(error);
		});
	}
	
});
});
var wps_par_migration_success = function() {
	
	if ( localised.pending_count != 0 || localised.completed_users_count != 0 ) {
		jQuery( ".treat-button" ).click();
		jQuery( ".treat-button" ).show();
	}else{
		jQuery( ".treat-button" ).hide();
		
	}
}