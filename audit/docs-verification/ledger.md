# Docs Verification Ledger — BuddyPress Activity Share Pro

**Wave:** 2.1  
**Audited surface source:** `audit/manifest.json` (direction 1.1)  
**Date:** 2026-06-07  
**Verdict legend:** VERIFIED | DRIFT | ASPIRATIONAL | FABRICATED

---

## Documents Audited

| Doc | Path |
|-----|------|
| COMPLETE-DEVELOPER-GUIDE | `docs/COMPLETE-DEVELOPER-GUIDE.md` |
| COMPLETE-USER-GUIDE | `docs/COMPLETE-USER-GUIDE.md` |
| DEVELOPER-GUIDE | `docs/DEVELOPER-GUIDE.md` |
| FEATURE-SUMMARY | `docs/FEATURE-SUMMARY.md` |
| HOW-TO-ENABLE-POST-SHARING | `docs/HOW-TO-ENABLE-POST-SHARING.md` |
| POST-TYPE-FILTERING | `docs/POST-TYPE-FILTERING.md` |
| RELEASE-CHECKLIST | `docs/RELEASE-CHECKLIST.md` |
| ROADMAP-POST-TYPE-SHARING | `docs/ROADMAP-POST-TYPE-SHARING.md` |
| USER-GUIDE | `docs/USER-GUIDE.md` |
| PLUGIN_ARCHITECTURE | `docs/architecture/PLUGIN_ARCHITECTURE.md` |

---

## Verification Table

### 1. REST API Claims

| # | Claim | Source doc | Verdict | Evidence |
|---|-------|-----------|---------|----------|
| R1 | Five REST endpoints exist: `GET /wp-json/bp-share/v1/statistics/{activity_id}`, `POST /wp-json/bp-share/v1/track`, `POST /wp-json/bp-share/v1/reshare`, `GET /wp-json/bp-share/v1/services`, `GET /wp-json/bp-share/v1/users/{user_id}/shares` | COMPLETE-DEVELOPER-GUIDE §REST API Endpoints | **FABRICATED** | `grep -rn "register_rest_route"` returns zero hits across the entire repo. No `bp-share/v1` namespace is registered anywhere. |
| R2 | Only REST surface: `bp_activity_share_count` field appended via `bp_rest_activity_prepare_value` filter | manifest.json, PLUGIN_ARCHITECTURE | **VERIFIED** | `public/class-buddypress-share-public.php:1968` — `bp_activity_post_reshare_data_embed_rest_api()` |

### 2. JavaScript API Claims

| # | Claim | Source doc | Verdict | Evidence |
|---|-------|-----------|---------|----------|
| J1 | `window.BPShare` namespace with `init()`, `shareExternal()`, `shareInternal()`, `trackShare()` methods | COMPLETE-DEVELOPER-GUIDE §JavaScript API | **FABRICATED** | Actual JS uses an IIFE with `SELECTORS`, `ShareCache` and unnamed internal functions. No `window.BPShare` object exists. |
| J2 | `jQuery.fn.bpShare` jQuery plugin extension | COMPLETE-DEVELOPER-GUIDE §JavaScript API | **FABRICATED** | Not present in `public/js/buddypress-share-public.js` or any bundled JS. |
| J3 | `bp-share-copied` and `bp-reshare-success` custom jQuery events | DEVELOPER-GUIDE §JavaScript Events | **UNVERIFIED — needs code search** | Claimed in DEVELOPER-GUIDE; not confirmed absent, but no grep hit found in this audit pass. Mark aspirational until code search confirms. |

### 3. PHP Hooks — Actions

