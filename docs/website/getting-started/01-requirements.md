# Requirements

Before installing BuddyPress Activity Share Pro, confirm that your site meets these requirements.

## WordPress and PHP

| Requirement | Version |
|---|---|
| WordPress | 5.0 or later (tested up to 6.9) |
| PHP | 7.4 or later |

PHP 8.0, 8.1, and 8.2 are supported.

## BuddyPress or BuddyBoss Platform

The plugin needs one of the following to be installed and active:

- **BuddyPress** - the free community plugin from wordpress.org. Keep it on a recent version.
- **BuddyBoss Platform** - the BuddyBoss commercial platform. BuddyBoss-specific behavior is handled automatically.

The plugin activates when either BuddyPress or BuddyBoss Platform is present, and it stops with an admin notice if neither is active.

The BuddyPress Activity component must be enabled. This plugin adds sharing controls to the activity stream, so with the Activity component turned off nothing will appear.

## Free Version Conflict

If the free **BuddyPress Activity Social Share** plugin is installed, it is deactivated automatically when you activate this Pro version. The two cannot run at the same time.

## Youzify Conflict

The plugin cannot run alongside Youzify. If Youzify is active, this plugin deactivates itself on `admin_init` and shows a notice. This is a hard restriction, not a setting.

## Theme Compatibility

The plugin works with any WordPress theme. Extra styling and native placement are included for:

- **BuddyX** and **BuddyX Pro**
- **Reign Theme**

On other themes the share buttons appear using the plugin's default styles.

## What the Plugin Does Not Require

- No API keys for any social network. Share links open on the social platform itself.
- No CDN or external image service is required.
- No WooCommerce or other e-commerce plugin.

## Next Step

Once your site meets these requirements, follow the [Installation](./02-installation.md) guide.
