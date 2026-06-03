# Changelog

All notable changes to BuddyPress Activity Share Pro will be documented in this file.

## [2.3.0] - 2026-06-03

### Added
- Modern card-based admin settings screen with a left-hand sidebar menu, unified under the shared WB Plugins hub (single submenu, clean `admin.php?page=buddypress-share` slug).
- First-run onboarding: a dismissible welcome screen with three quick-start steps, shown once on a fresh activation and never again. Existing installs are auto-marked complete on upgrade and never see it.
- Overview page with cached `COUNT(*)` sharing stats (total / today), a current-setup summary, and quick actions.
- Scoped admin design tokens (`--bpas-admin-*`), generic toast + accessible confirm modal (`window.bpasToast` / `window.bpasConfirm`), and a generic `[data-bpas-confirm]` handler that yields to elements owning their own `data-action`.

### Changed
- Each settings tab body moved into its own view under `admin/views/`; the legacy tab methods are now thin includes. RTL stylesheet rebuilt; a11y pass (`:focus-visible` rings, 40px tap targets, ARIA on nav + decorative icons). Plain-language copy throughout the admin.

### Removed
- The legacy Wbcom admin wrapper (shared loader hub registration, wrapper CSS/JS, integration class). The admin now lives natively under the WB Plugins hub.
- Dead admin auto-save JS path that had no server handler.

### Preserved (no data migration)
- All option keys, storage scopes, AJAX action names, nonces, meta keys, cron hooks, and `do_action`/`apply_filters` names are unchanged. Frontend share dropdown, reshare modal, and post-type buttons are untouched.

## [2.1.0] - 2025-08-01

### Added

#### Post Type Sharing Feature
- **New Floating Share Widget**: Sticky floating share buttons for all post types
  - Positioned at left or right middle of screen
  - Smooth cubic-bezier animations for professional feel
  - Light color scheme with subtle shadows
  - Auto-hide on scroll for better UX
  - Mobile-responsive with bottom bar option

#### Admin Interface
- **New Post Type Sharing Tab**: Configure sharing for different post types
  - Enable/disable sharing per post type
  - Configure services for each post type individually
  - Set default services for new post types
  - Position settings (left/right)
  - Mobile behavior options (bottom bar/hidden/same as desktop)

#### Smart Post Type Filtering
- **Intelligent Post Type Detection**: Automatically filters internal post types
  - Excludes page builder templates (Elementor, Divi, Beaver Builder, etc.)
  - Excludes form plugin types (Contact Form 7, WPForms, etc.)
  - Excludes WordPress internal types (blocks, templates, revisions)
  - Excludes e-commerce internals (orders, coupons)
  - Always includes core types (posts, pages)
  - Whitelists community types (bbPress forums/topics/replies)

#### Developer Features
- **Comprehensive Filters**: Full customization support
  - `bp_share_enable_post_type_sharing` - Enable/disable feature
  - `bp_share_available_post_types` - Customize available post types
  - `bp_share_post_type_whitelist` - Add to whitelist
  - `bp_share_post_type_is_valid` - Override validation logic
  - `bp_share_post_type_available_services` - Customize services

#### Database
- **New Tracking Table**: `{prefix}bp_share_post_shares`
  - Tracks shares by post type, post ID, and service
  - Stores user ID and timestamp
  - Optimized indexes for performance

### Improved
- **Performance**: Singleton pattern for all new classes
- **Security**: Nonce verification and capability checks
- **Code Organization**: Modular architecture with separate classes
- **Animations**: Professional cubic-bezier easing throughout
- **Accessibility**: ARIA labels and keyboard navigation support

### Technical Details

#### New Files Added
- `includes/post-types/class-bp-share-post-type-controller.php`
- `includes/post-types/class-bp-share-post-type-settings.php`
- `includes/post-types/class-bp-share-post-type-frontend.php`
- `includes/post-types/class-bp-share-post-type-tracker.php`
- `includes/post-types/class-bp-share-post-type-database.php`
- `admin/partials/bp-share-post-type-settings.php`
- `public/css/bp-share-post-type.css`
- `public/js/bp-share-post-type.js`
- `docs/ROADMAP-POST-TYPE-SHARING.md`
- `docs/POST-TYPE-FILTERING.md`

#### CSS Features
- Light color scheme with professional aesthetics
- Smooth transitions using cubic-bezier(0.4, 0, 0.2, 1)
- Responsive design with mobile-first approach
- Dark mode support with light theme preference
- Print styles to hide share widgets

#### JavaScript Features
- AJAX share tracking without page reload
- Clipboard API with fallback support
- Intersection Observer for impression tracking
- Smooth scroll behavior handling
- Outside click detection for closing

### Dependencies
- WordPress 5.0+
- Font Awesome 5+ (for social icons)
- jQuery (WordPress bundled)

## [2.0.0] - Previous Release

### Previous Features
- BuddyPress activity sharing
- Basic social services support
- Activity stream integration
- Share count tracking