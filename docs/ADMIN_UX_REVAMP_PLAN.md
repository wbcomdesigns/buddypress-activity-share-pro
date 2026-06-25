# Admin UX Revamp + First-Run Onboarding — Implementation Plan

**Plugin:** BuddyPress Activity Share Pro
**Current version:** 2.2.4 → **target 2.3.0**
**Reference pattern:** `buddypress-contact-me` 1.5.0 (modern card + sidebar shell, scoped design tokens)
**Canonical playbook:** `wp-plugin-development/references/wbcom-wrapper-migration.md` (the step-by-step this migration follows; worked examples: Sticky Post 2.3.7, Auto Friends 1.8.2, Contact Me 1.5.0)
**Design system:** `ux-foundation` — 16 admin rules, tokens, 6 page patterns, a11y/responsive contract
**Date:** 2026-06-03
**Status:** PLAN — awaiting approval before any code.

### Guideline decisions (recorded)
- **Icons: Dashicons, NOT Lucide.** ux-foundation Rule 5 prefers Lucide but exempts migrations; the wrapper-migration playbook + every already-migrated sibling (contact-me, Sticky Post, Auto Friends) ship Dashicons. Matching them is the whole point (bundle-wide visual consistency). Using Lucide here would make this plugin the odd one out.
- **Admin CSS needs no dark-mode block** (playbook §10.1 — the WP dashboard owns its color schemes). Tokens / 40px tap targets / `:focus-visible` rings still apply.
- **Scope is UX-only** (playbook Part 0): ZERO renames of option groups, option keys, AJAX action names, nonces, meta keys, cron hooks, transients, or `do_action`/`apply_filters` names. Any rename ships in a separate release.

---

## 1. Goal

Replace the current native-WP, top-tab Settings page with the modern **card + left-sidebar shell** used in `buddypress-contact-me`, driven by **scoped design tokens** (`--bpas-admin-*`), plus a **real, dismissible first-run onboarding** screen that auto-shows once and never again.

**Non-goals (this release):** no change to frontend share buttons, reshare modal, post-type sharing logic, tracking, or REST. Admin-layer only. All existing settings, option keys, storage scope, and AJAX behaviour are preserved byte-for-byte.

---

## 2. What we adopt from contact-me (the canonical modern pattern)

| Element | contact-me source | We replicate |
|---|---|---|
| Menu under shared **`wbcomplugins`** hub | `BCM_Admin::add_menu()` + `takeover_hub_landing()` | Yes — unify our split menu under the hub |
| Page **shell** (header + sidebar nav + body slot) | `views/shell.php` | Yes — `views/shell.php` |
| **Tab registry** → view router | `BCM_Admin::get_tabs()` + `render_page()` | Yes — one page, sidebar-routed views |
| **Scoped CSS tokens** (`--bcm-admin-*`) | `assets/css/admin.css` | Yes — rename to `--bpas-admin-*` |
| **Toast + confirm JS** (no native `alert/confirm`) | `assets/js/admin.js` (`bcmToast`, `bcmConfirm`) | Yes — `bpasToast`, `bpasConfirm` |
| Screen-gated enqueue + foreign-notice suppression | `enqueue_assets()`, `suppress_foreign_notices()`, `is_our_screen()` | Yes |

**What we deliberately do NOT copy:** contact-me's *single-option* `register_setting` model + sentinel inputs. Activity-share-pro stores **many independent `site_option`s** and uses **AJAX drag-drop** for services — a different, working storage contract we must keep (see §5). We borrow contact-me's *chrome*, not its *persistence*.

---

## 3. Current state (verified in code)

- **Menu split (must reconcile):**
  - `Buddypress_Share_Admin::bp_share_plugin_menu()` registers `add_options_page( …, 'buddypress-share', … )` — **only when `Wbcom_Shared_Loader` does NOT exist**.
  - Activation redirect (`buddypress-share.php:460`) and every tab link point to **`admin.php?page=wbcom-buddypress-share`** (the WBCom-integration slug, registered elsewhere).
  - → Today the canonical working page is `wbcom-buddypress-share`; `buddypress-share` is a fallback. The revamp standardizes on **one** hub submenu.
