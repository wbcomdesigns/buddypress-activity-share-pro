jQuery(document).ready(function($) {
    'use strict';
    
    // Use lowercase prefix for JavaScript object
    const licenseObj = window['bp_activity_share_plugin'.toLowerCase() + 'License'];
    
    if (typeof licenseObj === 'undefined') {
        return;
    }
    
    // Show license message
    function showLicenseMessage(message, type) {
        const messageDiv = $('#bp_activity_share_plugin-license-message');
        messageDiv.removeClass('success error info').addClass(type).text(message).show();
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            messageDiv.fadeOut();
        }, 5000);
    }
    
    // Handle change license key button
    $('#bp_activity_share_plugin-change-license').on('click', function() {
        $('#bp_activity_share_plugin-license-display').hide();
        $('#bp_activity_share_plugin-license-input-wrapper').show();
        $('#bp_activity_share_plugin_license_key').focus();
        
        // Hide the main action buttons
        $('#bp_activity_share_plugin-license-actions').hide();
        
        // Store original key
        const originalKey = $('#bp_activity_share_plugin_license_key_hidden').val();
        $('#bp_activity_share_plugin_license_key_hidden').data('original-key', originalKey);
    });
    
    // Handle cancel change button
    $('#bp_activity_share_plugin-cancel-change').on('click', function() {
        $('#bp_activity_share_plugin-license-display').show();
        $('#bp_activity_share_plugin-license-input-wrapper').hide();
        $('#bp_activity_share_plugin_license_key').val('');
        
        // Show the main action buttons
        $('#bp_activity_share_plugin-license-actions').show();
        
        // Restore the original key to hidden field
        const originalKey = $('#bp_activity_share_plugin_license_key_hidden').data('original-key');
        $('#bp_activity_share_plugin_license_key_hidden').val(originalKey);
    });
    
    // Handle save change button
    $('#bp_activity_share_plugin-save-change').on('click', function(e) {
        e.preventDefault();
        
        const newKey = $('#bp_activity_share_plugin_license_key').val().trim();
        
        if (!newKey) {
            showLicenseMessage('Please enter a license key', 'error');
            return;
        }
        
        // Show saving message
        $(this).text('Saving...');
        
        // Use AJAX to save the license key directly
        $.ajax({
            url: licenseObj.ajax_url,
            type: 'POST',
            data: {
                action: 'save_license_key',
                bp_activity_share_plugin_license_key: newKey,
                nonce: licenseObj.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Reload the page to show the updated license
                    const url = new URL(window.location.href);
                    url.searchParams.set('updated', 'true');
                    window.location.href = url.toString();
                } else {
                    showLicenseMessage(response.data.message || 'Error saving license key', 'error');
                    $('#bp_activity_share_plugin-save-change').text('Save');
                }
            },
            error: function(xhr, status, error) {
                showLicenseMessage('Error saving license key', 'error');
                $('#bp_activity_share_plugin-save-change').text('Save');
            }
        });
    });
    
    // Get the actual license key for AJAX operations
    function getActualLicenseKey() {
        const inputVal = $('#bp_activity_share_plugin_license_key').val().trim();
        const hiddenVal = $('#bp_activity_share_plugin_license_key_hidden').val().trim();
        
        // If user has entered a new key, use that
        if (inputVal && !inputVal.includes('*')) {
            return inputVal;
        }
        // Otherwise use the stored key
        return hiddenVal;
    }
    
    // Activate license
    $('#bp_activity_share_plugin-activate-license').on('click', function() {
        const button = $(this);
        const licenseKey = getActualLicenseKey();
        const messageDiv = $('#bp_activity_share_plugin-license-message');
        
        if (!licenseKey) {
            showLicenseMessage('Please enter a license key', 'error');
            return;
        }
        
        button.prop('disabled', true).text(licenseObj.strings.activating);
        messageDiv.removeClass('success error info').addClass('info').text('Activating license...').show();
        
        $.ajax({
            url: licenseObj.ajax_url,
            type: 'POST',
            data: {
                action: 'bp_activity_share_plugin_activate_license',
                license_key: licenseKey,
                nonce: licenseObj.nonce
            },
            success: function(response) {
                if (response.success) {
                    showLicenseMessage(response.data.message, 'success');
                    // Update status display
                    $('#bp_activity_share_plugin-license-status').html(response.data.status_html);
                    // Reload page after a delay
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showLicenseMessage(response.data.message, 'error');
                }
            },
            error: function() {
                showLicenseMessage('Error activating license', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Activate License');
            }
        });
    });
    
    // Deactivate license
    $('#bp_activity_share_plugin-deactivate-license').on('click', function() {
        if (!confirm('Are you sure you want to deactivate your license?')) {
            return;
        }
        
        const button = $(this);
        const messageDiv = $('#bp_activity_share_plugin-license-message');
        
        button.prop('disabled', true).text(licenseObj.strings.deactivating);
        messageDiv.removeClass('success error info').addClass('info').text('Deactivating license...').show();
        
        $.ajax({
            url: licenseObj.ajax_url,
            type: 'POST',
            data: {
                action: 'bp_activity_share_plugin_deactivate_license',
                nonce: licenseObj.nonce
            },
            success: function(response) {
                if (response.success) {
                    showLicenseMessage(response.data.message, 'success');
                    // Update status display
                    $('#bp_activity_share_plugin-license-status').html(response.data.status_html);
                    // Reload page after a delay
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showLicenseMessage(response.data.message, 'error');
                }
            },
            error: function() {
                showLicenseMessage('Error deactivating license', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Deactivate License');
            }
        });
    });
    
    // Check license
    $('#bp_activity_share_plugin-check-license').on('click', function() {
        const button = $(this);
        const messageDiv = $('#bp_activity_share_plugin-license-message');
        
        button.prop('disabled', true).text(licenseObj.strings.checking);
        messageDiv.removeClass('success error info').addClass('info').text('Checking license...').show();
        
        $.ajax({
            url: licenseObj.ajax_url,
            type: 'POST',
            data: {
                action: 'bp_activity_share_plugin_check_license',
                nonce: licenseObj.nonce
            },
            success: function(response) {
                if (response.success) {
                    showLicenseMessage(response.data.message, 'success');
                    // Update status display
                    $('#bp_activity_share_plugin-license-status').html(response.data.status_html);
                } else {
                    showLicenseMessage(response.data.message, 'error');
                }
            },
            error: function() {
                showLicenseMessage('Error checking license', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Check License');
            }
        });
    });
    
    // Show message if updated
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('updated') === 'true') {
        showLicenseMessage('License key saved successfully', 'success');
        // Update button text temporarily
        const saveBtn = $('button[name="bp_activity_share_plugin_save_license"]');
        if (saveBtn.length) {
            const originalText = saveBtn.text();
            saveBtn.text('Saved');
            setTimeout(function() {
                saveBtn.text(originalText);
            }, 3000);
        }
    }
    
    // No need for form submission handler - let it submit normally
    
    // Handle save button click (for new installations)
    $('button[name="bp_activity_share_plugin_save_license"]').on('click', function(e) {
        e.preventDefault();
        
        const newKey = $('#bp_activity_share_plugin_license_key').val().trim();
        
        if (!newKey) {
            showLicenseMessage('Please enter a license key', 'error');
            return;
        }
        
        // Show saving message
        $(this).text('Saving...');
        
        // Use AJAX to save the license key
        $.ajax({
            url: licenseObj.ajax_url,
            type: 'POST',
            data: {
                action: 'save_license_key',
                bp_activity_share_plugin_license_key: newKey,
                nonce: licenseObj.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Reload the page to show the updated license
                    const url = new URL(window.location.href);
                    url.searchParams.set('updated', 'true');
                    window.location.href = url.toString();
                } else {
                    showLicenseMessage(response.data.message || 'Error saving license key', 'error');
                    $('button[name="bp_activity_share_plugin_save_license"]').text('Save License Key');
                }
            },
            error: function(xhr, status, error) {
                showLicenseMessage('Error saving license key', 'error');
                $('button[name="bp_activity_share_plugin_save_license"]').text('Save License Key');
            }
        });
    });
    
    // Prevent form submission on enter key in license field
    $('#bp_activity_share_plugin_license_key').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('button[name="bp_activity_share_plugin_save_license"]').click();
        }
    });
});