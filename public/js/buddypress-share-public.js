/**
 * BuddyPress Activity Share - Main JavaScript (Fixed & Complete)
 * 
 * Handles all client-side functionality for the sharing interface.
 * FIXED: Modal close button and Select2 dropdown issues
 * 
 * @since      1.0.0
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/public/js
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */

(function($) {
    'use strict';

    /**
     * Store common selectors for better performance
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
     * Cache for loaded groups and friends
     */
    const ShareCache = {
        groups: null,
        friends: null,
        loaded: false,

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
                            reject(new Error(response.data?.message || 'Failed to load share options'));
                        }
                    },
                    error: () => {
                        reject(new Error('Network error while loading share options'));
                    }
                });
            });
        },

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
        
        init: function() {
            this.setupDropdowns();
            this.setupModalHandling();
            this.setupSocialSharing();
            this.setupCopyLink();
            this.setupReshareOptions();
            this.setupDropdownPosition();
        },

        /**
         * Setup activity dropdown functionality
         */
        setupDropdowns: function() {
            $(document).on('click', SELECTORS.shareButton, this.handleDropdownToggle.bind(this));
            $(document).on('click', SELECTORS.popupOverlay, this.closeDropdown.bind(this));
            $('body').on('mouseup', this.handleOutsideClick.bind(this));
            $(document).on('click', '.bp-share-activity-share-to-wrapper .bp-share', this.handleShareButtonClick.bind(this));
        },

        handleDropdownToggle: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const $dropdown = $button.closest(SELECTORS.shareDropdown);
            
            $('body').toggleClass('bp-share-popup-active');
            
            const $serviceButtons = $dropdown.siblings(SELECTORS.serviceButtons);
            $serviceButtons.toggle(500);

            const $overlay = $dropdown.siblings(SELECTORS.popupOverlay);
            if ($overlay.length) {
                $overlay.toggle();
            }

            const $optionsWrap = $('.bp-activity-more-options-wrap');
            $optionsWrap.toggleClass('bp-activity-share-popup-open');

            $dropdown.siblings('.selected').removeClass('selected');
            $dropdown.toggleClass('selected');
        },

        closeDropdown: function() {
            $(SELECTORS.popupOverlay).hide();
            $('body').removeClass('bp-share-popup-active');
            $(SELECTORS.serviceButtons).hide();
            $('.bp-activity-more-options-wrap').removeClass('bp-activity-share-popup-open');
            $(SELECTORS.shareDropdown).removeClass('selected');
        },

        handleOutsideClick: function(e) {
            const $container = $(SELECTORS.shareDropdown + ' *');
            if (!$container.is(e.target)) {
                $(SELECTORS.shareDropdown).removeClass('selected');
            }
        },

        handleShareButtonClick: function(e) {
            if (!$(e.currentTarget).hasClass('bp-copy')) {
                $(SELECTORS.shareDropdown).removeClass('selected');
            }
        },

        /**
         * FIXED: Setup modal handling with proper Bootstrap 4 support
         */
        setupModalHandling: function() {
            // Initialize Select2 properly
            this.initializeSelect2();
            
            // Modal event handlers
            $(document).on('click', SELECTORS.activityShareButton, this.handleShareButtonOpen.bind(this));
            $(document).on('click', '.bp-activity-share-activity', this.submitShareActivity.bind(this));
            $(document).on('click', '.bp-activity-share-close, .activity-share-modal-close', this.closeModal.bind(this));
            
            // Bootstrap modal events (both Bootstrap 4 and 5 compatibility)
            if (typeof $.fn.modal !== 'undefined') {
                $(SELECTORS.shareModal).on('show.bs.modal shown.bs.modal', this.onModalShow.bind(this));
                $(SELECTORS.shareModal).on('hide.bs.modal hidden.bs.modal', this.onModalHidden.bind(this));
            }
            
            // Backdrop and ESC key handling
            this.setupModalBackdropHandling();
        },

        /**
         * FIXED: Initialize Select2 with proper modal support
         */
        initializeSelect2: function() {
            if (typeof $.fn.select2 === 'undefined') {
                // Select2 not loaded, falling back to regular select
                return;
            }

            // Initialize Select2 when modal is shown
            $(document).on('shown.bs.modal', SELECTORS.shareModal, function() {
                const $select = $(SELECTORS.postInSelect);
                
                // Destroy existing Select2 if it exists
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }
                
                // Initialize with proper configuration
                $select.select2({
                    dropdownParent: $(SELECTORS.shareModal),
                    placeholder: 'Select where to share...',
                    allowClear: false,
                    minimumResultsForSearch: 10,
                    width: '100%',
                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });
            });

            // Fallback initialization for when modal is already visible
            $(document).ready(function() {
                setTimeout(function() {
                    const $select = $(SELECTORS.postInSelect);
                    if ($select.length && !$select.hasClass('select2-hidden-accessible')) {
                        $select.select2({
                            dropdownParent: $(SELECTORS.shareModal),
                            placeholder: 'Select where to share...',
                            allowClear: false,
                            minimumResultsForSearch: 10,
                            width: '100%'
                        });
                    }
                }, 100);
            });
        },

        /**
         * FIXED: Setup backdrop and ESC key handling
         */
        setupModalBackdropHandling: function() {
            // Handle backdrop clicks
            $(document).on('click', SELECTORS.shareModal, function(e) {
                if (e.target === this) {
                    $(this).modal('hide');
                }
            });
            
            // Handle ESC key
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27) { // ESC key
                    const $modal = $(SELECTORS.shareModal);
                    if ($modal.hasClass('show') || $modal.hasClass('in')) {
                        ActivityShare.closeModal();
                    }
                }
            });
        },

        onModalShow: function() {
            this.loadShareOptionsIfNeeded();
        },

        onModalHidden: function() {
            this.resetModal();
        },

        loadShareOptionsIfNeeded: function() {
            const $select = $(SELECTORS.postInSelect);
            const hasGroupsOptgroup = $select.find('#bp-share-groups-options').length > 0;
            const hasFriendsOptgroup = $select.find('#bp-share-friends-options').length > 0;
            
            // Only load if we have optgroups that need data
            if (hasGroupsOptgroup || hasFriendsOptgroup) {
                const groupsLoaded = $select.find('#bp-share-groups-options option').length > 0;
                const friendsLoaded = $select.find('#bp-share-friends-options option').length > 0;
                
                if (!groupsLoaded || !friendsLoaded) {
                    this.bpShareShowLoading();
                    
                    ShareCache.getShareOptions()
                        .then(this.bpShareLoadOptions.bind(this))
                        .catch(this.handleLoadError.bind(this))
                        .finally(this.bpShareHideLoading.bind(this));
                }
            }
        },

        bpShareShowLoading: function() {
            const $select = $(SELECTORS.postInSelect);
            $select.prop('disabled', true);
            $select.parent().addClass('loading');
        },

        bpShareHideLoading: function() {
            const $select = $(SELECTORS.postInSelect);
            $select.prop('disabled', false);
            $select.parent().removeClass('loading');
        },

        /**
         * FIXED: Populate share options with proper Select2 handling
         */
        bpShareLoadOptions: function(data) {
            const $select = $(SELECTORS.postInSelect);
            
            // Add groups to existing optgroup
            if (data.groups && data.groups.length > 0) {
                const $groupOptgroup = $select.find('#bp-share-groups-options');
                if ($groupOptgroup.length > 0) {
                    // Clear existing options
                    $groupOptgroup.empty();
                    
                    data.groups.forEach(group => {
                        $groupOptgroup.append(
                            $('<option>')
                                .val(group.id)
                                .attr('data-type', 'group')
                                .text(group.name)
                        );
                    });
                }
            }
            
            // Add friends to existing optgroup
            if (data.friends && data.friends.length > 0) {
                const $friendOptgroup = $select.find('#bp-share-friends-options');
                if ($friendOptgroup.length > 0) {
                    // Clear existing options
                    $friendOptgroup.empty();
                    
                    data.friends.forEach(friend => {
                        $friendOptgroup.append(
                            $('<option>')
                                .val(friend.id)
                                .attr('data-type', 'user')
                                .text(friend.display_name)
                        );
                    });
                }
            }
            
            // Refresh Select2 if it's initialized
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.trigger('change');
                $select.select2('destroy').select2({
                    dropdownParent: $(SELECTORS.shareModal),
                    placeholder: 'Select where to share...',
                    allowClear: false,
                    minimumResultsForSearch: 10,
                    width: '100%'
                });
            }
        },

        handleLoadError: function(error) {
            // Failed to load share options
            
            const $errorMsg = $('<div>')
                .addClass('bp-share-error-message')
                .text('Unable to load sharing options. Please try again.');
            
            $(SELECTORS.shareModal).find('.modal-header').after($errorMsg);
            
            setTimeout(() => {
                $errorMsg.fadeOut(() => $errorMsg.remove());
            }, 5000);
        },

        /**
         * FIXED: Handle share button open with proper modal showing
         */
        handleShareButtonOpen: function(e) {
            e.preventDefault();
            e.stopPropagation();

            let activityId = '';
            let activityHtml = '';
            const reshareShareActivity = bp_activity_share_vars.reshare_share_activity;
            const $button = $(e.currentTarget);

            // Handle different activity types
            if ($button.closest('.bp-generic-meta.action').hasClass('photos-meta') ||
                $button.closest('.bp-generic-meta.action').hasClass('videos-meta') ||
                $button.closest('.bp-generic-meta.action').hasClass('documents-meta')) {

                activityId = $button.data('activity-id');
                this.fetchActivityContent(activityId, reshareShareActivity);
            } else {
                if (typeof $button.data('post-id') !== 'undefined' && $button.data('post-id') !== '') {
                    activityId = $button.data('post-id');
                } else {
                    activityId = $button.data('activity-id');
                    activityHtml = $('#activity-' + activityId).html();

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

                if (activityHtml !== '') {
                    this.displayActivityInModal(activityId, activityHtml, reshareShareActivity);
                }
            }

            $('#bp-reshare-activity-id').val(activityId);
            
            // Show modal with proper method (Bootstrap 4 and 5 compatibility)
            this.showModal();
        },

        /**
         * FIXED: Show modal with proper Bootstrap compatibility
         */
        showModal: function() {
            const $modal = $(SELECTORS.shareModal);
            
            if (typeof $.fn.modal !== 'undefined') {
                $modal.modal('show');
            } else {
                // Fallback for manual modal handling
                $modal.addClass('show').removeClass('fade');
                $('body').addClass('modal-open');
                if (!$('.modal-backdrop').length) {
                    $('body').append('<div class="modal-backdrop fade show"></div>');
                }
            }
        },

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

        showActivityLoadingState: function() {
            const $container = $(SELECTORS.shareModal + ' .modal-body #bp-activity-share-widget-box-status-header');
            $container.html('<div class="bp-share-loading">Loading activity...</div>');
        },

        hideActivityLoadingState: function() {
            // Loading state will be replaced by content or error message
        },

        showActivityLoadError: function() {
            const $container = $(SELECTORS.shareModal + ' .modal-body #bp-activity-share-widget-box-status-header');
            $container.html('<div class="bp-share-error">Failed to load activity content.</div>');
        },

        displayActivityInModal: function(activityId, activityHtml, reshareShareActivity) {
            const activityUlClass = $('#activity-stream ul').attr('class') || 'activity-list item-list bp-list';
            const activityLiClass = $('#activity-stream ul li#activity-' + activityId).attr('class') ||
                'activity activity_update activity-item';

            const $container = $(SELECTORS.shareModal + ' .modal-body #bp-activity-share-widget-box-status-header');

            $container.attr('class', '').addClass(activityUlClass);
            $container.html('<div class="' + activityLiClass + '">' + activityHtml + '</div>');

            if (reshareShareActivity === 'parent') {
                $container.find('.activity-reshare-item-container').remove();
            }

            // Remove unnecessary elements
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

            this.bpShareButtonLoading();

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
                        this.handleShareError(response.data?.message || 'Failed to share activity');
                    }
                },
                error: () => {
                    this.handleShareError('Network error occurred');
                },
                complete: () => {
                    this.bpShareButtonReset();
                }
            });
        },

        bpShareButtonLoading: function() {
            const $button = $('.bp-activity-share-activity');
            $button.prop('disabled', true).text('Sharing...');
        },

        bpShareButtonReset: function() {
            const $button = $('.bp-activity-share-activity');
            $button.prop('disabled', false).text('Post');
        },

        handleShareSuccess: function(activityId, data) {
            this.closeModal();
            
            // Update share count
            const $shareCount = $('#bp-activity-reshare-count-' + activityId);
            if (data.share_count) {
                $shareCount.text(data.share_count);
            } else {
                const currentCount = parseInt($shareCount.text()) || 0;
                $shareCount.text(currentCount + 1);
            }

            // Update alternative share count display
            const $altShareCount = $('#bp-activity-share-' + activityId + ' .share-count');
            if ($altShareCount.length > 0) {
                const currentCount = parseInt($altShareCount.text()) || 0;
                $altShareCount.text(currentCount + 1);
            }

            this.showSuccessMessage('Activity shared successfully!');
        },

        handleShareError: function(message) {
            // Share error occurred
            this.showErrorMessage(message);
        },

        showSuccessMessage: function(message) {
            const $message = $('<div>')
                .addClass('bp-share-success-message notice notice-success')
                .text(message);
            
            $('body').prepend($message);
            
            setTimeout(() => {
                $message.fadeOut(() => $message.remove());
            }, 3000);
        },

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
         * FIXED: Close modal with proper Bootstrap compatibility
         */
        closeModal: function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            const $modal = $(SELECTORS.shareModal);
            
            if (typeof $.fn.modal !== 'undefined') {
                $modal.modal('hide');
            } else {
                // Fallback for manual modal handling
                $modal.removeClass('show').addClass('fade');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            }
        },

        /**
         * FIXED: Reset modal with proper Select2 handling
         */
        resetModal: function() {
            // Clear form fields
            $('#bp-activity-share-text').val('');
            $('.bp_activity_share_modal_error_message').hide();
            $('.bp-share-error-message').remove();
            
            // Reset select to default value
            const $select = $(SELECTORS.postInSelect);
            $select.val('0');
            
            // Trigger change for Select2
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.trigger('change');
            }
            
            // Clear activity content
            $('#bp-activity-share-widget-box-status-header').empty();
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

        openSharePopup: function(url) {
            const leftBoundary = window.screenLeft || window.screenX || 0;
            const topBoundary = window.screenTop || window.screenY || 0;

            const x = screen.width / 2 - 350 + leftBoundary;
            const y = screen.height / 2 - 225 + topBoundary;

            const popup = window.open(url, 'share_popup', 'height=485,width=700,left=' + x + ',top=' + y + ',scrollbars=yes,resizable=yes');
            
            if (popup) {
                popup.focus();
            }
        },

        /**
         * Setup copy link functionality
         */
        setupCopyLink: function() {
            $(document).on('click', SELECTORS.copyButton, (e) => {
                e.preventDefault();
                this.copyToClipboard($(e.currentTarget));
            });
        },

        copyToClipboard: function($button) {
            const copyText = $button.data('href');
            const $tooltip = $button.next();

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(copyText)
                    .then(() => this.showCopySuccess($tooltip))
                    .catch(() => this.fallbackCopyToClipboard(copyText, $tooltip));
            } else {
                this.fallbackCopyToClipboard(copyText, $tooltip);
            }
        },

        fallbackCopyToClipboard: function(text, $tooltip) {
            const $tempTextarea = $('<textarea>')
                .val(text)
                .css({
                    position: 'fixed',
                    left: '-9999px',
                    top: '-9999px'
                });
            
            $('body').append($tempTextarea);

            $tempTextarea.select();
            $tempTextarea[0].setSelectionRange(0, 99999);

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    this.showCopySuccess($tooltip);
                } else {
                    this.showCopyError($tooltip);
                }
            } catch (err) {
                // Copy failed
                this.showCopyError($tooltip);
            }

            $tempTextarea.remove();
        },

        showCopySuccess: function($tooltip) {
            $tooltip.removeClass('tooltip-hide').text('Link Copied!');
            
            setTimeout(() => {
                $tooltip.addClass('tooltip-hide');
            }, 2000);
        },

        showCopyError: function($tooltip) {
            $tooltip.removeClass('tooltip-hide').text('Copy failed');
            
            setTimeout(() => {
                $tooltip.addClass('tooltip-hide');
            }, 2000);
        },

        /**
         * Setup reshare options
         */
        setupReshareOptions: function() {
            $(document).on('click', SELECTORS.reshareBtn, (e) => {
                e.preventDefault();
                this.handleReshareOptionClick($(e.currentTarget));
            });

            $('body').on('mouseup', this.handleReshareCleanup.bind(this));
            $('.bp-activity-share-activity, .activity-share-modal-close').on('click', this.handleReshareCleanup.bind(this));
        },

        handleReshareOptionClick: function($button) {
            let reshareOption = $button.data('reshare');
            const reshareOptionText = $button.attr('data-title');

            if (reshareOption === 'my-profile') {
                reshareOption = '0';
            }

            $(SELECTORS.postInSelect).val(reshareOption).trigger('change');

            const $select2 = $(SELECTORS.postInSelect).next('.select2-container');
            if ($select2.length) {
                $select2.find('.select2-selection__rendered').text(reshareOptionText);
            }

            const reshareOptionTextClass = reshareOptionText.toLowerCase().replace(/\s+/g, '-');
            $('.activity-share-modal')
                .addClass(reshareOptionTextClass)
                .data('reshareOptionTextClass', reshareOptionTextClass);
        },

        handleReshareCleanup: function(e) {
            const $container = $('.activity-share-modal');
            const reshareOptionTextClass = $container.data('reshareOptionTextClass');

            if (!$container.is(e.target) && $container.has(e.target).length === 0) {
                $container.removeClass(reshareOptionTextClass);
            }
        },

        /**
         * Setup dropdown positioning
         */
        setupDropdownPosition: function() {
            let scrollTimeout;
            
            $(window).on('scroll', () => {
                if (scrollTimeout) {
                    clearTimeout(scrollTimeout);
                }
                
                scrollTimeout = setTimeout(this.toggleDropdownPosition, 16);
            });

            this.toggleDropdownPosition();
        },

        toggleDropdownPosition: function() {
            const windowScrollTop = $(window).scrollTop();
            const windowHeight = $(window).height();
            const documentHeight = $(document).height();
            const isNearBottom = (windowScrollTop + windowHeight) >= (documentHeight - 100);

            $('.bp-activity-share-dropdown-menu').toggleClass('position-top', isNearBottom);
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        try {
            ActivityShare.init();
            
            // Additional Bootstrap modal initialization for safety
            if (typeof $.fn.modal !== 'undefined' && $(SELECTORS.shareModal).length) {
                $(SELECTORS.shareModal).modal({
                    show: false,
                    backdrop: true,
                    keyboard: true
                });
            }
        } catch (error) {
            // Failed to initialize BuddyPress Activity Share
        }
    });

    // Expose for external access
    window.BPActivityShare = ActivityShare;

})(jQuery);