# Troubleshooting

Common problems and what to check. Every item below maps to real plugin behavior.

## The Share button does not appear on activity

Check, in order:

1. **BuddyPress Activity component is active** at Settings > BuddyPress > Components. The button hooks onto the activity stream, so without the Activity component nothing renders.
2. **Social sharing is enabled** on the Social Networks tab (master toggle, option `bp_share_services_enable`).
3. **At least one network is active.** With the Active list empty, there is nothing to show.
4. **The content type is not restricted.** On the Restrictions tab, if User Profiles or Groups resharing is disabled, the button is suppressed on those pages.

## Logged-out visitors do not see the Share button

Guest sharing is a separate toggle. Turn on Guest sharing in the Social Networks tab (option `bp_share_services_logout_enable`). Guests only ever see the social network buttons, never the internal Reshare button.

## The Reshare button is missing

The Reshare button only renders when at least one reshare destination is available and the viewer is logged in. It disappears when:

- All three destinations (User Profiles, Groups, Friends) are disabled on the Restrictions tab.
- The current page is a content type you disabled (for example blog posts).
- The viewer is a guest.

## Groups or Friends are missing from the Post in dropdown

The Groups option appears only when the BuddyPress Groups component is active and Groups resharing is not disabled. The Friends option appears only when the Friends component is active and the member has confirmed friends. Each list is capped at 50 entries.

## The plugin deactivated itself

Two cases do this on purpose:

- **The free plugin.** Activating Pro automatically deactivates the free BuddyPress Activity Social Share plugin. Only one can run at a time.
- **Youzify.** The plugin cannot run alongside Youzify. If Youzify is active, this plugin deactivates itself on `admin_init` and shows a notice. Remove Youzify to use this plugin.

## Post-type share events are not being recorded

Post-type share data is stored in a custom tracking table created on activation. If the table is missing (for example after a migration), deactivate and reactivate the plugin so the installer recreates it.

## The share count always shows nothing

A count of zero is hidden by design. The count appears once an activity has been shared at least once.

## The share button appears twice on single posts

This happens when a theme is not on the built-in support list (Reign Theme, BuddyX Pro) and you have also placed the `[bp_activity_post_reshare]` shortcode manually. The plugin appends the button to `the_content` automatically for unsupported themes. Remove either the shortcode or the automatic injection. To unhook the automatic one, see [Shortcode Reference](../developer-guide/03-shortcodes.md).

## The button shows but the modal does not open on a custom page

Assets load automatically on BuddyPress pages, single posts, and pages with the activity listing shortcode. On other layouts, force them to load:

```php
add_filter( 'bp_activity_share_load_assets', '__return_true' );
```

## Icons look wrong on BuddyBoss

The plugin force-loads Font Awesome when it detects the BuddyBoss theme, so icons render correctly there without extra setup. If icons still look off, check for another plugin or theme dequeuing Font Awesome.

## Still stuck?

See the [FAQ](../faq/faq.md), or contact the [Wbcom Designs support portal](https://wbcomdesigns.com/support/).
