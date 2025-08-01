# How to Enable Post Type Sharing in BuddyPress Activity Share Pro

## Quick Start

To see the floating share widget on your posts, follow these steps:

### 1. Enable the Feature

Add this code to your theme's `functions.php` file:

```php
// Enable post type sharing feature
add_filter( 'bp_share_enable_post_type_sharing', '__return_true' );
```

### 2. Clear Cache

- Clear your browser cache
- Clear any WordPress caching plugins
- Clear server cache if applicable

### 3. Visit a Single Post

Navigate to any single post on your site (e.g., http://yoursite.com/2025/07/03/hello-world/)

You should see a floating share button on the right side of the screen.

## Troubleshooting

### Widget Not Showing?

1. **Check if you're on a single post/page**
   - The widget only appears on single posts, not on archive pages or homepage

2. **Verify the code is added correctly**
   - Make sure the filter code is in your active theme's functions.php
   - Check for PHP errors in your error log

3. **Check browser console**
   - Open browser developer tools (F12)
   - Look for any JavaScript errors

4. **Verify plugin is active**
   - Go to Plugins page
   - Make sure BuddyPress Activity Share Pro is activated

### Backend Settings Not Saving?

1. **Check permissions**
   - Make sure you're logged in as an administrator
   - Verify you have the `manage_options` capability

2. **Clear browser cache**
   - Sometimes form data can be cached

3. **Check for JavaScript errors**
   - Open browser console while saving

## Customization

### Change Position

```php
// Position on left side instead of right
add_filter( 'bp_share_wrapper_position', function() {
    return 'left';
});
```

### Enable for Specific Post Types

```php
// Enable for posts and pages only
add_filter( 'bp_share_post_type_default_settings', function( $settings ) {
    $settings['enabled_post_types'] = array( 'post', 'page' );
    return $settings;
});
```

### Customize Services

```php
// Only show specific services
add_filter( 'bp_share_post_type_services', function( $services, $post_type ) {
    if ( $post_type === 'post' ) {
        return array( 'facebook', 'twitter', 'copy' );
    }
    return $services;
}, 10, 2 );
```

## Light Color Scheme

The widget now uses a light, minimal color scheme with:
- Clean white backgrounds
- Subtle gray borders
- Soft shadows
- Muted colors for better readability

## Admin Settings

1. Go to **Settings > Activity Share** (or **WB Plugins > BP Activity Share Pro** if using WBCom wrapper)
2. Click on **Post Type Sharing** tab
3. Configure:
   - Which post types have sharing
   - Which services appear for each post type
   - Position (left/right)
   - Mobile behavior

## Default Configuration

By default, when enabled:
- Posts have sharing enabled
- Services: Facebook, Twitter, LinkedIn, WhatsApp, Copy Link
- Position: Right side
- Mobile: Bottom bar

## For Developers

### Check if Feature is Active

```php
if ( apply_filters( 'bp_share_enable_post_type_sharing', false ) ) {
    // Feature is enabled
}
```

### Add Custom Service

```php
add_filter( 'bp_share_post_type_available_services', function( $services ) {
    $services['custom'] = array(
        'name' => 'Custom Service',
        'icon' => 'fas fa-share',
        'enabled_by_default' => false
    );
    return $services;
});
```

### Track Custom Events

```php
add_action( 'bp_share_post_shared', function( $post_id, $service, $user_id ) {
    // Your custom tracking code
}, 10, 3 );
```