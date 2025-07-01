=== Wbcom Designs - BuddyPress Activity Share Pro ===

Contributors: vapvarun,wbcomdesigns
Donate link: https://wbcomdesigns.com
Tags: buddypress, activity, share
Requires at least: 4.0
Tested up to: 6.8.2
Stable tag: 1.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Free WordPress plugin allows anyone to easily share BuddyPress Activities on major social media (Facebook, Twitter, Linkedin ).

== Description ==

BuddyPress Activity Social Share allows users to share activities on their social networking profiles.

A perfect plugin to make your user activities on your website social-share-friendly and dramatically increase your members' social reach!

[Live demo >](https://bb-free.buddyxtheme.com/)
[Additional Details >](https://wbcomdesigns.com/downloads/buddypress-activity-social-share/)

**If you like the plugin functionality, please leave a review to help the plugin grow!**

=== THEME - WORDPRESS THEME WITH OUTSTANDING BUDDYPRESS SUPPORT ===
* [FREE BuddyPress Theme: BuddyX](https://wordpress.org/themes/buddyx/) - Offers unique layouts with clean code and easy-to-customize options, giving you a whole new way to visualize BuddyPress.

== Installation ==

This section describes how to install the plugin and get it working.

1. Download the zip file and extract it.

2. Upload `bp-activity-social-share` directory to the `/wp-content/plugins/` directory

3. Activate the plugin through the \'Plugins\' menu.

4. Alternatively, you can use the WordPress Plugin installer from Dashboard->Plugins->Add New to add this plugin

5. Enjoy

If you need additional help, contact us for [Custom Development](https://wbcomdesigns.com/hire-us/).


== Frequently Asked Questions ==

= Does this plugin require another plugin? =
Yes, this plugin requires the  BuddyPress plugin.

== Changelog ==

== Changelog ==
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