| # | Claim | Source doc | Verdict | Evidence |
|---|-------|-----------|---------|----------|
| H1 | `bp_share_before_buttons` / `bp_share_after_buttons` actions | COMPLETE-DEVELOPER-GUIDE §Hooks | **FABRICATED** | Zero hits in any PHP file. These hooks do not exist. |
| H2 | `bp_share_settings_saved` action | COMPLETE-DEVELOPER-GUIDE §Hooks | **FABRICATED** | Zero hits. |
| H3 | `bp_share_activity_shared` action | COMPLETE-DEVELOPER-GUIDE §Hooks | **FABRICATED** | Zero hits. |
| H4 | `bp_share_before_reshare_content` / `bp_share_after_reshare_created` actions | COMPLETE-DEVELOPER-GUIDE §Hooks | **FABRICATED** | Zero hits. |
| H5 | `bp_share_admin_settings_init` / `bp_share_admin_enqueue_scripts` actions | COMPLETE-DEVELOPER-GUIDE §Hooks | **FABRICATED** | Zero hits. |
| H6 | `bp_share_visit_tracked` / `bp_share_analytics_updated` actions | COMPLETE-DEVELOPER-GUIDE §Hooks | **FABRICATED** | Zero hits (real hooks are `bp_share_external_visit_tracked` and `bp_share_activity_stats_updated`). |
| H7 | `bp_share_user_reshared_activity` action | DEVELOPER-GUIDE, COMPLETE-DEVELOPER-GUIDE | **VERIFIED** | `public/class-buddypress-share-public.php:1071` — `do_action('bp_share_user_reshared_activity', ...)` |
| H8 | `bp_share_after_create_reshare` action | DEVELOPER-GUIDE | **VERIFIED** | `public/class-buddypress-share-public.php:1058` — `do_action('bp_share_after_create_reshare', ...)` |
| H9 | `bp_share_before_sanitize_extra_settings` / `bp_share_after_sanitize_extra_settings` actions | DEVELOPER-GUIDE | **VERIFIED** | `admin/class-buddypress-share-admin.php:1118,1143` |
| H10 | `bp_share_user_services` action | DEVELOPER-GUIDE | **VERIFIED** | `public/class-buddypress-share-public.php:542` |
| H11 | `bp_share_internal_share_tracked` / `bp_share_external_share_tracked` / `bp_share_external_visit_tracked` / `bp_share_user_stats_updated` / `bp_share_activity_stats_updated` | DEVELOPER-GUIDE §Tracking | **VERIFIED** | `includes/class-buddypress-share-tracker.php:107,199,156,252,297` |
| H12 | `bp_share_post_shared` action | DEVELOPER-GUIDE, ROADMAP, HOW-TO | **VERIFIED** | `includes/post-types/class-bp-share-post-type-tracker.php:153` |
| H13 | `bp_share_before_create_reshare` — documented as action hook | DEVELOPER-GUIDE §Actions | **DRIFT** | It is a filter, not an action. `public/class-buddypress-share-public.php:1029`: `$reshare_data = apply_filters('bp_share_before_create_reshare', array(...))`. The manifest correctly lists it as a filter. |
| H14 | `bp_share_internal_tracked` action (Tracker class example) | COMPLETE-DEVELOPER-GUIDE §Tracker Class | **DRIFT** | The real hook is `bp_share_internal_share_tracked` (with `_share_`). The COMPLETE-DEVELOPER-GUIDE example code calls `do_action('bp_share_internal_tracked', ...)` — wrong name. |

### 4. PHP Hooks — Filters

