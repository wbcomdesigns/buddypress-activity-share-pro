=== Activity Share Pro ===
Contributors: wbcomdesigns, vapvarun
Tags: buddypress, share, repost, social share, analytics
Requires at least: 6.7
Tested up to: 7.1
Requires PHP: 8.1
Requires Plugins: bp-activity-social-share
Stable tag: 3.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Reposts, send to a friend, share buttons on any content, counts, trending posts, rewards and analytics for BuddyPress.

== Description ==

Activity Share Pro adds everything that keeps sharing inside your community to the free Activity Share for BuddyPress plugin.

* **Repost:** one tap reposts an update to your profile (with Undo), or repost with a comment to your profile or a group. @mentions work in the comment.
* **Send to a friend:** send any post to a friend as a private message.
* **Share replies:** replies get their own share menu and can be reposted, with a clear note if the reply is later deleted.
* **Share buttons on any content:** posts, pages, products, courses, events and other content types, above or below the content or as a floating button. Also a Share buttons block and the [bpas_share] shortcode.
* **Repost blog posts into the stream** as a quoted card.
* **Counts and who reposted** next to the Share button.
* **Most shared block and widget** for the last 7 or 30 days.
* **Share image for text posts:** a branded image is used when a text-only post is shared to Facebook, LinkedIn or WhatsApp.
* **Short share links** like yoursite.com/s/abc123 that credit the member who shared.
* **Share rewards** with WB Gamification, GamiPress or myCRED: points when a shared post brings a visitor or a new member.
* **Group controls:** group admins decide whether posts from their public group can be shared outside or reposted elsewhere.
* **Notifications:** "Anna and 5 others reposted your update", on the site and by email (members can switch the email off).
* **Share analytics:** shares by network, reposts, sends and visits, most shared content, 7 / 30 / 90 days, CSV export. Visitors are counted without storing IP addresses.
* **Privacy tools:** share data is included in WordPress's personal data export and erase tools.

== Installation ==

1. Install and activate Activity Share for BuddyPress (free).
2. Upload and activate Activity Share Pro.
3. Go to WB Plugins > Activity Share > Features and License.

== Changelog ==

= 3.6.0 - September 2026 =

Rebuilt as an add-on to the free Activity Share for BuddyPress: faster, safer and with many new ways to share.

* New      - Quick repost with Undo, and Repost with comment to your profile or a group.
* New      - Send to a friend as a private message.
* New      - Share and repost replies.
* New      - Share buttons on any content type, a Share buttons block and the [bpas_share] shortcode.
* New      - Repost blog posts and other content into the activity stream.
* New      - Share counts and a list of who reposted.
* New      - Most shared block and widget.
* New      - Share image for text-only posts.
* New      - Short share links that credit the member who shared.
* New      - Share rewards with WB Gamification, GamiPress and myCRED.
* New      - Group admins control outside sharing and reposting for their group.
* New      - Grouped repost notifications and an email members can switch off.
* New      - Privacy export and erase support for share data.
* New      - REST API for reposting, sending, share counts and analytics.
* Improve  - Analytics now work, with date ranges, pagination and CSV export, and stay fast on large sites.
* Improve  - The repost dialog works with the keyboard and screen readers and looks right on phones.
* Improve  - Bootstrap, Select2 and Font Awesome are no longer loaded.
* Fix      - Activating Pro while the free plugin is active no longer causes an error.
* Fix      - Posts from private and hidden groups can no longer be viewed or reposted by non-members.
* Fix      - Drafts and private posts can no longer be reposted.
* Fix      - Share tracking tables are created on update, without reactivating the plugin.
* Fix      - Right-to-left sites now get the correct styles.
* Fix      - Uninstall now removes all plugin data.
* Security - Visitor IP addresses are no longer stored.
* Dev      - Old settings, counts and share records move to the new format automatically; old entries are removed.
* Compat   - Requires Activity Share for BuddyPress 3.6.0, WordPress 6.7 and PHP 8.1. Updates now use your Wbcom Designs license.

= 2.3.1 - September 2026 =

* Fix      - Group members can now preview and reshare activity from their own private and hidden groups.
* Security - Members can no longer view activity from private or hidden groups they do not belong to through the share preview.

= 2.3.0 - June 2026 =

Admin redesign, a refreshed sharing experience, dark mode, and important stability fixes.

