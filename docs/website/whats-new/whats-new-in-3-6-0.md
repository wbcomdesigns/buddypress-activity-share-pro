# What's new in 3.6.0

3.6.0 rebuilds Activity Share Pro as an add-on to the free Activity Share for BuddyPress plugin. It adds quick reposts with Undo, Send to a friend, share buttons on any content, counts, analytics that work, short share links and rewards. Read the update notes and run the retest below before you tell your members the update is done.

## Update notes

- **Activity Share for BuddyPress 3.6.0 or later is required.** Update the free plugin and Pro together.
- **WordPress 6.7 and PHP 8.1 are required,** with BuddyPress 12.0 or later, or BuddyBoss Platform.
- **Your data moves automatically** the first time the site loads after the update, and the old entries are removed. See [Upgrading from Activity Share Pro 2.x](../getting-started/02-install-and-license.md#upgrading-from-activity-share-pro-2x) for exactly what is carried over.
- **Paste your license key again** on **WB Plugins > Activity Share > License** if the tab is empty after the update. The license now only controls updates; every feature works without it.
- **Settings now live on four tabs** under **WB Plugins > Activity Share**: **Features**, **Content types**, **Analytics** and **License**, next to the free **Networks** and **Appearance** tabs.
- **Visitor IP addresses are no longer stored,** and the ones stored by older versions are deleted. Visitors are counted with a one-way code that changes every day.
- **Bootstrap, Select2 and Font Awesome are no longer loaded** by the plugin. Theme or custom code that relied on Pro loading them needs its own copy.
- **Each update package is checked against its release signature** before it installs. A failed check is logged and does not block the update.

## What changed for site owners

- **Repost** is one tap with an **Undo** message, and **Repost with comment** goes to your profile or a group. Type `@` in the comment to mention friends; they get a notification.
- **Send to a friend** sends a post as a private message.
- **Share and repost replies.** Replies get their own Share menu.
- **Share buttons on any content type,** with a **Share buttons** block and the `[bpas_share]` shortcode. See the **Content types** tab.
- **Repost blog posts and other content** into the activity stream as a quoted card.
- **Share counts and a list of who reposted** next to the Share button.
- **Most shared block and widget** for the last 7 or 30 days.
- **Share image for text-only posts** when shared to Facebook, LinkedIn or WhatsApp.
- **Short share links** like `yoursite.com/s/abc123` that credit the member who shared.
- **Share rewards** with WB Gamification, GamiPress and myCRED.
- **Group admins** decide whether posts from their public group can be shared outside or reposted elsewhere.
- **Grouped repost notifications,** and an email members can switch off under **Settings > Email**.
- **Analytics that work.** Choose 7, 30 or 90 days, page through the Most shared list and export it to CSV. The report stays fast on large sites. Clicks and visits are counted only while **Share analytics** is on.
- **Privacy tools.** Share records are included in WordPress's personal data export and erase tools.
- **The repost dialog** works with a keyboard and screen readers, looks right on phones, and matches your theme in light and dark mode.
- **After reposting or sending,** a **View** link takes you to the post or the conversation. Messages use BuddyPress's own notice style at the bottom of the screen and stay until dismissed.
- **Undo repost** appears in the Share menu on posts you already reposted, including your own repost in the stream.

## Fixes you may have noticed

- Activating Pro while the free plugin is active no longer causes an error, and Pro activates whatever folder the free plugin is installed in, including installs from GitHub or a wbcomdesigns.com zip.
- Pages that show reposts no longer error on BuddyBoss Platform.
- Posts from private and hidden groups can no longer be viewed or reposted by non-members, and drafts and private posts can no longer be reposted.
- Undoing a repost removes it from the author's notification, even after the author has read it.
- The share count updates right after a repost or undo.
- Right-to-left sites get the correct styles.
- Share tracking tables are created on update, without reactivating the plugin.
- Uninstalling now removes all plugin data. See [Where Pro keeps its data, and what uninstall removes](../maintain/01-data-and-uninstall.md).

## Retest after updating

1. Open **WB Plugins > Activity Share > License** and check your license shows as active.
2. Open **Features** and check each switch is set the way you want, in particular **Repost**, **Send to a friend** and **Share analytics**.
3. Open **Content types** and check the post types and placements you used before.
4. As a member, repost a post and check **Undo** removes it again.
5. Repost with a comment into a group you belong to.
6. Send a post to a friend and check it arrives as a private message.
7. Open a short share link in a private window and check it lands on the post.
8. If you turned on **Share analytics**, open **Analytics** and check the report loads.

## For developers

- The old `bp_share_*` and `bp_reshare_settings` options, the old tracking tables and the old license options are migrated and removed. Code that read them directly must use the new `bpas_pro_settings` option, the `bpas_pro_events` table and the Pro hooks instead.
- Share counts are stored under the hidden field `_bpas_share_count` instead of `share_count`.