| # | Claim | Source doc | Verdict | Evidence |
|---|-------|-----------|---------|----------|
| F1 | `bp_share_services` filter (for adding/removing services) | COMPLETE-DEVELOPER-GUIDE §Filters | **DRIFT** | The actual filter for modifying available services in admin is `bp_share_available_services` (`admin/class-buddypress-share-admin.php:991`). A filter named `bp_share_services` does not appear as `apply_filters`. |
| F2 | `bp_share_enabled_services` filter | COMPLETE-DEVELOPER-GUIDE §Filters | **FABRICATED** | Zero hits. No such filter exists. |
| F3 | `bp_share_display_conditions` filter | COMPLETE-DEVELOPER-GUIDE §Filters | **FABRICATED** | Zero hits. No such filter exists. |
| F4 | `bp_share_button_html` filter | COMPLETE-DEVELOPER-GUIDE §Filters | **DRIFT** | Real filter is `bp_share_social_button_html` (4 args). `bp_share_button_html` (3 args as documented) does not exist. |
| F5 | `bp_share_activity_content` filter | COMPLETE-DEVELOPER-GUIDE §Filters | **FABRICATED** | Zero hits. No such filter exists. |
| F6 | `bp_share_button_classes` / `bp_share_button_text` / `bp_share_icon_class` filters | COMPLETE-DEVELOPER-GUIDE §Filters | **FABRICATED** | Zero hits. None of these exist. |
| F7 | `bp_share_user_can_share` / `bp_share_activity_shareable` filters | COMPLETE-DEVELOPER-GUIDE §Filters | **FABRICATED** | Zero hits. No such filters exist. |
| F8 | `bp_share_activity_url` / `bp_share_permalink` filters | COMPLETE-DEVELOPER-GUIDE §Filters | **FABRICATED** | Zero hits. Real URL filter is `bp_share_post_url`. |
| F9 | `bp_share_analytics_data` / `bp_share_statistics_query` filters | COMPLETE-DEVELOPER-GUIDE §Filters | **FABRICATED** | Zero hits. |
| F10 | `bp_share_max_platforms` / `bp_share_mobile_platforms` / `bp_share_force_display` filters | COMPLETE-USER-GUIDE §Troubleshooting | **FABRICATED** | Zero hits. None exist. |
| F11 | `bp_share_available_services` filter | DEVELOPER-GUIDE | **VERIFIED** | `admin/class-buddypress-share-admin.php:991` |
| F12 | `bp_share_services_config` filter | DEVELOPER-GUIDE | **VERIFIED** | `public/class-buddypress-share-public.php:739` |
| F13 | `bp_share_social_button_html` filter | DEVELOPER-GUIDE | **VERIFIED** | `public/class-buddypress-share-public.php:629` |
| F14 | `bp_share_tracking_parameters` filter (5 args) | DEVELOPER-GUIDE | **VERIFIED** | `public/class-buddypress-share-public.php:785` |
| F15 | `bp_share_activity_data` filter | DEVELOPER-GUIDE | **VERIFIED** | `public/class-buddypress-share-public.php:498` |
| F16 | `bp_share_sanitized_extra_settings` filter | DEVELOPER-GUIDE | **VERIFIED** | `admin/class-buddypress-share-admin.php` |
| F17 | `bp_share_enable_post_type_sharing` filter | RELEASE-CHECKLIST, HOW-TO, FEATURE-SUMMARY | **VERIFIED** | `includes/class-buddypress-share.php:313` |
| F18 | `bp_share_disable_ip_tracking` / `bp_share_anonymize_ip` filters | RELEASE-CHECKLIST | **VERIFIED** | `includes/post-types/class-bp-share-post-type-tracker.php:386,399` |
| F19 | `bp_share_allow_anonymous_sharing` / `bp_share_rate_limit` filters | RELEASE-CHECKLIST | **VERIFIED** | `includes/post-types/class-bp-share-post-type-controller.php:244,257` |
| F20 | `bp_share_wrapper_position` filter | HOW-TO-ENABLE-POST-SHARING | **UNVERIFIED** | Zero hits in PHP. Documented but not found in executable PHP. Mark aspirational. |
| F21 | `bp_share_post_type_default_settings` filter | HOW-TO-ENABLE-POST-SHARING | **UNVERIFIED** | Zero hits. Mark aspirational. |
| F22 | `bp_share_post_type_services` filter | HOW-TO-ENABLE-POST-SHARING, ROADMAP | **UNVERIFIED** | Zero hits as `apply_filters`. Possibly intended but not wired. Mark aspirational. |
| F23 | `bp_share_available_post_types` filter | POST-TYPE-FILTERING, FEATURE-SUMMARY | **UNVERIFIED** | Zero hits as `apply_filters`. |
| F24 | `bp_share_post_type_is_valid` filter | FEATURE-SUMMARY | **VERIFIED** | Manifest confirms at `includes/post-types/class-bp-share-post-type-settings.php` |

### 5. Database Schema Claims

