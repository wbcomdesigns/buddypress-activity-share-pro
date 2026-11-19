# Troubleshooting

## Activity Share Pro will not activate, or a notice keeps appearing

- **"Activity Share Pro was not activated. It needs the free Activity Share for BuddyPress plugin":** install and activate **Activity Share for BuddyPress** first, then Pro. Your Pro settings are untouched.
- **"Activity Share Pro needs the free Activity Share for BuddyPress plugin. Your Pro settings are safe...":** the free plugin was deactivated or removed after Pro was active. Use the button in the notice to install or activate it again.
- **"Activity Share Pro is paused until Activity Share for BuddyPress is updated":** your free plugin is older than 3.6. Update it. Sharing keeps working meanwhile.
- **"Please update Activity Share Pro to version X to match Activity Share for BuddyPress" (or the other way round):** the two plugins are on different releases. Update the one named in the notice. Pro keeps working in the meantime.
- **"Activity Share Pro does not support Youzify and is inactive while Youzify is active.":** deactivate Youzify to use Pro.

Keep both plugins on the same version number and update them together.

## Repost is missing from the Share menu

Check these in order:

1. **Features** tab: **Repost** is on.
2. The BuddyPress **Activity** component is on.
3. You are logged in. Visitors see **Repost** but are asked to log in.
4. The post is yours: you only get **Repost to a group**, and only if you belong to a group.
5. The post is in a group: you get **Repost in this group**, because posts from private and hidden groups, and from public groups that turned off reposting out, stay in the group.
6. The author turned off **Allow other members to repost my posts** in their profile settings.
7. For a blog post, page or product: its post type is ticked on the **Content types** tab, and it is published.
8. You already reposted it here: the menu shows **Undo repost** instead. A repost of the same post to the same place is allowed once.

## Send to a friend, Share replies or the member repost setting are missing

- **Send to a friend** needs the BuddyPress **Friend Connections** and **Private Messaging** components. Without both, the switch is removed from the Features tab and a note says why. The row also stays hidden for members with no friends yet, and on posts that cannot be shared outside the community.
- **Share replies** works only while **Repost** is also on.
- **Allow other members to repost my posts** appears under **Settings > General** only while **Repost** is on and the BuddyPress **Account Settings** component is active.

## The Share button count is not what I expected

The number counts reposts only. Sends to friends and clicks are not included. It disappears when it reaches zero, for example after the only repost is undone.

## Share analytics shows zero

Share analytics is **off by default**. Turn it on under **WB Plugins > Activity Share > Features**. Clicks and visits only count from the moment it is switched on. Visits also need **Short share links** to be on, because visits are counted when someone opens a short link. Reposts and sends to friends are counted even with analytics off, so if those also show zero, check that **Repost** and **Send to a friend** are on.

## A short share link (`/s/...`) returns a 404

Go to **Settings > Permalinks** and click **Save Changes** without changing anything. This rebuilds the rewrite rules that short links depend on. If your site has a page with the address `/s/`, short links use `/share/` instead.

## The share image for text posts is not generated

Pro draws this image with the PHP GD extension and FreeType. If your host does not have them, the feature does nothing and link previews use your site icon or logo. Ask your host to enable GD with FreeType support. The image is made only for public activity posts that have no picture.

## The Most shared block is out of date

The list is cached for 15 minutes. A new repost can take up to that long to appear.

## Share rewards give no points

1. Check that **WB Plugins > Activity Share > Features** shows the **Share rewards** line as on, naming your points plugin.
2. Check **Short share links** is on. Points are earned when someone opens a short link, or signs up after opening one.
3. Check the person who shared was logged in when they shared. Visitors' links earn no points, and a member earns nothing for opening their own link.
4. Check the point values in your points plugin.

## The License tab does not activate my key

- Paste the key exactly as it appears in your Wbcom Designs account.
- If the tab says automatic updates are unavailable because the update library is missing, download the plugin again from your account and reinstall it.
- Your key only controls updates. Features keep working while you sort it out.

## Updates do not appear

Activate your license on **WB Plugins > Activity Share > License**. Then check **Dashboard > Updates**. Updates come from the Wbcom Designs update server, or from the store if that server cannot be reached.

## Still stuck?

Contact Wbcom Designs support with your WordPress version, PHP version, the plugin versions (free and Pro), and the exact steps to reproduce the issue.
