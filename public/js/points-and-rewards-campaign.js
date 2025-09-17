(function ($) {
  "use strict";
  jQuery(document).ready(function ($) {

    // define modal dynamic color.
    jQuery(':root').css('--wps-modal-prim-col', wps_wpr_campaign_obj.wps_modal_color_one );
    jQuery(':root').css('--wps-modal-sec-col', wps_wpr_campaign_obj.wps_modal_color_two );

    if (wps_wpr_campaign_obj.is_pro_plugin_active) {

      // Apply the gradient background and white text if the pro plugin is active
      jQuery('#wps-wpr-campaign-modal .wps-wpr-hlt_co-footer').css('background', 'linear-gradient(45deg, ' + wps_wpr_campaign_obj.wps_modal_color_one + ', ' + wps_wpr_campaign_obj.wps_modal_color_two + ')');
      jQuery('#wps-wpr-campaign-modal .wps-wpr-hlt_co-footer p').css('color', 'white');
    } else {

      // Apply a solid gray background and black text if the pro plugin is inactive
      jQuery('#wps-wpr-campaign-modal .wps-wpr-hlt_co-footer').css('background', '#f4f4f4');
      jQuery('#wps-wpr-campaign-modal .wps-wpr-hlt_co-footer p').css('color', 'black');
    }


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
      ".wps-wpr_campaign-h2",
      function () {
        jQuery(this).children('.wps-wpr-hlw_co-icon').toggleClass('active');
        jQuery(this)
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
    $(document).on("click", ".wps_wpr_submit_quiz_ans", function () {

        const $this       = $(this);
        const index       = $this.data('index');
        const $modal      = $this.closest(".wps-wpr_cam-quiz-btn");
        const quiz_answer = $('input[name="wps_wpr_quiz_option_ans_' + index + '"]:checked').val();
        const $notice     = $modal.next(".wps_wpr_quiz_notice");
        const $loader     = $this.next(".wps_wpr_quiz_loader");
        const $quiz_modal  = $modal.prev('.wps-wpr_campaign-quiz-wrap');

        if (!quiz_answer) {
            $notice.show().css("color", "red").html("Choose an option to claim your reward!");
            return;
        }

        const data = {
            action: "update_quiz_data",
            nonce: wps_wpr_campaign_obj.wps_wpr_nonce,
            quiz_answer: quiz_answer,
            index: index
        };

        $notice.hide().html("");
        $loader.show();
        $this.prop("disabled", true);

        $.ajax({
            type: "POST",
            url: wps_wpr_campaign_obj.ajaxurl,
            data: data,
            success: function(response) {
                $loader.hide();
                const success_error_notice = $notice.show().css("color", response.result ? "green" : "red")
                  .html(response.msg || "Something went wrong. Please try again.");
                  if ( response.result == true ) {

                    $this.hide();
                    $quiz_modal.hide();
                  } else {

                    $this.prop("disabled", false);
                  }
                  setTimeout(() => {
                      success_error_notice.text('');
                    }, 2000);
            },
            error: function() {
                $loader.hide();
                $this.prop("disabled", false);
                $notice.show().css("color", "red").html("An error occurred. Please try again.");
            }
        });
    });

    // quiz scroll up while clicking.
    // $(document).on('click', '.wps-wpr_campaign-h2', function(e) {
    //   $('.wps-wpr-hlw_container').animate({

    //     scrollTop: $(window).scrollTop() + 10 // scroll down by 300 pixels
    //   }, 400); // 400ms animation speed
    // });

    // show win wheel spin to play the game using campaign modal.
    jQuery(document).on('click', '.wps_wpr_show_campaign_win_wheel_modal', function(e){
      e.preventDefault();

      // Show Win Wheel when click on Play link in Campaign Modal.
      jQuery('#wps_wpr_spin_canvas_id').hide();
      $('.wps_wpr_container-close').css('visibility', 'visible');
      jQuery('.wps_wpr_wheel_icon_main').show();
      setTimeout(function() {
          jQuery('.wps_wpr_container').addClass('wps_wpr-container--show');
      }, 200);
    });

    // show canvas when click on win wheel close btn.
    jQuery(document).on('click', '.wps_wpr_container-close', function(){

      jQuery('#wps_wpr_spin_canvas_id').show();
    });

  // Handle normal button/link clicks.
  $(document).on('click', '.wps_wpr_visit_insta_btn', function(e) {

      e.preventDefault();
      let key            = $(this).data('key');
      wps_wpr_ajax_call_for_social_campaign( key, $(this) );
  });

  // assign points when user watch youtube video.
  setTimeout(() => {
    
    (function(window, document){
      var iframe = document.querySelector('iframe.wps_wpr_yt[data-yt-id]');
      if (!iframe) return;

      // ✅ setup YT player (optional: log when video starts playing)
      function initPlayer() {
        try {
          new YT.Player(iframe.id, {
            events: {
              onStateChange: function(e) {
                if (e.data === YT.PlayerState.PLAYING) {

                  wps_wpr_ajax_call_for_social_campaign( iframe.dataset.key, iframe );
                }
              }
            }
          });
        } catch (err) {
          console.warn('YT API not ready for', iframe.id);
        }
      }
      
      if (!document.querySelector('script[src*="youtube.com/iframe_api"]')) {
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        document.head.appendChild(tag);
      }

      var old = window.onYouTubeIframeAPIReady;
      window.onYouTubeIframeAPIReady = function(){
        if (typeof old === 'function') old();
        initPlayer();
      };
    })(window, document);
  }, 1500);

  /**
   * AJAX call for social campaign for assigning the points.
   * @param {*} points 
   * @param {*} heading 
   */
  function wps_wpr_ajax_call_for_social_campaign( key, $_this ) {

    var data = {
          action : "action_social_link_click",
          nonce  : wps_wpr_campaign_obj.wps_wpr_nonce,
          key    : key
      };

      $.ajax({
          type    : "POST",
          url     : wps_wpr_campaign_obj.ajaxurl,
          data    : data,
          success : function (response) {

            $_this.closest('.wps_wpr_cam_insta_visit').prev('.wps-wpr_campaign-h2').addBack().hide();
            // Redirect after logging.
            if ( response ) {

              window.open( response, '_blank' );
            }
          },
          error: function () {
              alert("An error occurred while processing your request.");
          },
      });
  }

  });
})(jQuery);
