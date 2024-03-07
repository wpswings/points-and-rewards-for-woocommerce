(function($) {
    'use strict';
    
    jQuery(document).ready(function($){
        
        // +++++ Gamification JS +++++++++

        // This function is used get segments color.
        function wps_wpr_get_segment_color() {
            var segments = [];
            wps_wpr.wps_wpr_game_setting.forEach(function(element) {

                var segment       = {};
                segment.fillStyle = element;
                segments.push(segment);
            });
            return segments;
        }

        // Creaing canvas icon on user end.
        var canvasIcon  = document.getElementById('wps_wpr_spin_canvas_id');
        var contextIcon = canvasIcon.getContext('2d');
        var iconWheel   = new Winwheel({

            'canvasId'    : 'wps_wpr_spin_canvas_id',
            'numSegments' : wps_wpr.wps_wpr_game_setting.length, // Specify number of segments.
            'outerRadius' : 70, // Set outer radius so wheel fits inside the background.           
            'drawMode'    : 'code', // drawMode must be set to image.
            'drawText'    : true,
            'strokeStyle' : '#000', // Need to set this true if want code-drawn text on image wheels.
            'lineWidth'   : .3,
            'segments'    : wps_wpr_get_segment_color(),
            'animation'   : {
                'type'      : 'spinOngoing',
                'duration'  : 7,
                'easing'    : 'Linear.easeNone',
                'direction' : 'anti-clockwise',
                'repeat'    : -1,
                'yoyo'      : false
            },

            'pins': // Specify pin parameters.
            {
                'number': wps_wpr.wps_wpr_game_setting.length,
                'outerRadius': 3,
                'margin': 0,
                'fillStyle': '#fff ',
                'strokeStyle': '#000 ',
                'borderColor': '#000',
                'shadColor': 'rgba(0,0,0,0.2)',
                'borderWidth': 2,
                'BorderRadius': 68,
                'centerColor': '#fff',
                'centerRadius': 10,
                'shadowOffsetX': 2,
                'shadowOffsetY': 2,

            }
        });
        iconWheel.startAnimation();

        // Main Win wheel for full screen start
        // No. of elements and adjust in wheel start

        var inp_val_field       = jQuery('.wps_wpr_wheel .wps_wpr_number');
        var inp_val_field_total = jQuery('.wps_wpr_wheel .wps_wpr_number').length;
        var i                   = 1;
        var clipPathValues = {
            12: 'polygon(0 0, 45% 0, 100% 100%, 0 45%)',
            11: 'polygon(0 0, 46% 0, 100% 100%, 0 46%)',
            10: 'polygon(0 0, 50% 0, 100% 100%, 0 50%)',
            9: 'polygon(0 0, 55% 0, 100% 100%, 0 55%)',
            8: 'polygon(0 0, 59% 0, 100% 100%, 0 59%)',
            7: 'polygon(0 0, 66% 0, 100% 100%, 0 66%)',
            6: 'polygon(0 0, 74% 0, 100% 100%, 0 74%)',
        };

        var clipPathValue = clipPathValues[inp_val_field_total];
        inp_val_field.css('clip-path', clipPathValue);

        inp_val_field.each(function(ea) {
            $(this).css('--fs', i++);
            $(this).css('transform', 'rotate(calc(' + 360 / inp_val_field_total + 'deg * var(--fs)))');
        })

        // No. of elements and adjust in wheel ends
        function all_reset_wheel() {
            jQuery('.wps_wpr_wheel_icon_main').hide();
            jQuery('.wps_wpr_container').removeClass('wps_wpr-container--show');
            $('.wps_wpr_wheel').css('animation', '');
        }

        function all_reset_popup() {
            $('.wps_wpr_container-popup').hide();
            $('.wps_wpr_container-popup-gif').hide();
            $('.wps_wpr_container-popup-in').removeClass('wps_wpr_container-popup-in--show');
        }

        function pause_audio_all() {
            $('.wps_wpr_audio-spin').trigger('pause');
            $('.wps_wpr_audio-spin')[0].currentTime = 0;

            $('.wps_wpr_audio-cheer').trigger('pause');
            $('.wps_wpr_audio-cheer')[0].currentTime = 0;
        }

        // Show Win Wheel when click on Canvas.
        jQuery(document).on('click', '.wps_wpr_wheel_icon', function() {

            jQuery(this).hide();
            $('.wps_wpr_container-close').css('visibility', 'visible');

            // if user is login than open win wheel else show notice for login.
            if ( wps_wpr.wps_is_user_login ) {

                jQuery('.wps_wpr_wheel_icon_main').show();
                setTimeout(function() {
                    jQuery('.wps_wpr_container').addClass('wps_wpr-container--show');
                }, 200);
            } else {

                jQuery('.wps_wpr_guest_user_main_wrapper').show();
            }
        })

        // close guest user pop-up.
        jQuery(document).on('click', '.wps_wpr_guest_close_btn', function(){

            jQuery('.wps_wpr_guest_user_main_wrapper').hide();
            jQuery('.wps_wpr_wheel_icon').show();
        });

        let wheel        = jQuery('.wps_wpr_wheel');
        let spinBtn      = jQuery('#wps_wpr_spinWheelButton');
        let value;
        let minRotations = 5;
        let maxRotations = 6;

        let randomRotations   = Math.random() * (maxRotations - minRotations) + minRotations;
        let rotationInDegrees = Math.ceil(randomRotations * 360);

        //  spin on basis of no. of items start
        var items_to_select_from;
        if (inp_val_field_total == 6) {
            items_to_select_from = {
                1: 0,
                2: -20,
                3: -38,
                4: -54,
                5: -70,
                6: -86,
            }
        } else if (inp_val_field_total == 7) {
            items_to_select_from = {
                1: 0,
                2: -15,
                3: -30,
                4: -45,
                5: -58,
                6: -72,
                7: -87,
            }
        } else if (inp_val_field_total == 8) {
            items_to_select_from = {
                1: 0,
                2: -12,
                3: -24,
                4: -37,
                5: -49,
                6: -62,
                7: -74,
                8: -87,
            }
        } else if (inp_val_field_total == 9) {
            items_to_select_from = {
                1: 2,
                2: -9,
                3: -20,
                4: -31,
                5: -42,
                6: -53,
                7: -64,
                8: -75,
                9: -87,
            }
        } else if (inp_val_field_total == 10) {
            items_to_select_from = {
                1: 3,
                2: -7,
                3: -17,
                4: -27,
                5: -37,
                6: -47,
                7: -57,
                8: -67,
                9: -77,
                10: -87,
            }
        } else if (inp_val_field_total == 11) {
            items_to_select_from = {
                1: 4,
                2: -5,
                3: -15,
                4: -24,
                5: -33,
                6: -42,
                7: -51,
                8: -60,
                9: -69,
                10: -78,
                11: -87,
            }
        } else if (inp_val_field_total == 12) {
            items_to_select_from = {
                1: 5,
                2: -4,
                3: -12,
                4: -20,
                5: -28,
                6: -36,
                7: -45,
                8: -53,
                9: -61,
                10: -70,
                11: -79,
                12: -87,
            }
        }

        let rotationSpeed = 8;
        let rotationDeg   = 360;
        let totalRotation = rotationDeg * rotationSpeed;

        // Rotate spinner when click on Win Wheel.
        jQuery(document).on('click', '#wps_wpr_spinWheelButton', function() {

            var rotationValues = [];
            // get segment count and stop segments accordingly.
            wps_wpr.wps_wpr_select_spin_stop.forEach(function(element) {

                rotationValues.push(items_to_select_from[element]);
            });

            // if segemnts ar not set to stop win wheel, set default.
            if ( rotationValues.length == 0 ) {

                rotationValues = [items_to_select_from[5],items_to_select_from[4],items_to_select_from[3],items_to_select_from[2]];
            }

            let randomIndex    = Math.floor(Math.random() * rotationValues.length);
            let randomValue    = rotationValues[randomIndex];
            let time           = totalRotation + (randomValue * 3.6);

            $(this).css('pointer-events', 'none');
            wheel.animate({ deg: time }, {
                duration: 0,
                easing: 'swing',
                step: function(now) {
                    $(this).css('transform', `rotate(${now}deg)`);
                }
            });
        });

        // Click on cross button to hide win wheel.
        jQuery(document).on('click', '.wps_wpr_container-close', function() {

            // Show Canvas when win wheel close using cross button.
            jQuery('.wps_wpr_wheel_icon').show();

            $('#wps_wpr_spinWheelButton').css('pointer-events', 'auto');
            pause_audio_all();
            all_reset_popup();
            all_reset_wheel();
        })

        // confitti effect
        let wps_canvas       = document.getElementById("wps_wpr-canvas");
        let context          = wps_canvas.getContext("2d");
        let width            = window.innerWidth;
        let height           = window.innerHeight;
        let particles        = [];
        let particleSettings = {
            count: 200,
            gravity: 0.5,
            wave: 0,
        };

        window.requestAnimationFrame =
            window.requestAnimationFrame ||
            window.webkitRequestAnimationFrame ||
            window.mozRequesAnimationFrame ||
            window.oRequestAnimationFrame ||
            window.msRequestAnimationFrame ||
            function(callback) {
                window.setTimeout(callback, 1000 / 60);
            };
        
        //random number between range
        function randomNumberGenerator(min, max) {
            return Math.random() * (max - min) + min;
        }

        //Creates Confetti (particles)
        function createConfetti() {
            while (particles.length < particleSettings.count) {
                let particle = new Particle();
                //Random colors
                particle.color = `rgb( ${randomNumberGenerator(
                0,
                255
            )}, ${randomNumberGenerator(0, 255)}, ${randomNumberGenerator(0, 255)}`;
                //Store particles
                particles.push(particle);
            }
        }

        //Starts the confetti.
        const startConfetti = () => {
            context.clearRect(0, 0, window.innerWidth, window.innerHeight);
            createConfetti();
            for (let i in particles) {
                //Movement and shaking efffect
                particleSettings.wave += 0.4;
                particles[i].tiltAngle += randomNumberGenerator(0.01, 2);
                // controls particle speed
                particles[i].y +=
                    (Math.sin(particleSettings.wave) +
                        particles[i].area +
                        particleSettings.gravity) *
                    0.4;
                particles[i].tilt = Math.cos(particles[i].tiltAngle) * 0.3;
                //Draw the particle on screen
                particles[i].draw();
                //if particle has crosses the screen height
                if (particles[i].y > height) {
                    particles[i] = new Particle();
                    //Random colors
                    particles[i].color = `rgb( ${randomNumberGenerator(
                0,
                255
                )}, ${randomNumberGenerator(0, 255)}, ${randomNumberGenerator(0, 255)}`;
                }
            }
            let animationTimer = requestAnimationFrame(startConfetti);
        };

        // Animation.
        function Particle() {
            this.x = Math.random() * width;
            this.y = Math.random() * height - height;
            // Size of particle.
            this.area = randomNumberGenerator(8, 12);
            this.tilt = randomNumberGenerator(-4, 4);
            this.tiltAngle = 0;
        }

        //Mathod associated with particle.
        Particle.prototype = {
            draw: function() {
                context.beginPath();
                context.lineWidth = this.area;
                context.strokeStyle = this.color;
                this.x = this.x + this.tilt;
                context.moveTo(this.x + this.area / 2, this.y);
                context.lineTo(this.x, this.y + this.tilt + this.area / 2);
                context.stroke();
            },
        };

        window.onload = () => {
            wps_canvas.width = width;
            wps_canvas.height = height;
            window.requestAnimationFrame(startConfetti);
        };

        // Rotate Win Wheel when click on Spinner.
        jQuery(document).on('click', '#wps_wpr_spinWheelButton', function(e) {
            $('.wps_wpr_audio-spin').trigger('play');
            $('.wps_wpr_container-close').css('visibility', 'hidden');
            setTimeout(function() {
                // Get Value start
                $(document).ready(function() {
                    var targetElement = $("#wps_wpr_spinWheelButton");
                    var elements = $(".wps_wpr_number input");

                    function calculateVerticalPosition(element) {
                        var elementPosition = element.offset();
                        var elementCenterY = elementPosition.top + element.height() / 2;

                        var verticalPosition = elementCenterY - (-20);

                        return verticalPosition;
                    }

                    var closestElement = null;
                    var closestVerticalPosition = Infinity;

                    elements.each(function() {
                        var currentElement = $(this);
                        var currentVerticalPosition = calculateVerticalPosition(currentElement);
                        var verticalPositionDifference = Math.abs(currentVerticalPosition - 0);

                        if (verticalPositionDifference < closestVerticalPosition) {
                            closestVerticalPosition = verticalPositionDifference;
                            closestElement = parseInt(currentElement.attr('data-attr'));
                        }
                    });

                    // set claim points to show on pop-up.
                    $('.wps_wpr_container-popup-val .wps_wpr-val').empty();
                    $('.wps_wpr_container-popup-val .wps_wpr-val').append(closestElement + ' ' + wps_wpr.wps_points_name );
                    // set claim points to rewards user.
                    jQuery('.wps_wpr_user_claim_points').val(closestElement);
                });

                // Get Value ends
                $('.wps_wpr_audio-spin').trigger('pause');
                $('.wps_wpr_audio-spin')[0].currentTime = 0;
                $('.wps_wpr_audio-cheer').trigger('play');
                jQuery('.wps_wpr_container').removeClass('wps_wpr-container--show');
                $('.wps_wpr_container-popup').show();
                $('.wps_wpr_container-popup-gif').show();
                $('.wps_wpr_container-popup-in').addClass('wps_wpr_container-popup-in--show');
            }, 6000);
        });

        // Close pop-up and refresh page.
        jQuery(document).on('click', '.wps_wpr_container-popup-close', function() {
            location.reload();
        })

        // ++++++ Reset wheel, canvas and pop-up when click on Claim now button and assign points as well +++++++
        jQuery(document).on('click', '.wps_wpr_container-popup-claim', function() {

            jQuery(this).prop('disabled', true);
            var claim_points = parseInt( jQuery('.wps_wpr_user_claim_points').val().trim() );

            $('.wps_wpr_container-popup-claim').append('<span>&#10003;</span>');
            $('.wps_wpr_container-popup-claim').css({ 'color': 'var(--success-color)', 'borderColor': 'var(--success-color)' });

            var data = {
                'action'       : 'assign_claim_points',
                'claim_points' : claim_points,
                'nonce'        : wps_wpr.wps_wpr_nonce,
            };

            jQuery.ajax({
                'url'    : wps_wpr.ajaxurl,
                'method' : 'POST',
                'data'   : data,
                success : function( response ) {

                    jQuery('#wps_wpr_show_claim_msg').show();
                    if ( true == response.result ) {

                        jQuery('#wps_wpr_show_claim_msg').css( 'color', 'green' );
                        jQuery('#wps_wpr_show_claim_msg').html( response.msg );
                    } else {

                        jQuery('#wps_wpr_show_claim_msg').css( 'color', 'red' );
                        jQuery('#wps_wpr_show_claim_msg').html( response.msg );
                    }
                    setTimeout(function() {
                        $('.wps_wpr_container-popup-claim').children('span').remove();
                        jQuery('.wps_wpr_container-popup-claim').prop('disabled', false);
                        pause_audio_all();
                        all_reset_wheel();
                        all_reset_popup();
                        $('.wps_wpr_wheel_icon').hide();
                        location.reload();
                    }, 2000);
                },
                error : function( error ) {
                    console.log(error);
                }
            });
        });

        // Main Win wheel for full screen ends
    });
})(jQuery);