| # | Claim | Source doc | Verdict | Evidence |
|---|-------|-----------|---------|----------|
| D1 | Table `{prefix}bp_share_post_tracking` with columns id, post_id, post_type, service, user_id, ip_address, user_agent, referrer, shared_at | RELEASE-CHECKLIST, ROADMAP, manifest | **VERIFIED** | `includes/post-types/class-bp-share-post-type-tracker.php:41` — `$this->table_name = $wpdb->prefix . 'bp_share_post_tracking'` |
| D2 | Table `{prefix}bp_share_post_type_settings` | ROADMAP, manifest | **VERIFIED** | `includes/post-types/class-bp-share-post-type-tracker.php:88` |
| D3 | CRITICAL: Tables never created because `bp_share_activate` hook is never fired | manifest B1 | **VERIFIED** | `includes/class-buddypress-share-activator.php` — `activate()` method never calls `do_action('bp_share_activate')`. Tables silently absent on all installs. |
| D4 | Table named `{prefix}bp_share_post_shares` (different name) | FEATURE-SUMMARY §Database Schema | **DRIFT** | Actual table is `bp_share_post_tracking`. FEATURE-SUMMARY uses wrong name. |
| D5 | Table `{prefix}bp_share_tracking` and `{prefix}bp_share_statistics` | COMPLETE-DEVELOPER-GUIDE §Database Schema | **FABRICATED** | Neither table exists in the codebase. The real tables are `bp_share_post_tracking` and `bp_share_post_type_settings`. |
| D6 | `bp_share_user_stats` (user_meta), `bp_share_activity_stats`, `bp_share_visit_stats` (activity_meta) | PLUGIN_ARCHITECTURE | **VERIFIED** | `includes/class-buddypress-share-tracker.php` |
| D7 | `share_count` (activity_meta/post_meta), `shared_activity_id` (activity_meta) | CLAUDE.md, manifest | **VERIFIED** | `public/class-buddypress-share-public.php` |

### 6. AJAX Endpoint Claims

| # | Claim | Source doc | Verdict | Evidence |
|---|-------|-----------|---------|----------|
| A1 | AJAX action `bp_activity_create_reshare` (without `_ajax` suffix) | DEVELOPER-GUIDE §AJAX Endpoints | **DRIFT** | Actual action is `bp_activity_create_reshare_ajax`. `includes/class-buddypress-share.php:263`: `wp_ajax_bp_activity_create_reshare_ajax`. |
| A2 | All 7 AJAX actions (wss_social_icons, wss_social_remove_icons, bp_activity_create_reshare_ajax, bp_share_get_activity_content, bp_get_user_share_options, bp_share_post, bp_share_track_external) | manifest | **VERIFIED** | `includes/class-buddypress-share.php:207-267` + `includes/post-types/class-bp-share-post-type-controller.php` |

### 7. Class Claims

| # | Claim | Source doc | Verdict | Evidence |
|---|-------|-----------|---------|----------|
| C1 | `Buddypress_Share_License_Manager` class | DEVELOPER-GUIDE §Key Classes | **FABRICATED** | No such class exists. `admin/class-buddypress-share-admin.php:128` has comment: "License tab removed - plugin runs without restrictions." |
| C2 | `BP_Share_Post_Type_Database` class | FEATURE-SUMMARY §Key Classes | **FABRICATED** | No such class or file exists. DB operations are inside `BP_Share_Post_Type_Tracker`. |
| C3 | Core classes: Buddypress_Share, Buddypress_Share_Admin, Buddypress_Share_Public, Buddypress_Share_Tracker, BP_Share_Post_Type_* | manifest, PLUGIN_ARCHITECTURE | **VERIFIED** | All class files present and confirmed. |
| C4 | `Buddypress_Share_Tracker::get_user_stats()`, `get_activity_stats()`, `get_activity_visit_stats()` static methods | DEVELOPER-GUIDE §Tracker Class API | **VERIFIED** | `includes/class-buddypress-share-tracker.php:380,392,404` |

### 8. Version & Metadata Claims

| # | Claim | Source doc | Verdict | Evidence |
|---|-------|-----------|---------|----------|
| V1 | Plugin version 2.1.0 | CLAUDE.md Quick Reference, RELEASE-CHECKLIST | **DRIFT** | Actual version is 2.2.4 (`buddypress-share.php:32`, `audit/manifest.json`). CLAUDE.md and RELEASE-CHECKLIST are stale. |
| V2 | Requires WordPress 5.8+ | USER-GUIDE §Requirements | **DRIFT** | Plugin header states `Requires at least: 5.0`. USER-GUIDE inflates the minimum. |
| V3 | Requires WordPress 5.0+ | COMPLETE-USER-GUIDE §Prerequisites | **VERIFIED** | `readme.txt:6` — `Requires at least: 5.0`. |
| V4 | Requires BuddyPress 5.0+ | USER-GUIDE §Requirements | **DRIFT** | Manifest and CLAUDE.md state BuddyPress 8.0+. USER-GUIDE understates the requirement. |
| V5 | Requires BuddyPress 8.0+ | COMPLETE-USER-GUIDE §Prerequisites, manifest | **VERIFIED** | Consistent with manifest and CLAUDE.md. |
| V6 | Last Updated: Version 2.0.0 | COMPLETE-DEVELOPER-GUIDE, COMPLETE-USER-GUIDE footers | **DRIFT** | Current version is 2.2.4. Both complete guides have stale footers. |
| V7 | Plugin exclusively bundled with Reign Theme and BuddyX Pro | COMPLETE-USER-GUIDE §Introduction | **ASPIRATIONAL** | No activation guard checks for theme. Plugin activates on any installation. The claim describes a commercial bundling arrangement, not a technical enforcement. |

