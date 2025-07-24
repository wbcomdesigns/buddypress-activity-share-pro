# BuddyPress Activity Share Pro - User Guide

## Table of Contents
1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [Admin Settings](#admin-settings)
4. [Using Share Features](#using-share-features)
5. [Customization Options](#customization-options)
6. [Troubleshooting](#troubleshooting)
7. [FAQs](#faqs)

## Introduction

BuddyPress Activity Share Pro is a powerful plugin that adds social sharing capabilities to your BuddyPress community. Share activities on popular social networks, reshare within your community, and customize the sharing experience to match your brand.

### Key Features
- **Social Network Sharing**: Share activities to Facebook, X (Twitter), LinkedIn, WhatsApp, and more
- **Internal Resharing**: Members can reshare activities to their profile, groups, or friends
- **Customizable Design**: Multiple icon styles and color options
- **Copy Link Feature**: Quick link copying for easy sharing
- **Email Sharing**: Share activities via email with customizable templates
- **Share Counter**: Track how many times activities have been shared

## Getting Started

### Installation
1. Upload the plugin files to `/wp-content/plugins/buddypress-activity-share-pro/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to **WBcom Designs > BuddyPress Share** to configure settings

### Requirements
- WordPress 5.8 or higher
- BuddyPress 5.0+ or BuddyBoss Platform
- PHP 7.4 or higher (PHP 8.0+ compatible)

## Admin Settings

### Social Services Tab

#### Managing Social Networks
1. **Enable/Disable Services**:
   - Drag services from "Inactive Networks" to "Active Networks" to enable
   - Drag back to disable
   - Services are automatically saved when moved

2. **Available Services**:
   - Facebook
   - X (formerly Twitter)
   - LinkedIn
   - WhatsApp
   - Pinterest
   - Reddit
   - WordPress
   - Pocket
   - Telegram
   - Bluesky
   - Email
   - Copy Link

3. **Service Order**:
   - Drag active services to reorder them
   - The order determines how they appear in the share menu

### Share Style Tab

#### Icon Styles
Choose from four pre-designed icon styles:
1. **Circle**: Round icons with background colors
2. **Rectangle**: Square icons with rounded corners
3. **Black & White**: Monochrome icons
4. **Bar Icons**: Horizontal bar-style buttons

#### Custom Colors
- **Background Color**: Icon background color
- **Text Color**: Icon and label color
- **Hover Color**: Color when hovering over icons
- **Border Color**: Icon border color (if applicable)

### Restrictions Tab

Control where and how sharing works:

#### Content Restrictions
- **Blog Posts**: Enable/disable sharing for blog post activities
- **User Profiles**: Control resharing to user profiles
- **Groups**: Manage group activity sharing
- **Friends**: Control sharing to friends' timelines

#### Display Options
- **Shared Content Display**: Choose between showing parent activity or child activity
- **Logout Users**: Enable/disable sharing for logged-out visitors

### License Tab

Manage your plugin license:
1. Enter your license key
2. Click "Activate License"
3. View license status and expiration date
4. Access premium support when licensed

## Using Share Features

### For Site Members

#### Sharing an Activity
1. Find the activity you want to share
2. Click the "Share" button below the activity
3. Choose your sharing method:
   - **Social Networks**: Click any social icon to share externally
   - **Reshare**: Share within the community
   - **Copy Link**: Copy the activity link to clipboard

#### Resharing Options
When resharing, you can:
1. **Add a Message**: Include your own comment with the reshare
2. **Choose Destination**:
   - My Profile (default)
   - Specific Group (if you're a member)
   - Friend's Timeline (with mentions)
3. Click "Post" to share

#### Share Counter
- The number next to the share button shows total shares
- Includes both external shares and internal reshares
- Updates in real-time

### For Visitors (Logged Out)

If enabled by the admin:
- Can share activities to social networks
- Can copy activity links
- Cannot reshare within the community (requires login)

## Customization Options

### CSS Customization

Add custom CSS to further style the share buttons:

```css
/* Example: Change share button size */
.bp-activity-share-dropdown-toggle .button {
    padding: 8px 16px;
    font-size: 14px;
}

/* Example: Customize icon colors */
.bp-share-wrapper .bp-share {
    background-color: #your-color;
}

/* Example: Hide labels on mobile */
@media (max-width: 768px) {
    .bp-share-label {
        display: none;
    }
}
```

### Translation

The plugin is translation-ready:
1. Use translation plugins like Loco Translate
2. Find language files in `/languages/` directory
3. Text domain: `buddypress-share`

## Troubleshooting

### Common Issues

#### Share Buttons Not Appearing
1. Check if sharing is enabled in admin settings
2. Verify BuddyPress is active and updated
3. Check for theme conflicts
4. Ensure proper user permissions

#### Social Sharing Not Working
1. Check if pop-up blockers are enabled
2. Verify social services are properly configured
3. Test in different browsers
4. Check for JavaScript errors

#### Share Count Not Updating
1. Clear browser cache
2. Check AJAX functionality
3. Verify database permissions
4. Check for plugin conflicts

### Debug Mode

Enable WordPress debug mode to identify issues:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

## FAQs

### Q: Can I add custom social networks?
A: Yes, developers can use the `bp_share_available_services` filter to add custom services.

### Q: How do I style the share buttons to match my theme?
A: Use the Share Style tab for basic customization, or add custom CSS for advanced styling.

### Q: Can I disable sharing for specific activity types?
A: Yes, use the Restrictions tab to control sharing for different content types.

### Q: Is the plugin GDPR compliant?
A: Yes, the plugin doesn't collect personal data. External shares follow each platform's privacy policy.

### Q: Can I limit sharing to certain member types?
A: This requires custom development using the plugin's hooks and filters.

### Q: How do I report bugs or request features?
A: Contact support through your account at wbcomdesigns.com or submit issues on the support forum.

## Support

For additional help:
1. Check our [online documentation](https://wbcomdesigns.com/docs)
2. Visit the [support forum](https://wbcomdesigns.com/support)
3. Email: support@wbcomdesigns.com

## Updates

The plugin supports automatic updates when licensed:
1. Enter your license key in the License tab
2. Updates will appear in WordPress admin
3. Always backup before updating
4. Check changelog for new features

---

Thank you for using BuddyPress Activity Share Pro!