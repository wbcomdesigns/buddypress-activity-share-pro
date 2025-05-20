/**
 * BuddyPress Activity Share - Main JavaScript
 * 
 * Handles all client-side functionality for the sharing interface.
 */

const { __, _x, _n, sprintf } = wp.i18n;

(function($) {

    'use strict';

    // Store common selectors and settings
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

    // Activity share controller
    const ActivityShare = {
        /**
         * Initialize all event handlers and setup
         */
        init: function () {
            this.setupDropdowns();
            this.setupModalHandling();
            this.setupSocialSharing();
            this.setupCopyLink();
            this.setupReshareOptions();
            this.setupDropdownPosition();
            this.setupReadMore();
        },

        /**
         * Setup activity dropdown toggle functionality
         */
        setupDropdowns: function () {
            // Toggle dropdown on button click
            $(document).on('click', SELECTORS.shareButton, function (e) {
                e.preventDefault();
                $('body').addClass('bp-share-popup-active');
                $(this).parent().parent().next(SELECTORS.serviceButtons).toggle(500);

                const overlay = $(this).parent().parent().next().next(SELECTORS.popupOverlay);
                if (overlay.length) {
                    overlay.show();
                }

                const optionsWrap = $('.bp-activity-more-options-wrap');
                if (optionsWrap.length > 0 && !optionsWrap.hasClass('bp-activity-share-popup-open')) {
                    optionsWrap.addClass('bp-activity-share-popup-open');
                }
            });

            // Close dropdown when clicking overlay
            $(document).on('click', SELECTORS.popupOverlay, function () {
                $(this).hide();
                $('body').removeClass('bp-share-popup-active');
                $(SELECTORS.serviceButtons).hide();

                const optionsWrap = $('.bp-activity-more-options-wrap');
                if (optionsWrap.length > 0 && optionsWrap.hasClass('bp-activity-share-popup-open')) {
                    optionsWrap.removeClass('bp-activity-share-popup-open');
                }
            });

            // Close dropdown when clicking outside
            $('body').on('mouseup', function (e) {
                const container = $(SELECTORS.shareDropdown + ' *');
                if (!container.is(e.target)) {
                    $(SELECTORS.shareDropdown).removeClass('selected');
                }
            });

            // Close dropdown when clicking share buttons (except copy)
            $(document).on('click', '.bp-share-activity-share-to-wrapper .bp-share', function () {
                if (!$(this).hasClass('bp-copy')) {
                    $(SELECTORS.shareDropdown).removeClass('selected');
                }
            });

            // Toggle selected state on dropdown click
            $(document).on('click', SELECTORS.shareButton, function (e) {
                e.preventDefault();
                const current = $(this).closest(SELECTORS.shareDropdown);
                current.siblings('.selected').removeClass('selected');
                current.toggleClass('selected');
            });
        },

        /**
         * Setup modal handling for activity sharing
         */
        setupModalHandling: function () {
            // Initialize Select2 for post-in dropdown
            $(SELECTORS.postInSelect).select2({
                dropdownParent: $(SELECTORS.shareModal)
            });

            // Handle click on share button to open modal
            $(document).on('click', SELECTORS.activityShareButton, function (e) {
                e.preventDefault();

                let activityId = '';
                let activityHtml = '';
                const reshareShareActivity = bp_activity_share_vars.reshare_share_activity;

                // Handle different types of activities (photos, videos, documents)
                if ($(this).parents('.bp-generic-meta.action').hasClass('photos-meta') ||
                    $(this).parents('.bp-generic-meta.action').hasClass('videos-meta') ||
                    $(this).parents('.bp-generic-meta.action').hasClass('documents-meta')) {

                    activityId = $(this).data('activity-id');
                    ActivityShare.fetchActivityContent(activityId, reshareShareActivity);
                } else {
                    // Handle standard activities or posts
                    if (typeof $(this).data('post-id') !== 'undefined' && $(this).data('post-id') !== '') {
                        activityId = $(this).data('post-id');
                    } else {
                        activityId = $(this).data('activity-id');
                        activityHtml = $('#activity-' + activityId).html();

                        // Handle child activities if in child mode
                        if (reshareShareActivity === 'child') {
                            $('#activity-' + activityId + ' .activity-reshare-item-container').each(function () {
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
                        ActivityShare.displayActivityInModal(activityId, activityHtml, reshareShareActivity);
                    }
                }

                // Show modal
                $(SELECTORS.shareModal).on('shown.bs.modal', function () {
                    $(SELECTORS.shareModal).modal('show');
                });

                // Set activity ID for sharing
                $('#bp-reshare-activity-id').val(activityId);
            });

            // Handle click on "Post" button
            $(document).on('click', '.bp-activity-share-activity', function (e) {
                e.preventDefault();
                ActivityShare.submitShareActivity();
            });

            // Handle click on "Discard" button
            $(document).on('click', '.bp-activity-share-close', function () {
                $(SELECTORS.shareModal).modal('hide');
            });
        },

        /**
         * Fetch activity content via AJAX
         */
        fetchActivityContent: function (activityId, reshareShareActivity) {
            $.ajax({
                url: bp_activity_share_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'bp_share_get_activity_content',
                    activity_id: activityId,
                    _ajax_nonce: bp_activity_share_vars.ajax_nonce
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.data.contents !== '') {
                        const activityHtml = $($.parseHTML(response.data.contents))
                            .filter("#activity-" + activityId).html();

                        ActivityShare.displayActivityInModal(activityId, activityHtml, reshareShareActivity);
                    }
                },
                error: function () {
                    console.error(__('Failed to fetch activity content', 'buddypress-share'));
                }
            });
        },

        /**
         * Display activity content in the modal
         */
        displayActivityInModal: function (activityId, activityHtml, reshareShareActivity) {
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

            // Remove unnecessary elements
            $(SELECTORS.shareModal + ' .modal-body .activity-meta, ' +
                SELECTORS.shareModal + ' .modal-body .post-footer, ' +
                SELECTORS.shareModal + ' .modal-body .activity-comments, ' +
                SELECTORS.shareModal + ' .modal-body .entry-button-wraper, ' +
                SELECTORS.shareModal + ' .modal-body .bp-activity-post-footer').remove();
        },

        /**
         * Submit activity share via AJAX
         */
        submitShareActivity: function () {
            const activityContent = $(SELECTORS.shareModal + ' #bp-activity-share-text').val();
            const activityId = $(SELECTORS.shareModal + ' #bp-reshare-activity-id').val();
            const activityUserId = $(SELECTORS.shareModal + ' #bp-reshare-activity-user-id').val();
            const component = $(SELECTORS.shareModal + ' #bp-reshare-activity-current-component').val();
            const activityIn = $(SELECTORS.shareModal + ' #post-in').val();
            const type = $(SELECTORS.shareModal + ' #bp-reshare-type').val();
            const activityInType = $(SELECTORS.shareModal + ' #post-in option:selected').data('type');
            
            if( null == activityIn ) { 
                $('.bp_activity_share_modal_error_message').show();
                return false;
            }

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
                success: function (response) {
                    if (response.success) {
                        // Hide modal and clear input
                        $(SELECTORS.shareModal).modal('hide');
                        $(SELECTORS.shareModal + ' #bp-activity-share-text').val('');

                        // Update share count
                        const $shareCount = $('#bp-activity-reshare-count-' + activityId);
                        const currentCount = parseInt($shareCount.text()) || 0;
                        $shareCount.text(currentCount + 1);

                        // Update alternative share count display if present
                        const $altShareCount = $('#bp-activity-share-' + activityId + ' .share-count');
                        if ($altShareCount.length > 0) {
                            $altShareCount.text(currentCount + 1);
                        }
                    } else {
                        console.error(__('Failed to share activity', 'buddypress-share'));
                    }
                },
                error: function () {
                    console.error(__('AJAX request failed', 'buddypress-share'));
                }
            });
        },

        /**
         * Setup social sharing functionality
         */
        setupSocialSharing: function () {
            $(document).on('click', '.bp-share.has-popup', function (e) {
                const displayAttr = $(this).attr('attr-display');
                if (displayAttr !== 'no-popup') {
                    e.preventDefault();
                    ActivityShare.openSharePopup($(this).attr('href'));
                }
            });
        },

        /**
         * Open a sharing popup window
         */
        openSharePopup: function (url) {
            // Find window boundaries
            const leftBoundary = window.screenLeft || window.screenX || 0;
            const topBoundary = window.screenTop || window.screenY || 0;

            // Calculate centered position
            const x = screen.width / 2 - 350 + leftBoundary;
            const y = screen.height / 2 - 225 + topBoundary;

            // Open popup
            window.open(url, '', 'height=485,width=700,left=' + x + ',top=' + y);
        },

        /**
         * Setup copy link functionality
         */
        setupCopyLink: function () {
            $(document).on('click', SELECTORS.copyButton, function (e) {
                e.preventDefault();

                const copyText = $(this).data('href');
                const tooltip = $(this).next();

                // Create temporary textarea for copying
                const tempTextarea = $('<textarea>');
                tempTextarea.val(copyText);
                $('body').append(tempTextarea);

                // Select and copy text
                tempTextarea.select();
                let message;

                try {
                    const successful = document.execCommand('copy');
                    message = successful ?
                        __('Copied!', 'buddypress-share') :
                        __('Copy failed', 'buddypress-share');
                } catch (err) {
                    message = __('Unable to copy', 'buddypress-share');
                    console.error(message, err);
                }

                // Remove temporary textarea
                tempTextarea.remove();

                // Show tooltip with copied message
                tooltip.removeClass('tooltip-hide');

                // Hide tooltip after 1 second
                setTimeout(function () {
                    tooltip.addClass('tooltip-hide');
                }, 1000);
            });
        },

        /**
         * Setup reshare options for modal
         */
        setupReshareOptions: function () {
            // Handle click on reshare button to set dropdown value
            $(document).on('click', SELECTORS.reshareBtn, function (e) {
                e.preventDefault();

                // Get reshare options
                let reshareOption = $(this).data('reshare');
                const reshareOptionText = $(this).attr('data-title');

                // Convert 'my-profile' to 0 for select value
                if (reshareOption === 'my-profile') {
                    reshareOption = '0';
                }

                // Set select value and update display
                $(SELECTORS.postInSelect).val(reshareOption).trigger('change');

                // Update Select2 text display
                $(SELECTORS.postInSelect)
                    .next('.select2-container')
                    .find('.select2-selection__rendered')
                    .text(reshareOptionText);

                // Add class for styling
                const reshareOptionTextClass = reshareOptionText.toLowerCase().replace(/\s+/g, '-');
                $('.activity-share-modal')
                    .addClass(reshareOptionTextClass)
                    .data('reshareOptionTextClass', reshareOptionTextClass);
            });

            // Handle cleaning up classes on close
            $('body').mouseup(function (e) {
                const container = $('.activity-share-modal');
                const reshareOptionTextClass = container.data('reshareOptionTextClass');

                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    container.removeClass(reshareOptionTextClass);
                }
            });

            $('.bp-activity-share-activity, .activity-share-modal-close').on('click', function (e) {
                e.preventDefault();
                const container = $('.activity-share-modal');
                const reshareOptionTextClass = container.data('reshareOptionTextClass');
                container.removeClass(reshareOptionTextClass);
            });
        },

        /**
         * Setup dropdown position adjustment based on scroll position
         */
        setupDropdownPosition: function () {
            function toggleDropdownPosition() {
                $('.bp-activity-share-dropdown-menu').each(function () {
                    const $dropdown = $(this);
                    const windowScrollTop = $(window).scrollTop();
                    const windowHeight = $(window).height();
                    const documentHeight = $(document).height();
                    const isNearBottom = (windowScrollTop + windowHeight) >= (documentHeight - 100);

                    $dropdown.toggleClass('position-top', isNearBottom);
                });
            }

            // Run on scroll
            $(window).scroll(toggleDropdownPosition);

            // Run on load
            toggleDropdownPosition();
        },

        /**
         * Setup read more handling for shared activities
         */
        setupReadMore: function () {
            $(document).on('click', '.activity-reshare-item-container .activity-read-more a', function (event) {
                event.preventDefault();

                const activityId = $(this).parents('.activity-reshare-item-container').data('bp-activity-id');
                const target = $(event.target);
                const content = target.closest('div');
                const readMore = target.closest('span');

                $(readMore).addClass('loading');

                // Use BuddyPress Nouveau AJAX API
                bp.Nouveau.ajax({
                    action: 'get_single_activity_content',
                    id: activityId
                }, 'activity').done(function (response) {
                    $(readMore).removeClass('loading');

                    // Remove any existing feedback
                    if (content.parent().find('.bp-feedback').length) {
                        content.parent().find('.bp-feedback').remove();
                    }

                    if (response.success === false) {
                        // Show error feedback
                        content.after(response.data.feedback);
                        content.parent().find('.bp-feedback').hide().fadeIn(300);
                    } else {
                        // Replace content with full content
                        $(content).slideUp(300).html(response.data.contents).slideDown(300);
                    }
                });
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        ActivityShare.init();
    });

})(jQuery);