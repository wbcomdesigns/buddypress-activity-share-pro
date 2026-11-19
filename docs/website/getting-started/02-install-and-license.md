# Install, license and updates

## Install

1. Install and activate **Activity Share for BuddyPress** (the free plugin) first.
2. In wp-admin, go to **Plugins > Add New > Upload Plugin**, upload the Activity Share Pro zip and click **Install Now**.
3. Click **Activate**.
4. Pro opens **WB Plugins > Activity Share > Features**. Its tabs sit next to the free **Networks** and **Appearance** tabs under a **Pro** heading: **Features**, **Content types** and **Analytics**. **License** is under **Account**.

If the free plugin is missing or inactive, WordPress refuses to activate Pro and tells you to activate the free plugin first. Your Pro settings are not touched.

## Activate your license

1. Go to **WB Plugins > Activity Share > License**.
2. Paste the key from your Wbcom Designs account into **License key**.
3. Click **Activate License**.

The license only controls automatic updates. Every Pro feature works with or without it.

If the License tab says automatic updates are unavailable because the update library is missing, download the plugin again from your Wbcom Designs account and reinstall it.

## What the license check sends

To check your license and deliver updates, Pro contacts the Wbcom Designs update server (updates.wbcomdesigns.com, or the store at wbcomdesigns.com when that server cannot be reached). It sends your license key, the product ID, the plugin version and your site address. It also fetches the signature of each update package from the same hosts. No visitor or member data is sent. Each update package is checked against its release signature before it installs; a failed check is logged and does not block the update.

## Keep Free and Pro on the same version

Update Activity Share for BuddyPress and Activity Share Pro together.

- If the two versions differ but are on the same 3.6 line, Pro keeps working and a notice names the plugin that is behind.
- If Free is older than 3.6, Pro pauses and shows **Update Activity Share for BuddyPress**. Sharing keeps working from Free.
- If you deactivate or delete Free after Pro was active, Pro pauses with one notice and a button to install or activate Free again. Your Pro settings are kept.

## Upgrading from Activity Share Pro 2.x

Update the free plugin to 3.6.1 and Pro to 3.6.1 together. The first time the site loads after the update, Pro moves your data over and removes the old entries:

- **Settings.** Repost is switched off only if both reposting to a profile and reposting to a group were disabled before. Send to a friend is switched off if it was disabled. Post types you enabled for sharing are kept, with the placement **Floating button** if your old display style was floating (or was never set) and **Below the content** otherwise.
- **Share records and counts.** Share tracking for posts moves into the new analytics store, and share counts are kept.
- **Notifications.** Old share notifications are moved to the new repost notification type.
- **Visitor IP addresses are deleted.** Old versions stored them; this version never does.
- **Your license key.** The old license entries are removed and the new License tab starts empty. After updating, open **WB Plugins > Activity Share > License**, paste your key and click **Activate License** to keep receiving updates. If the tab already shows an active license, nothing more is needed. Features are not affected either way.
