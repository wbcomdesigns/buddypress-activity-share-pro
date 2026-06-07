---
title: "Hooks & Filters Reference"
slug: "hooks-filters-bpas"
category: "developer-guide"
short_id: "bpas"
---

# Hooks & Filters Reference

This page documents every action hook and filter that BuddyPress Activity Share Pro exposes. Use them to extend or modify plugin behavior without editing plugin files directly.

All hooks live in the `buddypress-share` text domain. Hook names beginning with `bp_share_` are registered by this plugin; do not confuse them with core BuddyPress hooks that follow a similar naming pattern.

---

## Actions

Actions fire at specific points in the plugin lifecycle. Add your own `add_action()` calls to tap into them.

### Activity (Internal) Resharing

#### `bp_share_after_create_reshare`

Fires after a reshare activity has been successfully created in the database.

**Since:** 1.5.2  
**File:** `public/class-buddypress-share-public.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$new_activity_id` | `int` | ID of the newly created share activity. |
| `$reshare_data` | `array` | Reshare data: `user_id`, `activity_id`, `activity_type`, `activity_content`, `activity_in`, `destination_type`. |

**Example use case:** Send a notification or award points when someone reshares an activity.

```php
add_action( 'bp_share_after_create_reshare', function( $new_activity_id, $reshare_data ) {
    // Award points to the sharer
    $user_id = $reshare_data['user_id'];
    my_points_plugin_award( $user_id, 5, 'activity_reshare' );
}, 10, 2 );
```

---

#### `bp_share_user_reshared_activity`

Fires after a reshare completes. Designed specifically for point systems and gamification plugins because it exposes both the original and new activity IDs alongside the destination type.

**Since:** 2.0.0  
**File:** `public/class-buddypress-share-public.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$user_id` | `int` | The user who performed the reshare. |
| `$destination_type` | `string` | Where the reshare was posted: `profile`, `group`, or `friend`. |
| `$original_activity_id` | `int` | The original activity that was reshared. |
| `$new_activity_id` | `int` | The newly created reshare activity. |

**Example use case:** Connect a GamiPress or myCRED points rule to resharing.

```php
add_action( 'bp_share_user_reshared_activity', function( $user_id, $destination_type, $original_activity_id, $new_activity_id ) {
    if ( $destination_type === 'group' ) {
        // Extra points for sharing to a group
        mycred_add( 'reshare_to_group', $user_id, 10, 'Shared activity to group' );
    }
}, 10, 4 );
```

---

### Post-Type Sharing

#### `bp_share_post_shared`

Fires after a share event for a standard WordPress post (or custom post type) has been recorded in the tracking table.

**Since:** 2.0.0  
**File:** `includes/post-types/class-bp-share-post-type-tracker.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$post_id` | `int` | ID of the post that was shared. |
| `$service` | `string` | Social service key (e.g. `facebook`, `twitter`, `whatsapp`). |
| `$user_id` | `int\|null` | User ID, or `null` if shared anonymously. |
| `$metadata` | `array` | Additional tracking data: `post_type`, `ip_address`, `user_agent`, `referrer`, `shared_at`. |

**Note:** This hook only fires when the custom tracking tables exist. If the tables are absent, deactivate and reactivate the plugin so the installer can recreate them.

```php
add_action( 'bp_share_post_shared', function( $post_id, $service, $user_id, $metadata ) {
    // Log to external analytics endpoint
    wp_remote_post( 'https://analytics.example.com/share', array(
        'body' => array(
            'post'    => $post_id,
            'service' => $service,
            'user'    => $user_id,
        ),
    ) );
}, 10, 4 );
```

---

#### `bp_share_post_visit_tracked`

Fires when a visitor follows a share link back to the site. The URL includes UTM and `bps_*` parameters that identify the original share event.

**Since:** 2.0.0  
**File:** `includes/post-types/class-bp-share-post-type-controller.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$visit_data` | `array` | Visit context: `post_id`, `service`, `shared_by` (user ID), `visitor_ip`, `timestamp`, `referrer`. |
| `$post_id` | `int` | ID of the post being visited. |

---

#### `bp_share_post_visit_tracked_data`

Fires inside `BP_Share_Post_Type_Tracker::track_visit()` with the raw visit data before it is written to post meta. Useful for routing visit data to a custom table or analytics service.

**Since:** 2.0.0  
**File:** `includes/post-types/class-bp-share-post-type-tracker.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$visit_data` | `array` | Visit tracking data array. |

---

### Activity Tracking

