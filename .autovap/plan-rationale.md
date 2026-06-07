# AutoVAP Planner Rationale — buddypress-activity-share-pro (docs-first takeover, batch 2)

## Feasibility verdict: GO — confidence 0.9

- Valid plugin structure (PHP boilerplate pattern, Loader-based hook registration), text domain `buddypress-share`, version 2.2.4.
- Work type (docs conversion) is fully supported: no source edits, read-only audit + write only under `docs/website/`, `FEATURES.json`, and audit ledgers. Zero build-pipeline dependency (Grunt is for asset minification, irrelevant to docs).
- Scope is sane and naturally phased: audit -> verify -> scaffold -> evidence.
- The docs corpus is rich (4,103 lines across 9 loose `.md` files + an `architecture/` subtree) and there is NO `docs/website/`, so batch-2 (raw convert) is exactly the right batch.

Confidence is below 1.0 only because of one large, confirmed drift (the phantom REST namespace) plus stale version/voice across guides — all handled by making wave 2 the inaccuracy-finder before any rewrite.

## Contract verification — every entry is grep-verified against EXECUTABLE PHP

Grep was restricted to `admin/ includes/ public/ buddypress-share.php` and visually confirmed to be executed code (not heredoc, not rendered admin-page text, not a code-sample string, not inside `docs/`). Counts:

- **Actions: 20** — e.g. `bp_share_post_shared` (includes/post-types/class-bp-share-post-type-tracker.php:153), `bp_share_after_create_reshare` (public/class-buddypress-share-public.php:1058), full tracker family (class-buddypress-share-tracker.php:107-340).
- **Filters: 27** — e.g. `bp_share_available_services` (admin/class-buddypress-share-admin.php:991), `bp_share_post_type_whitelist` (includes/post-types/class-bp-share-post-type-settings.php:428), `bp_share_services_config` (public/...:739), `bp_share_tracking_parameters` (public/...:785). `wbcom_submenu_label` lives in the shared-admin loader; kept because it is executable and customer-relevant for the shared dashboard.
- **Options: 11** — registered via `register_setting` (`bp_share_services_enable`, `bp_share_services_logout_enable`, `bp_share_services_extra`, `bp_share_services_serialized`, `bp_reshare_settings`, `bpas_icon_color_settings` at admin/...:846-852) plus get/update_(site_)option keys (`bp_share_services`, `bp_share_services_old`, `bp_share_plugin_version`, `bp_share_db_version`, `bp_share_install_date`). Note the site_option duplication for multisite is intentional.
- **DB tables: 2** — `{prefix}bp_share_post_tracking` and `{prefix}bp_share_post_type_settings`, both via `dbDelta` with full `CREATE TABLE` at includes/post-types/class-bp-share-post-type-tracker.php:68-102. Columns transcribed verbatim from the SQL.
- **Shortcode: 1** — `bp_activity_post_reshare`, registered at includes/class-buddypress-share.php:257 via the loader. The brackets form is `[bp_activity_post_reshare]`. (CLAUDE.md already states this correctly.)
- **AJAX: 9 actions** (5 logical handlers) — `wss_social_icons`, `wss_social_remove_icons`, `bp_activity_create_reshare_ajax`, `bp_share_get_activity_content`, `bp_get_user_share_options`, plus public/nopriv pairs `bp_share_post` and `bp_share_track_external`. AJAX is not a schema contract type, so it is documented as developer-guide content rather than a `contracts` block, but it is part of the audited surface for wave 1.

### Display-text / phantom-surface traps caught (the batch-1 lesson)

- **`bp-share/v1` REST namespace is FICTION.** `register_rest_route` returns ZERO hits anywhere in the repo (including non-code paths). `COMPLETE-DEVELOPER-GUIDE.md` lines 535-563 document five endpoints — `GET /statistics/{activity_id}`, `POST /track`, `POST /reshare`, `GET /services`, `GET /users/{user_id}/shares` — none of which exist as executable code. The ONLY real REST surface is the `bp_rest_activity_prepare_value` filter (includes/class-buddypress-share.php:260) embedding a `bp_activity_share_count` field into BuddyPress's existing activity response. The manifest therefore declares NO `rest_routes` contract; the rewrite (wave 3) must describe only the field embed, and the `dev-rest-claims-honest` journey enforces it.
- `apply_filters( 'active_plugins' )` (buddypress-share.php:71) is a READ of WordPress core's filter, not a surface this plugin provides — excluded from contracts.
- `apply_filters( 'bb_get_messages_compose_url' )` (buddyboss compat) is a BuddyBoss-owned filter being read — excluded as a provided surface.

