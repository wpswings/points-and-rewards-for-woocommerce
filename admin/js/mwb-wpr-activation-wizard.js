/**
 * Activation Wizard JavaScript
 *
 * Handles wizard navigation, template selection, form submission, and summary generation.
 *
 * @package    Ultimate_Woocommerce_Points_And_Rewards
 * @subpackage Ultimate_Woocommerce_Points_And_Rewards/admin/js
 * @since      3.0.0
 */

(function($) {
	'use strict';

	// Template configurations
	const templates = {
		simple: {
			name: 'Simple Store',
			config: {
				mwb_wpr_points_name_singular: 'Point',
				mwb_wpr_points_name_plural: 'Points',
				mwb_wpr_points_display_position: ['shop_page', 'product_page', 'checkout_page'],
				mwb_wpr_earning_enable: true,
				mwb_wpr_earning_rate: 1,
				mwb_wpr_earning_currency: 1,
				mwb_wpr_signup_points_enable: true,
				mwb_wpr_signup_points_value: 100,
				mwb_wpr_referral_enable: false,
				mwb_wpr_redemption_enable: true,
				mwb_wpr_redemption_rate: 100,
				mwb_wpr_redemption_value: 1,
				mwb_wpr_min_redeem_points: 100,
				mwb_wpr_redeem_location: 'both'
			}
		},
		subscription: {
			name: 'Subscription Store',
			config: {
				mwb_wpr_points_name_singular: 'Loyalty Point',
				mwb_wpr_points_name_plural: 'Loyalty Points',
				mwb_wpr_points_display_position: ['product_page', 'cart_page', 'checkout_page'],
				mwb_wpr_earning_enable: true,
				mwb_wpr_earning_rate: 2,
				mwb_wpr_earning_currency: 1,
				mwb_wpr_signup_points_enable: true,
				mwb_wpr_signup_points_value: 200,
				mwb_wpr_referral_enable: true,
				mwb_wpr_referral_points_value: 500,
				mwb_wpr_redemption_enable: true,
				mwb_wpr_redemption_rate: 100,
				mwb_wpr_redemption_value: 1,
				mwb_wpr_min_redeem_points: 200,
				mwb_wpr_redeem_location: 'checkout'
			}
		},
		multivendor: {
			name: 'Multi-Vendor',
			config: {
				mwb_wpr_points_name_singular: 'Reward Point',
				mwb_wpr_points_name_plural: 'Reward Points',
				mwb_wpr_points_display_position: ['shop_page', 'product_page', 'cart_page', 'checkout_page'],
				mwb_wpr_earning_enable: true,
				mwb_wpr_earning_rate: 1,
				mwb_wpr_earning_currency: 1,
				mwb_wpr_signup_points_enable: true,
				mwb_wpr_signup_points_value: 150,
				mwb_wpr_referral_enable: true,
				mwb_wpr_referral_points_value: 300,
				mwb_wpr_redemption_enable: true,
				mwb_wpr_redemption_rate: 100,
				mwb_wpr_redemption_value: 1,
				mwb_wpr_min_redeem_points: 150,
				mwb_wpr_redeem_location: 'both'
			}
		},
		custom: {
			name: 'Custom Configuration',
			config: {}
		}
	};

	// Store wizard data across steps
	let wizardData = {};

	$(document).ready(function() {
		initWizard();
	});

	function initWizard() {
		// Initialize Select2 for multi-select
		if ($.fn.select2) {
			$('.mwb-wpr-select2').select2({
				width: '100%'
			});
		}

		// Template selection
		$('.mwb-wpr-apply-template').on('click', handleTemplateSelection);

		// Navigation buttons
		$('.mwb-wpr-wizard-next').on('click', handleNextStep);
		$('.mwb-wpr-wizard-prev').on('click', handlePrevStep);
		$('.mwb-wpr-wizard-complete').on('click', handleCompleteSetup);

		// Load wizard data from sessionStorage if available
		loadWizardData();

		// Generate summary on step 5
		if (getCurrentStep() === 5) {
			generateSummary();
		}
	}

	function handleTemplateSelection(e) {
		e.preventDefault();
		const templateType = $(this).data('template');
		const template = templates[templateType];

		// Highlight selected template
		$('.mwb-wpr-template-card').removeClass('selected');
		$(this).closest('.mwb-wpr-template-card').addClass('selected');

		if (templateType === 'custom') {
			// Show custom fields
			$('#mwb-wpr-custom-fields').slideDown();
		} else {
			// Apply template configuration
			applyTemplate(template.config);
			$('#mwb-wpr-custom-fields').slideDown();
		}

		// Store selected template
		wizardData.selectedTemplate = templateType;
		saveWizardData();
	}

	function applyTemplate(config) {
		// Apply configuration to form fields
		Object.keys(config).forEach(function(key) {
			const value = config[key];
			const $field = $('[name="' + key + '"]');

			if ($field.length) {
				if ($field.attr('type') === 'checkbox') {
					$field.prop('checked', value);
				} else if ($field.is('select[multiple]')) {
					$field.val(value).trigger('change');
				} else {
					$field.val(value);
				}
			}
		});
	}

	function handleNextStep(e) {
		e.preventDefault();

		// Validate current step
		if (!validateCurrentStep()) {
			return;
		}

		// Save current step data
		saveCurrentStepData();

		// Navigate to next step
		const currentStep = getCurrentStep();
		const nextStep = currentStep + 1;
		navigateToStep(nextStep);
	}

	function handlePrevStep(e) {
		e.preventDefault();

		// Save current step data (no validation needed for going back)
		saveCurrentStepData();

		// Navigate to previous step
		const currentStep = getCurrentStep();
		const prevStep = currentStep - 1;
		navigateToStep(prevStep);
	}

	function handleCompleteSetup(e) {
		e.preventDefault();

		// Validate current step
		if (!validateCurrentStep()) {
			return;
		}

		// Save current step data
		saveCurrentStepData();

		// Show loading state
		const $button = $(this);
		const originalText = $button.text();
		$button.prop('disabled', true).text('Saving...');

		// Submit wizard data via AJAX
		$.ajax({
			url: mwbWprWizardData.ajaxUrl,
			type: 'POST',
			data: {
				action: 'mwb_wpr_save_wizard_settings',
				nonce: mwbWprWizardData.nonce,
				wizard_data: wizardData
			},
			success: function(response) {
				if (response.success) {
					// Clear stored wizard data
					sessionStorage.removeItem('mwb_wpr_wizard_data');

					// Redirect to Points and Rewards general settings page
					window.location.href = mwbWprWizardData.settingsUrl || 'admin.php?page=points_and_rewards_for_woocommerce_menu';
				} else {
					alert(response.data.message || 'Failed to save settings. Please try again.');
					$button.prop('disabled', false).text(originalText);
				}
			},
			error: function() {
				alert('An error occurred. Please try again.');
				$button.prop('disabled', false).text(originalText);
			}
		});
	}

	function validateCurrentStep() {
		const currentStep = getCurrentStep();
		let isValid = true;
		let errorMessage = '';

		switch (currentStep) {
			case 1:
				// Check if a template is selected or custom fields are filled
				if (!wizardData.selectedTemplate) {
					errorMessage = 'Please select a template or configure manually.';
					isValid = false;
				} else if (wizardData.selectedTemplate === 'custom') {
					const singular = $('[name="mwb_wpr_points_name_singular"]').val();
					const plural = $('[name="mwb_wpr_points_name_plural"]').val();
					if (!singular || !plural) {
						errorMessage = 'Please enter both singular and plural names for points.';
						isValid = false;
					}
				}
				break;

			case 2:
				// Validate earning rate
				const earningRate = parseFloat($('[name="mwb_wpr_earning_rate"]').val());
				const earningCurrency = parseFloat($('[name="mwb_wpr_earning_currency"]').val());
				if (isNaN(earningRate) || earningRate < 0 || isNaN(earningCurrency) || earningCurrency <= 0) {
					errorMessage = 'Please enter valid earning rate values.';
					isValid = false;
				}
				break;

			case 3:
				// Validate redemption rate
				const redemptionRate = parseFloat($('[name="mwb_wpr_redemption_rate"]').val());
				const redemptionValue = parseFloat($('[name="mwb_wpr_redemption_value"]').val());
				if (isNaN(redemptionRate) || redemptionRate < 1 || isNaN(redemptionValue) || redemptionValue <= 0) {
					errorMessage = 'Please enter valid redemption rate values.';
					isValid = false;
				}
				break;

			case 4:
				// No validation needed for notifications step
				break;
		}

		if (!isValid && errorMessage) {
			alert(errorMessage);
		}

		return isValid;
	}

	function saveCurrentStepData() {
		const currentStep = getCurrentStep();
		const $form = $('#mwb-wpr-wizard-form');
		const formData = {};

		// Serialize form data
		$form.find('input, select, textarea').each(function() {
			const $field = $(this);
			const name = $field.attr('name');

			if (!name || name === 'current_step' || name === 'mwb_wpr_wizard_nonce' || name === '_wp_http_referer') {
				return;
			}

			if ($field.attr('type') === 'checkbox') {
				formData[name] = $field.is(':checked');
			} else if ($field.is('select[multiple]')) {
				formData[name] = $field.val() || [];
			} else {
				formData[name] = $field.val();
			}
		});

		// Store step data
		wizardData['step' + currentStep] = formData;
		saveWizardData();
	}

	function saveWizardData() {
		sessionStorage.setItem('mwb_wpr_wizard_data', JSON.stringify(wizardData));
	}

	function loadWizardData() {
		const storedData = sessionStorage.getItem('mwb_wpr_wizard_data');
		if (storedData) {
			try {
				wizardData = JSON.parse(storedData);
			} catch (e) {
				wizardData = {};
			}
		}
	}

	function navigateToStep(step) {
		// Redirect to the step URL
		const url = new URL(window.location.href);
		url.searchParams.set('step', step);
		window.location.href = url.toString();
	}

	function getCurrentStep() {
		const urlParams = new URLSearchParams(window.location.search);
		return parseInt(urlParams.get('step')) || 1;
	}

	function generateSummary() {
		const $summaryContainer = $('#mwb-wpr-wizard-summary');
		if (!$summaryContainer.length) {
			return;
		}

		let summaryHtml = '<div class="mwb-wpr-summary-sections">';

		// Template selected
		if (wizardData.selectedTemplate) {
			const templateName = templates[wizardData.selectedTemplate].name;
			summaryHtml += '<div class="mwb-wpr-summary-section">';
			summaryHtml += '<h4>Template</h4>';
			summaryHtml += '<p>' + templateName + '</p>';
			summaryHtml += '</div>';
		}

		// Basic setup
		if (wizardData.step1) {
			summaryHtml += '<div class="mwb-wpr-summary-section">';
			summaryHtml += '<h4>Points Currency</h4>';
			summaryHtml += '<p>Singular: ' + (wizardData.step1.mwb_wpr_points_name_singular || 'Point') + '</p>';
			summaryHtml += '<p>Plural: ' + (wizardData.step1.mwb_wpr_points_name_plural || 'Points') + '</p>';
			summaryHtml += '</div>';
		}

		// Earning rules
		if (wizardData.step2) {
			summaryHtml += '<div class="mwb-wpr-summary-section">';
			summaryHtml += '<h4>Earning Rules</h4>';
			summaryHtml += '<p>Rate: ' + wizardData.step2.mwb_wpr_earning_rate + ' points per ' + wizardData.step2.mwb_wpr_earning_currency + ' spent</p>';
			if (wizardData.step2.mwb_wpr_signup_points_enable) {
				summaryHtml += '<p>Signup Bonus: ' + wizardData.step2.mwb_wpr_signup_points_value + ' points</p>';
			}
			if (wizardData.step2.mwb_wpr_referral_enable) {
				summaryHtml += '<p>Referral Bonus: ' + wizardData.step2.mwb_wpr_referral_points_value + ' points</p>';
			}
			summaryHtml += '</div>';
		}

		// Redemption
		if (wizardData.step3) {
			summaryHtml += '<div class="mwb-wpr-summary-section">';
			summaryHtml += '<h4>Redemption</h4>';
			summaryHtml += '<p>Rate: ' + wizardData.step3.mwb_wpr_redemption_rate + ' points = ' + wizardData.step3.mwb_wpr_redemption_value + ' currency</p>';
			summaryHtml += '<p>Minimum: ' + wizardData.step3.mwb_wpr_min_redeem_points + ' points</p>';
			summaryHtml += '</div>';
		}

		// Notifications
		if (wizardData.step4) {
			summaryHtml += '<div class="mwb-wpr-summary-section">';
			summaryHtml += '<h4>Notifications</h4>';
			const notifications = [];
			if (wizardData.step4.mwb_wpr_email_points_earned) notifications.push('Email on earn');
			if (wizardData.step4.mwb_wpr_email_points_redeemed) notifications.push('Email on redeem');
			if (wizardData.step4.mwb_wpr_email_points_expiring) notifications.push('Expiry reminders');
			if (wizardData.step4.mwb_wpr_sms_enable) notifications.push('SMS notifications');
			if (wizardData.step4.mwb_wpr_whatsapp_enable) notifications.push('WhatsApp notifications');
			summaryHtml += '<p>' + (notifications.length > 0 ? notifications.join(', ') : 'No notifications enabled') + '</p>';
			summaryHtml += '</div>';
		}

		summaryHtml += '</div>';
		$summaryContainer.html(summaryHtml);
	}

})(jQuery);