### 9. Admin UI Claims

| # | Claim | Source doc | Verdict | Evidence |
|---|-------|-----------|---------|----------|
| U1 | "Share Style Tab" (tab name) | COMPLETE-USER-GUIDE §Customization | **DRIFT** | Admin tab is named "Display Settings" (`admin/class-buddypress-share-admin.php:339,763`). No "Share Style Tab" exists. |
| U2 | "License Tab" with license key entry | USER-GUIDE §Admin Settings | **FABRICATED** | License tab was removed. `admin/class-buddypress-share-admin.php:128`: "License tab removed - plugin runs without restrictions." |
| U3 | Analytics dashboard at "WordPress Admin > BP Activity Share > Analytics" | COMPLETE-USER-GUIDE §Analytics | **ASPIRATIONAL** | No analytics admin page exists. The plugin tracks data via meta but has no dashboard UI to display it. |
| U4 | "Tools > BP Share Tools" menu with "Reset Share Counts" | COMPLETE-USER-GUIDE §Troubleshooting | **FABRICATED** | No such admin tools page exists anywhere in the plugin. |
| U5 | Settings tabs: Social Networks, Display Settings, Restrictions, Post Type Sharing, FAQ | manifest, PLUGIN_ARCHITECTURE | **VERIFIED** | `admin/class-buddypress-share-admin.php:339` |
| U6 | Settings at "WordPress Admin > Settings > BP Activity Share Pro" | COMPLETE-USER-GUIDE §Initial Setup | **DRIFT** | Correct URL is `admin.php?page=wbcom-buddypress-share` (under WBcom Designs menu). Falls back to `admin.php?page=buddypress-share` under Settings only when Wbcom_Shared_Loader is absent. |
| U7 | CSV / JSON / PDF export and API access for share data | COMPLETE-USER-GUIDE §Analytics | **ASPIRATIONAL** | No export functionality exists in the plugin. |

### 10. GDPR & Privacy Claims

| # | Claim | Source doc | Verdict | Evidence |
|---|-------|-----------|---------|----------|
| G1 | "GDPR compliant with configurable privacy options and user consent features" | COMPLETE-USER-GUIDE FAQ | **DRIFT** | User ID (`bps_uid`) is exposed in all external share URLs by default without opt-in (manifest B5, `public/class-buddypress-share-public.php:765`). IP stored in DB without consent notice (manifest). Filters exist to disable/anonymize but are not enabled by default. The claim overstates compliance. |
| G2 | GDPR filters: `bp_share_disable_ip_tracking`, `bp_share_anonymize_ip` | RELEASE-CHECKLIST | **VERIFIED** | `includes/post-types/class-bp-share-post-type-tracker.php:386,399` |

### 11. Release Checklist Claims

| # | Claim | Source doc | Verdict | Evidence |
|---|-------|-----------|---------|----------|
| RC1 | "READY FOR RELEASE — All critical issues have been addressed" | RELEASE-CHECKLIST | **DRIFT** | Testing checklist items are all unchecked (`[ ]`). Manifest documents 3 blockers/majors unresolved: B1 (tables never created), B2 (cron not wired), B3 (Bluesky icon invalid), B4 (wrong AJAX response format), B6 (empty uninstall.php). |
| RC2 | "Database tables with proper indexes" | RELEASE-CHECKLIST | **DRIFT** | Tables are defined with indexes in code but never created because `bp_share_activate` hook is never fired (manifest B1). The code is correct; the wiring is broken. |

### 12. Post-Type Sharing Feature Claims

