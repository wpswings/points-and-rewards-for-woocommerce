(function ($) {
    'use strict';

    jQuery(document).ready(function ($) {

        // change points tab layout color.
        var root = $(':root');
        root.css('--wps-wpr-primary', points_tab_layout_obj.points_tab_color );

        jQuery(document).on('click', '.wps_wpr_mail_button', function (e) {

            jQuery('.wps-wpr__email-input').css('display', 'flex');
        });

        jQuery(document).on('click', function (e) {
            if (!jQuery(e.target).closest('.wps_wpr_mail_button, .wps-wpr__email-input').length) {

                jQuery('.wps-wpr__email-input').hide();
                jQuery('#wps_wpr_enter_emaill').hide();
                jQuery('#wps_wpr_point').hide();
            }
        });

        setTimeout(function (e) {
            jQuery('.wps_wpr_membership_list_main_wrap .wps_wpr_upgrade_level').hide();
        }, 5000);

        jQuery('.wps_wpr_badge_way_points_main_wrap').parent().css({'padding':'0 10px','border':'none','overflow':'unset'});
    });
})(jQuery);