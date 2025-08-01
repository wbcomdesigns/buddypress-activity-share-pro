# BuddyPress Activity Share Pro - Post Type Sharing Extension Roadmap

## Overview
Extend the sharing functionality beyond BuddyPress activities to support all WordPress post types (posts, pages, custom post types) with a floating sticky sharing wrapper.

## How to Enable (Development Mode)

To enable the post type sharing feature in development:

```php
// Add to your theme's functions.php or a custom plugin
add_filter( 'bp_share_enable_post_type_sharing', '__return_true' );
```

Once enabled, you'll see a new "Post Type Sharing" tab in the plugin admin interface.

## Feature Specification

### 1. Floating Share Widget
- **Position**: Sticky right-side floating wrapper
- **Behavior**: 
  - Follows user scroll
  - Collapsible/expandable
  - Mobile-responsive (bottom bar on mobile)
- **Design**: 
  - Minimal when collapsed (just icons)
  - Expanded view shows service names
  - Share count display option

### 2. Post Type Support Configuration

#### Admin Settings Structure
```
Post Type Sharing Settings
├── Enable/Disable per Post Type
│   ├── Posts (disabled by default)
│   ├── Pages (disabled by default)
│   ├── Products (WooCommerce)
│   ├── Courses (LearnDash)
│   └── Custom Post Types
├── Service Selection per Post Type
│   └── For each enabled post type:
│       ├── Facebook
│       ├── Twitter/X
│       ├── LinkedIn
│       ├── WhatsApp
│       ├── Telegram
│       ├── Email
│       ├── Copy Link
│       └── Print
└── Global Settings
    ├── Default Services (pre-selected)
    ├── Position (left/right)
    ├── Style (floating/inline)
    └── Mobile behavior
```

### 3. Implementation Architecture

#### Database Schema
```sql
-- New table for post type sharing settings
CREATE TABLE {prefix}bp_share_post_type_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_type VARCHAR(50),
    enabled_services TEXT,
    position VARCHAR(20),
    style VARCHAR(20),
    custom_settings TEXT,
    created_at DATETIME,
    updated_at DATETIME
);

-- Tracking table for post shares
CREATE TABLE {prefix}bp_share_post_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    post_type VARCHAR(50),
    service VARCHAR(50),
    user_id INT,
    ip_address VARCHAR(45),
    shared_at DATETIME,
    INDEX idx_post_shares (post_id, service)
);
```

#### Core Classes Structure
```php
// Main controller
class BP_Share_Post_Type_Controller {
    - register_post_type_support()
    - render_floating_wrapper()
    - handle_ajax_share()
    - get_share_counts()
}

// Settings manager
class BP_Share_Post_Type_Settings {
    - get_enabled_post_types()
    - get_services_for_post_type()
    - save_settings()
    - get_default_services()
}

// Frontend renderer
class BP_Share_Post_Type_Frontend {
    - render_sticky_wrapper()
    - render_inline_buttons()
    - enqueue_assets()
    - handle_responsive()
}
```

### 4. Admin Interface Mockup

```
┌─────────────────────────────────────────────────────────┐
│ Post Type Sharing Settings                              │
├─────────────────────────────────────────────────────────┤
│ Enable Sharing for Post Types:                          │
│                                                         │
│ [x] Posts          Configure Services ▼                 │
│     ├─ [x] Facebook                                     │
│     ├─ [x] Twitter/X                                    │
│     ├─ [x] Copy Link                                    │
│     └─ [ ] WhatsApp                                     │
│                                                         │
│ [ ] Pages          Configure Services ▼                 │
│                                                         │
│ [x] Products       Configure Services ▼                 │
│     ├─ [x] Facebook                                     │
│     ├─ [x] Pinterest                                    │
│     ├─ [x] WhatsApp                                     │
│     └─ [x] Copy Link                                    │
│                                                         │
│ Display Settings:                                       │
│ Position: [Right ▼]  Style: [Floating ▼]               │
│                                                         │
│ Default Services (when enabling new post type):         │
│ [x] Facebook [x] Twitter [x] Copy Link [ ] Email       │
└─────────────────────────────────────────────────────────┘
```

### 5. Frontend Implementation

#### Floating Wrapper HTML Structure
```html
<div class="bp-share-floating-wrapper" data-post-id="123" data-post-type="post">
    <div class="bp-share-toggle">
        <span class="bp-share-icon">↗</span>
        <span class="bp-share-count">42</span>
    </div>
    <div class="bp-share-services">
        <a href="#" class="bp-share-service" data-service="facebook">
            <i class="fab fa-facebook"></i>
            <span class="service-name">Facebook</span>
        </a>
        <a href="#" class="bp-share-service" data-service="twitter">
            <i class="fab fa-twitter"></i>
            <span class="service-name">Twitter</span>
        </a>
        <a href="#" class="bp-share-service" data-service="copy">
            <i class="fas fa-link"></i>
            <span class="service-name">Copy Link</span>
        </a>
    </div>
</div>
```

