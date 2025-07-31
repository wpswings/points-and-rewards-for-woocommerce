(function ($) {
  "use strict";
  jQuery(document).ready(function ($) {

    // define modal dynamic color.
    jQuery(':root').css('--wps-modal-prim-col', wps_wpr_campaign_obj.wps_modal_color_one );
    jQuery(':root').css('--wps-modal-sec-col', wps_wpr_campaign_obj.wps_modal_color_two );

    // disabled earn field for guest users.
    if (!wps_wpr_campaign_obj.is_user_login) {
      setTimeout(function () {
        jQuery(".wps_wpr_guest_user_disable").each(function () {
          jQuery(this)
            .prop("disabled", true)
            .attr("disabled", "disabled")
            .css("opacity", "0.6");
        });
      }, 500);
    }

    setTimeout(() => {
      $("#wps-wpr-campaign-modal").addClass("wps-wpr-campaign-modal--active");
    }, 1000);

    $(".wps-wpr-hlw_close").on("click", function () {
      $("#wps-wpr-campaign-modal").removeClass(
        "wps-wpr-campaign-modal--active"
      );
    });

    jQuery(document).on(
      "mouseenter",
      ".wps-wpr_campaign-h2.wps_wpr_guest_user_disable",
      function () {
        jQuery('.wps-wpr_camp-guest-part').addClass("active");
      }
    );

    jQuery(document).on(
      "mouseleave",
      ".wps-wpr_campaign-h2.wps_wpr_guest_user_disable",
      function () {
        jQuery('.wps-wpr_camp-guest-part').removeClass("active");
      }
    );

    // hide and show referral and earn sections when click on earn buttons.
    jQuery(document).on("click", "#wps_wpr_campaign_earn_btn", function () {
      jQuery("#wps_wpr_campaign_referral_wrap").hide();
      jQuery("#wps_wpr_campaign_earn_wrap").show();
      jQuery(".wps-wpr-hlw_co-buttons").show();
    });

    // hide and show referral and earn sections when click on referral buttons.\
    jQuery(document).on("click", ".wps_wpr_campaign_btn", function () {
      jQuery(".wps_wpr_campaign_btn").removeClass(
        "wps_wpr_campaign_btn--active"
      );
      jQuery(this).addClass("wps_wpr_campaign_btn--active");
    });
    jQuery(document).on("click", "#wps_wpr_campaign_referral_btn", function () {
      jQuery(this).addClass("wps_wpr_campaign_btn--active");
      jQuery("#wps_wpr_campaign_earn_wrap").hide();
      jQuery("#wps_wpr_campaign_referral_wrap").show();
    });

    // accodian and earn points.
    jQuery('.wps-wpr_camp-birth').parent().slideDown();
    jQuery(document).on(
      "click",
      ".wps-wpr_campaign-h2 .wps-wpr_camp-h2-icon",
      function () {
        jQuery(this)
          .parent()
          .next(".wps-wpr_camp-acc-wrap")
          .slideToggle();
      }
    );

    // save login temporary link for re-direction user.
    jQuery(document).on("click", ".wps_wpr_campaign_login", function () {
      var data = {
        action: "action_campaign_login",
        nonce: wps_wpr_campaign_obj.wps_wpr_nonce,
        url: jQuery(this).attr("data-url"),
      };

      jQuery.ajax({
        type: "POST",
        url: wps_wpr_campaign_obj.ajaxurl,
        data: data,
        success: function (response) {
          console.log(response);
        },
        error: function () {
          alert("An error occurred while processing your request.");
        },
      });
    });

    // update birthday date.
    jQuery(document).on(
      "click",
      "#wps_wpr_campaign_save_birthday",
      function () {
        const $btn = jQuery(this);
        const $notice = jQuery(".wps_wpr_birthday_success_notice");
        const $loader = jQuery(".wps_wpr_birthday_loader");
        const birthday = jQuery("#account_bday").val();

        // Validate input
        if (!birthday) {
          $notice
            .text("Please enter your birthday date")
            .css("color", "red")
            .show();
          return false;
        }

        // Prepare for AJAX
        $btn.prop("disabled", true);
        $loader.show();
        $notice.hide().empty();

        jQuery.ajax({
          type: "POST",
          url: wps_wpr_campaign_obj.ajaxurl,
          data: {
            action: "save_birthday_date",
            nonce: wps_wpr_campaign_obj.wps_wpr_nonce,
            birth_date: birthday,
          },
          success: function (response) {
            $btn.prop("disabled", false);
            $loader.hide();

            const color = response.result == true ? "green" : "red";
            $notice.text(response.msg).css("color", color).show();
          },
          error: function () {
            $btn.prop("disabled", false);
            $loader.hide();
            $notice
              .text("Something went wrong. Please try again.")
              .css("color", "red")
              .show();
          },
        });
      }
    );

    // validate quiz answer.
    jQuery(document).on("click", "#wps_wpr_submit_quiz_ans", function () {
      const $notice = jQuery(".wps_wpr_quiz_notice");
      const $loader = jQuery(".wps_wpr_quiz_loader");
      const $submitBtn = jQuery("#wps_wpr_submit_quiz_ans");
      const quiz_answer = jQuery(
        'input[name="wps_wpr_quiz_option_ans"]:checked'
      ).val();

      if (!quiz_answer) {
        $notice
          .show()
          .css("color", "red")
          .html("Choose an option to claim your reward!");
        return;
      }

      // Prepare data for AJAX.
      const data = {
        action: "update_quiz_data",
        nonce: wps_wpr_campaign_obj.wps_wpr_nonce,
        quiz_answer: quiz_answer,
      };

      // Update UI before AJAX.
      $notice.hide().html("");
      $loader.show();
      $submitBtn.prop("disabled", true);

      jQuery.ajax({
        type: "POST",
        url: wps_wpr_campaign_obj.ajaxurl,
        data: data,
        success: function (response) {
          $loader.hide();
          $notice
            .show()
            .css("color", response.result ? "green" : "red")
            .html(response.msg || "Something went wrong. Please try again.");
        },
        error: function () {
          $loader.hide();
          $submitBtn.prop("disabled", false);
          $notice
            .show()
            .css("color", "red")
            .html("An error occurred. Please try again.");
        },
      });
    });

    // quiz scroll up while clicking.
    $(document).on('click', '.wps-wpr_campaign-h2', function(e) {
      $('.wps-wpr-hlw_container').animate({

        scrollTop: $(window).scrollTop() + 100 // scroll down by 300 pixels
      }, 400); // 400ms animation speed
    });
  });
})(jQuery);
