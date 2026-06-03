# UX Drift Audit — BuddyPress Activity Share Pro 2.3.0

**Branch:** `admin-ux-revamp-2.3.0`  
**Scope:** `public/` + `includes/post-types/` (frontend-only; admin is a separate revamp)  
**Purpose:** Build spec for (a) FA → Lucide swap, (b) ux-foundation token/component convergence  
**Status:** READ-ONLY register — no code modified  
**Date:** 2026-06-03

---

## Quick Summary

| Dimension | Count / Status |
|---|---|
| Font Awesome usages (PHP icon strings) | 39 class assignments across 3 files |
| CSS selectors targeting `[class*=" fa-"]` | 76 selector blocks in `buddypress-share-public.css` |
| Custom `as-icons` font glyphs in use | 4 (share-square, times, calendar, folder) |
| Raw hex values outside `:root` token block | 52 unique values across 2 CSS files |
| `@media` breakpoints (source CSS files) | 5 in `buddypress-share-public.css`, 3+ in `bp-share-post-type.css` — should be 2 bottom-anchored blocks each |
| `:focus-visible` gaps | 0 — focus block exists at lines 594–602; BUT no `:focus-visible` pseudo-class used (only bare `:focus`) |
| Tap targets below 40px | 3: close button (32×32), reshare-icon items (min-height 32px), dropdown items (min-height 32px) |
| Physical margin/padding (RTL-fragile) | 11 instances across 2 CSS files |
| Hover rules without `@media (hover:hover)` guard | All hover rules (18+) are unguarded |
| Dark-mode coverage | Modal only; floating widget (`bp-share-post-type.css`) uses `prefers-color-scheme: dark` instead of body-class approach |
| Inline `<script>` blocks in PHP | 2 (lines 831 and 1779 of `class-buddypress-share-public.php`) |
| CDN dependencies (FA + Bootstrap + Select2) | Confirmed — all 3 loaded from `cdnjs.cloudflare.com` |
| `prefers-reduced-motion` guard | Missing from both `buddypress-share-public.css` and `bp-share-post-type.css` |

**Bootstrap/Select2 recommendation:** Bundle Select2 locally (already needed, 62KB). Replace Bootstrap modal with a ux-foundation native modal primitive — eliminates the CDN Bootstrap JS + CSS entirely (~130KB combined), removes the BS class namespace pollution (`.modal`, `.fade`, `.show`, `.modal-dialog`, etc.) and the dual-init script problem. Estimated effort: 1 focused sprint (~3 days including the responsive/a11y work for the modal shell).

---

## Deliverable 1 — Font Awesome → Lucide Icon Map

### 1A. PHP icon class assignments

All 39 usages are string values passed into `<i class="...">` elements rendered in templates. After the swap each call site changes from an FA class string to a Lucide inline SVG helper (e.g., `bpas_icon( 'share-2' )`). Group by file:

#### `includes/class-buddypress-share-assets.php` (15 usages)

These are the fallback mapping table for when FA is present. After FA removal this method either goes away or returns Lucide SVG markup directly.

| Line | FA class | UI context | Lucide replacement | Notes |
|---|---|---|---|---|
| 163 | `fab fa-facebook` | Dashicons fallback — Facebook | **Bundled brand SVG** | Brand mark; Lucide has no `facebook`. Use inline SVG from Simple Icons or a bundled `public/images/brand/facebook.svg`. |
| 164 | `fab fa-x-twitter` | X/Twitter | **Bundled brand SVG** | `twitter-x-line.svg` already exists at `public/images/twitter-x-line.svg` — use it. |
| 165 | `fab fa-twitter` | Legacy Twitter alias | **Bundled brand SVG** | Same as above. Deduplicate `twitter` and `x` to one bundled SVG. |
| 166 | `fab fa-linkedin` | LinkedIn | **Bundled brand SVG** | No Lucide equivalent. Use a bundled `linkedin.svg`. |
| 167 | `fab fa-pinterest` | Pinterest | **Bundled brand SVG** | Use a bundled `pinterest.svg`. |
| 168 | `fab fa-reddit` | Reddit | **Bundled brand SVG** | Use a bundled `reddit.svg`. |
| 169 | `fab fa-wordpress` | WordPress | **Bundled brand SVG** | Use a bundled `wordpress.svg`. |
| 170 | `fab fa-get-pocket` | Pocket | **Bundled brand SVG** | Use a bundled `pocket.svg`. |
| 171 | `fab fa-telegram` | Telegram | **Bundled brand SVG** | `telegram-fill.svg` already exists at `public/images/telegram-fill.svg` — use it. |
| 172 | `fab fa-whatsapp` | WhatsApp | **Bundled brand SVG** | Use a bundled `whatsapp.svg`. |
| 173 | `fas fa-envelope` | Email | `mail` (Lucide) | `<svg data-lucide="mail">` or inline |
| 174 | `fas fa-envelope` | Email alias | `mail` (Lucide) | Same |
| 175 | `fas fa-link` | Copy link | `link` (Lucide) | |
| 178 | `fas fa-share` | Default fallback | `share-2` (Lucide) | |

