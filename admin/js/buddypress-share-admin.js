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
                            serviceName + ' ' + (action === 'enable' ? 'enabled' : 'disabled') + ' successfully', 
                            'success'
                        );
                        // Service updated successfully
                    } else {
                        this.showNotice('Failed to update service: ' + (response.data?.message || 'Unknown error'), 'error');
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
         * Show loading indicator for service being moved
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
         * Revert service move if AJAX fails
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
         * Update "no services" messages based on list contents
         */
        updateNoServicesMessages: function() {
            // Check enabled services list
            const $enabledList = $('#drag_icon_ul');
            const $enabledItems = $enabledList.find('.socialicon[data-service]');
            let $enabledMessage = $enabledList.find('.no-services-message');
            
            if ($enabledItems.length === 0) {
                if ($enabledMessage.length === 0) {
                    $enabledMessage = $('<li class="no-services-message">No services enabled. Drag services from the available list to enable them.</li>');
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
                    $availableMessage = $('<li class="no-services-message">All services are enabled. Drag services from the enabled list to disable them.</li>');
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

            // Handle settings reset
            $(document).on('click', '.reset-settings', function(e) {
                if (!confirm('Are you sure you want to reset all settings? This action cannot be undone.')) {
                    e.preventDefault();
                    return false;
                }
            });

            // Auto-save functionality for certain fields
            this.setupAutoSave();
        },

        /**
         * Setup auto-save functionality
         */
        setupAutoSave: function() {
            let autoSaveTimeout;
            
            $('.auto-save').on('change input', function() {
                clearTimeout(autoSaveTimeout);
                const $field = $(this);
                
                autoSaveTimeout = setTimeout(function() {
                    BPShareAdmin.autoSaveField($field);
                }, 2000);
            });
        },

        /**
         * Auto-save individual field
         */
        autoSaveField: function($field) {
            const fieldName = $field.attr('name');
            const fieldValue = $field.val();
            
            if (!fieldName) return;
            
            $.ajax({
                url: bp_share_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'bp_share_auto_save',
                    field_name: fieldName,
                    field_value: fieldValue,
                    nonce: bp_share_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        BPShareAdmin.showNotice('Settings auto-saved', 'info', 2000);
                    }
                }
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
            
            // BuddyPress Share Admin initialized successfully
        } catch (error) {
            // Failed to initialize BP Share Admin
        }
    });

    // Expose for external access if needed
    window.BPShareAdmin = BPShareAdmin;

})(jQuery);