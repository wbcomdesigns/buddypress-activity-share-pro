# Plugin: BuddyPress Activity Share Pro

## Quick Reference
- **Slug**: `buddypress-activity-share-pro`
- **Main File**: `buddypress-share.php`
- **Version**: 2.1.0
- **Text Domain**: `buddypress-share`
- **Requires**: BuddyPress 8.0+ or BuddyBoss Platform
- **PHP**: 7.4+
- **WordPress**: 5.0+
- **Author**: Wbcom Designs
- **Settings URL**: `admin.php?page=wbcom-buddypress-share`

## Architecture Docs
Full documentation in `docs/architecture/`:
- [PLUGIN_ARCHITECTURE.md](docs/architecture/PLUGIN_ARCHITECTURE.md) - Complete reference
- [manifest/](docs/architecture/manifest/) - Index files

## Key Entry Points
- **Main File**: `buddypress-share.php` - Bootstrap, constants, activation hooks
- **Core Class**: `includes/class-buddypress-share.php` - Orchestrator, loads dependencies, defines hooks
- **Admin**: `admin/class-buddypress-share-admin.php` - Settings UI (5 tabs), AJAX handlers
- **Public**: `public/class-buddypress-share-public.php` - Frontend rendering, share buttons, reshare modal, AJAX
- **Post Types**: `includes/post-types/class-bp-share-post-type-controller.php` - Post type sharing entry point

## Plugin Flow
1. `bp_loaded` -> `bpshare_pro_plugin_init()` -> `new Buddypress_Share()` -> `$plugin->run()`
2. `Buddypress_Share` loads dependencies, registers all hooks via `Buddypress_Share_Loader`
3. Admin: Tabbed settings page under WBCom Designs menu or Settings > Activity Share
4. Frontend: Share dropdown on `bp_activity_entry_meta`, Bootstrap modal for resharing
5. Post Types: Floating/inline share buttons on singular posts via `wp_footer` or `the_content`

## Settings Tabs
| Tab | Section Key | Settings Group |
|-----|-------------|----------------|
| Social Networks | `general` / `services` | `bp_share_general_settings` |
| Display Settings | `display` / `icons` | `bpas_icon_color_settings` |
| Restrictions | `restrictions` / `sharing` | `bp_reshare_settings` |
| Post Type Sharing | `post-types` | `bp_share_post_type_settings` |
| FAQ | `faq` | N/A (static) |

## Social Services
Facebook, X (Twitter), LinkedIn, Pinterest, Reddit, WordPress, Pocket, Telegram, Bluesky, WhatsApp, E-mail, Copy Link

## Database
### Options
| Option | Description |
|--------|-------------|
| `bp_share_services` | Enabled social services |
| `bp_share_services_enable` | Master toggle |
| `bp_share_services_logout_enable` | Guest sharing |
| `bp_reshare_settings` | Reshare restrictions |
| `bpas_icon_color_settings` | Icon style/colors |
| `bp_share_post_type_settings` | Post type sharing config |

### Custom Tables
- `{prefix}bp_share_post_tracking` - Post type share events
- `{prefix}bp_share_post_type_settings` - Per-post-type settings

### Key Meta
- `share_count` (activity_meta/post_meta) - Share count
- `shared_activity_id` (activity_meta) - Original activity reference
- `bp_share_user_stats` (user_meta) - User share statistics
- `bp_share_activity_stats` (activity_meta) - Activity share statistics

## AJAX Endpoints
| Endpoint | Auth | Purpose |
|----------|------|---------|
| `wss_social_icons` | Admin | Add social service |
| `wss_social_remove_icons` | Admin | Remove social service |
| `bp_activity_create_reshare_ajax` | User | Create activity reshare |
| `bp_share_get_activity_content` | User | Load activity content |
| `bp_get_user_share_options` | User | Load groups/friends |
| `bp_share_post` | Public | Track post share |
| `bp_share_track_external` | Public | Track external share |

