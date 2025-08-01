# Changelog

All notable changes to BuddyPress Activity Share Pro will be documented in this file.

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