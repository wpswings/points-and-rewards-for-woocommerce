!(function (e) {
	"use strict";
	e(document).ready(function () {
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
		e(document).find("#wps_wpr_restrictions_for_purchasing_cat").select2(),
		e(document).find("#wps_wpr_restrict_redeem_points_category_wise").select2(),
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
	  wps_wpr_object.check_pro_activate &&
		jQuery(document).on("click", ".wps_wpr_repeat_button", function () {
		  var r = "";
		  e(document).find(".wps_wpr_object_purchase").remove(),
			(r =
			  '<div class="wps_wpr_object_purchase"><p>' +
			  wps_wpr_object.pro_text +
			  ' <a target="_blanck" href="' +
			  wps_wpr_object.pro_link +
			  '">' +
			  wps_wpr_object.pro_link_text +
			  "</a></p></div>"),
			e(".parent_of_div").append(r);
		}),
		wps_wpr_object.check_pro_activate &&
		  e(document).on("click", "#wps_wpr_add_more", function () {
			var r = "";
			e(document).find(".wps_wpr_object_purchase").remove(),
			  e(
				(r =
				  '<div class="wps_wpr_object_purchase"><p>' +
				  wps_wpr_object.pro_text +
				  ' <a target="_blanck" href="' +
				  wps_wpr_object.pro_link +
				  '">' +
				  wps_wpr_object.pro_link_text +
				  "</a></p></div>")
			  ).insertAfter(".wp-list-table");
		  }),
		jQuery(document).on("click", ".wps_wpr_remove_button", function () {
		  var r = e(this).attr("id");
		  0 == r &&
			(e(document).find(".wps_wpr_repeat_button").hide(),
			e("#wps_wpr_membership_setting_enable").attr("checked", !1)),
			e("#wps_wpr_parent_repeatable_" + r).remove();
		}),
		e(document).on("click", "#dismiss_notice", function (r) {
		  r.preventDefault();
		  var i = {
			action: "wps_wpr_dismiss_notice",
			wps_nonce: wps_wpr_object.wps_wpr_nonce,
		  };
		  e.ajax({
			url: wps_wpr_object.ajaxurl,
			type: "POST",
			data: i,
			success: function (e) {
			  window.location.reload();
			},
		  });
		}),
		jQuery(document).on(
		  "click",
		  "#wps_wpr_points_on_previous_order",
		  function () {
			var e = jQuery("#wps_wpr_previous_order_point_value").val().trim();
			if (
			  (jQuery(this).prop("disabled", !0),
			  jQuery(".wps_wpr_previous_order_notice").hide(),
			  jQuery(".wps_wpr_previous_order_notice").html(""),
			  parseInt(e) > 0)
			) {
			  var r = {
				action: "assign_points_on_previous_order",
				nonce: wps_wpr_object.wps_wpr_nonce,
				rewards_points: e,
			  };
			  jQuery(".wps_wpr_previous_order_loader").show(),
				jQuery.ajax({
				  method: "POST",
				  url: wps_wpr_object.ajaxurl,
				  data: r,
				  success: function (e) {
					jQuery(".wps_wpr_previous_order_loader").hide(),
					  jQuery("#wps_wpr_points_on_previous_order").prop(
						"disabled",
						!1
					  ),
					  !0 == e.result
						? (jQuery(".wps_wpr_previous_order_notice").show(),
						  jQuery(".wps_wpr_previous_order_notice").css(
							"color",
							"green"
						  ),
						  jQuery(".wps_wpr_previous_order_notice").html(e.msg))
						: (jQuery(".wps_wpr_previous_order_notice").show(),
						  jQuery(".wps_wpr_previous_order_notice").css(
							"color",
							"red"
						  ),
						  jQuery(".wps_wpr_previous_order_notice").html(e.msg));
				  },
				});
			} else
			  jQuery("#wps_wpr_points_on_previous_order").prop("disabled", !1),
				jQuery(".wps_wpr_previous_order_notice").show(),
				jQuery(".wps_wpr_previous_order_notice").css("color", "red"),
				jQuery(".wps_wpr_previous_order_notice").html(
				  "Please enter valid points"
				);
		  }
		);
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
				.attr("required", !1),
			jQuery(document)
				.find("#wps_wpr_membership_category_list_" + e)
				.attr("required", !1),
			jQuery(document)
				.find("#wps_wpr_membership_discount_" + e)
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
				.attr("required", !0),
			jQuery(document)
				.find("#wps_wpr_membership_category_list_" + e)
				.attr("required", !0),
			jQuery(document)
				.find("#wps_wpr_membership_discount_" + e)
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
  
			  var new_row = '<tr class="wps_wpr_add_game_segment_dynamically"><td><input type="text" name="wps_wpr_enter_segment_name[]" id="wps_wpr_enter_segment_name" value="" required></td><td><input type="number" min="1" name="wps_wpr_enter_segment_points[]" id="wps_wpr_enter_segment_points" value="" required></td><td><input type="number" max="20" min="1" name="wps_wpr_enter_sgemnet_font_size[]" id="wps_wpr_enter_sgemnet_font_size" value="" required></td><td><input type="color" name="wps_wpr_enter_segment_color[]" id="wps_wpr_enter_segment_color[]" class="wps_wpr_enter_segment_color" value=""></td><td><input type="button" name="wps_wpr_remove_game_segment" id="wps_wpr_remove_game_segment" value="+"></td></tr>';
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

	// if pro is not activated than show notice for user.
	jQuery('.wps_wpr_pro_plugin_notices').hide();
	if ( wps_wpr_object.check_pro_activate ) {
		jQuery(document).on('click', '#wps_wpr_user_badges_fields_add', function(){

			jQuery(document).find('.wps_wpr_object_purchase').remove();
			var pro_plugin_msg = '<div class="wps_wpr_object_purchase"><p>' + wps_wpr_object.badge_pro__text + ' <a target="_blanck" href="' + wps_wpr_object.pro_link + '">' + wps_wpr_object.pro_link_text + "</a></p></div>";
			jQuery('.wps_wpr_pro_plugin_notices').show();
			jQuery('.wps_wpr_pro_plugin_notices').append( pro_plugin_msg );
		});
	}

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

	// plugin banner ajax.
	jQuery(document).on( 'click', '#dismiss-banner', function(){
		var data = {
			action:'wps_wpr_ajax_banner_action',
			wps_nonce:wps_wpr_object.wps_wpr_nonce
		};
		jQuery.ajax({
			url: wps_wpr_object.ajaxurl,
			type: "POST",
			data: data,
			success: function(response) {
				window.location.reload();
			}
		});
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
  