/**
 * Campaign Admin JavaScript
 *
 * Handles template library modal and campaign scheduling.
 *
 * @since 2.10.0
 * @package Points_Rewards_For_WooCommerce
 */

(function($) {
	'use strict';

	/**
	 * Template Library Manager
	 */
	const WPS_Campaign_Templates = {

		/**
		 * Initialize template library
		 */
		init: function() {
			this.bindEvents();
			this.loadTemplateCategories();
		},

		/**
		 * Bind DOM events
		 */
		bindEvents: function() {
			// Open template modal
			$(document).on('click', '.wps_wpr_browse_templates_btn', this.openModal.bind(this));

			// Close modal
			$(document).on('click', '.wps-template-modal-close', this.closeModal.bind(this));
			$(document).on('click', '#wps-campaign-template-modal', function(e) {
				// Close if clicking on the modal background (not the container)
				if (e.target.id === 'wps-campaign-template-modal') {
					WPS_Campaign_Templates.closeModal(e);
				}
			});

			// Category filter
			$(document).on('click', '.wps-template-category-tab', this.filterByCategory.bind(this));

			// Search
			$(document).on('input', '#wps-template-search', this.searchTemplates.bind(this));

			// Template selection
			$(document).on('click', '.wps-template-select-btn', this.selectTemplate.bind(this));

			// Preview
			$(document).on('click', '.wps-template-preview-btn', this.previewTemplate.bind(this));

			// Season filter
			$(document).on('change', '#wps-template-season-filter', this.filterBySeason.bind(this));
		},

		/**
		 * Open template modal
		 */
		openModal: function(e) {
			e.preventDefault();
			console.log('=== Opening template modal ===');

			const $modal = $('#wps-campaign-template-modal');
			console.log('Modal element exists:', $modal.length > 0);
			console.log('Modal initial display:', $modal.css('display'));

			// Force modal to be visible with explicit styles
			$modal.css({
				'display': 'flex',
				'visibility': 'visible',
				'opacity': '1'
			}).show();
			$('body').addClass('wps-modal-open');

			console.log('Modal display after show:', $modal.css('display'));
			console.log('Modal visibility:', $modal.is(':visible'));
			console.log('Modal opacity:', $modal.css('opacity'));
			console.log('Modal z-index:', $modal.css('z-index'));
			console.log('Modal dimensions:', { width: $modal.width(), height: $modal.height() });
			console.log('Modal offset:', $modal.offset());

			// Check modal container specifically
			const $container = $('.wps-template-modal-container');
			console.log('=== Modal Container Debug ===');
			console.log('Container exists:', $container.length > 0);
			console.log('Container display:', $container.css('display'));
			console.log('Container visibility:', $container.css('visibility'));
			console.log('Container position:', $container.css('position'));
			console.log('Container dimensions:', { width: $container.width(), height: $container.height() });
			console.log('Container offset:', $container.offset());
			console.log('Container computed styles:', {
				margin: $container.css('margin'),
				padding: $container.css('padding'),
				top: $container.css('top'),
				left: $container.css('left'),
				transform: $container.css('transform')
			});

			// Visual debug: Add borders and backgrounds to both modal and container
			$modal.css({
				'border': '10px solid red',
				'background': 'rgba(255, 0, 0, 0.3)'
			});
			$container.css({
				'border': '10px solid blue',
				'background': 'yellow'
			});

			// Try to scroll container into view
			if ($container.length > 0) {
				$container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
			}

			// Check if anything is covering the modal
			const rect = $modal[0].getBoundingClientRect();
			console.log('Modal viewport position:', rect);
			console.log('Modal in viewport:', rect.top >= 0 && rect.left >= 0 && rect.bottom <= window.innerHeight && rect.right <= window.innerWidth);

			setTimeout(function() {
				$modal.css({
					'border': '',
					'background': ''
				});
				$container.css({
					'border': '',
					'background': ''
				});
			}, 5000);

			// Load templates
			this.loadTemplates('all');
		},

		/**
		 * Close template modal
		 */
		closeModal: function(e) {
			e.preventDefault();
			$('#wps-campaign-template-modal').fadeOut(300);
			$('body').removeClass('wps-modal-open');
		},

		/**
		 * Load template categories
		 */
		loadTemplateCategories: function() {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_wpr_get_template_categories',
					security: wps_wpr_campaign_admin.nonce
				},
				success: function(response) {
					if (response.success) {
						WPS_Campaign_Templates.renderCategories(response.data);
					}
				}
			});
		},

		/**
		 * Render category tabs
		 */
		renderCategories: function(categories) {
			let html = '<button class="wps-template-category-tab active" data-category="all">All Templates</button>';

			$.each(categories, function(key, category) {
				html += `<button class="wps-template-category-tab" data-category="${key}">
							${category.label} <span class="count">(${category.count})</span>
						</button>`;
			});

			$('.wps-template-categories').html(html);
		},

		/**
		 * Load templates
		 */
		loadTemplates: function(category, search = '') {
			console.log('Loading templates for category:', category, 'search:', search);
			$('.wps-template-grid').html('<div class="wps-template-loading">Loading templates...</div>');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_wpr_get_campaign_templates',
					category: category,
					search: search,
					security: wps_wpr_campaign_admin.nonce
				},
				success: function(response) {
					debugger;
					console.log('AJAX response:', response);
					if (response.success) {
						WPS_Campaign_Templates.renderTemplates(response.data);
					} else {
						console.warn('No templates found:', response);
						$('.wps-template-grid').html('<div class="wps-no-templates">No templates found.</div>');
					}
				},
				error: function(xhr, status, error) {
					console.error('AJAX error:', status, error, xhr);
					$('.wps-template-grid').html('<div class="wps-template-error">Error loading templates. Check console.</div>');
				}
			});
		},

		/**
		 * Render template grid
		 */
		renderTemplates: function(templates) {
			console.log('=== renderTemplates called ===');
			console.log('Templates data:', templates);
			console.log('Templates array length:', templates ? templates.length : 'null/undefined');

			const $grid = $('.wps-template-grid');
			console.log('Grid element exists:', $grid.length > 0);
			console.log('Grid is visible:', $grid.is(':visible'));
			console.log('Grid parent modal visible:', $('#wps-campaign-template-modal').is(':visible'));

			if (!templates || templates.length === 0) {
				console.warn('No templates to render');
				$grid.html('<div class="wps-no-templates">No templates found.</div>');
				console.log('Grid HTML after no templates:', $grid.html());
				return;
			}

			let html = '';

			try {
				$.each(templates, function(index, template) {
					// Safely get template data
					if (!template || !template.metadata) {
						console.warn('Invalid template at index', index, template);
						return true; // Continue to next
					}

					const primaryColor = template.metadata.color_primary || '#a13a93';
					const secondaryColor = template.metadata.color_secondary || '#ffbb21';
					const gradientStyle = `background: linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%);`;
					const heading = (template.metadata.heading || '').replace(/'/g, '&#39;').replace(/"/g, '&quot;');
					const imageUrl = template.metadata.url || '';

					html += `
						<div class="wps-template-card" data-category="${template.category}" data-template-id="${template.template_id}">
							<div class="wps-template-thumbnail" style="${gradientStyle}">
								<img src="${imageUrl}" alt="${heading}" loading="lazy" onerror="this.style.display='none'">
								<div class="wps-template-overlay">
									<button class="wps-template-preview-btn" data-url="${imageUrl}">
										<span class="dashicons dashicons-visibility"></span> Preview
									</button>
								</div>
							</div>
							<div class="wps-template-info">
								<h4 class="wps-template-name">${template.template_id}</h4>
								<p class="wps-template-heading">${heading}</p>
								<div class="wps-template-colors">
									<span class="wps-color-swatch" style="background: ${primaryColor};" title="${primaryColor}"></span>
									<span class="wps-color-swatch" style="background: ${secondaryColor};" title="${secondaryColor}"></span>
								</div>
								<button class="wps-template-select-btn button button-primary"
									data-category="${template.category}"
									data-template-id="${template.template_id}"
									data-heading="${heading}"
									data-url="${imageUrl}"
									data-color-primary="${primaryColor}"
									data-color-secondary="${secondaryColor}">
									Select Template
								</button>
							</div>
						</div>
					`;
				});

				console.log('Templates rendered successfully!');
				console.log('Generated HTML length:', html.length);
				console.log('First 200 chars of HTML:', html.substring(0, 200));

				$grid.html(html);

				console.log('Grid HTML updated. Verifying...');
				console.log('Grid children count:', $grid.children().length);
				console.log('Template cards count:', $('.wps-template-card').length);
				console.log('Grid HTML length after update:', $grid.html().length);

			} catch (error) {
				console.error('ERROR in renderTemplates:', error);
				console.error('Error stack:', error.stack);
				$grid.html('<div class="wps-template-error">Error rendering templates. Check console for details.</div>');
			}
		},

		/**
		 * Filter templates by category
		 */
		filterByCategory: function(e) {
			e.preventDefault();

			const $btn = $(e.currentTarget);
			const category = $btn.data('category');

			$('.wps-template-category-tab').removeClass('active');
			$btn.addClass('active');

			this.loadTemplates(category, $('#wps-template-search').val());
		},

		/**
		 * Filter by season
		 */
		filterBySeason: function(e) {
			const season = $(e.target).val();

			if (season === 'all') {
				$('.wps-template-card').show();
			} else {
				$('.wps-template-card').hide();
				$(`.wps-template-card[data-season="${season}"]`).show();
			}
		},

		/**
		 * Search templates
		 */
		searchTemplates: function(e) {
			const keyword = $(e.target).val();
			const activeCategory = $('.wps-template-category-tab.active').data('category');

			// Debounce search
			clearTimeout(this.searchTimeout);
			this.searchTimeout = setTimeout(function() {
				WPS_Campaign_Templates.loadTemplates(activeCategory, keyword);
			}, 500);
		},

		/**
		 * Select template and auto-fill settings
		 */
		selectTemplate: function(e) {
			e.preventDefault();

			const $btn = $(e.currentTarget);
			const templateData = {
				heading: $btn.data('heading'),
				url: $btn.data('url'),
				colorPrimary: $btn.data('color-primary'),
				colorSecondary: $btn.data('color-secondary'),
				category: $btn.data('category'),
				templateId: $btn.data('template-id')
			};

			// Auto-fill campaign settings
			$('#wps_wpr_enter_campaign_heading').val(templateData.heading);
			$('#wps_wpr_enter_campaign_image_url').val(templateData.url);
			$('.wps_wpr_campaign_color_one').val(templateData.colorPrimary);
			$('.wps_wpr_campaign_color_two').val(templateData.colorSecondary);

			// Show preview
			this.updatePreview(templateData);

			// Close modal
			this.closeModal(e);

			// Show success notice
			this.showNotice('success', `Template "${templateData.templateId}" applied successfully!`);

			// Trigger event for developers
			$(document).trigger('wps_campaign_template_selected', [templateData]);
		},

		/**
		 * Preview template in lightbox
		 */
		previewTemplate: function(e) {
			e.preventDefault();
			e.stopPropagation();

			const imageUrl = $(e.currentTarget).data('url');

			// Create lightbox
			const lightboxHtml = `
				<div class="wps-template-lightbox">
					<div class="wps-lightbox-overlay"></div>
					<div class="wps-lightbox-content">
						<button class="wps-lightbox-close">&times;</button>
						<img src="${imageUrl}" alt="Template Preview">
					</div>
				</div>
			`;

			$('body').append(lightboxHtml);
			$('.wps-template-lightbox').fadeIn(200);

			// Close lightbox
			$(document).on('click', '.wps-lightbox-close, .wps-lightbox-overlay', function() {
				$('.wps-template-lightbox').fadeOut(200, function() {
					$(this).remove();
				});
			});
		},

		/**
		 * Update campaign preview
		 */
		updatePreview: function(templateData) {
			const previewHtml = `
				<div class="wps-campaign-preview" style="background: linear-gradient(135deg, ${templateData.colorPrimary} 0%, ${templateData.colorSecondary} 100%); padding: 20px; border-radius: 8px; margin-top: 15px;">
					<img src="${templateData.url}" alt="${templateData.heading}" style="max-width: 100%; height: auto; border-radius: 4px; margin-bottom: 10px;">
					<h3 style="color: #fff; margin: 0; font-size: 18px;">${templateData.heading}</h3>
					<p style="color: #fff; margin: 5px 0 0; font-size: 12px;">Colors: ${templateData.colorPrimary} / ${templateData.colorSecondary}</p>
				</div>
			`;

			// Remove old preview
			$('.wps-campaign-preview').remove();

			// Add new preview after image URL field
			$('#wps_wpr_enter_campaign_image_url').closest('.wps_wpr_general_content').append(previewHtml);
		},

		/**
		 * Show admin notice
		 */
		showNotice: function(type, message) {
			const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
			const noticeHtml = `
				<div class="notice ${noticeClass} is-dismissible wps-template-notice" style="margin: 15px 0;">
					<p>${message}</p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button>
				</div>
			`;

			$('.wps_wpr_user_badges_main_wrappers').prepend(noticeHtml);

			// Auto-dismiss after 5 seconds
			setTimeout(function() {
				$('.wps-template-notice').fadeOut(300, function() {
					$(this).remove();
				});
			}, 5000);

			// Manual dismiss
			$(document).on('click', '.wps-template-notice .notice-dismiss', function() {
				$(this).closest('.wps-template-notice').fadeOut(300, function() {
					$(this).remove();
				});
			});
		}
	};

	/**
	 * Campaign Scheduling Manager
	 */
	const WPS_Campaign_Scheduling = {

		/**
		 * Initialize scheduling
		 */
		init: function() {
			this.bindEvents();
			this.initDatetimePickers();
		},

		/**
		 * Bind events
		 */
		bindEvents: function() {
			// Enable scheduling toggle
			$(document).on('change', '#wps_wpr_enable_scheduling', this.toggleSchedulingFields.bind(this));

			// Clear dates button
			$(document).on('click', '#wps_wpr_clear_schedule', this.clearDates.bind(this));
		},

		/**
		 * Toggle scheduling fields
		 */
		toggleSchedulingFields: function(e) {
			const isChecked = $(e.target).is(':checked');

			if (isChecked) {
				$('.wps-scheduling-fields').slideDown(300);
			} else {
				$('.wps-scheduling-fields').slideUp(300);
			}
		},

		/**
		 * Initialize datetime pickers
		 */
		initDatetimePickers: function() {
			// Add min attribute to prevent past dates
			const now = new Date();
			const minDate = now.toISOString().slice(0, 16);

			$('#wps_wpr_campaign_start_date, #wps_wpr_campaign_end_date').attr('min', minDate);

			// Validate end date is after start date
			$('#wps_wpr_campaign_start_date').on('change', function() {
				const startDate = $(this).val();
				$('#wps_wpr_campaign_end_date').attr('min', startDate);
			});
		},

		/**
		 * Clear schedule dates
		 */
		clearDates: function(e) {
			e.preventDefault();

			$('#wps_wpr_campaign_start_date, #wps_wpr_campaign_end_date').val('');
			WPS_Campaign_Templates.showNotice('success', 'Campaign schedule cleared.');
		}
	};

	/**
	 * Quiz Management
	 */
	const WPS_Quiz_Manager = {

		/**
		 * Initialize quiz manager
		 */
		init: function() {
			this.bindEvents();
		},

		/**
		 * Bind events
		 */
		bindEvents: function() {
			// Add quiz button (Free version - limit to 1)
			$(document).on('click', '#wps_wpr_add_quiz', this.addQuiz.bind(this));

			// Remove quiz button
			$(document).on('click', '.wps_wpr_remove_quiz', this.removeQuiz.bind(this));
		},

		/**
		 * Add new quiz question
		 */
		addQuiz: function(e) {
			e.preventDefault();

			// Check if Pro version
			const isPro = wps_wpr_campaign_admin.is_pro || false;
			const quizCount = $('.wps_wpr_quiz_row').length;

			// Free version: limit to 1 quiz
			if (!isPro && quizCount >= 1) {
				this.showProNotice();
				return;
			}

			// Clone first quiz row
			const $template = $('.wps_wpr_quiz_row').first().clone();

			// Clear values
			$template.find('input[type="text"], input[type="number"]').val('');

			// Append to container
			$('#wps_wpr_quiz_container').append($template);

			WPS_Campaign_Templates.showNotice('success', 'Quiz question added.');
		},

		/**
		 * Remove quiz question
		 */
		removeQuiz: function(e) {
			e.preventDefault();

			const $quizRow = $(e.currentTarget).closest('.wps_wpr_quiz_row');
			const quizCount = $('.wps_wpr_quiz_row').length;

			// Keep at least 1 quiz
			if (quizCount <= 1) {
				WPS_Campaign_Templates.showNotice('error', 'At least one quiz question is required.');
				return;
			}

			$quizRow.fadeOut(300, function() {
				$(this).remove();
			});
		},

		/**
		 * Show Pro upgrade notice for quiz limits
		 */
		showProNotice: function() {
			const upgradeHtml = `
				<div class="notice notice-warning wps-pro-upgrade-notice" style="margin: 15px 0; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
					<p style="margin: 0;">
						<strong>Upgrade to Pro</strong> to add unlimited quiz questions!
						<a href="https://wpswings.com/product/points-and-rewards-for-woocommerce-plugin/?utm_source=wpswings-par-pro&utm_medium=par-org-backend&utm_campaign=go-pro" target="_blank" class="button button-primary" style="margin-left: 10px;">Upgrade Now</a>
					</p>
				</div>
			`;

			$('.wps_wpr_insert_pro_html').html(upgradeHtml).show();

			setTimeout(function() {
				$('.wps-pro-upgrade-notice').fadeOut(300);
			}, 8000);
		}
	};

	/**
	 * Initialize all modules on document ready
	 */
	$(document).ready(function() {
		WPS_Campaign_Templates.init();
		WPS_Campaign_Scheduling.init();
		WPS_Quiz_Manager.init();
	});

})(jQuery);