## Top drift risks (for wave 2 to confirm; code findings -> Basecamp, never fixed here)

1. **Phantom REST API** (highest). Whole `bp-share/v1` section is aspirational. Either it was planned and cut, or copy-pasted from another plugin. Decision in wave 2: relabel as roadmap or delete; raise a Basecamp card if the endpoints were intended to ship.
2. **Stale version stamps.** Guides say "Last Updated: Version 2.0.0"; plugin is 2.2.4; the repo `CLAUDE.md` Quick Reference says 2.1.0 (its own header is stale vs the plugin file). All three disagree.
3. **Hook coverage gap.** `CLAUDE.md` advertises a "Most Used" hook subset and omits the entire tracker action family (`bp_share_internal_share_tracked`, `bp_share_external_share_tracked`, `bp_share_user_stats_updated`, etc.). The developer guide must reference the full grep-verified set, not the curated subset.
4. **AJAX endpoint table** in CLAUDE.md is close but auth labels need re-checking against the actual `nopriv` registrations (only `bp_share_post` and `bp_share_track_external` have nopriv variants).
5. **Two overlapping guide pairs** (`USER-GUIDE` vs `COMPLETE-USER-GUIDE`, `DEVELOPER-GUIDE` vs `COMPLETE-DEVELOPER-GUIDE`) — fold-in must pick one canonical source per topic and not double-document.

## Stability annotations (noted, not acted on — docs run)

- IP-address tracking in `bp_share_post_tracking` plus `bp_share_anonymize_ip` / `bp_share_disable_ip_tracking` filters: the docs must state the privacy/GDPR posture accurately. Flag-only.
- Rate limiting (`bp_share_rate_limit`, default 20/hr) and anonymous sharing (`bp_share_allow_anonymous_sharing`) are real and must be documented with correct defaults.
- CDN/minified asset filters (`bp_share_use_cdn_assets`, `bp_share_use_minified_assets`) appear in three files; document once in the developer guide.

## Why the waves are ordered this way

- **Wave 1 (audit)** establishes ground truth with file:line evidence before any claim is trusted. Read-only `audit` journey; writes only to `audit/`. No journeys gate it.
- **Wave 2 (verify)** diffs the loose guides against wave-1 truth and records fold-in decisions + Basecamp-card candidates. Per the hard rule, NO doc-accuracy journeys gate this wave — its purpose is to surface inaccuracies, so gating it on accuracy would be circular.
- **Wave 3 (scaffold)** writes `docs/website/` folding in only verified content. Doc-accuracy journeys (`owner-settings-doc-matches-ui`, `owner-post-type-sharing-doc`, `dev-hooks-match-code`, `dev-rest-claims-honest`) gate from here. Direction 3.2 depends_on 3.1 so `docs_config.json` exists before the developer-guide section is appended to it. Scopes are disjoint (getting-started/user-guide vs developer-guide).
- **Wave 4 (FEATURES.json)** is built from the wave-1/wave-2 evidence and re-gated by the hook + REST honesty journeys so no fictional feature leaks into the evidence file.

## Functional gate target

All journeys target the support docker site `http://localhost:8080` (`local_wp_site: support-reference-wp (docker)`), set in the target block. No Local WP site is used.

## Contracts I was unsure about

- `bp_share_user_services` (public:542) and `bp_activity_share_before/after_post_meta` (public:1664/1680) are real `do_action` calls but lightly documented; included as actions, flagged for wave-2 to confirm they are stable extension points vs internal.
- `bp_share_services_serialized` is registered via `register_setting` but I did not find a corresponding read; included as a contract (it is a registered option) but flagged as a possible orphaned-write candidate for a Basecamp card.