| # | Claim | Source doc | Verdict | Evidence |
|---|-------|-----------|---------|----------|
| PT1 | Post type sharing OFF by default, requires filter to enable | HOW-TO-ENABLE-POST-SHARING | **DRIFT** | Filter default is `true` (`includes/class-buddypress-share.php:313`: `apply_filters('bp_share_enable_post_type_sharing', true)`). Feature is ON by default, not off. |
| PT2 | Floating share widget on singular posts | FEATURE-SUMMARY, ROADMAP, HOW-TO | **VERIFIED** | `includes/post-types/class-bp-share-post-type-frontend.php` renders widget via `wp_footer` |
| PT3 | Position filter: left/right | HOW-TO, ROADMAP | **ASPIRATIONAL** | `bp_share_wrapper_position` filter not found as `apply_filters` in PHP. May be hardcoded. |
| PT4 | `[bp_activity_post_reshare]` shortcode | manifest, PLUGIN_ARCHITECTURE | **VERIFIED** | `public/class-buddypress-share-public.php:1931` |
| PT5 | BP_Share_Post_Type_Settings::is_valid_post_type() method | POST-TYPE-FILTERING | **VERIFIED** | Confirmed in manifest. |
| PT6 | ROADMAP database schema shows tables as future work | ROADMAP-POST-TYPE-SHARING §Phase 1 | **DRIFT** | ROADMAP shows unchecked Phase 1 items for table creation, but tables are already coded (though broken due to B1). ROADMAP is stale/pre-shipping. |

---

## Summary by Verdict

| Verdict | Count |
|---------|-------|
| VERIFIED | 28 |
| DRIFT | 18 |
| ASPIRATIONAL | 6 |
| FABRICATED | 16 |

**Total claims audited: 68**

---

## Fold-In Decisions

| Doc | Recommendation | Rationale |
|-----|---------------|-----------|
| COMPLETE-DEVELOPER-GUIDE | **Do not fold in** | Contains wholesale fabricated REST endpoints (R1), fabricated JS namespace (J1, J2), fabricated filters (F1-F10), fabricated hooks (H1-H6), and a fabricated DB schema (D5). Less than 40% of its claims are verified. Salvageable sections: tracker class API (C4), myCRED/GamiPress integration patterns (use real hooks). |
| COMPLETE-USER-GUIDE | **Partial fold-in with heavy editing** | Core user workflow is accurate (social networks, resharing, CLAUDE.md settings). Strip: analytics dashboard (U3), tools page (U4), license tab (U2), GDPR overclaiming (G1), CSV export (U7). Fix: tab naming (U1), settings URL (U6), BuddyPress version (V4/V5). |
| DEVELOPER-GUIDE | **Fold in as primary developer reference** | Highest accuracy doc. Verified hooks, tracker API, filter signatures match code. Fix: AJAX action name (A1), `bp_share_before_create_reshare` is a filter not action (H13). |
| FEATURE-SUMMARY | **Partial fold-in** | Post type overview is accurate. Fix: wrong table name (D4), remove `BP_Share_Post_Type_Database` class (C2). |
| HOW-TO-ENABLE-POST-SHARING | **Fold in with corrections** | Practical guide is useful. Fix: feature-default claim (PT1 — already ON by default). Remove unverified filters (F20-F22). |
| POST-TYPE-FILTERING | **Fold in** | Accurate description of filtering logic. No fabricated claims. |
| RELEASE-CHECKLIST | **Do not fold in** | Internal pre-release checklist, not customer-facing. Testing items all unchecked. Falsely marks READY FOR RELEASE (RC1). |
| ROADMAP-POST-TYPE-SHARING | **Do not fold in** | Pre-shipping internal spec. Phase items have been shipped (table code exists) but are broken (B1). Keeping as developer context only. |
| USER-GUIDE | **Fold in as base, corrections needed** | Good structure and user flow. Fix: WordPress version (V2), BuddyPress version (V4), remove license tab section (U2). |
| PLUGIN_ARCHITECTURE | **Fold in** | Accurate architecture reference. Version number is stale (2.1.0 in text, actual 2.2.4) but structure is correct. |

---

## Basecamp Card Candidates (Code Findings)

These are code bugs found during verification. Each is a Basecamp card candidate for the Bugs column.