- **5 top-tabs**, custom `.wbcom-nav-tab-wrapper`: Social Networks · Display · Restrictions · Post Type Sharing · FAQ. Routed by `?section=` in `bp_share_plugin_options()`.
- **Storage:** `get_site_option()` / `update_site_option()` for everything (multisite-wide). Caches cleared on `update_site_option_*` hooks.
- **Services AJAX:** `wss_social_icons` / `wss_social_remove_icons` drive a drag-drop enabled/disabled list (`bp_share_services` + `bp_share_services_serialized`).
- **Activation:** `Buddypress_Share_Activator::activate()` seeds defaults (idempotent), sets `bp_share_plugin_version` + `bp_share_install_date`. **No onboarding flag exists yet.**
- **Build:** Grunt (`uglify`, `cssmin`, `rtlcss`, `wp-i18n`). Assets via `bp_share_enqueue_style/script()` helpers (auto `.min` + RTL).

---

## 4. Target architecture (new/changed files)

```
admin/
  class-buddypress-share-admin.php      [MODIFY] menu→hub, enqueue gating, render_page() router,
                                                  onboarding redirect/flag, keep all AJAX + sanitizers
  views/                                [NEW DIR]
    shell.php                           [NEW] header + sidebar + body slot (from contact-me shell)
    hub.php                             [NEW] shared WB Plugins hub landing (card grid)
    onboarding.php                      [NEW] first-run welcome: 3 quick-start steps + CTAs + Skip
    overview.php                        [NEW] stats + current-config summary + quick actions
    settings-networks.php               [NEW] ← bp_share_social_networks_page() body (drag-drop kept)
    settings-display.php                [NEW] ← bp_share_display_settings_page() body
    settings-restrictions.php           [NEW] ← bp_share_restrictions_page() body
    settings-post-types.php             [NEW] ← bp_share_post_types_page() body
    faq.php                             [NEW] ← bp_share_faq_page() body
  css/buddypress-share-admin.css        [REWRITE] token-based; scope .bpas-admin; + css-rtl rebuild
  js/buddypress-share-admin.js          [MODIFY] add bpasToast/bpasConfirm; keep drag-drop + color picker
buddypress-share.php                    [MODIFY] activation redirect → hub slug + onboarding param
includes/class-buddypress-share-activator.php  [MODIFY] seed `bpas_onboarding_complete` = 0 on fresh install only
```

The existing `bp_share_*_page()` methods become thin includes of the matching `views/settings-*.php` (move markup, keep the method as a one-line `include` so nothing else that calls them breaks). This is a **refactor, not a rewrite** of the field logic.

---

## 5. Option-preservation map (NON-NEGOTIABLE — zero data migration)

Every option keeps its **exact key, storage function (`site_option`), and shape**. The UI changes; the contract does not.

| Setting | Option key | Storage | Save path (unchanged) |
|---|---|---|---|
| Master enable | `bp_share_services_enable` | `site_option` | Settings API group `bp_share_general_settings` |
| Logout sharing | `bp_share_services_logout_enable` | `site_option` | same |
| Extra (open-in) | `bp_share_services_extra` | `site_option` | `sanitize_extra_settings()` |
| Enabled services | `bp_share_services` | `site_option` | **AJAX** `wss_social_icons` / `wss_social_remove_icons` |
| Services drag state | `bp_share_services_serialized` | `option` | hidden field |
| Icon style/colors | `bpas_icon_color_settings` | `site_option` | `sanitize_icon_settings()` |
| Reshare/restrictions | `bp_reshare_settings` | `site_option` | `sanitize_reshare_settings()` |
| Post-type sharing | `bp_share_post_type_settings` | `site_option` | custom handler |
| Version / install | `bp_share_plugin_version`, `bp_share_install_date` | `site_option` | activator |

