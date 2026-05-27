!(function (e) {
	"use strict";
	e(document).ready(function () {
		window.wps_has_date_validation = false;

	  !0 ==
	  jQuery(document).find("#wps_wpr_membership_setting_enable").prop("checked")
		? jQuery(document).find(".parent_of_div").closest("tr").show()
		: (jQuery(document).find(".parent_of_div").closest("tr").hide(), r()),
		jQuery(document)
		  .find(".wps_wpr_membership_select_all_category_common")
		  .click(function (r) {
			r.preventDefault();
			var i = e(this).data("id");
			jQuery(document)
			  .find("#wps_wpr_membership_category_list_" + i + " option")
			  .prop("selected", "selected"),
			  jQuery(document)
				.find("#wps_wpr_membership_category_list_" + i)
				.trigger("change");
		  }),
		jQuery(document)
		  .find(".wps_wpr_membership_select_none_category_common")
		  .click(function (r) {
			r.preventDefault();
			var i = e(this).data("id");
			jQuery(document)
			  .find("#wps_wpr_membership_category_list_" + i + " option")
			  .removeAttr("selected"),
			  jQuery(document)
				.find("#wps_wpr_membership_category_list_" + i)
				.trigger("change");
		  }),
		e(document).find(".notice-image img").css("max-width", "50px"),
		e(document).find(".notice-content").css("margin-left", "15px"),
		e(document)
		  .find(".notice-container")
		  .css({
			"padding-top": "5px",
			"padding-bottom": "5px",
			display: "flex",
			"justify-content": "left",
			"align-items": "center",
		  }),
		e(document).on("click", ".wps_wpr_common_slider", function () {
		  e(this).next(".wps_wpr_points_view").slideToggle("fast"),
			e(this).toggleClass("active");
		}),
		// Wrap content in any section title that doesn't already have a PHP-generated wrapper
		e(".wps_wpr_general_sign_title").each(function () {
		  var $title = e(this);
		  if ($title.next(".wps_wpr_section_content").length === 0 && $title.nextAll().length > 0) {
			$title.nextAll().wrapAll('<div class="wps_wpr_section_content"></div>');
		  }
		});
		// Open the first section on each page by default
		var $firstTitle = e(".wps_wpr_general_sign_title").first();
		if ($firstTitle.length) {
		  $firstTitle.next(".wps_wpr_section_content").show();
		  $firstTitle.addClass("wps_wpr_section_active");
		}
		e(document).on("click", ".wps_wpr_general_sign_title", function (ev) {
		  if (e(ev.target).closest("a").length) { return; }
		  e(this).next(".wps_wpr_section_content").slideToggle(300);
		  e(this).toggleClass("wps_wpr_section_active");
		}),
		e(document).find("#wps_wpr_restrictions_for_purchasing_cat").select2(),
		e(document).find("#wps_wpr_restrict_redeem_points_category_wise").select2(),
		e(document).find("#wps_wpr_restrict_redeem_points_membership_wise").select2(),
		e(document).find("#wps_wpr_restrict_per_currency_dummy_points_category_wise").select2(),
		e(document).find("#wps_wpr_referral_purchase_dummy_points_category_wise").select2(),
		e(".wps_points_update").click(function () {
		  var r = e(this).data("id"),
			i = e(document)
			  .find("#add_sub_points" + r)
			  .val(),
			p = e(document)
			  .find("#wps_sign" + r)
			  .val(),
			s = e(document)
			  .find("#wps_remark" + r)
			  .val();
		  if ((i = Number(i)) > 0 && i === parseInt(i, 10)) {
			if ("" != s) {
			  jQuery("#wps_wpr_loader").show();
			  var t = {
				action: "wps_wpr_points_update",
				points: i,
				user_id: r,
				sign: p,
				reason: s,
				wps_nonce: wps_wpr_object.wps_wpr_nonce,
			  };
			  e.ajax({
				url: wps_wpr_object.ajaxurl,
				type: "POST",
				data: t,
				success: function (r) {
				  jQuery("#wps_wpr_loader").hide(),
					e("html, body").animate(
					  { scrollTop: e(".wps_rwpr_header").offset().top },
					  800
					),
					e(
					  '<div class="notice notice-success is-dismissible"><p><strong>' +
						wps_wpr_object.success_update +
						"</strong></p></div>"
					).insertAfter(e(".wps_rwpr_header")),
					setTimeout(function () {
					  location.reload();
					}, 1e3);
				},
			  });
			} else alert(wps_wpr_object.reason);
		  } else alert(wps_wpr_object.validpoint);
		}),
		e(document).on("click", ".wps_wpr_email_wrapper_text", function () {
		  e(this).siblings(".wps_wpr_email_wrapper_content").slideToggle();
		}),
		e(document).on(
		  "change",
		  "#wps_wpr_membership_setting_enable",
		  function () {
			!0 == e(this).prop("checked")
			  ? (jQuery(document).find(".parent_of_div").closest("tr").show(),
				i())
			  : (jQuery(document).find(".parent_of_div").closest("tr").hide(),
				r());
		  }
		),
		e(document).on("change", ".wps_wpr_common_class_categ", function () {
		  var r = e(this).data("id"),
			i = e("#wps_wpr_membership_category_list_" + r).val();
		  jQuery("#wps_wpr_loader").show();
		  var p = {
			action: "wps_wpr_select_category",
			wps_wpr_categ_list: i,
			wps_nonce: wps_wpr_object.wps_wpr_nonce,
		  };
		  e.ajax({
			url: wps_wpr_object.ajaxurl,
			type: "POST",
			data: p,
			dataType: "json",
			success: function (e) {
			  if ("success" == e.result) {

				var i             = e.data,
				p                 = "";
				var uniqueOptions = new Set();

				// Store the previous selected value
				var previousSelectedValue = jQuery("#wps_wpr_membership_product_list_" + r).val();

				for (var s in i) {
					if (!uniqueOptions.has(s)) {
						uniqueOptions.add(s);
						p += '<option value="' + s + '">' + i[s] + "</option>";
					}
				}

				// Get the previous HTML content of the element
				var previousValue = jQuery("#wps_wpr_membership_product_list_" + r).html();
				p = previousValue + p;

				// Update the HTML with unique options
				jQuery("#wps_wpr_membership_product_list_" + r).html(p);

				// Remove any duplicate <option> elements
				jQuery("#wps_wpr_membership_product_list_" + r + " option").each(function() {
					if (uniqueOptions.has(this.value)) {
						uniqueOptions.delete(this.value); // Remove it from the set if it's already seen
					} else {
						jQuery(this).remove(); // Remove duplicate <option>
					}
				});

				// Re-select the previous selected value
				jQuery("#wps_wpr_membership_product_list_" + r).val(previousSelectedValue);

				// Initialize or reinitialize the Select2 plugin
				jQuery("#wps_wpr_membership_product_list_" + r).select2();
				jQuery("#wps_wpr_loader").hide();
			  }
			},
		  });
		});
	  for (var p = e(".wps_wpr_repeat:last").data("id"), s = 0; s <= p; s++)
		e(document)
		  .find("#wps_wpr_membership_category_list_" + s)
		  .select2(),
		  e(document)
			.find("#wps_wpr_membership_product_list_" + s)
			.select2();
		  
		/*Check add more column in the order total settings*/
		jQuery(document).on('click','#wps_wpr_add_more',function($) {
			if(jQuery('#wps_wpr_thankyouorder_enable').prop("checked") == true) {

				var response = check_validation_setting();
				if( response == true) {
					jQuery('.wps_error').hide();
					var tbody_length = jQuery('.wps_wpr_thankyouorder_tbody > tr').length;
					var new_row = '<tr valign="top"><td class="forminp forminp-text"><label for="wps_wpr_thankyouorder_minimum"><input type="text" name="wps_wpr_thankyouorder_minimum[]" class="wps_wpr_thankyouorder_minimum input-text wc_input_price" required=""></label></td><td class="forminp forminp-text"><label for="wps_wpr_thankyouorder_maximum"><input type="text" name="wps_wpr_thankyouorder_maximum[]" class="wps_wpr_thankyouorder_maximum"></label></td><td class="forminp forminp-text"><label for="wps_wpr_thankyouorder_current_type"><input type="text" name="wps_wpr_thankyouorder_current_type[]" class="wps_wpr_thankyouorder_current_type input-text wc_input_price" required=""></label></td><td class="wps_wpr_remove_thankyouorder_content forminp forminp-text"><input type="button" value="Remove" class="wps_wpr_remove_thankyouorder button" ></td></tr>';

					if( tbody_length == 2 ) {
						jQuery( '.wps_wpr_remove_thankyouorder_content' ).each( function() {
							jQuery(this).show();
						});
					}
					jQuery('.wps_wpr_thankyouorder_tbody').append(new_row);
				} else {
					jQuery('html, body').animate({
						scrollTop: $(".wps_rwpr_header").offset().top
					}, 800);
				
					var remove_message = '<div class="notice notice-error is-dismissible wps_error"><p><strong>'+wps_wpr_object.notice_error+'</strong></p></div>';
					
					jQuery(remove_message).insertAfter('.wps_rwpr_header');
				}			
			}
		});

		/*Check validation of the order total settings*/
		var check_validation_setting = function(){
		
			if(jQuery('#wps_wpr_thankyouorder_enable').prop("checked") == true) {
				var tbody_length  = jQuery('.wps_wpr_thankyouorder_tbody > tr').length;
				var i             = 1;
				var min_arr       = []; var max_arr = [];
				var empty_warning = false;
				var is_lesser     = false;
				var num_valid     = false;
				jQuery('.wps_wpr_thankyouorder_minimum').each(function(){

					min_arr.push(jQuery(this).val());
				});
				var i = 1;

				jQuery('.wps_wpr_thankyouorder_maximum').each(function(){

					max_arr.push(jQuery(this).val());
					i++;
				});

				var i                 = 1;
				var thankyouorder_arr = [];
				jQuery('.wps_wpr_thankyouorder_current_type').each(function(){
					thankyouorder_arr.push(jQuery(this).val());
					if(!jQuery(this).val()){				
						jQuery('.wps_wpr_thankyouorder_tbody > tr:nth-child('+(i+1)+') .wps_wpr_thankyouorder_current_type').css("border-color", "red");
						empty_warning = true;
					}
					else {
						jQuery('.wps_wpr_thankyouorder_tbody > tr:nth-child('+(i+1)+') .wps_wpr_thankyouorder_current_type').css("border-color", "");				
					}
					i++;			
				});

				if(empty_warning) {
					jQuery('.notice.notice-error.is-dismissible').each(function(){
						jQuery(this).remove();
					});
					jQuery('.notice.notice-success.is-dismissible').each(function(){
						jQuery(this).remove();
					});

					jQuery('html, body').animate({
						scrollTop: jQuery(".wps_rwpr_header").offset().top
					}, 800);
					var empty_message = '<div class="notice notice-error is-dismissible"><p><strong>Some Fields are empty!</strong></p></div>';
					jQuery(empty_message).insertBefore(jQuery('.wps_wpr_general_wrapper'));
					return;
				}

				var minmaxcheck = false;
				if(max_arr.length >0 && min_arr.length > 0) {
	
					if( min_arr.length == max_arr.length && max_arr.length == thankyouorder_arr.length) {

						for ( var j = 0; j < min_arr.length; j++) {

							if(parseInt(min_arr[j]) > parseInt(max_arr[j])) {
								minmaxcheck = true;
								jQuery('.wps_wpr_thankyouorder_tbody > tr:nth-child('+(j+2)+') .wps_wpr_thankyouorder_minimum').css("border-color", "red");
								jQuery('.wps_wpr_thankyouorder_tbody > tr:nth-child('+(j+2)+') .wps_wpr_thankyouorder_minimum').css("border-color", "red");
							}
							else{
								jQuery('.wps_wpr_thankyouorder_tbody > tr:nth-child('+(j+2)+') .wps_wpr_thankyouorder_minimum').css("border-color", "");
								jQuery('.wps_wpr_thankyouorder_tbody > tr:nth-child('+(j+2)+') .wps_wpr_thankyouorder_minimum').css("border-color", "");
							}
						}
					}
					else {
						jQuery('.notice.notice-error.is-dismissible').each(function(){
							jQuery(this).remove();
						});
						jQuery('.notice.notice-success.is-dismissible').each(function(){
							jQuery(this).remove();
						});

						jQuery('html, body').animate({
							scrollTop: jQuery(".wps_rwpr_header").offset().top
						}, 800);
						var empty_message = '<div class="notice notice-error is-dismissible"><p><strong>Some Fields are empty!</strong></p></div>';
						jQuery(empty_message).insertBefore(jQuery('.wps_wpr_general_wrapper'));
						return;
					}
				}

				if(minmaxcheck) {
					jQuery('.notice.notice-error.is-dismissible').each(function(){
						jQuery(this).remove();
					});
					jQuery('.notice.notice-success.is-dismissible').each(function(){
						jQuery(this).remove();
					});

					jQuery('html, body').animate({
						scrollTop: jQuery(".wps_rwpr_header").offset().top
					}, 800);
					var empty_message = '<div class="notice notice-error is-dismissible"><p><strong>Minimum value cannot have value grater than Maximim value.</strong></p></div>';
					jQuery(empty_message).insertAfter(jQuery('.wps_wpr_general_wrapper'));
					return;
				}
				return true;
			} else {
				return true;
			}
		};

		jQuery( document ).on(
			"change",'input',
			'.wps_wpr_thankyouorder_minimum input-text',
			function(){
				var count = jQuery( this ).attr('class');
				var value1 = jQuery(this).val();
			
				if(value1<0 && count =='wps_wpr_thankyouorder_minimum input-text wc_input_price'){
					alert('Negative values not allowed');
					jQuery(this).val("1");
				}
			}
		);

		jQuery( document ).on(
			"change",'input',
			'.wps_wpr_thankyouorder_maximum',
			function(){
				var count = jQuery( this ).attr('class');
				var value1 = jQuery(this).val();
				if(value1<0 && count =='wps_wpr_thankyouorder_maximum'){
					alert('Negative values not allowed');
					jQuery(this).val("1");
				}
			}
		);

		jQuery( document ).on(
			"change",'input',
			'.wps_wpr_thankyouorder_current_type input-text wc_input_price',
			function(){
				var count = jQuery( this ).attr('class');
				var value1 = jQuery(this).val();
			
				if(value1<0 && count =='wps_wpr_thankyouorder_current_type input-text wc_input_price'){
					alert('Negative values not allowed');
					jQuery(this).val("1");	
				}
			}
		);

		jQuery(document).on('click','.wps_wpr_remove_thankyouorder',function() {

			if(jQuery('#wps_wpr_thankyouorder_enable').prop("checked") == true) {
				
				jQuery(this).closest('tr').remove();
				var tbody_length = jQuery('.wps_wpr_thankyouorder_tbody > tr').length;
				if( tbody_length == 1 ){
					jQuery( '.wps_wpr_remove_thankyouorder_content' ).each( function() {
						jQuery(this).hide();
					});
				}
			}
		});

		jQuery(document).on("click", ".wps_wpr_remove_button", function () {
		  var r = e(this).attr("id");
		  0 == r &&
			(e(document).find(".wps_wpr_repeat_button").hide(),
			e("#wps_wpr_membership_setting_enable").attr("checked", !1)),
			e("#wps_wpr_parent_repeatable_" + r).remove();
		}),

		// JS for assign previous order points.
		jQuery(document).on("click", "#wps_wpr_points_on_previous_order", function (e) {

			const $btn = jQuery(this);
			try {

				// Try to run date validation (defined in optional file).
				if ( typeof validateDateBeforeSubmit === "function" ) {

					const proceed = validateDateBeforeSubmit($btn);
					if (! proceed ) return false; // Stop if validation failed.
				}
			} catch (err) {

				// No validation function defined — just continue.
			}
		
			// Trigger actual points assignment
			$btn.trigger("submit_points_assignment");
		});

		// ajax call to assign points on previous order.
		jQuery(document).on("submit_points_assignment", "#wps_wpr_points_on_previous_order", function () {

			const $btn       = jQuery(this);
			const points     = jQuery("#wps_wpr_previous_order_point_value").val().trim();
			const $notice    = jQuery(".wps_wpr_previous_order_notice");
			const $loader    = jQuery(".wps_wpr_previous_order_loader");
			const start_date = jQuery('#wps_wpr_previous_order_start_date').val();
			const end_date   = jQuery('#wps_wpr_previous_order_end_date').val();

			// Reset notice and disable button
			$btn.prop("disabled", true);
			$notice.hide().html("");

			if (parseInt(points) > 0) {
			  const data = {
				action: "assign_points_on_previous_order",
				nonce: wps_wpr_object.wps_wpr_nonce,
				rewards_points: points,
				start_date : start_date,
				end_date : end_date
			  };

			  $loader.show();
			  jQuery.ajax({
				method: "POST",
				url: wps_wpr_object.ajaxurl,
				data: data,
				success: function (response) {

				  $loader.hide();
				  $btn.prop("disabled", false);
				  const color = response.result ? "green" : "red";
				  $notice
					.css("color", color)
					.html(response.msg)
					.show();
				},
				error: function () {
				  $loader.hide();
				  $btn.prop("disabled", false);
				  $notice
					.css("color", "red")
					.html(wps_wpr_object.wps_ajax_error)
					.show();
				},
			  });
			} else {
			  $btn.prop("disabled", false);
			  $notice
				.css("color", "red")
				.html(wps_wpr_object.validpoint)
				.show();
			}
		});

	});
	var r = function () {
		jQuery(document)
		  .find(".wps_wpr_repeat")
		  .each(function (e, r) {
			jQuery(document)
			  .find("#wps_wpr_membership_level_name_" + e)
			  .attr("required", !1),
			jQuery(document)
				.find("#wps_wpr_membership_level_value_" + e)
				.attr("required", !1),
			jQuery(document)
				.find("#wps_wpr_membership_expiration_days_" + e)
				.attr("required", !1),
			jQuery(document)
				.find("#wps_wpr_membership_expiration_" + e)
				.attr("required", !1);
		  });
	  },
	  i = function () {
		jQuery(document)
		  .find(".wps_wpr_repeat")
		  .each(function (e, r) {
			jQuery(document)
				.find("#wps_wpr_membership_level_name_" + e)
				.attr("required", !0),
			jQuery(document)
				.find("#wps_wpr_membership_level_value_" + e)
				.attr("required", !0),
			jQuery(document)
				.find("#wps_wpr_membership_expiration_days_" + e)
				.attr("required", !0),
			jQuery(document)
				.find("#wps_wpr_membership_expiration_" + e)
				.attr("required", !0);
		  });
	  };
  })(jQuery),
	setTimeout(function () {
	  jQuery(window).width() >= 900 &&
		jQuery(".wps_rwpr_navigator_template").stickySidebar({
		  topSpacing: 60,
		  bottomSpacing: 60,
		});
	}, 500),
	jQuery(document).ready(function () {
	  jQuery(".dashicons.dashicons-menu").click(function () {
		jQuery(".wps_rwpr_navigator_template").toggleClass("open-btn");
	  });
	}),
	jQuery(document).on(
	  "change",
	  "input",
	  "#wps_wpr_coupon_conversion_price",
	  function () {
		var e = jQuery(this).attr("id");
		0 > jQuery(this).val() &&
		  "wps_wpr_coupon_conversion_price" == e &&
		  (alert(wps_wpr_object.negative), jQuery(this).val("1"));
	  }
	),
	jQuery(document).ready(function () {
	  jQuery(".notice-dismiss").click(function () {
		jQuery(".notice-success").remove();
	  });

	  // campaign page dropdown.
	  jQuery(document).find('#wps_wpr_select_page_for_campaign').select2();
  
	  /** =========== Gamification Features Start Here =========== */

	  jQuery(document).find('#wps_wpr_select_win_wheel_page').select2();
	  jQuery(document).find('#wps_wpr_select_spin_stop').select2();
  
	  jQuery(document).on('click', '#wps_wpr_gamification_fields_add', function(){
		
		// check segment count
		if ( jQuery('.wps_wpr_add_game_segment_dynamically').length < 12 ) {
  
		  // check setting is enable.
		  if ( true == jQuery('.wps_wpr_enable_gamification_settings').prop('checked') ) {
  
			// validate segments values
			if ( wps_wpr_segments_validation() ) {
  
			  var new_row = '<tr class="wps_wpr_add_game_segment_dynamically"><td><input type="text" name="wps_wpr_enter_segment_name[]" id="wps_wpr_enter_segment_name" value="" required></td><td><select name="wps_wpr_game_rewards_type[]" class="wps_wpr_game_rewards_type"><option value="points">Points</option><option value="wallet">Wallet</option></select></td><td><input type="number" min="1" name="wps_wpr_enter_segment_points[]" id="wps_wpr_enter_segment_points" value="" required></td><td><input type="number" max="20" min="1" name="wps_wpr_enter_sgemnet_font_size[]" id="wps_wpr_enter_sgemnet_font_size" value="" required></td><td><input type="color" name="wps_wpr_enter_segment_color[]" id="wps_wpr_enter_segment_color[]" class="wps_wpr_enter_segment_color" value=""></td><td><input type="button" name="wps_wpr_remove_game_segment" id="wps_wpr_remove_game_segment" value="+"></td></tr>';
			  jQuery('.wps_wpr_segment_gamification_settings_wrappers').append( new_row );
			} else {
			  
			  jQuery('.notice.notice-error.is-dismissible').each(function(){
				jQuery(this).remove();
			  });
			  jQuery('.notice.notice-success.is-dismissible').each(function(){
				jQuery(this).remove();
			  });
  
			  jQuery('html, body').animate({
				scrollTop: jQuery(".wps_rwpr_header").offset().top
			  }, 800);
			  var empty_message = '<div class="notice notice-error is-dismissible"><p><strong>Some Fields are empty!</strong></p></div>';
			  jQuery(empty_message).insertBefore(jQuery('.wps_wpr_user_gamifications_main_wrappers'));
			}
		  }
		} else {
  
		  // show alert msg when segment reached.
		  alert( wps_wpr_object.segment_reached_msg );
		}
	 });
  
	 // Remove segments.
	 jQuery(document).on('click', '#wps_wpr_remove_game_segment', function(){
		// check setting is enable.
		if ( true == jQuery('.wps_wpr_enable_gamification_settings').prop('checked') ) {
		  
		  // check segment count.
		  if ( jQuery('.wps_wpr_add_game_segment_dynamically').length > 6 ) {
  
			jQuery(this).parents('.wps_wpr_add_game_segment_dynamically').remove();
		  } else {
  
			alert( wps_wpr_object.segment_limit_msg );
		  }
		}
	  })
  
	  // Validating segments.
	  function wps_wpr_segments_validation() {
  
		var result       = true;
		var segment_name = [];
		var i            = 0
		jQuery(document).find('.wps_wpr_enter_segment_name').each(function(){
		  segment_name.push( jQuery(this).val() );
		  if ( ! jQuery(this).val() ) {
  
			++i;
		  }
		});
		  
		var segment_points = [];
		var x              = 0;
		jQuery(document).find('.wps_wpr_enter_segment_points').each(function(){
		  segment_points.push( jQuery(this).val() );
		  if ( ! jQuery(this).val() ) {
  
			++x;
		  }
		});
  
		var segment_size = [];
		var y            = 0;
		jQuery(document).find('.wps_wpr_enter_sgemnet_font_size').each(function(){
		  segment_size.push( jQuery(this).val() );
		  if ( ! jQuery(this).val() ) {
  
			++y;
		  }
		});
  
		var segmentcolor = [];
		var z             = 0;
		jQuery(document).find('.wps_wpr_enter_segment_color').each(function(){
		  segmentcolor.push( jQuery(this).val() );
		  if ( ! jQuery(this).val() ) {
  
			++z;
		  }
		});
  
		if ( i > 0 || x > 0 || y > 0 || z > 0 ) {
  
		  result = false;
		}
		
		return result;
	 }

	// check wallet plugin is active or not.
	if ( ! wps_wpr_object.is_wallet_active ) {
		jQuery(document).on('change', '.wps_wpr_game_rewards_type', function() {
			var $select = jQuery(this);

			// If current value is "wallet"
			if ( $select.val() === 'wallet' ) {
				// Show confirm with option to go to plugin page
				if ( confirm(wps_wpr_object.wallet_alert_message) ) {
					window.open("https://wordpress.org/plugins/wallet-system-for-woocommerce/", "_blank");
				}

				// Remove wallet option
				$select.find('option[value="wallet"]').remove();

				// Reset selection to "points" (or first option available)
				$select.val('points').trigger('change');
			}
		});
	}

	 /** ============ User Badges Feature Start here. ============== */
	 
	 // Open Custom media window to select images.
	jQuery(document).on('click', '.wps_wpr_add_user_badges_img', open_custom_media_window);
	function open_custom_media_window() {

		if (this.window === undefined) {
			this.window = wp.media({
				title: 'Insert Image',
				library: { type: 'image' },
				multiple: false,
				button: { text: 'Insert Image' }
			});

			var self = this;
			this.window.on('select', function () {
				var response = self.window.state().get('selection').first().toJSON();
				jQuery(self).nextAll('.wps_wpr_image_attachment_id').val(response.sizes.thumbnail.url);
				jQuery(self).prevAll('.wps_wpr_icon_user_badges').attr('src', response.sizes.thumbnail.url);
				jQuery(self).prevAll('.wps_wpr_icon_user_badges').show();
			});
		}

		this.window.open();
		return false;
	}

	// Add badges fields dynamic.
	jQuery(document).on('click', '#wps_wpr_user_badges_fields_add', function() {
		if (true === jQuery('.wps_wpr_enable_user_badges_settings').prop('checked')) {

			if (wps_wpr_badges_validation()) {

				var new_row = '<tr class="wps_wpr_add_user_badges_dynamic"><td><input type="text" name="wps_wpr_enter_badges_name[]" id="wps_wpr_enter_badges_name" class="wps_wpr_enter_badges_name" value="" required></td><td><input type="number" min="1" name="wps_wpr_badges_threshold_points[]" id="wps_wpr_badges_threshold_points" class="wps_wpr_badges_threshold_points" value="" required></td><td><input type="number" min="1" name="wps_wpr_badges_rewards_points[]" id="wps_wpr_badges_rewards_points" class="wps_wpr_badges_rewards_points" value="" required></td><td><div class="wps_wpr_icon_user_badges_wrap"><img src="' + wps_wpr_object.wps_badge_image + '" class="wps_wpr_icon_user_badges"><input type="button" class="wps_wpr_add_user_badges_img" value="Replace"><input type="hidden" name="wps_wpr_image_attachment_id[]" class="wps_wpr_image_attachment_id" value="' + wps_wpr_object.wps_badge_image + '"/></div></td><td style="width: 60px;"><input type="button" name="wps_wpr_remove_user_badges" id="wps_wpr_remove_user_badges" class="wps_wpr_remove_user_badges" value="+"></td></tr>';
				jQuery('.wps_wpr_user_badges_table_settings_wrappers').append(new_row);
			} else {

				jQuery('.notice.notice-error.is-dismissible').each(function() {
					jQuery(this).remove();
				});
				jQuery('.notice.notice-success.is-dismissible').each(function() {
					jQuery(this).remove();
				});

				jQuery('html, body').animate({
					scrollTop: jQuery(".wps_rwpr_header").offset().top
				}, 800);
				var empty_message = '<div class="notice notice-error is-dismissible"><p><strong>Some Fields are empty!</strong></p></div>';
				jQuery(empty_message).insertBefore(jQuery('.wps_wpr_user_badges_main_wrappers'));
			}
		}
	});

	// Validating badges.
	function wps_wpr_badges_validation() {

		var result      = true;
		var badges_name = [];
		var i           = 0
		jQuery(document).find('.wps_wpr_enter_badges_name').each(function(){
		badges_name.push( jQuery(this).val() );
		if ( ! jQuery(this).val() ) {

			++i;
		}
		});
		
		var threshold_points = [];
		var x                = 0;
		jQuery(document).find('.wps_wpr_badges_threshold_points').each(function(){
		threshold_points.push( jQuery(this).val() );
		if ( ! jQuery(this).val() ) {

			++x;
		}
		});

		var badges_rewards_points = [];
		var y                     = 0;
		jQuery(document).find('.wps_wpr_badges_rewards_points').each(function(){
		badges_rewards_points.push( jQuery(this).val() );
		if ( ! jQuery(this).val() ) {

			++y;
		}
		});

		if ( i > 0 || x > 0 || y > 0 ) {

		result = false;
		}
		return result;
	}

	// Remove user badges.
	jQuery(document).on('click', '#wps_wpr_remove_user_badges', function(){
		// check setting is enable.
		if ( true == jQuery('.wps_wpr_enable_user_badges_settings').prop('checked') ) {
			if ( jQuery('.wps_wpr_add_user_badges_dynamic').length > 2 ) {

				jQuery(this).parents('.wps_wpr_add_user_badges_dynamic').remove();
			} else {

				alert( 'This is the default setting, you cannot remove it!!' );
			}
		}
	});

	// threshold amount in incremented order.
	jQuery(document).on('keyup', '.wps_wpr_badges_threshold_points', function(){
		
		var current_threshold  = parseInt( jQuery(this).val() );
		var previous_threshold = parseInt( jQuery(this).closest('.wps_wpr_add_user_badges_dynamic').prev('tr').find('.wps_wpr_badges_threshold_points').val() );
		
		if ( current_threshold < previous_threshold ) {

			jQuery(this).focus();
			jQuery(this).css( 'border', '2px solid red' );
			jQuery('.wps_wpr_show_incremented_warning_msg').show();
			jQuery('.wps_wpr_show_incremented_warning_msg').html( wps_wpr_object.threshold_warning_msg );
			jQuery('.wps_wpr_add_more_btn_badge').prop( 'disabled', true );
			jQuery('#wps_wpr_save_user_badges_settings').prop( 'disabled', true );
		} else {

			jQuery(this).blur();
			jQuery(this).removeAttr('style');
			jQuery('.wps_wpr_show_incremented_warning_msg').hide();
			jQuery('.wps_wpr_show_incremented_warning_msg').html( '' );
			jQuery('.wps_wpr_add_more_btn_badge').prop( 'disabled', false );
			jQuery('#wps_wpr_save_user_badges_settings').prop( 'disabled', false );
		}
	});

	// restrict rewards fields to enter more than 100.
	jQuery(document).on('change', '.wps_wpr_assign_mem_rewards_points', function(){

		var count = jQuery(this).prop('id');
		count     = count.replace( 'wps_wpr_choose_mem_points_type_', '' );
		var check = jQuery('#wps_wpr_choose_mem_points_type_' + count).val();

		if ( 'percent' === check ) {

			jQuery('#wps_wpr_assign_mem_points_val_' + count).attr('max', 100);
		} else {

			jQuery('#wps_wpr_assign_mem_points_val_' + count).removeAttr('max');
		}
	});

	// restrict to enter alphabet in per currecny fields.
	jQuery(document).on('mouseleave', '#wps_wpr_coupon_conversion_points', function(){
		this.value = this.value.replace(/[^0-9]/g, '');
	});

	// restrict to enter alphabet in redemption settings.
	jQuery(document).on('mouseleave', '#wps_wpr_cart_price_rate', function(){
		this.value = this.value.replace(/[^0-9]/g, '');
	});

	jQuery(document).on('mouseleave', '#wps_wpr_coupon_redeem_price', function(){
		this.value = this.value.replace(/[^0-9]/g, '');
	});

	// restrict to enter alphabet in purchase through settings.
	jQuery(document).on('mouseleave', '#wps_wpr_product_purchase_price', function(){
		this.value = this.value.replace(/[^0-9]/g, '');
	})


	// restrict user from points table.
	jQuery(document).on('change', '.wps_wpr_restrict_user', function(){

		var user_id = jQuery(this).attr('data-id');
		if ( jQuery(this).is(':checked') ) {
			
			var checked = jQuery(this).val();
			wps_wpr_restrict_user_call( user_id, checked );
		} else {

			wps_wpr_restrict_user_call( user_id, 'no' );
		}
	});

	/**
	 * 
	 * @param {int} user_id user_id.
	 * @param {string} checked checked.
	 */
	function wps_wpr_restrict_user_call( user_id, checked ) {
		var data    = {
			'action'  : 'restrict_user_from_points_table',
			wps_nonce : wps_wpr_object.wps_wpr_nonce,
			'user_id' : user_id,
			'checked' : checked,
		};
		jQuery.ajax({
			'url'    : wps_wpr_object.ajaxurl,
			'method' : 'POST',
			'data'   : data,
			success  : function(response) {
				console.log(response);
			}
		});
	}

	// +++++++++++   Import user points functionality start here   ++++++++++++

	// Importing table points.
	jQuery(document).on('click','.wps_import',function(e){
		e.preventDefault();
		var userpoints_csv_import = jQuery('#userpoints_csv_import').val();
		if ( '' === userpoints_csv_import ) {

			alert( wps_wpr_object.invalid_files );
			return false;
		} else {
			
			jQuery('.wps_wpr_export_points_table_main_wrap').show();
		}
	});

	// validate radio button and perform import event.
	jQuery(document).on('click', '#wps_wpr_confirm_import_option', function(){

		var wps_wpr_export_table_option = jQuery('.wps_wpr_export_table_option:checked').val();
		if ( '' == wps_wpr_export_table_option || undefined == wps_wpr_export_table_option ) {

			jQuery('.wps_wpr_export_table_option').focus();
			jQuery('.wps_wpr_radion_button_notice').show();
			jQuery('.wps_wpr_radion_button_notice').html(wps_wpr_object.radio_validate_msg);
		} else {

			jQuery('.wps_wpr_radion_button_notice').hide();
			jQuery('.wps_wpr_radion_button_notice').html('');
			jQuery('.wps_wpr_export_points_table_main_wrap').hide();
			jQuery("#wps_wpr_loader").show();

			var form_data = new FormData(jQuery('form#mainform')[0]);
			form_data.append( 'action', 'wps_large_scv_import' );
			form_data.append( 'wps_wpr_export_table_option', wps_wpr_export_table_option );
			form_data.append( 'wps_nonce', wps_wpr_object.wps_wpr_nonce );
			form_data.append('start', 0);
			wps_wpr_process_csv_chunk(form_data);
		}
	});

	// hide import pop-up.
	jQuery(document).on('click', '.wps_wpr_export_shadow, .wps_wpr_export_close', function(){
		jQuery('.wps_wpr_export_points_table_main_wrap').hide();
	});

	// perform recursive ajax.
	function wps_wpr_process_csv_chunk(form_data) {

		jQuery.ajax({
			type        : "POST",
			dataType    : "json",
			url         : wps_wpr_object.ajaxurl,
			data        : form_data,
			processData : false,
			contentType : false,
			success: function(response) {

				console.log('Progress: ' + response.progress + '%');
				if ( response.result == false ) {

					alert( response.msg );
					jQuery("#wps_wpr_loader").hide();
					return false;
				} else {

					if ( ! response.finished ) {

						// Prepare data for next chunk.
						form_data.set('start', response.start);
						wps_wpr_process_csv_chunk(form_data); // Recursive call for next chunk.
					} else {

						jQuery("#wps_wpr_loader").hide();
						alert(wps_wpr_object.csv_import_success_msg);
						location.reload();
					}
				}
			},
			error: function(xhr, status, error) {
				jQuery("#wps_wpr_loader").hide();
				alert('Error: ' + xhr.responseText);
			}
		});
	}
});

