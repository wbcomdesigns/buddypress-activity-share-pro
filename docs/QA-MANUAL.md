# QA Manual — BuddyPress Activity Share Pro 2.3.0

Admin UX revamp + first-run onboarding. **UX-only release** — no option keys,
storage scopes, AJAX actions, nonces, meta keys, cron hooks, or hook names
changed. This manual covers what to verify before tagging.

- **Settings URL:** `admin.php?page=buddypress-share` (under the WB Plugins / `wbcomplugins` hub)
- **Test site:** `https://wbcom-pro.local`
- **Versions:** 2.2.4 → 2.3.0

---

## 1. Menu & hub

- [ ] Exactly **one** "Activity Share" submenu appears under **WB Plugins** (no duplicate).
- [ ] `admin.php?page=buddypress-share` loads the new card-panel screen.
- [ ] `admin.php?page=wbcomplugins` shows the WB Plugins hub card grid (our render).
- [ ] The legacy `admin.php?page=wbcom-buddypress-share` bookmark is **intentionally gone** (owner directive — no alias). It returns the standard WP "not allowed / not found" page.
- [ ] Plugin-row **Settings** link points to `admin.php?page=buddypress-share`.

## 2. Shell & navigation

- [ ] Header shows title, subtitle, and the version pill (`v2.3.0`).
- [ ] Sidebar groups: **Overview**; **Settings** (Social Networks, Display, Restrictions, Post Type Sharing); **Resources** (FAQ); plus Setup guide + Documentation.
- [ ] Active tab is highlighted. Each tab link uses `&tab=<slug>`.
- [ ] No third-party admin notices leak onto the screen.

## 3. Overview

- [ ] Stats render: **Total post shares**, **Shares today**, **Active networks** (matches the count of enabled networks).
- [ ] On a site with no tracking rows, stats show `0` (no error, no fatal).
- [ ] "Current setup" reflects the real sharing on/off, guest on/off, and icon style.
- [ ] Quick-action buttons navigate to Networks / Display / Setup guide.

## 4. First-run onboarding

- [ ] **Fresh install** (delete `bpas_onboarding_complete`, activate): redirect lands on `&onboarding=1` and the full-width welcome shows (no sidebar).
- [ ] **Skip for now** → sets `bpas_onboarding_complete = 1`, lands on Overview.
- [ ] **Get started** → same: flag set, lands on Overview.
- [ ] Reload `&onboarding=1`: onboarding does **not** show again (normal shell renders).
- [ ] **Upgrade install** (had `bp_share_install_date` already): onboarding **never** shows — the activator presets the flag.
- [ ] Correct at 390px and 1280px. No console errors.

## 5. Settings round-trip (contract — option keys/scopes unchanged)

For each, change a value, Save, reload, and confirm persistence with `wp option get <key>` (single-site) / `wp site option get <key>` (multisite):

- [ ] **Social Networks** — `bp_share_services_enable`, `bp_share_services_logout_enable`, `bp_share_services_extra` persist (`bp_share_general_settings` group).
- [ ] **Display** — `bpas_icon_color_settings` persists. **Sentinel:** it is written by the Settings API and read with `get_option`; the read-back must be byte-identical to before. Do **not** reconcile its scope.
- [ ] **Restrictions** — `bp_reshare_settings` persists with the same key.
- [ ] **Post Type Sharing** — saving via the `bp_share_post_type_settings` nonce persists `bp_share_post_type_settings`.
- [ ] After save, the page returns to `admin.php?page=buddypress-share&tab=<slug>`.

## 6. Services drag-drop

- [ ] On Networks, drag a network from **Inactive → Active**: `wss_social_icons` AJAX fires, network moves, `bp_share_services` updates.
- [ ] Drag **Active → Inactive**: `wss_social_remove_icons` fires, `bp_share_services` updates.
- [ ] Reorder within Active, Save: `bp_share_services_serialized` reflects the order on reload.
- [ ] Nonce `bp_share_admin_nonce` unchanged. No console errors.

## 7. Toast / confirm

- [ ] `window.bpasToast(msg, tone)` renders a dismissing toast (info/success/error).
- [ ] `window.bpasConfirm(opts)` opens a modal; ESC cancels, Enter confirms, click-outside cancels, focus lands on the confirm button.
- [ ] An element with both `data-bpas-confirm` and `data-action` is **not** intercepted by the generic handler (yields to its own handler).
- [ ] No native `alert()` / `confirm()` anywhere.

## 8. Frontend regression (must be unchanged)

- [ ] Activity stream: share dropdown opens; reshare modal opens, submits, creates a reshared activity (`share_count` + `shared_activity_id` meta written with the old keys).
- [ ] Post-type share buttons render and track on a single post.
- [ ] Guest sharing still works when enabled.

## 9. Multisite

- [ ] `site_option`-scoped keys remain network-wide.
- [ ] Onboarding flag is network-wide (`bpas_onboarding_complete` via `update_site_option`).

## 10. Accessibility

- [ ] Keyboard: tab through the sidebar + form controls; visible `:focus-visible` rings.
- [ ] Tap targets ≥ 40px (nav links, checkboxes/radios, service chips, buttons).
- [ ] Decorative dashicons are `aria-hidden`; nav has an `aria-label`.

## 11. Responsive & RTL

- [ ] 1280px (sidebar + content), 768/1024px (sidebar wraps), 390/430px (single column).
- [ ] In an RTL locale the layout mirrors (logical properties + `admin/css-rtl/` build).

## 12. Dark mode

- [ ] The WP admin owns its color scheme; the panel tokens stay legible in both light and the WP dark admin schemes.

## 13. Static / contract gates

- [ ] `php -l` clean on all changed PHP.
- [ ] WPCS: 0 errors on new files.
- [ ] PCP (`wp plugin check`) on the **built zip**: clean of licensing detections (no EDD here — updates via Plugin Update Checker v5).
- [ ] README ↔ header match: `Requires at least` 5.0, `Requires PHP` 7.4, `Stable tag` 2.3.0.
- [ ] Re-grep confirms zero renames of option keys / `wp_ajax_*` / nonces / meta / cron / hooks.

## 14. Known / accepted

- [ ] Icons are **Dashicons**, not Lucide (accepted migration exception; matches migrated siblings).
- [ ] `bpas_icon_color_settings` split scope (write site_option / read get_option) is **preserved on purpose** — any reconciliation ships separately with a migration.
- [ ] Pre-existing public `ajax_track_external_share` is nonce-gated (no capability) by design — frontend tracking, out of this release's scope.

## 15. Open / escalated

- [ ] **Frontend CDN drop (plan §14.5) NOT done in 2.3.0.** Bootstrap (reshare modal) and Select2 (group/friend selector) are still loaded from CDN on the front end. Dropping them without bundling local copies would break the reshare modal — a frontend regression the no-regression contract forbids. Bundle local Bootstrap/Select2 (or move the modal off Bootstrap) in a separate frontend release. The **admin** layer already loads no CDN (Dashicons only).
