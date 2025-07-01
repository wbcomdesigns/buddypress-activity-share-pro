/**
 * BuddyPress Activity Share Pro - Admin JavaScript (Production)
 * 
 * Clean, organized JavaScript for all admin interface functionality
 * Optimized for production with better error handling and performance
 * 
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin/js
 * @since      1.5.1
 */

(function($) {
    'use strict';

    /**
     * Admin Interface Controller
     */
    const BPShareAdmin = {
        
        // Configuration
        config: {
            selectors: {
                dragDropArea: '.social_icon_section',
                enabledServices: '#drag_icon_ul',
                disabledServices: '#drag_social_icon',
                socialIcon: '.socialicon',
                toggleSwitch: '.bp-share-toggle input[type="checkbox"]',
                colorPicker: '.bp-share-color-picker',
                submitButton: '.bp-share-submit-button',
                resetButton: '.bp-share-reset-button',
                form: 'form',
                tabs: '.nav-tab',
                responsiveToggle: '#bp-share-toggle-btn',
                navTabs: '.bp-share-nav-tabs',
                notice: '.bp-share-notice',
                previewIcon: '.bp-share-preview-icon'
            },
            classes: {
                loading: 'bp-share-loading',
                active: 'is-active',
                disabled: 'disabled',
                error: 'error',
                success: 'success',
                changed: 'has-changes'
            }
        },

        // State management
        state: {
            unsavedChanges: false,
            submitting: false,
            dragActive: false
        },

        /**
         * Initialize all admin functionality
         */
        init: function() {
            this.setupEventListeners();
            this.setupDragDrop();
            this.setupColorPickers();
            this.setupFormHandling();
            this.setupResponsiveMenu();
            this.setupAccessibility();
            this.setupNotifications();
            this.setupPreviewUpdates();
            
            // Setup page-specific functionality
            this.setupPageSpecific();
            
            console.log('BP Share Admin initialized');
        },

        /**
         * Setup event listeners
         */
        setupEventListeners: function() {
            const self = this;
            
            // Form change detection
            $(document).on('change input', 'form input, form select, form textarea', function() {
                self.markAsChanged();
            });

            // Toggle switches
            $(document).on('change', this.config.selectors.toggleSwitch, function() {
                self.handleToggleChange($(this));
            });

            // Submit buttons
            $(document).on('click', this.config.selectors.submitButton, function(e) {
                self.handleFormSubmit(e, $(this));
            });

            // Reset buttons
            $(document).on('click', this.config.selectors.resetButton, function(e) {
                self.handleReset(e, $(this));
            });

            // Tab navigation
            $(document).on('click', this.config.selectors.tabs, function(e) {
                self.handleTabClick(e, $(this));
            });

            // Responsive menu
            $(document).on('change', this.config.selectors.responsiveToggle, function() {
                self.toggleResponsiveMenu($(this).is(':checked'));
            });

            // Notice dismissal
            $(document).on('click', '.notice-dismiss', function() {
                self.dismissNotice($(this));
            });

            // Window events
            $(window).on('beforeunload', function() {
                return self.handleBeforeUnload();
            });

            $(window).on('resize', this.debounce(function() {
                self.handleResize();
            }, 250));
        },

        /**
         * Setup drag and drop functionality
         */
        setupDragDrop: function() {
            if (!$(this.config.selectors.dragDropArea).length) {
                return;
            }

            const self = this;

            // Initialize sortable for enabled services
            $(this.config.selectors.enabledServices).sortable({
                items: this.config.selectors.socialIcon,
                placeholder: 'sortable-placeholder',
                tolerance: 'pointer',
                start: function(event, ui) {
                    ui.placeholder.height(ui.item.height());
                    ui.item.addClass('sorting');
                    self.state.dragActive = true;
                },
                stop: function(event, ui) {
                    ui.item.removeClass('sorting');
                    self.state.dragActive = false;
                    self.saveServiceOrder();
                }
            });

            // Setup drag and drop between lists
            this.setupServiceDragDrop();
        },

        /**
         * Setup drag and drop between service lists
         */
        setupServiceDragDrop: function() {
            const self = this;

            // Make social icons draggable
            $(this.config.selectors.socialIcon).draggable({
                revert: "invalid",
                helper: "clone",
                start: function() {
                    $(this).css('opacity', '0.5');
                    $('.social-services-list').addClass('drag-active');
                },
                stop: function() {
                    $(this).css('opacity', '1');
                    $('.social-services-list').removeClass('drag-active');
                }
            });

            // Setup drop zones
            $(this.config.selectors.enabledServices).droppable({
                accept: "#drag_social_icon > li",
                drop: function(event, ui) {
                    self.handleServiceEnable(ui.draggable);
                }
            });

            $(this.config.selectors.disabledServices).droppable({
                accept: "#drag_icon_ul > li",
                drop: function(event, ui) {
                    self.handleServiceDisable(ui.draggable);
                }
            });
        },

        /**
         * Handle enabling a service
         */
        handleServiceEnable: function($item) {
            const serviceName = $item.text().trim();
            const serviceClass = 'icon_' + serviceName;
            const $newItem = $('<li class="socialicon ui-draggable ' + serviceClass + '">' + serviceName + '</li>').hide();
            
            $(this.config.selectors.enabledServices).append($newItem);
            $newItem.fadeIn();
            
            $item.fadeOut(() => {
                $item.remove();
                // Remove "no services" message if present
                $(this.config.selectors.disabledServices + ' .no-services-message').remove();
            });

            this.callServiceAPI('wss_social_icons', { term_name: serviceName })
                .then(() => {
                    this.showNotification(`${serviceName} enabled successfully`, 'success');
                    this.markAsChanged();
                    this.reinitializeDragDrop();
                })
                .catch((error) => {
                    this.showNotification(`Failed to enable ${serviceName}`, 'error');
                    console.error('Service enable error:', error);
                });
        },

        /**
         * Handle disabling a service
         */
        handleServiceDisable: function($item) {
            const serviceName = $item.text().trim();
            const serviceClass = 'icon_' + serviceName;
            const $newItem = $('<li class="socialicon ui-draggable ' + serviceClass + '">' + serviceName + '</li>').hide();
            
            $(this.config.selectors.disabledServices).append($newItem);
            $newItem.fadeIn();
            
            $item.fadeOut(() => {
                $item.remove();
                // Remove "no services" message if present
                $(this.config.selectors.enabledServices + ' .no-services-message').remove();
            });

            this.callServiceAPI('wss_social_remove_icons', { icon_name: serviceName })
                .then(() => {
                    this.showNotification(`${serviceName} disabled successfully`, 'success');
                    this.markAsChanged();
                    this.reinitializeDragDrop();
                })
                .catch((error) => {
                    this.showNotification(`Failed to disable ${serviceName}`, 'error');
                    console.error('Service disable error:', error);
                });
        },

        /**
         * Reinitialize drag and drop after changes
         */
        reinitializeDragDrop: function() {
            // Destroy existing draggable/droppable
            $(this.config.selectors.socialIcon).draggable('destroy');
            
            // Reinitialize
            this.initializeDraggableServices();
        },

        /**
         * Call service management API
         */
        callServiceAPI: function(action, data) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: bp_share_admin_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: action,
                        nonce: bp_share_admin_vars.nonce,
                        ...data
                    },
                    success: function(response) {
                        if (response.success) {
                            resolve(response.data);
                        } else {
                            reject(new Error(response.data?.message || 'API call failed'));
                        }
                    },
                    error: function(xhr, status, error) {
                        reject(new Error(`Network error: ${error}`));
                    }
                });
            });
        },

        /**
         * Save service order
         */
        saveServiceOrder: function() {
            const order = [];
            $(this.config.selectors.enabledServices + ' ' + this.config.selectors.socialIcon).each(function() {
                order.push($(this).text().trim());
            });

            this.callServiceAPI('bp_share_save_service_order', { order: order })
                .then(() => {
                    this.showNotification('Service order saved', 'success', 2000);
                })
                .catch((error) => {
                    console.error('Save order error:', error);
                });
        },

        /**
         * Setup color pickers
         */
        setupColorPickers: function() {
            if (!$(this.config.selectors.colorPicker).length) {
                return;
            }

            const self = this;

            $(this.config.selectors.colorPicker).wpColorPicker({
                change: function(event, ui) {
                    self.updateColorPreview(event.target, ui.color.toString());
                    self.markAsChanged();
                },
                clear: function(event) {
                    self.updateColorPreview(event.target, '');
                    self.markAsChanged();
                }
            });
        },

        /**
         * Update color preview
         */
        updateColorPreview: function(input, color) {
            const $input = $(input);
            const colorType = $input.attr('id');
            
            // Update preview icons based on color type
            if (colorType.includes('bg_color')) {
                $(this.config.selectors.previewIcon).css('background-color', color);
            } else if (colorType.includes('text_color')) {
                $(this.config.selectors.previewIcon).css('color', color);
            }

            // Trigger preview update
            this.updateLivePreview();
        },

        /**
         * Update live preview for icon settings
         */
        updateLivePreview: function() {
            const selectedStyle = $('input[name*="icon_style"]:checked').val() || 'circle';
            const bgColor = $('#icon_bg_color').val() || '#667eea';
            const textColor = $('#icon_text_color').val() || '#ffffff';
            const hoverColor = $('#icon_hover_color').val() || '#5a6fd8';

            // Update preview icons
            $(this.config.selectors.previewIcon)
                .removeClass('circle rec blackwhite baricon')
                .addClass(selectedStyle)
                .css({
                    'background-color': bgColor,
                    'color': textColor
                });

            // Update hover styles dynamically
            this.updateDynamicStyles(hoverColor, textColor);
        },

        /**
         * Update dynamic hover styles
         */
        updateDynamicStyles: function(hoverColor, textColor) {
            const hoverStyles = `
                <style id="bp-share-preview-hover-styles">
                    .bp-share-preview-icon:hover {
                        background-color: ${hoverColor} !important;
                        color: ${textColor} !important;
                    }
                </style>
            `;
            
            $('#bp-share-preview-hover-styles').remove();
            $('head').append(hoverStyles);
        },

        /**
         * Setup form handling
         */
        setupFormHandling: function() {
            const self = this;

            // Form validation
            $(this.config.selectors.form).on('submit', function(e) {
                const $form = $(this);
                
                if (!self.validateForm($form)) {
                    e.preventDefault();
                    return false;
                }

                self.state.submitting = true;
                self.showFormLoading($form);
            });

            // Auto-save functionality (optional)
            this.setupAutoSave();
        },

        /**
         * Validate form
         */
        validateForm: function($form) {
            let isValid = true;
            const $requiredFields = $form.find('[required]');

            $requiredFields.each(function() {
                const $field = $(this);
                const value = $field.val();

                if (!value || value.trim() === '') {
                    this.showFieldError($field, bp_share_admin_vars.strings.error);
                    isValid = false;
                } else {
                    this.clearFieldError($field);
                }
            }.bind(this));

            return isValid;
        },

        /**
         * Show field error
         */
        showFieldError: function($field, message) {
            $field.addClass(this.config.classes.error);
            
            let $errorMsg = $field.siblings('.field-error');
            if (!$errorMsg.length) {
                $errorMsg = $('<div class="field-error"></div>');
                $field.after($errorMsg);
            }
            
            $errorMsg.text(message).slideDown(300);
        },

        /**
         * Clear field error
         */
        clearFieldError: function($field) {
            $field.removeClass(this.config.classes.error);
            $field.siblings('.field-error').slideUp(300);
        },

        /**
         * Setup auto-save functionality
         */
        setupAutoSave: function() {
            if (!bp_share_admin_vars.auto_save_enabled) {
                return;
            }

            let autoSaveTimer;
            const self = this;
            
            $(document).on('change input', 'form input, form select, form textarea', function() {
                const $form = $(this).closest('form');
                
                clearTimeout(autoSaveTimer);
                
                autoSaveTimer = setTimeout(() => {
                    self.autoSaveForm($form);
                }, 5000);
            });
        },

        /**
         * Auto-save form
         */
        autoSaveForm: function($form) {
            const formData = $form.serialize();
            
            $.ajax({
                url: bp_share_admin_vars.ajax_url,
                type: 'POST',
                data: formData + '&action=bp_share_auto_save&nonce=' + bp_share_admin_vars.nonce,
                success: (response) => {
                    if (response.success) {
                        this.showNotification(bp_share_admin_vars.strings.saved, 'success', 2000);
                        this.state.unsavedChanges = false;
                    }
                },
                error: () => {
                    console.warn('Auto-save failed');
                }
            });
        },

        /**
         * Handle toggle changes
         */
        handleToggleChange: function($toggle) {
            const toggleId = $toggle.attr('id');
            const isChecked = $toggle.is(':checked');

            // Handle specific toggle dependencies
            switch (toggleId) {
                case 'bp_share_services_enable':
                    $('#social_share_logout_wrap').toggle(isChecked);
                    break;
                    
                // Add more dependencies as needed
            }

            // Show confirmation for important toggles
            if ($toggle.data('confirm')) {
                const confirmMessage = $toggle.data('confirm');
                if (!confirm(confirmMessage)) {
                    $toggle.prop('checked', !isChecked).trigger('change');
                }
            }

            this.markAsChanged();
        },

        /**
         * Handle form submission
         */
        handleFormSubmit: function(e, $button) {
            if (this.state.submitting) {
                e.preventDefault();
                return false;
            }

            const $form = $button.closest('form');
            this.showFormLoading($form, $button);
        },

        /**
         * Show form loading state
         */
        showFormLoading: function($form, $button = null) {
            if ($button) {
                const originalText = $button.text();
                $button.prop('disabled', true)
                       .text(bp_share_admin_vars.strings.saving)
                       .data('original-text', originalText);
            }
            
            $form.addClass(this.config.classes.loading);
        },

        /**
         * Hide form loading state
         */
        hideFormLoading: function($form, $button = null) {
            if ($button) {
                const originalText = $button.data('original-text') || 'Save';
                $button.prop('disabled', false).text(originalText);
            }
            
            $form.removeClass(this.config.classes.loading);
        },

        /**
         * Handle reset functionality
         */
        handleReset: function(e, $button) {
            e.preventDefault();
            
            const confirmMessage = $button.data('confirm') || bp_share_admin_vars.strings.confirm_reset;
            
            if (!confirm(confirmMessage)) {
                return;
            }

            $button.prop('disabled', true).text(bp_share_admin_vars.strings.loading);

            this.callServiceAPI('bp_share_reset_settings', {})
                .then(() => {
                    this.showNotification('Settings reset successfully', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                })
                .catch((error) => {
                    this.showNotification('Failed to reset settings', 'error');
                    console.error('Reset error:', error);
                })
                .finally(() => {
                    $button.prop('disabled', false).text('Reset to Defaults');
                });
        },

        /**
         * Handle tab navigation
         */
        handleTabClick: function(e, $tab) {
            // Add loading indicator
            $tab.addClass(this.config.classes.loading);
            
            // Handle smooth navigation
            const tabUrl = $tab.attr('href');
            if (tabUrl && tabUrl.includes('#')) {
                e.preventDefault();
                this.handleHashNavigation(tabUrl);
            }
        },

        /**
         * Handle hash-based navigation
         */
        handleHashNavigation: function(url) {
            const hash = url.split('#')[1];
            if (hash) {
                // Smooth scroll to target
                const $target = $('#' + hash);
                if ($target.length) {
                    $('html, body').animate({
                        scrollTop: $target.offset().top - 100
                    }, 500);
                }
            }
        },

        /**
         * Setup responsive menu
         */
        setupResponsiveMenu: function() {
            const self = this;
            
            // Close menu when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.bp-share-tabs-section').length) {
                    self.toggleResponsiveMenu(false);
                }
            });
        },

        /**
         * Toggle responsive menu
         */
        toggleResponsiveMenu: function(show) {
            const $navTabs = $(this.config.selectors.navTabs);
            const $toggle = $(this.config.selectors.responsiveToggle);
            
            if (show) {
                $navTabs.addClass('mobile-open');
                $toggle.prop('checked', true);
            } else {
                $navTabs.removeClass('mobile-open');
                $toggle.prop('checked', false);
            }
        },

        /**
         * Setup accessibility features
         */
        setupAccessibility: function() {
            // Add ARIA labels to interactive elements
            this.addAriaLabels();
            
            // Setup keyboard navigation
            this.setupKeyboardNavigation();
            
            // Setup screen reader announcements
            this.setupScreenReaderAnnouncements();
        },

        /**
         * Add ARIA labels
         */
        addAriaLabels: function() {
            // Toggle switches
            $(this.config.selectors.toggleSwitch).each(function() {
                const $input = $(this);
                const label = $input.closest('.bp-share-form-section')
                                   .find('.bp-share-section-title').text();
                if (label) {
                    $input.attr('aria-label', label);
                }
            });

            // Tab navigation
            $(this.config.selectors.tabs).each(function() {
                const $tab = $(this);
                const isActive = $tab.hasClass('nav-tab-active');
                $tab.attr('aria-selected', isActive);
            });
        },

        /**
         * Setup keyboard navigation
         */
        setupKeyboardNavigation: function() {
            // Tab navigation with arrow keys
            $(this.config.selectors.tabs).on('keydown', function(e) {
                const $current = $(this);
                let $next;

                switch(e.key) {
                    case 'ArrowLeft':
                    case 'ArrowUp':
                        e.preventDefault();
                        $next = $current.parent().prev().find('.nav-tab');
                        if (!$next.length) {
                            $next = $('.nav-tab').last();
                        }
                        $next.focus();
                        break;
                        
                    case 'ArrowRight':
                    case 'ArrowDown':
                        e.preventDefault();
                        $next = $current.parent().next().find('.nav-tab');
                        if (!$next.length) {
                            $next = $('.nav-tab').first();
                        }
                        $next.focus();
                        break;
                        
                    case 'Home':
                        e.preventDefault();
                        $('.nav-tab').first().focus();
                        break;
                        
                    case 'End':
                        e.preventDefault();
                        $('.nav-tab').last().focus();
                        break;
                }
            });
        },

        /**
         * Setup screen reader announcements
         */
        setupScreenReaderAnnouncements: function() {
            // Create aria-live region if it doesn't exist
            if (!$('#bp-share-announcements').length) {
                $('body').append('<div id="bp-share-announcements" aria-live="polite" aria-atomic="true" class="screen-reader-text"></div>');
            }
        },

        /**
         * Announce to screen readers
         */
        announceToScreenReader: function(message) {
            $('#bp-share-announcements').text(message);
        },

        /**
         * Setup notifications system
         */
        setupNotifications: function() {
            // Create notification container if it doesn't exist
            if (!$('.bp-share-notifications').length) {
                $('body').append('<div class="bp-share-notifications"></div>');
            }

            // Auto-hide existing notices
            $(this.config.selectors.notice).each(function() {
                const $notice = $(this);
                setTimeout(() => {
                    $notice.fadeOut(500);
                }, 5000);
            });
        },

        /**
         * Show notification
         */
        showNotification: function(message, type = 'info', duration = 4000) {
            const $notification = $(`
                <div class="bp-share-notification bp-share-notification-${type}">
                    <div class="notification-content">
                        <span class="notification-message">${message}</span>
                        <button class="notification-dismiss">&times;</button>
                    </div>
                </div>
            `);

            $('.bp-share-notifications').append($notification);
            
            // Animate in
            $notification.slideDown(300);
            
            // Auto-dismiss
            setTimeout(() => {
                $notification.slideUp(300, function() {
                    $(this).remove();
                });
            }, duration);

            // Manual dismiss
            $notification.find('.notification-dismiss').on('click', function() {
                $notification.slideUp(300, function() {
                    $(this).remove();
                });
            });

            // Announce to screen readers
            this.announceToScreenReader(message);
        },

        /**
         * Dismiss notice
         */
        dismissNotice: function($button) {
            $button.closest('.bp-share-notice').fadeOut(300, function() {
                $(this).remove();
            });
        },

        /**
         * Setup preview updates
         */
        setupPreviewUpdates: function() {
            // Icon style changes
            $('input[name*="icon_style"]').on('change', () => {
                this.updateLivePreview();
            });

            // Initial preview update
            this.updateLivePreview();
        },

        /**
         * Setup page-specific functionality
         */
        setupPageSpecific: function() {
            const currentPage = this.getCurrentPage();
            
            switch (currentPage) {
                case 'general-settings':
                    this.setupGeneralSettings();
                    break;
                case 'share-settings':
                    this.setupShareSettings();
                    break;
                case 'icon-settings':
                    this.setupIconSettings();
                    break;
            }
        },

        /**
         * Get current admin page
         */
        getCurrentPage: function() {
            const params = new URLSearchParams(window.location.search);
            const page = params.get('page');
            const tab = params.get('tab');
            
            if (page === 'buddypress-share-settings') {
                return 'share-settings';
            } else if (page === 'buddypress-share-icons') {
                return 'icon-settings';
            } else if (tab === 'bpas_general_settings') {
                return 'general-settings';
            }
            
            return 'welcome';
        },

        /**
         * Setup general settings page
         */
        setupGeneralSettings: function() {
            // Page-specific functionality for general settings
            console.log('General settings page loaded');
        },

        /**
         * Setup share settings page
         */
        setupShareSettings: function() {
            // Page-specific functionality for share settings
            console.log('Share settings page loaded');
        },

        /**
         * Setup icon settings page
         */
        setupIconSettings: function() {
            // Page-specific functionality for icon settings
            console.log('Icon settings page loaded');
        },

        /**
         * Mark form as changed
         */
        markAsChanged: function() {
            this.state.unsavedChanges = true;
            $('form').addClass(this.config.classes.changed);
            
            // Show change indicator
            if (!$('.changes-indicator').length) {
                const $indicator = $('<div class="changes-indicator">' +
                    '<span class="dashicons dashicons-marker"></span>' +
                    bp_share_admin_vars.strings.settings_changed +
                    '</div>');
                
                $('form').first().prepend($indicator);
                $indicator.slideDown(300);
            }
        },

        /**
         * Handle before unload
         */
        handleBeforeUnload: function() {
            if (this.state.unsavedChanges && !this.state.submitting) {
                return bp_share_admin_vars.strings.settings_changed;
            }
        },

        /**
         * Handle window resize
         */
        handleResize: function() {
            const windowWidth = $(window).width();
            
            if (windowWidth > 768) {
                // Reset mobile menu state on larger screens
                this.toggleResponsiveMenu(false);
            }
        },

        /**
         * Utility: Debounce function
         */
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        /**
         * Utility: Get URL parameters
         */
        getUrlParams: function() {
            const params = {};
            window.location.search.slice(1).split('&').forEach(pair => {
                const [key, value] = pair.split('=');
                params[decodeURIComponent(key)] = decodeURIComponent(value || '');
            });
            return params;
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        try {
            BPShareAdmin.init();
        } catch (error) {
            console.error('Failed to initialize BP Share Admin:', error);
        }
    });

    // Expose for external access if needed
    window.BPShareAdmin = BPShareAdmin;

})(jQuery);