/**
 * Setup Wizard JavaScript
 *
 * Handles the setup wizard navigation and form submission.
 *
 * @package Points_And_Rewards_For_WooCommerce
 * @since   2.10.3
 */

(function($) {
	'use strict';

	let currentStep = 0;
	const totalSteps = 6;

	/**
	 * Initialize wizard
	 */
	function initWizard() {
		// Initialize select2 for better dropdowns
		if ($.fn.select2) {
			$('.wps-wpr-select').select2({
				minimumResultsForSearch: -1
			});
		}

		// Navigation buttons
		$('.wps-wpr-btn-next').on('click', handleNext);
		$('.wps-wpr-btn-prev').on('click', handlePrevious);
		$('.wps-wpr-btn-finish').on('click', handleFinish);

		// Template card selection
		$('.wps-wpr-template-card input[type="radio"]').on('change', function() {
			$('.wps-wpr-template-card').removeClass('wps-wpr-template-selected');
			$(this).closest('.wps-wpr-template-card').addClass('wps-wpr-template-selected');
		});

		// Progress step click
		$('.wps-wpr-progress-step').on('click', function() {
			const stepNum = parseInt($(this).data('step'));
			if (stepNum < currentStep) {
				goToStep(stepNum);
			}
		});

		// Initialize first step
		updateStepDisplay();
	}

	/**
	 * Go to next step
	 */
	function handleNext(e) {
		e.preventDefault();

		if (!validateCurrentStep()) {
			return;
		}

		if (currentStep < 5) {
			currentStep++;
			updateStepDisplay();
		}
	}

	/**
	 * Go to previous step
	 */
	function handlePrevious(e) {
		e.preventDefault();

		if (currentStep > 0) {
			currentStep--;
			updateStepDisplay();
		}
	}

	/**
	 * Handle wizard completion
	 */
	function handleFinish(e) {
		e.preventDefault();

		if (!validateCurrentStep()) {
			return;
		}

		// Show loading state
		const $btn = $(this);
		const originalText = $btn.text();
		$btn.prop('disabled', true).text(wps_wpr_wizard_obj.i18n.saving);

		// Collect all form data
		const formData = collectFormData();

		// Send AJAX request
		$.ajax({
			url: wps_wpr_wizard_obj.ajax_url,
			type: 'POST',
			data: {
				action: 'wps_wpr_save_wizard_settings',
				nonce: wps_wpr_wizard_obj.nonce,
				wizard_data: formData
			},
			success: function(response) {
				if (response.success) {
					showNotification(response.data.message, 'success');
					// Redirect to settings page after 1 second
					setTimeout(function() {
						window.location.href = response.data.redirect_url;
					}, 1000);
				} else {
					showNotification(response.data.message || wps_wpr_wizard_obj.i18n.error, 'error');
					$btn.prop('disabled', false).text(originalText);
				}
			},
			error: function() {
				showNotification(wps_wpr_wizard_obj.i18n.error, 'error');
				$btn.prop('disabled', false).text(originalText);
			}
		});
	}

	/**
	 * Update step display
	 */
	function updateStepDisplay() {
		// Hide all steps
		$('.wps-wpr-wizard-step').removeClass('wps-wpr-wizard-step-active');

		// Show current step
		$('.wps-wpr-wizard-step[data-step="' + currentStep + '"]').addClass('wps-wpr-wizard-step-active');

		// Update progress bar (steps 0-5, so 5 intervals between 6 steps)
		const progressPercent = (currentStep / 5) * 100;
		$('.wps-wpr-progress-fill').css('width', progressPercent + '%');

		// Update progress steps
		$('.wps-wpr-progress-step').removeClass('completed active');
		$('.wps-wpr-progress-step').each(function() {
			const stepNum = parseInt($(this).data('step'));
			if (stepNum < currentStep) {
				$(this).addClass('completed');
			} else if (stepNum === currentStep) {
				$(this).addClass('active');
			}
		});

		// Update navigation buttons
		if (currentStep === 0) {
			$('.wps-wpr-btn-prev').hide();
		} else {
			$('.wps-wpr-btn-prev').show();
		}

		if (currentStep === 5) {
			$('.wps-wpr-btn-next').hide();
			$('.wps-wpr-btn-finish').show();
		} else {
			$('.wps-wpr-btn-next').show();
			$('.wps-wpr-btn-finish').hide();
		}

		// Show/hide additional info section on step 5
		if (currentStep === 5) {
			$('.wps-wpr-wizard-info-section[data-step="5"]').show();
		} else {
			$('.wps-wpr-wizard-info-section[data-step="5"]').hide();
		}

		// Scroll to top
		$('.wps-wpr-wizard-content').animate({ scrollTop: 0 }, 300);
	}

	/**
	 * Go to specific step
	 */
	function goToStep(stepNum) {
		currentStep = stepNum;
		updateStepDisplay();
	}

	/**
	 * Validate current step
	 */
	function validateCurrentStep() {
		const $currentStep = $('.wps-wpr-wizard-step[data-step="' + currentStep + '"]');
		let isValid = true;

		// Check required fields in current step
		$currentStep.find('input[required], select[required], textarea[required]').each(function() {
			if (!$(this).val()) {
				isValid = false;
				$(this).addClass('wps-wpr-input-error');
			} else {
				$(this).removeClass('wps-wpr-input-error');
			}
		});

		if (!isValid) {
			showNotification(wps_wpr_wizard_obj.i18n.validation_error, 'error');
		}

		return isValid;
	}

	/**
	 * Collect all form data
	 */
	function collectFormData() {
		const formData = {};

		// Collect data from each step (starting from 0)
		for (let step = 0; step < totalSteps; step++) {
			formData['step' + step] = {};
			const $step = $('.wps-wpr-wizard-step[data-step="' + step + '"]');

			// Get all input, select, and textarea values
			$step.find('input, select, textarea').each(function() {
				const $field = $(this);
				const name = $field.attr('name');

				if (!name) return;

				// Extract field name from step[X][field_name] format
				const match = name.match(/step\d+\[([^\]]+)\](?:\[([^\]]+)\])?/);
				if (!match) return;

				const fieldName = match[1];
				const subField = match[2];

				if ($field.attr('type') === 'checkbox') {
					if (subField) {
						// Handle nested checkboxes
						if (!formData['step' + step][fieldName]) {
							formData['step' + step][fieldName] = {};
						}
						formData['step' + step][fieldName][subField] = $field.is(':checked') ? $field.val() : '0';
					} else {
						formData['step' + step][fieldName] = $field.is(':checked') ? $field.val() : '0';
					}
				} else if ($field.attr('type') === 'radio') {
					if ($field.is(':checked')) {
						formData['step' + step][fieldName] = $field.val();
					}
				} else {
					if (subField) {
						// Handle nested fields (like currency_points[USD])
						if (!formData['step' + step][fieldName]) {
							formData['step' + step][fieldName] = {};
						}
						formData['step' + step][fieldName][subField] = $field.val();
					} else {
						formData['step' + step][fieldName] = $field.val();
					}
				}
			});
		}

		return formData;
	}

	/**
	 * Show notification
	 */
	function showNotification(message, type) {
		// Remove existing notifications
		$('.wps-wpr-notification').remove();

		const $notification = $('<div class="wps-wpr-notification wps-wpr-notification-' + type + '">' + message + '</div>');
		$('body').append($notification);

		// Auto-hide after 5 seconds
		setTimeout(function() {
			$notification.fadeOut(function() {
				$(this).remove();
			});
		}, 5000);
	}

	// Initialize when document is ready
	$(document).ready(function() {
		if ($('.wps-wpr-setup-wizard-wrap').length) {
			initWizard();
		}
	});

})(jQuery);
