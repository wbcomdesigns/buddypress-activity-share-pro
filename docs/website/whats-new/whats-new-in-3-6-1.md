# What's new in 3.6.1

3.6.1 makes the repost and send dialog more reliable, fixes the wording of reposts, and tightens when Send to a friend appears. It follows 3.6.0, released a day earlier: if you have not read [What's new in 3.6.0](whats-new-in-3-6-0.md), read it first. Activity Share for BuddyPress 3.6.1 is released with it; update both plugins together.

## Update notes

- **Activity Share for BuddyPress 3.6.0 or later is required,** and 3.6.1 is the version to install alongside Pro 3.6.1.
- **WordPress 6.7, PHP 8.1 and BuddyPress 12.0 or later, or BuddyBoss Platform,** are required, as in 3.6.0.
- **Activating Pro without the free plugin active is now refused** with a message that tells you to activate the free plugin first. Deactivating the free plugin later leaves Pro paused with one notice and a button to activate it again. Your Pro settings are kept. See [Install, license and updates](../getting-started/02-install-and-license.md).
- **Send to a friend now needs two BuddyPress components:** Friend Connections and Private Messaging. If either is off, the switch disappears from the Features tab and a note says why. Turn them on under **BuddyPress > Settings > Components**.
- **No settings were changed or moved** in this release.

## What changed for site owners

- **Share rewards status on the Features tab.** A read-only line names the points plugin that is active (WB Gamification, GamiPress or myCRED), or tells you how to turn rewards on.
- **Send to a friend appears only where it can work.** It is hidden on a repost whose original cannot be shared, and it sends only to friends.
- **Repost wording.** A reposted page now reads "reposted a page", and other content types name the item and its type, for example "reposted Blue Hoodie (Product)".
- **The repost and send dialog** stays centred on screen with BuddyBoss and with themes that reset dialog styles. In browsers without native dialog support it still dims the page, keeps keyboard focus inside and closes with Escape. Closing it with Escape returns focus to the Share button.
- **No stray text in embeds and feeds.** The share and repost menu placed above or below a post no longer appears as loose text in embeds, RSS feeds or REST API content.

## Retest after updating

1. On **WB Plugins > Activity Share > Features**, check the **Share rewards** line shows the points plugin you use.
2. As a member with at least one friend, click **Share** on a public post and check **Send to a friend** appears. Send one and check it arrives as a private message.
3. Click **Share** on a repost whose original post was deleted or made private and check **Send to a friend** is not offered.
4. Click **Share**, then **Repost with comment**, press Escape and check focus returns to the **Share** button.
5. Repost a page or a product and check the activity line reads "reposted a page" or names the item and its type.
6. Open a post that has a share menu above or below it, in an RSS feed or an embed, and check no menu text appears.
7. Deactivate the free plugin for a moment, check Pro shows one notice with an activate button, and activate the free plugin again.

## For developers

- Repost, send and group features now register only while the BuddyPress component they need is switched on. The REST routes `reshare`, `activity/{id}/shares` and `activity/{id}/reposters` need Activity, `send` and `me/friends` need Friend Connections and Private Messaging, and `me/groups` needs User Groups. A request to a route whose component is off returns the normal "no route" error.
- The `bpas_pro_repost_action` filter receives the new wording for pages and other content types.
