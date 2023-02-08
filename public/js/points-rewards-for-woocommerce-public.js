(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	$( document ).ready(
		function() {

				/*create clipboard */
				var btns      = document.querySelectorAll( 'button' );
				var message   = '';
				var clipboard = new ClipboardJS( btns );
				/*View Benefits of the Membership Role*/
				$( '.wps_wpr_level_benefits' ).click(
					function(){

						var wps_wpr_level = $( this ).data( 'id' );
						jQuery( '#wps_wpr_popup_wrapper_' + wps_wpr_level ).css( 'display', 'block' );

						jQuery( '.wps_wpr_close' ).click(
							function(){

								jQuery( '#wps_wpr_popup_wrapper_' + wps_wpr_level ).css( 'display', 'none' );
							}
						);
					}
				);
				/*Slide toggle on tables*/
				$( document ).on(
					'click',
					'.wps_wpr_common_slider',
					function(){
						$( this ).siblings( '.wps_wpr_common_table' ).slideToggle( "fast" );
						$( this ).children( '.wps_wpr_open_toggle' ).toggleClass( 'wps_wpr_plus_icon' );
					}
				);

				/*Custom Points on Cart Subtotal handling via Ajax*/
				$( document ).on(
					'click',
					'#wps_cart_points_apply',
					function(){
						var user_id                  = $( this ).data( 'id' );
						var user_total_point         = wps_wpr.wps_user_current_points.trim();
						var order_limit              = $( this ).data( 'order-limit' );
						var message                  = '';
						var html                     = '';
						var wps_wpr_cart_points_rate = wps_wpr.wps_wpr_cart_points_rate;
						var wps_wpr_cart_price_rate  = wps_wpr.wps_wpr_cart_price_rate;
						var wps_cart_points          = $( '#wps_cart_points' ).val().trim();

						$( "#wps_wpr_cart_points_notice" ).html( "" );
						$( "wps_wpr_cart_points_success" ).html( "" );

						if (wps_cart_points !== 'undefined' && wps_cart_points !== '' && wps_cart_points !== null && wps_cart_points > 0) {
							if (user_total_point !== null && user_total_point > 0 && parseFloat( user_total_point ) >= parseFloat( wps_cart_points ) ) {

								block( $( '.woocommerce-cart-form' ) );
								block( $( '.woocommerce-checkout' ) );
								var data = {
									action:'wps_wpr_apply_fee_on_cart_subtotal',
									user_id:user_id,
									wps_cart_points:wps_cart_points,
									wps_nonce:wps_wpr.wps_wpr_nonce,
								};
								$.ajax(
									{
										url: wps_wpr.ajaxurl,
										type: "POST",
										data: data,
										dataType :'json',
										success: function(response)
									{
											if (response.result == true) {
												message = response.message;
												$( "#wps_wpr_cart_points_success" ).addClass( 'woocommerce-message' );
												$( "#wps_wpr_cart_points_success" ).removeClass( 'wps_rwpr_settings_display_none_notice' );
												$( "#wps_wpr_cart_points_success" ).html( message );
												$( "#wps_wpr_cart_points_success" ).show();
											} else {
												message = response.message;
												$( "#wps_wpr_cart_points_notice" ).addClass( 'woocommerce-error' );
												$( "#wps_wpr_cart_points_notice" ).removeClass( 'wps_rwpr_settings_display_none_notice' );
												$( "#wps_wpr_cart_points_notice" ).html( message );
												$( "#wps_wpr_cart_points_notice" ).show();
											}
										},
										complete: function(){
											unblock( $( '.woocommerce-cart-form' ) );
											unblock( $( '.woocommerce-cart-form' ) );
											location.reload();
										}
									}
								);
							} else if( order_limit !== 'undefined' && order_limit !== '' && order_limit !== null && order_limit > 0 ){
									if ($( ".woocommerce-cart-form" ).offset() ) {
										$(".wps_error").remove();
										$( 'html, body' ).animate(
											{
												scrollTop: $( ".woocommerce-cart-form" ).offset().top
											},
											800
										);
										var assing_message = '<ul class="woocommerce-error wps_error" role="alert"><li>' + wps_wpr.above_order_limit + '</li></ul>';
										$( assing_message ).insertBefore( $( '.woocommerce-cart-form' ) );
									} else {
										$(".wps_error").remove();
										$( 'html, body' ).animate(
											{
												scrollTop: $( ".custom_point_checkout" ).offset().top
											},
											800
										);
										var assing_message = '<ul class="woocommerce-error wps_error" role="alert"><li>' + wps_wpr.above_order_limit + '</li></ul>';
										$( assing_message ).insertBefore( $( '.custom_point_checkout' ) );
									}

							} else{
									if ($( ".woocommerce-cart-form" ).offset() ) {
										$(".wps_error").remove();
										$( 'html, body' ).animate(
											{
												scrollTop: $( ".woocommerce-cart-form" ).offset().top
											},
											800
										);
										var assing_message = '<ul class="woocommerce-error wps_error" role="alert"><li>' + wps_wpr.not_suffient + '</li></ul>';
										$( assing_message ).insertBefore( $( '.woocommerce-cart-form' ) );
									} else {
										$(".wps_error").remove();
										$( 'html, body' ).animate(
											{
												scrollTop: $( ".custom_point_checkout" ).offset().top
											},
											800
										);
										var assing_message = '<ul class="woocommerce-error wps_error" role="alert"><li>' + wps_wpr.not_suffient + '</li></ul>';
										$( assing_message ).insertBefore( $( '.custom_point_checkout' ) );
									}
								}
							}
						}
					);
				/*Removing Custom Points on Cart Subtotal handling via Ajax*/  // Paypal Issue Change End //
				$( document ).on(
					'click',
					'.wps_remove_virtual_coupon',
					function(e){
						e.preventDefault();
						if ( ! wps_wpr.is_checkout ) {
							block( $( '.woocommerce-cart-form' ) );
						}
						var $this = $(this);
						
						var data = {
							action:'wps_wpr_remove_cart_point',
							coupon_code: $(this).data('coupon'),
							wps_nonce:wps_wpr.wps_wpr_nonce,
							is_checkout:wps_wpr.is_checkout
						};
						$.ajax(
							{
								url: wps_wpr.ajaxurl,
								type: "POST",
								data: data,
								dataType :'json',
								success: function(response)
								{
									if (response.result == true) {
										$( '#wps_cart_points' ).val( '' );
										if ( wps_wpr.is_checkout ) {
											setTimeout(function() {
												$this.closest('tr.cart-discount').remove();
												jQuery(document.body).trigger("update_checkout");
											}, 200);	
										}
										location.reload();
									}
								},
								complete: function(){
									if ( ! wps_wpr.is_checkout ) {
										unblock( $( '.woocommerce-cart-form' ) );
										location.reload();
									}
								}
							}
						);
					}
				);// Paypal Issue Change End //
				/*Removing Custom Points on Cart Subtotal handling via Ajax*/
				/*This is code for the loader*/
				var block = function( $node ) {
					if ( ! is_blocked( $node ) ) {
						$node.addClass( 'processing' ).block(
							{
								message: null,
								overlayCSS: {
									background: '#fff',
									opacity: 0.6
								}
							}
						);
					}
				};
				var is_blocked = function( $node ) {
					return $node.is( '.processing' ) || $node.parents( '.processing' ).length;
				};
				var unblock = function( $node ) {
					$node.removeClass( 'processing' ).unblock();
				};
				/*Add confirmation in the myaccount page*/
				$( document ).on(
					'click',
					'#wps_wpr_upgrade_level_click',
					function(){
						var wps_wpr_confirm = confirm( wps_wpr.confirmation_msg );
						if (wps_wpr_confirm) {
							$( document ).find( '#wps_wpr_upgrade_level' ).click();
						}
					}
				);
				//custom code
				/*Generate custom coupon*/
				$( '.wps_wpr_custom_wallet' ).click(function(){
					var user_id = $( this ).data( 'id' );
					var user_points = $( '#wps_custom_wallet_point_num' ).val().trim();
					$('#wps_wpr_custom_wallet').prop('disabled', true);
					if ( user_points ) {
						var message = '';
						var html = '';
						$( "#wps_wpr_wallet_notification" ).html( "" );
						user_points = parseFloat( user_points );
						var data = {
							action:'wps_wpr_generate_custom_wallet', 
							points:user_points,
							user_id:user_id,
							wps_nonce:wps_wpr.wps_wpr_nonce,
						};
						jQuery( "#wps_wpr_loader" ).show();
						$.ajax({
							url: wps_wpr.ajaxurl,
							type: "POST",
							data: data,
							dataType :'json',
							success: function(response){
								$('#wps_wpr_custom_wallet').prop('disabled', false);
								jQuery( "#wps_wpr_loader" ).hide();
								if ( response.result == true ) {
									var html = '<b style="color:green;">' + response.message + '</b>';
									
								}
								if( response.result == false ) {
									var html = '<b style="color:red;">' + response.message + '</b>';
								}
								$( "#wps_wpr_wallet_notification" ).html( html );
								
							}
						});
					} else {
						$('#wps_wpr_custom_wallet').prop('disabled', false);
						$( "#wps_wpr_wallet_notification" ).html( '<b style="color:red;">' + wps_wpr.empty_notice + '</b>' )
					}
			}
	);
		});
})( jQuery );