# Activity Share Pro

Paid add-on (`buddypress-activity-share-pro`, main file `buddypress-share.php` - never rename). **Extends the free plugin Activity Share for BuddyPress** (`bp-activity-social-share`); Free and Pro always ship the **same version**. EDD item **1634903**.

Plans: `docs/plans/3.6.0-PLAN.md` (synced copy; canonical in the free repo) and `docs/plans/3.6.0-IMPLEMENTATION-PRO.md`.

## Rules
- Follow `/wp-plugin-development` and `ux-foundation`.
- **Pro uses Free only through `bp-activity-social-share/includes/functions.php` and hooks** (`bpas_share_menu_items`, `bpas_share_toggle_after`, `bpas_admin_tabs`, `bpas_share_context`, `bpas_share_url`, `bpas_can_share_externally`). Never reference `BPAS\*` classes - `bin/local-ci.sh` fails on it. Need something new from Free? Add a function or hook to Free.
- Pro ships no share buttons, networks, icons, tokens, OG, autoloader or settings for Free keys. Its classes load through Free's autoloader (`bpas_register_autoload( 'BPAS_Pro', ... )`).
- Every repost / send rule lives in `Reshare\Reshare_Service` (UI and REST both call it). Rules are fixed, not settings (plan 12.3).
- Settings: `bpas_pro_settings` via `BPAS_Pro\Settings` - Features toggles + content types only.
- One DB table: `{prefix}bpas_pro_events`, touched only by `Tracking\Events`. No IP addresses stored anywhere.
- Upgrades on `init` (`BPAS_Pro\Upgrade`), never the activation hook. Bump `BPAS_PRO_DB_VERSION` with each migration.
- Licensing: `includes/licensing.php` + vendored `libs/edd-sl-sdk` (1.0.3 with the Wbcom template-whitelist guard in `Handler.php`, plus a plugin-level guard on the overlay AJAX). Licence gates updates only, never features.
- Text domain `buddypress-activity-share-pro`; strings in JS are passed via inline data.

## Layout
| Path | Role |
|---|---|
| `buddypress-share.php` | Header, constants, compatibility checks (Free missing / older / newer, Youzify), boot |
| `includes/class-plugin.php` | Module wiring |
| `includes/reshare/` | Activity types, service, menu rows, composer dialog, reposted card, reply share, counts, member setting |
| `includes/posts/` | Share on any post type, block, `[bpas_share]` |
| `includes/tracking/`, `includes/analytics/` | Events table, tracker, analytics, trending block + widget |
| `includes/links/` | Short links `/s/{code}-{sig}` |
| `includes/rewards/` | WB Gamification, GamiPress, myCRED adapters |
| `includes/cards/` | Share image card (GD + bundled DejaVu Sans) |
| `includes/groups/` | Group admin sharing controls |
| `includes/notifications/` | Grouped notification + email situation `bpas-repost` |
| `includes/privacy/`, `includes/jobs/` | Export / erase, retention cleanup |
| `includes/api/` | REST `bpas/v1`: reshare, send, me/groups, me/friends, shares, reposters, pro-settings, analytics, track |
| `includes/admin/` | Features, Content types, Analytics, License tabs on Free's page |

## Commands
- `npm run build` - assets (`.min`, `-rtl`)
- `bin/local-ci.sh` - lint, WPCS, no-Free-internals, versions (incl. Free pair), SDK guard, plan sync, i18n
- `bin/build-release.sh` - clean zip in `dist/`

## Basecamp
Project 37939219 "Activity Share (Free + Pro)" - the ONE board for both plugins since 2026-09-25 (the old Free project is archived). Card table 7507533844. Columns: Triage 7507533845, Not now 7507533846, Suggestions 7507533860, Possible Bugs 10340598697 (QA intake), Bugs 7507533850, Ready for Development 7507533853, Code Improvement 7808139615, In Development 7507533851, Ready for Testing 7507533854, Scope 9099749151, In Testing 9099749199, Done 7507533852.
