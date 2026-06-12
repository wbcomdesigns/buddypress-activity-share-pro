# Your First Share

This page walks through what your members will see after the plugin is active, and how to confirm everything is working.

## What Members See

On any BuddyPress activity page, each activity entry has a row of share icons below the post content. By default those icons are: Facebook, X (Twitter), LinkedIn, WhatsApp, Email, and Copy Link.

Members click an icon to share that activity to the corresponding network. The plugin builds a direct share URL using the activity's permalink, so no API keys are required.

### Sharing to a Social Network

1. A member finds an activity they want to share.
2. They click the social network icon — for example, Facebook.
3. A small popup window opens (by default) with the Facebook share dialog pre-filled with the activity URL.
4. The member completes the share on Facebook's own page.
5. The popup closes when done.

The "Popup Windows" option in settings controls whether links open in a popup or a new tab. Either way, the activity URL is what gets shared.

### Copying the Link

The **Copy Link** button copies the activity's permalink to the clipboard. The member can then paste it anywhere — a message, an email, another app.

### Resharing Inside Your Community

The plugin also adds an internal reshare option. When a member clicks the reshare icon (if enabled), a modal dialog opens. From there they can:

- Post the activity to their own profile
- Share it to a BuddyPress group they belong to
- Send it to a friend (if the BuddyPress Friends component is active)

The member can add a comment before resharing. The reshared activity appears in the activity stream with a reference back to the original.

### Share Counts

The number of times an activity has been shared is tracked and displayed next to the share button. This count includes both internal reshares and external social shares. If the count is 0, it is hidden.

## Guest Sharing

If Guest Sharing is enabled in settings (it is on by default), logged-out visitors can also see the share icons and use the social network buttons. The internal reshare option is not available to guests — that requires a logged-in member.

## Confirming the Setup

To run a quick test after installation:

1. Log in as a regular member (not an admin).
2. Go to the activity stream.
3. Click the Facebook share icon on any activity.
4. Confirm a Facebook share popup opens.
5. Close the popup without completing the share.
6. Click the **Copy Link** button and verify the link is copied to your clipboard.
7. Now click the reshare icon and verify the modal opens with options to share to your profile or a group.

If any of these steps do not work, check the [Requirements](./requirements.md) page and confirm the BuddyPress Activity component is active.

## Adjusting Which Networks Appear

Out of the box, six networks are active. You can enable additional networks (Pinterest, Reddit, Telegram, Bluesky, Pocket, WordPress.com) or remove any of the default six.

Go to **WBcom Designs > BuddyPress Share**, and on the **Social Networks** tab, drag networks between the **Active Networks** and **Inactive Networks** lists. Changes save immediately as you drag.

The order of icons on the frontend matches the order in the Active Networks list, so you can reorder them by dragging within that list.
