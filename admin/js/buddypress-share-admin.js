/**
 * BuddyPress Activity Share - Admin JavaScript (Complete & Fixed)
 * 
 * Essential functionality: drag/drop, AJAX, and color picker.
 * Clean, optimized code with proper error handling.
 * 
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin/js
 * @since      1.5.2
 */

(function($) {
    'use strict';

    var bpasI18n = (window.bpasAdmin && window.bpasAdmin.i18n) || {};

    /* ─── Toast ───────────────────────────────────────────────
     * window.bpasToast(message, tone) — non-blocking feedback.
     * Replaces native alert(); ux-foundation Rule 10.
     */
    function bpasGetToastHost() {
        var host = document.querySelector('.bpas-toast-host');
        if (!host) {
            host = document.createElement('div');
            host.className = 'bpas-toast-host';
            document.body.appendChild(host);
        }
        return host;
    }

    function bpasToast(message, tone) {
        tone = tone || 'info';
        var host = bpasGetToastHost();
        var el = document.createElement('div');
        el.className = 'bpas-toast bpas-toast--' + tone;
        el.setAttribute('role', 'status');
        el.textContent = String(message);
        host.appendChild(el);

        requestAnimationFrame(function() {
            el.classList.add('bpas-toast--visible');
        });

        window.setTimeout(function() {
            el.classList.remove('bpas-toast--visible');
            window.setTimeout(function() {
                if (el.parentNode) {
                    el.parentNode.removeChild(el);
                }
            }, 250);
        }, 3600);
    }

    window.bpasToast = bpasToast;

    /* ─── Confirm modal (returns a Promise) ───────────────────
     * window.bpasConfirm({ title, message, tone, confirmLabel, cancelLabel })
     * Replaces native confirm(); ESC = cancel, Enter = confirm, focus trap
     * on the confirm button, click-outside cancels.
     */
    function bpasConfirm(opts) {
        opts = opts || {};
        return new Promise(function(resolve) {
            var backdrop = document.createElement('div');
            backdrop.className = 'bpas-confirm-backdrop';

            var card = document.createElement('div');
            card.className = 'bpas-confirm';
            card.setAttribute('role', 'dialog');
            card.setAttribute('aria-modal', 'true');

            if (opts.title) {
                var title = document.createElement('h2');
                title.className = 'bpas-confirm__title';
                title.textContent = opts.title;
                card.appendChild(title);
            }

            var message = opts.message || bpasI18n.confirmDanger || '';
            if (message) {
                var desc = document.createElement('p');
                desc.className = 'bpas-confirm__desc';
                desc.textContent = message;
                card.appendChild(desc);
            }

            var actions = document.createElement('div');
            actions.className = 'bpas-confirm__actions';

            var cancelBtn = document.createElement('button');
            cancelBtn.type = 'button';
            cancelBtn.className = 'bpas-btn bpas-btn-secondary';
            cancelBtn.textContent = opts.cancelLabel || bpasI18n.confirmCancel || 'Cancel';

            var confirmBtn = document.createElement('button');
            confirmBtn.type = 'button';
            confirmBtn.className = 'bpas-btn ' + ('danger' === opts.tone ? 'bpas-btn-danger' : 'bpas-btn-primary');
            confirmBtn.textContent = opts.confirmLabel || bpasI18n.confirmContinue || 'Continue';

            actions.appendChild(cancelBtn);
            actions.appendChild(confirmBtn);
            card.appendChild(actions);
            backdrop.appendChild(card);
            document.body.appendChild(backdrop);

            function cleanup(result) {
                document.removeEventListener('keydown', onKey);
                if (backdrop.parentNode) {
                    backdrop.parentNode.removeChild(backdrop);
                }
                resolve(result);
            }

            function onKey(e) {
                if ('Escape' === e.key) { cleanup(false); }
                if ('Enter' === e.key) { cleanup(true); }
            }

            cancelBtn.addEventListener('click', function() { cleanup(false); });
            confirmBtn.addEventListener('click', function() { cleanup(true); });
            backdrop.addEventListener('click', function(e) {
                if (e.target === backdrop) { cleanup(false); }
            });
            document.addEventListener('keydown', onKey);
            confirmBtn.focus();
        });
    }

    window.bpasConfirm = bpasConfirm;

    /**
     * Admin functionality controller
     */
    const BPShareAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.setupDragDrop();
            this.setupColorPickers();
            this.setupFormHandling();
            this.setupToggleDependencies();
            this.setupNotifications();
            this.setupOnboarding();
            this.setupGenericConfirm();
        },

        /**
         * Generic destructive-action confirm.
         *
         * Any element that opts in with data-bpas-confirm gets a modal
         * confirmation before its default action. Playbook 11.1: this MUST
         * yield to elements that own a specific data-action handler (the
         * drag-drop service buttons, etc.) — those manage their own flow, so
         * we skip them here to avoid double-handling.
         */
        setupGenericConfirm: function() {
            $(document).on('click', '[data-bpas-confirm]', function(e) {
                var $el = $(this);

                // Yield to elements with their own data-action handler.
                if (typeof $el.attr('data-action') !== 'undefined') {
                    return;
                }

                // Allow the second click through once confirmed.
                if ($el.data('bpas-confirm-ok')) {
                    return;
                }

                e.preventDefault();

                var msg = $el.data('bpas-confirm') || bpasI18n.confirmDanger;
                var tone = $el.data('bpas-confirm-tone') || 'danger';

                bpasConfirm({ message: msg, tone: tone }).then(function(ok) {
                    if (!ok) {
                        return;
                    }
                    $el.data('bpas-confirm-ok', true);
                    if ($el.is('a') && $el.attr('href')) {
                        window.location.href = $el.attr('href');
                    } else if ($el.is('button') || $el.is('input')) {
                        var form = $el.closest('form').get(0);
                        if (!form) {
                            return;
                        }
                        // Preserve the clicked submit button's name/value so
                        // PHP handlers keyed off the button name still fire.
                        var btnName = $el.attr('name') || '';
                        var btnValue = $el.attr('value') || '1';
                        if (btnName) {
                            var hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = btnName;
                            hidden.value = btnValue;
                            form.appendChild(hidden);
                        }
                        form.submit();
                    }
                });
            });
        },

        /**
         * Setup drag and drop for social services
         */
        setupDragDrop: function() {
            if (!$('#drag_icon_ul').length || !$('#drag_social_icon').length) {
                return;
            }

            // Make enabled services sortable
            $('#drag_icon_ul').sortable({
                items: '.socialicon',
                placeholder: 'ui-sortable-placeholder',
                tolerance: 'pointer',
                connectWith: '#drag_social_icon',
                cursor: 'move',
                opacity: 0.8,
                start: function(event, ui) {
                    ui.item.addClass('sorting');
                    $('.no-services-message').hide();
                    ui.placeholder.height(ui.item.height());
                },
                stop: function(event, ui) {
                    ui.item.removeClass('sorting');
                    BPShareAdmin.handleServiceMove(ui.item, 'enable');
                    BPShareAdmin.updateNoServicesMessages();
                },
                over: function(event, ui) {
                    $(this).addClass('drag-over');
                },
                out: function(event, ui) {
                    $(this).removeClass('drag-over');
                }
            });

            // Make available services sortable
            $('#drag_social_icon').sortable({
                items: '.socialicon',
                placeholder: 'ui-sortable-placeholder',
                tolerance: 'pointer',
                connectWith: '#drag_icon_ul',
                cursor: 'move',
                opacity: 0.8,
                start: function(event, ui) {
                    ui.item.addClass('sorting');
                    $('.no-services-message').hide();
                    ui.placeholder.height(ui.item.height());
                },
                stop: function(event, ui) {
                    ui.item.removeClass('sorting');
                    BPShareAdmin.handleServiceMove(ui.item, 'disable');
                    BPShareAdmin.updateNoServicesMessages();
                },
                over: function(event, ui) {
                    $(this).addClass('drag-over');
                },
                out: function(event, ui) {
                    $(this).removeClass('drag-over');
                }
            });

            // Add visual feedback for drag operations
            this.setupDragVisualFeedback();
        },

        /**
         * Setup visual feedback for drag operations
         */
        setupDragVisualFeedback: function() {
            $('.enabled-services-list, .disabled-services-list').on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('drag-active');
            }).on('dragleave', function(e) {
                $(this).removeClass('drag-active');
            }).on('drop', function(e) {
                $(this).removeClass('drag-active');
            });
        },

        /**
         * Handle service move between lists
         */
        handleServiceMove: function($item, action) {
            const serviceName = $item.data('service') || $item.text().trim();
            const isInEnabledList = $item.closest('#drag_icon_ul').length > 0;
            const isInAvailableList = $item.closest('#drag_social_icon').length > 0;
            
            // Determine correct action based on final position
            let finalAction;
            if (isInEnabledList) {
                finalAction = 'enable';
            } else if (isInAvailableList) {
                finalAction = 'disable';
            } else {
                return; // Item not in either list
            }
            
            this.updateServiceStatus(serviceName, finalAction);
            this.updateServicesHiddenField();
        },

        /**
         * Update services hidden field for form submission
         */
        updateServicesHiddenField: function() {
            const enabledServices = {};
            $('#drag_icon_ul .socialicon[data-service]').each(function() {
                const service = $(this).data('service');
                const name = $(this).text().trim();
                if (service && name) {
                    enabledServices[service] = name;
                }
            });
            
            if ($('#bp_share_services_serialized').length) {
                $('#bp_share_services_serialized').val(JSON.stringify(enabledServices));
            }
        },

        /**
         * Update service status via AJAX
         */
        updateServiceStatus: function(serviceName, action) {
            if (!serviceName) {
                // Service name is required
                return;
            }

            const ajaxAction = action === 'enable' ? 'wss_social_icons' : 'wss_social_remove_icons';
            const dataField = action === 'enable' ? 'term_name' : 'icon_name';
            
            // Show loading indicator
            this.showLoadingIndicator(serviceName, action);
            
            $.ajax({
                url: bp_share_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: ajaxAction,
                    [dataField]: serviceName,
                    nonce: bp_share_admin_vars.nonce
                },
                timeout: 10000, // 10 second timeout
                success: (response) => {
                    if (response.success) {
                        this.showNotice(
                            serviceName + ' ' + (action === 'enable' ? 'activated' : 'deactivated') + ' successfully', 
                            'success'
                        );
                        // Service updated successfully
                    } else {
                        this.showNotice('Failed to update network: ' + (response.data?.message || 'Unknown error'), 'error');
                        // Service update failed
                        this.revertServiceMove(serviceName, action);
                    }
                },
                error: (xhr, status, error) => {
                    let errorMessage = 'Network error occurred';
                    if (status === 'timeout') {
                        errorMessage = 'Request timed out';
                    } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMessage = xhr.responseJSON.data.message;
                    }
                    
                    this.showNotice(errorMessage, 'error');
                    // AJAX error occurred
                    this.revertServiceMove(serviceName, action);
                },
                complete: () => {
                    this.hideLoadingIndicator(serviceName);
                }
            });
        },

        /**
         * Show loading indicator for network being moved
         */
        showLoadingIndicator: function(serviceName, action) {
            const $item = $(`.socialicon[data-service="${serviceName}"]`);
            $item.addClass('updating').append('<span class="spinner"></span>');
        },

        /**
         * Hide loading indicator
         */
        hideLoadingIndicator: function(serviceName) {
            const $item = $(`.socialicon[data-service="${serviceName}"]`);
            $item.removeClass('updating').find('.spinner').remove();
        },

        /**
         * Revert network move if AJAX fails
         */
        revertServiceMove: function(serviceName, action) {
            const $item = $(`.socialicon[data-service="${serviceName}"]`);
            const targetList = action === 'enable' ? '#drag_social_icon' : '#drag_icon_ul';
            
            // Move item back to original list
            $item.detach().appendTo(targetList);
            this.updateNoServicesMessages();
            this.updateServicesHiddenField();
        },

        /**
         * Update "no networks" messages based on list contents
         */
        updateNoServicesMessages: function() {
            // Check enabled services list
            const $enabledList = $('#drag_icon_ul');
            const $enabledItems = $enabledList.find('.socialicon[data-service]');
            let $enabledMessage = $enabledList.find('.no-services-message');
            
            if ($enabledItems.length === 0) {
                if ($enabledMessage.length === 0) {
                    $enabledMessage = $('<li class="no-services-message">No networks enabled. Drag networks from the inactive list to enable them.</li>');
                    $enabledList.append($enabledMessage);
                }
                $enabledMessage.show();
            } else {
                $enabledMessage.hide();
            }

            // Check available services list
            const $availableList = $('#drag_social_icon');
            const $availableItems = $availableList.find('.socialicon[data-service]');
            let $availableMessage = $availableList.find('.no-services-message');
            
            if ($availableItems.length === 0) {
                if ($availableMessage.length === 0) {
                    $availableMessage = $('<li class="no-services-message">All networks are active. Drag networks from the active list to disable them.</li>');
                    $availableList.append($availableMessage);
                }
                $availableMessage.show();
            } else {
                $availableMessage.hide();
            }
        },

        /**
         * Setup color pickers for icon settings
         */
        setupColorPickers: function() {
            if (!$.fn.wpColorPicker) {
                return;
            }

            $('.bp-share-color-picker').each(function() {
                const $this = $(this);
                const defaultColor = $this.val() || $this.data('default-color') || '#667eea';
                
                $this.wpColorPicker({
                    defaultColor: defaultColor,
                    change: function(event, ui) {
                        // Color change handling
                        const color = ui.color.toString();
                        $(this).val(color).trigger('change');
                        BPShareAdmin.showColorPreview(this, color);
                    },
                    clear: function() {
                        // Color clear handling
                        const defaultColor = $(this).data('default-color') || '#667eea';
                        $(this).val(defaultColor).trigger('change');
                        BPShareAdmin.showColorPreview(this, defaultColor);
                    },
                    hide: true,
                    palettes: [
                        '#667eea', '#764ba2', '#f093fb', '#4facfe',
                        '#43e97b', '#38ef7d', '#ffecd2', '#fcb69f',
                        '#ff9a9e', '#fecfef', '#fccb90', '#d57eeb'
                    ]
                });
            });
        },

        /**
         * Show color preview
         */
        showColorPreview: function(element, color) {
            const $preview = $(element).siblings('.color-preview');
            if ($preview.length === 0) {
                $(element).after('<div class="color-preview" style="width: 20px; height: 20px; border: 1px solid #ccc; margin-left: 10px; display: inline-block;"></div>');
            }
            $(element).siblings('.color-preview').css('background-color', color);
        },

        /**
         * Setup form handling
         */
        setupFormHandling: function() {
            // Handle form submissions with loading states
            $('form').on('submit', function(e) {
                const $form = $(this);
                const $submitBtn = $form.find('[type="submit"]');
                const originalValue = $submitBtn.val();
                
                // Prevent double submission
                if ($submitBtn.prop('disabled')) {
                    e.preventDefault();
                    return false;
                }
                
                $submitBtn.prop('disabled', true);
                $submitBtn.val(bp_share_admin_vars.strings.saving || 'Saving...');
                
                // Re-enable after timeout as fallback
                setTimeout(function() {
                    $submitBtn.prop('disabled', false);
                    $submitBtn.val(originalValue);
                }, 10000);
            });

            // Handle settings reset via the shared confirm modal (no native
            // confirm()). The reset link opts in with .reset-settings; it
            // does not own a data-action handler, so the generic
            // [data-bpas-confirm] path is not involved here.
            $(document).on('click', '.reset-settings', function(e) {
                var $el = $(this);
                if ($el.data('bpas-confirm-ok')) {
                    return;
                }
                e.preventDefault();
                bpasConfirm({
                    message: bpasI18n.confirmDanger || 'Are you sure you want to reset all settings? This cannot be undone.',
                    tone: 'danger'
                }).then(function(ok) {
                    if (!ok) {
                        return;
                    }
                    $el.data('bpas-confirm-ok', true);
                    if ($el.is('a') && $el.attr('href')) {
                        window.location.href = $el.attr('href');
                    } else {
                        var form = $el.closest('form').get(0);
                        if (form) { form.submit(); }
                    }
                });
            });
        },

        /**
         * Setup toggle dependencies (show/hide related options)
         */
        setupToggleDependencies: function() {
            // Handle main sharing enable/disable toggle
            $('#bp_share_services_enable').on('change', function() {
                const isEnabled = $(this).is(':checked');
                $('#logout_sharing_row').toggle(isEnabled);
                
                if (!isEnabled) {
                    $('#bp_share_services_logout_enable').prop('checked', false);
                }
            });

            // Handle icon style changes
            $('input[name="bpas_icon_color_settings[icon_style]"]').on('change', function() {
                const selectedStyle = $(this).val();
                BPShareAdmin.updateIconStylePreview(selectedStyle);
            });

            // Trigger on page load to set initial state
            $('#bp_share_services_enable').trigger('change');
            $('input[name="bpas_icon_color_settings[icon_style]"]:checked').trigger('change');
        },

        /**
         * Update icon style preview
         */
        updateIconStylePreview: function(style) {
            const $preview = $('.icon-style-preview');
            if ($preview.length === 0) {
                return;
            }
            
            $preview.removeClass('circle rec blackwhite baricon').addClass(style);
        },

        /**
         * Setup notification system
         */
        setupNotifications: function() {
            // Auto-dismiss existing notices after 5 seconds
            $('.notice').each(function() {
                const $notice = $(this);
                if (!$notice.hasClass('notice-persistent')) {
                    setTimeout(function() {
                        $notice.fadeOut(500, function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            });

            // Handle notice dismiss buttons
            $(document).on('click', '.notice-dismiss', function() {
                $(this).closest('.notice').fadeOut(300, function() {
                    $(this).remove();
                });
            });

            // Handle manual notice close buttons
            $(document).on('click', '.notice .close-button', function(e) {
                e.preventDefault();
                $(this).closest('.notice').fadeOut(300, function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Show admin notice
         */
        showNotice: function(message, type, duration) {
            type = type || 'info';
            duration = duration || 5000;
            
            // Remove existing notices of the same type
            $('.bp-share-notice-' + type).remove();
            
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible bp-share-notice-' + type + '">' +
                '<p>' + message + '</p>' +
                '<button type="button" class="notice-dismiss">' +
                '<span class="screen-reader-text">Dismiss this notice.</span>' +
                '</button>' +
                '</div>');
            
            // Insert after page title or at top of admin content
            const $target = $('.wrap h1').first();
            if ($target.length) {
                $target.after($notice);
            } else {
                $('.wrap').prepend($notice);
            }
            
            // Auto-dismiss after duration
            if (duration > 0) {
                setTimeout(function() {
                    $notice.fadeOut(function() {
                        $(this).remove();
                    });
                }, duration);
            }

            // Handle dismiss button
            $notice.find('.notice-dismiss').on('click', function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Show loading overlay
         */
        showLoadingOverlay: function(message) {
            message = message || bp_share_admin_vars.strings.loading || 'Loading...';
            
            if ($('.bp-share-loading-overlay').length) {
                return;
            }
            
            const $overlay = $('<div class="bp-share-loading-overlay">' +
                '<div class="bp-share-loading-content">' +
                '<div class="bp-share-spinner"></div>' +
                '<p>' + message + '</p>' +
                '</div>' +
                '</div>');
            
            $('body').append($overlay);
            $overlay.fadeIn(200);
        },

        /**
         * Hide loading overlay
         */
        hideLoadingOverlay: function() {
            $('.bp-share-loading-overlay').fadeOut(200, function() {
                $(this).remove();
            });
        },

        /**
         * Utility function to get URL parameters
         */
        getUrlParameter: function(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            const results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        },

        /**
         * Utility function to validate color codes
         */
        isValidColor: function(color) {
            if (!color) return false;
            
            // Test hex colors
            if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color)) {
                return true;
            }
            
            // Test named colors
            const namedColors = ['red', 'blue', 'green', 'yellow', 'orange', 'purple', 'pink', 'brown', 'black', 'white', 'gray', 'grey'];
            if (namedColors.includes(color.toLowerCase())) {
                return true;
            }
            
            return false;
        },

        /**
         * First-run onboarding: mark complete then continue.
         *
         * Both CTAs and the step links call bpas_complete_onboarding so the
         * welcome screen never auto-shows again, then navigate on.
         */
        setupOnboarding: function() {
            const $onboarding = $('.bpas-onboarding');
            if (!$onboarding.length) {
                return;
            }

            const complete = function(redirectUrl) {
                $.ajax({
                    url: bp_share_admin_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bpas_complete_onboarding',
                        nonce: bp_share_admin_vars.nonce
                    }
                }).always(function(response) {
                    let target = redirectUrl;
                    if (!target && response && response.data && response.data.redirect) {
                        target = response.data.redirect;
                    }
                    window.location.href = target || window.location.pathname + window.location.search.replace(/[?&]onboarding=1/, '');
                });
            };

            $('#bpas-onboarding-start').on('click', function(e) {
                e.preventDefault();
                complete();
            });

            $('#bpas-onboarding-skip').on('click', function(e) {
                e.preventDefault();
                complete();
            });

            // Clicking a step link marks onboarding done, then follows the link.
            $onboarding.on('click', '[data-bpas-onboarding-go]', function(e) {
                e.preventDefault();
                complete($(this).attr('href'));
            });
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        try {
            BPShareAdmin.init();
            
            // Add admin-specific styles
            if ($('body').hasClass('bp-activity-share-admin')) {
                $('body').addClass('bp-share-admin-loaded');
            }
            
            // Initialize services messages on first load
            setTimeout(function() {
                BPShareAdmin.updateNoServicesMessages();
            }, 100);
            
            // BuddyPress Share Admin initialized successfully
        } catch (error) {
            // Failed to initialize BP Share Admin
        }
    });

    // Expose for external access if needed
    window.BPShareAdmin = BPShareAdmin;

})(jQuery);