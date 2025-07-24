# BuddyPress Activity Share Pro - Developer Guide

## Table of Contents
1. [Introduction](#introduction)
2. [Architecture Overview](#architecture-overview)
3. [Hooks Reference](#hooks-reference)
4. [Filters Reference](#filters-reference)
5. [Custom Services](#custom-services)
6. [API Reference](#api-reference)
7. [Code Examples](#code-examples)
8. [Best Practices](#best-practices)

## Introduction

This guide provides developers with comprehensive documentation for extending and customizing BuddyPress Activity Share Pro. Learn how to use hooks, filters, and APIs to integrate the plugin with your custom solutions.

## Architecture Overview

### Plugin Structure
```
buddypress-activity-share-pro/
├── admin/                  # Admin functionality
│   ├── class-buddypress-share-admin.php
│   └── css/js/            # Admin assets
├── public/                # Frontend functionality
│   ├── class-buddypress-share-public.php
│   └── css/js/            # Public assets
├── includes/              # Core functionality
│   ├── class-buddypress-share.php
│   └── helper-functions.php
└── license/               # License management
```

### Key Classes
- `Buddypress_Share`: Main plugin class
- `Buddypress_Share_Admin`: Admin functionality
- `Buddypress_Share_Public`: Frontend functionality
- `Buddypress_Share_License_Manager`: License handling

## Hooks Reference

### Action Hooks

#### `bp_share_before_create_reshare`
Fires before creating a reshare activity.

```php
do_action( 'bp_share_before_create_reshare', array $reshare_data );
```

**Parameters:**
- `$reshare_data` (array): Contains user_id, activity_id, activity_type, activity_content, activity_in, destination_type

**Example:**
```php
add_action( 'bp_share_before_create_reshare', function( $reshare_data ) {
    // Log reshare attempts
    error_log( 'User ' . $reshare_data['user_id'] . ' is resharing activity ' . $reshare_data['activity_id'] );
});
```

#### `bp_share_after_create_reshare`
Fires after successfully creating a reshare.

```php
do_action( 'bp_share_after_create_reshare', int $new_activity_id, array $reshare_data );
```

**Parameters:**
- `$new_activity_id` (int): ID of the newly created share activity
- `$reshare_data` (array): Original reshare data

**Example:**
```php
add_action( 'bp_share_after_create_reshare', function( $new_activity_id, $reshare_data ) {
    // Send notification to original author
    bp_notifications_add_notification( array(
        'user_id'          => $original_author_id,
        'item_id'          => $new_activity_id,
        'component_name'   => 'activity',
        'component_action' => 'reshared_activity',
    ));
}, 10, 2 );
```

#### `bp_share_before_sanitize_extra_settings`
Fires before sanitizing extra settings.

```php
do_action( 'bp_share_before_sanitize_extra_settings', array $input );
```

**Parameters:**
- `$input` (array): Raw settings input data

#### `bp_share_after_sanitize_extra_settings`
Fires after sanitizing extra settings.

```php
do_action( 'bp_share_after_sanitize_extra_settings', array $sanitized, array $input );
```

**Parameters:**
- `$sanitized` (array): Sanitized settings
- `$input` (array): Original input data

#### `bp_share_user_services`
Add custom sharing options to the share dropdown.

```php
do_action( 'bp_share_user_services', array $args, string $activity_link, string $activity_title );
```

**Example:**
```php
add_action( 'bp_share_user_services', function( $args, $activity_link, $activity_title ) {
    ?>
    <div class="custom-share-option">
        <a href="#" class="custom-share-button" data-link="<?php echo esc_attr( $activity_link ); ?>">
            <i class="fas fa-custom-icon"></i>
            <span>Custom Share</span>
        </a>
    </div>
    <?php
}, 10, 3 );
```

## Filters Reference

### Filter Hooks

#### `bp_share_activity_data`
Modify activity share data before rendering.

```php
apply_filters( 'bp_share_activity_data', array $share_data, object $activity );
```

**Parameters:**
- `$share_data` (array): Contains activity_link, activity_title, mail_subject
- `$activity` (object): Current activity object

**Example:**
```php
add_filter( 'bp_share_activity_data', function( $share_data, $activity ) {
    // Add custom UTM parameters to share links
    $share_data['activity_link'] = add_query_arg( array(
        'utm_source'   => 'buddypress',
        'utm_medium'   => 'social',
        'utm_campaign' => 'activity_share'
    ), $share_data['activity_link'] );
    
    return $share_data;
}, 10, 2 );
```

#### `bp_share_services_config`
Customize available sharing services.

```php
apply_filters( 'bp_share_services_config', array $services, string $activity_link, string $activity_title, string $mail_subject );
```

**Parameters:**
- `$services` (array): Array of service configurations
- `$activity_link` (string): Activity permalink
- `$activity_title` (string): Activity title
- `$mail_subject` (string): Email subject

**Example:**
```php
add_filter( 'bp_share_services_config', function( $services, $activity_link, $activity_title ) {
    // Add custom messaging service
    $services['Signal'] = array(
        'url'   => 'https://signal.org/share#' . urlencode( $activity_link ),
        'icon'  => 'fas fa-comment-dots',
        'label' => __( 'Signal', 'textdomain' )
    );
    
    // Remove a service
    unset( $services['Pinterest'] );
    
    return $services;
}, 10, 3 );
```

#### `bp_share_social_button_html`
Modify individual share button HTML.

```php
apply_filters( 'bp_share_social_button_html', string $button_html, string $service, array $details, string $activity_link );
```

**Parameters:**
- `$button_html` (string): HTML for the share button
- `$service` (string): Service name (Facebook, Twitter, etc.)
- `$details` (array): Service configuration details
- `$activity_link` (string): Activity permalink

**Example:**
```php
add_filter( 'bp_share_social_button_html', function( $button_html, $service, $details, $activity_link ) {
    // Add custom attributes to Facebook button
    if ( 'Facebook' === $service ) {
        $button_html = str_replace( 
            'class="button bp-share"', 
            'class="button bp-share" data-fb-share="true"', 
            $button_html 
        );
    }
    
    return $button_html;
}, 10, 4 );
```

#### `bp_share_available_services`
Modify the list of available services in admin.

```php
apply_filters( 'bp_share_available_services', array $services );
```

**Parameters:**
- `$services` (array): Array of available services (key => label)

**Example:**
```php
add_filter( 'bp_share_available_services', function( $services ) {
    // Add custom services
    $services['Discord'] = 'Discord';
    $services['Slack'] = 'Slack';
    
    // Remove services
    unset( $services['Pocket'] );
    
    return $services;
});
```

#### `bp_share_sanitized_extra_settings`
Filter sanitized extra settings.

```php
apply_filters( 'bp_share_sanitized_extra_settings', array $sanitized, array $input );
```

**Parameters:**
- `$sanitized` (array): Sanitized settings
- `$input` (array): Raw input data

**Example:**
```php
add_filter( 'bp_share_sanitized_extra_settings', function( $sanitized, $input ) {
    // Add custom setting sanitization
    if ( isset( $input['custom_setting'] ) ) {
        $sanitized['custom_setting'] = sanitize_text_field( $input['custom_setting'] );
    }
    
    return $sanitized;
}, 10, 2 );
```

## Custom Services

### Adding a Custom Social Service

```php
// 1. Add to available services
add_filter( 'bp_share_available_services', function( $services ) {
    $services['Discord'] = 'Discord';
    return $services;
});

// 2. Add service configuration
add_filter( 'bp_share_services_config', function( $services, $activity_link, $activity_title ) {
    $services['Discord'] = array(
        'url'   => 'https://discord.com/share?url=' . urlencode( $activity_link ),
        'icon'  => 'fab fa-discord',
        'label' => __( 'Discord', 'textdomain' )
    );
    
    return $services;
}, 10, 3 );

// 3. Add custom styling (optional)
add_action( 'wp_head', function() {
    ?>
    <style>
        .bp-share-wrapper a#bp_discord_share {
            background-color: #5865F2;
        }
        .bp-share-wrapper a#bp_discord_share:hover {
            background-color: #4752C4;
        }
    </style>
    <?php
});
```

## API Reference

### JavaScript Events

#### `bp-share-copied`
Triggered when link is copied to clipboard.

```javascript
jQuery(document).on('bp-share-copied', function(event, data) {
    console.log('Link copied:', data.link);
});
```

#### `bp-reshare-success`
Triggered after successful reshare.

```javascript
jQuery(document).on('bp-reshare-success', function(event, data) {
    console.log('Activity reshared:', data.activity_id);
    console.log('New share count:', data.share_count);
});
```

### AJAX Endpoints

#### Create Reshare
- **Action**: `bp_activity_create_reshare`
- **Method**: POST
- **Nonce**: `bp-activity-share-nonce`
- **Parameters**:
  - `activity_id`: Original activity ID
  - `type`: Share type (activity_share, post_share)
  - `activity_content`: Reshare message
  - `activity_in`: Group ID (optional)
  - `activity_in_type`: Destination type (user, group)

### PHP Functions

#### `bp_share_get_activity_type()`
Get the current activity type.

```php
$activity_type = bp_share_get_activity_type();
```

#### `bp_share_should_show_share_button( $activity_type )`
Check if share button should be displayed.

```php
if ( bp_share_should_show_share_button( $activity_type ) ) {
    // Show share button
}
```

#### `bp_share_get_activity_title()`
Get formatted activity title for sharing.

```php
$title = bp_share_get_activity_title();
```

## Code Examples

### Custom Share Counter Display

```php
// Display share count in a custom location
add_action( 'bp_activity_entry_meta', function() {
    $activity_id = bp_get_activity_id();
    $share_count = bp_activity_get_meta( $activity_id, 'share_count', true );
    
    if ( $share_count > 0 ) {
        printf( 
            '<span class="custom-share-count">%s %s</span>',
            esc_html( $share_count ),
            _n( 'share', 'shares', $share_count, 'textdomain' )
        );
    }
});
```

### Restrict Sharing by User Role

```php
// Only allow certain roles to reshare
add_filter( 'bp_share_before_create_reshare', function( $reshare_data ) {
    $user = get_user_by( 'id', $reshare_data['user_id'] );
    
    if ( ! in_array( 'administrator', $user->roles ) && ! in_array( 'editor', $user->roles ) ) {
        wp_die( __( 'You do not have permission to reshare activities.', 'textdomain' ) );
    }
    
    return $reshare_data;
});
```

### Track External Shares

```php
// Log external shares via AJAX
add_action( 'wp_footer', function() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('.bp-share-wrapper a.bp-share').on('click', function() {
            var service = $(this).attr('id').replace('bp_', '').replace('_share', '');
            var activity_id = $(this).closest('.activity-item').data('bp-activity-id');
            
            $.post(ajaxurl, {
                action: 'track_external_share',
                service: service,
                activity_id: activity_id,
                nonce: '<?php echo wp_create_nonce( 'track_share_nonce' ); ?>'
            });
        });
    });
    </script>
    <?php
});

add_action( 'wp_ajax_track_external_share', function() {
    check_ajax_referer( 'track_share_nonce', 'nonce' );
    
    $service = sanitize_text_field( $_POST['service'] );
    $activity_id = intval( $_POST['activity_id'] );
    
    // Log to custom table or meta
    do_action( 'bp_share_external_share_tracked', $service, $activity_id );
    
    wp_die();
});
```

### Custom Share Modal

```php
// Replace default share modal with custom implementation
add_filter( 'bp_share_social_button_html', function( $button_html, $service, $details, $activity_link ) {
    if ( 'Facebook' === $service ) {
        $button_html = sprintf(
            '<div class="bp-share-wrapper">
                <a class="button bp-share custom-fb-share" href="#" data-url="%s">
                    <i class="%s"></i>
                    <span class="bp-share-label">%s</span>
                </a>
            </div>',
            esc_attr( $activity_link ),
            esc_attr( $details['icon'] ),
            esc_html( $details['label'] )
        );
    }
    
    return $button_html;
}, 10, 4 );

// Add custom JavaScript for modal
add_action( 'wp_footer', function() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('.custom-fb-share').on('click', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            
            // Custom FB share implementation
            FB.ui({
                method: 'share',
                href: url,
            }, function(response){
                // Handle response
            });
        });
    });
    </script>
    <?php
});
```

## Best Practices

### Performance
1. **Cache share counts**: Use transients for frequently accessed data
2. **Lazy load share buttons**: Load only when needed
3. **Minimize database queries**: Batch operations when possible

### Security
1. **Always verify nonces**: Use `check_ajax_referer()` for AJAX requests
2. **Sanitize all input**: Use appropriate WordPress sanitization functions
3. **Validate permissions**: Check user capabilities before actions

### Compatibility
1. **Check for dependencies**: Verify BuddyPress/BuddyBoss is active
2. **Use proper hooks**: Respect plugin load order
3. **Test with popular themes**: Ensure compatibility

### Code Quality
1. **Follow WordPress coding standards**
2. **Document your code**: Use PHPDoc blocks
3. **Use meaningful hook names**: Prefix with your plugin slug
4. **Handle errors gracefully**: Provide fallbacks

### Example: Safe Custom Integration

```php
/**
 * Safely add custom functionality to BuddyPress Share
 */
class My_Custom_BP_Share_Extension {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Check if parent plugin is active
        if ( ! class_exists( 'Buddypress_Share' ) ) {
            return;
        }
        
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_filter( 'bp_share_available_services', array( $this, 'add_custom_services' ) );
        add_action( 'bp_share_after_create_reshare', array( $this, 'handle_after_reshare' ), 10, 2 );
    }
    
    /**
     * Add custom services
     */
    public function add_custom_services( $services ) {
        $services['CustomService'] = __( 'Custom Service', 'textdomain' );
        return $services;
    }
    
    /**
     * Handle after reshare
     */
    public function handle_after_reshare( $new_activity_id, $reshare_data ) {
        // Your custom logic here
        update_post_meta( $new_activity_id, '_custom_reshare_data', $reshare_data );
    }
}

// Initialize on plugins_loaded
add_action( 'plugins_loaded', function() {
    new My_Custom_BP_Share_Extension();
});
```

## Support

For developer support:
1. Check the [code repository](https://wbcomdesigns.com)
2. Submit technical questions to [support](https://wbcomdesigns.com/support)
3. Review the source code for additional inline documentation

---

Happy coding with BuddyPress Activity Share Pro!