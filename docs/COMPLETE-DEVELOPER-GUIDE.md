# BuddyPress Activity Share Pro - Complete Developer Guide

## 📚 Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Core Classes & Methods](#core-classes--methods)
3. [Hooks Reference](#hooks-reference)
4. [Filters Reference](#filters-reference)
5. [JavaScript API](#javascript-api)
6. [REST API Endpoints](#rest-api-endpoints)
7. [Database Schema](#database-schema)
8. [Integration Examples](#integration-examples)
9. [Custom Platform Development](#custom-platform-development)
10. [Performance Optimization](#performance-optimization)
11. [Security Best Practices](#security-best-practices)
12. [Testing & Debugging](#testing--debugging)

---

## Architecture Overview

### Plugin Structure
```
buddypress-activity-share-pro/
├── admin/                    # Admin functionality
│   ├── class-buddypress-share-admin.php
│   ├── css/                 # Admin styles
│   └── js/                  # Admin scripts
├── public/                  # Frontend functionality
│   ├── class-buddypress-share-public.php
│   ├── css/                 # Frontend styles
│   └── js/                  # Frontend scripts
├── includes/                # Core functionality
│   ├── class-buddypress-share.php
│   ├── class-buddypress-share-tracker.php
│   ├── class-buddypress-share-assets.php
│   └── bp-share-helpers.php
└── buddypress-share.php    # Main plugin file
```

### Loading Sequence
```php
1. buddypress-share.php          // Entry point
2. class-buddypress-share.php    // Core initialization
3. class-buddypress-share-loader.php  // Hook registration
4. class-buddypress-share-public.php  // Frontend features
5. class-buddypress-share-admin.php   // Admin features
```

---

## Core Classes & Methods

### Main Plugin Class

```php
class Buddypress_Share {
    
    /**
     * Initialize the plugin
     */
    public function __construct() {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Run the plugin
     */
    public function run() {
        $this->loader->run();
    }
}
```

### Public Class Methods

```php
class Buddypress_Share_Public {
    
    /**
     * Add share buttons to activity
     * 
     * @param string $content Activity content
     * @return string Modified content with share buttons
     */
    public function bp_activity_share_button_display_handler($content) {
        if (!$this->should_display_buttons()) {
            return $content;
        }
        
        $buttons = $this->generate_share_buttons();
        return $content . $buttons;
    }
    
    /**
     * Generate share buttons HTML
     * 
     * @param int $activity_id Activity ID
     * @return string HTML output
     */
    public function generate_share_buttons($activity_id = null) {
        $services = $this->get_enabled_services();
        $html = '<div class="bp-share-buttons">';
        
        foreach ($services as $service => $config) {
            $html .= $this->generate_button($service, $config, $activity_id);
        }
        
        $html .= '</div>';
        return apply_filters('bp_share_buttons_html', $html, $activity_id);
    }
}
```

### Tracker Class

```php
class Buddypress_Share_Tracker {
    
    /**
     * Track internal share event
     * 
     * @param int $user_id User who shared
     * @param string $reshare_type Type of reshare
     * @param int $original_activity Original activity ID
     * @param int $new_activity_id New activity ID
     */
    public function track_internal_share($user_id, $reshare_type, $original_activity, $new_activity_id) {
        $share_data = array(
            'user_id' => $user_id,
            'activity_id' => $original_activity,
            'new_activity_id' => $new_activity_id,
            'share_type' => 'internal',
            'destination_type' => $reshare_type,
            'timestamp' => current_time('mysql'),
            'ip_address' => $this->get_user_ip()
        );
        
        // Store in database or trigger action
        do_action('bp_share_internal_tracked', $share_data);
        
        // Update statistics
        $this->update_share_statistics($share_data);
    }
    
    /**
     * Get share statistics
     * 
     * @param int $activity_id Activity ID
     * @return array Statistics data
     */
    public function get_share_statistics($activity_id) {
        $stats = array(
            'total_shares' => $this->get_total_shares($activity_id),
            'platform_breakdown' => $this->get_platform_breakdown($activity_id),
            'time_distribution' => $this->get_time_distribution($activity_id),
            'user_stats' => $this->get_user_statistics($activity_id)
        );
        
        return apply_filters('bp_share_statistics', $stats, $activity_id);
    }
}
```

---

## Hooks Reference

### Action Hooks

#### `bp_share_before_buttons`
Fires before share buttons are displayed.
```php
do_action('bp_share_before_buttons', $activity_id);

// Example usage
add_action('bp_share_before_buttons', function($activity_id) {
    echo '<div class="share-header">Share this post:</div>';
});
```

#### `bp_share_after_buttons`
Fires after share buttons are displayed.
```php
do_action('bp_share_after_buttons', $activity_id);

// Example usage
add_action('bp_share_after_buttons', function($activity_id) {
    $count = bp_share_get_total_shares($activity_id);
    echo "<span class='share-count'>Shared {$count} times</span>";
});
```

#### `bp_share_user_reshared_activity`
Fires when a user reshares an activity.
```php
do_action('bp_share_user_reshared_activity', 
    $user_id,           // User who shared
    $reshare_type,      // 'profile', 'group', or 'message'
    $original_activity, // Original activity ID
    $new_activity_id    // New activity ID created
);

// Example: Award points
add_action('bp_share_user_reshared_activity', function($user_id, $type, $original, $new) {
    if (function_exists('mycred_add')) {
        mycred_add('activity_reshare', $user_id, 10, 'Reshared activity %d', $original);
    }
}, 10, 4);
```

#### `bp_share_external_share_tracked`
Fires when an external share is tracked.
```php
do_action('bp_share_external_share_tracked', $share_data);

// Share data structure
$share_data = array(
    'activity_id' => 123,
    'user_id' => 456,
    'service' => 'facebook',
    'timestamp' => '2024-01-01 12:00:00',
    'ip_address' => '192.168.1.1'
);
```

#### `bp_share_settings_saved`
Fires after settings are saved.
```php
do_action('bp_share_settings_saved', $settings);

// Example: Clear cache after settings change
add_action('bp_share_settings_saved', function($settings) {
    wp_cache_delete('bp_share_settings', 'bp_share');
    wp_cache_delete('bp_share_services', 'bp_share');
});
```

### More Action Hooks

```php
// Activity-specific hooks
do_action('bp_share_activity_shared', $activity_id, $platform);
do_action('bp_share_before_reshare_content', $activity_id);
do_action('bp_share_after_reshare_created', $new_activity_id);

// Admin hooks
do_action('bp_share_admin_settings_init');
do_action('bp_share_admin_enqueue_scripts');

// Tracking hooks
do_action('bp_share_visit_tracked', $activity_id, $referrer);
do_action('bp_share_analytics_updated', $stats_data);
```

---

## Filters Reference

### Service Filters

#### `bp_share_services`
Modify available social services.
```php
add_filter('bp_share_services', function($services) {
    // Add custom service
    $services['discord'] = array(
        'name' => 'Discord',
        'icon' => 'fab fa-discord',
        'url' => 'https://discord.com/share?url={url}&text={title}',
        'color' => '#7289da'
    );
    
    // Remove a service
    unset($services['pinterest']);
    
    return $services;
});
```

#### `bp_share_enabled_services`
Filter which services are enabled.
```php
add_filter('bp_share_enabled_services', function($enabled) {
    // Only enable specific services for mobile
    if (wp_is_mobile()) {
        return array('whatsapp', 'telegram', 'facebook');
    }
    return $enabled;
});
```

### Display Filters

#### `bp_share_display_conditions`
Control when share buttons are displayed.
```php
add_filter('bp_share_display_conditions', function($display, $activity) {
    // Don't show for activities older than 30 days
    $activity_date = strtotime($activity->date_recorded);
    $days_old = (time() - $activity_date) / (60 * 60 * 24);
    
    if ($days_old > 30) {
        return false;
    }
    
    // Don't show for private activities
    if ($activity->hide_sitewide) {
        return false;
    }
    
    return $display;
}, 10, 2);
```

#### `bp_share_button_html`
Modify individual button HTML.
```php
add_filter('bp_share_button_html', function($html, $service, $activity_id) {
    // Add custom attributes
    $html = str_replace('<a ', '<a data-service="' . $service . '" ', $html);
    
    // Add share count
    $count = bp_share_get_service_count($activity_id, $service);
    if ($count > 0) {
        $html .= "<span class='share-count'>{$count}</span>";
    }
    
    return $html;
}, 10, 3);
```

### Content Filters

#### `bp_share_activity_content`
Modify shared activity content.
```php
add_filter('bp_share_activity_content', function($content, $activity) {
    // Add watermark to reshared content
    $content .= "\n\n— Shared via " . get_bloginfo('name');
    
    // Limit content length
    if (strlen($content) > 500) {
        $content = substr($content, 0, 497) . '...';
    }
    
    return $content;
}, 10, 2);
```

#### `bp_share_tracking_parameters`
Modify tracking parameters.
```php
add_filter('bp_share_tracking_parameters', function($params, $activity_id, $service) {
    // Add custom tracking parameters
    $params['campaign_id'] = 'summer_2024';
    $params['user_type'] = is_user_logged_in() ? 'member' : 'visitor';
    $params['activity_type'] = bp_activity_get_meta($activity_id, 'activity_type', true);
    
    // Add A/B testing parameter
    $params['variant'] = (rand(0, 1) == 0) ? 'a' : 'b';
    
    return $params;
}, 10, 3);
```

### More Filters

```php
// Customization filters
apply_filters('bp_share_button_classes', $classes, $service);
apply_filters('bp_share_button_text', $text, $service);
apply_filters('bp_share_icon_class', $icon_class, $service);

// Permission filters
apply_filters('bp_share_user_can_share', $can_share, $user_id, $activity_id);
apply_filters('bp_share_activity_shareable', $shareable, $activity);

// URL filters
apply_filters('bp_share_activity_url', $url, $activity_id);
apply_filters('bp_share_permalink', $permalink, $activity);

// Analytics filters
apply_filters('bp_share_analytics_data', $data, $activity_id);
apply_filters('bp_share_statistics_query', $query, $args);
```

---

## JavaScript API

### Core Functions

```javascript
// BPShare namespace
window.BPShare = {
    
    /**
     * Initialize share functionality
     */
    init: function() {
        this.bindEvents();
        this.setupTracking();
    },
    
    /**
     * Share to external platform
     */
    shareExternal: function(platform, activityId) {
        const url = this.getShareUrl(platform, activityId);
        
        // Track share event
        this.trackShare(platform, activityId);
        
        // Open share window
        window.open(url, 'share-' + platform, 'width=600,height=400');
    },
    
    /**
     * Share internally
     */
    shareInternal: function(type, activityId) {
        const data = {
            action: 'bp_share_internal',
            type: type,
            activity_id: activityId,
            nonce: bp_share_vars.nonce
        };
        
        jQuery.post(bp_share_vars.ajax_url, data, function(response) {
            if (response.success) {
                BPShare.showNotification('Activity shared successfully!');
                BPShare.updateShareCount(activityId);
            }
        });
    },
    
    /**
     * Track share event
     */
    trackShare: function(platform, activityId) {
        // Google Analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'share', {
                'event_category': 'social',
                'event_label': platform,
                'value': activityId
            });
        }
        
        // Custom tracking
        jQuery.post(bp_share_vars.ajax_url, {
            action: 'bp_share_track',
            platform: platform,
            activity_id: activityId,
            nonce: bp_share_vars.nonce
        });
    }
};

// jQuery extensions
jQuery.fn.bpShare = function(options) {
    const settings = jQuery.extend({
        platforms: ['facebook', 'twitter', 'whatsapp'],
        trackingEnabled: true,
        popupWindow: true,
        onShare: function() {},
        onError: function() {}
    }, options);
    
    return this.each(function() {
        // Initialize share buttons
        jQuery(this).on('click', '.bp-share-button', function(e) {
            e.preventDefault();
            
            const platform = jQuery(this).data('service');
            const activityId = jQuery(this).data('activity-id');
            
            try {
                BPShare.shareExternal(platform, activityId);
                settings.onShare(platform, activityId);
            } catch(error) {
                settings.onError(error);
            }
        });
    });
};
```

### Event Listeners

```javascript
// Document ready
jQuery(document).ready(function($) {
    
    // Initialize share buttons
    $('.bp-share-buttons').bpShare({
        onShare: function(platform, activityId) {
            console.log('Shared to ' + platform);
        }
    });
    
    // Custom share button handler
    $(document).on('click', '.custom-share-btn', function(e) {
        e.preventDefault();
        
        const activityId = $(this).closest('.activity-item').data('id');
        BPShare.openShareModal(activityId);
    });
    
    // Copy link functionality
    $(document).on('click', '.bp-share-copy-link', function(e) {
        e.preventDefault();
        
        const url = $(this).data('url');
        BPShare.copyToClipboard(url);
    });
});

// Custom events
jQuery(document).on('bp_share_completed', function(e, data) {
    console.log('Share completed:', data);
});

jQuery(document).on('bp_share_failed', function(e, error) {
    console.error('Share failed:', error);
});
```

---

## REST API Endpoints

### Available Endpoints

```php
// Get share statistics
GET /wp-json/bp-share/v1/statistics/{activity_id}

// Track external share
POST /wp-json/bp-share/v1/track
{
    "activity_id": 123,
    "service": "facebook",
    "user_id": 456
}

// Create internal share
POST /wp-json/bp-share/v1/reshare
{
    "activity_id": 123,
    "type": "profile",
    "message": "Check this out!"
}

// Get enabled services
GET /wp-json/bp-share/v1/services

// Get user share history
GET /wp-json/bp-share/v1/users/{user_id}/shares
```

### Registering Custom Endpoints

```php
add_action('rest_api_init', function() {
    register_rest_route('bp-share/v1', '/custom-endpoint', array(
        'methods' => 'GET',
        'callback' => 'bp_share_custom_endpoint_handler',
        'permission_callback' => function() {
            return current_user_can('read');
        },
        'args' => array(
            'activity_id' => array(
                'required' => true,
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            )
        )
    ));
});

function bp_share_custom_endpoint_handler($request) {
    $activity_id = $request->get_param('activity_id');
    
    // Your custom logic
    $data = array(
        'activity_id' => $activity_id,
        'custom_data' => 'Your data here'
    );
    
    return new WP_REST_Response($data, 200);
}
```

---

## Database Schema

### Custom Tables (Optional)

```sql
-- Share tracking table
CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bp_share_tracking` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `activity_id` bigint(20) NOT NULL,
    `user_id` bigint(20) DEFAULT NULL,
    `service` varchar(50) NOT NULL,
    `share_type` enum('internal','external') DEFAULT 'external',
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text,
    `referrer` text,
    `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `activity_id` (`activity_id`),
    KEY `user_id` (`user_id`),
    KEY `service` (`service`),
    KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Share statistics table
CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bp_share_statistics` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `activity_id` bigint(20) NOT NULL,
    `service` varchar(50) NOT NULL,
    `share_count` int(11) DEFAULT 0,
    `last_shared` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `activity_service` (`activity_id`, `service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Meta Data Storage

```php
// Activity meta
bp_activity_add_meta($activity_id, 'total_shares', 0);
bp_activity_add_meta($activity_id, 'share_services', serialize($services));
bp_activity_add_meta($activity_id, 'last_shared', current_time('mysql'));

// User meta
update_user_meta($user_id, 'bp_share_total_shares', $count);
update_user_meta($user_id, 'bp_share_points', $points);
update_user_meta($user_id, 'bp_share_preferences', $preferences);
```

---

## Integration Examples

### myCRED Integration

```php
/**
 * Award points for sharing activities
 */
add_action('bp_share_user_reshared_activity', function($user_id, $type, $original, $new) {
    if (!function_exists('mycred_add')) {
        return;
    }
    
    // Different points for different share types
    $points = array(
        'profile' => 5,
        'group' => 7,
        'message' => 3
    );
    
    $amount = isset($points[$type]) ? $points[$type] : 5;
    
    // Award points to sharer
    mycred_add(
        'bp_activity_share',
        $user_id,
        $amount,
        'Shared activity to %s',
        $original,
        array('type' => $type),
        'mycred_default'
    );
    
    // Award points to original author
    $activity = bp_activity_get_specific(array('activity_ids' => $original));
    if (!empty($activity['activities'])) {
        $author_id = $activity['activities'][0]->user_id;
        
        mycred_add(
            'bp_activity_reshared',
            $author_id,
            10,
            'Your activity was reshared',
            $original,
            array('sharer' => $user_id),
            'mycred_default'
        );
    }
}, 10, 4);
```

### GamiPress Integration

```php
/**
 * Register GamiPress triggers
 */
add_action('init', function() {
    if (!function_exists('gamipress_register_trigger')) {
        return;
    }
    
    gamipress_register_trigger('bp_share_activity', array(
        'label' => 'Share a BuddyPress activity',
        'listener' => 'bp_share_user_reshared_activity',
        'score' => 10
    ));
    
    gamipress_register_trigger('bp_get_reshared', array(
        'label' => 'Get your activity reshared',
        'listener' => 'bp_share_activity_reshared_by_others',
        'score' => 20
    ));
});

// Trigger when activity is reshared
add_action('bp_share_user_reshared_activity', function($user_id, $type) {
    do_action('gamipress_bp_share_activity', $user_id, $type);
}, 10, 2);
```

### WooCommerce Integration

```php
/**
 * Add share buttons to WooCommerce product activities
 */
add_filter('bp_share_display_conditions', function($display, $activity) {
    // Check if this is a WooCommerce product activity
    if ($activity->type === 'new_product' || $activity->type === 'product_review') {
        return true;
    }
    
    return $display;
}, 10, 2);

// Customize share content for products
add_filter('bp_share_activity_content', function($content, $activity) {
    if ($activity->type === 'new_product') {
        $product_id = bp_activity_get_meta($activity->id, 'product_id', true);
        if ($product_id) {
            $product = wc_get_product($product_id);
            $content = sprintf(
                "Check out %s - Now only %s! %s",
                $product->get_name(),
                $product->get_price_html(),
                $content
            );
        }
    }
    
    return $content;
}, 10, 2);
```

### Custom Analytics Integration

```php
/**
 * Send share data to custom analytics platform
 */
add_action('bp_share_external_share_tracked', function($share_data) {
    // Send to your analytics API
    $api_data = array(
        'event' => 'social_share',
        'properties' => array(
            'activity_id' => $share_data['activity_id'],
            'platform' => $share_data['service'],
            'user_id' => $share_data['user_id'],
            'timestamp' => $share_data['timestamp']
        )
    );
    
    wp_remote_post('https://your-analytics.com/track', array(
        'body' => json_encode($api_data),
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer YOUR_API_KEY'
        )
    ));
});
```

---

## Custom Platform Development

### Adding a Custom Social Platform

```php
/**
 * Register custom social platform
 */
class BP_Share_Custom_Platform {
    
    public function __construct() {
        add_filter('bp_share_services', array($this, 'register_platform'));
        add_filter('bp_share_platform_share_url', array($this, 'build_share_url'), 10, 3);
        add_action('wp_ajax_bp_share_custom_platform', array($this, 'handle_share'));
    }
    
    public function register_platform($services) {
        $services['custom_platform'] = array(
            'name' => 'Custom Platform',
            'icon' => 'fas fa-share-custom',
            'color' => '#123456',
            'requires_api' => true,
            'api_endpoint' => 'https://api.custom-platform.com/share',
            'api_key' => get_option('custom_platform_api_key')
        );
        
        return $services;
    }
    
    public function build_share_url($url, $platform, $activity) {
        if ($platform !== 'custom_platform') {
            return $url;
        }
        
        $params = array(
            'url' => bp_activity_get_permalink($activity->id),
            'title' => strip_tags($activity->content),
            'api_key' => $this->get_api_key(),
            'user_id' => get_current_user_id()
        );
        
        return add_query_arg($params, 'https://custom-platform.com/share');
    }
    
    public function handle_share() {
        check_ajax_referer('bp_share_nonce', 'nonce');
        
        $activity_id = intval($_POST['activity_id']);
        
        // Custom share logic
        $result = $this->share_to_platform($activity_id);
        
        if ($result) {
            // Track the share
            do_action('bp_share_custom_platform_shared', $activity_id);
            
            wp_send_json_success(array(
                'message' => 'Shared successfully!',
                'share_id' => $result
            ));
        } else {
            wp_send_json_error('Failed to share');
        }
    }
    
    private function share_to_platform($activity_id) {
        // Your API integration logic
        return true;
    }
}

new BP_Share_Custom_Platform();
```

---

## Performance Optimization

### Caching Strategies

```php
/**
 * Implement caching for share counts
 */
class BP_Share_Cache {
    
    const CACHE_GROUP = 'bp_share';
    const CACHE_TIME = 3600; // 1 hour
    
    public static function get_share_count($activity_id) {
        $cache_key = 'share_count_' . $activity_id;
        $count = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if (false === $count) {
            // Calculate count from database
            $count = self::calculate_share_count($activity_id);
            
            // Store in cache
            wp_cache_set($cache_key, $count, self::CACHE_GROUP, self::CACHE_TIME);
        }
        
        return $count;
    }
    
    public static function invalidate_cache($activity_id) {
        $cache_key = 'share_count_' . $activity_id;
        wp_cache_delete($cache_key, self::CACHE_GROUP);
        
        // Also clear related caches
        wp_cache_delete('popular_shares', self::CACHE_GROUP);
        wp_cache_delete('user_shares_' . get_current_user_id(), self::CACHE_GROUP);
    }
    
    private static function calculate_share_count($activity_id) {
        global $wpdb;
        
        // Get from meta
        $internal = bp_activity_get_meta($activity_id, 'internal_shares', true);
        
        // Get from tracking table if exists
        $external = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}bp_share_tracking 
             WHERE activity_id = %d",
            $activity_id
        ));
        
        return intval($internal) + intval($external);
    }
}

// Use transients for expensive operations
function bp_share_get_popular_activities($limit = 10) {
    $transient_key = 'bp_share_popular_' . $limit;
    $popular = get_transient($transient_key);
    
    if (false === $popular) {
        global $wpdb;
        
        $popular = $wpdb->get_results($wpdb->prepare(
            "SELECT activity_id, COUNT(*) as share_count 
             FROM {$wpdb->prefix}bp_share_tracking 
             GROUP BY activity_id 
             ORDER BY share_count DESC 
             LIMIT %d",
            $limit
        ));
        
        set_transient($transient_key, $popular, HOUR_IN_SECONDS);
    }
    
    return $popular;
}
```

### Database Optimization

```php
/**
 * Optimize database queries
 */

// Add indexes for better performance
function bp_share_create_indexes() {
    global $wpdb;
    
    $indexes = array(
        "ALTER TABLE {$wpdb->prefix}bp_activity_meta 
         ADD INDEX idx_share_meta (meta_key(20), meta_value(50))",
         
        "ALTER TABLE {$wpdb->prefix}bp_share_tracking 
         ADD INDEX idx_user_activity (user_id, activity_id)",
         
        "ALTER TABLE {$wpdb->prefix}bp_share_tracking 
         ADD INDEX idx_service_date (service, timestamp)"
    );
    
    foreach ($indexes as $index) {
        $wpdb->query($index);
    }
}

// Batch processing for large operations
function bp_share_batch_update_statistics() {
    global $wpdb;
    
    $batch_size = 100;
    $offset = 0;
    
    do {
        $activities = $wpdb->get_results($wpdb->prepare(
            "SELECT activity_id, COUNT(*) as count 
             FROM {$wpdb->prefix}bp_share_tracking 
             GROUP BY activity_id 
             LIMIT %d OFFSET %d",
            $batch_size,
            $offset
        ));
        
        foreach ($activities as $activity) {
            bp_activity_update_meta(
                $activity->activity_id,
                'total_external_shares',
                $activity->count
            );
        }
        
        $offset += $batch_size;
        
        // Prevent timeout
        if ($offset % 500 === 0) {
            sleep(1);
        }
        
    } while (count($activities) === $batch_size);
}
```

### Asset Loading Optimization

```php
/**
 * Lazy load share functionality
 */
add_action('wp_enqueue_scripts', function() {
    // Only load on pages with activities
    if (!bp_is_activity_component() && !bp_is_group() && !bp_is_user()) {
        return;
    }
    
    // Load core script with defer
    wp_enqueue_script(
        'bp-share-lazy',
        BP_SHARE_URL . 'assets/js/lazy-share.js',
        array('jquery'),
        BP_SHARE_VERSION,
        true
    );
    
    // Inline critical CSS
    $critical_css = '.bp-share-buttons{display:flex;gap:10px;}';
    wp_add_inline_style('bp-share-main', $critical_css);
    
    // Defer non-critical CSS
    add_filter('style_loader_tag', function($html, $handle) {
        if ('bp-share-icons' === $handle) {
            return str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.rel='stylesheet'\"", $html);
        }
        return $html;
    }, 10, 2);
});
```

---

## Security Best Practices

### Input Sanitization

```php
/**
 * Sanitize all user inputs
 */
class BP_Share_Security {
    
    public static function sanitize_share_data($data) {
        $sanitized = array();
        
        // Sanitize activity ID
        $sanitized['activity_id'] = absint($data['activity_id']);
        
        // Sanitize share type
        $allowed_types = array('profile', 'group', 'message');
        $sanitized['type'] = in_array($data['type'], $allowed_types) 
            ? sanitize_key($data['type']) 
            : 'profile';
        
        // Sanitize message
        $sanitized['message'] = wp_kses_post($data['message']);
        
        // Sanitize service name
        $sanitized['service'] = sanitize_key($data['service']);
        
        return $sanitized;
    }
    
    public static function verify_share_permission($user_id, $activity_id) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Check if activity exists
        $activity = bp_activity_get_specific(array('activity_ids' => $activity_id));
        if (empty($activity['activities'])) {
            return false;
        }
        
        $activity = $activity['activities'][0];
        
        // Check privacy
        if ($activity->hide_sitewide && $activity->user_id !== $user_id) {
            return false;
        }
        
        // Check user capabilities
        if (!bp_activity_user_can_read($activity, $user_id)) {
            return false;
        }
        
        // Allow filtering
        return apply_filters('bp_share_user_can_share', true, $user_id, $activity);
    }
}

// Nonce verification for AJAX requests
add_action('wp_ajax_bp_share_activity', function() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'bp_share_nonce')) {
        wp_die('Security check failed');
    }
    
    // Verify user permission
    if (!BP_Share_Security::verify_share_permission(
        get_current_user_id(),
        $_POST['activity_id']
    )) {
        wp_die('Permission denied');
    }
    
    // Sanitize data
    $data = BP_Share_Security::sanitize_share_data($_POST);
    
    // Process share
    bp_share_process($data);
});
```

### Data Validation

```php
/**
 * Validate share data before processing
 */
function bp_share_validate_data($data) {
    $errors = array();
    
    // Validate activity ID
    if (empty($data['activity_id']) || !is_numeric($data['activity_id'])) {
        $errors[] = 'Invalid activity ID';
    }
    
    // Validate URL
    if (!empty($data['url']) && !filter_var($data['url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Invalid URL';
    }
    
    // Validate service
    $valid_services = bp_share_get_services();
    if (!isset($valid_services[$data['service']])) {
        $errors[] = 'Invalid service';
    }
    
    // Rate limiting
    if (bp_share_is_rate_limited(get_current_user_id())) {
        $errors[] = 'Too many shares. Please wait.';
    }
    
    if (!empty($errors)) {
        return new WP_Error('validation_failed', implode(', ', $errors));
    }
    
    return true;
}

// Rate limiting implementation
function bp_share_is_rate_limited($user_id) {
    $transient_key = 'bp_share_rate_' . $user_id;
    $shares = get_transient($transient_key);
    
    if ($shares >= 10) { // Max 10 shares per minute
        return true;
    }
    
    set_transient($transient_key, $shares + 1, 60);
    return false;
}
```

---

## Testing & Debugging

### Unit Tests

```php
/**
 * PHPUnit test examples
 */
class BP_Share_Test extends WP_UnitTestCase {
    
    public function setUp() {
        parent::setUp();
        
        // Create test user
        $this->user_id = $this->factory->user->create();
        wp_set_current_user($this->user_id);
        
        // Create test activity
        $this->activity_id = bp_activity_add(array(
            'user_id' => $this->user_id,
            'content' => 'Test activity content',
            'type' => 'activity_update'
        ));
    }
    
    public function test_share_button_display() {
        $buttons = bp_share_get_buttons($this->activity_id);
        
        $this->assertNotEmpty($buttons);
        $this->assertContains('bp-share-buttons', $buttons);
    }
    
    public function test_internal_share() {
        $result = bp_share_internal(array(
            'activity_id' => $this->activity_id,
            'type' => 'profile',
            'user_id' => $this->user_id
        ));
        
        $this->assertTrue($result);
        $this->assertEquals(1, bp_share_get_count($this->activity_id));
    }
    
    public function test_tracking_parameters() {
        $params = apply_filters('bp_share_tracking_parameters', array(), $this->activity_id, 'facebook');
        
        $this->assertArrayHasKey('utm_source', $params);
        $this->assertArrayHasKey('utm_medium', $params);
        $this->assertEquals('buddypress_share', $params['utm_source']);
    }
    
    public function test_share_permissions() {
        // Test logged out user
        wp_set_current_user(0);
        $this->assertFalse(bp_share_user_can_share(0, $this->activity_id));
        
        // Test logged in user
        wp_set_current_user($this->user_id);
        $this->assertTrue(bp_share_user_can_share($this->user_id, $this->activity_id));
    }
}
```

### Debug Logging

```php
/**
 * Enable debug logging
 */
if (!defined('BP_SHARE_DEBUG')) {
    define('BP_SHARE_DEBUG', true);
}

function bp_share_log($message, $data = null) {
    if (!BP_SHARE_DEBUG) {
        return;
    }
    
    $log_file = WP_CONTENT_DIR . '/bp-share-debug.log';
    
    $entry = sprintf(
        "[%s] %s\n",
        current_time('mysql'),
        $message
    );
    
    if ($data !== null) {
        $entry .= print_r($data, true) . "\n";
    }
    
    error_log($entry, 3, $log_file);
}

// Usage examples
bp_share_log('Share button clicked', array(
    'activity_id' => $activity_id,
    'service' => $service,
    'user_id' => get_current_user_id()
));

// Debug mode for JavaScript
add_action('wp_enqueue_scripts', function() {
    if (BP_SHARE_DEBUG) {
        wp_localize_script('bp-share-main', 'bp_share_debug', array(
            'enabled' => true,
            'log_level' => 'verbose'
        ));
    }
});
```

### Browser Console Debugging

```javascript
// Enable debug mode in JavaScript
if (bp_share_debug && bp_share_debug.enabled) {
    
    window.BPShareDebug = {
        log: function(message, data) {
            if (bp_share_debug.log_level === 'verbose') {
                console.group('BP Share Debug');
                console.log(message);
                if (data) {
                    console.table(data);
                }
                console.groupEnd();
            }
        },
        
        trackEvent: function(event, data) {
            this.log('Event: ' + event, data);
            
            // Send to server for logging
            jQuery.post(ajaxurl, {
                action: 'bp_share_log_event',
                event: event,
                data: JSON.stringify(data),
                nonce: bp_share_vars.nonce
            });
        }
    };
    
    // Override share function for debugging
    const originalShare = BPShare.shareExternal;
    BPShare.shareExternal = function(platform, activityId) {
        BPShareDebug.trackEvent('share_attempt', {
            platform: platform,
            activity_id: activityId,
            timestamp: Date.now()
        });
        
        return originalShare.apply(this, arguments);
    };
}
```

---

## Advanced Features

### A/B Testing Implementation

```php
/**
 * Implement A/B testing for share buttons
 */
class BP_Share_AB_Testing {
    
    public static function get_variant($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Consistent variant for each user
        $variant = get_user_meta($user_id, 'bp_share_ab_variant', true);
        
        if (!$variant) {
            $variant = (rand(0, 1) === 0) ? 'control' : 'variant';
            update_user_meta($user_id, 'bp_share_ab_variant', $variant);
        }
        
        return $variant;
    }
    
    public static function render_buttons($activity_id) {
        $variant = self::get_variant();
        
        if ($variant === 'control') {
            // Original button style
            return bp_share_get_default_buttons($activity_id);
        } else {
            // Variant with different style/position
            return bp_share_get_variant_buttons($activity_id);
        }
    }
    
    public static function track_conversion($variant, $platform) {
        // Track which variant performs better
        $conversions = get_option('bp_share_ab_conversions', array());
        
        if (!isset($conversions[$variant])) {
            $conversions[$variant] = array();
        }
        
        if (!isset($conversions[$variant][$platform])) {
            $conversions[$variant][$platform] = 0;
        }
        
        $conversions[$variant][$platform]++;
        
        update_option('bp_share_ab_conversions', $conversions);
    }
}
```

### Machine Learning Integration

```php
/**
 * Predict share likelihood using ML
 */
class BP_Share_ML_Predictor {
    
    public static function predict_share_probability($activity_id, $user_id) {
        // Collect features
        $features = array(
            'activity_age' => self::get_activity_age($activity_id),
            'content_length' => self::get_content_length($activity_id),
            'has_media' => self::has_media($activity_id),
            'author_popularity' => self::get_author_popularity($activity_id),
            'time_of_day' => date('G'),
            'day_of_week' => date('w'),
            'user_share_history' => self::get_user_share_rate($user_id)
        );
        
        // Send to ML API
        $response = wp_remote_post('https://ml-api.example.com/predict', array(
            'body' => json_encode($features),
            'headers' => array('Content-Type' => 'application/json')
        ));
        
        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            return $body['probability'];
        }
        
        return 0.5; // Default probability
    }
    
    public static function should_promote_activity($activity_id) {
        $probability = self::predict_share_probability($activity_id, get_current_user_id());
        
        // Promote activities with high share probability
        return $probability > 0.7;
    }
}
```

---

## Conclusion

This comprehensive developer guide covers all aspects of BuddyPress Activity Share Pro development. Use these examples and references to:

- Extend functionality with custom features
- Integrate with third-party services
- Optimize performance
- Implement advanced tracking
- Create custom share platforms
- Debug and test effectively

For additional support:
- **Documentation**: https://wbcomdesigns.com/docs/
- **Support Forum**: https://wbcomdesigns.com/support/
- **GitHub**: https://github.com/wbcomdesigns/

---

*Last Updated: Version 2.0.0*
*© 2024 Wbcom Designs - All Rights Reserved*