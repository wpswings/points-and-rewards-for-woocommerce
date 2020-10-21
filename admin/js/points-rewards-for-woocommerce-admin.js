/**
 * The admin-specific js functionlity
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin
 */

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

	$( document ).ready(
		function() {
			/*This will hide/show membership*/
			if(jQuery(document).find('#mwb_wpr_membership_setting_enable').prop("checked") == true){
				
				jQuery(document).find('.parent_of_div').closest('tr').show();
			}
			else{
				jQuery(document).find('.parent_of_div').closest('tr').hide();
				mwb_wpr_remove_validation();
			}

			jQuery(document).find('.mwb_wpr_membership_select_all_category_common').click(function(e){
				e.preventDefault();
				var count = $( this ).data( 'id' );
				 jQuery(document).find("#mwb_wpr_membership_category_list_"+count+" option").prop("selected","selected");
		         jQuery(document).find("#mwb_wpr_membership_category_list_"+count).trigger("change");
			});

			jQuery(document).find('.mwb_wpr_membership_select_none_category_common').click(function(e){
				e.preventDefault();
				var count = $( this ).data( 'id' );
				 jQuery(document).find("#mwb_wpr_membership_category_list_"+count+" option").removeAttr( 'selected' );
		         jQuery(document).find("#mwb_wpr_membership_category_list_"+count).trigger("change");
			});
			
			$( document ).find( '.notice-image img' ).css( "max-width", "50px" );
			$( document ).find( '.notice-content' ).css( "margin-left", "15px" );
			$( document ).find( '.notice-container' ).css( { "padding-top": "5px", "padding-bottom": "5px", "display": "flex", "justify-content": "left", "align-items": "center" } );

			$( document ).on(
				'click',
				'.mwb_wpr_common_slider',
				function(){
					$( this ).next( '.mwb_wpr_points_view' ).slideToggle( 'slow' );
					$( this ).toggleClass( 'active' );
				}
			);
			$( document ).find( '#mwb_wpr_restrictions_for_purchasing_cat' ).select2();
			
			/* Update user Points in the points Table*/
			$( '.mwb_points_update' ).click(
				function(){
					var user_id = $( this ).data( 'id' );
					var user_points = $( document ).find( "#add_sub_points" + user_id ).val();
					var sign = $( document ).find( "#mwb_sign" + user_id ).val();
					var reason = $( document ).find( "#mwb_remark" + user_id ).val();
					user_points = Number( user_points );
					if (user_points > 0 && user_points === parseInt( user_points, 10 )) {
						if ( reason != '' ) {
							jQuery( "#mwb_wpr_loader" ).show();
							var data = {
								action:'mwb_wpr_points_update',
								points:user_points,
								user_id:user_id,
								sign:sign,
								reason:reason,
								mwb_nonce:mwb_wpr_object.mwb_wpr_nonce,
							};
							$.ajax(
								{
									url: mwb_wpr_object.ajaxurl,
									type: "POST",
									data: data,
									success: function(response)
								{
										jQuery( "#mwb_wpr_loader" ).hide();
										$( 'html, body' ).animate(
											{
												scrollTop: $( ".mwb_rwpr_header" ).offset().top
											},
											800
										);
										var assing_message = '<div class="notice notice-success is-dismissible"><p><strong>' + mwb_wpr_object.success_update + '</strong></p></div>';
										$( assing_message ).insertAfter( $( '.mwb_rwpr_header' ) );
										setTimeout( function(){ location.reload(); }, 1000 );
									}
								}
							);
						} else {
							alert( mwb_wpr_object.reason );
						}
					} else {
						alert( mwb_wpr_object.validpoint );
					}
				}
			);

			$( document ).on(
				'click',
				'.mwb_wpr_email_wrapper_text',
				function(){
					$( this ).siblings( '.mwb_wpr_email_wrapper_content' ).slideToggle();
				}
			);


			
			$(document).on('change','#mwb_wpr_membership_setting_enable',function() {
				if($(this).prop("checked") == true) {			
					jQuery(document).find('.parent_of_div').closest('tr').show();
					mwb_wpr_add_validation();
				}
				else{
					jQuery(document).find('.parent_of_div').closest('tr').hide();
					mwb_wpr_remove_validation();
				}
			});	
			/*This will add new setting*/
			$( document ).on(
				"change",
				".mwb_wpr_common_class_categ",
				function(){
					var count = $( this ).data( 'id' );
					var mwb_wpr_categ_list = $( '#mwb_wpr_membership_category_list_' + count ).val();
					jQuery( "#mwb_wpr_loader" ).show();
					var data = {
						action:'mwb_wpr_select_category',
						mwb_wpr_categ_list:mwb_wpr_categ_list,
						mwb_nonce:mwb_wpr_object.mwb_wpr_nonce,
					};
					$.ajax(
						{
							url: mwb_wpr_object.ajaxurl,
							type: "POST",
							data: data,
							dataType :'json',
							success: function(response)
						{

								if (response.result == 'success') {
									var product = response.data;
									var option = '';
									for (var key in product) {
										option += '<option value="' + key + '">' + product[key] + '</option>';
									}
									jQuery( "#mwb_wpr_membership_product_list_" + count ).html( option );
									jQuery( "#mwb_wpr_membership_product_list_" + count ).select2();
									jQuery( "#mwb_wpr_loader" ).hide();
								}
							}
						}
					);

				}
			);
			var count = $( '.mwb_wpr_repeat:last' ).data( 'id' );
			for (var i = 0; i <= count; i++) {
				 $( document ).find( '#mwb_wpr_membership_category_list_' + i ).select2();
				 $( document ).find( '#mwb_wpr_membership_product_list_' + i ).select2();
			}

			/*Add a label for purchasing the paid plan*/
			if (mwb_wpr_object.check_pro_activate) {
				jQuery( document ).on(
					'click',
					'.mwb_wpr_repeat_button',
					function(){
						var html = '';
						$( document ).find( '.mwb_wpr_object_purchase' ).remove();
						html = '<div class="mwb_wpr_object_purchase"><p>' + mwb_wpr_object.pro_text + ' <a target="_blanck" href="' + mwb_wpr_object.pro_link + '">' + mwb_wpr_object.pro_link_text + '</a></p></div>';
						$( '.parent_of_div' ).append( html );
					}
				);
			}

			/*Add a label for purchasing the paid plan*/
			if (mwb_wpr_object.check_pro_activate) {
				$( document ).on(
					'click',
					'#mwb_wpr_add_more',
					function() {
						var html = '';
						$( document ).find( '.mwb_wpr_object_purchase' ).remove();
						html = '<div class="mwb_wpr_object_purchase"><p>' + mwb_wpr_object.pro_text + ' <a target="_blanck" href="' + mwb_wpr_object.pro_link + '">' + mwb_wpr_object.pro_link_text + '</a></p></div>';
						$( html ).insertAfter( '.wp-list-table' );
					}
				);
			}
			jQuery( document ).on(
				'click',
				'.mwb_wpr_remove_button',
				function(){
					var curr_div = $( this ).attr( 'id' );
					if (curr_div == 0) {
						$( document ).find( '.mwb_wpr_repeat_button' ).hide();
						$( '#mwb_wpr_membership_setting_enable' ).attr( 'checked',false );
					}
					$( '#mwb_wpr_parent_repeatable_' + curr_div ).remove();

				}
			);
			/*support popup form */			
			$( document ).on(
			'click',
			'#dismiss_notice',
			function(e){
				e.preventDefault();
				var data = {
					action:'mwb_wpr_dismiss_notice',
					mwb_nonce:mwb_wpr_object.mwb_wpr_nonce,
				};
				$.ajax(
					{
						url: mwb_wpr_object.ajaxurl,
						type: "POST",
						data: data,
						success: function(response)
					{
							window.location.reload();
						}
					}
				);
			}
		);
		}
	);