#### `public/class-buddypress-share-public.php` (13 usages + 1 direct echo)

| Line | FA class | UI context | Lucide replacement |
|---|---|---|---|
| 638 | `fas fa-link` | Copy link button (direct echo in `bp_share_social_buttons()`) | `link` (Lucide) |
| 663 | `fab fa-facebook-f` | Share dropdown — Facebook button | **Bundled brand SVG** (`facebook.svg`) |
| 668 | `fab fa-twitter` | Share dropdown — X/Twitter | **Bundled brand SVG** (`twitter-x-line.svg` exists) |
| 673 | `fab fa-linkedin-in` | Share dropdown — LinkedIn | **Bundled brand SVG** (`linkedin.svg`) |
| 678 | `fab fa-pinterest-p` | Share dropdown — Pinterest | **Bundled brand SVG** (`pinterest.svg`) |
| 683 | `fab fa-reddit-alien` | Share dropdown — Reddit | **Bundled brand SVG** (`reddit.svg`) |
| 688 | `fab fa-wordpress` | Share dropdown — WordPress | **Bundled brand SVG** (`wordpress.svg`) |
| 693 | `fab fa-get-pocket` | Share dropdown — Pocket | **Bundled brand SVG** (`pocket.svg`) |
| 698 | `fab fa-telegram-plane` | Share dropdown — Telegram | **Bundled brand SVG** (`telegram-fill.svg` exists) |
| 703 | `fas fa-bluesky` | Share dropdown — Bluesky | **Bundled brand SVG** (`bluesky-fill.svg` exists at `public/images/bluesky-fill.svg`) |
| 708 | `fab fa-whatsapp` | Share dropdown — WhatsApp | **Bundled brand SVG** (`whatsapp.svg`) |
| 725 | `fas fa-envelope` | Share dropdown — Email | `mail` (Lucide) |

#### `includes/post-types/class-bp-share-post-type-settings.php` (13 usages — the `$default_services` icon map)

| Line | FA class | Service | Lucide / brand replacement |
|---|---|---|---|
| 44 | `fab fa-facebook-f` | Facebook | **Bundled brand SVG** |
| 49 | `fab fa-twitter` | Twitter/X | **Bundled brand SVG** (`twitter-x-line.svg`) |
| 54 | `fab fa-linkedin-in` | LinkedIn | **Bundled brand SVG** |
| 59 | `fab fa-whatsapp` | WhatsApp | **Bundled brand SVG** |
| 64 | `fab fa-telegram-plane` | Telegram | **Bundled brand SVG** (`telegram-fill.svg`) |
| 69 | `fab fa-pinterest-p` | Pinterest | **Bundled brand SVG** |
| 74 | `fab fa-reddit-alien` | Reddit | **Bundled brand SVG** |
| 79 | `fab fa-wordpress` | WordPress | **Bundled brand SVG** |
| 84 | `fab fa-get-pocket` | Pocket | **Bundled brand SVG** |
| 89 | `fas fa-bluesky` | Bluesky | **Bundled brand SVG** (`bluesky-fill.svg`) |
| 94 | `fas fa-envelope` | Email | `mail` (Lucide) |
| 99 | `fas fa-print` | Print | `printer` (Lucide) |
| 104 | `fas fa-link` | Copy Link | `link` (Lucide) |

### 1B. Custom `as-icons` font (4 glyphs in use)

The plugin ships its own iconfont at `public/icons/as-icons.*`. These 4 glyphs are actively rendered and must also migrate to Lucide (removing the `as-icons` enqueue entirely is the goal):

| `as-icon-*` class | UI context | Files | Lucide replacement |
|---|---|---|---|
| `as-icon-share-square` | Share toggle button, reshare button, post-share button | `class-buddypress-share-public.php:519,585,1433,1948` | `share-2` |
| `as-icon-times` | Reshare modal close button | `class-buddypress-share-public.php:1715` | `x` |
| `as-icon-calendar` | Post meta in reshare preview | `class-buddypress-share-public.php:1668` | `calendar` |
| `as-icon-folder` | Post category in reshare preview | `class-buddypress-share-public.php:1676` | `folder` |

Unused glyphs in the font (bookmark, share-all, share-alt-square, share-alt, share-square-light, repeat, retweet-alt, retweet) should be deleted with the font file after migration.

### 1C. CSS selectors targeting `[class*=" fa-"]` (76 selector blocks in `buddypress-share-public.css`)

These are all in the icon-style theming block (lines 1085–1393) and one BuddyBoss compat rule (line 1444). After the Lucide migration, the selector target changes from `i[class*=" fa-"]` to `.bpas-icon` (or the Lucide `<svg>` element directly). The four style families to preserve:

| Style class | Selector pattern | Lines | Action after migration |
|---|---|---|---|
| `.circle` | `i[class*=" fa-"]` — rounded background | 1085–1155 | Replace with `.bpas-icon svg { border-radius: 50%; }` pattern |
| `.rec` | `i[class*=" fa-"]` — square background | 1157–1162 | Replace with `.bpas-icon svg { border-radius: 0; }` |
| `.blackwhite` | `i[class*=" fa-"]` — mono border | 1164–1185 | Replace with brand SVG + border token |
| `.baricon` | `i[class*=" fa-"]` — brand-color top-border bar | 1190–1275 | Brand-color values stay (they are the network's official hex values — see Deliverable 2) |
| BuddyBoss compat | `.fa-link:before` override with bb-icons glyph | 1444–1449 | Drop entirely after Lucide migration; provide `link` Lucide icon |

### 1D. Brand mark decision

For the 10 social network brand icons (Facebook, X, LinkedIn, Pinterest, Reddit, WordPress, Pocket, Telegram, Bluesky, WhatsApp), **use bundled brand SVGs — not Lucide**. Lucide is a general-purpose outline icon set and carries no brand mark icons. Bluesky, Telegram, and Twitter-X SVGs already exist in `public/images/`. The remaining 7 (Facebook, LinkedIn, Pinterest, Reddit, WordPress, Pocket, WhatsApp) need to be added to `public/images/brand/` from the respective brand's official media kit. Bundle them as inline-renderable SVGs via a `bpas_brand_svg( 'facebook' )` PHP helper.

---

## Deliverable 2 — Raw-Hex → Token Map

The `--bp-share-*` token system is already well-structured in `public/css/buddypress-share-public.css` lines 10–53. The problem is `bp-share-post-type.css` does not consume those tokens — it hardcodes raw hex throughout. Additionally, the baricon/circle/rec style block in `buddypress-share-public.css` uses brand-color hex values for network icons that belong in named tokens.

### 2A. Network brand colors (baricon style block — `buddypress-share-public.css` lines 1212–1392)

These are the official brand palette hex values. They are **not** duplicates of any `--bp-share-*` token (they are network-identity colors, not theme-adaptive values). Map them to `--bpas-brand-*` tokens:

| Raw hex | Service | Proposed token | Notes |
|---|---|---|---|
| `#696f75` | Reshare / Copy (neutral) | `--bpas-brand-neutral` | Same value used in circle, blackwhite, baricon, rec styles |
| `#3B5998` | Facebook | `--bpas-brand-facebook` | Official Facebook blue |
| `#1DA1F2` | X/Twitter | `--bpas-brand-x` | Official X blue (note: X is migrating to `#000000` — flag for future update) |
| `#AD0000` | Email | `--bpas-brand-email` | Dark red |
| `#46bd00` | WhatsApp | `--bpas-brand-whatsapp` | Official WhatsApp green |
| `#007BB6` | LinkedIn | `--bpas-brand-linkedin` | Official LinkedIn blue |
| `#BD081C` | Pinterest | `--bpas-brand-pinterest` | Official Pinterest red |
| `#FF4501` | Reddit | `--bpas-brand-reddit` | Official Reddit orange-red |
| `#21759B` | WordPress | `--bpas-brand-wordpress` | WordPress blue |
| `#EF3E56` | Pocket | `--bpas-brand-pocket` | Pocket red |
| `#0088cc` | Telegram | `--bpas-brand-telegram` | Telegram blue (`telegram-fill.svg` uses same) |
| `#1185fe` | Bluesky | `--bpas-brand-bluesky` | Official Bluesky blue (`bluesky-fill.svg` uses same) |
| `#000` / `#000000` | Blackwhite style border | `--bpas-brand-blackwhite-fg` | |

These tokens belong in a `:root` block in `buddypress-share-public.css` (brand colors never change per theme). They are **not** duplicates of any admin token.

### 2B. UI structural colors in `bp-share-post-type.css` — remap to existing `--bp-share-*` tokens

All raw hex in `bp-share-post-type.css` is duplicating Tailwind gray-scale values already defined as `--bp-share-gray-*` tokens in `buddypress-share-public.css`. The fix is to import or re-declare those tokens in `bp-share-post-type.css` and replace all hardcoded values:

| Raw hex | Token it maps to | Lines in `bp-share-post-type.css` |
|---|---|---|
| `#ffffff` | `var(--bp-share-white)` | 46, 132, 299 |
| `#e5e7eb` | `var(--bp-share-gray-200)` | 47, 133, 476, 490, 578 |
| `#c7cbd1` | `var(--bp-share-gray-300)` (close) | 64 — add `--bp-share-gray-250` or round to 300 |
| `#6b7280` | `var(--bp-share-gray-500)` | 100, 579 |
| `#4b5563` | `var(--bp-share-gray-600)` | 106, 151, 481 |
| `#f3f4f6` | `var(--bp-share-gray-100)` | 107, 187, 196, 485 |
| `#f9fafb` | `var(--bp-share-gray-50)` | 166, 475 |
| `#1f2937` | `var(--bp-share-gray-800)` | 177, 251, 277, 363 (as `#333`), 486 |
| `#374151` | `var(--bp-share-gray-700)` | 477, 491, 495 |
| `#f5f5f5` | `var(--bp-share-gray-50)` | 355, 384, 393 |
| `#333` | `var(--bp-share-gray-800)` | 363, 385, 395 |
| `#666` | `var(--bp-share-gray-500)` | 369 |
| `#667eea` | `var(--bp-share-primary)` — NOTE: `#667eea` ≠ `#2563eb` (primary). This is a **conflicting blue** | 241, 243, 404, 457 — needs decision: standardize to `--bp-share-primary` or introduce `--bpas-brand-copy` |
| `#5a67d8` | `var(--bp-share-primary-hover)` — same conflict as above | 409, 451, 463, 465 |
| `#000` | `var(--bp-share-black)` | 445, 446 |
| `#e5e5e5` | `var(--bp-share-gray-200)` | 393 |
| Service brand hex | See §2A tokens above | 231–243 |

**Conflict flag:** `#667eea`/`#5a67d8` in `bp-share-post-type.css` are a distinct purple-blue used for default button backgrounds and the Email/Copy service icon. They do not match the `--bp-share-primary` blue (`#2563eb`/`#1d4ed8`). Before tokenizing, decide: unify to `--bp-share-primary`, or create `--bpas-inline-btn-bg: #667eea` as a separate token. The current mismatch makes the floating widget visually inconsistent with the activity share dropdown.

### 2C. Dark mode in `bp-share-post-type.css` — `prefers-color-scheme` vs. body-class

`bp-share-post-type.css` lines 472–501 use `@media (prefers-color-scheme: dark)`. The plugin's main CSS uses a body-class approach (`body.dark-mode`, `body[data-theme="dark"]`). The standard per ux-foundation is **body class** (explicitly toggled by the theme), not `prefers-color-scheme`. The `bp-share-post-type.css` block must be refactored to match:

```css
/* Replace @media (prefers-color-scheme: dark) { ... } with: */
body.dark-mode .bp-share-floating-wrapper,
body[data-theme="dark"] .bp-share-floating-wrapper,
body.dark .bp-share-floating-wrapper {
    /* overrides */
}
```

Additionally, the dark override in `bp-share-post-type.css` is **inverted**: it keeps the widget in light colors even in dark mode (`background: #f9fafb` etc.). This appears intentional ("Light approach" comment) but contradicts the dark-mode contract. Confirm the desired behavior before migrating.

---

## Deliverable 3 — A11y / Responsive Drift

### 3A. `:focus-visible` gaps

The focus block at `buddypress-share-public.css:594–602` uses the bare `:focus` pseudo-class, not `:focus-visible`. This means keyboard-focus rings fire on mouse click too (or in some browsers don't fire at all when the element receives programmatic focus). Upgrade all 6 selectors in that block to `:focus-visible`.

Missing `:focus-visible` entirely: the floating widget toggle (`.bp-share-toggle`), every `.bp-share-service` link, and all `.bp-share-button` elements in `bp-share-post-type.css` have no focus ring at all. Add:

```css
.bp-share-toggle:focus-visible,
.bp-share-service:focus-visible,
.bp-share-button:focus-visible {
    outline: 2px solid var(--bp-share-primary);
    outline-offset: 2px;
}
```

The `as-icon` close button (`<button class="close activity-share-modal-close">`) has `aria-label="Close"` which is correct, but the focus ring depends on `buddypress-share-public.css:594` which uses bare `:focus`. Upgrade to `:focus-visible`.

### 3B. Sub-40px tap targets

| Element | Current size | File:line | Fix |
|---|---|---|---|
| `.close` / `.activity-share-modal-close` | 32×32px | `buddypress-share-public.css:159–160` | Increase to 40×40px (or 44×44px per mobile heuristic) |
| `.bp-activity-share-dropdown-menu .bp-activity-share-btn` / `.bp-share-wrapper` | `min-height: 32px` | `buddypress-share-public.css:745` | Increase to `min-height: 40px` |
| Avatar image in modal | 35×35px on mobile | `buddypress-share-public.css:672` (inside `@media (max-width: 480px)`) | Avatar is not interactive — acceptable |

### 3C. `@media` block count and placement

Per ux-foundation each CSS source file should have exactly two `@media` blocks, both bottom-anchored, at `max-width: 1024px` and `max-width: 640px`.

**`buddypress-share-public.css` has 5 distinct `@media` blocks** scattered through the file:
- Line 612: `@media (max-width: 768px)` — non-standard breakpoint (should be 640px)
- Line 655: `@media (max-width: 480px)` — extra breakpoint (should collapse into 640px block)
- Line 684: `@media screen and (max-width: 782px)` — admin bar compat, keeps as exception
- Line 900: `@media screen and (max-width: 750px)` — non-standard breakpoint for mobile sheet (should be 640px)
- Line 986: `@media screen and (max-width: 300px)` — micro breakpoint; remove or collapse

**`bp-share-post-type.css` has 3 `@media` blocks**:
- Line 284: `@media (max-width: 768px)` — non-standard
- Line 472: `@media (prefers-color-scheme: dark)` — functional, should be body-class (see §2C)
- Line 588: `@media print` — valid utility

Consolidation path: standardize the two responsive breakpoints to `640px` (mobile) and `1024px` (tablet) in both files, collapse the `480px`/`480px`/`300px` blocks into the `640px` block.

### 3D. Hover without `@media (hover:hover)` guard

All hover rules throughout both CSS files are bare `:hover` pseudo-classes with no `@media (hover: hover) and (pointer: fine)` guard. On touch devices these get "stuck" after a tap (the hover state persists). Affected rule groups:

- `buddypress-share-public.css`: 18+ `:hover` rules — close button, dropdown items, share wrapper items, dropdown toggle, all baricon/blackwhite icon hover states
- `bp-share-post-type.css`: 8+ `:hover` rules — toggle hover, service hover, button hover

All transform-based hover effects (`transform: translateY(-1px)`, `transform: translateY(-2px)`, `transform: scale(1.05)`, `transform: scale(1.1)`) particularly need the guard since they cause visual glitches on touch.

Pattern to apply:
```css
@media (hover: hover) and (pointer: fine) {
    .bp-activity-share-dropdown-toggle a.dropdown-toggle:hover { ... }
    .bp-activity-share-close:hover { ... }
    /* etc. */
}
```

### 3E. RTL — physical margin/padding properties

Both files use physical `margin-left`, `margin-right`, `padding-left`, `padding-right` instead of logical `margin-inline-start/end` and `padding-inline-start/end`. RTL companion files exist (`css-rtl/`) and are served via `wp_style_add_data( ..., 'rtl', 'replace' )` in `bp-share-helpers.php` — the RTL file generation (grunt-rtlcss) covers these automatically. However, any properties that are intentionally asymmetric (not mirrored in RTL) need manual review:

| Property | File:line | Logical replacement | RTL intent |
|---|---|---|---|
| `margin-left: 8px` (loading spinner) | `buddypress-share-public.css:488` | `margin-inline-start: 8px` | Mirror |
| `margin-right: 8px` (button text) | `buddypress-share-public.css:500` | `margin-inline-end: 8px` | Mirror |
| `margin-right: 10px` (loading indicator) | `buddypress-share-public.css:524` | `margin-inline-end: 10px` | Mirror |
| `margin-left: 5px` (reshare count) | `buddypress-share-public.css:847` | `margin-inline-start: 5px` | Mirror |
| `margin-left: 0 !important` (mobile label) | `buddypress-share-public.css:938` | `margin-inline-start: 0` | Mirror |
| `margin-left: 10px` (share label) | `buddypress-share-public.css:1049` | `margin-inline-start: 10px` | Mirror |
| `padding-right: 0` (modal-open body) | `buddypress-share-public.css:1416` | `padding-inline-end: 0` | Mirror |
| `margin-left: 8px` (service name) | `bp-share-post-type.css:227` | `margin-inline-start: 8px` | Mirror |
| `padding-left: 0` / `padding-right: 0` (mobile) | `bp-share-post-type.css:287–288` | `padding-inline: 0` | Mirror |

Since grunt-rtlcss already handles the RTL build, converting to logical properties is a clean-up but not strictly required for RTL correctness. It eliminates reliance on the RTL CSS file for these properties and future-proofs new components.

### 3F. Dark mode gaps

The reshare modal (`activity-share-modal`) has comprehensive dark mode token support (lines 56–73) covering all `--bp-share-bg`, `--bp-share-text`, `--bp-share-border` tokens. However:

1. The **floating widget** (`bp-share-post-type.css`) uses an inverted `prefers-color-scheme: dark` block that keeps the widget in light mode even when the theme says dark — likely unintentional. See §2C.
2. The **share dropdown** (`.bp-activity-share-dropdown-menu`) has no explicit dark-mode block. It consumes `--bp-share-bg` and `--bp-share-border` tokens, so it will adapt if the body class adds the token overrides — but only if the token block in `buddypress-share-public.css:56–73` is extended to cover the dropdown's parent, not just `.activity-share-modal`.
3. The **baricon/blackwhite brand-color blocks** (lines 1183–1392) hardcode `#000` and `#fff` without tokens. In dark mode `.blackwhite` style will still render black text on unknown backgrounds.

Fix: broaden the dark mode token override selector from `.activity-share-modal` to a shared wrapper `.bp-share-app` (or `body`) that covers all plugin surfaces.

### 3G. Missing `prefers-reduced-motion` guard

Both CSS files contain animations and transitions with no `prefers-reduced-motion: reduce` block:

- `buddypress-share-public.css`: `@keyframes bp-share-spin` (loading spinner — 0.8s / 1s infinite), `transform: scale(1.05)` on close button hover, `transform: translateY(-1px)` on buttons
- `bp-share-post-type.css`: `@keyframes bp-share-pulse` (4s infinite), `@keyframes bp-share-bounce` (0.6s), `@keyframes bp-share-slide-in-right/left` (0.8s), `@keyframes bp-share-loading` (0.8s infinite), multiple `transition: all 0.3s/0.4s/0.5s` rules

Add to both files:
```css
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
```

---

## Deliverable 4 — CDN Dependencies

### Confirmed CDN enqueues (`public/class-buddypress-share-public.php:62–68`)

| Library | Handle | CDN URL | Version |
|---|---|---|---|
| Font Awesome | `bp-share-fontawesome` / `bp-share-font-awesome` | `cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css` | 5.15.4 |
| Bootstrap CSS | `bp-share-bootstrap` | `cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css` | 4.6.2 |
| Bootstrap JS | `bp-share-bootstrap` | `cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js` | 4.6.2 |
| Select2 CSS | `bp-share-select2` | `cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css` | 4.1.0-rc.0 |
| Select2 JS | `bp-share-select2` | `cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js` | 4.1.0-rc.0 |

Note: a BuddyBoss branch at line 121–127 also hard-enqueues Font Awesome CDN unconditionally when the BuddyBoss theme is active, bypassing the conflict check.

### Recommendation — Bootstrap (reshare modal)

**Replace with a ux-foundation native modal.** Bootstrap is loaded solely to power the single `#activity-share-modal` (class `modal fade show`). The modal markup (`role="dialog"`, `aria-modal`, `aria-labelledby`, focus-trap, ESC key close, backdrop click) can be implemented natively in ~120 lines of CSS + 60 lines of JS using the pattern from ux-foundation's modal primitive. This eliminates:
- Bootstrap CSS: ~155KB (minified)
- Bootstrap JS bundle: ~77KB (minified)
- Class namespace pollution across ~40 CSS rules in `buddypress-share-public.css` (`modal-dialog`, `modal-content`, `modal-header`, `modal-footer`, `modal-body`, `fade`, `show`, `btn`, `btn-secondary`, `btn-primary`)
- The dual-initialization bug (Bootstrap is initialized twice — once at line 856–863 of `buddypress-share-public.js` and again in the inline `<script>` at line 1779–1822 of `class-buddypress-share-public.php`)

**Estimated effort:** 3 working days including the new modal component CSS, JS, PHP template refactor, Bootstrap class cleanup, and regression testing at 5 viewports.

### Recommendation — Select2 (group/friend picker)

**Bundle locally.** Select2 (v4.1.0-rc.0) is required for the group/friend picker in the reshare modal — a genuine usability improvement over a native `<select>` when users have many groups. Unlike Bootstrap, removing it requires a full UI replacement.

Action: copy `select2.min.css` and `select2.min.js` to `public/vendor/select2/` and update the enqueue handles to local paths. Combined file size: ~62KB. Remove the `rc.0` suffix by upgrading to the final 4.1.0 release. The `@phpcs:ignore` comment on the CDN URL (line 67) confirms this is a known debt.

---

## Deliverable 5 — Usability Findings

### Context

The plugin's share UI has three distinct surfaces:
1. **Activity share dropdown** — appears on each BuddyPress activity item; opens a fly-out list of reshare + social share options
2. **Reshare modal** (`#activity-share-modal`) — Bootstrap modal for posting a reshare to a group/profile/friend, with a text area and destination picker
3. **Post-type floating/inline widget** — for singular posts/CPTs (separate `bp-share-post-type.js/css`)

### Findings

**Finding 1 — Share and Reshare are exposed as peers but mean entirely different things**

The dropdown mixes two conceptually different actions on the same list: "Reshare to my BuddyPress feed" (creates a new activity post) and "Share to Facebook/Twitter/etc." (opens an external window). Both appear as line items inside the same fly-out. A user who clicks "Reshare" expecting a Twitter share gets a modal with a text editor. A user looking for Facebook gets confused that it's mixed with the Reshare option. This is a core discoverability failure — the two actions need visual separation (a divider with section labels: "Share on BuddyPress" and "Share externally") or should be split into two separate controls.

**Finding 2 — The "Post" button in the reshare modal uses a hardcoded English label without loading state reset**

`bpShareButtonLoading()` at line 543 of `buddypress-share-public.js` replaces the button text with the hardcoded string `'Sharing...'` and `bpShareButtonReset()` resets it to `'Post'`. Neither string is translatable (they bypass `wp_localize_script` and the `wp-i18n` package despite `wp-i18n` being listed as a dependency). The button also loses its loading spinner markup on reset — the spinner (`<span class="loading-spinner">`) stays permanently hidden after a successful share.

**Finding 3 — The "Discard" / "Post" footer buttons duplicate Bootstrap's `btn btn-secondary` / `btn btn-primary` classes plus the plugin's own `bp-activity-share-close` / `bp-activity-share-activity` classes, creating a specificity war**

Both buttons carry both class sets (e.g., `class="btn btn-secondary bp-activity-share-close"`). The plugin CSS overrides Bootstrap via `!important` throughout. After Bootstrap removal, the `btn btn-secondary` / `btn btn-primary` classes become dead and the `!important` overrides become vacuous. This is cleanup debt, but it also means the buttons' visual state is fragile — a theme that also loads Bootstrap will fight with the plugin.

**Finding 4 — The "Post in" destination picker is a `<select>` that renders with an absolutely-positioned floating `<label>` that overlaps the Select2 rendered input**

`buddypress-share-public.css:248–266` uses an `absolute`-positioned label with `top: -8px` to create a "floating label" effect. Select2 replaces the native `<select>` with its own DOM at `shown.bs.modal`, which renders `aria-hidden="true"` on the original `<select>`. The floating label's `for="post-in"` association breaks because Select2 uses a different ID for its visible input. Screen readers will not announce the label when the Select2 widget receives focus.

**Finding 5 — The mobile sheet drawer (bottom-positioned dropdown at ≤750px) has no dismiss affordance**

On mobile the dropdown converts to a bottom drawer (`position: fixed; bottom: 0`). The drawer closes on outside click (`handleOutsideClick`) but there is no visible close button or swipe-down handle. On small screens the drawer may cover 80vh of content with no keyboard-accessible way to close it without clicking the original toggle button (which may have scrolled off screen). ESC key handling exists in the JS but the user has no visual cue that ESC closes it.

**Finding 6 — The floating post-type widget has no accessible name on the toggle button**

`class-bp-share-post-type-frontend.php:74–87` renders `.bp-share-toggle` as a `<div>`, not a `<button>`. It contains an inline SVG (decorative, no `aria-label`) and an optional share count `<span>`. A `<div>` is not keyboard-reachable by default and is not announced as interactive by screen readers. It needs to be a `<button type="button" aria-label="Share this post">` with `aria-expanded` bound to the open/closed state.

---

## Findings Register (ranked)

| # | Severity | Category | Finding | File:line | Follow-up journey |
|---|---|---|---|---|---|
| 1 | **Blocker** | F2 inline-script | Inline `<script>` block in PHP for modal/Select2 init | `class-buddypress-share-public.php:1779–1823` | `fix` — move to `buddypress-share-public.js` |
| 2 | **Blocker** | F2 inline-script | Inline `<script>` block in PHP for popup activation | `class-buddypress-share-public.php:831–839` | `fix` — move to `buddypress-share-public.js` via `wp_localize_script` |
| 3 | **Blocker** | Icons | 39 FA icon class strings + 76 CSS selectors targeting `[class*=" fa-"]` — FA removal makes all social icons invisible | `class-buddypress-share-public.php`, `class-buddypress-share-assets.php`, `class-bp-share-post-type-settings.php`, `buddypress-share-public.css` | `improve` — implement Lucide + brand SVG swap per Deliverable 1 |
| 4 | **Blocker** | Icons | 4 `as-icons` font glyphs still loaded from `public/icons/` — must migrate before removing FA enqueue | `public/css/as-icons.css`, `class-buddypress-share-public.php:519,585,1433,1715,1668,1676,1948` | `improve` — replace with Lucide per Deliverable 1B |
| 5 | **Blocker** | CDN | Bootstrap 4.6.2 loaded from CDN — dual-initialization bug + class pollution | `class-buddypress-share-public.php:62–68`, `buddypress-share-public.js:857–863` | `improve` — replace modal with ux-foundation native |
| 6 | **Major** | Hex / tokens | `bp-share-post-type.css` has 50+ raw hex values, none using `--bp-share-*` tokens | `public/css/bp-share-post-type.css` (throughout) | `improve` — token migration per Deliverable 2B |
| 7 | **Major** | Hex / tokens | `#667eea`/`#5a67d8` conflict with `--bp-share-primary` (`#2563eb`) in post-type CSS | `public/css/bp-share-post-type.css:241,243,404,409,457,451,463,465` | `fix` — decide unified primary; update tokens |
| 8 | **Major** | A11y | `.bp-share-toggle` is a `<div>` — not keyboard reachable, no `aria-label`, no `aria-expanded` | `class-bp-share-post-type-frontend.php:74–87` | `fix` — change to `<button>` |
| 9 | **Major** | A11y | `<select id="post-in">` label association broken by Select2 replacement | `class-buddypress-share-public.php:1734–1748`, `buddypress-share-public.css:248–266` | `fix` — use `aria-labelledby` on Select2 container |
| 10 | **Major** | A11y | No `prefers-reduced-motion` block in either CSS file — 4 infinite keyframe animations | `buddypress-share-public.css`, `bp-share-post-type.css` | `fix` — add guard block |
| 11 | **Major** | Breakpoints | 5 non-standard breakpoints in `buddypress-share-public.css` (768/480/782/750/300px) — should be 2 bottom-anchored blocks | `buddypress-share-public.css:612,655,684,900,986` | `improve` — consolidate to 640/1024 |
| 12 | **Major** | Dark mode | `bp-share-post-type.css` uses `prefers-color-scheme: dark` instead of body-class dark mode — inconsistent with modal and ux-foundation contract | `bp-share-post-type.css:472–501` | `fix` — migrate to body-class approach |
| 13 | **Major** | CDN | Select2 CDN enqueue — use stable 4.1.0 release, bundle locally | `class-buddypress-share-public.php:141–148,198–204` | `improve` — bundle to `public/vendor/select2/` |
| 14 | **Major** | Usability | Share and Reshare peers in same dropdown with no visual separation | `class-buddypress-share-public.php:526–541` | `improve` — add divider + section labels |
| 15 | **Major** | Usability | Mobile bottom drawer has no visible dismiss control and no keyboard affordance | `buddypress-share-public.css:900–984`, `buddypress-share-public.js:128–133` | `fix` — add close handle and ESC visual hint |
| 16 | **Minor** | Tap target | Close button (`32×32px`) below 40px minimum | `buddypress-share-public.css:159–160` | `fix` — increase to 40px |
| 17 | **Minor** | Tap target | Dropdown item `min-height: 32px` below 40px minimum | `buddypress-share-public.css:745` | `fix` — increase to 40px |
| 18 | **Minor** | Focus | `:focus` used instead of `:focus-visible` in modal focus block | `buddypress-share-public.css:594–602` | `fix` — upgrade to `:focus-visible` |
| 19 | **Minor** | Focus | Floating widget `.bp-share-toggle` and `.bp-share-service` have no focus ring | `bp-share-post-type.css` (no focus rules) | `fix` — add `:focus-visible` ring |
| 20 | **Minor** | Hover | 18+ `:hover` rules with no `@media (hover:hover)` guard — stick on touch | `buddypress-share-public.css`, `bp-share-post-type.css` | `improve` — wrap in hover media query |
| 21 | **Minor** | RTL | 9 physical `margin-left/right` / `padding-left/right` instances | `buddypress-share-public.css:488,500,524,847,938,1049,1416`, `bp-share-post-type.css:227,287,288` | `improve` — convert to logical properties |
| 22 | **Minor** | Usability | "Post" / "Sharing..." button text not translatable (`wp-i18n` listed as dep but strings are hardcoded) | `buddypress-share-public.js:544–550` | `fix` — use `__()` from `wp.i18n` via localized vars |
| 23 | **Polish** | Hex | 13 brand-color hex values in baricon/circle block not yet tokenized | `buddypress-share-public.css:1212–1392` | `improve` — add `--bpas-brand-*` tokens per Deliverable 2A |
| 24 | **Polish** | Usability | Reshare modal shows "Status Update" as the sub-label for current user — not meaningful context for a reshare action | `class-buddypress-share-public.php:1728` | `improve` — change to "Sharing to…" or similar |
| 25 | **Polish** | Icons | BuddyBoss compat block overrides `fa-link:before` with a `bb-icons` glyph via `content: "\eec8"` | `buddypress-share-public.css:1444–1449` | Remove after Lucide migration (no longer needed) |

---

## Scores

| Lens | Grade | Notes |
|---|---|---|
| Icons (FA → Lucide) | F | 39 PHP + 76 CSS FA usages; 4 custom font glyphs; all social networks need brand SVGs |
| Raw hex / tokens | D | Modal CSS is well-tokenized. Post-type CSS has 50+ raw hex; 13 brand colors untokenized; #667eea conflict |
| A11y / responsive | D | Focus rings use bare `:focus`; toggle is a `<div>`; 5 non-standard breakpoints; no reduced-motion guard; 3 sub-40px targets |
| CDN dependencies | D | Bootstrap + Select2 + FA all CDN; Bootstrap is removable; Select2 needs local bundle |
| Usability | C | Share/reshare mixing is the core UX problem; mobile sheet lacks dismiss; label/Select2 a11y gap |

---

*This document is a read-only build spec. No code was modified during this audit.*