#### `bp_share_internal_share_tracked`

Fires after an internal reshare event has been recorded (user meta and activity meta updated).

**Since:** 2.0.0  
**File:** `includes/class-buddypress-share-tracker.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$share_data` | `array` | Share context: `user_id`, `activity_id`, `new_activity_id`, `share_type`, `destination_type`, `timestamp`, `ip_address`. |
| `$user_id` | `int` | The user who performed the reshare. |

---

#### `bp_share_external_visit_tracked`

Fires when a visitor arrives at the site via a tracked share link (the URL contains `bps_aid` and `bps_service` parameters).

**Since:** 2.0.0  
**File:** `includes/class-buddypress-share-tracker.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$visit_data` | `array` | Visit context: `activity_id`, `service`, `shared_by`, `visitor_ip`, `timestamp`, `referrer`. |

---

#### `bp_share_external_share_tracked`

Fires after an external share event has been processed via the `bp_share_track_external` AJAX endpoint.

**Since:** 2.0.0  
**File:** `includes/class-buddypress-share-tracker.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$share_data` | `array` | Share context: `user_id`, `activity_id`, `share_type`, `service`, `timestamp`, `ip_address`. |

---

#### `bp_share_user_stats_updated`

Fires after a user's share statistics have been updated in user meta (`bp_share_user_stats`).

**Since:** 2.0.0  
**File:** `includes/class-buddypress-share-tracker.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$user_id` | `int` | The user whose stats were updated. |
| `$stats` | `array` | Updated statistics: `total_shares`, `internal_shares`, `external_shares`, `last_share_date`, `share_breakdown`. |

---

#### `bp_share_activity_stats_updated`

Fires after an activity's share statistics have been updated in activity meta (`bp_share_activity_stats`).

**Since:** 2.0.0  
**File:** `includes/class-buddypress-share-tracker.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$activity_id` | `int` | The activity whose stats were updated. |
| `$stats` | `array` | Updated statistics: `total_shares`, `internal_shares`, `external_shares`, `unique_sharers`, `last_share_date`. |

---

#### `bp_share_activity_visit_stats_updated`

Fires after an activity's visit statistics have been updated in activity meta (`bp_share_visit_stats`).

**Since:** 2.0.0  
**File:** `includes/class-buddypress-share-tracker.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$activity_id` | `int` | The activity whose visit stats were updated. |
| `$stats` | `array` | Updated statistics: `total_visits`, `service_visits`, `last_visit_date`. |

---

### Admin / Settings

#### `bp_share_clear_public_cache`

Fires whenever the admin saves settings and the plugin clears its internal cache (transients and object cache). Hook in here if your code caches anything derived from plugin settings.

**Since:** 1.5.1  
**File:** `admin/class-buddypress-share-admin.php`

**Parameters:** none

```php
add_action( 'bp_share_clear_public_cache', function() {
    // Purge your own derived cache
    delete_transient( 'my_plugin_share_config' );
} );
```

---

#### `bp_share_before_sanitize_extra_settings`

Fires before the plugin sanitizes the "extra settings" options array (`bp_share_services_extra`). The raw `$input` is passed for inspection only — modifying it here has no effect on what gets sanitized.

**Since:** (undocumented, present in admin class)  
**File:** `admin/class-buddypress-share-admin.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$input` | `array` | Raw, unsanitized input from the settings form. |

---

#### `bp_share_after_sanitize_extra_settings`

Fires after the "extra settings" options array has been sanitized and is about to be saved.

**Since:** (undocumented, present in admin class)  
**File:** `admin/class-buddypress-share-admin.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$sanitized` | `array` | The sanitized settings that will be saved. |
| `$input` | `array` | The original raw input. |

---

### Rendering Hooks

#### `bp_share_user_services`

An action rendered inside the share dropdown, after the social network buttons. Use it to output additional share destinations (for example, a custom private message button).

**Since:** 1.5.2  
**File:** `public/class-buddypress-share-public.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$args` | `array` | Reserved; always an empty array currently. |
| `$activity_link` | `string` | The permalink for the activity being shared. |
| `$activity_title` | `string` | The title of the activity being shared. |

```php
add_action( 'bp_share_user_services', function( $args, $activity_link, $activity_title ) {
    echo '<div class="bp-share-wrapper">';
    echo '<a class="button bp-share" href="' . esc_url( '/my-custom-share/?url=' . urlencode( $activity_link ) ) . '">';
    echo '<i class="fas fa-envelope"></i> <span>Direct Message</span>';
    echo '</a></div>';
}, 10, 3 );
```