* New      - Refreshed the admin settings screen with a cleaner card-based layout and a left-hand menu, so everything is easier to find.
* New      - Added a quick setup guide that appears the first time you activate the plugin to help you get started fast. It only shows once, and existing sites never see it.
* New      - Added an Overview page with at-a-glance sharing stats and quick links to the most-used settings.
* New      - Reshare notifications let members know when someone reshares their activity.
* New      - Reshare analytics show how often content is reshared across your community.
* New      - Added controls to limit who can reshare by role, cap how often a member can reshare, and add campaign tags to reshared links.
* New      - Activated several previously inactive settings: share count display, prevent self-sharing, respect activity privacy, share-button border color, and friends-only resharing.
* Improve  - Redesigned the activity share menu with compact rows, larger icons, a clear divider between resharing and social networks, and visible keyboard focus.
* Improve  - Refreshed the sharing icons for a cleaner, more consistent look across the activity stream.
* Improve  - Added dark mode support and alignment with the latest BuddyX and Reign theme styles.
* Improve  - Share button colors set on the Display tab now also apply to the buttons in the activity stream.
* Improve  - Bundled the supporting script libraries with the plugin so the sharing UI loads without any external request.
* Improve  - Better accessibility and right-to-left language support across the sharing UI.
* Improve  - All your existing settings, networks, colors, and restrictions are kept exactly as they were.
* Fix      - Resolved a fatal error that could occur when an activity was reshared repeatedly in a chain.
* Fix      - The share popup now opens correctly for activities that load as you scroll the stream.
* Fix      - Resolved a PHP 8 error in the activity content filter.
* Fix      - Fixed an invisible label on the Overview primary button caused by matching text and background colors.

= 2.2.4 - June 2026 =
* Fix      - Re-share modal no longer appears unintentionally on pages across the site outside activity streams.
* Fix      - Prevented iOS Safari from auto-zooming when focusing form fields in the share modal.

= 2.2.3 =
* Fix: Resolved unnecessary rendering of re-share modal on shortcode pages.
* Fix: CSS conflict with notification dropdown items.

= 2.2.2 =
* Fix: PHP Warning — attempt to read property post_content on null when activity has no associated post
* Fix: Resolved PHPCS coding standards violations

= 2.2.1 =
* Fixed: Include source CSS/JS files in release ZIP for GPL compliance

= 2.2.0 =
* Fixed: Reshare activity display options not working correctly
* Fixed: Activity read more issue for reshared activities
* Fixed: Share icons not showing with BuddyBoss theme
* Fixed: Post types services FontAwesome icon not displaying
* Fixed: Bootstrap CSS conflict with themes
* Fixed: Elementor compatibility issue
* Fixed: Post archive page share display issue
* Improved: Icon style and color management

= 2.1.0 =
* Added: Post Type Sharing functionality - share buttons can now be added to any WordPress post type
* Added: Flexible service configuration per post type with customizable social networks
* Added: Smart post type detection - automatically excludes internal/system post types
* Added: Display position and style options for post type share buttons
* Added: Mobile-specific behavior settings for better responsive experience
* Added: New settings page for managing post type sharing options
* Fixed: PHP 8.2+ compatibility - replaced undefined constant INPUT_REQUEST
* Fixed: Plugin activation error with proper request parameter handling
* Enhanced: Default settings - post types are now disabled by default for better control
* Enhanced: Admin interface with improved settings organization
* Enhanced: Security with proper nonce verification and input sanitization
* Improved: Build process with optimized CSS/JS minification
* Improved: RTL support with generated RTL stylesheets
* Updated: Translation files with new strings for post type functionality

= 2.0.0 =
* Major Update: Complete overhaul with new tracking foundation for analytics and gamification
* Added: Share tracking system for internal reshares and external social shares
* Added: New hook `bp_share_user_reshared_activity` for point/reward system integration
* Added: UTM and custom tracking parameters to all external share links
* Added: Automatic statistics tracking (user stats, activity stats, visit stats)
* Added: New `Buddypress_Share_Tracker` class for tracking functionality
* Added: Multiple new hooks and filters for developer extensibility
* Added: Comprehensive user and developer documentation
* Fixed: All PHP 8.0+ compatibility issues (replaced FILTER_SANITIZE_STRING)
* Fixed: All undefined variable and array index warnings
* Fixed: array_key_first() compatibility for PHP < 7.3
* Fixed: License table spacing and CSS issues
* Improved: Moved inline styles to external CSS files
* Improved: Better error handling without suppression operators
* Enhanced: Type safety and proper validation throughout
* Required: Minimum PHP version now 7.4+

= 1.5.1 =
* Fixed: Fatal error on plugin activation and several PHP warnings.
* Fixed: Issues with translation loading and incorrect text domains.
* Fixed: Console warnings when disabling sharing options.
* Improved: Sharing message layout and group selection UI.
* Improved: Confirmation notice when settings are saved.
* Improved: RTL compatibility and overall wording for better clarity.
* Updated: Plugin update checker for smoother version management.
* Cleaned: Removed unused files and optimized code for better performance.
* Enhanced: JavaScript and PHP logic for sharing activities.
* Added: Inline documentation for improved developer experience.