| Card # | Title | Severity | Column | Detail |
|--------|-------|----------|--------|--------|
| BC-1 | Custom DB tables never created — `bp_share_activate` hook never fired | Blocker | Bugs | `includes/class-buddypress-share-activator.php` activate() never calls `do_action('bp_share_activate')`. `BP_Share_Post_Type_Tracker` listens for this hook to create tables. Every fresh install has no tables; post-type tracking silently fails. Fix: call `do_action('bp_share_activate')` inside `Buddypress_Share_Activator::activate()`, or wire `BP_Share_Post_Type_Tracker::create_tables()` directly to `register_activation_hook`. |
| BC-2 | Cron cleanup callback not wired | Major | Bugs | `bp_share_weekly_cleanup` cron event scheduled on activation (`class-buddypress-share-activator.php:289`) but no `add_action` wires `Buddypress_Share_Activator::cleanup_orphaned_data` to it. Cleanup never runs. |
| BC-3 | Bluesky icon class invalid in Font Awesome 5 | Major | Bugs | `fas fa-bluesky` does not exist in FA 5.15.4. Added in FA 6.6+. Bluesky button renders blank on all installs. `public/class-buddypress-share-public.php:703`, `includes/post-types/class-bp-share-post-type-settings.php:89`. Use the bundled SVG (`public/images/bluesky-fill.svg`) instead. |
| BC-4 | Post-type AJAX uses `wp_die(json_encode())` instead of `wp_send_json_*` | Major | Bugs | `includes/post-types/class-bp-share-post-type-controller.php:245-297`. Does not set JSON content-type header. Breaks native WP AJAX error handling. |
| BC-5 | User ID exposed in public share URLs (`bps_uid`) | Major | Bugs | `public/class-buddypress-share-public.php:765`, `includes/post-types/class-bp-share-post-type-controller.php:452`. Logged-in user's ID appended to every share link. Any recipient can identify the sharer. Privacy/GDPR concern. Should be opt-in or removed. |
| BC-6 | `uninstall.php` contains no cleanup logic | Major | Bugs | `uninstall.php` is a boilerplate skeleton with only the `WP_UNINSTALL_PLUGIN` guard. Custom tables, options, user_meta, activity_meta are all orphaned on uninstall. WordPress.org submission requirement. |
| BC-7 | `bpas_icon_color_settings` written via `update_site_option` but read via `get_option` | Minor | Code Improvement | Activator sets via `update_site_option`; Admin and Public read via `get_option`. On multisite these return different values, causing silent defaults. |
| BC-8 | Bootstrap 4.6.2 and Select2 loaded from CDN | Major | Bugs | `public/class-buddypress-share-public.php:62-67`. Violates WordPress.org submission rules requiring bundled assets. Site breaks in offline/air-gapped environments. Must be bundled locally before .org submission. |
| BC-9 | CLAUDE.md version mismatch (2.1.0 vs 2.2.4) | Minor | Code Improvement | `CLAUDE.md:5` states version 2.1.0; actual version is 2.2.4. CLAUDE.md needs updating. |
| BC-10 | Post-type sharing ON by default creates silent DB failures | Major | Bugs | `includes/class-buddypress-share.php:313` defaults `bp_share_enable_post_type_sharing` to `true`. The subsystem loads even when custom tables don't exist (see BC-1), causing DB errors on every post-type share attempt. Add a table-existence guard before enabling the subsystem. |

---

## Notes for Wave 3 (Scaffold docs/website/)

1. The ONLY REST surface to document is the `bp_activity_share_count` field via `bp_rest_activity_prepare_value`. No `bp-share/v1` namespace should appear anywhere in docs/website/.
2. The DEVELOPER-GUIDE is the primary basis for docs/website/developer-guide/; COMPLETE-DEVELOPER-GUIDE should NOT be used as source due to fabricated content.
3. The JavaScript section in docs/website/ must describe the actual IIFE structure (no `window.BPShare`).
4. Post-type sharing default state (ON) must be documented correctly — counter to HOW-TO guide.
5. `bp_share_before_create_reshare` must be documented as a filter, not an action.
6. AJAX action name is `bp_activity_create_reshare_ajax` (with `_ajax` suffix).
7. Version must be 2.2.4 throughout.
8. No License Tab section.
9. No Analytics Dashboard UI section (the data is tracked but no UI exists).