---

#### `bp_activity_share_before_post_meta`

Fires before the plugin outputs the reshare modal inner content (the post meta section inside the modal). Use it to inject content at the top of the modal form.

**Since:** (undocumented, present in public class)  
**File:** `public/class-buddypress-share-public.php`

**Parameters:** none

---

#### `bp_activity_share_after_post_meta`

Fires after the plugin outputs the reshare modal inner content. Use it to inject content at the bottom of the modal form.

**Since:** (undocumented, present in public class)  
**File:** `public/class-buddypress-share-public.php`

**Parameters:** none

---

### Plugin Lifecycle

#### `bp_share_plugin_upgraded`

Fires when the plugin detects an upgrade (stored version differs from current version).

**Since:** 2.0.0  
**File:** `includes/class-buddypress-share.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$old_version` | `string` | Version string before the upgrade. |
| `$new_version` | `string` | Current (new) version string. |

---

#### `bp_share_deactivated`

Fires during the plugin's `deactivate_` hook, before WP removes the plugin from the active list.

**Since:** 2.0.0  
**File:** `includes/class-buddypress-share.php`

**Parameters:** none

---

#### `bp_share_uninstalled`

Fires during `uninstall.php` execution. Note: the uninstall file currently contains only the `WP_UNINSTALL_PLUGIN` guard and this hook call — no actual cleanup logic runs unless you add it.

**Since:** 2.0.0  
**File:** `includes/class-buddypress-share.php`

**Parameters:** none

---

## Filters

Filters let you modify data before the plugin uses it. Always return the value, even if you choose not to change it.

### Services & Display

#### `bp_share_available_services`

Modifies the list of social services that appear in the admin drag-and-drop interface. The array keys are internal service identifiers; the values are the display labels.

**Since:** 1.5.2  
**File:** `admin/class-buddypress-share-admin.php`

**Default value:** `Facebook`, `X` (Twitter), `LinkedIn`, `Pinterest`, `Reddit`, `WordPress`, `Pocket`, `Telegram`, `Bluesky`, `WhatsApp`, `E-mail`, `Copy-Link`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$services` | `array` | Associative array of `'ServiceKey' => 'Display Label'`. |

**Return:** `array`

**Example — add a custom service:**

```php
add_filter( 'bp_share_available_services', function( $services ) {
    $services['Mastodon'] = 'Mastodon';
    return $services;
} );
```

**Example — remove a service from the available list:**

```php
add_filter( 'bp_share_available_services', function( $services ) {
    unset( $services['Pinterest'] );
    return $services;
} );
```

---

#### `bp_share_services_config`

Modifies the full configuration for each service used to build share URLs. Each entry in the array contains the icon class, share URL template, and display label. This filter runs on every activity page load, so keep callbacks lightweight.

**Since:** 1.5.2  
**File:** `public/class-buddypress-share-public.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$services` | `array` | Full config array keyed by service name. Each value has `icon`, `url`, `title`. |
| `$activity_link` | `string` | Current activity permalink. |
| `$activity_title` | `string` | Current activity title. |
| `$mail_subject` | `string` | Subject line pre-filled for the E-mail service. |

**Return:** `array`

---

#### `bp_share_social_button_html`

Modifies the HTML for a single social share button before it is output.

**Since:** 1.5.2  
**File:** `public/class-buddypress-share-public.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$button_html` | `string` | The complete HTML string for the button. |
| `$service` | `string` | The service name (e.g. `Facebook`, `X`). |
| `$details` | `array` | The service config array (icon, url, title). |
| `$activity_link` | `string` | The activity permalink. |

**Return:** `string`

---

#### `bp_share_activity_data`

Modifies the activity link, title, and mail subject before they are passed to share buttons. Use this to customise what text and URL appear in shared content.

**Since:** 1.5.2  
**File:** `public/class-buddypress-share-public.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$share_data` | `array` | Array with keys `activity_link`, `activity_title`, `mail_subject`. |
| `$activity` | `object` | The current BuddyPress activity object. |

**Return:** `array` — must return the same array structure.

```php
add_filter( 'bp_share_activity_data', function( $share_data, $activity ) {
    // Prepend site name to the title used in shares
    $share_data['activity_title'] = get_bloginfo( 'name' ) . ': ' . $share_data['activity_title'];
    return $share_data;
}, 10, 2 );
```

---

### Resharing

#### `bp_share_before_create_reshare`

Modifies the reshare data array immediately before the plugin creates the new activity in the database. Return a modified copy; the plugin reads the result.

**Since:** 1.5.2  
**File:** `public/class-buddypress-share-public.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$reshare_data` | `array` | Array with keys: `user_id`, `activity_id`, `activity_type`, `activity_content`, `activity_in`, `destination_type`. |

**Return:** `array`

**Example — prepend text to reshare content:**

```php
add_filter( 'bp_share_before_create_reshare', function( $reshare_data ) {
    $reshare_data['activity_content'] = '[Reshared] ' . $reshare_data['activity_content'];
    return $reshare_data;
} );
```

---

#### `bp_activity_reshare_post_type`

Controls which post types display the reshare modal button when viewed on a singular post page.

**Since:** (undocumented, present in public class)  
**File:** `public/class-buddypress-share-public.php` and `buddypress-share.php`

**Default value:** `['post']`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$post_types` | `array` | Indexed array of post type slugs. |

