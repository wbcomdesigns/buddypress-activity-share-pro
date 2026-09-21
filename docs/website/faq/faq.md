# Frequently Asked Questions

These answers match the FAQ shipped inside the plugin's admin screen.

## Does this plugin require another plugin?

Yes. It needs BuddyPress or BuddyBoss Platform installed and active. With neither present, the plugin stops and shows an admin notice.

## How do I turn on social sharing?

Sharing is on by default. Use the Social Networks tab to turn it on or off, and make sure at least one network is active.

## Can visitors share without logging in?

Yes. Turn on Guest sharing in the Social Networks tab so logged-out visitors can share public activity. The internal Reshare option always requires an account.

## Which networks are supported?

Facebook, X (Twitter), LinkedIn, Pinterest, Reddit, WordPress, Pocket, Telegram, Bluesky, WhatsApp, E-mail, and Copy Link. You turn each one on or off individually.

## How do I change how the buttons look?

Open the Display tab to pick an icon style (Circle, Rectangle, Black & White, or Bar) and set the colors.

## Do sharing links open in a popup?

By default, yes. Sharing links open in a small popup. You can turn this off in the Social Networks tab. WhatsApp and E-mail always open normally, never as a popup.

## How does resharing work?

When a member reshares an activity, you can show just the original activity or the full activity with nested content. Set this in the Restrictions tab.

## Can I turn off sharing for certain activity types?

Yes. In the Restrictions tab you can turn off resharing for blog posts, member profiles, and groups independently.

## Does this work with BuddyBoss Platform?

Yes. The plugin works with both BuddyPress and BuddyBoss Platform automatically.

## Do I need API keys for the social networks?

No. Share links open on each social platform's own sharing page, so no API keys or app credentials are required.

## Is there a REST API?

The plugin adds a read-only `bp_activity_share_count` field to the BuddyPress activity REST responses. It does not register its own REST namespace. See [Integration and API Reference](../developer-guide/02-integration.md).

## Where can I get support?

For premium support, visit the [Wbcom Designs support portal](https://wbcomdesigns.com/support/).
