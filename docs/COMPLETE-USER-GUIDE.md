# BuddyPress Activity Share Pro - Complete User Guide

## 📚 Table of Contents

1. [Introduction](#introduction)
2. [Installation & Activation](#installation--activation)
3. [Initial Setup](#initial-setup)
4. [Social Platforms Configuration](#social-platforms-configuration)
5. [Internal Resharing Features](#internal-resharing-features)
6. [Customization Options](#customization-options)
7. [Activity Type Controls](#activity-type-controls)
8. [User Permissions](#user-permissions)
9. [Analytics & Tracking](#analytics--tracking)
10. [Gamification Integration](#gamification-integration)
11. [Troubleshooting](#troubleshooting)
12. [FAQ](#faq)

---

## Introduction

BuddyPress Activity Share Pro is a premium social sharing plugin exclusively available with **Reign Theme** and **BuddyX Pro**. It transforms your BuddyPress community by enabling comprehensive sharing capabilities both externally (social media) and internally (within your community).

### Key Benefits
- 🚀 Increase community engagement
- 📈 Track sharing analytics
- 🎮 Gamify user interactions
- 🌐 Expand social reach
- 💡 Boost content visibility

---

## Installation & Activation

### Prerequisites
- WordPress 5.0 or higher
- PHP 7.4 or higher
- BuddyPress 8.0+ OR BuddyBoss Platform
- Reign Theme OR BuddyX Pro theme

### Installation Steps

1. **Via WordPress Admin:**
   - Navigate to `Plugins > Add New`
   - Click `Upload Plugin`
   - Select the `buddypress-activity-share-pro.zip` file
   - Click `Install Now`
   - Activate the plugin

2. **Via FTP:**
   - Extract `buddypress-activity-share-pro.zip`
   - Upload to `/wp-content/plugins/` directory
   - Navigate to `Plugins` in WordPress admin
   - Activate the plugin

### Post-Activation
After activation, you'll be automatically redirected to the plugin settings page.

---

## Initial Setup

### Quick Start Wizard

1. **Navigate to Settings:**
   - Go to `WordPress Admin > Settings > BP Activity Share Pro`

2. **Enable Social Sharing:**
   - Toggle "Enable Social Share" to ON
   - Toggle "Show in Logout Mode" if you want non-logged users to see share buttons

3. **Select Default Platforms:**
   - Check the platforms you want to enable by default
   - Recommended: Facebook, Twitter/X, LinkedIn, WhatsApp, Email

4. **Save Settings:**
   - Click "Save Changes"
   - Share buttons will now appear on all activities

---

## Social Platforms Configuration

### Available Platforms

#### Primary Social Networks
- **Facebook** - Share to Facebook timeline or pages
- **Twitter/X** - Post to Twitter with customizable text
- **LinkedIn** - Share to professional network
- **WhatsApp** - Direct share via WhatsApp (mobile optimized)

#### Messaging Platforms
- **Telegram** - Share via Telegram
- **Email** - Send via email client
- **Copy Link** - Copy activity URL to clipboard

#### Emerging Platforms
- **Bluesky** - Share to Bluesky social
- **Pinterest** - Pin activity images
- **Reddit** - Share to subreddits

### Platform Configuration

#### Enable/Disable Platforms
1. Go to `Settings > BP Activity Share Pro > Services`
2. Check/uncheck platforms
3. Drag to reorder (order reflects on frontend)
4. Save changes

#### Custom Share Messages
```
Default format: {title} - {url}
Custom format: Check out this post: {title} via @YourSite {url} #community
```

#### Platform-Specific Settings

**Facebook:**
- Requires Open Graph meta tags (automatically added)
- Supports custom images and descriptions
- Works with Facebook debugger

**Twitter/X:**
- Character limit: 280
- Supports hashtags and mentions
- URL automatically shortened if needed

**LinkedIn:**
- Professional formatting preserved
- Supports rich media previews
- Company page sharing available

**WhatsApp:**
- Mobile-first experience
- Pre-filled message format
- Works with WhatsApp Web

---

## Internal Resharing Features

### Share to Profile
Allows users to reshare activities to their own profile/timeline.

**How to use:**
1. Click share button on any activity
2. Select "Share to Profile"
3. Add optional comment
4. Click "Share"

**Features:**
- Original author attribution maintained
- Nested reshare support (reshare of reshares)
- Comment addition capability
- Privacy settings respected

### Share to Groups
Share activities to specific BuddyPress groups.

**How to use:**
1. Click share button
2. Select "Share to Group"
3. Choose target group(s)
4. Add group-specific message
5. Share

**Features:**
- Multi-group selection
- Group privacy respected
- Admin approval if required
- Group notification support

### Share via Messages
Send activities through private messages.

**How to use:**
1. Click share button
2. Select "Share via Message"
3. Select recipients
4. Add personal message
5. Send

**Features:**
- Multiple recipient support
- Message threading maintained
- Read receipts (if enabled)
- Attachment support

---

## Customization Options

### Visual Styling

#### Icon Styles
1. **Icon Sets:**
   - Font Awesome 5.15.4
   - Custom AS-Icons
   - Dashicons fallback

2. **Color Schemes:**
   - Default theme colors
   - Custom color picker
   - Dark mode auto-detection
   - Individual platform colors

3. **Button Styles:**
   - Icon only
   - Icon + text
   - Text only
   - Custom CSS classes

#### Layout Options
```css
/* Horizontal Layout (default) */
.bp-share-buttons { display: flex; gap: 10px; }

/* Vertical Layout */
.bp-share-buttons.vertical { flex-direction: column; }

/* Grid Layout */
.bp-share-buttons.grid { display: grid; grid-template-columns: repeat(5, 1fr); }
```

### Position Controls

1. **Activity Feed Position:**
   - Before activity content
   - After activity content
   - In activity meta section
   - Floating action button

2. **Single Activity Position:**
   - Top of activity
   - Bottom of activity
   - Sidebar widget
   - Sticky position

### Mobile Optimization

**Responsive Features:**
- Touch-optimized buttons
- Swipe gestures support
- Bottom sheet on mobile
- Native app detection

**Mobile-Specific Settings:**
```php
// Enable mobile-only platforms
add_filter('bp_share_mobile_platforms', function($platforms) {
    $platforms[] = 'whatsapp';
    $platforms[] = 'telegram';
    return $platforms;
});
```

---

## Activity Type Controls

### Supported Activity Types

1. **Default Types:**
   - Status updates
   - Blog posts
   - Comments
   - Group updates
   - Friendships

2. **Media Types:**
   - Photos
   - Videos
   - Audio
   - Documents
   - Links

3. **Custom Types:**
   - Custom post types
   - Third-party activities
   - Plugin-specific content

### Restriction Settings

#### Global Restrictions
```php
// Disable for specific activity types
Settings > BP Activity Share Pro > Restrictions

☐ Status Updates
☑ Blog Posts
☑ Photos
☐ Private Activities
```

#### Conditional Display
```php
// Show only for certain conditions
add_filter('bp_share_display_conditions', function($show, $activity) {
    // Only show for activities with 5+ comments
    if ($activity->comment_count < 5) {
        return false;
    }
    return $show;
}, 10, 2);
```

---

## User Permissions

### Role-Based Permissions

#### Administrator Settings
- Full access to all features
- Can modify global settings
- Access to analytics dashboard
- Can restrict other roles

#### Member Capabilities
```php
// Settings > BP Activity Share Pro > Permissions

☑ Can share own activities
☑ Can share others' activities
☑ Can reshare to profile
☑ Can share to groups
☐ Can view share analytics
```

### Privacy Controls

1. **Activity Privacy:**
   - Public activities: Full sharing
   - Private activities: No external sharing
   - Friends-only: Limited sharing
   - Group activities: Group members only

2. **User Privacy Settings:**
   - Allow/block reshares
   - Hide share counts
   - Disable tracking
   - Opt-out options

---

## Analytics & Tracking

### Share Tracking Dashboard

**Access:** `WordPress Admin > BP Activity Share > Analytics`

#### Metrics Available

1. **Overall Statistics:**
   - Total shares (internal + external)
   - Share rate (shares/views)
   - Most shared activities
   - Top sharing users

2. **Platform Breakdown:**
   ```
   Facebook:     45% (450 shares)
   Twitter:      25% (250 shares)
   WhatsApp:     15% (150 shares)
   Internal:     10% (100 shares)
   Others:       5%  (50 shares)
   ```

3. **Time-Based Analytics:**
   - Hourly distribution
   - Daily trends
   - Weekly patterns
   - Monthly growth

### UTM Parameters

All external shares automatically include:
```
utm_source=buddypress_share
utm_medium=social
utm_campaign=activity_share
utm_content=activity_{id}
bps_aid={activity_id}
bps_uid={user_id}
bps_time={timestamp}
```

### Google Analytics Integration

1. **Setup:**
   - Add GA tracking code
   - Enable enhanced ecommerce
   - Create custom dimensions

2. **Track Events:**
   ```javascript
   gtag('event', 'share', {
     'event_category': 'social',
     'event_label': 'facebook',
     'value': activity_id
   });
   ```

### Export Data

**Available Formats:**
- CSV export
- JSON export
- PDF reports
- API access

---

## Gamification Integration

### myCRED Integration

#### Setup Points for Sharing
1. Install and activate myCRED
2. Go to `myCRED > Hooks`
3. Enable "BuddyPress Activity Share"
4. Configure point values:
   ```
   Share own activity:      5 points
   Share others' activity:  2 points
   Get reshared:           10 points
   Daily limit:            50 points
   ```

### GamiPress Integration

#### Create Achievements
1. Install GamiPress
2. Create new achievement
3. Add requirements:
   - Share 10 activities
   - Get reshared 5 times
   - Share to 3 different platforms

#### Example Achievement Setup
```
Achievement: Social Butterfly
Requirements:
✓ Share 10 activities (any platform)
✓ Use 5 different social platforms
✓ Get 20 reshares on your content
Reward: Special badge + 100 bonus points
```

### Custom Gamification

```php
// Award custom points
add_action('bp_share_user_reshared_activity', function($user_id, $type) {
    // Your custom point system
    update_user_meta($user_id, 'share_points', 
        get_user_meta($user_id, 'share_points', true) + 10
    );
    
    // Check for milestones
    $total_points = get_user_meta($user_id, 'share_points', true);
    if ($total_points >= 100) {
        // Award achievement
        do_action('user_earned_achievement', $user_id, 'super_sharer');
    }
}, 10, 2);
```

---

## Troubleshooting

### Common Issues & Solutions

#### Share Buttons Not Showing

**Possible Causes:**
1. Plugin not activated
2. BuddyPress not active
3. Theme compatibility issue
4. JavaScript conflict

**Solutions:**
```php
// Check if plugin is active
if (function_exists('bp_share_init')) {
    echo "Plugin is active";
}

// Force display buttons
add_filter('bp_share_force_display', '__return_true');
```

#### Incorrect Share Counts

**Reset share counts:**
1. Go to `Tools > BP Share Tools`
2. Click "Reset Share Counts"
3. Confirm action

#### Social Platform Issues

**Facebook sharing not working:**
- Clear Facebook cache: https://developers.facebook.com/tools/debug/
- Check Open Graph tags
- Verify SSL certificate

**Twitter character limit:**
- Shorten activity text
- Use URL shortener
- Reduce hashtags

#### Performance Issues

**Optimize performance:**
```php
// Enable caching
define('BP_SHARE_CACHE_TIME', 3600); // 1 hour

// Limit platforms
add_filter('bp_share_max_platforms', function() {
    return 5;
});
```

### Debug Mode

Enable debug mode for detailed logging:
```php
// In wp-config.php
define('BP_SHARE_DEBUG', true);
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check logs at: `/wp-content/debug.log`

---

## FAQ

### General Questions

**Q: Is this plugin available for individual purchase?**
A: No, BuddyPress Activity Share Pro is exclusively bundled with Reign Theme and BuddyX Pro.

**Q: Can I use it with any BuddyPress theme?**
A: Yes, while optimized for Reign and BuddyX Pro, it works with any BuddyPress-compatible theme.

**Q: Does it work with BuddyBoss Platform?**
A: Yes, full compatibility with BuddyBoss Platform.

### Feature Questions

**Q: Can I add custom social platforms?**
A: Yes, use the `bp_share_services` filter to add custom platforms.

**Q: Is share tracking GDPR compliant?**
A: Yes, with configurable privacy options and user consent features.

**Q: Can I limit sharing to certain user roles?**
A: Yes, through the Permissions settings panel.

### Technical Questions

**Q: What are the minimum requirements?**
- PHP 7.4+
- WordPress 5.0+
- BuddyPress 8.0+ or BuddyBoss Platform

**Q: Is it multisite compatible?**
A: Yes, with network-wide or per-site activation options.

**Q: Does it support RTL languages?**
A: Yes, full RTL support with dedicated stylesheets.

---

## Support & Resources

### Getting Help

1. **Documentation:** https://wbcomdesigns.com/docs/
2. **Support Forum:** https://wbcomdesigns.com/support/
3. **Email Support:** support@wbcomdesigns.com
4. **Video Tutorials:** YouTube Channel

### Useful Links

- [Reign Theme](https://wbcomdesigns.com/downloads/reign-buddypress-theme/)
- [BuddyX Pro](https://wbcomdesigns.com/downloads/buddyx-pro/)
- [Developer Documentation](./DEVELOPER-GUIDE.md)
- [Changelog](../CHANGELOG.md)

### Stay Updated

- Follow us on Twitter: @wbcomdesigns
- Join our Facebook Group
- Subscribe to our newsletter

---

*Last Updated: Version 2.0.0*
*© 2024 Wbcom Designs - All Rights Reserved*