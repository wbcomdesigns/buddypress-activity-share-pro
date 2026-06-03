# AutoVAP Plan Rationale — Admin UX Revamp + First-Run Onboarding

**Target:** buddypress-activity-share-pro 2.2.4 → 2.3.0
**Spine:** `docs/ADMIN_UX_REVAMP_PLAN.md` (approved). This manifest serializes plan §14 into autovap waves.
**HOW lives in skills/refs** — `wp-plugin-development/references/wbcom-wrapper-migration.md` (Parts 0–18) + `ux-foundation`. Not restated here.

## Feasibility

**Verdict: GO. Confidence 0.85.**

- Structure valid: WP Plugin Boilerplate layout, PHP present, recognizable admin/includes/public split.
- Work type supported: UX-only chrome migration with three live reference implementations on the same machine (contact-me 1.5.0, Sticky Post 2.3.7, Auto Friends 1.8.2). Build pipeline (Grunt: uglify/cssmin/rtlcss/wp-i18n) is ready for the CSS/JS/RTL work.
- Scope sane: refactor (move markup into views), not a rewrite. The field logic and persistence stay put.
- No DB schema change — the two custom tables are untouched and pinned as contracts.
- The one elevated risk (the split-scope option, below) is contained to a Wave-0 decision + a contract pin, not a blocker. Hence GO, not DEFER.

## Wave-0 inventory findings (recorded here so the build relies on them)

1. **EDD licensing is NOT active → License tab is OUT.** No `edd_*` actions, no license `register_setting`. Update delivery uses Yahnis Elsts plugin-update-checker (PUC v5), not EDD. The only artifact is a legacy option key `bp_activity_share_plugin_license_key` that the uninstaller cleans up — preserved in contracts, no UI. So the gate-6 carve-out for `plugin_updater_detected` does not apply here; PCP on the built zip should be clean of licensing detections.

2. **`bpas_icon_color_settings` is SPLIT-SCOPE in current code — this is the §12.9 sentinel case.** It is *written* with `update_site_option` (activator, line ~101) and the Settings API group `bpas_icon_color_settings`, but *read* with `get_option` in three places (admin render line ~745, post-type frontend, public class). On single-site these alias to the same row so it "works"; on multisite the read may miss the network-wide write. **The UX release must preserve current behavior byte-for-byte — do NOT silently switch read/write to a single scope.** Any scope reconciliation is a data-behavior change and ships in a separate release with a migration. The settings-display extraction (2.2) must keep the exact `get_option`/`update_site_option` calls it inherits, and the round-trip journey (`settings-roundtrip-multisite`) explicitly asserts the read-back is unchanged. Flagged to the owner as the single thing I was unsure about.

3. **`bp_share_services_serialized` is registered under the `bp_share_general_settings` group** (`sanitize_text_field`), not a standalone `option` as plan §5's "hidden field" row implies. Both the group and the key are pinned; the networks-tab extraction (2.1) must keep the hidden field inside the general-settings form so the serialize state still saves.

4. **Menu split confirmed.** `add_options_page('buddypress-share', …)` is the fallback (only when `Wbcom_Shared_Loader` is absent); every tab link + the activation redirect target `admin.php?page=wbcom-buddypress-share`. Wave 1.2 unifies to one `wbcomplugins` submenu and registers an alias so both legacy slugs resolve (journey `old-bookmark-resolves`). No `class-wbcom-integration.php` file exists in the repo grep — the `wbcom-buddypress-share` slug is registered by the shared `Wbcom_Shared_Loader`/hub; 1.2 must read where that registration happens before deciding canonical vs. alias (spine §6 open item).

5. **No `register_rest_route`** — the only REST surface is the `bp_rest_activity_prepare_value` filter (already in contracts.filters). No route contract needed.

## Contracts = the hard guarantee

