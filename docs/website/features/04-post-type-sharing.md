# Post Type Sharing

Post Type Sharing adds share buttons to standard WordPress content (posts and other public post types), separate from the BuddyPress activity stream. It has its own settings tab and its own frontend widget.

## What it does

- Renders share buttons on singular views of the post types you enable.
- Supports a floating, inline, or button display style.
- Supports left, right, or bottom placement, with a configurable mobile behavior.
- Records each share event in a tracking table for analytics.

The buttons are rendered by `BP_Share_Post_Type_Frontend`, and the whole subsystem is driven by `BP_Share_Post_Type_Settings` and `BP_Share_Post_Type_Controller`.

## Configure it

Open the **Post Type Sharing** settings tab. There you choose:

- Which public post types show share buttons.
- Which social services appear for post-type shares.
- The display position (left / right / bottom) and style (floating / inline / button).
- Mobile behavior.

Settings are stored in `bp_share_post_type_settings`.

## Share button on single posts

On single WordPress posts, a Share button is also appended to the content automatically for themes that are not Reign or BuddyX Pro (those themes place it through their own integration). You can also place it yourself with the `[bp_activity_post_reshare]` shortcode; see [Shortcode Reference](../developer-guide/03-shortcodes.md).

## Rate limiting and anonymous shares

Post-type share tracking is protected against abuse:

- A single user or IP is limited to 20 share events per hour by default (`bp_share_rate_limit`).
- Logged-out visitors are tracked by default; require login by returning `false` from `bp_share_allow_anonymous_sharing`.

## Turn it off

Post-type sharing is on by default. Return `false` from `bp_share_enable_post_type_sharing` to disable the entire subsystem.

## Note on tables

Post-type share events are stored in a custom tracking table created on activation. If share events are not being recorded, deactivate and reactivate the plugin so the installer can recreate the tables.
