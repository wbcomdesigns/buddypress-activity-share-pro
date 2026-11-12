# QA Handoff - Activity Share Free + Pro 3.6.0

The same file ships in both repos. Test the `3.6.0` branch of **both** plugins together (Free `bp-activity-social-share`, Pro `buddypress-activity-share-pro`), not a release zip. Everything below is unreleased.

## Setup

- WordPress 6.9+, PHP 8.1-8.5, BuddyPress (Nouveau). One pass on BuddyBoss Platform.
- Theme: Reign 8.0.x (light and dark; dark mode is the theme switch, never the OS setting). Spot-check BuddyX.
- No SEO plugin for the Open Graph checks (Yoast, Rank Math, AIOSEO or SEOPress make our tags stand down on purpose).
- Optional: WP Job Manager Resumes, BuddyPress Business Profile, BP Anonymous Activities.
- Roles: site admin, member, guest. Viewports: 1440 and 390.
- Short links changed format in this build: copy links fresh from the share menu. Old `/s/xxx-12345678/` links now 404.

## Cards in Ready for Testing

### Free

| Card | What to test |
|---|---|
| 10422019761, 10340631781 - share title | Anonymous activity shares "Anonymous User posted an update" on X, email and WhatsApp. A name with `&` shows as `&`, not `&amp;`. An empty action shares "Shared update". |
| 10502406986 - menu labels wrap | At 1024 every share label sits on one line; the panel grows to fit. At 390 the sheet layout wraps as before. |
| 10474816133, 10474815813 - upgrade keeps old behaviour | Upgrade from 3.5.4: Pinterest stays on if it showed before, stays off if the owner had removed it. Guest sharing stays off unless the old "logged-out sharing" option was on. Fresh installs use the new defaults. |
| 10346002375 - BuddyBoss activation | On BuddyBoss Platform, Free and Pro both activate (no "Requires: BuddyPress" block). Activity share menus and the group header share work. |
| NEW - Reign dark mode colours | Reign 8.0.x and 8.1, dark: share menu, repost / send / reposters dialogs and the text box use Reign's own card colours (`#1a2028` surface, `#c5c8cd` text, `#262e38` border), not slate blue. Light mode and BuddyX (light and dark) unchanged. 1440 and 390. |
| 10340717169 - group / profile preview image | Group with avatar + cover: `og:image` is the cover, with `og:image:width/height/type/alt`. Group with a 150px avatar only: no `og:image`, `twitter:card=summary`. Member with no upload: no theme or BuddyPress placeholder. Member with an uploaded avatar of 200px+: the avatar. |

### Pro

| Card | What to test |
|---|---|
| 10394811429 - activation redirect | Deactivate Pro, activate it from Plugins: lands on Activity Share settings, Features tab. Same for Free (no tab). With BuddyPress / BuddyBoss off (or Youzify on), activating Free stays on Plugins and shows the "needs BuddyPress" notice instead of a "not allowed" page. |
| Version notice (audit) | Free and Pro on different versions: the notice names the plugin that is behind ("update Activity Share Pro to X" or "update Activity Share for BuddyPress to X"). |
| 10502493644 - resume share hidden (Reign 8.0.x) | Content Types > Resume = Above. On a single resume the Share button sits on the banner and opens; 1440 and 390, light and dark. |
| 8821740106 - dialog on unrelated pages | View source: no `bpas-pro-dialog` on `/members/`, `/groups/`, profile tabs, group members. Present on `/activity/`, member activity, single activity, group home, and posts with Pro rows; Send to a friend opens it on each. |
| 10336203968 - short links | Share links look like `/s/22-character-code/`; no member ID can be read from link or cookie. Guest visit: 302 to the item, `bpas_ref` cookie (HttpOnly, SameSite=Lax). Tampered code: home page. Log in: cookie cleared. Sign up after a visit: rewards hook fires for the sharer. Copy link and More options copy the short link. |
| 10336203532 - image card | Text-only update shared: 1200x630 card with author avatar, name, excerpt, site icon/logo, site name. Long URLs wrap. Edit the update: the old card file is removed. `add_filter( 'bpas_pro_image_card', '__return_false' )` turns it off. |
| 10502521980 - Business single page | Content Types > Business = Floating (or Above/Below): button shows on single business pages. Never on BuddyPress profiles, groups, directories or single activity. |
| 10395058273 - block / shortcode vs placement | Content Types tab text now says a post with the Share block or `[bpas_share]` shows the menu only there and skips the placement for that post. Behaviour unchanged (by design). |
| 10502418277 - dialog textarea in dark | By design: muted border at rest, accent border while focused (the dialog focuses the field on open). See the card for measurements. |

## Already on the branch, re-verify only

10487989569 (Bootstrap), 10336127563 and 10340189083 (dependency slug), 10336770267 (BuddyBoss group repost), 10327750794 (display tab, bottom clip), 10422019761 / 10340631781 / 10502406986 / 10346002375 (Free rows above). All re-checked 2026-11-13; evidence on each card.

BuddyBoss note for the test site: BuddyBoss Platform does not load while bbPress is active (it ships its own forums). Deactivate bbPress first, or every BuddyPress-dependent plugin looks broken.

## Not in this release

- 10497698141 (per-type style / mobile override) and 10340830835 (send to several friends): Not now, owner decision.
- Image card: joined scripts (Arabic, Indic) draw unshaped letters (GD limit, documented). Colours come from the `bpas_pro_card_colors` filter, not the theme.
- Reign 8.1 beta renders resumes without `the_content`, so Above/Below placement shows nothing there. That is a Reign issue, reported on card 10502493644.

## Regression areas

- Activity stream, member and group headers: share menu, repost, repost with comment, send to a friend, reposters list.
- Content types: post, page, product, any public type; block and `[bpas_share]` shortcode stand down the automatic menu.
- Settings: Networks, Display, Content Types, Features; save and reload.
- `bin/local-ci.sh` passes in both repos.
