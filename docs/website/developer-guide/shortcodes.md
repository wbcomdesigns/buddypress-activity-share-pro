# Shortcode Reference

BuddyPress Activity Share Pro registers one shortcode. Use it when you need to place the share button somewhere specific in a template or page — for example, inside a custom single-post layout or a page builder block.

---

## `[bp_activity_post_reshare]`

Renders the BuddyPress share button on a single blog post page. The button opens the share modal, displays the share count next to it, and lets the visitor post the article to the BuddyPress activity stream or any configured social network.

**Registered in:** `public/class-buddypress-share-public.php`, line 1931  
**Since:** 1.0.0

### Where it works

This shortcode only produces output when both conditions are true:

- The current page is a single post (`is_single()` returns `true`).
- The post type is `post` (standard WordPress blog posts).

On archives, pages, custom post types, or BuddyPress pages the shortcode returns an empty string without error.

### Parameters

This shortcode accepts no parameters. Add it exactly as shown below.

```
[bp_activity_post_reshare]
```

### Output

The shortcode renders a `<div>` containing an anchor element styled as a share button. The button displays:

- A share icon (from the plugin's icon font).
- The label **Share**.
- The current share count for the post (blank when the count is zero).

When the visitor clicks the button, the share modal opens. The modal content and available networks are controlled by the plugin's settings in **WBcom Designs > BuddyPress Share**.

### When to use this shortcode

Most themes do not need this shortcode. The plugin hooks into `the_content` filter automatically and appends the share button to every single post. The shortcode exists for two specific situations:

1. **Themes that handle their own content rendering** — Reign Theme and BuddyX Pro integrate the button through their own hooks. Other themes that bypass `the_content` filter may need the shortcode to place the button manually.
2. **Custom template placement** — When the auto-appended position (end of post content) does not match your design, remove or suppress the automatic button and place the shortcode where you want it in your template instead.

### Usage example — PHP template

To place the button inside a template file (for example, `single.php` in a child theme), use `do_shortcode`:

```php
<?php echo do_shortcode( '[bp_activity_post_reshare]' ); ?>
```

Call this inside the Loop where `get_the_ID()` returns the correct post ID. Calling it outside the Loop produces an empty string.

### Usage example — Page builder or block editor

If you are using a page builder (Elementor, Beaver Builder) or the block editor on a post, add a shortcode block or shortcode element and enter:

```
[bp_activity_post_reshare]
```

The block editor's shortcode block renders the button correctly on the front end only — the button does not preview inside the editor canvas.

### Interaction with the "Blog Posts" sharing restriction

The admin setting **Content Restrictions > Blog Posts** (stored as `disable_post_reshare_activity`) controls only the automatic `the_content` injection. If that setting is enabled to suppress automatic injection, the shortcode still renders the button wherever you placed it. There is no admin option to disable the shortcode independently.

### CSS classes on the output

| Class | Element | Purpose |
|---|---|---|
| `bp-activity-post-share-btn` | `<div>` | Outer wrapper |
| `bp-activity-share-btn` | `<div>` | Secondary outer wrapper |
| `generic-button` | `<div>` | Compatibility class for theme button styles |
| `bp-activity-share-button` | `<a>` | The clickable link |
| `bp-secondary-action` | `<a>` | BuddyPress secondary action style |
| `bp-activity-reshare-icon` | `<span>` | Icon wrapper |
| `as-icon-share-square` | `<i>` | Share icon glyph |
| `bp-share-text` | `<span>` | "Share" label |
| `bp-post-reshare-count` | `<span>` | Share count display |

The `<span>` carrying the share count has a dynamic `id` attribute in the format `bp-activity-reshare-count-{post_id}`. This ID is used by the plugin's JavaScript to update the count without a page reload after a share is recorded.

### Troubleshooting

**The button appears but the modal does not open.**  
The plugin's CSS and JavaScript must be loaded on the page. Assets load automatically on BuddyPress pages, single posts, and pages that contain the `[activity-listing]` shortcode. If the post does not match any of these conditions, use the `bp_activity_share_load_assets` filter to force asset loading:

```php
add_filter( 'bp_activity_share_load_assets', '__return_true' );
```

Add this to your child theme's `functions.php`.

**The shortcode outputs nothing.**  
Confirm that:

- You are on a single post page (`is_single()` is `true`).
- The post type is `post`, not a custom post type.
- The shortcode tag is typed exactly as `[bp_activity_post_reshare]` with no extra attributes.

**The button appears twice (once from the shortcode and once appended to content).**  
The plugin's automatic injection runs on `the_content` for themes that are not on the built-in support list (Reign Theme, BuddyX Pro). If you place the shortcode manually, suppress the automatic version by unhooking it:

```php
add_action( 'wp_loaded', function() {
    $public = buddypress_share()->get_public_instance();
    remove_filter( 'the_content', array( $public, 'bp_activity_post_share_button_action' ), 999 );
} );
```

Replace `buddypress_share()->get_public_instance()` with the actual method your plugin version exposes if the loader structure differs.