// Fix notification tab sidebar layout.
jQuery( document ).ready( function ( $ ) {
	function wpsFixNotificationSidebar() {
		const $wrapper = $( '#wps_rwpr_setting_wrapper[data-wps-rma-active-tab="points-notification"]' );
		if ( ! $wrapper.length ) {
			return;
		}

		const $layout = $wrapper.find( '.wps_rma_dashboard_layout' ).first();
		if ( ! $layout.length ) {
			return;
		}

		const $content = $layout.find( '> .wps_rwpr_content_template' ).first();
		let $sidebar = $layout.find( '> .wps_rma_right_sidebar' ).first();
		if ( ! $sidebar.length ) {
			$sidebar = $( '<aside class="wps_rma_right_sidebar"></aside>' );
			$layout.append( $sidebar );
		}

		// Move support cards to the right sidebar, regardless of where they got rendered.
		const $cards = $wrapper.find( '.wps_rma_side_card' );
		if ( $cards.length ) {
			$cards.each( function () {
				$sidebar.append( this );
			} );
		}

		if ( $content.length && $content.parent()[0] !== $layout[0] ) {
			$layout.prepend( $content );
		}
		if ( $sidebar.parent()[0] !== $layout[0] ) {
			$layout.append( $sidebar );
		}

		$layout[0].style.setProperty( 'display', 'grid', 'important' );
		$layout[0].style.setProperty( 'grid-template-columns', 'minmax(0,1fr) 275px', 'important' );
		$layout[0].style.setProperty( 'gap', '14px', 'important' );
		$layout[0].style.setProperty( 'align-items', 'start', 'important' );

		if ( $content.length ) {
			$content[0].style.setProperty( 'grid-column', '1', 'important' );
			$content[0].style.setProperty( 'grid-row', '1', 'important' );
			$content[0].style.setProperty( 'max-width', '100%', 'important' );
			$content[0].style.setProperty( 'width', 'auto', 'important' );
			$content[0].style.setProperty( 'min-width', '0', 'important' );
		}

		if ( $sidebar.length ) {
			$sidebar[0].style.setProperty( 'grid-column', '2', 'important' );
			$sidebar[0].style.setProperty( 'grid-row', '1', 'important' );
			$sidebar[0].style.setProperty( 'display', 'grid', 'important' );
			$sidebar[0].style.setProperty( 'gap', '12px', 'important' );
			$sidebar[0].style.setProperty( 'align-content', 'start', 'important' );
		}
	}

	wpsFixNotificationSidebar();
	setTimeout( wpsFixNotificationSidebar, 100 );
	setTimeout( wpsFixNotificationSidebar, 400 );
	setTimeout( wpsFixNotificationSidebar, 900 );
	$( window ).on( 'load', wpsFixNotificationSidebar );
} );


