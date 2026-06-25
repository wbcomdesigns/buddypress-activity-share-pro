# Functionality Gap Audit — BuddyPress Activity Share Pro v2.3.0

**Branch:** `admin-ux-revamp-2.3.0`  
**Audit date:** 2026-06-03  
**Purpose:** Covers all gaps so the end user benefits before shipping as a feature release.  
**Method:** Every option / meta / setting / behavior classified A–E below.

---

## Classification Key

| Code | Meaning |
|------|---------|
| A | Has UI + works — fine, skip |
| B | Works but no admin toggle — hardcoded default, owner can't change it |
| C | Setting saved / defaulted but dead — never read by any code |
| D | Pro feature with weak/missing admin surface — exists but under-exposed |
| E | Missing capability — a competing plugin would offer this |

---

## Prioritised Task List

### P0 — Broken / Dead setting with real intent (fix before release)

---

#### P0-1 · `enable_share_count` is written but never read

**Category:** C  
**Evidence:** `includes/class-buddypress-share-activator.php:116` sets `enable_share_count => 1` inside `bp_reshare_settings`. Every read-path (`bp_share_inner_activity_filter`, `bp_activity_post_share_button_action`, shortcode `bp_activity_post_reshare`, REST API field) unconditionally reads and renders `share_count` meta — zero check against this key.  
**Impact:** Site owner saving "hide the count" (via any future UI, or if they set it to 0 directly) would see no change. The toggle has no effect.  
**Type:** UI-only + minimal backend guard (`if (!$settings['enable_share_count']) return ''` on the count span)  
**Effort:** S  
**Key:** `bp_reshare_settings['enable_share_count']`  

---

#### P0-2 · `prevent_self_share` is written but never enforced

**Category:** C  
**Evidence:** `activator.php:117` sets `prevent_self_share => 0`. Neither `bp_activity_create_reshare_ajax()` (`public/class-buddypress-share-public.php:960`) nor any hook checks the user ID of the original activity against `get_current_user_id()`.  
**Impact:** A user can reshare their own activity regardless of the setting. For communities where that looks spammy (self-promotion), this is the #1 operator complaint.  
**Type:** Needs frontend wiring — add a check in `bp_activity_create_reshare_ajax` after nonce/auth, before `create_share_activity`.  
**Effort:** S  
**Key:** `bp_reshare_settings['prevent_self_share']`  

---

#### P0-3 · `respect_privacy` is written but never enforced

**Category:** C  
**Evidence:** `activator.php:118` sets `respect_privacy => 1`. The `create_share_activity()` method (`public/class-buddypress-share-public.php:1128`) sets `hide_sitewide` only for non-public groups. There is no check that the original activity is visible to the resharing user, nor any guard that the reshare inherits the original's visibility.  
**Impact:** Private activity in a hidden group can be reshared to a public profile, leaking content. Effectively breaks the "respect privacy" guarantee that the setting name implies.  
**Type:** Needs frontend wiring — read original activity's `component`/`item_id`/`hide_sitewide` and enforce inheritance in `create_share_activity`.  
**Effort:** M  
**Key:** `bp_reshare_settings['respect_privacy']`  

---

#### P0-4 · `bpas_icon_color_settings[border_color]` is sanitized but never used

**Category:** C  
**Evidence:** `admin/class-buddypress-share-admin.php:452` — `sanitize_icon_settings()` iterates `['bg_color','text_color','hover_color','border_color']` and persists `border_color` if submitted. The Display view (`admin/views/settings-display.php`) has no field for `border_color`. Neither the activity-share CSS (`buddypress-share-public.css`) nor the post-type frontend (`class-bp-share-post-type-frontend.php:163–165`) injects `border_color` as a CSS custom property.  
**Impact:** If a developer sets this via WP-CLI, nothing happens visually.  
**Type:** UI-only — add the color-picker field to the Display view and inject `--bp-share-btn-border` into the inline style block in `render_inline_buttons` and via `wp_add_inline_style` for the activity-share dropdown.  
**Effort:** S  
**Key:** `bpas_icon_color_settings['border_color']`  

