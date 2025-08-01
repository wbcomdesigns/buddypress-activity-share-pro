# Post Type Filtering in BuddyPress Activity Share Pro

## Overview

The plugin intelligently filters out internal and system post types that shouldn't have social sharing functionality. This ensures a clean, user-friendly interface that only shows relevant content types.

## Filtering Criteria

Post types are included ONLY if they meet ALL of these criteria:

1. **Public**: `$post_type->public === true`
2. **Has UI**: `$post_type->show_ui === true`
3. **Publicly Queryable**: `$post_type->publicly_queryable === true`
4. **Not Excluded from Search**: `$post_type->exclude_from_search === false`
5. **Standard Capability Type**: `capability_type` is 'post' or 'page'
6. **Not Attachment**: Post type is not 'attachment'
7. **No Internal Patterns**: Doesn't contain internal keywords

## Automatically Excluded Post Types

### Page Builder Templates
- `elementor_library` - Elementor Templates
- `e-floating-buttons` - Elementor Floating Buttons
- `e-landing-page` - Elementor Landing Pages
- Any post type containing `elementor`, `e-floating`, or `e-landing`
- `fl-builder-template` - Beaver Builder Templates
- `et_pb_layout` - Divi Layouts
- `vc_*` - Visual Composer Elements
- `fusion_template` - Avada Templates
- `brizy*` - Brizy Templates
- `oxygen*` - Oxygen Templates

### WordPress Core
- `wp_block` - Reusable Blocks
- `wp_template` - FSE Templates
- `wp_template_part` - Template Parts
- `wp_navigation` - Navigation Menus
- `customize_changeset` - Customizer Changesets
- `custom_css` - Custom CSS
- `revision` - Post Revisions
- `nav_menu_item` - Menu Items
- `oembed_cache` - oEmbed Cache

### Form Plugins
- `wpcf7_contact_form` - Contact Form 7
- `wpforms` - WPForms
- `mc4wp-form` - Mailchimp Forms
- `ninja*` - Ninja Forms/Tables

### E-commerce
- `shop_order` - WooCommerce Orders
- `shop_order_refund` - Refunds
- `shop_coupon` - Coupons

### Others
- `acf-field-group` - ACF Field Groups
- `acf-field` - ACF Fields
- `tablepress_table` - TablePress Tables
- Any post type starting with `_`

## What's Included

### Always Included (Core Types)
- **Posts** - Standard blog posts
- **Pages** - Static pages

### Whitelisted Types (Community/Commerce)
- **Forums** (bbPress)
- **Topics** (bbPress)
- **Replies** (bbPress)
- **Products** (WooCommerce)
- **Courses** (LearnDash/LifterLMS/General LMS)
- **Lessons** (LearnDash/LifterLMS/General LMS)
- **Quizzes** (LearnDash/LifterLMS/General LMS)
- **Events** (The Events Calendar/General Event plugins)
- **Venues** (The Events Calendar)
- **Organizers** (The Events Calendar)
- **Downloads** (Easy Digital Downloads)
- **Portfolio Items**
- **Projects**
- **Team Members**
- **Testimonials**
- **Books**
- **Movies**
- **Recipes**
- **Job Listings** (WP Job Manager)
- **Resumes** (WP Job Manager)
- **Properties** (Real Estate plugins)
- **Listings** (Directory plugins)
- **Donations** (GiveWP/Charitable)
- **Causes** (Fundraising plugins)

### Other Valid Custom Types
Any custom post type that:
- Is public
- Has a UI
- Is publicly queryable
- Not excluded from search
- Doesn't match internal patterns

## Customization

### Add Custom Exclusions

```php
add_filter( 'bp_share_available_post_types', function( $post_types ) {
    // Remove a specific post type
    unset( $post_types['portfolio'] );
    
    return $post_types;
});
```

### Force Include a Post Type

```php
add_filter( 'bp_share_available_post_types', function( $post_types ) {
    // Force include a post type that was filtered out
    $post_type_obj = get_post_type_object( 'my_internal_type' );
    if ( $post_type_obj ) {
        $post_types['my_internal_type'] = $post_type_obj;
    }
    
    return $post_types;
});
```

### Debug: See All Post Types

```php
add_action( 'init', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    $all_types = get_post_types( array(), 'objects' );
    $settings = BP_Share_Post_Type_Settings::get_instance();
    
    error_log( 'All registered post types:' );
    foreach ( $all_types as $name => $obj ) {
        $valid = $settings->is_valid_post_type( $name );
        error_log( sprintf(
            '%s - Public: %s, UI: %s, Queryable: %s, Valid: %s',
            $name,
            $obj->public ? 'Y' : 'N',
            $obj->show_ui ? 'Y' : 'N',
            $obj->publicly_queryable ? 'Y' : 'N',
            $valid ? 'YES' : 'NO'
        ) );
    }
}, 100 );
```

## Benefits

1. **Cleaner Interface**: Users only see relevant post types
2. **Better Performance**: No unnecessary options
3. **Prevents Errors**: Internal types often don't have proper URLs
4. **User-Friendly**: Less confusion about what can be shared
5. **Future-Proof**: New internal types are automatically excluded