**New option (additive only):** `bpas_onboarding_complete` (`site_option`, `0|1`). No existing key is renamed, moved, or its storage scope changed.

---

## 6. Menu unification (resolves the split)

- Adopt contact-me's hub logic in `bp_share_plugin_menu()`:
  - If `wbcomplugins` top-level not yet registered → `add_menu_page('wbcomplugins', …, render_hub)`.
  - Always `add_submenu_page('wbcomplugins', …, MENU_SLUG, render_page)`.
  - Add `takeover_hub_landing()` at priority 999.
- **Slug decision:** keep **`buddypress-share`** as `MENU_SLUG` (matches `add_options_page` + CLAUDE Settings URL history) **and** register a lightweight redirect/alias so existing `wbcom-buddypress-share` bookmarks and the activation redirect still resolve. Reconcile `class-wbcom-integration.php` so it no longer double-registers. → **Open item to confirm during build:** read `class-wbcom-integration.php` and decide alias vs. canonical; must not produce two menu entries.

---

## 7. First-run onboarding flow + state

```
Activation (activated_plugin)
  └─ if BuddyPress active & not Youzify:
       redirect → admin.php?page=buddypress-share&onboarding=1
                          │
                          ▼
   render_page(): if `onboarding=1` AND get_site_option('bpas_onboarding_complete') !== '1'
        → render views/onboarding.php  (full-width, no sidebar)
            • Step 1: Pick networks  → [tab=networks]
            • Step 2: Choose icon style → [tab=display]
            • Step 3: Set restrictions → [tab=restrictions]
            • [Get Started] → sets flag + goes to Overview
            • [Skip] → sets flag + goes to Overview
        else → normal shell + Overview
```

- **Flag set** via a tiny admin-post/AJAX handler `bpas_complete_onboarding` (nonce + `manage_options`) → `update_site_option('bpas_onboarding_complete','1')`. Both CTAs call it.
- **Re-access:** onboarding is also linkable from the sidebar footer ("Setup guide") but never auto-shows again once the flag is set.
- **Upgrade safety:** existing installs (which already have `bp_share_install_date`) get `bpas_onboarding_complete = 1` set in the activator's upgrade path so they are NOT shown onboarding on update. Only genuinely fresh installs see it.

---

## 8. Tab registry → view map

| Sidebar slug | Group | Icon (dashicon) | View | Save mechanism |
|---|---|---|---|---|
| `overview` | main | `dashicons-chart-bar` | overview.php | n/a |
| `networks` | settings | `dashicons-share-alt2` | settings-networks.php | AJAX drag-drop + Settings API |
| `display` | settings | `dashicons-art` | settings-display.php | Settings API |
| `restrictions` | settings | `dashicons-admin-settings` | settings-restrictions.php | Settings API |
| `post-types` | settings | `dashicons-admin-post` | settings-post-types.php | custom handler |
| `faq` | resources | `dashicons-editor-help` | faq.php | n/a |

Legacy `?section=` values (`general/services/icons/sharing/post-types/faq`) map onto the new `?tab=` slugs so old links keep working.

---

## 9. Design tokens & assets

- New `assets`/`admin/css/buddypress-share-admin.css` built on **`.bpas-admin`-scoped** custom properties mirroring contact-me's token set (accent, surface, text scale, semantic, radius, spacing, shadow). **No raw hex in component rules.**
- Honors **dark mode** and **RTL** (rebuild `admin/css-rtl/` via `grunt rtlcss`); 40px min tap targets (fixes the a11y nit contact-me carries).
- `admin/js/buddypress-share-admin.js`: add `window.bpasToast()` + `window.bpasConfirm()` (Promise-based, ESC/Enter, focus trap); **keep** existing jQuery UI drag-drop + `wpColorPicker`.
- Enqueue gated by `is_our_screen()` (our submenu + hub landing only). `suppress_foreign_notices()` on our screen.
- `wp_localize_script` `bpasAdmin` with `ajaxUrl`, nonces (`onboarding`, existing service nonces), i18n strings.