---

#### P0-5 · `disable_friends_reshare_activity` — removed from activator defaults but still read by modal

**Category:** C / inconsistency  
**Evidence:** `activator.php:375–378` explicitly unsets `disable_friends_reshare_activity` from `bp_reshare_settings` on activation/upgrade (cleanup of a removed feature). Yet `public/class-buddypress-share-public.php:569` still lists it in `$reshare_types` for the "any reshare enabled?" check, and `public/class-buddypress-share-public.php:1743` checks `empty($bp_reshare_settings['disable_friends_reshare_activity'])` to conditionally render the Friends optgroup in the modal. Result: the optgroup renders (key is absent, `empty(null)` is true), but the Restrictions tab has no toggle for it — the admin can't disable friend-resharing.  
**Impact:** Friend-reshare is permanently enabled with no admin control. If the feature was intentionally retired, the modal dead-branch should be removed. If it should be re-exposed, the Restrictions view needs the toggle back.  
**Type:** Decision required (remove OR re-expose); either path is UI-only.  
**Effort:** S  
**Key:** `bp_reshare_settings['disable_friends_reshare_activity']`  

---

#### P0-6 · Activity-share buttons ignore saved `bg_color` / `text_color` / `hover_color`

**Category:** B (partially)  
**Evidence:** `class-bp-share-post-type-frontend.php:163–165` correctly injects the three colors as `--bp-share-btn-bg/color/hover` CSS custom properties into the post-type inline/floating widget. But the activity-feed share dropdown (`public/class-buddypress-share-public.php:513`) only uses `icon_style` for the CSS class name; it never injects the saved colors as inline CSS vars on the dropdown container. The activity-share CSS hardcodes `--bp-share-primary: #2563eb` (`buddypress-share-public.css:26`).  
**Impact:** "Background color" and "Icon color" saved on the Display tab take effect on post-type buttons but have zero visible effect on the activity-feed share dropdown (the main feature surface). The admin will think the setting is broken.  
**Type:** UI-only — output an inline `style="--bp-share-btn-bg:X;..."` attribute on the `.bp-activity-share-dropdown-menu` container in `bp_share_inner_activity_filter()`.  
**Effort:** S  
**Key:** `bpas_icon_color_settings['bg_color','text_color','hover_color']`

---

### P1 — High end-user value (ship in same feature release)

---

#### P1-1 · Share analytics / stats are collected but never surfaced in the admin

**Category:** D  
**Evidence:**  
- `includes/class-buddypress-share-tracker.php` writes `bp_share_user_stats` (user meta) and `bp_share_activity_stats` + `bp_share_visit_stats` (activity meta) on every reshare and tracked link visit.  
- `includes/post-types/class-bp-share-post-type-tracker.php` writes a full `{prefix}bp_share_post_tracking` table row (post_id, service, user_id, ip, referrer, shared_at).  
- `admin/views/overview.php` shows "Total post shares" and "Shares today" from the tracking table but shows nothing from the activity-share tracker (user meta / activity meta).  
- There is no way for the admin to see: top-shared activities, most active sharers, per-network breakdown for activity shares, or to filter/date-range any of these.  
**Impact:** The infrastructure is production-quality; the value is invisible. Admins who pay for "Pro" expect a reporting screen.  
**Type:** UI-only — add an Analytics tab (or expand Overview) with: top 10 shared activities, top sharers, per-network counts for the last 30 days, date filter. Data exists in meta; no new tables needed.  
**Effort:** M  
**Key:** N/A (new `analytics` tab reading existing meta/table data)  

---

#### P1-2 · Rate limiting on post-type sharing is hardcoded at 20/hour — no admin control

