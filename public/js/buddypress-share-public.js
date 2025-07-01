/**
 * BuddyPress Activity Share - Main JavaScript
 * 
 * Handles all client-side functionality for the sharing interface.
 * Optimized for large sites with dynamic loading and improved performance.
 * 
 * @since      1.0.0
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/public/js
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */

const { __, _x, _n, sprintf } = wp.i18n;

(function($) {
    'use strict';

    /**
     * Store common selectors and settings for better performance
     */
    const SELECTORS = {
        shareButton: '.bp-activity-share-dropdown-toggle a.dropdown-toggle',
        shareDropdown: '.bp-activity-share-dropdown-toggle',
        shareModal: '#activity-share-modal',
        activityShareButton: '.bp-secondary-action.bp-activity-share-button',
        copyButton: '.bp-copy',
        popupOverlay: '.bp-share-service-popup-overlay',
        serviceButtons: '.service-buttons',
        postInSelect: '#post-in',
        reshareBtn: '.bp-activity-reshare-btn'
    };

    /**
     * Cache for loaded groups and friends to avoid repeated AJAX calls
     */
    const ShareCache = {
        groups: null,
        friends: null,
        loaded: false,

        /**
         * Get cached data or load via AJAX
         * @param {boolean} forceReload - Force reload from server
         * @returns {Promise} Promise resolving to share options data
         */
        getShareOptions: function(forceReload = false) {
            if (this.loaded && !forceReload) {
                return Promise.resolve({
                    groups: this.groups || [],
                    friends: this.friends || []
                });
            }

            return new Promise((resolve, reject) => {
                $.ajax({
                    url: bp_activity_share_vars.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'bp_get_user_share_options',
                        _ajax_nonce: bp_activity_share_vars.ajax_nonce
                    },
                    dataType: 'json',
                    success: (response) => {
                        if (response.success) {
                            this.groups = response.data.groups || [];
                            this.friends = response.data.friends || [];
                            this.loaded = true;
                            resolve(response.data);
                        } else {
                            reject(new Error(response.data?.message || __('Failed to load share options', 'buddypress-share')));
                        }
                    },
                    error: (xhr, status, error) => {
                        reject(new Error(__('Network error while loading share options', 'buddypress-share')));
                    }
                });
            });
        },

        /**
         * Clear cache
         */
        clear: function() {
            this.groups = null;
            this.friends = null;
            this.loaded = false;
        }
    };

    /**
     * Main activity share controller
     */
    const ActivityShare = {
        /**
         * Initialize all event handlers and setup
         */
        init: function() {
            this.setupDropdowns();
            this.setupModalHandling();
            this.setupSocialSharing();
            this.setupCopyLink();
            this.setupReshareOptions();
            this.setupDropdownPosition();
            this.setupReadMore();
            this.setupPerformanceOptimizations();
        },

        /**
         * Setup activity dropdown toggle functionality
         */
        setupDropdowns: function() {
            // Use event delegation for better performance
            $(document).on('click', SELECTORS.shareButton, this.handleDropdownToggle.bind(this));
            $(document).on('click', SELECTORS.popupOverlay, this.closeDropdown.bind(this));
            
            // Close dropdown when clicking outside
            $('body').on('mouseup', this.handleOutsideClick.bind(this));
            
            // Close dropdown when clicking share buttons (except copy)
            $(document).on('click', '.bp-share-activity-share-to-wrapper .bp-share', this.handleShareButtonClick.bind(this));
        },

        /**
         * Handle dropdown toggle with optimized DOM manipulation
         * @param {Event} e - Click event
         */
        handleDropdownToggle: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const $dropdown = $button.closest(SELECTORS.shareDropdown);
            
            // Toggle body class for styling
            $('body').toggleClass('bp-share-popup-active');
            
            // Show/hide service buttons
            const $serviceButtons = $dropdown.siblings(SELECTORS.serviceButtons);
            $serviceButtons.toggle(500);

            // Handle overlay
            const $overlay = $dropdown.siblings(SELECTORS.popupOverlay);
            if ($overlay.length) {
                $overlay.toggle();
            }

            // Handle options wrap
            const $optionsWrap = $('.bp-activity-more-options-wrap');
            $optionsWrap.toggleClass('bp-activity-share-popup-open');

            // Toggle selected state
            $dropdown.siblings('.selected').removeClass('selected');
            $dropdown.toggleClass('selected');
        },

        /**
         * Close dropdown and cleanup
         */
        closeDropdown: function() {
            $(SELECTORS.popupOverlay).hide();
            $('body').removeClass('bp-share-popup-active');
            $(SELECTORS.serviceButtons).hide();
            $('.bp-activity-more-options-wrap').removeClass('bp-activity-share-popup-open');
            $(SELECTORS.shareDropdown).removeClass('selected');
        },

        /**
         * Handle clicks outside dropdown
         * @param {Event} e - Mouse event
         */
        handleOutsideClick: function(e) {
            const $container = $(SELECTORS.shareDropdown + ' *');
            if (!$container.is(e.target)) {
                $(SELECTORS.shareDropdown).removeClass('selected');
            }
        },

        /**
         * Handle share button clicks
         * @param {Event} e - Click event
         */
        handleShareButtonClick: function(e) {
            if (!$(e.currentTarget).hasClass('bp-copy')) {
                $(SELECTORS.shareDropdown).removeClass('selected');
            }
        },

        /**
         * Setup modal handling for activity sharing with dynamic loading
         */
        setupModalHandling: function() {
            // Initialize Select2 with optimized settings
            this.initializeSelect2();
            
            // Handle click on share button to open modal
            $(document).on('click', SELECTORS.activityShareButton, this.handleShareButtonOpen.bind(this));
            
            // Handle form submission
            $(document).on('click', '.bp-activity-share-activity', this.submitShareActivity.bind(this));
            
            // Handle modal close
            $(document).on('click', '.bp-activity-share-close', this.closeModal.bind(this));
            
            // Handle modal events
            $(SELECTORS.shareModal).on('show.bs.modal', this.onModalShow.bind(this));
            $(SELECTORS.shareModal).on('hidden.bs.modal', this.onModalHidden.bind(this));
        },

        /**
         * Initialize Select2 with optimized configuration
         */
        initializeSelect2: function() {
            if ($(SELECTORS.postInSelect).length) {
                $(SELECTORS.postInSelect).select2({
                    dropdownParent: $(SELECTORS.shareModal),
                    placeholder: __('Select where to share...', 'buddypress-share'),
                    allowClear: false,
                    minimumResultsForSearch: 10 // Only show search if more than 10 options
                });
            }
        },

        /**
         * Handle modal show event - load groups and friends dynamically
         */
        onModalShow: function() {
            this.loadShareOptionsIfNeeded();
        },

        /**
         * Handle modal hidden event - cleanup
         */
        onModalHidden: function() {
            this.resetModal();
        },

        /**
         * Load share options (groups/friends) dynamically when needed
         */
        loadShareOptionsIfNeeded: function() {
            const $select = $(SELECTORS.postInSelect);
            const currentOptions = $select.find('option').length;
            
            // Only load if we don't have groups/friends options yet
            if (currentOptions <= 2) { // Only "My Profile" and "Message" options
                this.showLoadingState();
                
                ShareCache.getShareOptions()
                    .then(this.populateShareOptions.bind(this))
                    .catch(this.handleLoadError.bind(this))
                    .finally(this.hideLoadingState.bind(this));
            }
        },

        /**
         * Show loading state in modal
         */
        showLoadingState: function() {
            const $select = $(SELECTORS.postInSelect);
            $select.prop('disabled', true);
            $select.parent().addClass('loading');
        },

        /**
         * Hide loading state in modal
         */
        hideLoadingState: function() {
            const $select = $(SELECTORS.postInSelect);
            $select.prop('disabled', false);
            $select.parent().removeClass('loading');
        },

        /**
         * Populate select with loaded groups and friends
         * @param {Object} data - Groups and friends data
         */
        populateShareOptions: function(data) {
            const $select = $(SELECTORS.postInSelect);
            
            // Add groups if available
            if (data.groups && data.groups.length > 0) {
                const $groupOptgroup = $('<optgroup>').attr('label', __('Groups', 'buddypress-share'));
                
                data.groups.forEach(group => {
                    $groupOptgroup.append(
                        $('<option>')
                            .val(group.id)
                            .attr('data-type', 'group')
                            .text(group.name)
                    );
                });
                
                $select.append($groupOptgroup);
            }
            
            // Add friends if available
            if (data.friends && data.friends.length > 0) {
                const $friendOptgroup = $('<optgroup>').attr('label', __('Friends', 'buddypress-share'));
                
                data.friends.forEach(friend => {
                    $friendOptgroup.append(
                        $('<option>')
                            .val(friend.id)
                            .attr('data-type', 'user')
                            .text(friend.display_name)
                    );
                });
                
                $select.append($friendOptgroup);
            }
            
            // Refresh Select2 to show new options
            $select.trigger('change');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy').select2({
                    dropdownParent: $(SELECTORS.shareModal),
                    placeholder: __('Select where to share...', 'buddypress-share'),
                    allowClear: false,
                    minimumResultsForSearch: 10
                });
            }
        },

        /**
         * Handle loading error
         * @param {Error} error - Error object
         */
        handleLoadError: function(error) {
            console.error(__('Failed to load share options:', 'buddypress-share'), error);
            
            // Show user-friendly error message
            const $errorMsg = $('<div>')
                .addClass('bp-share-error-message')
                .text(__('Unable to load sharing options. Please try again.', 'buddypress-share'));
            
            $(SELECTORS.shareModal).find('.modal-header').after($errorMsg);
            
            // Remove error message after 5 seconds
            setTimeout(() => {
                $errorMsg.fadeOut(() => $errorMsg.remove());
            }, 5000);
        },

        /**
         * Handle share button open with optimized activity loading
         * @param {Event} e - Click event
         */
        handleShareButtonOpen: function(e) {
            e.preventDefault();

            let activityId = '';
            let activityHtml = '';
            const reshareShareActivity = bp_activity_share_vars.reshare_share_activity;
            const $button = $(e.currentTarget);

            // Handle different types of activities
            if ($button.closest('.bp-generic-meta.action').hasClass('photos-meta') ||
                $button.closest('.bp-generic-meta.action').hasClass('videos-meta') ||
                $button.closest('.bp-generic-meta.action').hasClass('documents-meta')) {

                activityId = $button.data('activity-id');
                this.fetchActivityContent(activityId, reshareShareActivity);
            } else {
                // Handle standard activities or posts
                if (typeof $button.data('post-id') !== 'undefined' && $button.data('post-id') !== '') {
                    activityId = $button.data('post-id');
                } else {
                    activityId = $button.data('activity-id');
                    activityHtml = $('#activity-' + activityId).html();

                    // Handle child activities if in child mode
                    if (reshareShareActivity === 'child') {
                        $('#activity-' + activityId + ' .activity-reshare-item-container').each(function() {
                            const idParts = $(this).attr('id').split("bp-reshare-activity-");
                            activityId = idParts[1];
                            activityHtml = $(this).html();

                            if ($(this).hasClass('post-reshare-item-container')) {
                                $('#bp-reshare-type').val('post_share');
                            } else {
                                $('#bp-reshare-type').val('activity_share');
                            }
                        });
                    }
                }

                // If we have HTML content to show
                if (activityHtml !== '') {
                    this.displayActivityInModal(activityId, activityHtml, reshareShareActivity);
                }
            }

            // Set activity ID for sharing
            $('#bp-reshare-activity-id').val(activityId);

            // Show modal
            $(SELECTORS.shareModal).modal('show');
        },

        /**
         * Fetch activity content via AJAX with error handling
         * @param {string} activityId - Activity ID
         * @param {string} reshareShareActivity - Share mode
         */
        fetchActivityContent: function(activityId, reshareShareActivity) {
            $.ajax({
                url: bp_activity_share_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'bp_share_get_activity_content',
                    activity_id: activityId,
                    _ajax_nonce: bp_activity_share_vars.ajax_nonce
                },
                dataType: 'json',
                beforeSend: () => {
                    this.showActivityLoadingState();
                },
                success: (response) => {
                    if (response.success && response.data.contents !== '') {
                        const activityHtml = $($.parseHTML(response.data.contents))
                            .filter("#activity-" + activityId).html();

                        this.displayActivityInModal(activityId, activityHtml, reshareShareActivity);
                    } else {
                        this.showActivityLoadError();
                    }
                },
                error: () => {
                    this.showActivityLoadError();
                },
                complete: () => {
                    this.hideActivityLoadingState();
                }
            });
        },

        /**
         * Show loading state for activity content
         */
        showActivityLoadingState: function() {
            const $container = $(SELECTORS.shareModal + ' .modal-body #bp-activity-share-widget-box-status-header');
            $container.html('<div class="bp-share-loading">' + __('Loading activity...', 'buddypress-share') + '</div>');
        },

        /**
         * Hide loading state for activity content
         */
        hideActivityLoadingState: function() {
            // Loading state will be replaced by content or error message
        },

        /**
         * Show error message for activity loading
         */
        showActivityLoadError: function() {
            const $container = $(SELECTORS.shareModal + ' .modal-body #bp-activity-share-widget-box-status-header');
            $container.html('<div class="bp-share-error">' + __('Failed to load activity content.', 'buddypress-share') + '</div>');
        },

        /**
         * Display activity content in the modal with optimized DOM manipulation
         * @param {string} activityId - Activity ID
         * @param {string} activityHtml - Activity HTML content
         * @param {string} reshareShareActivity - Share mode
         */
        displayActivityInModal: function(activityId, activityHtml, reshareShareActivity) {
            // Determine classes
            const activityUlClass = $('#activity-stream ul').attr('class') || 'activity-list item-list bp-list';
            const activityLiClass = $('#activity-stream ul li#activity-' + activityId).attr('class') ||
                'activity activity_update activity-item';

            const $container = $(SELECTORS.shareModal + ' .modal-body #bp-activity-share-widget-box-status-header');

            // Reset and set container class
            $container.attr('class', '').addClass(activityUlClass);

            // Add activity content
            $container.html('<div class="' + activityLiClass + '">' + activityHtml + '</div>');

            // Remove reshare container if in parent mode
            if (reshareShareActivity === 'parent') {
                $container.find('.activity-reshare-item-container').remove();
            }

            // Remove unnecessary elements for cleaner display
            const elementsToRemove = [
                '.activity-meta',
                '.post-footer',
                '.activity-comments',
                '.entry-button-wraper',
                '.bp-activity-post-footer'
            ];
            
            elementsToRemove.forEach(selector => {
                $container.find(selector).remove();
            });
        },

        /**
         * Submit activity share via AJAX with enhanced error handling
         */
        submitShareActivity: function(e) {
            e.preventDefault();

            const activityContent = $(SELECTORS.shareModal + ' #bp-activity-share-text').val();
            const activityId = $(SELECTORS.shareModal + ' #bp-reshare-activity-id').val();
            const activityUserId = $(SELECTORS.shareModal + ' #bp-reshare-activity-user-id').val();
            const component = $(SELECTORS.shareModal + ' #bp-reshare-activity-current-component').val();
            const activityIn = $(SELECTORS.shareModal + ' #post-in').val();
            const type = $(SELECTORS.shareModal + ' #bp-reshare-type').val();
            const activityInType = $(SELECTORS.shareModal + ' #post-in option:selected').data('type');
            
            // Validation
            if (null == activityIn || activityIn === '') { 
                $('.bp_activity_share_modal_error_message').show();
                return false;
            }

            // Show loading state
            this.showSubmitLoadingState();

            $.ajax({
                url: bp_activity_share_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'bp_activity_create_reshare_ajax',
                    activity_content: activityContent,
                    activity_id: activityId,
                    activity_user_id: activityUserId,
                    component: component,
                    activity_in: activityIn,
                    activity_in_type: activityInType,
                    type: type,
                    _ajax_nonce: bp_activity_share_vars.ajax_nonce
                },
                dataType: 'json',
                success: (response) => {
                    if (response.success) {
                        this.handleShareSuccess(activityId, response.data);
                    } else {
                        this.handleShareError(response.data?.message || __('Failed to share activity', 'buddypress-share'));
                    }
                },
                error: (xhr, status, error) => {
                    this.handleShareError(__('Network error occurred', 'buddypress-share'));
                },
                complete: () => {
                    this.hideSubmitLoadingState();
                }
            });
        },

        /**
         * Show loading state for form submission
         */
        showSubmitLoadingState: function() {
            const $button = $('.bp-activity-share-activity');
            $button.prop('disabled', true).text(__('Sharing...', 'buddypress-share'));
        },

        /**
         * Hide loading state for form submission
         */
        hideSubmitLoadingState: function() {
            const $button = $('.bp-activity-share-activity');
            $button.prop('disabled', false).text(__('Post', 'buddypress-share'));
        },

        /**
         * Handle successful share
         * @param {string} activityId - Activity ID
         * @param {Object} data - Response data
         */
        handleShareSuccess: function(activityId, data) {
            // Hide modal and clear input
            $(SELECTORS.shareModal).modal('hide');
            
            // Update share count
            const $shareCount = $('#bp-activity-reshare-count-' + activityId);
            if (data.share_count) {
                $shareCount.text(data.share_count);
            } else {
                const currentCount = parseInt($shareCount.text()) || 0;
                $shareCount.text(currentCount + 1);
            }

            // Update alternative share count display if present
            const $altShareCount = $('#bp-activity-share-' + activityId + ' .share-count');
            if ($altShareCount.length > 0) {
                const currentCount = parseInt($altShareCount.text()) || 0;
                $altShareCount.text(currentCount + 1);
            }

            // Show success message
            this.showSuccessMessage(__('Activity shared successfully!', 'buddypress-share'));
        },

        /**
         * Handle share error
         * @param {string} message - Error message
         */
        handleShareError: function(message) {
            console.error(__('Share error:', 'buddypress-share'), message);
            this.showErrorMessage(message);
        },

        /**
         * Show success message
         * @param {string} message - Success message
         */
        showSuccessMessage: function(message) {
            const $message = $('<div>')
                .addClass('bp-share-success-message notice notice-success')
                .text(message);
            
            $('body').prepend($message);
            
            setTimeout(() => {
                $message.fadeOut(() => $message.remove());
            }, 3000);
        },

        /**
         * Show error message
         * @param {string} message - Error message
         */
        showErrorMessage: function(message) {
            const $message = $('<div>')
                .addClass('bp-share-error-message notice notice-error')
                .text(message);
            
            $(SELECTORS.shareModal).find('.modal-body').prepend($message);
            
            setTimeout(() => {
                $message.fadeOut(() => $message.remove());
            }, 5000);
        },

        /**
         * Close modal and reset state
         */
        closeModal: function() {
            $(SELECTORS.shareModal).modal('hide');
        },

        /**
         * Reset modal to initial state
         */
        resetModal: function() {
            // Clear form
            $(SELECTORS.shareModal + ' #bp-activity-share-text').val('');
            
            // Hide error messages
            $('.bp_activity_share_modal_error_message').hide();
            $('.bp-share-error-message').remove();
            
            // Reset select to default
            $(SELECTORS.postInSelect).val('0').trigger('change');
            
            // Clear activity content
            $(SELECTORS.shareModal + ' #bp-activity-share-widget-box-status-header').empty();
        },

        /**
         * Setup social sharing functionality
         */
        setupSocialSharing: function() {
            $(document).on('click', '.bp-share.has-popup', (e) => {
                const displayAttr = $(e.currentTarget).attr('attr-display');
                if (displayAttr !== 'no-popup') {
                    e.preventDefault();
                    this.openSharePopup($(e.currentTarget).attr('href'));
                }
            });
        },

        /**
         * Open a sharing popup window with optimized positioning
         * @param {string} url - URL to open
         */
        openSharePopup: function(url) {
            // Find window boundaries
            const leftBoundary = window.screenLeft || window.screenX || 0;
            const topBoundary = window.screenTop || window.screenY || 0;

            // Calculate centered position
            const x = screen.width / 2 - 350 + leftBoundary;
            const y = screen.height / 2 - 225 + topBoundary;

            // Open popup with optimized settings
            const popup = window.open(url, 'share_popup', 'height=485,width=700,left=' + x + ',top=' + y + ',scrollbars=yes,resizable=yes');
            
            // Focus popup if it was successfully opened
            if (popup) {
                popup.focus();
            }
        },

        /**
         * Setup copy link functionality with clipboard API fallback
         */
        setupCopyLink: function() {
            $(document).on('click', SELECTORS.copyButton, (e) => {
                e.preventDefault();
                this.copyToClipboard($(e.currentTarget));
            });
        },

        /**
         * Copy text to clipboard with modern API and fallback
         * @param {jQuery} $button - Copy button element
         */
        copyToClipboard: function($button) {
            const copyText = $button.data('href');
            const $tooltip = $button.next();

            // Try modern clipboard API first
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(copyText)
                    .then(() => this.showCopySuccess($tooltip))
                    .catch(() => this.fallbackCopyToClipboard(copyText, $tooltip));
            } else {
                this.fallbackCopyToClipboard(copyText, $tooltip);
            }
        },

        /**
         * Fallback copy method for older browsers
         * @param {string} text - Text to copy
         * @param {jQuery} $tooltip - Tooltip element
         */
        fallbackCopyToClipboard: function(text, $tooltip) {
            // Create temporary textarea for copying
            const $tempTextarea = $('<textarea>')
                .val(text)
                .css({
                    position: 'fixed',
                    left: '-9999px',
                    top: '-9999px'
                });
            
            $('body').append($tempTextarea);

            // Select and copy text
            $tempTextarea.select();
            $tempTextarea[0].setSelectionRange(0, 99999); // For mobile devices

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    this.showCopySuccess($tooltip);
                } else {
                    this.showCopyError($tooltip);
                }
            } catch (err) {
                console.error(__('Copy failed:', 'buddypress-share'), err);
                this.showCopyError($tooltip);
            }

            // Remove temporary textarea
            $tempTextarea.remove();
        },

        /**
         * Show copy success tooltip
         * @param {jQuery} $tooltip - Tooltip element
         */
        showCopySuccess: function($tooltip) {
            $tooltip.removeClass('tooltip-hide').text(__('Link Copied!', 'buddypress-share'));
            
            setTimeout(() => {
                $tooltip.addClass('tooltip-hide');
            }, 2000);
        },

        /**
         * Show copy error tooltip
         * @param {jQuery} $tooltip - Tooltip element
         */
        showCopyError: function($tooltip) {
            $tooltip.removeClass('tooltip-hide').text(__('Copy failed', 'buddypress-share'));
            
            setTimeout(() => {
                $tooltip.addClass('tooltip-hide');
            }, 2000);
        },

        /**
         * Setup reshare options for modal
         */
        setupReshareOptions: function() {
            // Handle click on reshare button to set dropdown value
            $(document).on('click', SELECTORS.reshareBtn, (e) => {
                e.preventDefault();
                this.handleReshareOptionClick($(e.currentTarget));
            });

            // Handle cleaning up classes on close
            $('body').on('mouseup', this.handleReshareCleanup.bind(this));
            $('.bp-activity-share-activity, .activity-share-modal-close').on('click', this.handleReshareCleanup.bind(this));
        },

        /**
         * Handle reshare option click
         * @param {jQuery} $button - Clicked button
         */
        handleReshareOptionClick: function($button) {
            // Get reshare options
            let reshareOption = $button.data('reshare');
            const reshareOptionText = $button.attr('data-title');

            // Convert 'my-profile' to 0 for select value
            if (reshareOption === 'my-profile') {
                reshareOption = '0';
            }

            // Set select value and update display
            $(SELECTORS.postInSelect).val(reshareOption).trigger('change');

            // Update Select2 text display if initialized
            const $select2 = $(SELECTORS.postInSelect).next('.select2-container');
            if ($select2.length) {
                $select2.find('.select2-selection__rendered').text(reshareOptionText);
            }

            // Add class for styling
            const reshareOptionTextClass = reshareOptionText.toLowerCase().replace(/\s+/g, '-');
            $('.activity-share-modal')
                .addClass(reshareOptionTextClass)
                .data('reshareOptionTextClass', reshareOptionTextClass);
        },

        /**
         * Handle reshare cleanup
         * @param {Event} e - Event object
         */
        handleReshareCleanup: function(e) {
            const $container = $('.activity-share-modal');
            const reshareOptionTextClass = $container.data('reshareOptionTextClass');

            if (!$container.is(e.target) && $container.has(e.target).length === 0) {
                $container.removeClass(reshareOptionTextClass);
            }
        },

        /**
         * Setup dropdown position adjustment based on scroll position
         */
        setupDropdownPosition: function() {
            // Throttle scroll events for better performance
            let scrollTimeout;
            
            $(window).on('scroll', () => {
                if (scrollTimeout) {
                    clearTimeout(scrollTimeout);
                }
                
                scrollTimeout = setTimeout(this.toggleDropdownPosition, 16); // ~60fps
            });

            // Run on load
            this.toggleDropdownPosition();
        },

        /**
         * Toggle dropdown position based on scroll
         */
        toggleDropdownPosition: function() {
            const windowScrollTop = $(window).scrollTop();
            const windowHeight = $(window).height();
            const documentHeight = $(document).height();
            const isNearBottom = (windowScrollTop + windowHeight) >= (documentHeight - 100);

            $('.bp-activity-share-dropdown-menu').toggleClass('position-top', isNearBottom);
        },

        /**
         * Setup read more handling for shared activities
         */
        setupReadMore: function() {
            $(document).on('click', '.activity-reshare-item-container .activity-read-more a', this.handleReadMore.bind(this));
        },

        /**
         * Handle read more functionality
         * @param {Event} event - Click event
         */
        handleReadMore: function(event) {
            event.preventDefault();

            const $target = $(event.target);
            const activityId = $target.closest('.activity-reshare-item-container').data('bp-activity-id');
            const $content = $target.closest('div');
            const $readMore = $target.closest('span');

            $readMore.addClass('loading');

            // Use BuddyPress Nouveau AJAX API if available
            if (typeof bp !== 'undefined' && bp.Nouveau && bp.Nouveau.ajax) {
                bp.Nouveau.ajax({
                    action: 'get_single_activity_content',
                    id: activityId
                }, 'activity').done((response) => {
                    this.handleReadMoreSuccess(response, $content, $readMore);
                }).fail(() => {
                    this.handleReadMoreError($content, $readMore);
                });
            } else {
                // Fallback to custom AJAX
                this.fallbackReadMore(activityId, $content, $readMore);
            }
        },

        /**
         * Handle read more success
         * @param {Object} response - AJAX response
         * @param {jQuery} $content - Content element
         * @param {jQuery} $readMore - Read more element
         */
        handleReadMoreSuccess: function(response, $content, $readMore) {
            $readMore.removeClass('loading');

            // Remove any existing feedback
            $content.parent().find('.bp-feedback').remove();

            if (response.success === false) {
                // Show error feedback
                $content.after(response.data.feedback);
                $content.parent().find('.bp-feedback').hide().fadeIn(300);
            } else {
                // Replace content with full content
                $content.slideUp(300, () => {
                    $content.html(response.data.contents).slideDown(300);
                });
            }
        },

        /**
         * Handle read more error
         * @param {jQuery} $content - Content element
         * @param {jQuery} $readMore - Read more element
         */
        handleReadMoreError: function($content, $readMore) {
            $readMore.removeClass('loading');
            console.error(__('Failed to load full activity content', 'buddypress-share'));
        },

        /**
         * Fallback read more for older BuddyPress versions
         * @param {string} activityId - Activity ID
         * @param {jQuery} $content - Content element
         * @param {jQuery} $readMore - Read more element
         */
        fallbackReadMore: function(activityId, $content, $readMore) {
            // Implement fallback AJAX call if needed
            $readMore.removeClass('loading');
            console.warn(__('Read more functionality requires BuddyPress Nouveau', 'buddypress-share'));
        },

        /**
         * Setup performance optimizations
         */
        setupPerformanceOptimizations: function() {
            // Preload critical resources
            this.preloadCriticalResources();
            
            // Setup intersection observer for lazy loading
            this.setupLazyLoading();
            
            // Debounce resize events
            this.setupResizeHandler();
        },

        /**
         * Preload critical resources
         */
        preloadCriticalResources: function() {
            // Preload modal HTML structure if not already present
            if (!$(SELECTORS.shareModal).length) {
                // Modal will be loaded by PHP, this is just a placeholder for future enhancements
            }
        },

        /**
         * Setup lazy loading for share options
         */
        setupLazyLoading: function() {
            // Use Intersection Observer to load share options when modal is likely to be opened
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            // Preload share options when share buttons come into view
                            ShareCache.getShareOptions();
                        }
                    });
                }, { rootMargin: '100px' });

                // Observe share buttons
                $(SELECTORS.activityShareButton).each((index, element) => {
                    observer.observe(element);
                });
            }
        },

        /**
         * Setup debounced resize handler
         */
        setupResizeHandler: function() {
            let resizeTimeout;
            
            $(window).on('resize', () => {
                if (resizeTimeout) {
                    clearTimeout(resizeTimeout);
                }
                
                resizeTimeout = setTimeout(() => {
                    this.handleResize();
                }, 250);
            });
        },

        /**
         * Handle window resize
         */
        handleResize: function() {
            // Recalculate dropdown positions
            this.toggleDropdownPosition();
            
            // Adjust modal positioning if open
            if ($(SELECTORS.shareModal).hasClass('show')) {
                // Modal repositioning logic here if needed
            }
        }
    };

    /**
     * Initialize on document ready with error handling
     */
    $(document).ready(function() {
        try {
            ActivityShare.init();
        } catch (error) {
            console.error(__('Failed to initialize BuddyPress Activity Share:', 'buddypress-share'), error);
        }
    });

    // Expose ActivityShare for external access if needed
    window.BPActivityShare = ActivityShare;

})(jQuery);