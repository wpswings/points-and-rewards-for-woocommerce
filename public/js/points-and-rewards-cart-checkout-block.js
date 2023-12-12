(function($) {
    'use strict';
    
    jQuery(document).ready(function($){

		// ============= Append Add a points section html ============

		// show html on cart page.
		if ( 1 == wps_wpr.is_cart_redeem_sett_enable ) {

			if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {

				setTimeout(() => {
					if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {
	
						jQuery('.wp-block-woocommerce-cart-order-summary-coupon-form-block').append('<div id="wps_wpr_button_to_add_points_section"><a href="#">Add a points</a></div>');
					}
				}, 1000);

				jQuery(document).on('mouseover', '.woocommerce-cart.woocommerce-page', function(){
					if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {
	
						jQuery('.wp-block-woocommerce-cart-order-summary-coupon-form-block').append('<div id="wps_wpr_button_to_add_points_section"><a href="#">Add a points</a></div>');
					}
				});
			}
		}

		// show html on checkout page.
		if ( 1 == wps_wpr.is_checkout_redeem_enable ) {
			if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {

				setTimeout(() => {
					if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {

						jQuery('.wp-block-woocommerce-checkout-order-summary-coupon-form-block').append('<div id="wps_wpr_button_to_add_points_section"><a href="#">Add a points</a></div>');
					}
				}, 1000);

				jQuery(document).on('mouseover', '.woocommerce-checkout.woocommerce-page', function(){
					if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {

						jQuery('.wp-block-woocommerce-checkout-order-summary-coupon-form-block').append('<div id="wps_wpr_button_to_add_points_section"><a href="#">Add a points</a></div>');
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

				jQuery('.wp-block-woocommerce-cart-order-summary-coupon-form-block').append('<div class="wps_wpr_append_points_apply_html"><input type="number" min="0" name="wps_cart_points" class="input-text" id="wps_cart_points" value="" placeholder="Points"/><button class="button wps_cart_points_apply" name="wps_cart_points_apply" id="wps_cart_points_apply" value="Apply Points" data-order-limit="0">Apply Points</button></div>');
				jQuery('.wp-block-woocommerce-checkout-order-summary-coupon-form-block').append('<div class="wps_wpr_append_points_apply_html"><input type="number" min="0" name="wps_cart_points" class="input-text" id="wps_cart_points" value="" placeholder="Points"/><button class="button wps_cart_points_apply" name="wps_cart_points_apply" id="wps_cart_points_apply" value="Apply Points" data-order-limit="0">Apply Points</button></div>');
			} else {

				var required_points = parseInt( minimum_redeem_points - wps_user_current_points );
				jQuery('.wp-block-woocommerce-cart-order-summary-coupon-form-block').append( 'You require : ' + required_points + ' more to get redeem' );
				jQuery('.wp-block-woocommerce-checkout-order-summary-coupon-form-block').append( 'You require : ' + required_points + ' more to get redeem' );;
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