`migrations: []` is intentional and matches the non-negotiable hard contract: zero renames of option keys/groups, `wp_ajax_*` actions (incl. `wss_social_icons` / `wss_social_remove_icons`), nonces, meta keys (`share_count`, `shared_activity_id`, `bp_share_user_stats`, `bp_share_activity_stats`, `_bp_share_visits`), cron hook (`bp_share_weekly_cleanup`), or `do_action`/`apply_filters` names. The contract block was seeded entirely from the grep inventory, not the docs, so the engine diffs against reality. Any breach is a HARD HALT and the owner's call — a slave must never re-plan around it.

Icons stay **Dashicons, not Lucide** (ux-foundation Rule 5 migration exception; matches the migrated siblings). The `ux-audit` gate's default "Lucide icons" assertion is a known, accepted deviation for this migration — noted so a reviewer doesn't treat it as a failure.

## Stability annotations (enterprise scale)

- **Overview stats (3.1)** is the only data-read surface. Must use `COUNT(*)`-style aggregates against `bp_share_post_tracking` (it has `idx_post_shares`, `idx_user_shares`, `idx_date_shares` — counts are indexed), cached in a transient invalidated on share write. Carries the `database` skill for this reason. No unbounded `SELECT *` over share rows.
- Services list (≤12) and post-type list (bounded by registered public types) are not scale risks.
- All async surfaces (Overview, onboarding) must handle empty/error/loading.

## Why the waves are ordered this way

Maps the 9-phase ordering onto plan §14:
- **Wave 0 (inventory)** is a read-only `audit`-journey pre-pass — the regression net + the two go/no-go decisions above. Single direction (no parallelism by design).
- **Wave 1 (shell+menu)** is the foundation: shell/tokens (1.1, CSS+view files) and menu/hub/alias (1.2, admin class + main file) have disjoint scope and run in parallel. 1.2 retains the legacy admin class for sanitizers + the two service AJAX handlers (playbook Part 3).
- **Wave 2 (view extraction)** splits one direction per view file plus one for the router method (2.6 owns the admin class alone). All scopes disjoint — five views + one class file, no overlap. This is where the round-trip and drag-drop journeys gate.
- **Wave 3 (overview+onboarding)** needs the router live, so it depends on Wave 2. 3.1 (overview view) and 3.2 (onboarding view + activator) are disjoint.
- **Wave 4 (JS)** touches only admin JS — isolated, runs after the views/handlers exist so the `[data-bpas-confirm]`-yields-to-`data-action` rule (playbook §11.1) can be verified against real buttons.
- **Wave 5 (polish/release)** four disjoint scopes: RTL/CSS (5.1), drop-CDN in includes+public (5.2), copy/privacy in views (5.3), version/docs (5.4). The frontend-regression + round-trip + bookmark journeys gate the release. `buddypress-share.php` appears in 1.2 and 5.4 but in different waves, so there is no same-wave scope overlap.

## Worktree isolation

Parallel directions within Waves 1, 2, 3, and 5 touch admin files; each implementer runs in its own worktree (engine default). Wave 2 is the densest — six directions — but each owns exactly one file, so merges are clean.

## Gate mapping (plan §12 → manifest)

- Per-direction static: `php-lint`, `wpcs` everywhere; `ux-audit` on every view/CSS/JS direction; `wiring` (= `/action-audit` + `wppqa_check_wiring_completeness` + REST/JS contract) on every direction that moves a handler, form, or button. The template-variable contract check (the `$bcm_tabs`-vs-`$tabs` blank-sidebar bug) and `wppqa_check_plugin_dev_rules` ride inside the `wpcs`/`wiring`/`qa-suite` recipes — not restated.
- Per-wave functional: `browser-smoke` (Journey coverage at 390/1280) + `qa-suite` (includes `wppqa_audit_plugin`, a11y, database, template + template-contract, enum-consistency). The builtin `contract` gate runs at every wave integration and enforces the option/hook/meta/cron pins above.
- PCP on the **built zip**, README↔header version match, and the multisite settings round-trip are covered by Wave-5 functional gates + the version-bump direction (5.4).
