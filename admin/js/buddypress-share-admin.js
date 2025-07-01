/**
 * BuddyPress Activity Share - Admin JavaScript (Updated)
 * 
 * Simplified to essential functionality only: drag/drop, AJAX, and color picker.
 * Removes complex custom UI in favor of native WordPress patterns.
 * 
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin/js
 * @since      1.5.2
 */

(function($) {
    'use strict';

    /**
     * Simplified admin functionality controller
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
                start: function(event, ui) {
                    ui.item.addClass('sorting');
                    $('.no-services-message').hide();
                },
                stop: function(event, ui) {
                    ui.item.removeClass('sorting');
                    BPShareAdmin.handleServiceMove(ui.item, 'enable');
                    BPShareAdmin.updateNoServicesMessages();
                }
            });

            // Make available services sortable
            $('#drag_social_icon').sortable({
                items: '.socialicon',
                placeholder: 'ui-sortable-placeholder',
                tolerance: 'pointer',
                connectWith: '#drag_icon_ul',
                start: function(event, ui) {
                    ui.item.addClass('sorting');
                    $('.no-services-message').hide();
                },
                stop: function(event, ui) {
                    ui.item.removeClass('sorting');
                    BPShareAdmin.handleServiceMove(ui.item, 'disable');
                    BPShareAdmin.updateNoServicesMessages();
                }
            });
        },

        /**
         * Handle service move between lists
         * @param {jQuery} $item - Moved item
         * @param {string} action - 'enable' or 'disable'
         */
        handleServiceMove: function($item, action) {
            const serviceName = $item.text().trim();
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
        },

        /**
         * Update service status via AJAX
         * @param {string} serviceName - Service name
         * @param {string} action - 'enable' or 'disable'
         */
        updateServiceStatus: function(serviceName, action) {
            const ajaxAction = action === 'enable' ? 'wss_social_icons' : 'wss_social_remove_icons';
            const dataField = action === 'enable' ? 'term_name' : 'icon_name';
            
            $.ajax({
                url: bp_share_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: ajaxAction,
                    [dataField]: serviceName,
                    nonce: bp_share_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        BPShareAdmin.showNotice(
                            serviceName + (action === 'enable' ? ' enabled' : ' disabled') + ' successfully', 
                            'success'
                        );
                    } else {
                        BPShareAdmin.showNotice('Failed to update service', 'error');
                        console.error('Service update failed:', response);
                    }
                },
                error: function(xhr, status, error) {
                    BPShareAdmin.showNotice('Network error occurred', 'error');
                    console.error('AJAX error:', error);
                }
            });
        },

        /**
         * Update "no services" messages based on list contents
         */
        updateNoServicesMessages: function() {
            // Check enabled services list
            const $enabledList = $('#drag_icon_ul');
            if ($enabledList.find('.socialicon').length === 0) {
                if (!$enabledList.find('.no-services-message').length) {
                    $enabledList.append('<li class="no-services-message">No services enabled. Drag services from the available list to enable them.</li>');
                }
            } else {
                $enabledList.find('.no-services-message').remove();
            }

            // Check available services list
            const $availableList = $('#drag_social_icon');
            if ($availableList.find('.socialicon').length === 0) {
                if (!$availableList.find('.no-services-message').length) {
                    $availableList.append('<li class="no-services-message">All services are enabled. Drag services from the enabled list to disable them.</li>');
                }
            } else {
                $availableList.find('.no-services-message').remove();
            }
        },

        /**
         * Setup color pickers for icon settings
         */
        setupColorPickers: function() {
            if (!$.fn.wpColorPicker) {
                return;
            }

            $('.bp-share-color-picker').wpColorPicker({
                change: function(event, ui) {
                    // Color change handling if needed in the future
                },
                clear: function(event) {
                    // Color clear handling if needed in the future
                }
            });
        },

        /**
         * Setup form handling
         */
        setupFormHandling: function() {
            // Handle form submissions with loading states
            $('form').on('submit', function() {
                const $form = $(this);
                const $submitBtn = $form.find('[type="submit"]');
                const originalValue = $submitBtn.val();
                
                $submitBtn.prop('disabled', true);
                $submitBtn.val(bp_share_admin_vars.strings.saving || 'Saving...');
                
                // Re-enable after timeout as fallback
                setTimeout(function() {
                    $submitBtn.prop('disabled', false);
                    $submitBtn.val(originalValue);
                }, 5000);
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
            });

            // Trigger on page load to set initial state
            $('#bp_share_services_enable').trigger('change');
        },

        /**
         * Setup notification system
         */
        setupNotifications: function() {
            // Auto-dismiss existing notices after 5 seconds
            $('.notice').each(function() {
                const $notice = $(this);
                setTimeout(function() {
                    $notice.fadeOut(500);
                }, 5000);
            });

            // Handle notice dismiss buttons
            $(document).on('click', '.notice-dismiss', function() {
                $(this).closest('.notice').fadeOut(300, function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Show admin notice
         * @param {string} message - Notice message
         * @param {string} type - Notice type (success, error, warning, info)
         */
        showNotice: function(message, type) {
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');
            
            // Insert after page title
            $('.wrap h1').after($notice);
            
            // Auto-dismiss after 3 seconds
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);

            // Handle dismiss button
            $notice.find('.notice-dismiss').on('click', function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            });
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