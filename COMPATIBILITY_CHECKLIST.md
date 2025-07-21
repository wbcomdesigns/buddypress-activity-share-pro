# BuddyPress Activity Share Pro - Compatibility Checklist

## Platform Support Testing

This checklist covers testing for both BuddyPress and BuddyBoss Platform compatibility.

### Core Requirements
- [ ] Plugin activates without errors on BuddyPress
- [ ] Plugin activates without errors on BuddyBoss Platform
- [ ] No PHP errors in debug log
- [ ] No JavaScript console errors

### 1. Activity Stream Integration

#### On Profile Activity Stream
- [ ] **BuddyPress**: Reshare button appears on activity items
- [ ] **BuddyBoss**: Reshare button appears on activity items
- [ ] Reshare button styling matches platform theme
- [ ] Share count displays correctly
- [ ] Share dropdown opens properly

#### On Group Activity Stream
- [ ] **BuddyPress**: Reshare button appears on group activities
- [ ] **BuddyBoss**: Reshare button appears on group activities
- [ ] Can reshare from group to profile
- [ ] Can reshare from group to another group
- [ ] Group permissions are respected

### 2. Reshare Modal Functionality

#### Modal Display
- [ ] **BuddyPress**: Modal opens when clicking reshare
- [ ] **BuddyBoss**: Modal opens when clicking reshare
- [ ] Close button (X) works properly
- [ ] ESC key closes modal
- [ ] Backdrop click closes modal

#### Share Options Dropdown
- [ ] "My Profile" option is available
- [ ] Groups populate dynamically (if user has groups)
- [ ] Friends populate dynamically (if user has friends)
- [ ] Select2 dropdown functions properly
- [ ] No console errors during dropdown interaction

### 3. Reshare Actions

#### Share to Profile
- [ ] **BuddyPress**: Can share activity to own profile
- [ ] **BuddyBoss**: Can share activity to own profile
- [ ] Original activity is embedded correctly
- [ ] Share count increments
- [ ] Activity appears in profile stream
- [ ] Can add custom text to reshare

#### Share to Groups
- [ ] **BuddyPress**: Can share to groups user is member of
- [ ] **BuddyBoss**: Can share to groups user is member of
- [ ] Only shows groups where user can post
- [ ] Activity appears in selected group
- [ ] Group privacy settings respected

#### Share to Friends
- [ ] **BuddyPress**: Can share to friends' walls (with mention)
- [ ] **BuddyBoss**: Can share to friends' walls (with mention)
- [ ] Friend receives notification of mention
- [ ] Activity appears in main stream with mention

### 4. Social Network Sharing

#### Share Button Dropdown
- [ ] Social share buttons appear on hover/click
- [ ] Copy link button works
- [ ] Link copied notification appears

#### Social Networks (Each Platform)
Test each enabled social network on both platforms:

##### Facebook
- [ ] **BuddyPress**: Generates correct share URL
- [ ] **BuddyBoss**: Generates correct share URL
- [ ] Opens Facebook share dialog
- [ ] Activity permalink is correct

##### Twitter/X
- [ ] **BuddyPress**: Generates correct tweet URL
- [ ] **BuddyBoss**: Generates correct tweet URL
- [ ] Opens Twitter compose window
- [ ] Includes activity excerpt

##### LinkedIn
- [ ] **BuddyPress**: Generates correct share URL
- [ ] **BuddyBoss**: Generates correct share URL
- [ ] Opens LinkedIn share dialog

##### WhatsApp
- [ ] **BuddyPress**: Generates correct share URL
- [ ] **BuddyBoss**: Generates correct share URL
- [ ] Opens WhatsApp with pre-filled message

##### Email
- [ ] **BuddyPress**: Creates mailto link
- [ ] **BuddyBoss**: Creates mailto link
- [ ] Subject and body populated correctly

##### Pinterest
- [ ] **BuddyPress**: Generates correct pin URL
- [ ] **BuddyBoss**: Generates correct pin URL
- [ ] Opens Pinterest create dialog

### 5. Activity Types Support

#### Standard Activities
- [ ] Text-only activities can be reshared
- [ ] Activities with images can be reshared
- [ ] Activities with links can be reshared
- [ ] Reshared activities can be reshared again (if enabled)

#### Special Activity Types
- [ ] **BuddyPress**: Blog post activities
- [ ] **BuddyBoss**: Media activities (photos/videos)
- [ ] **BuddyBoss**: Document activities
- [ ] Group join/leave activities (should not have share button)
- [ ] Friendship activities (should not have share button)

### 6. Settings & Configuration

#### Admin Settings
- [ ] Settings page loads without errors
- [ ] All settings save properly
- [ ] Social services can be enabled/disabled
- [ ] Drag & drop reordering works
- [ ] Color customization works

#### Feature Settings
- [ ] Reshare to profile enable/disable
- [ ] Reshare to groups enable/disable  
- [ ] Reshare to friends enable/disable
- [ ] Share count display toggle
- [ ] Self-sharing prevention
- [ ] Privacy settings respected

### 7. Performance & Compatibility

#### Ajax Operations
- [ ] Share action completes without page reload
- [ ] Groups load dynamically without delay
- [ ] Friends load dynamically without delay
- [ ] No duplicate Ajax requests

#### Theme Compatibility
- [ ] **BuddyPress**: Works with default BP themes
- [ ] **BuddyBoss**: Works with BuddyBoss theme
- [ ] Works with popular themes (Reign, BuddyX, etc.)
- [ ] RTL support functions correctly

### 8. Mobile Responsiveness

#### Touch Devices
- [ ] Share button accessible on mobile
- [ ] Modal displays properly on small screens
- [ ] Dropdown selections work on touch
- [ ] Social share buttons work on mobile

### 9. Edge Cases & Security

#### Error Handling
- [ ] Handles deleted activities gracefully
- [ ] Handles private group activities properly
- [ ] Handles blocked users correctly
- [ ] Rate limiting prevents spam

#### Permissions
- [ ] Non-logged users see social share only
- [ ] Logged users see reshare options
- [ ] Admin capabilities respected
- [ ] Nonce verification works

### 10. Platform-Specific Features

#### BuddyPress Specific
- [ ] Works with BP REST API
- [ ] Works with BP Nouveau template pack
- [ ] Works with BP Legacy template pack

#### BuddyBoss Specific
- [ ] Works with BB Platform REST API
- [ ] Works with BB activity moderation
- [ ] Works with BB privacy options
- [ ] Works with BB media/document sharing

## Testing Notes

### Version Compatibility
- BuddyPress: 8.0+ (Latest: 14.x)
- BuddyBoss Platform: 2.0+ (Latest: 2.6.x)
- WordPress: 5.0+
- PHP: 7.4+

### Known Differences
1. BuddyBoss uses different CSS classes for some elements
2. BuddyBoss has enhanced privacy controls
3. BuddyBoss includes media handling
4. Ajax endpoints may differ slightly

### Testing Process
1. Test each item on fresh install
2. Test with multiple users
3. Test with various group types
4. Test with different privacy settings
5. Check debug log after each section