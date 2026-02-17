# BuddyPress Activity Share Pro - Architecture Reference

## Overview

BuddyPress Activity Share Pro enables social sharing and internal resharing of BuddyPress activities. It supports sharing to external social networks (Facebook, X, LinkedIn, WhatsApp, etc.) and internal resharing within the BuddyPress activity stream, including to groups and friends. Version 2.1.0 adds post type sharing with floating/inline share widgets.

## Plugin Bootstrap

**Main File:** `buddypress-share.php`

1. Defines constants: `BP_ACTIVITY_SHARE_PLUGIN_VERSION`, `BP_SHARE`, `BP_ACTIVITY_SHARE_PLUGIN_URL`, `BP_ACTIVITY_SHARE_PLUGIN_BASENAME`, `BP_ACTIVITY_SHARE_PLUGIN_PATH`
2. Loads Plugin Update Checker from `plugin-update-checker/`
3. Registers activation/deactivation hooks
4. On `bp_loaded` hook, initializes plugin via `bpshare_pro_plugin_init()` -> `run_buddypress_share_pro()` -> `new Buddypress_Share()` -> `$plugin->run()`
5. WBCom integration initializes on `init` (priority 1) and `plugins_loaded` (priority 20) for admin

## Core Architecture

### Class Hierarchy

```
Buddypress_Share (core orchestrator)
  -> Buddypress_Share_Loader (hook registration)
  -> Buddypress_Share_i18n (internationalization)
  -> Buddypress_Share_Admin (admin settings UI)
  -> Buddypress_Share_Public (frontend rendering + AJAX)
  -> Buddypress_Share_Assets (asset management)
  -> Buddypress_Share_Tracker (share analytics)
  -> BP_Share_Post_Type_Controller (post type sharing)
     -> BP_Share_Post_Type_Settings (settings manager)
     -> BP_Share_Post_Type_Frontend (frontend renderer)
     -> BP_Share_Post_Type_Tracker (post type analytics DB)
  -> BP_Activity_Share_Wbcom_Integration (admin wrapper)
```

### Loader Pattern

The plugin uses the WordPress Plugin Boilerplate pattern with `Buddypress_Share_Loader`. All hooks are registered in `Buddypress_Share::define_admin_hooks()` and `Buddypress_Share::define_public_hooks()`, then executed via `$loader->run()`.

## Key Classes

### `Buddypress_Share` (`includes/class-buddypress-share.php`)
- Core orchestrator class
- Loads all dependencies, sets locale, defines admin/public hooks
- Initializes post type sharing subsystem via `init_post_type_sharing()`
- Handles plugin upgrade routines

### `Buddypress_Share_Admin` (`admin/class-buddypress-share-admin.php`)
- Settings page with tabbed UI: Social Networks, Display Settings, Restrictions, Post Type Sharing, FAQ
- AJAX handlers: `wss_social_icons` (add service), `wss_social_remove_icons` (remove service)
- Registers settings groups: `bp_share_general_settings`, `bp_reshare_settings`, `bpas_icon_color_settings`
- Syncs options to site_options for multisite

### `Buddypress_Share_Public` (`public/class-buddypress-share-public.php`)
- Renders share dropdown on each activity via `bp_activity_entry_meta` hook
- Share button with social service links + reshare button
- Bootstrap modal popup for resharing (Select2 dropdown for group/friend selection)
- OpenGraph meta tags for single activities
- AJAX handlers: `bp_activity_create_reshare_ajax`, `bp_share_get_activity_content`, `get_user_share_options_ajax`
- UTM tracking parameters on all share links
- REST API integration: adds `bp_activity_share_count` to activity responses

### `Buddypress_Share_Tracker` (`includes/class-buddypress-share-tracker.php`)
- Singleton pattern
- Tracks internal reshares via `bp_share_user_reshared_activity` hook
- Processes tracking parameters from shared links on `init`
- Stores stats in user_meta (`bp_share_user_stats`) and activity_meta (`bp_share_activity_stats`, `bp_share_visit_stats`)

### Post Type Sharing Subsystem (since 2.1.0)