**Category:** B  
**Evidence:** `includes/post-types/class-bp-share-post-type-controller.php:257` — `apply_filters('bp_share_rate_limit', 20)`. There is no admin field; the filter exists for developers only.  
**Impact:** On low-traffic community sites 20/hr may be too low; on high-risk spam sites too high. No admin can adjust without custom code.  
**Type:** UI-only — add a numeric field in the Post Type Sharing tab (or a new Advanced/Restrictions card) that saves to `bp_share_post_type_settings['rate_limit']` and passes it as the filter default.  
**Effort:** S  
**Key:** `bp_share_post_type_settings['rate_limit']` (new key, reuses filter)  

---

#### P1-3 · UTM tracking parameters are always appended — no opt-out

**Category:** B  
**Evidence:** `public/class-buddypress-share-public.php:751–788` — `add_share_tracking_params()` unconditionally adds `utm_source`, `utm_medium`, `utm_campaign`, `bps_aid`, `bps_uid`, `bps_time`, `utm_content`, `bps_service` to every share link. The post-type controller does the same. An admin cannot disable this (e.g., for GDPR environments where `bps_uid` could be PII in shared URLs).  
**Impact:** GDPR-conscious sites need a toggle to strip the `bps_uid` param at minimum. Some admins also don't want UTM noise in their own GA.  
**Type:** UI-only — add a "UTM tracking" toggle in Networks or a new Privacy card. When off, skip `add_share_tracking_params` (already filter-hookable with `bp_share_tracking_parameters` returning empty array, but that's developer-only).  
**Effort:** S  
**Key:** `bp_share_services_extra['enable_utm_tracking']` (new key)  

---

#### P1-4 · No reshare notifications — original author never told their content was reshared

**Category:** E  
**Evidence:** `do_action('bp_share_after_create_reshare', ...)` and `do_action('bp_share_user_reshared_activity', ...)` fire on every reshare (`public/class-buddypress-share-public.php:1058–1076`). No notification is sent. No BP notification (`bp_notifications_add_notification`) is wired anywhere.  
**Impact:** The author of an activity has no idea anyone reshared their content. On BuddyBoss/BuddyPress, members expect a notification ("User X reshared your activity"). This is the most-requested Pro feature in competing plugins (Youzify, BuddyPress Activity Plus).  
**Type:** Needs frontend wiring — hook `bp_share_after_create_reshare`, call `bp_notifications_add_notification()` for the original activity's `user_id`, add a `bp_share_notifications_format_callback()` action. Add a toggle in Restrictions: "Notify author when their activity is reshared".  
**Effort:** M  
**Key:** `bp_reshare_settings['enable_reshare_notifications']` (new key)  

---

#### P1-5 · No per-activity-type enable/disable for social-network sharing

**Category:** E  
**Evidence:** The Networks tab enables/disables networks globally. The `bp_share_inner_activity_filter()` function has a hook (`bp_share_should_show_share_button`) that checks the activity type, but `bp_share_should_show_share_button()` is never defined in the plugin's PHP — it's referenced but only guards the button if it exists (`function_exists` gate, `public/class-buddypress-share-public.php:478`).  
**Impact:** Admins cannot hide the social-share buttons on certain activity types (e.g., hide "Share" on profile-update activities but keep it on status posts). Competing plugins (BuddyPress Activity Filter Pro) offer this as table stakes.  
**Type:** Needs frontend wiring — define `bp_share_should_show_share_button()` as a function that reads from a new `bp_share_services_extra['disabled_activity_types']` array setting, and add a multi-checkbox UI in the Networks or Restrictions tab listing registered BP activity types.  
**Effort:** M  
**Key:** `bp_share_services_extra['disabled_activity_types']` (new key)  

---

#### P1-6 · Post-type sharing DB tables only created on `bp_share_activate` hook — silent failure on existing sites

**Category:** D  
**Evidence:** `includes/post-types/class-bp-share-post-type-tracker.php:44` hooks `create_tables()` to `bp_share_activate`. This hook fires only in `register_activation_hook`. Sites that had the plugin active before 2.1.0 (when post-type sharing was added) never trigger this hook on upgrade, so `{prefix}bp_share_post_tracking` may not exist.  
**Evidence corroborated by:** CLAUDE.md Known Issues list and `admin/views/overview.php:40` which guards with `SHOW TABLES LIKE %s` — a defensive measure that confirms the missing-table risk is known.  
**Impact:** Post-type sharing shows no stats and silently fails to track for any site that upgraded from < 2.1.0.  
**Type:** Needs wiring — add a `dbDelta` call in the upgrade path inside `handle_plugin_upgrade()` (already exists in `activator.php:253`) for `version_compare($old,'2.1.0','<')`.  
**Effort:** S  
**Key:** N/A (migration path fix)  

---

### P2 — Nice-to-have (next minor release)

---

#### P2-1 · `border_color` picker missing from Display view (UI gap for existing sanitizer)

Covered under P0-4 above (dual P0 because the dead sanitizer is a waste; the picker is P2 value).

---

#### P2-2 · "Open links in popup" setting only applies to non-WhatsApp, non-email links — no admin explanation

**Category:** B  
**Evidence:** `public/class-buddypress-share-public.php:835` — inline script excludes `#bp_whatsapp_share` and `#bp_email_share` from receiving `has-popup`. The Networks tab toggle just says "Open links in a popup window" with no caveat. Admins enabling the toggle will wonder why WhatsApp and Email don't pop up.  
**Impact:** Confusing UX for site admins; no current way to control which services should be excluded from popup mode.  
**Type:** UI-only — add a helper text under the toggle ("WhatsApp and Email always open in a new tab.") or, better, expose a per-service popup override.  
**Effort:** S  
**Key:** N/A (copy clarification only for the basic fix)  

---

#### P2-3 · Share count on activity buttons not guarded by `enable_share_count`

This is the same as P0-1 but called out separately for the CSS/display angle: the count `<span>` is always rendered (even when empty), creating an empty span in the DOM on every activity. Should be conditionally output.  
**Category:** B (follow-up from P0-1)  
**Effort:** S  

---

#### P2-4 · No icon/button preview in the Display tab

**Category:** D  
**Evidence:** The 2.3.0 Display view removed the icon preview that existed in older versions (referenced in CLAUDE.md: "old admin had an icon preview"). The style-selector shows text labels and a blank `.style-preview` div (the class exists in the CSS; it renders nothing in the new admin shell).  
**Impact:** An admin changing from "Circle" to "Bar" has to visit the frontend to see the result.  
**Type:** UI-only — add a live-preview box that renders sample buttons with the selected style and current color-picker values via JS on input change.  
**Effort:** M  
**Key:** N/A  

---

#### P2-5 · Who-can-reshare (minimum role / capability gate) not exposed

**Category:** E  
**Evidence:** `bp_activity_create_reshare_ajax()` checks only `is_user_logged_in()` — any authenticated user can reshare, including subscribers. No role or membership check.  
**Impact:** Membership-gated communities (e.g., "only Premium members can reshare") have no option.  
**Type:** Needs frontend wiring — add a role/capability selector in Restrictions: "Minimum role to reshare" (subscriber / contributor / member / custom capability). Read in the AJAX handler before creating the activity.  
**Effort:** M  
**Key:** `bp_reshare_settings['min_reshare_capability']` (new key)  

---

#### P2-6 · Post-type sharing: per-post-type service overrides are saved but not exposed in the admin UI

**Category:** D  
**Evidence:** `BP_Share_Post_Type_Settings` has `post_type_services` in its data model (`get_services_for_post_type()` at `class-bp-share-post-type-settings.php:204`) and it's saved in `settings-post-types.php:39`. The admin partial (`admin/partials/bp-share-post-type-settings.php`) does not render per-post-type service checkboxes — only global display settings.  
**Impact:** The architecture supports "posts show Facebook+LinkedIn, pages show Copy Link only" but the admin has no way to configure it.  
**Type:** UI-only — add a collapsible per-post-type section in the Post Type Sharing tab with service checkboxes for each enabled post type.  
**Effort:** M  
**Key:** `bp_share_post_type_settings['post_type_services'][{post_type}]` (key exists)  

---

#### P2-7 · UTM `campaign` is hardcoded as `activity_share` / `post_share` — no custom campaign name field

**Category:** B  
**Evidence:** `public/class-buddypress-share-public.php:764` and `class-bp-share-post-type-controller.php:452`.  
**Impact:** Site owners running paid ads or wanting to distinguish community-A vs community-B in GA cannot set a custom campaign name without a developer filter.  
**Type:** UI-only — add "UTM campaign name" text field under Networks.  
**Effort:** S  
**Key:** `bp_share_services_extra['utm_campaign']` (new key)  

---

## Setting / Feature State Matrix (Reshare engine deep-dive)

| Feature | Wired? | Admin configurable? | Key | Notes |
|---------|--------|---------------------|-----|-------|
| Share count display | YES — always shows | NO — `enable_share_count` key exists, never read | `bp_reshare_settings['enable_share_count']` | P0-1 |
| Prevent self-share | NO — never checked | NO — key saved, ignored | `bp_reshare_settings['prevent_self_share']` | P0-2 |
| Respect privacy | Partial — group-only | NO — key saved, ignored | `bp_reshare_settings['respect_privacy']` | P0-3 |
| Friends reshare optgroup | YES — always shown | NO — key cleaned up in activator | `bp_reshare_settings['disable_friends_reshare_activity']` | P0-5 |
| Per-network on/off | YES | YES — drag-drop | `bp_share_services` | A |
| Reshare to profile | YES | YES — checkbox | `bp_reshare_settings['disable_my_profile_reshare_activity']` | A |
| Reshare to group | YES | YES — checkbox | `bp_reshare_settings['disable_group_reshare_activity']` | A |
| Reshare post (blog) | YES | YES — checkbox | `bp_reshare_settings['disable_post_reshare_activity']` | A |
| Reshare display mode (parent/child) | YES | YES — radio | `bp_reshare_settings['reshare_share_activity']` | A |
| Reshare notifications | NO | NO | not yet | P1-4 |
| Who-can-reshare (role gate) | NO | NO | not yet | P2-5 |
| Share limit / rate limiting | Partial — post-type only (20/hr) | NO | filter only | P1-2 |
| Icon style | YES | YES — radio | `bpas_icon_color_settings['icon_style']` | A |
| Background color | Partial — post-type only | YES — picker | `bpas_icon_color_settings['bg_color']` | P0-6 |
| Icon/text color | Partial — post-type only | YES — picker | `bpas_icon_color_settings['text_color']` | P0-6 |
| Hover color | Partial — post-type only | YES — picker | `bpas_icon_color_settings['hover_color']` | P0-6 |
| Border color | NO | NO — field missing | `bpas_icon_color_settings['border_color']` | P0-4 |
| UTM tracking on/off | NO — always on | NO | filter only | P1-3 |
| Per-activity-type social share | NO | NO | `bp_share_should_show_share_button()` not defined | P1-5 |
| Analytics dashboard | NO | N/A | tracking data exists, not displayed | P1-1 |
| Post-type tracking table on upgrade | NO — activation-only | N/A | `bp_share_post_tracking` may not exist | P1-6 |

---

## Summary

| Priority | Count | Items |
|----------|-------|-------|
| P0 | 6 | P0-1 through P0-6 |
| P1 | 6 | P1-1 through P1-6 |
| P2 | 7 | P2-1 through P2-7 |
| **Total gaps** | **19** | |