---

## 10. Version, changelog, docs

- Bump **2.2.4 → 2.3.0** across: `buddypress-share.php` header + `BP_ACTIVITY_SHARE_PLUGIN_VERSION`, `readme.txt` stable tag + changelog, `package.json`, `CLAUDE.md` Quick Reference.
- `readme.txt` changelog entry (customer-facing, plain language per portfolio policy): *"Refreshed the admin settings screen with a cleaner card-based layout, a left-hand menu, and a quick setup guide shown the first time you activate the plugin. All your existing settings are kept."* — no file paths / hook names / jargon.
- Refresh `CLAUDE.md`: new admin file map, settings URL, onboarding flag, version, Recent Changes row.

---

## 11. Big-site readiness check (admin scope)

Admin pages here aren't row-list heavy, but per the checklist:
- **Overview stats** must use `COUNT(*)`-style aggregate reads, not loading all share rows — reuse existing tracker count methods; cache the dashboard numbers (transient, invalidated on share write) so Overview doesn't query cold on every page load.
- Services list is bounded (≤12) — fine.
- Post-type settings list bounded by registered public post types — fine.
- All three states (empty / error / loading) handled on Overview + onboarding.

---

## 12. QA gates (must pass before release — from playbook Part 14)

**Static / contract:**
1. `php -l` on every new/changed PHP file.
2. **WPCS** clean (`mcp__wpcs__wpcs_check_directory`) — errors block, warnings OK.
3. **wppqa MCP** `wppqa_audit_plugin` → `failed=0`; specifically `wppqa_check_plugin_dev_rules` (no alert/confirm, no `$_POST` iteration, 40px, no raw `-1`), `wppqa_check_wiring_completeness` (every saved setting is read somewhere).
4. **`/action-audit`** (playbook Part 12): view selectors ↔ JS handlers ↔ `wp_ajax_*` ↔ nonce names all consistent; no orphan buttons, no dead handlers.
5. **Template-variable contract** (Part 12.2): every `@var` the shell reads is set by `render_page()` before `include` — guards the `$bcm_tabs`-vs-`$tabs` blank-sidebar bug that hit contact-me on release.
6. **PCP on the BUILT zip** (not source): `wp plugin check` = 0 errors (the EDD `plugin_updater_detected` is the only allowed detection if licensing is active).
7. **README ↔ plugin-header match**: `Requires at least`, `Requires PHP`, `Stable tag` identical in both files.
8. **ABSPATH guard** + `/* translators */` comments on every placeholder string; no `esc_html_e($var)` (use `echo esc_html($var)`).

**Functional / data:**
9. **Settings round-trip**: save each tab, reload, confirm every option in §5 persists with identical key/scope (`wp option get` / `wp site option get`). Especially services drag-drop + "unchecked = off". If any single option key is split across two tabs, add the sentinel-merge sanitizer (playbook §7.1).
10. **Multisite**: `site_option` scope still network-wide.
11. **Onboarding**: fresh install → shows once → flag set → never again; upgrade install → never shows.
12. **Menu**: exactly one submenu under WB Plugins; old `wbcom-buddypress-share` + `buddypress-share` URLs both resolve; hub takeover works in a mixed install.

**Browser (Playwright MCP) + regression:**
13. All views render at 390/430/768/1024/1440px; `:focus-visible` rings; keyboard nav; zero console errors; tail `debug.log` for PHP notices while clicking every action button.
14. **No frontend regression**: share dropdown + reshare modal + post-type buttons unchanged (old option keys honored).
15. **QA-MANUAL.md** (15 sections, per Contact Me `2956d8f` shape) written before release.

---

## 13. Risks & mitigations

