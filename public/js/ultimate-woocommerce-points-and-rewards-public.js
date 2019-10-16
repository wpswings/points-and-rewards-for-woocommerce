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
	 $(document).ready(function(){
	 	$('.mwb_wpr_generate_coupon').click(function(){
	 		var user_id = $(this).data('id');
	 		var message = ''; var html = '';
	 		$("#mwb_wpr_points_notification").html("");
	 		jQuery("#mwb_wpr_loader").show();
	 		var data = {
	 			action:'mwb_wpr_generate_original_coupon',
	 			user_id:user_id,
	 			mwb_nonce:mwb_wpr.mwb_wpr_nonce,				
	 		};
	 		
	 		$.ajax({
	 			url: mwb_wpr.ajaxurl, 
	 			type: "POST",  
	 			data: data,
	 			dataType :'json',
	 			success: function(response) 
	 			{
	 				
	 				jQuery("#mwb_wpr_loader").hide();
	 				if(response.result == true)
	 				{	
	 					$('.points_log').css('display','block');
	 					$('.points_log').html(response.html);
	 					$('#mwb_wpr_points_only').html(response.points);
	 					message = response.message;
	 					html = '<b style="color:green;">'+message+'</b>';
	 					$('.mwb_current_points').html(response.points);
	 					var minimum_points=mwb_wpr.minimum_points;
	 					if(response.points < minimum_points)
	 					{
	 						$('#points_form').hide();
	 					}
	 				}
	 				else
	 				{
	 					message = response.message;
	 					html = '<b style="color:red;">'+message+'</b>';
	 				}
	 				$("#mwb_wpr_points_notification").html(html);
	 			}
	 		});
	 		
	 	});
	 	/*Generate custom coupon*/
	 	$('.mwb_wpr_custom_coupon').click(function(){
			var user_id = $(this).data('id');
			var user_points = $('#mwb_custom_point_num').val();
			var message = ''; var html = '';
			$("#mwb_wpr_points_notification").html("");
			user_points = parseFloat(user_points);
			if(user_points > 0 && $.isNumeric(user_points))
			{
				jQuery("#mwb_wpr_loader").show();
				var data = {
					action:'mwb_wpr_generate_custom_coupon',
					points:user_points,
					user_id:user_id,
					mwb_nonce:mwb_wpr.mwb_wpr_nonce,					
				};
		      	$.ajax({
		  			url: mwb_wpr.ajaxurl, 
		  			type: "POST",  
		  			data: data,
		  			dataType :'json',
		  			success: function(response) 
		  			{
		  				jQuery("#mwb_wpr_loader").hide();
		  				if(response.result == true)
						{	
							$('.points_log').css('display','block');
							$('.points_log').html(response.html);
							$('#mwb_wpr_points_only').html(response.points);
							message = response.message;
							html = '<b style="color:green;">'+message+'</b>';
							$('.mwb_current_points').html(response.points);
							var minimum_points=mwb_wpr.minimum_points;
							if(response.points < minimum_points)
							{
								$('#points_form').html(mwb_wpr.minimum_points_text);
							}
						}
						else
						{
							message = response.message;
							html = '<b style="color:red;">'+message+'</b>';
						}
						$("#mwb_wpr_points_notification").html(html);
		  			}
		  		});
			}
			else
			{
				html = '<b style="color:red;">'+mwb_wpr.message+'</b>';
				$("#mwb_wpr_points_notification").html(html);
			}
		});
		/*Send points to another one*/
		$('#mwb_wpr_share_point').click(function(){
			var user_id = $(this).data('id');
			var shared_point = $('#mwb_wpr_enter_point').val();
			var email_id = $('#mwb_wpr_enter_email').val();
			$("#mwb_wpr_shared_points_notification").html("");
			if(shared_point > 0 )
			{
				jQuery("#mwb_wpr_loader").show();
				var data = {
					action:'mwb_wpr_sharing_point_to_other',
					shared_point:shared_point,
					user_id:user_id,
					email_id:email_id,
					mwb_nonce:mwb_wpr.mwb_wpr_nonce,			
				};
				$.ajax({
					url: mwb_wpr.ajaxurl, 
					type: "POST",  
					data: data,
					dataType :'json',
					success: function(response) 
					{
						jQuery("#mwb_wpr_loader").hide();
						if(response !== null && response.result == true)
						{	
							$('#mwb_wpr_points_only').html(response.available_points);
							message = response.message;
							html = '<b style="color:green;">'+message+'</b>';
						}
						else
						{
							message = response.message;
							html = '<b style="color:red;">'+message+'</b>';
						}
						$("#mwb_wpr_shared_points_notification").html(html);
					}
				});
			}
			else
			{
				html = '<b style="color:red;">'+mwb_wpr.message+'</b>';
				$("#mwb_wpr_shared_points_notification").html(html);
			}

		});
	  	/*Make Readonly if selected in backend*/
	  	$(document).on('change','#mwb_wpr_pro_cost_to_points',function(){
	  		console.log(mwb_wpr_pro.make_readonly);
	  		if(mwb_wpr_pro.make_readonly == 1) {
	  			$('#mwb_wpr_some_custom_points').attr('readonly',true);
	  		}
	  		if($(this).prop("checked") == true) {
	  			var mwb_wpr_some_custom_points = $('#mwb_wpr_some_custom_points').val();
	  			$('.mwb_wpr_enter_some_points').css("display","block");
	  		}
	  		else{
	  			$('.mwb_wpr_enter_some_points').css("display","none");
	  		}
	  	});
	  	var pre_variation_id = '';
	  	$(document).on('change','.variation_id',function(e) {
	   		e.preventDefault();
			var variation_id = $(this).val();
			if( variation_id != null && variation_id > 0 && pre_variation_id != variation_id){
				pre_variation_id = variation_id;
				block($('.summary.entry-summary'));
				var data = {
					action:'mwb_wpr_append_variable_point',
					variation_id:pre_variation_id,
					mwb_nonce:mwb_wpr.mwb_wpr_nonce,			
				};
				$.ajax({
		  			url: mwb_wpr.ajaxurl, 
		  			type: "POST",  
		  			data: data,
		  			dataType :'json',
		  			success: function(response) 
		  			{

		  				if(response.result == true && response.variable_points > 0) {	
		  					$('.mwb_wpr_variable_points').html(response.variable_points);
		  					$('.mwb_wpr_product_point').css('display','block');
		  				}
						if(response.result_price == "html"  && response.variable_price_html != null){
							$('.woocommerce-variation-price').html(response.variable_price_html);
						}
						if(response.result_point == "product_purchased_using_point" && response.variable_points_cal_html !=null){

							$('.mwb_wpr_variable_pro_pur_using_point').html(response.variable_points_cal_html);
							$('.mwb_wpr_variable_pro_pur_using_point').css('display','block');
						}
						//MWB CUSTOM CODE
						if(response.purchase_pro_pnts_only == "purchased_pro_points" && response.price_html !=null){
							$('.woocommerce-variation-price').html(response.price_html+" Points");
							//$('.woocommerce-Price-amount').html(response.price_html+" Points");
						}
						//MWB CUSTOM CODE
						
		  			},
		  			complete: function() 
					{
						unblock( $( '.summary.entry-summary' ) );
					}
		  		});
			}
			else if(variation_id != null && variation_id > 0){
				block($('.summary.entry-summary'));
				var data = {
					action:'mwb_pro_purchase_points_only',
					variation_id:variation_id,
					mwb_nonce:mwb_wpr.mwb_wpr_nonce,			
				};
				$.ajax({
		  			url: mwb_wpr.ajaxurl, 
		  			type: "POST",  
		  			data: data,
		  			dataType :'json',
		  			success: function(response) 
		  			{
						if(response.purchase_pro_pnts_only == "purchased_pro_points" && response.price_html !=null){
							$('.woocommerce-variation-price').html(response.price_html+" Points");
							//$('.woocommerce-Price-amount').html(response.price_html+" Points");
						}
						//MWB CUSTOM CODE
						
		  			},
		  			complete: function() 
					{
						unblock( $( '.summary.entry-summary' ) );
					}
		  		});
			}
		});
		if($('input[id="mwb_wpr_pro_cost_to_points"]').prop("checked") == true) {
			$('.mwb_wpr_enter_some_points').css("display","block");
		}
		else {
			$('.mwb_wpr_enter_some_points').css("display","none");
		}
		$(document).on('change','#mwb_wgm_price',function(){
			var mwb_gift_price = $(this).val();
			if(mwb_gift_price != null){
				$('.mwb_wpr_when_variable_pro').html(mwb_gift_price);
				$('#mwb_wpr_some_custom_points').val(mwb_gift_price);
				$('#mwb_wpr_pro_cost_to_points').val(mwb_gift_price);
			}
		});
		var block = function( $node ) {
			if ( ! is_blocked( $node ) ) {
				$node.addClass( 'processing' ).block( {
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				} );
			}
		};
		var is_blocked = function( $node ) {
			return $node.is( '.processing' ) || $node.parents( '.processing' ).length;
		};
		var unblock = function( $node ) {
			$node.removeClass( 'processing' ).unblock();
		};
	 	
	 });
})( jQuery );
