(function($) {
    'use strict';
    
    jQuery(document).ready(function($){

		// ============= Append Add a points section html ============

		// show html on cart page.
		if ( 1 == wps_wpr.is_cart_redeem_sett_enable ) {

			if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {

				setTimeout(() => {
					if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {
	
						jQuery('.wp-block-woocommerce-cart-order-summary-coupon-form-block').append('<div id="wps_wpr_button_to_add_points_section"><a href="#">' + wps_wpr.wps_add_a_points + '</a></div>');
					}
				}, 1000);

				jQuery(document).on('mouseover', '.woocommerce-cart.woocommerce-page', function(){
					if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {
	
						jQuery('.wp-block-woocommerce-cart-order-summary-coupon-form-block').append('<div id="wps_wpr_button_to_add_points_section"><a href="#">' + wps_wpr.wps_add_a_points + '</a></div>');
					}
				});
			}
		}

		// show html on checkout page.
		if ( 1 == wps_wpr.is_checkout_redeem_enable ) {
			if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {

				setTimeout(() => {
					if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {

						jQuery('.wp-block-woocommerce-checkout-order-summary-coupon-form-block').append('<div id="wps_wpr_button_to_add_points_section"><a href="#">' + wps_wpr.wps_add_a_points + '</a></div>');
					}
				}, 1000);

				jQuery(document).on('mouseover', '.woocommerce-checkout.woocommerce-page', function(){
					if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {

						jQuery('.wp-block-woocommerce-checkout-order-summary-coupon-form-block').append('<div id="wps_wpr_button_to_add_points_section"><a href="#">' + wps_wpr.wps_add_a_points + '</a></div>');
					}
				});
			}
		}

		// Append Points apply section on cart and checkout page.
		jQuery(document).on('click', '#wps_wpr_button_to_add_points_section', function(e){

			e.preventDefault();
			jQuery(this).hide();

			var minimum_redeem_points   = parseInt( wps_wpr.get_min_redeem_req );
			var wps_user_current_points = parseInt( wps_wpr.wps_user_current_points );
			if ( minimum_redeem_points <= wps_user_current_points ) {

				jQuery('.wp-block-woocommerce-cart-order-summary-coupon-form-block').append('<div class="wps_wpr_apply_custom_points custom_point_checkout wps_wpr_append_points_apply_html"><input type="number" min="0" name="wps_cart_points" class="input-text" id="wps_cart_points" value="" placeholder="' + wps_wpr.wps_points_name + '"/><button class="button wps_cart_points_apply" name="wps_cart_points_apply" id="wps_cart_points_apply" data-order-limit="0">' + wps_wpr.wps_apply_points + '</button><p>' + wps_wpr_cart_block_obj. available_points_msg + ' : ' + wps_wpr_cart_block_obj.current__points + ' </p></div>');
				jQuery('.wp-block-woocommerce-checkout-order-summary-coupon-form-block').append('<div class="wps_wpr_apply_custom_points custom_point_checkout wps_wpr_append_points_apply_html"><input type="number" min="0" name="wps_cart_points" class="input-text" id="wps_cart_points" value="" placeholder="' + wps_wpr.wps_points_name + '"/><button class="button wps_cart_points_apply" name="wps_cart_points_apply" id="wps_cart_points_apply" data-order-limit="0">' + wps_wpr.wps_apply_points + '</button><p>' + wps_wpr_cart_block_obj.available_points_msg + ' : ' + wps_wpr_cart_block_obj.current__points + ' </p></div>');
			} else {

				var required_points = parseInt( minimum_redeem_points - wps_user_current_points );
				jQuery('.wp-block-woocommerce-cart-order-summary-coupon-form-block').append( wps_wpr.points_message_require + required_points + wps_wpr.points_more_to_redeem );
				jQuery('.wp-block-woocommerce-checkout-order-summary-coupon-form-block').append( wps_wpr.points_message_require + required_points + wps_wpr.points_more_to_redeem );
			}
		});

		// Remove coupon when cart block enable.
		setTimeout(() => {
			
			jQuery('.wc-block-components-chip__remove').attr('onclick','on_cart_click(this)');
		}, 2000);

		$(document).on('click',
			'.wc-block-components-chip__remove .wc-block-components-chip__remove-icon',
			function(e) {
				e.preventDefault();
				var coupon_name = jQuery(this).closest('.wc-block-components-chip__remove').prev().prev().html();
				if ( coupon_name.toLowerCase() == wps_wpr.points_coupon_name.toLowerCase() ) {

					var $this = $(this);
					var data = {
						action: 'wps_wpr_remove_cart_point',
						coupon_code: $(this).data('coupon'),
						wps_nonce: wps_wpr.wps_wpr_nonce,
						is_checkout: wps_wpr.is_checkout
					};
					$.ajax({
						url: wps_wpr.ajaxurl,
						type: "POST",
						data: data,
						dataType: 'json',
						success: function(response) {
							if (response.result == true) {
								$('#wps_cart_points').val('');
								if (wps_wpr.is_checkout) {
									setTimeout(function() {
										$this.closest('tr.cart-discount').remove();
										jQuery(document.body).trigger("update_checkout");
									}, 200);
								}
								location.reload();
							}
						},
						complete: function() {
							location.reload();
						}
					});
				}
			}
		);
		
		// update page when cart and checkout total earning points functionality enable.
		if ( '1' == wps_wpr_cart_block_obj.wps_wpr_cart_page_total_earning_points ) {

			jQuery(document).on('click', '.wc-block-components-quantity-selector__button.wc-block-components-quantity-selector__button--plus', function(){
				wps_wpr_refresh_cart_page();
			});

			jQuery(document).on('click', '.wc-block-components-quantity-selector__button.wc-block-components-quantity-selector__button--minus', function(){
				wps_wpr_refresh_cart_page();
			});
		}

		// Restore redemption state after page reload for block-based cart/checkout
		function wps_wpr_restore_block_redemption_state() {
			// Only run if redemption is enabled for cart or checkout
			if ( 1 == wps_wpr.is_cart_redeem_sett_enable || 1 == wps_wpr.is_checkout_redeem_enable ) {

				jQuery.ajax({
					url: wps_wpr.ajaxurl,
					type: 'POST',
					data: {
						action: 'wps_wpr_get_redemption_state',
						wps_nonce: wps_wpr.wps_wpr_nonce
					},
					dataType: 'json',
					success: function(response) {
						if (response.success && response.data && response.data.redeemed_points > 0) {
							// Points are applied - hide the "Add Points" button and apply form
							jQuery('#wps_wpr_button_to_add_points_section').hide();
							jQuery('.wps_wpr_append_points_apply_html').hide();

							// The discount with remove button should already be visible from server
						}
					}
				});
			}
		}

		// Call on page load with a slight delay to ensure elements are rendered
		setTimeout(function() {
			wps_wpr_restore_block_redemption_state();
		}, 500);

		// Display points-to-discount notice on block cart
		function wps_wpr_display_cart_notice() {
			if ( typeof wps_wpr_cart_block_obj !== 'undefined' &&
				 wps_wpr_cart_block_obj.checkout_notice_data &&
				 wps_wpr_cart_block_obj.checkout_notice_data.show_cart_notice ) {

				var noticeHtml = '<div class="wps-wpr-points-to-discount-notice wps-wpr-cart-notice" style="margin: 15px 0;">' +
					'<p class="wps-wpr-notice-text">' +
					'<span class="wps-wpr-notice-icon">🎁</span>' +
					'<strong>' + wps_wpr_cart_block_obj.checkout_notice_data.notice_html + '</strong>' +
					'<span class="wps-wpr-notice-cta">Keep shopping to earn more points!</span>' +
					'</p>' +
					'</div>';

				// Try to find the cart order summary coupon form block (best location)
				var targetElement = jQuery('.wp-block-woocommerce-cart-order-summary-coupon-form-block');

				if ( targetElement.length === 0 ) {
					// Fallback: try cart order summary subtotal block
					targetElement = jQuery('.wp-block-woocommerce-cart-order-summary-subtotal-block');
				}

				if ( targetElement.length === 0 ) {
					// Fallback: try cart order summary block
					targetElement = jQuery('.wp-block-woocommerce-cart-order-summary-block');
				}

				// Only add if not already present
				if ( targetElement.length > 0 && jQuery('.wps-wpr-cart-notice').length === 0 ) {
					targetElement.before(noticeHtml);
				}
			}
		}

		// Display points-to-discount notice on block checkout
		function wps_wpr_display_checkout_notice() {
			if ( typeof wps_wpr_cart_block_obj !== 'undefined' &&
				 wps_wpr_cart_block_obj.checkout_notice_data &&
				 wps_wpr_cart_block_obj.checkout_notice_data.show_checkout_notice ) {

				var noticeHtml = '<div class="wps-wpr-points-to-discount-notice wps-wpr-checkout-notice" style="margin: 15px 0;">' +
					'<p class="wps-wpr-notice-text">' +
					'<span class="wps-wpr-notice-icon">🎁</span>' +
					'<strong>' + wps_wpr_cart_block_obj.checkout_notice_data.notice_html + '</strong>' +
					'</p>' +
					'</div>';

				// Try to find the order summary coupon form block (best location)
				var targetElement = jQuery('.wp-block-woocommerce-checkout-order-summary-coupon-form-block');

				if ( targetElement.length === 0 ) {
					// Fallback: try order summary subtotal block
					targetElement = jQuery('.wp-block-woocommerce-checkout-order-summary-subtotal-block');
				}

				if ( targetElement.length === 0 ) {
					// Fallback: try order summary block
					targetElement = jQuery('.wp-block-woocommerce-checkout-order-summary-block');
				}

				// Only add if not already present
				if ( targetElement.length > 0 && jQuery('.wps-wpr-checkout-notice').length === 0 ) {
					targetElement.before(noticeHtml);
				}
			}
		}

		// Display notices with retry logic (elements may load dynamically)
		setTimeout(function() {
			wps_wpr_display_cart_notice();
			wps_wpr_display_checkout_notice();
		}, 1000);

		setTimeout(function() {
			wps_wpr_display_cart_notice();
			wps_wpr_display_checkout_notice();
		}, 2000);

		// Also try on mouseover (when user interacts with page)
		jQuery(document).on('mouseover', '.woocommerce-cart', function() {
			wps_wpr_display_cart_notice();
		});

		jQuery(document).on('mouseover', '.woocommerce-checkout', function() {
			wps_wpr_display_checkout_notice();
		});
	});
})(jQuery);

