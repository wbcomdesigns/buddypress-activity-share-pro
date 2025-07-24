# BuddyPress Activity Share Pro - Developer Guide

## Table of Contents
1. [Introduction](#introduction)
2. [Architecture Overview](#architecture-overview)
3. [Hooks Reference](#hooks-reference)
4. [Filters Reference](#filters-reference)
5. [Share Tracking System](#share-tracking-system)
6. [Custom Services](#custom-services)
7. [API Reference](#api-reference)
8. [Code Examples](#code-examples)
9. [Best Practices](#best-practices)

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
- `Buddypress_Share_Tracker`: Share tracking and analytics (v2.0.0+)

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

#### `bp_share_user_reshared_activity` (v2.0.0+)
Specific trigger for point/reward systems after reshare. Perfect for gamification integrations.

```php
do_action( 'bp_share_user_reshared_activity', int $user_id, string $reshare_type, int $original_activity, int $new_activity_id );
```

**Parameters:**
- `$user_id` (int): The user who performed the reshare
- `$reshare_type` (string): Type of reshare (profile, group, friend)
- `$original_activity` (int): Original activity ID that was reshared
- `$new_activity_id` (int): The newly created share activity ID

**Example - Award Points:**
```php
add_action( 'bp_share_user_reshared_activity', function( $user_id, $reshare_type, $original_activity, $new_activity_id ) {
    // Award different points based on share type
    $points = array(
        'profile' => 10,
        'group'   => 15,
        'friend'  => 20
    );
    
    $award_points = isset( $points[$reshare_type] ) ? $points[$reshare_type] : 10;
    
    // Integration with myCRED
    if ( function_exists( 'mycred_add' ) ) {
        mycred_add( 
            'reshare_activity',
            $user_id,
            $award_points,
            'Reshared activity to %s',
            $reshare_type,
            array( 'ref_type' => 'post', 'ref_id' => $original_activity )
        );
    }
}, 10, 4 );
```

#### Tracking System Hooks (v2.0.0+)

**`bp_share_internal_share_tracked`** - Fired when internal share is tracked
```php
do_action( 'bp_share_internal_share_tracked', array $share_data, int $user_id );
```

**`bp_share_external_share_tracked`** - Fired when external share is tracked
```php
do_action( 'bp_share_external_share_tracked', array $share_data );
```

**`bp_share_external_visit_tracked`** - Fired when someone visits a tracked link
```php
do_action( 'bp_share_external_visit_tracked', array $visit_data );
```

**`bp_share_user_stats_updated`** - Fired after user share statistics are updated
```php
do_action( 'bp_share_user_stats_updated', int $user_id, array $stats );
```

**`bp_share_activity_stats_updated`** - Fired after activity share statistics are updated
```php
do_action( 'bp_share_activity_stats_updated', int $activity_id, array $stats );
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

#### `bp_share_tracking_parameters` (v2.0.0+)
Customize tracking parameters added to external share links.

```php
apply_filters( 'bp_share_tracking_parameters', array $tracking_params, string $url, string $service, int $activity_id, int $user_id );
```

**Parameters:**
- `$tracking_params` (array): Default tracking parameters
- `$url` (string): The original URL
- `$service` (string): The service name (facebook, x-twitter, etc.)
- `$activity_id` (int): The activity being shared
- `$user_id` (int): The user sharing the activity

**Default Parameters:**
- `utm_source`: 'buddypress_share'
- `utm_medium`: 'social'
- `utm_campaign`: 'activity_share'
- `utm_content`: Service name
- `bps_aid`: Activity ID
- `bps_uid`: User ID
- `bps_service`: Service name
- `bps_time`: Timestamp

**Example - Add Custom Campaign:**
```php
add_filter( 'bp_share_tracking_parameters', function( $params, $url, $service, $activity_id, $user_id ) {
    // Add seasonal campaign
    $params['utm_campaign'] = 'summer_2024_share';
    
    // Add user segment
    $user_data = get_userdata( $user_id );
    if ( $user_data ) {
        $params['user_role'] = $user_data->roles[0] ?? 'member';
    }
    
    // Remove user ID for privacy
    unset( $params['bps_uid'] );
    
    return $params;
}, 10, 5 );
```

## Share Tracking System

### Overview (v2.0.0+)

The share tracking system provides comprehensive analytics for both internal reshares and external social media shares. It automatically tracks user behavior, activity metrics, and provides hooks for integration with point systems and analytics platforms.

### Tracking Features

#### 1. Internal Reshare Tracking
Automatically tracks when users reshare activities within the community:
- User statistics (total shares, breakdown by type)
- Activity statistics (shares per activity, unique sharers)
- Timestamp and destination tracking

#### 2. External Share Tracking
All external share links include tracking parameters:
- Standard UTM parameters for analytics platforms
- Custom BuddyPress parameters for detailed tracking
- Service-specific tracking for each social platform

#### 3. Visit Tracking
Tracks when users click on shared links:
- Total visits per activity
- Breakdown by service/platform
- Referrer tracking

### Tracker Class API

```php
// Get user share statistics
$user_stats = Buddypress_Share_Tracker::get_user_stats( $user_id );
/*
Returns:
array(
    'total_shares' => 42,
    'internal_shares' => 30,
    'external_shares' => 12,
    'last_share_date' => '2024-01-15 10:30:00',
    'share_breakdown' => array(
        'profile' => 20,
        'group' => 10,
        'facebook' => 5,
        'x-twitter' => 7
    )
)
*/

// Get activity share statistics
$activity_stats = Buddypress_Share_Tracker::get_activity_stats( $activity_id );
/*
Returns:
array(
    'total_shares' => 15,
    'internal_shares' => 10,
    'external_shares' => 5,
    'unique_sharers' => array( 1, 5, 8, 12 ),
    'last_share_date' => '2024-01-15 10:30:00'
)
*/

// Get activity visit statistics
$visit_stats = Buddypress_Share_Tracker::get_activity_visit_stats( $activity_id );
/*
Returns:
array(
    'total_visits' => 150,
    'service_visits' => array(
        'facebook' => 80,
        'x-twitter' => 50,
        'linkedin' => 20
    ),
    'last_visit_date' => '2024-01-15 10:30:00'
)
*/
```

### Integration Examples

#### GamiPress Points Integration
```php
add_action( 'bp_share_user_reshared_activity', function( $user_id, $reshare_type ) {
    if ( function_exists( 'gamipress_award_points' ) ) {
        // Award points based on share type
        $points = ( 'group' === $reshare_type ) ? 15 : 10;
        gamipress_award_points( $user_id, $points, 'reshare_activity' );
    }
}, 10, 2 );
```

#### Achievement System
```php
add_action( 'bp_share_user_stats_updated', function( $user_id, $stats ) {
    // Award achievement for 10 reshares
    if ( $stats['internal_shares'] == 10 ) {
        // Award "Social Butterfly" achievement
        do_action( 'bp_share_award_achievement', $user_id, 'social_butterfly' );
    }
    
    // Award achievement for 50 total shares
    if ( $stats['total_shares'] == 50 ) {
        // Award "Share Master" achievement
        do_action( 'bp_share_award_achievement', $user_id, 'share_master' );
    }
}, 10, 2 );
```

#### Analytics Dashboard
```php
// Display share statistics in user profile
add_action( 'bp_before_member_header_meta', function() {
    $user_id = bp_displayed_user_id();
    $stats = Buddypress_Share_Tracker::get_user_stats( $user_id );
    
    if ( $stats && $stats['total_shares'] > 0 ) {
        ?>
        <div class="share-stats">
            <span class="stat-item">
                <strong><?php echo $stats['total_shares']; ?></strong> 
                <?php _e( 'Total Shares', 'buddypress-share' ); ?>
            </span>
            <span class="stat-item">
                <strong><?php echo count( $stats['share_breakdown'] ); ?></strong> 
                <?php _e( 'Platforms Used', 'buddypress-share' ); ?>
            </span>
        </div>
        <?php
    }
});
```

#### Google Analytics Integration
```php
// Track reshares in Google Analytics
add_action( 'bp_share_internal_share_tracked', function( $share_data ) {
    if ( ! is_admin() ) {
        ?>
        <script>
        if ( typeof gtag !== 'undefined' ) {
            gtag('event', 'share', {
                'event_category': 'social',
                'event_action': 'reshare',
                'event_label': '<?php echo $share_data['destination_type']; ?>',
                'user_id': <?php echo $share_data['user_id']; ?>,
                'activity_id': <?php echo $share_data['activity_id']; ?>
            });
        }
        </script>
        <?php
    }
});
```

### Privacy Controls

```php
// Remove user ID from tracking parameters
add_filter( 'bp_share_tracking_parameters', function( $params ) {
    unset( $params['bps_uid'] );
    return $params;
});

// Anonymize IP addresses
add_filter( 'bp_share_internal_share_tracked', function( $share_data ) {
    // Hash IP instead of storing raw
    if ( isset( $share_data['ip_address'] ) ) {
        $share_data['ip_address'] = hash( 'sha256', $share_data['ip_address'] );
    }
    return $share_data;
});
```

### Performance Optimization

```php
// Cache user statistics
add_action( 'bp_share_user_stats_updated', function( $user_id, $stats ) {
    set_transient( 'bp_share_user_stats_' . $user_id, $stats, HOUR_IN_SECONDS );
}, 10, 2 );

// Batch process share tracking
add_action( 'bp_share_user_reshared_activity', function( $user_id, $reshare_type, $original_activity, $new_activity_id ) {
    // Queue for batch processing instead of real-time
    as_schedule_single_action( time() + 60, 'process_share_batch', array(
        'user_id' => $user_id,
        'type' => $reshare_type,
        'activity_id' => $original_activity
    ));
}, 10, 4 );
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