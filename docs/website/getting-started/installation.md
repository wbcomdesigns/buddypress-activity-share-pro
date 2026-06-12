# Installation

## Before You Begin

Check that your site meets the [Requirements](./requirements.md). In particular, BuddyPress (or BuddyBoss Platform) must be active before you install this plugin.

## Upload via WordPress Admin

This is the most common method.

1. Log in to your WordPress admin.
2. Go to **Plugins > Add New**.
3. Click **Upload Plugin** at the top of the page.
4. Click **Choose File**, select the `buddypress-activity-share-pro.zip` file you downloaded, then click **Install Now**.
5. After the upload finishes, click **Activate Plugin**.

If the free **BuddyPress Activity Social Share** plugin is active on your site, it will be deactivated automatically at this step. You do not need to deactivate it manually first.

## Upload via FTP

If you prefer to upload files directly:

1. Extract the zip file on your computer. You will get a folder named `buddypress-activity-share-pro` (or `bp-activity-social-share` — use whatever folder name came from the zip).
2. Connect to your server via FTP and navigate to `/wp-content/plugins/`.
3. Upload the extracted folder there.
4. In your WordPress admin, go to **Plugins > Installed Plugins**.
5. Find **Wbcom Designs - BuddyPress Activity Share Pro** and click **Activate**.

## After Activation

Once activated, the plugin sets up default settings automatically:

- Six social networks are enabled by default: Facebook, X (Twitter), LinkedIn, WhatsApp, Email, and Copy Link.
- Guest sharing (for logged-out visitors) is enabled.
- Share buttons open social networks in popup windows.
- The icon style is set to Circle.

You can change any of these defaults in **WBcom Designs > BuddyPress Share** (or **Settings > Activity Share** if the WBcom admin menu is not active on your site).

## Verify the Installation

After activating:

1. Visit any BuddyPress activity page on your site (typically `/activity/`).
2. You should see share icons below each activity entry, in the activity meta area.
3. If the icons do not appear, check that the BuddyPress Activity component is enabled at **Settings > BuddyPress > Components**.

## Next Step

Go to [Your First Share](./first-share.md) to walk through how sharing works for your members.