#### CSS Styling
```css
.bp-share-floating-wrapper {
    position: fixed;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 9999;
    transition: all 0.3s ease;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .bp-share-floating-wrapper {
        position: fixed;
        bottom: 0;
        right: 0;
        left: 0;
        top: auto;
        transform: none;
        flex-direction: row;
    }
}
```

### 6. JavaScript Functionality

```javascript
class BPSharePostType {
    constructor() {
        this.wrapper = document.querySelector('.bp-share-floating-wrapper');
        this.initEvents();
        this.trackVisibility();
    }
    
    initEvents() {
        // Toggle expand/collapse
        // Handle share clicks
        // Copy link functionality
        // Track share events
    }
    
    sharePost(service, postId, postType) {
        // Generate share URL
        // Track share event
        // Open share window
    }
    
    trackVisibility() {
        // Intersection Observer for analytics
    }
}
```

### 7. Hooks and Filters

```php
// Enable/disable for specific post type
apply_filters('bp_share_post_type_enabled', $enabled, $post_type);

// Modify services for post type
apply_filters('bp_share_post_type_services', $services, $post_type);

// Customize wrapper position
apply_filters('bp_share_wrapper_position', $position, $post_id);

// After share action
do_action('bp_share_post_shared', $post_id, $service, $user_id);

// Modify share URL
apply_filters('bp_share_post_url', $url, $post_id, $service);
```

### 8. Development Phases

#### Phase 1: Core Infrastructure (Week 1-2)
- [ ] Database tables creation
- [ ] Settings API implementation
- [ ] Admin interface for post type configuration
- [ ] Basic service management

#### Phase 2: Frontend Implementation (Week 3-4)
- [ ] Floating wrapper HTML/CSS
- [ ] JavaScript share functionality
- [ ] Responsive design
- [ ] Copy link feature

#### Phase 3: Tracking & Analytics (Week 5)
- [ ] Share tracking implementation
- [ ] Analytics dashboard
- [ ] Export functionality
- [ ] Performance optimization

#### Phase 4: Advanced Features (Week 6-7)
- [ ] Custom styling options
- [ ] A/B testing support
- [ ] Share message customization
- [ ] Open Graph optimization

#### Phase 5: Integration & Testing (Week 8)
- [ ] WooCommerce product sharing
- [ ] LearnDash course sharing
- [ ] Custom post type testing
- [ ] Performance testing

### 9. Configuration Examples

#### Enable for WooCommerce Products
```php
add_filter('bp_share_post_type_enabled', function($enabled, $post_type) {
    if ($post_type === 'product') {
        return true;
    }
    return $enabled;
}, 10, 2);

// Custom services for products
add_filter('bp_share_post_type_services', function($services, $post_type) {
    if ($post_type === 'product') {
        // Add Pinterest for products
        $services['pinterest'] = true;
        // Remove LinkedIn
        unset($services['linkedin']);
    }
    return $services;
}, 10, 2);
```

#### Custom Positioning
```php
// Left side for RTL sites
add_filter('bp_share_wrapper_position', function($position) {
    if (is_rtl()) {
        return 'left';
    }
    return $position;
});
```

### 10. Migration Path

For existing users:
1. Current activity sharing remains unchanged
2. Post type sharing is opt-in (disabled by default)
3. Settings migration tool for bulk configuration
4. Backward compatibility maintained

### 11. Performance Considerations

- Lazy load share counts
- Cache share URLs
- Debounce scroll events
- Minimize DOM operations
- Async loading of service icons

### 12. Security Features

- Nonce verification for AJAX calls
- Rate limiting for share tracking
- XSS prevention in share messages
- CSRF protection
- IP-based spam prevention

### 13. Future Enhancements

- QR code sharing
- Native app share sheets
- Custom share services
- Scheduled sharing
- Share analytics API
- Integration with social media APIs for share counts

## Implementation Priority

1. **High Priority**
   - Basic post/page support
   - Core social services (Facebook, Twitter, Copy)
   - Floating wrapper UI
   - Mobile responsiveness

2. **Medium Priority**
   - Custom post type support
   - Advanced positioning options
   - Share tracking
   - Service customization per post type

3. **Low Priority**
   - A/B testing
   - Advanced analytics
   - Custom styling UI
   - Third-party integrations

## Success Metrics

- Page load performance impact < 50ms
- Share interaction rate > 5%
- Mobile usage > 40%
- Zero breaking changes for existing features
- 90% browser compatibility (IE11+)