(function($) {
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
    $(document).ready(
        function() {

            // Restrict rewards points settings features.
            if ('active' == wps_wpr.is_restrict_status_set) {
                if (wps_wpr.is_restrict_message_enable) {
                    $('.wps_wpr_restrict_user_message').hide();
                    $('.wps_wpr_show_restrict_message').html(wps_wpr.wps_restrict_rewards_msg);
                    $('.wps_wpr_show_restrict_message').css('color', 'red');
                    setTimeout(() => {
                        
                        jQuery(document).find('.wp-block-woocommerce-cart-order-summary-coupon-form-block.wc-block-components-totals-wrapper').append('<div class="wps_wpr_color_for_restriction_msg">' + wps_wpr.wps_restrict_rewards_msg + '</div>');
                        $('.wps_wpr_color_for_restriction_msg').css('color', 'red');
                    }, 2000);
                }

                // get current url and reset url.
                var oldURL = window.location.protocol + "//" + window.location.host + window.location.pathname;
                if (window.history != 'undefined' && window.history.pushState != 'undefined') {
                    window.history.pushState({ path: oldURL }, '', oldURL);
                }
            }

            /*create clipboard */
            var btns = document.querySelectorAll('button');
            var message = '';
            var clipboard = new ClipboardJS(btns);
            /*View Benefits of the Membership Role*/
            $('.wps_wpr_level_benefits').click(
                function() {

                    var wps_wpr_level = $(this).data('id');
                    jQuery('#wps_wpr_popup_wrapper_' + wps_wpr_level).css('display', 'block');

                    jQuery('.wps_wpr_close').click(
                        function() {

                            jQuery('#wps_wpr_popup_wrapper_' + wps_wpr_level).css('display', 'none');
                        }
                    );
                }
            );

            /*Slide toggle on tables*/
            $(document).on(
                'click',
                '.wps_wpr_common_slider',
                function() {

                    jQuery(this).siblings('.wps_wpr_points_view').slideToggle();
                }
            );

            /*Custom Points on Cart Subtotal handling via Ajax*/
            $(document).on(
                'click',
                '#wps_cart_points_apply',
                function() {
                    var user_id = $(this).data('id');
                    var user_total_point = wps_wpr.wps_user_current_points.trim();
                    var order_limit = $(this).data('order-limit');
                    var message = '';
                    var html = '';
                    var wps_cart_points = $('#wps_cart_points').val().trim();

                    $("#wps_wpr_cart_points_notice").html("");
                    $("wps_wpr_cart_points_success").html("");

                    if (wps_cart_points !== 'undefined' && wps_cart_points !== '' && wps_cart_points !== null && wps_cart_points > 0) {
                        if (user_total_point !== null && user_total_point > 0 && parseFloat(user_total_point) >= parseFloat(wps_cart_points)) {

                            block($('.woocommerce-cart-form'));
                            block($('.woocommerce-checkout'));
                            var data = {
                                action: 'wps_wpr_apply_fee_on_cart_subtotal',
                                user_id: user_id,
                                wps_cart_points: wps_cart_points,
                                wps_nonce: wps_wpr.wps_wpr_nonce,
                            };
                            $.ajax({
                                url: wps_wpr.ajaxurl,
                                type: "POST",
                                data: data,
                                dataType: 'json',
                                success: function(response) {
                                    if (response.result == true) {
                                        message = response.message;
                                        $("#wps_wpr_cart_points_success").addClass('woocommerce-message');
                                        $("#wps_wpr_cart_points_success").removeClass('wps_rwpr_settings_display_none_notice');
                                        $("#wps_wpr_cart_points_success").html(message);
                                        $("#wps_wpr_cart_points_success").show();
                                    } else {
                                        message = response.message;
                                        $("#wps_wpr_cart_points_notice").addClass('woocommerce-error');
                                        $("#wps_wpr_cart_points_notice").removeClass('wps_rwpr_settings_display_none_notice');
                                        $("#wps_wpr_cart_points_notice").html(message);
                                        $("#wps_wpr_cart_points_notice").show();
                                    }
                                },
                                complete: function() {
                                    unblock($('.woocommerce-cart-form'));
                                    unblock($('.woocommerce-cart-form'));

                                    if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {
                                        if (!wps_wpr.checkout_page) {
                                            $('html, body').animate({
                                                    scrollTop: jQuery(".woocommerce-cart-form").offset().top
                                                },
                                                800
                                            );
                                        }
                                    }
                                    // Restrict rewards points settings features.
                                    if (wps_wpr.is_restrict_message_enable) {

                                        // set new url from here.
                                        var oldURL = window.location.protocol + "//" + window.location.host + window.location.pathname;
                                        var newUrl = oldURL + "?status=" + "active" + "&nonce=" + wps_wpr.wps_wpr_nonce;
                                        if (window.history != 'undefined' && window.history.pushState != 'undefined') {
                                            window.history.pushState({ path: newUrl }, '', newUrl);
                                        }

                                        if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {
                                            setTimeout(() => {
                                                location.reload();
                                            }, 1500);
                                        } else {
                                            location.reload();
                                        }
                                    } else {

                                        if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {
                                            setTimeout(() => {
                                                location.reload();
                                            }, 1500);
                                        } else {
                                            location.reload();
                                        }
                                    }
                                }
                            });
                        } else if (order_limit !== 'undefined' && order_limit !== '' && order_limit !== null && order_limit > 0) {
                            if ($(".woocommerce-cart-form").offset()) {
                                $(".wps_error").remove();
                                if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {
                                    if (!wps_wpr.checkout_page) {
                                        $('html, body').animate({
                                                scrollTop: $(".woocommerce-cart-form").offset().top
                                            },
                                            800
                                        );
                                    }
                                }
                                var assing_message = '<ul class="woocommerce-error wps_error" role="alert"><li>' + wps_wpr.above_order_limit + '</li></ul>';
                                $(assing_message).insertBefore($('.woocommerce-cart-form'));
                            } else {
                                $(".wps_error").remove();
                                if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {
                                    if (wps_wpr.checkout_page) {
                                        $('html, body').animate({
                                                scrollTop: $(".custom_point_checkout").offset().top
                                            },
                                            800
                                        );
                                    }
                                }
                                var assing_message = '<ul class="woocommerce-error wps_error" role="alert"><li>' + wps_wpr.above_order_limit + '</li></ul>';
                                $(assing_message).insertBefore($('.custom_point_checkout'));
                            }

                        } else {
                            if ($(".woocommerce-cart-form").offset()) {
                                $(".wps_error").remove();
                                if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {
                                    if (!wps_wpr.checkout_page) {
                                        $('html, body').animate({
                                                scrollTop: $(".woocommerce-cart-form").offset().top
                                            },
                                            800
                                        );
                                    }
                                }
                                var assing_message = '<ul class="woocommerce-error wps_error" role="alert"><li>' + wps_wpr.not_suffient + '</li></ul>';
                                $(assing_message).insertBefore($('.woocommerce-cart-form'));
                            } else {
                                $(".wps_error").remove();
                                if ( jQuery('#wps_wpr_button_to_add_points_section').length === 0 ) {
                                    if (!wps_wpr.checkout_page) {
                                        $('html, body').animate({
                                                scrollTop: $(".custom_point_checkout").offset().top
                                            },
                                            800
                                        );
                                    }
                                }
                                var assing_message = '<ul class="woocommerce-error wps_error" role="alert"><li>' + wps_wpr.not_suffient + '</li></ul>';
                                $(assing_message).insertBefore($('.custom_point_checkout'));
                            }
                        }
                    }
                }
            );
            /*Removing Custom Points on Cart Subtotal handling via Ajax*/ // Paypal Issue Change End //
            $(document).on(
                'click',
                '.wps_remove_virtual_coupon',
                function(e) {
                    e.preventDefault();
                    if (!wps_wpr.is_checkout) {
                        block($('.woocommerce-cart-form'));
                    }
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
                            if (!wps_wpr.is_checkout) {
                                unblock($('.woocommerce-cart-form'));
                                location.reload();
                            }
                        }
                    });
                }
            ); // Paypal Issue Change End //
            /*Removing Custom Points on Cart Subtotal handling via Ajax*/
            /*This is code for the loader*/
            var block = function($node) {
                if (!is_blocked($node)) {
                    $node.addClass('processing').block({
                        message: null,
                        overlayCSS: {
                            background: '#fff',
                            opacity: 0.6
                        }
                    });
                }
            };
            var is_blocked = function($node) {
                return $node.is('.processing') || $node.parents('.processing').length;
            };
            var unblock = function($node) {
                $node.removeClass('processing').unblock();
            };
            /*Add confirmation in the myaccount page*/
            $(document).on(
                'click',
                '#wps_wpr_upgrade_level_click',
                function() {
                    var wps_wpr_confirm = confirm(wps_wpr.confirmation_msg);
                    if (wps_wpr_confirm) {
                        $(document).find('#wps_wpr_upgrade_level').click();
                    }
                }
            );
            //custom code
            /*Generate custom coupon*/
            $('.wps_wpr_custom_wallet').click(function() {
                var user_points = $('#wps_custom_wallet_point_num').val().trim();
                $('#wps_wpr_custom_wallet').prop('disabled', true);
                if (user_points) {
                    var html = '';
                    $("#wps_wpr_wallet_notification").html("");
                    user_points = parseFloat(user_points);
                    // Security: user_id is now determined server-side from authenticated session.
                    var data = {
                        action: 'wps_wpr_generate_custom_wallet',
                        points: user_points,
                        wps_nonce: wps_wpr.wps_wpr_nonce,
                    };
                    jQuery("#wps_wpr_loader").show();
                    $.ajax({
                        url: wps_wpr.ajaxurl,
                        type: "POST",
                        data: data,
                        dataType: 'json',
                        success: function(response) {
                            $('#wps_wpr_custom_wallet').prop('disabled', false);
                            jQuery("#wps_wpr_loader").hide();
                            // Handle WordPress standardized JSON response format.
                            if (response.success === true && response.data && response.data.message) {
                                html = '<b style="color:green;">' + response.data.message + '</b>';
                            } else if (response.success === false && response.data && response.data.message) {
                                html = '<b style="color:red;">' + response.data.message + '</b>';
                            } else {
                                html = '<b style="color:red;">An unexpected error occurred.</b>';
                            }
                            $("#wps_wpr_wallet_notification").html(html);
                            // Optionally reload the page after successful conversion to update points display.
                            if (response.success === true) {
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            }
                        },
                        error: function() {
                            $('#wps_wpr_custom_wallet').prop('disabled', false);
                            jQuery("#wps_wpr_loader").hide();
                            $("#wps_wpr_wallet_notification").html('<b style="color:red;">Connection error. Please try again.</b>');
                        }
                    });
                } else {
                    $('#wps_wpr_custom_wallet').prop('disabled', false);
                    $("#wps_wpr_wallet_notification").html('<b style="color:red;">' + wps_wpr.empty_notice + '</b>')
                }
            });

            // Toggle SMS and WhatsApp notifications.
            jQuery(document).on('change', '.wps_wpr_off_sms_notify, .wps_wpr_off_whatsapp_notify', function () {

                const $this     = jQuery(this);
                const isChecked = $this.is(':checked') ? 'yes' : 'no';
                const isSMS     = $this.hasClass('wps_wpr_off_sms_notify');
                const data      = {
                    action                                 : 'stop_sms_whatsapp_notify',
                    nonce                                  : wps_wpr.wps_wpr_nonce,
                    [isSMS ? 'stop_sms' : 'stop_whatsapp'] : isChecked
                };

                wps_wpr_common_func_to_stop_notify(data);
            });

            // AJAX function to handle notification toggle.
            function wps_wpr_common_func_to_stop_notify(data) {
                jQuery.ajax({
                    method  : 'POST',
                    url     : wps_wpr.ajaxurl,
                    data    : data,
                    success : function (response) {
                        const $notice = jQuery('.wps_wpr_notify_notice_wrap');
                        $notice.show().css('color', response.result ? 'red' : 'green').html(response.msg);
                    }
                });
            }

            // Restore redemption state after page reload
            function wps_wpr_restore_redemption_state() {
                // Only run on cart/checkout pages where apply points section exists
                if (jQuery('.wps_wpr_apply_custom_points, .custom_point_checkout').length > 0 ||
                    (wps_wpr.is_checkout || jQuery('body').hasClass('woocommerce-cart'))) {

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
                                // Points are applied - hide the apply form and show applied state
                                jQuery('.wps_wpr_apply_custom_points').hide();
                                jQuery('.custom_point_checkout').hide();

                                // The discount row with remove button should already be in the DOM from server
                                // Just ensure it's visible
                                jQuery('.wps_remove_virtual_coupon').closest('tr').show();
                            }
                        }
                    });
                }
            }

            // Call on page load
            wps_wpr_restore_redemption_state();

        });
})(jQuery);