| Risk | Mitigation |
|---|---|
| Breaking the services AJAX during markup move | Keep handlers + DOM IDs/classes the drag-drop JS relies on; move markup verbatim into `settings-networks.php` |
| Double menu entries from WBCom integration | Reconcile `class-wbcom-integration.php` before merge (Open item §6) |
| Showing onboarding to existing users on update | Set `bpas_onboarding_complete=1` in activator upgrade branch |
| Token CSS bleeding into other Wbcom pages on hub | Scope all vars under `.bpas-admin`; hub uses neutral shared tokens |
| site_option vs option confusion | Map in §5 is the single source of truth; no scope changes |

---

## 14. Build sequence (phased, after approval → maps to autovap waves)

0. **Inventory pre-pass** (playbook Part 1) — grep + record every option key, AJAX action, nonce, meta key, cron hook the plugin emits. This list is the regression safety net; re-grep the rebuilt code before each commit. Confirm whether EDD licensing is active (License tab in/out) and whether any option key is split across tabs (sentinel needed?).
1. **Shell + tokens** — `views/shell.php` (+ `wp-header-end` marker), `hub.php` (+ hub-takeover @999, wrapper-slug filter), token CSS (`sed bpsp- → bpas-`, verify `grep -c "^\.bpsp-"` = 0), enqueue gating (`is_our_screen`), menu→hub. **Retain `class-buddypress-share-admin.php`** for sanitizers + the two service AJAX handlers (Part 3) — new panel class owns UI; unwire the old class's menu/enqueue methods; verify no method left at `0 refs`.
2. **View extraction** — move each `bp_share_*_page()` body into `views/settings-*.php`; methods become thin includes. Verify save round-trip per tab (§12.9).
3. **Overview + onboarding** — `overview.php`, `onboarding.php`, flag handler, activation redirect + upgrade-safety (preset flag for existing installs).
4. **JS** — `bpasToast`/`bpasConfirm`; generic `[data-bpas-confirm]` handler must **skip elements that own a specific `data-action` handler** (playbook §11.1 — the drag-drop/service buttons); wire confirms on destructive actions.
5. **Polish** — RTL rebuild (`grunt rtlcss`), a11y (`:focus-visible`, aria-labels, 40px), FAQ, **drop the Bootstrap/Select2/FontAwesome CDN enqueues** (Part 14 gate — bundle locally or use WP core), **plain-language copy pass** (Part 17 — no option-key jargon in labels), **privacy audit** of any legacy setting that exposes non-owner data (Part 18).
6. **Gates** — run `/action-audit` + §12 static/contract gates → browser checklist (§12.13) → version bump 2.2.4→2.3.0 → changelog/docs/QA-MANUAL → commit.

---

## 15. Open items
- ~~Read `class-wbcom-integration.php` → decide canonical slug vs alias (§6).~~ **RESOLVED (owner, 2026-06-03):** remove the old admin wrapper entirely — no alias, no coexistence. Build the new card-panel admin clean under the `wbcomplugins` hub with a clean canonical slug; do NOT preserve the old `wbcom-buddypress-share` bookmark ("free to use any link"). "Wrapper" = legacy UI/chrome + old admin CSS/JS (delete per playbook Part 2); the non-UI `sanitize_*` methods + the two service AJAX handlers are KEPT (playbook Part 3 — they are the data contract, not wrapper). There is no `class-wbcom-integration.php`; the old slug came from the shared `Wbcom_Shared_Loader` hub — locate and detach from it.
- **License tab: OUT (resolved in W0).** No EDD; updates ship via Plugin Update Checker v5. Only a dormant `bp_activity_share_plugin_license_key` key exists — pinned, no UI.
- **`bpas_icon_color_settings` scope (resolved in W0):** written via `update_site_option`, read via `get_option`. Preserve byte-for-byte; do NOT reconcile (separate release). Round-trip journey asserts unchanged read-back.
- **`bp_share_services_serialized`** is registered inside the `bp_share_general_settings` group, not standalone — pinned accordingly.
- Confirm tracker exposes a cheap `COUNT(*)` for Overview stats (§11); add one if missing.