jQuery(document).ready(function($){

		jQuery(document).on('click','.wps_wpr_repeat_button',function(){

			var error                    = false;
			var empty_message            = '';
			var count                    = $('.wps_wpr_repeat:last').data('id');

			var LevelName                = $('#wps_wpr_membership_level_name_'+count).val();
			var LevelPoints              = $('#wps_wpr_membership_level_value_'+count).val();
			var CategValue               = $('#wps_wpr_membership_category_list_'+count).val();
			var ProdValue                = $('#wps_wpr_membership_product_list_'+count).val();
			var Discount                 = $('#wps_wpr_membership_discount_'+count).val();
			if(!(LevelName) || !(LevelPoints) ||  !(CategValue)  || !(Discount)) {
				if(!(LevelName)) {
					error = true;
					empty_message+= '<div class="notice notice-error is-dismissible"><p><strong>'+wps_wpr_object.LevelName_notice+'</strong></p></div>'; 
					$('#wps_wpr_membership_level_name_'+count).addClass('wps_wpr_error_notice');

				} else {
					$('#wps_wpr_membership_level_name_'+count).removeClass('wps_wpr_error_notice');	
				}

				if(!(LevelPoints)) {
					error = true;
					empty_message+= '<div class="notice notice-error is-dismissible"><p><strong>'+wps_wpr_object.LevelValue_notice+'</strong></p></div>'; 
					$('#wps_wpr_membership_level_value_'+count).addClass('wps_wpr_error_notice');

				} else {
					$('#wps_wpr_membership_level_value_'+count).removeClass('wps_wpr_error_notice');
				}

				if(!(CategValue)) {
					error = true;
					empty_message+= '<div class="notice notice-error is-dismissible"><p><strong>'+wps_wpr_object.CategValue_notice+'</strong></p></div>';
					$('#wps_wpr_membership_category_list_'+count).addClass('wps_wpr_error_notice');
				} else {
					$('#wps_wpr_membership_category_list_'+count).removeClass('wps_wpr_error_notice');
				}

				if(!(Discount)) {
					error = true;
					empty_message+= '<div class="notice notice-error is-dismissible"><p><strong>'+wps_wpr_object.Discount_notice+'</strong></p></div>';
					$('#wps_wpr_membership_discount_'+count).addClass('wps_wpr_error_notice');
				} else {
					$('#wps_wpr_membership_discount_'+count).removeClass('wps_wpr_error_notice');
				}
			}

			if(error) {
				$('.notice.notice-error.is-dismissible').each(function(){
					$(this).remove();
				});
				$('.notice.notice-success.is-dismissible').each(function(){
					$(this).remove();
				});
				$('html, body').animate({
					scrollTop: $(".wps_rwpr_header").offset().top
				}, 800);
				$(empty_message).insertAfter($('.wps_rwpr_header'));
			} else {
				count = parseInt(count)+1; 
				var cat_id;
				var cat_name;
				var html         = "";
				var cat_options  = "";
				var Categ_option = wps_wpr_object.Categ_option;
				var cat_name     = [];
				
				for(var key in Categ_option) {
					cat_name = Categ_option[key].cat_name;
					cat_id = Categ_option[key].id;
					cat_options+='<option value="'+cat_id+'">'+cat_name+'</option>';
				}
			
				html+='<div id ="wps_wpr_parent_repeatable_'+count+'" data-id="'+count+'" class="wps_wpr_repeat">';
				html+='<table class="wps_wpr_repeatable_section">';
				html+='<tr valign="top"><th scope="row" class="titledesc"><label for="wps_wpr_membership_level_name">'+wps_wpr_object.Labeltext+'</label></th>';
				html+='<td class="forminp forminp-text"><label for="wps_wpr_membership_level_name"><input type="text" name="wps_wpr_membership_level_name_'+count+'" value="" id="wps_wpr_membership_level_name_'+count+'" class="text_points" required>'+wps_wpr_object.Labelname+'</label><input type="button" value='+wps_wpr_object.Remove_text+' class="button-primary woocommerce-save-button wps_wpr_remove_button" id="'+count+'"></td></tr>';
				html+='<tr valign="top"><th scope="row" class="titledesc"><label for="wps_wpr_membership_level_value">'+wps_wpr_object.Points+'</label></th><td class="forminp forminp-text"><label for="wps_wpr_membership_level_value"><input type="number" min="1" value="" name="wps_wpr_membership_level_value_'+count+'" id="wps_wpr_membership_level_value_'+count+'" class="input-text" required></label></td></tr>';
				html+='<tr valign="top"><th scope="row" class="titledesc"><label for="wps_wpr_membership_expiration">'+wps_wpr_object.Exp_period+'</label></th><td class="forminp forminp-text"><input type="number" min="1" value="" name="wps_wpr_membership_expiration_'+count+'"id="wps_wpr_membership_expiration_'+count+'" class="input-text"><select id="wps_wpr_membership_expiration_days_'+count+'" name="wps_wpr_membership_expiration_days_'+count+'"><option value="days">'+wps_wpr_object.Days+'</option><option value="weeks">'+wps_wpr_object.Weeks+'</option><option value="months">'+wps_wpr_object.Months+'</option><option value="years">'+wps_wpr_object.Years+'</option>';
				html+='<tr valign="top"><th scope="row" class="titledesc"><label for="wps_wpr_membership_category_list">'+wps_wpr_object.Categ_text+'</label></th><td class="forminp forminp-text"><select id="wps_wpr_membership_category_list_'+count+'" class="wps_wpr_common_class_categ" data-id="'+count+'" multiple="multiple" name="wps_wpr_membership_category_list_'+count+'[]">'+cat_options+'</select></td></tr>';
				html+='<tr valign="top"><th scope="row" class="titledesc"><label for="wps_wpr_membership_product_list">'+wps_wpr_object.Prod_text+'</label></th><td class="forminp forminp-text"><select id="wps_wpr_membership_product_list_'+count+'" multiple="multiple" name="wps_wpr_membership_product_list_'+count+'[]"></select></td></tr>';
				html+='<tr valign="top"><th scope="row" class="titledesc"><label for="wps_wpr_membership_discount">'+wps_wpr_object.Discounttext+'</label></th><td class="forminp forminp-text"><label for="wps_wpr_membership_discount"><input type="number" min="0" max="100" value="0" name="wps_wpr_membership_discount_'+count+'" id="wps_wpr_membership_discount_'+count+'" class="input-text"></label></td></tr>';
				html+='<tr valign="top"><th scope="row" class="titledesc"><label for="wps_wpr_enable_to_rewards_with_points">'+wps_wpr_object.enble_mem_reward_label+'</label></th><td class="forminp forminp-text"><label for="wps_wpr_enable_to_rewards_with_points"><input type="checkbox" value="1" name="wps_wpr_enable_to_rewards_with_points_'+count+'" id="wps_wpr_enable_to_rewards_with_points_'+count+'" class="input-text"></label></td></tr>';
				html+='<tr valign="top"><th scope="row" class="titledesc"><label for="wps_wpr_mem_reward_type">'+wps_wpr_object.mem_points_type+'</label></th><td class="forminp forminp-text"><label for="wps_wpr_choose_mem_points_type"><select name="wps_wpr_choose_mem_points_type_'+count+'" id="wps_wpr_choose_mem_points_type_'+count+'" class="wps_wpr_assign_mem_rewards_points"><option value="fixed">Fixed</option><option value="percent">Percent</option></select></label></td><input type="hidden" value="'+count+'" name="hidden_count"></tr>';
				html+='<tr valign="top"><th scope="row" class="titledesc"><label for="wps_wpr_mem_rewards_points">'+wps_wpr_object.Points+'</label></th><td class="forminp forminp-text"><label for="wps_wpr_assign_mem_points_val"><input type="number" min="0" name="wps_wpr_assign_mem_points_val_'+count+'" id="wps_wpr_assign_mem_points_val_'+count+'" value="0"></label></td><input type="hidden" value="'+count+'" name="hidden_count"></tr>';
				html+='<tr valign="top"><th scope="row" class="titledesc"><label for="wps_wpr_enable_free_shipping_">'+wps_wpr_object.wps_wpr_free_shipping+'</label></th><td class="forminp forminp-text"><label for="wps_wpr_enable_free_shipping_"><input type="checkbox" value="1" name="wps_wpr_enable_free_shipping_'+count+'" id="wps_wpr_enable_free_shipping_'+count+'" class="input-text"></label></td></tr>';
				html+='<tr valign="top"><th scope="row" class="titledesc"><label for="wps_wpr_enable_mem_wise_per_curr_">'+wps_wpr_object.wps_enable_mem_per_curr+'</label></th><td class="forminp forminp-text"><label for="wps_wpr_enable_mem_wise_per_curr_"><input type="checkbox" value="1" name="wps_wpr_enable_mem_wise_per_curr_'+count+'" id="wps_wpr_enable_mem_wise_per_curr_'+count+'" class="input-text"></label></td></tr>';
				html+='<tr valign="top"><th scope="row" class="titledesc"><label for="wps_wpr_membership_wise_price_">'+wps_wpr_object.wps_set_mem_curr_values+'</label></th><td class="forminp forminp-text"><label for="wps_wpr_membership_wise_price_"><input type="number" value="0" name="wps_wpr_membership_wise_price_'+count+'" id="wps_wpr_membership_wise_price_'+count+'" class="input-text"></label><label for="wps_wpr_membership_wise_points_"><input type="number" value="0" name="wps_wpr_membership_wise_points_'+count+'" id="wps_wpr_membership_wise_points_'+count+'" class="input-text"></label></td></tr></table></div>';
				$('.parent_of_div').append(html);
				$('#wps_wpr_parent_repeatable_'+count+'').find('#wps_wpr_membership_category_list_'+count).select2();
				$('#wps_wpr_parent_repeatable_'+count+'').find('#wps_wpr_membership_product_list_'+count).select2();
			}
		});

	// open whatsapp sample template.
    jQuery(document).on('click', '.wps_wpr_preview_whatsapp_sample', function(e){

        jQuery(document).find('.wps_wpr_preview_whatsapp_sample').css('color', '#2271b1');
        e.preventDefault();
        jQuery('.wps_wpr_preview_whatsapp_template_img').show();
    });

    // Hide modal when clicking outside the image.
    jQuery(document).on('click', '.wps_wpr_preview_whatsapp_template_img', function(){

        jQuery('.wps_wpr_preview_whatsapp_template_img').hide();
    });

	// sync points on Klaviyo.
	// Reset Points feature start here.
	jQuery(document).on('click', '#wps_wpr_syncs_points_on_klaviyo_btn', function(){

		var wps_wpr_klaviyo_public_api_key = jQuery('#wps_wpr_klaviyo_public_api_key').val().trim();
		if ( wps_wpr_klaviyo_public_api_key ) {

			jQuery(this).prop( 'disabled', true );
			wps_wpr_recursive_to_sync_points_on_klaviyo( wps_wpr_object.wps_user_count );
		} else {

			jQuery('.wps_wpr_klaviyo_sync_notice').show().css('color', 'red').html('please enter your Klaviyo public API key.');
		}
	});

	// Recursive call back.
	function wps_wpr_recursive_to_sync_points_on_klaviyo( user_count, current_page = '' ) {

		var wps_wpr_klaviyo_public_api_key = jQuery('#wps_wpr_klaviyo_public_api_key').val().trim();
		var get_count                      = 50; // Default count to sync per request.
		if ( user_count > get_count ) {

			get_count = get_count;
		} else {
			get_count = user_count;
		}

		var data = {
			'action'                 : 'wps_sync_points_on_klaviyo',
			'wps_nonce'              : wps_wpr_object.wps_wpr_nonce,
			'current_page'           : current_page,
			'per_user'               : get_count,
			'klaviyo_public_api_key' : wps_wpr_klaviyo_public_api_key,
		};

		jQuery('.wps_wpr_klaviyo_sync_loader').show();
		jQuery('.wps_wpr_klaviyo_sync_notice').show();

		jQuery.ajax({
			'method' : 'POST',
			'url'    : wps_wpr_object.ajaxurl,
			'data'   : data,
			success  : function( response ) {
				if ( parseInt( user_count ) >= parseInt( response.offset ) + parseInt( response.per_user ) ) {

					if ( response.offset <= 0 ) {

						var reset_status = get_count;
					} else {

						reset_status = parseFloat( response.offset ) + parseFloat( get_count );
					}

					jQuery('.wps_wpr_klaviyo_sync_notice').css('color', 'green').html( reset_status + ' user points has been successfully synced' );
					wps_wpr_recursive_to_sync_points_on_klaviyo( user_count, response.current_page );
				} else {

					jQuery('#wps_wpr_syncs_points_on_klaviyo_btn').prop( 'disabled', false );
					jQuery('.wps_wpr_klaviyo_sync_loader').hide();
					jQuery('.wps_wpr_klaviyo_sync_notice').hide();
					window.location.reload();
				}
			},
			error    : function( error ) {
				console.log( error );
			}
		});
	}

	// when quiz is enbale make the all quiz fields are required.
	jQuery(document).on('change', '.wps_wpr_enable_quiz_contest_campaign', function () {
		const checked = jQuery(this).is(':checked');
		if ( jQuery('.wps_wpr_quiz_row').length == 1 ) {
			if (checked) {
				jQuery('.wps_wpr_quiz_field').prop('required', true);
			} else {
				jQuery('.wps_wpr_quiz_field').prop('required', false);
			}
		}
	});

	// CAMPAIGN JS part.
		var maxQuizzes = 4;
		// Add quiz.
		$('#wps_wpr_add_quiz').click(function(e){
			e.preventDefault();

			if(!validateLastQuiz()) {
				alert('Please fill all fields in the current quiz before adding a new one.');
				return;
			}

			var totalQuizzes = $('#wps_wpr_quiz_container .wps_wpr_quiz_row').length;
			if(totalQuizzes >= maxQuizzes) {
				alert('Maximum quizzes reached');
				return;
			}

			var $firstQuiz = $('#wps_wpr_quiz_container .wps_wpr_quiz_row:first').clone();
			$firstQuiz.find('input').val('');
			$firstQuiz.find('.validation_error').hide();
			$('#wps_wpr_quiz_container').append($firstQuiz);

			toggleRemoveButtons();
		});

		// Remove quiz
		$('#wps_wpr_quiz_container').on('click', '.wps_wpr_remove_quiz', function(e){
			e.preventDefault();
			$(this).closest('.wps_wpr_quiz_row').remove();
			toggleRemoveButtons();
		});

		// Initial check
		toggleRemoveButtons();

		// function calling for remove button which remove the quiz section.
		function toggleRemoveButtons() {

			var $rows = jQuery('#wps_wpr_quiz_container .wps_wpr_quiz_row');
			if ($rows.length > 1) {
				$rows.find('.wps_wpr_remove_quiz').show();
			} else {
				$rows.find('.wps_wpr_remove_quiz').hide();
			}
		}

		// calling function for validating quiz section fields.
		function validateLastQuiz() {
			var lastQuiz = jQuery('#wps_wpr_quiz_container .wps_wpr_quiz_row').last();
			var valid = true;

			lastQuiz.find('.wps_wpr_quiz_field').each(function(){
				if(jQuery.trim(jQuery(this).val()) === '') {
					jQuery(this).next('.validation_error').show();
					valid = false;
				} else {
					jQuery(this).next('.validation_error').hide();
				}
			});

			return valid;
		}


	// Open Campaign existing modal template.
	jQuery(document).on('click', '.wps_wpr_view_campaign_existing_template', function(e){

		e.preventDefault();
		$(".wps-popup").addClass("popup--active");
	});

	// close Campaign template modal.
	$(document).on(
		"click",
		".wps-popup_shadow,.wps-popup_m-close",
		function (e) {
			$(".wps-popup").removeClass("popup--active");
		}
	);

	// Make Active tab on Banner modal.
	$(document).on('click', '.wps-popup_m-sidebar span', function() {
		// Get the ID of the clicked tab
		const tabId = $(this).attr('id');

		// Remove the 'active_tab' class from all content
		$('.wps-popup_m-content').removeClass('active_tab');
		
		// Add the 'active_tab' class to the relevant content based on the tab clicked
		$(`.${tabId}`).addClass('active_tab');
		
		// Remove the 'active' class from all sidebar spans
		$(".wps-popup_m-sidebar span").removeClass("active");

		// Add the 'active' class to the clicked tab
		$(this).addClass("active");

		// Store the active tab ID in localStorage
		localStorage.setItem('activeTab', tabId);
	});

	// On page load, check if there's an active tab in localStorage
	const activeTab = localStorage.getItem('activeTab');
	if (activeTab) {
		// Set the active tab and content based on localStorage
		$(`#${activeTab}`).addClass("active");  // Set active class on the tab
		$(`.${activeTab}`).addClass("active_tab");  // Set active_tab class on the content
	}

	// Ajax call for set the banner image, heading and modal color.
	$(document).on("click", ".wps_wpr_apply_banner_img", function (e) {
		e.preventDefault();

		const $button  = $(this);
		const $banner  = $button.closest(".wps-popup_mcb-img");
		const $section = $banner.closest(".wps-popup_m-content");

		// Reset all banners
		$(".wps-popup_mcb-img").removeClass("button--active");
		$(".wps_wpr_apply_banner_img").text("Apply");

		// Mark current banner
		$banner.addClass("button--active");

		// Gather banner details
		const banner_heading = $banner.find(".wps_wpr_camp_banner_heading").text();
		const banner_image   = $banner.find(".wps_wpr_cam_banner_image").attr("src");
		const modal_prim_col = $banner.find(".wps_wpr_cam_prim_color").text();
		const modal_sec_col  = $banner.find(".wps_wpr_cam_sec_color").text();

		// Get the "festival section" class (halloween / black_friday / happy_easter)
		const classes = $section.attr("class").split(/\s+/);
		const sectionClass = classes.find(cls => cls.startsWith("wps_wpr_"));

		// Save section + index in localStorage
		const applied_banner = {
			section: sectionClass,   // e.g. "wps_wpr_black_friday"
			index: $banner.index()
		};
		localStorage.setItem("applied_banner", JSON.stringify(applied_banner));

		// Perform AJAX request
		$.ajax({
			url     : wps_wpr_object.ajaxurl,
			method  : "POST",
			data    : {
				action         : "wps_set_camp_heading_and_image",
				wps_nonce      : wps_wpr_object.wps_wpr_nonce,
				banner_heading : banner_heading,
				banner_image   : banner_image,
				modal_prim_col : modal_prim_col,
				modal_sec_col  : modal_sec_col
			},
			success : function() {
				$button.text("Applied");
				setTimeout(function () {
					$(".wps-popup").removeClass("popup--active");
					window.location.reload();
				}, 800);
			},
			error: function(xhr, status, error) {
				console.error("Error:", error);
				alert("An error occurred while updating the campaign. Please try again.");
			}
		});
	});

	// Restore selection on page load.
	const saved = localStorage.getItem("applied_banner");
	if (saved) {

		const { section, index } = JSON.parse(saved);

		// Reset everything
		$(".wps-popup_mcb-img").removeClass("button--active");
		$(".wps_wpr_apply_banner_img").text("Apply");
		$(".wps-popup_m-content").removeClass("active_tab");
		$(".wps-popup_m-sidebar span").removeClass("active");

		// Activate correct section and sidebar
		$(`.${section}`).addClass("active_tab");
		$(`#${section}`).addClass("active"); // assumes sidebar spans have ids = section classes

		// Highlight the saved banner
		const $target = $(`.${section}`).find(".wps-popup_mcb-img").eq(index);
		if ($target.length) {
			$target.addClass("button--active")
				.find(".wps_wpr_apply_banner_img").text("Applied");
		}
	}

	// Social share campaign: Add/remove blocks, validate before adding new.
	const $section = $( '.wps_wpr_general_row_wrap' );
	const $addBtn  = $( '#wps_wpr_add_social_share_campaign' );
	const tpl      = document.getElementById( 'wps_wpr_campaign_template' );

	// Utility: show/hide error box
	function wps_showError( msg ) {
		let $error = $( '#wps_wpr_error_message' );
		if ( $error.length === 0 ) {
			$error = $( '<div id="wps_wpr_error_message" class="wps-error-msg" role="alert"></div>' )
				.insertBefore( $addBtn );
		}
		$error.text( msg );
	}

	function wps_hideError() {
		$( '#wps_wpr_error_message' ).remove();
	}

	// Validate a single article block (only visible fields inside block)
	function validateBlock( $article ) {
		let isValid = true;
		$article.find( 'input, select' ).each( function () {
			const $f = $( this );
			// treat unchecked checkboxes/radios as not relevant; but here we only have inputs/selects
			if ( $.trim( String( $f.val() ) ) === '' ) {
				$f.addClass( 'wps-error-field' );
				isValid = false;
			} else {
				$f.removeClass( 'wps-error-field' );
			}
		} );
		return isValid;
	}

	// Create a new block from template (returns jQuery object)
	function createBlockFromTemplate() {
		if ( ! tpl ) {
			// fallback: clone last block
			const $last = $section.find( 'article.wps_wpr_general_row' ).last();
			const $clone = $last.clone();
			$clone.find( 'input' ).val( '' );
			$clone.find( 'select' ).prop( 'selectedIndex', 0 );
			return $clone;
		}
		const frag = tpl.content.cloneNode( true );
		// jQuery can't wrap DocumentFragment directly in older jQuery; so find the article inside
		const $frag = $( frag );
		const $article = $frag.find( 'article.wps_wpr_general_row' ).first();
		// make sure no duplicate IDs or other state exist (template already blank)
		$article.find( 'input, select' ).val( '' ).prop( 'selectedIndex', 0 );
		return $article;
	}

	// Add handler
	$addBtn.on( 'click', function ( e ) {
		e.preventDefault();
		wps_hideError();

		const $lastArticle = $section.find( 'article.wps_wpr_general_row' ).last();

		// If there's an existing last article, validate it first
		if ( $lastArticle.length && ! validateBlock( $lastArticle ) ) {
			wps_showError( 'Please fill all fields before adding a new campaign.' );
			// scroll to first error field
			const $firstErr = $lastArticle.find( '.wps-error-field' ).first();
			if ( $firstErr.length ) {
				$firstErr.focus();
			}
			return;
		}

		const $newArticle = createBlockFromTemplate();
		// Insert the new article before the Add button row
		$addBtn.closest( 'div' ).before( $newArticle );
		// focus first input of the new block
		$newArticle.find( 'input, select' ).first().focus();
	} );

	// Remove handler (delegated)
	$section.on( 'click', '.wps_wpr_remove_campaign', function ( e ) {
		e.preventDefault();
		const $article = $( this ).closest( 'article.wps_wpr_general_row' );
		const total = $section.find( 'article.wps_wpr_general_row' ).length;

		if ( total > 1 ) {
			$article.remove();
		} else {
			// If it's the only one, clear fields instead of removing markup (keeps names present)
			$article.find( 'input' ).val( '' );
			$article.find( 'select' ).prop( 'selectedIndex', 0 );
		}
		wps_hideError();
	} );

	// Live input: remove error highlight when user types/selects
	$section.on( 'input change', 'input, select', function () {
		const $f = $( this );
		if ( $.trim( String( $f.val() ) ) !== '' ) {
			$f.removeClass( 'wps-error-field' );
			// if no more fields with error, remove global message
			if ( $section.find( '.wps-error-field' ).length === 0 ) {
				wps_hideError();
			}
		}
	} );

});