**Return:** `array`

```php
// Show reshare modal on WooCommerce products too
add_filter( 'bp_activity_reshare_post_type', function( $post_types ) {
    $post_types[] = 'product';
    return $post_types;
} );
```

---

#### `bp_activity_reshare_action`

Forces the reshare modal and button to render on pages that are not normally BuddyPress pages and are not single post views. Return `true` to enable on the current page.

**Since:** (undocumented, present in public class)  
**File:** `public/class-buddypress-share-public.php`

**Default value:** `false`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$enable` | `bool` | Whether to enable the reshare button on the current page. |

**Return:** `bool`

---

### UTM / Tracking

#### `bp_share_tracking_parameters`

Modifies the UTM and `bps_*` query parameters appended to activity share links.

**Since:** 2.0.0  
**File:** `public/class-buddypress-share-public.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$tracking_params` | `array` | Default params: `utm_source`, `utm_medium`, `utm_campaign`, `bps_aid`, `bps_uid`, `bps_time`, `utm_content`, `bps_service`. |
| `$url` | `string` | The original URL before tracking params are added. |
| `$service` | `string` | The service name. |
| `$activity_id` | `int` | The activity being shared. |
| `$user_id` | `int` | The user sharing (0 if logged out). |

**Return:** `array`

**Privacy note:** The default params include `bps_uid` (user ID). If you need to remove it for privacy reasons, unset it here. See also `bp_share_disable_ip_tracking`.

```php
add_filter( 'bp_share_tracking_parameters', function( $params, $url, $service, $activity_id, $user_id ) {
    // Remove user ID from public share links
    unset( $params['bps_uid'] );
    return $params;
}, 10, 5 );
```

---

### Post-Type Sharing

#### `bp_share_enable_post_type_sharing`

Toggles the entire post-type sharing subsystem. Return `false` to disable it completely. Defaults to `true` (always on).

**Since:** 2.1.0  
**File:** `includes/class-buddypress-share.php`

**Default value:** `true`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$enabled` | `bool` | Whether post-type sharing should be initialised. |

**Return:** `bool`

```php
// Disable post-type sharing entirely
add_filter( 'bp_share_enable_post_type_sharing', '__return_false' );
```

---

#### `bp_share_post_url`

Modifies the final share URL for a specific service and post before it is returned to the frontend.

**Since:** 2.0.0  
**File:** `includes/post-types/class-bp-share-post-type-controller.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$url` | `string` | The share URL for the service. |
| `$service` | `string` | Service key (e.g. `facebook`, `twitter`). |
| `$post_id` | `int` | The post being shared. |

**Return:** `string`

---

#### `bp_share_post_tracking_url`

Modifies the post share URL after tracking parameters have been appended.

**Since:** 2.0.0  
**File:** `includes/post-types/class-bp-share-post-type-controller.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$url` | `string` | URL with tracking params already appended. |
| `$tracking_params` | `array` | The tracking parameters array. |
| `$service` | `string` | Service key. |
| `$post_id` | `int` | The post being shared. |

**Return:** `string`

---

#### `bp_share_allow_anonymous_sharing`

Controls whether logged-out visitors can trigger the post-share AJAX endpoint. Returns `true` by default so guest shares are tracked. Return `false` to require login.

**Since:** 2.0.0  
**File:** `includes/post-types/class-bp-share-post-type-controller.php`

**Default value:** `true`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$allow` | `bool` | Whether anonymous sharing is permitted. |

**Return:** `bool`

---

#### `bp_share_rate_limit`

Sets the maximum number of share events a single user/IP combination can record per hour. The default is 20.

**Since:** 2.0.0  
**File:** `includes/post-types/class-bp-share-post-type-controller.php`

**Default value:** `20`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$limit` | `int` | Maximum shares per hour. |

