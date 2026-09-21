# Social Sharing

Social sharing puts a Share button on every BuddyPress activity entry. Members open it to send the activity to any social network you have enabled, or to copy the link. This is the core of the plugin and it is on by default.

## What it does

- Adds a Share button to the activity stream (on the `bp_activity_entry_meta` hook).
- Opens a dropdown listing the enabled networks, plus a Reshare option for logged-in members.
- Builds each share URL from the activity permalink, so no social network API keys are required.
- Suppresses the button on profile or group pages when you have restricted those content types.

## Supported networks

Twelve destinations are built in:

Facebook, X (Twitter), LinkedIn, Pinterest, Reddit, WordPress, Pocket, Telegram, Bluesky, WhatsApp, E-mail, and Copy Link.

Each has its own share URL pattern. Legacy "Twitter" entries saved by older versions are migrated to "X" automatically on save. For what each button does on the frontend, see [Supported Social Networks](../usage/03-supported-networks.md).

## Configure it

Open the **Social Networks** settings tab.

- **Turn sharing on or off** with the master toggle (option `bp_share_services_enable`, on by default).
- **Choose which networks appear** by dragging them between the **Active** and **Inactive** lists. Each drag saves immediately over AJAX; there is no separate Save step. The active set is stored in `bp_share_services`.
- **Reorder icons** by dragging within the Active list. The frontend order matches the Active list order.
- **Guest sharing**: enable it (option `bp_share_services_logout_enable`, on by default) so logged-out visitors see the social buttons on public activity. Guests never see the internal Reshare button.
- **Popup windows**: when on (default), social links open in a small popup instead of a new tab. WhatsApp and E-mail always open normally regardless of this setting. Stored in `bp_share_services_extra[bp_share_services_open]`.

## Defaults on a fresh install

Six networks are enabled out of the box: Facebook, X (Twitter), LinkedIn, WhatsApp, E-mail, and Copy Link. Guest sharing and popup windows are both on.

## Share count

A running share count is shown on the Share button. It is stored as activity meta (`share_count`) and increases on each successful internal reshare. A count of zero is hidden.

## Extend it

- `bp_share_available_services` - add or remove networks in the admin list.
- `bp_share_services_config` - change the icon, URL template, or label per service.
- `bp_share_social_button_html` - change a single button's HTML before output.

See the [Hooks and Filters Reference](../developer-guide/01-hooks.md) for full signatures.
