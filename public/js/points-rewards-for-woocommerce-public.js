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
				var btns = document.querySelectorAll( 'button' );
				var message = '';
				var clipboard = new ClipboardJS( btns );
				/*View Benefits of the Membership Role*/
				$( '.mwb_wpr_level_benefits' ).click(
					function(){

						var mwb_wpr_level = $( this ).data( 'id' );
						jQuery( '#mwb_wpr_popup_wrapper_' + mwb_wpr_level ).css( 'display', 'block' );

						jQuery( '.mwb_wpr_close' ).click(
							function(){

								jQuery( '#mwb_wpr_popup_wrapper_' + mwb_wpr_level ).css( 'display', 'none' );
							}
						);
					}
				);
				/*Slide toggle on tables*/
				$( document ).on(
					'click',
					'.mwb_wpr_common_slider',
					function(){
						$( this ).siblings( '.mwb_wpr_common_table' ).slideToggle( "fast" );
						$( this ).children( '.mwb_wpr_open_toggle' ).toggleClass( 'mwb_wpr_plus_icon' );
					}
				);

				/*Custom Points on Cart Subtotal handling via Ajax*/
				$( document ).on(
					'click',
					'#mwb_cart_points_apply',
					function(){
						var user_id = $( this ).data( 'id' );
						var user_total_point = $( this ).data( 'point' );
						var order_limit = $( this ).data( 'order-limit' );
						var message = ''; var html = '';
						var mwb_wpr_cart_points_rate = mwb_wpr.mwb_wpr_cart_points_rate;
						var mwb_wpr_cart_price_rate = mwb_wpr.mwb_wpr_cart_price_rate;
						var mwb_cart_points = $( '#mwb_cart_points' ).val();
						$( "#mwb_wpr_cart_points_notice" ).html( "" );
						$( "mwb_wpr_cart_points_success" ).html( "" );
						if (mwb_cart_points !== 'undefined' && mwb_cart_points !== '' && mwb_cart_points !== null && mwb_cart_points > 0) {
							if (user_total_point !== null && user_total_point > 0 && user_total_point >= mwb_cart_points ) {
								block( $( '.woocommerce-cart-form' ) );
								block( $( '.woocommerce-checkout' ) );
								var data = {
									action:'mwb_wpr_apply_fee_on_cart_subtotal',
									user_id:user_id,
									mwb_cart_points:mwb_cart_points,
									mwb_nonce:mwb_wpr.mwb_wpr_nonce,
								};
								$.ajax(
									{
										url: mwb_wpr.ajaxurl,
										type: "POST",
										data: data,
										dataType :'json',
										success: function(response)
									{
											if (response.result == true) {
												message = response.message;
												html = message;
												$( "#mwb_wpr_cart_points_success" ).removeClass( 'mwb_rwpr_settings_display_none_notice' );
												$( "#mwb_wpr_cart_points_success" ).html( html );
												$( "#mwb_wpr_cart_points_success" ).show();
											} else {
												message = response.message;
												html = message;
												$( "#mwb_wpr_cart_points_notice" ).removeClass( 'mwb_rwpr_settings_display_none_notice' );
												$( "#mwb_wpr_cart_points_notice" ).html( html );
												$( "#mwb_wpr_cart_points_notice" ).show();
											}
										},
										complete: function(){
											unblock( $( '.woocommerce-cart-form' ) );
											unblock( $( '.woocommerce-cart-form' ) );
											location.reload();
										}
									}
								);
							} else {
								if( order_limit !== 'undefined' && order_limit !== '' && order_limit !== null && order_limit > 0 ){
									if ($( ".woocommerce-cart-form" ).offset() ) {
										$( 'html, body' ).animate(
											{
												scrollTop: $( ".woocommerce-cart-form" ).offset().top
											},
											800
										);
										var assing_message = '<ul class="woocommerce-error" role="alert"><li>' + mwb_wpr.above_order_limit + '</li></ul>';
										$( assing_message ).insertBefore( $( '.woocommerce-cart-form' ) );
									} else {
										$( 'html, body' ).animate(
											{
												scrollTop: $( ".custom_point_checkout" ).offset().top
											},
											800
										);
										var assing_message = '<ul class="woocommerce-error" role="alert"><li>' + mwb_wpr.above_order_limit + '</li></ul>';
										$( assing_message ).insertBefore( $( '.custom_point_checkout' ) );
									}

								} else{
									if ($( ".woocommerce-cart-form" ).offset() ) {
										$( 'html, body' ).animate(
											{
												scrollTop: $( ".woocommerce-cart-form" ).offset().top
											},
											800
										);
										var assing_message = '<ul class="woocommerce-error" role="alert"><li>' + mwb_wpr.not_suffient + '</li></ul>';
										$( assing_message ).insertBefore( $( '.woocommerce-cart-form' ) );
									} else {
										$( 'html, body' ).animate(
											{
												scrollTop: $( ".custom_point_checkout" ).offset().top
											},
											800
										);
										var assing_message = '<ul class="woocommerce-error" role="alert"><li>' + mwb_wpr.not_suffient + '</li></ul>';
										$( assing_message ).insertBefore( $( '.custom_point_checkout' ) );
									}
								}
							}
						}
					}
				);

				/*Removing Custom Points on Cart Subtotal handling via Ajax*/
				$( document ).on(
					'click',
					'#mwb_wpr_remove_cart_point',
					function(){
						block( $( '.woocommerce-cart-form' ) );
						var data = {
							action:'mwb_wpr_remove_cart_point',
							mwb_nonce:mwb_wpr.mwb_wpr_nonce
						};
						$.ajax(
							{
								url: mwb_wpr.ajaxurl,
								type: "POST",
								data: data,
								dataType :'json',
								success: function(response)
							{
									if (response.result == true) {
										$( '#mwb_cart_points' ).val( '' );
									}
								},
								complete: function(){
									unblock( $( '.woocommerce-cart-form' ) );
									location.reload();
								}
							}
						);
					}
				);
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
					'#mwb_wpr_upgrade_level_click',
					function(){
						var mwb_wpr_confirm = confirm( mwb_wpr.confirmation_msg );
						if (mwb_wpr_confirm) {
							  $( document ).find( '#mwb_wpr_upgrade_level' ).click();
						}
					}
				);
		}
	);

})( jQuery );