**Return:** `int`

```php
// Raise the rate limit for authenticated users
add_filter( 'bp_share_rate_limit', function( $limit ) {
    if ( is_user_logged_in() ) {
        return 100;
    }
    return $limit;
} );
```

---

### Privacy / GDPR

#### `bp_share_disable_ip_tracking`

Return `true` to skip IP address collection entirely. When disabled, the stored IP is replaced with the string `'anonymous'`.

**Since:** 2.0.0  
**File:** `includes/post-types/class-bp-share-post-type-tracker.php`

**Default value:** `false`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$disable` | `bool` | Whether to disable IP tracking. |

**Return:** `bool`

```php
// GDPR: never store IPs
add_filter( 'bp_share_disable_ip_tracking', '__return_true' );
```

---

#### `bp_share_anonymize_ip`

Return `true` to strip the last octet from IPv4 addresses before storage (e.g. `192.168.1.100` is stored as `192.168.1.0`). Only applied when IP tracking is not fully disabled.

**Since:** 2.0.0  
**File:** `includes/post-types/class-bp-share-post-type-tracker.php`

**Default value:** `false`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$anonymize` | `bool` | Whether to anonymize the stored IP. |

**Return:** `bool`

```php
add_filter( 'bp_share_anonymize_ip', '__return_true' );
```

---

### Settings Sanitization

#### `bp_share_sanitized_extra_settings`

Modifies the sanitized "extra settings" array before it is saved to the database.

**Since:** (undocumented, present in admin class)  
**File:** `admin/class-buddypress-share-admin.php`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$sanitized` | `array` | The sanitized settings array. |
| `$input` | `array` | The raw input array before sanitization. |

**Return:** `array`

---

### Asset Loading

#### `bp_share_use_cdn_assets`

Return `true` to load Bootstrap 4.6.2 and Select2 from the Cloudflare CDN instead of bundled copies. Defaults to `false` (use local/CDN — note: the plugin currently loads these from CDN regardless; this filter controls an intended local-bundling path).

**Since:** (undocumented, present in both admin and public classes)  
**File:** `admin/class-buddypress-share-admin.php`, `public/class-buddypress-share-public.php`

**Default value:** `false`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$use_cdn` | `bool` | Whether to use CDN assets. |

**Return:** `bool`

---

#### `bp_activity_share_load_assets`

Return `true` to force-load the plugin's CSS and JS on a page that is not a BuddyPress page and not a singular post. Useful if your theme outputs activity streams via a shortcode outside the standard BuddyPress template.

**Since:** (undocumented, present in public class)  
**File:** `public/class-buddypress-share-public.php`

**Default value:** `false`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$load` | `bool` | Whether to force-load assets on the current page. |

**Return:** `bool`

---

### Theme Compatibility

#### `buddypress_share_theme_support`

Lists the theme template slugs that have built-in support for the share button. For these themes, the plugin skips the `the_content` filter injection and relies on the theme's own template hook.

**Since:** (undocumented, present in main class)  
**File:** `includes/class-buddypress-share.php`

**Default value:** `['reign-theme', 'buddyx-pro']`

**Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `$themes` | `array` | Array of theme template slugs. |

**Return:** `array`

```php
// Register your custom theme for native support
add_filter( 'buddypress_share_theme_support', function( $themes ) {
    $themes[] = 'my-buddypress-theme';
    return $themes;
} );
```

---

## Hook Firing Order

The sequence below shows when the most frequently used hooks fire during a typical reshare request:

1. `bp_share_before_create_reshare` — filter, modify reshare data before DB write
2. Activity created in the database
3. `bp_share_after_create_reshare` — action, activity ID and data available
4. `bp_share_user_reshared_activity` — action, for points/rewards
5. Share count updated
6. `bp_share_internal_share_tracked` — action, user/activity meta updated
7. `bp_share_user_stats_updated` — action, after user meta write
8. `bp_share_activity_stats_updated` — action, after activity meta write

For post-type shares the order is:

1. Nonce verified, rate limit checked (`bp_share_rate_limit`)
2. `bp_share_allow_anonymous_sharing` checked
3. Share URL built (`bp_share_post_url`, `bp_share_post_tracking_url`)
4. Tracking record inserted
5. `bp_share_post_shared` — action

---

## Related Documentation

- Getting Started — see the plugin's main documentation index.
- Settings Reference — see the plugin's settings documentation.