#### `BP_Share_Post_Type_Controller` (`includes/post-types/class-bp-share-post-type-controller.php`)
- Main controller, singleton
- Registers post type support (`bp-share`)
- Renders floating wrapper (`wp_footer`) or inline buttons (`the_content` filter)
- AJAX share tracking with rate limiting
- Custom DB table: `{prefix}bp_share_post_tracking`

#### `BP_Share_Post_Type_Settings` (`includes/post-types/class-bp-share-post-type-settings.php`)
- Settings stored in option `bp_share_post_type_settings`
- Intelligent post type validation (excludes page builders, form plugins, etc.)
- Per-post-type service configuration

#### `BP_Share_Post_Type_Frontend` (`includes/post-types/class-bp-share-post-type-frontend.php`)
- Renders floating share widget with SVG toggle icon
- Renders inline share buttons with CSS custom properties for theming
- Formats share counts (K/M suffixes)

#### `BP_Share_Post_Type_Tracker` (`includes/post-types/class-bp-share-post-type-tracker.php`)
- Custom tracking table with `dbDelta`
- Per-post, per-user, and overall statistics
- GDPR-aware IP tracking with anonymization filters
- Visit tracking from shared links stored in post meta

## Helper Files

### `bp-share-helpers.php` (`includes/bp-share-helpers.php`)
- Asset loading helpers: `bp_share_enqueue_style()`, `bp_share_enqueue_script()`
- Auto minification (`.min`) and RTL support
- Safe BuddyPress function wrappers: `bp_share_is_bp_active()`, `bp_share_get_activity_id()`, etc.

### `bp-share-buddyboss-compat.php` (`includes/bp-share-buddyboss-compat.php`)
- BuddyBoss Platform detection and compatibility
- Platform-specific CSS classes, script dependencies, modal classes
- Activity type exclusion list for share buttons

### `Buddypress_Share_Assets` (`includes/class-buddypress-share-assets.php`)
- Font Awesome loading with local/CDN/dashicons fallback
- Icon class mapping per social service

## Database

### Options (wp_options / wp_sitemeta)
| Option | Type | Description |
|--------|------|-------------|
| `bp_share_services` | array | Enabled social services (key => label) |
| `bp_share_services_enable` | int | Master enable/disable toggle |
| `bp_share_services_logout_enable` | int | Allow guest sharing |
| `bp_share_services_extra` | array | Extra options (popup windows) |
| `bp_reshare_settings` | array | Reshare restrictions and display mode |
| `bpas_icon_color_settings` | array | Icon style, colors |
| `bp_share_plugin_version` | string | Installed version |
| `bp_share_post_type_settings` | array | Post type sharing configuration |

### Meta Keys
| Key | Storage | Description |
|-----|---------|-------------|
| `share_count` | activity_meta / post_meta | Share count per activity/post |
| `shared_activity_id` | activity_meta | Reference to original shared activity |
| `bp_share_user_stats` | user_meta | User share statistics |
| `bp_share_activity_stats` | activity_meta | Activity share statistics |
| `bp_share_visit_stats` | activity_meta | Visit tracking from shared links |
| `_bp_share_visits` | post_meta | Post visit tracking data |

### Custom Tables
| Table | Since | Description |
|-------|-------|-------------|
| `{prefix}bp_share_post_tracking` | 2.1.0 | Post type share events tracking |
| `{prefix}bp_share_post_type_settings` | 2.1.0 | Per-post-type settings (DB version) |

### Database Indexes
Created on activation:
- `idx_bp_share_count` on activity_meta (`meta_key`, `activity_id`)
- `idx_bp_post_share_count` on postmeta (`meta_key`, `post_id`)

## Hooks Reference

### Actions (Plugin-Specific)
| Hook | Location | Description |
|------|----------|-------------|
| `bp_share_after_create_reshare` | Public | After successful reshare |
| `bp_share_user_reshared_activity` | Public | After reshare (for points/rewards) |
| `bp_share_internal_share_tracked` | Tracker | Internal share tracked |
| `bp_share_external_share_tracked` | Tracker | External share tracked |
| `bp_share_external_visit_tracked` | Tracker | Visit from shared link |
| `bp_share_user_stats_updated` | Tracker | User stats updated |
| `bp_share_activity_stats_updated` | Tracker | Activity stats updated |
| `bp_share_post_shared` | Post Tracker | Post share tracked |
| `bp_share_post_visit_tracked` | Controller | Post visit tracked |
| `bp_share_clear_public_cache` | Admin | Cache cleared |
| `bp_share_plugin_upgraded` | Core | Plugin upgraded |
| `bp_share_deactivated` | Core | Plugin deactivated |
| `bp_share_uninstalled` | Core | Plugin uninstalled |