// Remove coupon when cart block enable.
function on_cart_click(data) {
	var coupon_name = jQuery(data).closest('.wc-block-components-chip__remove').prev().prev().html();
	if ( coupon_name.toLowerCase() == wps_wpr.points_coupon_name.toLowerCase() ) {

		var $this = jQuery(this);
		var data = {
			action: 'wps_wpr_remove_cart_point',
			wps_nonce: wps_wpr.wps_wpr_nonce,
			is_checkout: wps_wpr.is_checkout
		};
		jQuery.ajax({
			url: wps_wpr.ajaxurl,
			type: "POST",
			data: data,
			dataType: 'json',
			success: function(response) {
				if (response.result == true) {
					jQuery('#wps_cart_points').val('');
					if (wps_wpr.is_checkout) {
						setTimeout(function() {
							$this.closest('tr.cart-discount').remove();
							jQuery(document.body).trigger("update_checkout");
						}, 200);
					}
					location.reload();
				}
			},
			complete: function() {
				location.reload();
			}
		});
	}
}

function wps_wpr_refresh_cart_page() {
	var cart_checkout_qtyt = jQuery(this).closest('.wc-block-components-quantity-selector').find('.wc-block-components-quantity-selector__input').val();
	var data               = {
		'action'             : 'updating_total_earning_points',
		'nonce'              : wps_wpr_cart_block_obj.wps_wpr_nonce,
		'cart_checkout_qtyt' : cart_checkout_qtyt
	};
	jQuery.ajax({
		url     : wps_wpr_cart_block_obj.ajaxurl,
		method  : 'POST',
		data    : data,
		success : function( response ) {
			setTimeout(() => {
				
				window.location.reload();
			}, 1500);
		}
	});
}
