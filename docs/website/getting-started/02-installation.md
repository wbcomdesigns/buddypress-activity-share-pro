# Installation

## Before You Begin

Check that your site meets the [Requirements](./01-requirements.md). In particular, BuddyPress (or BuddyBoss Platform) must be active before you install this plugin.

## Upload via WordPress Admin

This is the most common method.

1. Log in to your WordPress admin.
2. Go to **Plugins > Add New**.
3. Click **Upload Plugin** at the top of the page.
4. Click **Choose File**, select the plugin zip you downloaded, then click **Install Now**.
5. After the upload finishes, click **Activate Plugin**.

If the free **BuddyPress Activity Social Share** plugin is active on your site, it is deactivated automatically at this step. You do not need to deactivate it manually first.

## Upload via FTP

If you prefer to upload files directly:

1. Extract the zip file on your computer.
2. Connect to your server via FTP and navigate to `/wp-content/plugins/`.
3. Upload the extracted plugin folder there.
4. In your WordPress admin, go to **Plugins > Installed Plugins**.
5. Find **Wbcom Designs - BuddyPress Activity Share Pro** and click **Activate**.

## After Activation

On activation the plugin sets up its defaults automatically:

- Six networks are enabled by default: Facebook, X (Twitter), LinkedIn, WhatsApp, E-mail, and Copy Link.
- Guest sharing (for logged-out visitors) is enabled.
- Share links open in a popup window.
- The icon style is set to Circle.

Change any of these under the plugin's settings screen. The settings live under the shared **WB Plugins** menu (page slug `buddypress-share`). If the shared Wbcom admin menu is not present on your site, the plugin adds a standalone **Settings > Activity Share** menu instead.

The first time you open the settings, a short onboarding screen appears once to point out where the main options are.

## Verify the Installation

After activating:

1. Visit any BuddyPress activity page on your site (typically `/activity/`).
2. You should see the Share button below each activity entry, in the activity meta area.
3. If it does not appear, check that the BuddyPress Activity component is enabled at **Settings > BuddyPress > Components**.

## Next Step

Go to [Your First Share](./03-first-share.md) to see how sharing works for your members.