= 1.5.0 =
* Updated: Plugin Update Checker to v5 (PucFactory) with modern initialization for better reliability.  
* Optimized: `bp_activity_create_reshare_ajax` function for enhanced **security**, **performance**, and **readability**.  
* Added: Filter support for **shortcode compatibility**.  
* Improved: Activity share popup now appears correctly on media modals when using the BuddyBoss theme.  
* Enhanced: Display of “time since” on reshared posts with BuddyBoss integration.  
* Updated: Dependencies, and improved handling of “Read More” in reshared activity content.  
* Fixed: GamiPress compatibility – user earnings are now properly awarded on shared activities.  
* Cleaned: RTL CSS and JS file translation logic, and removed unused/commented code.

= 1.4.0 =
* Fixed escaping functions for improved security.
* Fixed issue with social dropdown not showing in logout mode with BuddyX Pro.
* Added Telegram and Bluesky sharing functionality.
* Fixed issue excluding WhatsApp and Email services from popup behavior.
* Set default enabled services to Facebook, Twitter, LinkedIn, Email, and WhatsApp.
* Fixed warnings and potential fatal errors in plugin functions.
* Enhanced Email and WhatsApp sharing links with dynamic site title and URL.
* Improved `display_admin_notice()` function.
* Improved `check_installation_date()` function.
* Simplified `seconds_to_words()` function.
* Enabled minified CSS and JS file loading for better performance.
* Removed unused functions and redundant code.
* Added setting to show social share icons in logout mode.


= 1.3.0 =
* Fix: Hide share count when the count is 0 for a cleaner UI.
* Fix: Managed the behavior of the share popup, now hidden upon clicking the BuddyPress share icon.
* Fix: Resolved fatal error caused by the Share Pro feature.
* Update: Corrected "Whatsapp" typo to "WhatsApp."
* Update: Managed shared links to open in a new tab for better navigation.
* Fix: Managed BuddyPress activity share URL for improved functionality.
* Fix: Managed share URLs for BuddyBoss Platform for consistency.
* Fix: Corrected issue where "My Profile" shares were posted in groups instead of activities.

= 1.2.3 =
* Fix: Issue with BuddyBoss
* Fix: UI fixes
* Fix: Warning 
* Fix: Merge share option in one place
* Fix: (#146) UI fixes with BB Platform
* Fix: (#146) Meta action section
* Fix: (#146) BuddyPress Photos, Videos and Documents reshare issues
* Fix: (#146) Blog post reshare content issue
* Fix: Optimize activity share code flow
* Added: Activity share button to show/hide setting
* Updated: Twitter to X and condition to show share icons
* Updated: Backend options description
* Managed:  Popup window option
* Managed:  Show share dropdown menu on the last activity
* Managed: Group activity listing share UI
* Managed: (#141) Dokan tooltip not working with plugin
* Managed: (#140) Copy share URL with the Safari browser
* Managed: (#140) Share URL in a compose message

= 1.2.2 =
* Updated: Share activity URL
* Updated: (#135) Labels
* Updated: (#135) Label, content, top banner, and doc link
* Updated: (#135) Label, content, top banner, and doc link
* Updated: (#135) Tooltip text position
* Updated: (#135) Copy bottom tooltip position
* Fix: Bp v12 fixes
* Fix: Reshare activity from message with BuddyBoss
* Fix: Duplicate post button issue
* Fix: Reshare activity on message
* Fix: (#135) Service buttons UI fixes
* Fix: (#135) Added description for a post-sharing option
* Fix: (#135) Managed share popup UI with bb platform
* Fix: (#135) BP share open graph Youzify support
* Fix: (#135) Removed icon color options and added icon pattern
* Fix: (135) Added copy link functionality
* Fix: (#135) Removed post button color setting managed via theme color scheme
* Fix: PHPCS nonce fixes
* Fix: PHPCS fixes
* Fix: (#127) Issue with PHP 8.2

= 1.2.1 =
* Fix: Fixed fatal error on plugin activation
* Fix: Fixed wc-vendors dropdown issue with select2 js
* Fix: Fixed CSRF vulnerability

= 1.2.0 =
* Fix: Fixed enable social share option issue
* Fix: Fixed Plugin redirect issue when multi plugin activate the same time
* Fix: Added missing action and text domain fixes
* Fix: Update social icons backend setting drag drop structure
* Fix: (#113) Removed failed to load source map error

= 1.1.1 =
* Fix: Update admin wrapper UI

= 1.1.0 =
* Fix: Managed share button icon with bb platform
* Fix: Fixed popup select2 UI with 3rd party plugins
* Fix: Fixed Fatal error: Uncaught Error: Call to undefined function friends

= 1.0.0 =
* First version.
