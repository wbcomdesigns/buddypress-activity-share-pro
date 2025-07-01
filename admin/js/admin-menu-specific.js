/**
 * BuddyPress Activity Share Pro - Admin Menu Specific JavaScript
 * 
 * Clean, organized JavaScript for all admin interface functionality
 * Separated from template files for better maintainability
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
        
        /**
         * Initialize all admin functionality
         */
        init: function() {
            this.setupTabNavigation();
            this.setupFormHandling();
            this.setupDragDrop();
            this.setupNotifications();
            this.setupColorPickers();
            this.setupToggles();
            this.setupModals();
            this.setupProgressIndicators();
            this.setupResponsiveMenu();
            this.setupAccessibility();
            this.setupAnalytics();
        },

        /**
         * Enhanced tab navigation with smooth transitions
         */
        setupTabNavigation: function() {
            // Smooth tab switching
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                const $this = $(this);
                const tabUrl = $this.attr('href');
                
                // Add loading state
                $this.addClass('loading');
                
                // Remove active state from other tabs
                $('.nav-tab').removeClass('nav-tab-active');
                $this.addClass('nav-tab-active');
                
                // Add transition effect
                $('.bp-share-admin-content').addClass('transitioning');
                
                // Navigate to tab after short delay for smooth effect
                setTimeout(() => {
                    window.location.href = tabUrl;
                }, 150);
            });

            // Add hover effects and tooltips
            $('.nav-tab').each(function() {
                const $tab = $(this);
                const tabText = $tab.text().trim();
                
                $tab.attr('title', `Navigate to ${tabText} section`);
                
                // Add ripple effect on click
                $tab.on('click', function(e) {
                    BPShareAdmin.createRippleEffect(e, this);
                });
            });

            // Update URL hash on tab change for better bookmarking
            this.updateUrlHash();
        },

        /**
         * Create ripple effect for better user feedback
         */
        createRippleEffect: function(event, element) {
            const $element = $(element);
            const $ripple = $('<span class="ripple"></span>');
            
            const rect = element.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = event.clientX - rect.left - size / 2;
            const y = event.clientY - rect.top - size / 2;
            
            $ripple.css({
                width: size,
                height: size,
                left: x,
                top: y
            });
            
            $element.append($ripple);
            
            setTimeout(() => {
                $ripple.remove();
            }, 600);
        },

        /**
         * Enhanced form handling with validation and auto-save
         */
        setupFormHandling: function() {
            // Auto-save functionality
            let autoSaveTimer;
            
            $('.bp-share-form input, .bp-share-form select, .bp-share-form textarea').on('change input', function() {
                const $form = $(this).closest('form');
                
                // Clear existing timer
                clearTimeout(autoSaveTimer);
                
                // Mark form as changed
                $form.addClass('has-changes');
                BPShareAdmin.showChangeIndicator($form);
                
                // Set auto-save timer (5 seconds after last change)
                autoSaveTimer = setTimeout(() => {
                    BPShareAdmin.autoSaveForm($form);
                }, 5000);
            });

            // Enhanced form submission with progress indication
            $('form').on('submit', function(e) {
                const $form = $(this);
                const $submitBtn = $form.find('[type="submit"]');
                
                // Validate form before submission
                if (!BPShareAdmin.validateForm($form)) {
                    e.preventDefault();
                    return false;
                }
                
                // Show loading state
                BPShareAdmin.showFormLoading($form, $submitBtn);
                
                // Clear auto-save timer
                clearTimeout(autoSaveTimer);
            });

            // Real-time validation
            this.setupRealTimeValidation();
        },

        /**
         * Show visual indicator for unsaved changes
         */
        showChangeIndicator: function($form) {
            if (!$form.find('.changes-indicator').length) {
                const $indicator = $('<div class="changes-indicator">' +
                    '<span class="dashicons dashicons-marker"></span>' +
                    'You have unsaved changes' +
                    '</div>');
                
                $form.prepend($indicator);
                $indicator.slideDown(300);
            }
        },

        /**
         * Auto-save form functionality
         */
        autoSaveForm: function($form) {
            const formData = $form.serialize();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData + '&action=bp_share_auto_save',
                success: function(response) {
                    if (response.success) {
                        BPShareAdmin.showNotification('Settings auto-saved', 'success', 2000);
                        $form.removeClass('has-changes');
                        $form.find('.changes-indicator').slideUp(300);
                    }
                },
                error: function() {
                    BPShareAdmin.showNotification('Auto-save failed', 'error', 3000);
                }
            });
        },

        /**
         * Form validation with helpful error messages
         */
        validateForm: function($form) {
            let isValid = true;
            const $requiredFields = $form.find('[required]');
            
            $requiredFields.each(function() {
                const $field = $(this);
                const value = $field.val();
                
                if (!value || value.trim() === '') {
                    BPShareAdmin.showFieldError($field, 'This field is required');
                    isValid = false;
                } else {
                    BPShareAdmin.clearFieldError($field);
                }
            });
            
            return isValid;
        },

        /**
         * Show field-specific error message
         */
        showFieldError: function($field, message) {
            $field.addClass('error');
            
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
            $field.removeClass('error');
            $field.siblings('.field-error').slideUp(300);
        },

        /**
         * Enhanced drag and drop with visual feedback
         */
        setupDragDrop: function() {
            if ($('.social_icon_section').length === 0) return;

            // Initialize sortable for enabled services
            this.initializeSortable();
            
            // Enhanced drag and drop with better visual feedback
            $('.socialicon').each(function() {
                $(this).attr('draggable', true);
            });

            // Global drag and drop handlers
            $(document).on('dragstart', '.socialicon', this.handleDragStart.bind(this));
            $(document).on('dragover', '.social-services-list', this.handleDragOver.bind(this));
            $(document).on('drop', '.social-services-list', this.handleDrop.bind(this));
            $(document).on('dragend', '.socialicon', this.handleDragEnd.bind(this));

            // Add touch support for mobile
            this.setupTouchDragDrop();
        },

        /**
         * Initialize sortable functionality for enabled services
         */
        initializeSortable: function() {
            $('#drag_icon_ul').sortable({
                items: '.socialicon',
                placeholder: 'sortable-placeholder',
                tolerance: 'pointer',
                start: function(event, ui) {
                    ui.placeholder.height(ui.item.height());
                    ui.item.addClass('sorting');
                },
                stop: function(event, ui) {
                    ui.item.removeClass('sorting');
                    BPShareAdmin.saveServiceOrder();
                }
            });
        },

        /**
         * Handle drag start with enhanced visual feedback
         */
        handleDragStart: function(e) {
            const $item = $(e.currentTarget);
            $item.addClass('dragging');
            
            // Add visual feedback to valid drop zones
            $('.social-services-list').addClass('drop-zone-active');
            
            // Store drag data
            e.originalEvent.dataTransfer.setData('text/plain', $item.text());
            e.originalEvent.dataTransfer.effectAllowed = 'move';
        },

        /**
         * Handle drag over with visual feedback
         */
        handleDragOver: function(e) {
            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            
            const $dropZone = $(e.currentTarget);
            $dropZone.addClass('drag-over');
        },

        /**
         * Handle drop with smooth animation
         */
        handleDrop: function(e) {
            e.preventDefault();
            
            const $dropZone = $(e.currentTarget);
            const $draggedItem = $('.socialicon.dragging');
            
            if ($draggedItem.length === 0) return;
            
            const serviceName = $draggedItem.text().trim();
            const isEnabling = $dropZone.attr('id') === 'drag_icon_ul';
            
            // Animate the move
            this.animateServiceMove($draggedItem, $dropZone, isEnabling, serviceName);
        },

        /**
         * Animate service move between lists
         */
        animateServiceMove: function($item, $dropZone, isEnabling, serviceName) {
            // Clone item for animation
            const $clone = $item.clone().addClass('moving');
            $dropZone.append($clone);
            
            // Remove original with fade effect
            $item.fadeOut(300, function() {
                $(this).remove();
            });
            
            // Animate clone in
            $clone.hide().fadeIn(300, function() {
                $clone.removeClass('moving');
                
                // Update via AJAX
                BPShareAdmin.updateServiceStatus(serviceName, isEnabling);
            });
        },

        /**
         * Update service status via AJAX
         */
        updateServiceStatus: function(serviceName, isEnabling) {
            const action = isEnabling ? 'wss_social_icons' : 'wss_social_remove_icons';
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: action,
                    term_name: serviceName,
                    icon_name: serviceName,
                    nonce: my_ajax_object.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const message = isEnabling ? 
                            `${serviceName} enabled successfully` : 
                            `${serviceName} disabled successfully`;
                        BPShareAdmin.showNotification(message, 'success');
                    } else {
                        BPShareAdmin.showNotification('Failed to update service', 'error');
                    }
                },
                error: function() {
                    BPShareAdmin.showNotification('Network error occurred', 'error');
                }
            });
        },

        /**
         * Enhanced notification system
         */
        setupNotifications: function() {
            // Create notification container if it doesn't exist
            if (!$('.bp-share-notifications').length) {
                $('body').append('<div class="bp-share-notifications"></div>');
            }

            // Auto-hide existing notices after 5 seconds
            $('.notice').each(function() {
                const $notice = $(this);
                setTimeout(() => {
                    $notice.fadeOut(500);
                }, 5000);
            });

            // Enhanced dismiss functionality
            $(document).on('click', '.notice-dismiss', function() {
                $(this).closest('.notice').fadeOut(300, function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Show custom notification
         */
        showNotification: function(message, type = 'info', duration = 4000) {
            const $notification = $(`
                <div class="bp-share-notification bp-share-notification-${type}">
                    <div class="notification-content">
                        <span class="notification-icon"></span>
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
        },

        /**
         * Enhanced color picker setup
         */
        setupColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.bp-share-color-picker').wpColorPicker({
                    change: function(event, ui) {
                        BPShareAdmin.updateColorPreview(event.target, ui.color.toString());
                    },
                    clear: function(event) {
                        BPShareAdmin.updateColorPreview(event.target, '');
                    }
                });
            }

            // Setup live preview for icon colors
            this.setupIconPreview();
        },

        /**
         * Update color preview in real-time
         */
        updateColorPreview: function(input, color) {
            const $input = $(input);
            const previewTarget = $input.data('preview-target');
            
            if (previewTarget) {
                $(previewTarget).css('background-color', color);
            }

            // Update live preview for icon settings
            if ($input.closest('.bp-share-icon-settings').length) {
                this.updateIconStylePreview();
            }
        },

        /**
         * Setup icon style preview
         */
        setupIconPreview: function() {
            // Create preview container if it doesn't exist
            if (!$('.bp-share-icon-preview').length) {
                const previewHtml = `
                    <div class="bp-share-icon-preview">
                        <h4>Live Preview</h4>
                        <div class="preview-icons">
                            <span class="preview-icon facebook">Facebook</span>
                            <span class="preview-icon twitter">Twitter</span>
                            <span class="preview-icon linkedin">LinkedIn</span>
                        </div>
                    </div>
                `;
                $('.bp-share-icon-settings').append(previewHtml);
            }

            // Update preview when style changes
            $('input[name*="icon_style"]').on('change', this.updateIconStylePreview.bind(this));
        },

        /**
         * Update icon style preview
         */
        updateIconStylePreview: function() {
            const selectedStyle = $('input[name*="icon_style"]:checked').val() || 'circle';
            const bgColor = $('#icon_bg_color').val() || '#667eea';
            const textColor = $('#icon_text_color').val() || '#ffffff';
            const hoverColor = $('#icon_hover_color').val() || '#5a6fd8';

            $('.preview-icon').removeClass('circle rec blackwhite baricon')
                              .addClass(selectedStyle)
                              .css({
                                  'background-color': bgColor,
                                  'color': textColor
                              });

            // Update hover styles
            const hoverStyles = `
                <style id="preview-hover-styles">
                    .preview-icon:hover {
                        background-color: ${hoverColor} !important;
                        color: ${textColor} !important;
                    }
                </style>
            `;
            
            $('#preview-hover-styles').remove();
            $('head').append(hoverStyles);
        },

        /**
         * Enhanced toggle switches
         */
        setupToggles: function() {
            // Custom toggle functionality
            $('.bp-share-toggle input[type="checkbox"]').on('change', function() {
                const $toggle = $(this);
                const $slider = $toggle.siblings('.bp-share-slider');
                
                // Add transition effect
                $slider.addClass('transitioning');
                
                setTimeout(() => {
                    $slider.removeClass('transitioning');
                }, 300);

                // Handle dependent options
                BPShareAdmin.handleToggleDependencies($toggle);
                
                // Show confirmation for important toggles
                if ($toggle.data('confirm')) {
                    BPShareAdmin.showToggleConfirmation($toggle);
                }
            });

            // Add keyboard support
            $('.bp-share-toggle input[type="checkbox"]').on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).click();
                }
            });
        },

        /**
         * Handle toggle dependencies (show/hide related options)
         */
        handleToggleDependencies: function($toggle) {
            const toggleId = $toggle.attr('id');
            const isChecked = $toggle.is(':checked');

            // Handle specific dependencies
            switch (toggleId) {
                case 'bp_share_services_enable':
                    $('#social_share_logout_wrap').toggle(isChecked);
                    break;
                    
                // Add more dependencies as needed
            }
        },

        /**
         * Show confirmation for important toggle changes
         */
        showToggleConfirmation: function($toggle) {
            const confirmMessage = $toggle.data('confirm');
            const isChecked = $toggle.is(':checked');
            
            const confirmation = confirm(confirmMessage);
            if (!confirmation) {
                // Revert toggle if user cancels
                $toggle.prop('checked', !isChecked).trigger('change');
            }
        },

        /**
         * Setup modal functionality
         */
        setupModals: function() {
            // Generic modal setup
            $('.bp-share-modal-trigger').on('click', function(e) {
                e.preventDefault();
                const modalId = $(this).data('modal');
                BPShareAdmin.openModal(modalId);
            });

            // Modal close handlers
            $('.bp-share-modal-close, .bp-share-modal-overlay').on('click', function() {
                BPShareAdmin.closeModal();
            });

            // Escape key to close modal
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    BPShareAdmin.closeModal();
                }
            });
        },

        /**
         * Open modal with animation
         */
        openModal: function(modalId) {
            const $modal = $(modalId);
            $modal.addClass('active');
            $('body').addClass('modal-open');
            
            // Focus first input in modal
            setTimeout(() => {
                $modal.find('input, select, textarea').first().focus();
            }, 300);
        },

        /**
         * Close modal with animation
         */
        closeModal: function() {
            $('.bp-share-modal').removeClass('active');
            $('body').removeClass('modal-open');
        },

        /**
         * Progress indicators for long-running operations
         */
        setupProgressIndicators: function() {
            // Show progress for form submissions
            this.setupFormProgress();
            
            // Show progress for AJAX operations
            this.setupAjaxProgress();
        },

        /**
         * Show form loading state
         */
        showFormLoading: function($form, $submitBtn) {
            const originalText = $submitBtn.text();
            
            $submitBtn.prop('disabled', true)
                     .html('<span class="spinner"></span> Saving...')
                     .data('original-text', originalText);
            
            $form.addClass('loading');
        },

        /**
         * Hide form loading state
         */
        hideFormLoading: function($form, $submitBtn) {
            const originalText = $submitBtn.data('original-text') || 'Save';
            
            $submitBtn.prop('disabled', false)
                     .text(originalText);
            
            $form.removeClass('loading');
        },

        /**
         * Setup responsive menu for mobile
         */
        setupResponsiveMenu: function() {
            $('#bp-share-toggle-btn').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.bp-share-nav-tabs').toggleClass('mobile-open', isChecked);
            });

            // Close menu when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.bp-share-tabs-section').length) {
                    $('#bp-share-toggle-btn').prop('checked', false);
                    $('.bp-share-nav-tabs').removeClass('mobile-open');
                }
            });

            // Handle window resize
            $(window).on('resize', this.handleResize.bind(this));
        },

        /**
         * Handle responsive layout changes
         */
        handleResize: function() {
            const windowWidth = $(window).width();
            
            if (windowWidth > 768) {
                // Reset mobile menu state on larger screens
                $('#bp-share-toggle-btn').prop('checked', false);
                $('.bp-share-nav-tabs').removeClass('mobile-open');
            }
        },

        /**
         * Enhanced accessibility features
         */
        setupAccessibility: function() {
            // Add ARIA labels to interactive elements
            this.addAriaLabels();
            
            // Setup keyboard navigation
            this.setupKeyboardNavigation();
            
            // Add screen reader announcements
            this.setupScreenReaderAnnouncements();
        },

        /**
         * Add appropriate ARIA labels
         */
        addAriaLabels: function() {
            $('.bp-share-toggle input').each(function() {
                const $input = $(this);
                const label = $input.closest('.bp-share-form-section')
                                   .find('.bp-share-section-title').text();
                if (label) {
                    $input.attr('aria-label', label);
                }
            });

            $('.nav-tab').each(function() {
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
            $('.nav-tab').on('keydown', function(e) {
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
            // Create aria-live region for announcements
            if (!$('#bp-share-announcements').length) {
                $('body').append('<div id="bp-share-announcements" aria-live="polite" aria-atomic="true" class="screen-reader-text"></div>');
            }
        },

        /**
         * Announce message to screen readers
         */
        announceToScreenReader: function(message) {
            $('#bp-share-announcements').text(message);
        },

        /**
         * Basic analytics setup for admin interactions
         */
        setupAnalytics: function() {
            // Track tab switches
            $('.nav-tab').on('click', function() {
                const tabName = $(this).text().trim();
                BPShareAdmin.trackEvent('tab_switch', { tab: tabName });
            });

            // Track toggle changes
            $('.bp-share-toggle input').on('change', function() {
                const toggleName = $(this).attr('id');
                const isEnabled = $(this).is(':checked');
                BPShareAdmin.trackEvent('toggle_change', { 
                    toggle: toggleName, 
                    enabled: isEnabled 
                });
            });

            // Track form submissions
            $('form').on('submit', function() {
                const formName = $(this).attr('id') || 'unknown';
                BPShareAdmin.trackEvent('form_submit', { form: formName });
            });
        },

        /**
         * Track custom events (can be extended for analytics integration)
         */
        trackEvent: function(eventName, eventData = {}) {
            // This can be extended to integrate with Google Analytics, 
            // Mixpanel, or other analytics services
            if (window.gtag) {
                window.gtag('event', eventName, eventData);
            }
            
            // For debugging
            if (window.console && window.console.log) {
                console.log('BP Share Admin Event:', eventName, eventData);
            }
        },

        /**
         * Update URL hash for better navigation
         */
        updateUrlHash: function() {
            const hash = window.location.hash;
            if (hash && $(`.nav-tab[href*="${hash}"]`).length) {
                $(`.nav-tab[href*="${hash}"]`).addClass('nav-tab-active');
            }
        },

        /**
         * Setup real-time form validation
         */
        setupRealTimeValidation: function() {
            // Email validation
            $('input[type="email"]').on('blur', function() {
                const email = $(this).val();
                if (email && !BPShareAdmin.isValidEmail(email)) {
                    BPShareAdmin.showFieldError($(this), 'Please enter a valid email address');
                } else {
                    BPShareAdmin.clearFieldError($(this));
                }
            });

            // URL validation
            $('input[type="url"]').on('blur', function() {
                const url = $(this).val();
                if (url && !BPShareAdmin.isValidUrl(url)) {
                    BPShareAdmin.showFieldError($(this), 'Please enter a valid URL');
                } else {
                    BPShareAdmin.clearFieldError($(this));
                }
            });
        },

        /**
         * Validate email format
         */
        isValidEmail: function(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        /**
         * Validate URL format
         */
        isValidUrl: function(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        },

        /**
         * Save service order when items are rearranged
         */
        saveServiceOrder: function() {
            const order = [];
            $('#drag_icon_ul .socialicon').each(function() {
                order.push($(this).text().trim());
            });

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bp_share_save_service_order',
                    order: order,
                    nonce: my_ajax_object.nonce
                },
                success: function(response) {
                    if (response.success) {
                        BPShareAdmin.showNotification('Service order saved', 'success', 2000);
                    }
                }
            });
        },

        /**
         * Setup touch support for drag and drop on mobile devices
         */
        setupTouchDragDrop: function() {
            let draggedElement = null;
            
            $('.socialicon').on('touchstart', function(e) {
                draggedElement = this;
                $(this).addClass('touch-dragging');
            });

            $('.social-services-list').on('touchmove', function(e) {
                e.preventDefault();
            });

            $('.social-services-list').on('touchend', function(e) {
                if (draggedElement) {
                    const $draggedElement = $(draggedElement);
                    const $dropZone = $(this);
                    
                    if ($draggedElement.closest('.social-services-list')[0] !== $dropZone[0]) {
                        const serviceName = $draggedElement.text().trim();
                        const isEnabling = $dropZone.attr('id') === 'drag_icon_ul';
                        
                        BPShareAdmin.animateServiceMove($draggedElement, $dropZone, isEnabling, serviceName);
                    }
                    
                    $draggedElement.removeClass('touch-dragging');
                    draggedElement = null;
                }
            });
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        try {
            BPShareAdmin.init();
            
            // Add some CSS for enhanced functionality
            BPShareAdmin.addDynamicStyles();
            
        } catch (error) {
            console.error('Failed to initialize BP Share Admin:', error);
        }
    });

    /**
     * Add dynamic styles for enhanced functionality
     */
    BPShareAdmin.addDynamicStyles = function() {
        const styles = `
            <style id="bp-share-dynamic-styles">
                .ripple {
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255,255,255,0.3);
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                }
                
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
                
                .changes-indicator {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    color: #856404;
                    padding: 10px 15px;
                    border-radius: 6px;
                    margin-bottom: 20px;
                    display: none;
                }
                
                .changes-indicator .dashicons {
                    color: #f39c12;
                    margin-right: 8px;
                }
                
                .field-error {
                    color: #dc3545;
                    font-size: 12px;
                    margin-top: 5px;
                    display: none;
                }
                
                .bp-share-notifications {
                    position: fixed;
                    top: 32px;
                    right: 20px;
                    z-index: 999999;
                    max-width: 400px;
                }
                
                .bp-share-notification {
                    background: #fff;
                    border-left: 4px solid #007cba;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    margin-bottom: 10px;
                    border-radius: 6px;
                    overflow: hidden;
                    display: none;
                }
                
                .bp-share-notification-success {
                    border-left-color: #28a745;
                }
                
                .bp-share-notification-error {
                    border-left-color: #dc3545;
                }
                
                .notification-content {
                    padding: 15px 20px;
                    display: flex;
                    align-items: center;
                }
                
                .notification-message {
                    flex: 1;
                    margin: 0 10px;
                }
                
                .notification-dismiss {
                    background: none;
                    border: none;
                    font-size: 18px;
                    cursor: pointer;
                    opacity: 0.7;
                }
                
                .notification-dismiss:hover {
                    opacity: 1;
                }
                
                .socialicon.dragging {
                    opacity: 0.5;
                    transform: scale(1.05);
                }
                
                .social-services-list.drag-over {
                    background: rgba(102, 126, 234, 0.1);
                    border-color: #667eea;
                }
                
                .socialicon.moving {
                    animation: moveIn 0.3s ease;
                }
                
                @keyframes moveIn {
                    from {
                        opacity: 0;
                        transform: translateY(-20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                .touch-dragging {
                    opacity: 0.7;
                    transform: scale(1.1);
                    z-index: 1000;
                }
                
                @media (max-width: 768px) {
                    .bp-share-notifications {
                        left: 10px;
                        right: 10px;
                        max-width: none;
                    }
                }
            </style>
        `;
        
        $('head').append(styles);
    };

    // Expose BPShareAdmin globally for external access
    window.BPShareAdmin = BPShareAdmin;

})(jQuery);