### Filters (Plugin-Specific)
| Filter | Location | Description |
|--------|----------|-------------|
| `bp_share_available_services` | Admin | Modify available social services |
| `bp_share_use_cdn_assets` | Assets | Enable CDN loading (default: false) |
| `bp_share_activity_data` | Public | Modify share data before rendering |
| `bp_share_social_button_html` | Public | Modify social button HTML |
| `bp_share_services_config` | Public | Modify sharing services config |
| `bp_share_tracking_parameters` | Public | Modify UTM tracking params |
| `bp_share_before_create_reshare` | Public | Modify reshare data |
| `bp_share_enable_post_type_sharing` | Core | Enable/disable post type feature |
| `bp_share_post_type_whitelist` | Settings | Add to post type whitelist |
| `bp_share_post_type_is_valid` | Settings | Override post type validation |
| `bp_share_post_type_available_services` | Settings | Customize post type services |
| `bp_share_rate_limit` | Controller | Share rate limit (default: 20/hr) |
| `bp_share_allow_anonymous_sharing` | Controller | Allow anonymous shares |
| `bp_share_disable_ip_tracking` | Tracker | Disable IP tracking (GDPR) |
| `bp_share_anonymize_ip` | Tracker | Anonymize stored IPs |
| `buddypress_share_theme_support` | Core | Theme support list |
| `bp_activity_reshare_post_type` | Main | Reshare-able post types |
| `bp_activity_share_load_assets` | Public | Force load assets |

## Supported Social Services

Facebook, X (Twitter), LinkedIn, Pinterest, Reddit, WordPress, Pocket, Telegram, Bluesky, WhatsApp, E-mail, Copy Link

## Requirements
- WordPress 5.0+
- PHP 7.4+
- BuddyPress 8.0+ or BuddyBoss Platform
- Incompatible with: Youzify plugin

## File Structure

```
buddypress-activity-share-pro/
|-- buddypress-share.php              # Main plugin file
|-- uninstall.php                     # Uninstall handler
|-- index.php                         # Security index
|-- admin/
|   |-- class-buddypress-share-admin.php    # Admin settings
|   |-- partials/bp-share-post-type-settings.php  # Post type settings template
|   |-- css/buddypress-share-admin[.min].css
|   |-- css-rtl/buddypress-share-admin[.min].css
|   |-- js/buddypress-share-admin[.min].js
|-- includes/
|   |-- class-buddypress-share.php          # Core class
|   |-- class-buddypress-share-loader.php   # Hook loader
|   |-- class-buddypress-share-i18n.php     # i18n
|   |-- class-buddypress-share-activator.php # Activation
|   |-- class-buddypress-share-assets.php   # Asset manager
|   |-- class-buddypress-share-tracker.php  # Share tracker
|   |-- class-wbcom-integration.php         # WBCom admin wrapper
|   |-- bp-share-helpers.php                # Helper functions
|   |-- bp-share-buddyboss-compat.php       # BuddyBoss compat
|   |-- post-types/
|   |   |-- class-bp-share-post-type-controller.php
|   |   |-- class-bp-share-post-type-settings.php
|   |   |-- class-bp-share-post-type-frontend.php
|   |   |-- class-bp-share-post-type-tracker.php
|   |-- shared-admin/                       # WBCom shared admin UI
|-- public/
|   |-- class-buddypress-share-public.php   # Public frontend
|   |-- css/buddypress-share-public[.min].css
|   |-- css/as-icons[.min].css
|   |-- css/bp-share-post-type[.min].css
|   |-- css-rtl/  (RTL versions of all CSS)
|   |-- js/buddypress-share-public[.min].js
|   |-- js/bp-share-post-type[.min].js
|-- plugin-update-checker/                  # PUC library (vendor)
```
