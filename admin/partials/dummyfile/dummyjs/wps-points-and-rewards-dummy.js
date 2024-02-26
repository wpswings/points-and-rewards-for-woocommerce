(function ($) {
    'use strict';

    jQuery(document).ready(function ($) {

        // Allow user roles.notification_addon points-expiration product-purchase-points
        jQuery(document).find('#wps_wpr_allowed_selected_user_role').select2();
        // Notification addon page.
        jQuery(document).find('#wps_wpr_notification_button_page').select2();

        // disabled save settings button of pro plugin.
        jQuery(document).find('.wps_wpr_disabled_pro_plugin').prop('disabled', true);

        // adding class in pro tabs.
        jQuery('.wps_rwpr_tabs').each(function () {
            if (wps_dummy_obj.api_tabs == jQuery(this).find('a').text()) {
                jQuery(this).addClass('wps_wpr_pro_plugin_settings');
            }
            if (wps_dummy_obj.pur_points_tab == jQuery(this).find('a').text()) {
                jQuery(this).addClass('wps_wpr_pro_plugin_settings');
            }
            if (wps_dummy_obj.expire_tab == jQuery(this).find('a').text()) {
                jQuery(this).addClass('wps_wpr_pro_plugin_settings');
            }
            if (wps_dummy_obj.addon_tabs == jQuery(this).find('a').text()) {
                jQuery(this).addClass('wps_wpr_pro_plugin_settings');
            }
        });

        // adding class for go pro setting.
        jQuery('input.wps_wpr_pro_plugin_settings').parents('.wps_wpr_general_content').addClass('wps_wpr_pro_plugin_settings');
        jQuery('select.wps_wpr_pro_plugin_settings').parents('.wps_wpr_general_content').addClass('wps_wpr_pro_plugin_settings');
        jQuery('textarea.wps_wpr_pro_plugin_settings').parents('.wps_wpr_general_content').addClass('wps_wpr_pro_plugin_settings');

        // add class to show go pro pop-up.
        // var pro_tag_label = jQuery('.wps_rwpr_content_template .wps_wpr_pro_plugin_settings').parents('.wps_wpr_general_row');
        // jQuery(pro_tag_label).add('.wps_wpr_points_table_second_wrappers, .wps_wpr_api_details_main_wrapper, table')
        //     .on('click', function () {
        //         jQuery('.wps-wpr__popup-dummy-for-pro').addClass('dummy-popup-active');
        //         jQuery('.wps-wpr__popup-dummy-for-pro').show();
        //     });

        // show pop-up for pro settings.
        var pro_tag_label = jQuery('.wps_wpr_pro_plugin_settings').parents('.wps_wpr_general_row');
        jQuery(pro_tag_label).on('click', function () {
            jQuery('.wps-wpr__popup-dummy-for-pro').addClass('dummy-popup-active');
            jQuery('.wps-wpr__popup-dummy-for-pro').show();
        });

        // show pop-up for points table.
        jQuery('.wps_wpr_points_table_second_wrappers.wps_wpr_pro_plugin_settings').on('click', function () {
            jQuery('.wps-wpr__popup-dummy-for-pro').addClass('dummy-popup-active');
            jQuery('.wps-wpr__popup-dummy-for-pro').show();
        });

         // show pop-up for API.
        jQuery('.wps_wpr_api_details_main_wrapper.wps_wpr_pro_plugin_settings').on('click', function () {
            jQuery('.wps-wpr__popup-dummy-for-pro').addClass('dummy-popup-active');
            jQuery('.wps-wpr__popup-dummy-for-pro').show();
        });

        // show pop-up for points table.
        jQuery('table.wps_wpr_pro_plugin_settings').on('click', function () {
            jQuery('.wps-wpr__popup-dummy-for-pro').addClass('dummy-popup-active');
            jQuery('.wps-wpr__popup-dummy-for-pro').show();
        });
        
        // closing go pro pop-up. 
        jQuery(document).on('click', '.dummy_popup-close, .dummy_popup-shadow', function () {
            jQuery('.wps-wpr__popup-dummy-for-pro').removeClass('dummy-popup-active');
            jQuery('.wps-wpr__popup-dummy-for-pro').hide();
        });
    });
})(jQuery);