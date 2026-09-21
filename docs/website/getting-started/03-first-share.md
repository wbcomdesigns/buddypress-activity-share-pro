# Your First Share

This page walks through what your members see once the plugin is active, and how to confirm everything works.

## What Members See

On any BuddyPress activity page, each activity entry has a Share button below the post content. Opening it shows the enabled social networks and, for logged-in members, a Reshare option.

By default the enabled networks are Facebook, X (Twitter), LinkedIn, WhatsApp, E-mail, and Copy Link.

The plugin builds a direct share URL from the activity's permalink, so no API keys are needed.

### Sharing to a Social Network

1. A member finds an activity they want to share.
2. They open the Share menu and click a network icon, for example Facebook.
3. A small popup window opens (by default) with that platform's share dialog, pre-filled with the activity URL.
4. The member completes the share on the platform's own page.

The Popup Windows option controls whether links open in a popup or a new tab. WhatsApp and E-mail always open normally, never as a popup.

### Copying the Link

The Copy Link button copies the activity's permalink to the clipboard. A small "Link Copied!" tooltip confirms the action. No new tab opens.

### Resharing Inside Your Community

Logged-in members also get an internal Reshare option. Clicking Reshare opens a modal where they can:

- Post the activity to their own profile.
- Share it to a BuddyPress group they belong to.
- Send it to a friend (when the BuddyPress Friends component is active).

The member can add a message before posting. The reshared activity appears in the feed with a reference back to the original.

### Share Count

A share count appears on the Share button. It increases on each successful internal reshare. When the count is zero it is hidden.

## Guest Sharing

With Guest Sharing enabled (the default), logged-out visitors also see the social network buttons on public activity. The internal Reshare option is never shown to guests, since resharing requires an account.

## Confirming the Setup

To run a quick test after installation:

1. Log in as a regular member (not an admin).
2. Go to the activity stream.
3. Open the Share menu on any activity and click the Facebook icon.
4. Confirm a Facebook share popup opens, then close it without completing the share.
5. Click Copy Link and confirm the link is on your clipboard.
6. Click Reshare and confirm the modal opens with options to post to your profile or a group.

If any step fails, see [Troubleshooting](../troubleshooting/troubleshooting.md).

## Adjusting Which Networks Appear

Out of the box, six networks are active. You can enable more (Pinterest, Reddit, Telegram, Bluesky, Pocket, WordPress) or remove any of the defaults.

On the Social Networks settings tab, drag networks between the **Active** and **Inactive** lists. Changes save immediately as you drag. The order of the Active list is the order the icons appear on the frontend, so you can reorder by dragging within it.
