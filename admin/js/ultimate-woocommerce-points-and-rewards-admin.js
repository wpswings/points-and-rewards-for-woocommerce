(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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

	$(document).ready(function() {
		// On License form submit.
		jQuery(document).on('click','#ultimate-woocommerce-points-and-rewards-license-activate',function(e){
			e.preventDefault();
			$( 'div#ultimate-woocommerce-points-and-rewards-ajax-loading-gif' ).css( 'display', 'inline-block' );

			var license_key =  $( 'input#ultimate-woocommerce-points-and-rewards-license-key' ).val();
			ultimate_woocommerce_points_and_rewards_license_request( license_key );
		});
		
		// License Ajax request.
		function ultimate_woocommerce_points_and_rewards_license_request( license_key ) {

			$.ajax({

		        type:'POST',
		        dataType: 'json',
	    		url: license_ajax_object.ajaxurl,

		        data: {
		        	'action': 'ultimate_woocommerce_points_and_rewards_license',
		        	'ultimate_woocommerce_points_and_rewards_purchase_code': license_key,
		        	'ultimate-woocommerce-points-and-rewards-license-nonce': license_ajax_object.license_nonce,
		        },

		        success:function( data ) {

		        	$( 'div#ultimate-woocommerce-points-and-rewards-ajax-loading-gif' ).hide();

		        	if ( false === data.status ) {

	                    $( "p#ultimate-woocommerce-points-and-rewards-license-activation-status" ).css( "color", "#ff3333" );
	                }

	                else {

	                	$( "p#ultimate-woocommerce-points-and-rewards-license-activation-status" ).css( "color", "#42b72a" );
	                }

		        	$( 'p#ultimate-woocommerce-points-and-rewards-license-activation-status' ).html( data.msg );

		        	if ( true === data.status ) {

	                    setTimeout(function() {
	                    	window.location = license_ajax_object.reloadurl;
	                    }, 500);
	                }
		        }
			});
		}
		/*Add Section in the membership section list*/
		jQuery(document).on('click','.mwb_wpr_repeat_button',function(){
    		var error = false;
    		var empty_message = '';
    		var count = $('.mwb_wpr_repeat:last').data('id');
    		var LevelName = $('#mwb_wpr_membership_level_name_'+count).val();
    		var LevelPoints = $('#mwb_wpr_membership_level_value_'+count).val();
    		var CategValue = $('#mwb_wpr_membership_category_list_'+count).val();
    		var ProdValue = $('#mwb_wpr_membership_product_list_'+count).val();
    		var Discount = $('#mwb_wpr_membership_discount_'+count).val();
    		if(!(LevelName) || !(LevelPoints) ||  !(CategValue)  || !(Discount))
    		{	
    			
    			if(!(LevelName))
    			{
    				error = true;
    				empty_message+= '<div class="notice notice-error is-dismissible"><p><strong>'+license_ajax_object.LevelName_notice+'</strong></p></div>'; 
    				$('#mwb_wpr_membership_level_name_'+count).addClass('mwb_wpr_error_notice');

    			}
    			else
    			{
    				$('#mwb_wpr_membership_level_name_'+count).removeClass('mwb_wpr_error_notice');	
    			}
    			if(!(LevelPoints))
    			{
    				error = true;
    				empty_message+= '<div class="notice notice-error is-dismissible"><p><strong>'+license_ajax_object.LevelValue_notice+'</strong></p></div>'; 
    				$('#mwb_wpr_membership_level_value_'+count).addClass('mwb_wpr_error_notice');

    			}
    			else
    			{
    				$('#mwb_wpr_membership_level_value_'+count).removeClass('mwb_wpr_error_notice');
    			}
    			if(!(CategValue))
    			{
    				error = true;
    				empty_message+= '<div class="notice notice-error is-dismissible"><p><strong>'+license_ajax_object.CategValue_notice+'</strong></p></div>';
    				$('#mwb_wpr_membership_category_list_'+count).addClass('mwb_wpr_error_notice');
    			}
    			else
    			{
    				$('#mwb_wpr_membership_category_list_'+count).removeClass('mwb_wpr_error_notice');
    			}
    			if(!(Discount))
    			{
    				error = true;
    				empty_message+= '<div class="notice notice-error is-dismissible"><p><strong>'+license_ajax_object.Discount_notice+'</strong></p></div>';
    				$('#mwb_wpr_membership_discount_'+count).addClass('mwb_wpr_error_notice');
    			}
    			else
    			{
    				$('#mwb_wpr_membership_discount_'+count).removeClass('mwb_wpr_error_notice');
    			}
    		}
    		if(error)
    		{
    			/*$("#mwb_wpr_error_notice").html(error_notice);
	        	$('#mwb_wpr_error_notice').css('display', 'table');*/

	        	$('.notice.notice-error.is-dismissible').each(function(){
                	$(this).remove();
	            });
	            $('.notice.notice-success.is-dismissible').each(function(){
	                $(this).remove();
	            });
	            $('html, body').animate({
                	scrollTop: $(".mwb_rwpr_header").offset().top
            	}, 800);
				$(empty_message).insertAfter($('.mwb_rwpr_header'));
    		}
    		else
    		{	
    			count = parseInt(count)+1; 
    			var cat_id;
    			var cat_name;
	    		var html = ""; var cat_options = "";
	    		var Categ_option = license_ajax_object.Categ_option;
	    		var cat_name = [];
	    		
	    		for(var key in Categ_option)
	    		{
	    			cat_name = Categ_option[key].cat_name;
	    			cat_id = Categ_option[key].id;
	    			cat_options+='<option value="'+cat_id+'">'+cat_name+'</option>';
	    		}
	    	
	    		html+='<div id ="mwb_wpr_parent_repeatable_'+count+'" data-id="'+count+'" class="mwb_wpr_repeat">';
	    		html+='<table class="mwb_wpr_repeatable_section">';
	    		html+='<tr valign="top"><th scope="row" class="titledesc"><label for="mwb_wpr_membership_level_name">'+license_ajax_object.Labeltext+'</label></th>';
	    		html+='<td class="forminp forminp-text"><label for="mwb_wpr_membership_level_name"><input type="text" name="mwb_wpr_membership_level_name_'+count+'" value="" id="mwb_wpr_membership_level_name_'+count+'" class="text_points" required>'+license_ajax_object.Labelname+'</label><input type="button" value='+license_ajax_object.Remove_text+' class="button-primary woocommerce-save-button mwb_wpr_remove_button" id="'+count+'"></td></tr>';
	    		html+='<tr valign="top"><th scope="row" class="titledesc"><label for="mwb_wpr_membership_level_value">'+license_ajax_object.Points+'</label></th><td class="forminp forminp-text"><label for="mwb_wpr_membership_level_value"><input type="number" min="1" value="" name="mwb_wpr_membership_level_value_'+count+'" id="mwb_wpr_membership_level_value_'+count+'" class="input-text" required></label></td></tr>';
	    		html+='<tr valign="top"><th scope="row" class="titledesc"><label for="mwb_wpr_membership_expiration">'+license_ajax_object.Exp_period+'</label></th><td class="forminp forminp-text"><input type="number" min="1" value="" name="mwb_wpr_membership_expiration_'+count+'"id="mwb_wpr_membership_expiration_'+count+'" class="input-text"><select id="mwb_wpr_membership_expiration_days_'+count+'" name="mwb_wpr_membership_expiration_days_'+count+'"><option value="days">'+license_ajax_object.Days+'</option><option value="weeks">'+license_ajax_object.Weeks+'</option><option value="months">'+license_ajax_object.Months+'</option><option value="years">'+license_ajax_object.Years+'</option>';
	    		html+='<tr valign="top"><th scope="row" class="titledesc"><label for="mwb_wpr_membership_category_list">'+license_ajax_object.Categ_text+'</label></th><td class="forminp forminp-text"><select id="mwb_wpr_membership_category_list_'+count+'" required="true" class="mwb_wpr_common_class_categ" data-id="'+count+'" multiple="multiple" name="mwb_wpr_membership_category_list_'+count+'[]">'+cat_options+'</select></td></tr>';
	    		html+='<tr valign="top"><th scope="row" class="titledesc"><label for="mwb_wpr_membership_product_list">'+license_ajax_object.Prod_text+'</label></th><td class="forminp forminp-text"><select id="mwb_wpr_membership_product_list_'+count+'" multiple="multiple" name="mwb_wpr_membership_product_list_'+count+'[]"></select></td></tr>';
	    		html+='<tr valign="top"><th scope="row" class="titledesc"><label for="mwb_wpr_membership_discount">'+license_ajax_object.Discounttext+'</label></th><td class="forminp forminp-text"><label for="mwb_wpr_membership_discount"><input type="number" min="1" value="" name="mwb_wpr_membership_discount_'+count+'" id="mwb_wpr_membership_discount_'+count+'" class="input-text" required></label></td><input type = "hidden" value="'+count+'" name="hidden_count"></tr></table></div>';
				$('.parent_of_div').append(html);
	    		$('#mwb_wpr_parent_repeatable_'+count+'').find('#mwb_wpr_membership_category_list_'+count).select2();
	    		$('#mwb_wpr_parent_repeatable_'+count+'').find('#mwb_wpr_membership_product_list_'+count).select2();
    		}
    	});

    	jQuery(document).on('click','.mwb_wpr_remove_button',function(){
    		//$('.parent_of_div .mwb_wpr_repeat:last').remove();
    		var curr_div = $(this).attr('id');
    		if(curr_div == 0) {
    			$(document).find('.mwb_wpr_repeat_button').hide();
    			$('#mwb_wpr_membership_setting_enable').attr('checked',false);
    		}
    		$('#mwb_wpr_parent_repeatable_'+curr_div).remove();
    		
    	});

    	$(document).on("click",".mwb_wpr_submit_per_category",function(){
    			var mwb_wpr_categ_id = $(this).attr('id');
    			var mwb_wpr_categ_point = $('#mwb_wpr_points_to_per_categ_'+mwb_wpr_categ_id).val();
    			var data = [];
    			if(mwb_wpr_categ_point.length > 0)
    			{
    				if(mwb_wpr_categ_point % 1 === 0 && mwb_wpr_categ_point > 0)
	    			{
	    				jQuery("#mwb_wpr_loader").show();
						data = {
							action:'mwb_wpr_per_pro_category',
							mwb_wpr_categ_id:mwb_wpr_categ_id,
							mwb_wpr_categ_point:mwb_wpr_categ_point,
							mwb_nonce:license_ajax_object.mwb_wpr_nonce,
						};
				      	$.ajax({
				  			url: license_ajax_object.ajaxurl, 
				  			type: "POST",  
				  			data: data,
				  			dataType :'json',
				  			success: function(response) 
				  			{	
				  				
					  			if(response.result == 'success')
			                    {	var category_id = response.category_id;
			                    	var categ_point = response.categ_point;
		                        	jQuery('#mwb_wpr_points_to_per_categ_'+category_id).val(categ_point);
		                        	$('.notice.notice-error.is-dismissible').each(function(){
									$(this).remove();
									});
									$('.notice.notice-success.is-dismissible').each(function(){
										$(this).remove();
									});
									
									$('html, body').animate({
								        scrollTop: $(".mwb_rwpr_header").offset().top
								    }, 800);
								    var assing_message = '<div class="notice notice-success is-dismissible"><p><strong>'+license_ajax_object.success_assign+'</strong></p></div>';
								    $(assing_message).insertBefore($('.mwb_wpr_general_wrapper'));
			                        jQuery("#mwb_wpr_loader").hide();
			                    }
				  			}
				  		});	
	    			}
	    			else
	    			{
	    				$('.notice.notice-error.is-dismissible').each(function(){
						$(this).remove();
						});
						$('.notice.notice-success.is-dismissible').each(function(){
							$(this).remove();
						});
						
						$('html, body').animate({
					        scrollTop: $(".mwb_rwpr_header").offset().top
					    }, 800);
					    var valid_point = '<div class="notice notice-error is-dismissible"><p><strong>'+license_ajax_object.error_assign+'</strong></p></div>';
					    $(remove_message).insertAfter($('.mwb_wpr_general_wrapper'));
	    			}
    			}
    			else
    			{	
    				jQuery("#mwb_wpr_loader").show();
					data = {
						action:'mwb_wpr_per_pro_category',
						mwb_wpr_categ_id:mwb_wpr_categ_id,
						mwb_wpr_categ_point:mwb_wpr_categ_point,
						mwb_nonce:license_ajax_object.mwb_wpr_nonce,
					};
			      	$.ajax({
			  			url: license_ajax_object.ajaxurl, 
			  			type: "POST",  
			  			data: data,
			  			dataType :'json',
			  			success: function(response) 
			  			{	
			  				
				  			if(response.result == 'success')
		                    {	var category_id = response.category_id;
		                    	var categ_point = response.categ_point;
	                        	jQuery('#mwb_wpr_points_to_per_categ_'+category_id).val(categ_point);
	                        	$('.notice.notice-error.is-dismissible').each(function(){
								$(this).remove();
								});
								$('.notice.notice-success.is-dismissible').each(function(){
									$(this).remove();
								});
								$('html, body').animate({
							        scrollTop: $(".mwb_rwpr_header").offset().top
							    }, 800);
							    var remove_message = '<div class="notice notice-success is-dismissible"><p><strong>'+license_ajax_object.success_remove+'</strong></p></div>';
							    $(remove_message).insertBefore($('.mwb_wpr_general_wrapper'));
		                        jQuery("#mwb_wpr_loader").hide();
		                    }
			  			}
			  		});
    			}
		});
		/*Assign the product purchase points category wise*/
		$(document).on("click",".mwb_wpr_submit_purchase_points_per_category",function(){			
    			var mwb_wpr_categ_id = $(this).attr('id');
    			var mwb_wpr_categ_point = $('#mwb_wpr_purchase_points_cat'+mwb_wpr_categ_id).val();
    			var data = [];    			
    			if(mwb_wpr_categ_point.length > 0)
    			{
    				if(mwb_wpr_categ_point % 1 === 0 && mwb_wpr_categ_point > 0)
	    			{
	    				jQuery("#mwb_wpr_loader").show();
						data = {
							action:'mwb_wpr_per_pro_pnt_category',
							mwb_wpr_categ_id:mwb_wpr_categ_id,
							mwb_wpr_categ_point:mwb_wpr_categ_point,
							mwb_nonce:license_ajax_object.mwb_wpr_nonce,

						};
				      	$.ajax({
				  			url: license_ajax_object.ajaxurl, 
				  			type: "POST",  
				  			data: data,
				  			dataType :'json',
				  			success: function(response) 
				  			{	

					  			if(response.result == 'success')
			                    {	
			                    	var category_id = response.category_id;
			                    	var categ_point = response.categ_point;
		                        	jQuery('#mwb_wpr_purchase_points_cat'+category_id).val(categ_point);
		                        	$('.notice.notice-error.is-dismissible').each(function(){
									$(this).remove();
									});
									$('.notice.notice-success.is-dismissible').each(function(){
										$(this).remove();
									});
									
									$('html, body').animate({
								        scrollTop: $(".mwb_rwpr_header").offset().top
								    }, 800);
								    var assing_message = '<div class="notice notice-success is-dismissible"><p><strong>'+license_ajax_object.success_assign+'</strong></p></div>';
								     $(assing_message).insertBefore($('.mwb_wpr_general_wrapper'));
			                        jQuery("#mwb_wpr_loader").hide();
			                    }
				  			}
				  		});	
	    			}
	    			else
	    			{
	    				$('.notice.notice-error.is-dismissible').each(function(){
						$(this).remove();
						});
						$('.notice.notice-success.is-dismissible').each(function(){
							$(this).remove();
						});
						
						$('html, body').animate({
					        scrollTop: $(".mwb_rwpr_header").offset().top
					    }, 800);
					    var valid_point = '<div class="notice notice-error is-dismissible"><p><strong>'+license_ajax_object.error_assign+'</strong></p></div>';
					    $(valid_point).insertBefore($('.mwb_wpr_general_wrapper'));
	    			}
    			}
    			else
    			{	
    				jQuery("#mwb_wpr_loader").show();
					data = {
						action:'mwb_wpr_per_pro_pnt_category',
						mwb_wpr_categ_id:mwb_wpr_categ_id,
						mwb_wpr_categ_point:mwb_wpr_categ_point,
						mwb_nonce:license_ajax_object.mwb_wpr_nonce,
					};
			      	$.ajax({
			  			url: license_ajax_object.ajaxurl, 
			  			type: "POST",  
			  			data: data,
			  			dataType :'json',
			  			success: function(response) 
			  			{	
				  			if(response.result == 'success')
		                    {	var category_id = response.category_id;
		                    	var categ_point = response.categ_point;
	                        	jQuery('#mwb_wpr_purchase_points_cat'+category_id).val(categ_point);
	                        	$('.notice.notice-error.is-dismissible').each(function(){
								$(this).remove();
								});
								$('.notice.notice-success.is-dismissible').each(function(){
									$(this).remove();
								});
								
								$('html, body').animate({
							        scrollTop: $(".mwb_rwpr_header").offset().top
							    }, 800);
							    var remove_message = '<div class="notice notice-success is-dismissible"><p><strong>'+license_ajax_object.success_remove+'</strong></p></div>';
							    $(remove_message).insertBefore($('.mwb_wpr_general_wrapper'));
		                        jQuery("#mwb_wpr_loader").hide();
		                    }
			  			}
			  		});
    			}
		});
		/*Check add more column in the order total settings*/
		$(document).on('click','#mwb_wpr_add_more',function() {
			if($('#mwb_wpr_thankyouorder_enable').prop("checked") == true)
			{
				var response = check_validation_setting();
				if( response == true)
				{
					var tbody_length = $('.mwb_wpr_thankyouorder_tbody > tr').length;
					var new_row = '<tr valign="top"><td class="forminp forminp-text"><label for="mwb_wpr_thankyouorder_minimum"><input type="text" name="mwb_wpr_thankyouorder_minimum[]" class="mwb_wpr_thankyouorder_minimum input-text wc_input_price" required=""></label></td><td class="forminp forminp-text"><label for="mwb_wpr_thankyouorder_maximum"><input type="text" name="mwb_wpr_thankyouorder_maximum[]" class="mwb_wpr_thankyouorder_maximum"></label></td><td class="forminp forminp-text"><label for="mwb_wpr_thankyouorder_current_type"><input type="text" name="mwb_wpr_thankyouorder_current_type[]" class="mwb_wpr_thankyouorder_current_type input-text wc_input_price" required=""></label></td><td class="mwb_wpr_remove_thankyouorder_content forminp forminp-text"><input type="button" value="Remove" class="mwb_wpr_remove_thankyouorder button" ></td></tr>';
					
					if( tbody_length == 2 )
					{
						$( '.mwb_wpr_remove_thankyouorder_content' ).each( function() {
							$(this).show();
						});
					}
					$('.mwb_wpr_thankyouorder_tbody').append(new_row);
				}			
			}
		});
		/*Check validation of the order total settings*/
		var check_validation_setting = function(){
			if($('#mwb_wpr_thankyouorder_enable').prop("checked") == true) {
				var tbody_length = $('.mwb_wpr_thankyouorder_tbody > tr').length;
				var i = 1;
				var min_arr = []; var max_arr = [];
				var empty_warning = false;
				var is_lesser = false;
				var num_valid = false;
				$('.mwb_wpr_thankyouorder_minimum').each(function(){
					min_arr.push($(this).val());		
				});
				var i = 1;

				$('.mwb_wpr_thankyouorder_maximum').each(function(){
					max_arr.push($(this).val());
					i++;			
				});
				var i = 1;
				var thankyouorder_arr = [];
				$('.mwb_wpr_thankyouorder_current_type').each(function(){
					thankyouorder_arr.push($(this).val());
					if(!$(this).val()){				
						$('.mwb_wpr_thankyouorder_tbody > tr:nth-child('+(i+1)+') .mwb_wpr_thankyouorder_current_type').css("border-color", "red");
						empty_warning = true;
					}
					else {
						$('.mwb_wpr_thankyouorder_tbody > tr:nth-child('+(i+1)+') .mwb_wpr_thankyouorder_current_type').css("border-color", "");				
					}
					i++;			
				});
				if(empty_warning) {
					$('.notice.notice-error.is-dismissible').each(function(){
						$(this).remove();
					});
					$('.notice.notice-success.is-dismissible').each(function(){
						$(this).remove();
					});

					$('html, body').animate({
						scrollTop: $(".mwb_rwpr_header").offset().top
					}, 800);
					var empty_message = '<div class="notice notice-error is-dismissible"><p><strong>Some Fields are empty!</strong></p></div>';
					$(empty_message).insertBefore($('.mwb_wpr_general_wrapper'));
					return;
				}
				var minmaxcheck = false;
				if(max_arr.length >0 && min_arr.length > 0) {
	
					if( min_arr.length == max_arr.length && max_arr.length == thankyouorder_arr.length) {

						for ( var j = 0; j < min_arr.length; j++) {

							if(parseInt(min_arr[j]) > parseInt(max_arr[j])) {
								minmaxcheck = true;
								$('.mwb_wpr_thankyouorder_tbody > tr:nth-child('+(j+2)+') .mwb_wpr_thankyouorder_minimum').css("border-color", "red");
								$('.mwb_wpr_thankyouorder_tbody > tr:nth-child('+(j+2)+') .mwb_wpr_thankyouorder_minimum').css("border-color", "red");
							}
							else{
								$('.mwb_wpr_thankyouorder_tbody > tr:nth-child('+(j+2)+') .mwb_wpr_thankyouorder_minimum').css("border-color", "");
								$('.mwb_wpr_thankyouorder_tbody > tr:nth-child('+(j+2)+') .mwb_wpr_thankyouorder_minimum').css("border-color", "");
							}
						}
					}
					else {
						$('.notice.notice-error.is-dismissible').each(function(){
							$(this).remove();
						});
						$('.notice.notice-success.is-dismissible').each(function(){
							$(this).remove();
						});

						$('html, body').animate({
							scrollTop: $(".mwb_rwpr_header").offset().top
						}, 800);
						var empty_message = '<div class="notice notice-error is-dismissible"><p><strong>Some Fields are empty!</strong></p></div>';
						$(empty_message).insertBefore($('.mwb_wpr_general_wrapper'));
						return;
					}
				}
				if(minmaxcheck) {
					$('.notice.notice-error.is-dismissible').each(function(){
						$(this).remove();
					});
					$('.notice.notice-success.is-dismissible').each(function(){
						$(this).remove();
					});

					$('html, body').animate({
						scrollTop: $(".mwb_rwpr_header").offset().top
					}, 800);
					var empty_message = '<div class="notice notice-error is-dismissible"><p><strong>Minimum value cannot have value grater than Maximim value.</strong></p></div>';
					$(empty_message).insertAfter($('.mwb_wpr_general_wrapper'));
					return;
				}
				return true;
			}
		else {
			return false;
		}
	};

	$(document).on('click','.mwb_wpr_remove_thankyouorder',function() {

		if($('#mwb_wpr_thankyouorder_enable').prop("checked") == true)
		{
			$(this).closest('tr').remove();
			var tbody_length = $('.mwb_wpr_thankyouorder_tbody > tr').length;

			if( tbody_length == 2 ){
				$( '.mwb_wpr_remove_thankyouorder_content' ).each( function() {
					$(this).hide();
				});
			}
		}
	});	
    	
	});

})( jQuery );
