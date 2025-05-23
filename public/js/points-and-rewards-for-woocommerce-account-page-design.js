(function ($) {
    'use strict';

    jQuery(document).ready(function ($) {

        // change points tab layout color.
        var root = $(':root');
        root.css('--wps-wpr-primary', points_tab_layout_obj.points_tab_color );

        // hide user membership current level name
        setTimeout(function (e) {
                jQuery('.wps_wpr_membership_list_main_wrap .wps_wpr_upgrade_level').hide();
        }, 5000);

        // js for points tab template three.
        if ( 'temp_three' == points_tab_layout_obj.design_temp_type ) {

            // PAR jQuery Start
            $(".wps-p_mash-item").on("click", function () {
                // Remove active class from all nav and content items
                $(".wps-p_mash-item").removeClass("wps-p_mash-i--active");
                $(".wps-p_masd-item").removeClass("wps-p_masd-i--active");
        
                // Add active class to the clicked nav item
                $(this).addClass("wps-p_mash-i--active");
        
                // Get the suffix from the clicked item's class (e.g., "membership", "coupon", etc.)
                var classes = $(this).attr("class").split(" ");
                var suffix = null;
                classes.forEach(function (c) {
                if (c.startsWith("wps-p_mash-i-") && !c.endsWith("--active")) {
                    suffix = c.replace("wps-p_mash-i-", "");
                }
                });
        
                // Activate the corresponding content item
                if (suffix) {
                $(".wps-p_masd-i-" + suffix).addClass("wps-p_masd-i--active");
                }
            });
        
            $(".wps-p_masmh-item.wps-p_mash-i-menu,.wps-par_mas-head")
                .on("mouseenter", function () {
                $(".wps-par_mas-head").addClass("wps-par_mas-head--active");
                })
                .on("mouseleave", function () {
                $(".wps-par_mas-head").removeClass("wps-par_mas-head--active");
            });
        } else {

            // js for points tab template two.
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

            jQuery('.wps_wpr_badge_way_points_main_wrap').parent().css({'padding':'0 10px','border':'none','overflow':'unset'});
        }
    });
})(jQuery);