var mwb_wpr_remove_validation = function(){
	jQuery(document).find('.mwb_wpr_repeat').each(function(index,element){
		jQuery(document).find('#mwb_wpr_membership_level_name_'+index).attr( 'required',false );
		jQuery(document).find('#mwb_wpr_membership_level_value_'+index).attr( 'required',false );
		jQuery(document).find('#mwb_wpr_membership_expiration_days_'+index).attr( 'required',false );
		jQuery(document).find('#mwb_wpr_membership_expiration_'+index).attr( 'required',false );
		jQuery(document).find('#mwb_wpr_membership_category_list_'+index).attr( 'required',false );
		jQuery(document).find('#mwb_wpr_membership_discount_'+index).attr( 'required',false );
	});
};
var mwb_wpr_add_validation = function(){
	jQuery(document).find('.mwb_wpr_repeat').each(function(index,element){
		jQuery(document).find('#mwb_wpr_membership_level_name_'+index).attr( 'required',true );
		jQuery(document).find('#mwb_wpr_membership_level_value_'+index).attr( 'required',true );
		jQuery(document).find('#mwb_wpr_membership_expiration_days_'+index).attr( 'required',true );
		jQuery(document).find('#mwb_wpr_membership_expiration_'+index).attr( 'required',true );
		jQuery(document).find('#mwb_wpr_membership_category_list_'+index).attr( 'required',true );
		jQuery(document).find('#mwb_wpr_membership_discount_'+index).attr( 'required',true );
	});
};

})( jQuery );
/*======================================
	=            Sticky-Sidebar            =
	======================================*/
setTimeout(
	function()
	  {
		if ( jQuery( window ).width() >= 900 ) {
			jQuery( '.mwb_rwpr_navigator_template' ).stickySidebar(
				{
					topSpacing: 60,
					bottomSpacing: 60
					}
			);
		}
	},
	500
);

/*=====  End of Sticky-Sidebar  ======*/
jQuery( document ).ready(
	function(){
			jQuery( ".dashicons.dashicons-menu" ).click(
				function(){
					jQuery( ".mwb_rwpr_navigator_template" ).toggleClass( "open-btn" );
				}
			);
	}
);