## Hooks (Most Used)
### Actions
- `bp_share_after_create_reshare` - After successful reshare
- `bp_share_user_reshared_activity` - Reshare event (for points/rewards)
- `bp_share_post_shared` - Post type share tracked

### Filters
- `bp_share_available_services` - Modify available social services
- `bp_share_services_config` - Modify sharing URL config
- `bp_share_enable_post_type_sharing` - Toggle post type feature
- `bp_share_tracking_parameters` - Modify UTM params
- `bp_share_before_create_reshare` - Modify reshare data before save
- `bp_share_post_type_whitelist` - Add to valid post types
- `bp_activity_reshare_post_type` - Reshare-able post types

## Shortcodes
- `[bp_activity_post_reshare]` - Renders share button on single posts

## REST API
- Adds `bp_activity_share_count` to `bp_rest_activity_prepare_value` response

## Compatibility
- **BuddyBoss Platform**: Full compatibility via `bp-share-buddyboss-compat.php`
- **Themes**: Special support for `reign-theme`, `buddyx-pro`
- **Incompatible**: Youzify plugin
- **Free Version**: Auto-deactivates `buddypress-activity-social-share` on activation

## Key Constants
```php
BP_ACTIVITY_SHARE_PLUGIN_VERSION  // '2.1.0'
BP_SHARE                          // 'buddypress-share'
BP_ACTIVITY_SHARE_PLUGIN_URL      // Plugin URL
BP_ACTIVITY_SHARE_PLUGIN_PATH     // Plugin path
BP_ACTIVITY_SHARE_PLUGIN_BASENAME // Plugin basename
```

## Development Notes
- Uses WordPress Plugin Boilerplate pattern (Loader class for hook registration)
- Singleton pattern for post type subsystem classes
- Auto `.min` and RTL via `bp_share_enqueue_style()` / `bp_share_enqueue_script()`
- Settings synced to site_options for multisite
- Bootstrap 4.6.2 modal for reshare UI
- Select2 4.1.0 for group/friend selection dropdown
- Font Awesome 5.15.4 with dashicons fallback
- UTM tracking on all share links
- Rate limiting on post type sharing (20/hr default)

## Recent Changes
| Date | Type | Description | Files |
|------|------|-------------|-------|
| 2025-08-01 | Feature | Post type sharing with floating/inline widgets | includes/post-types/*, public/css/bp-share-post-type.css, public/js/bp-share-post-type.js |
| 2025-08-01 | Feature | Share tracking database with analytics | includes/post-types/class-bp-share-post-type-tracker.php |
| 2025-08-01 | Feature | Admin post type settings tab | admin/partials/bp-share-post-type-settings.php |

## Known Issues / TODOs
- Post type sharing database tables created on `bp_share_activate` hook (may need manual trigger)
- Twitter branding migration to X is handled but `fab fa-twitter` icon still used
- Bootstrap/Select2 CDN loaded on all BP pages (consider conditional loading)

## Git Commit Rules
- **NEVER use Co-Authored-By** in commit messages
- **NEVER use "Generated with Claude Code"** footer
- Keep commit messages clean without any attribution lines

## Basecamp Project Management

**Project:** BuddyPress Activity Share Pro
- **Project ID:** 37939219
- **Card Table ID:** 7507533844

**Column IDs:**
| Column | ID |
|--------|-----|
| Triage | 7507533845 |
| Not now | 7507533846 |
| Suggestions | 7507533860 |
| Bugs | 7507533850 |
| Ready for Development | 7507533853 |
| Code Improvement | 7808139615 |
| In Development | 7507533851 |
| Ready for Testing | 7507533854 |
| Scope | 9099749151 |
| In Testing | 9099749199 |
| Done | 7507533852 |

**URLs:**
- Bugs: https://3.basecamp.com/5798509/buckets/37939219/card_tables/columns/7507533850
- Ready for Testing: https://3.basecamp.com/5798509/buckets/37939219/card_tables/columns/7507533854
