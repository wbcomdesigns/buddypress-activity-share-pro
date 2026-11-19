# Where Pro keeps its data, and what uninstall removes

## Where the data is stored

| Data | Where |
|---|---|
| Settings (Features, Content types) | The `bpas_pro_settings` option |
| Reposts | Normal BuddyPress activity items, owned by the member who reposted |
| Share, repost, send and visit records for analytics | The `bpas_pro_events` table (with your database prefix). One row per event: type, item, network, member, a one-way visitor code and date. IP addresses are never stored. |
| Share counts | A hidden field on each activity item or post |
| Member choice "Allow other members to repost my posts" | A field on the member's account |
| Group choices under **Manage > Settings** | Fields on the group |
| Repost notifications and the repost email | BuddyPress notifications, and a BuddyPress email under **Appearance > Emails** ("A member reposts one of your updates.") |
| Share images for text posts | PNG files in `uploads/bpas-cards` |
| Cached lists | Short-lived cache entries: the Most shared list for 15 minutes, and a one-day limit on repost emails per post |

## How long analytics records are kept

Records are deleted after 12 months by a daily clean-up job. Developers can change this with the `bpas_pro_retention_days` filter; the value is never less than 30 days. Records are included in WordPress's personal data export and erase tools under **Tools > Export/Erase Personal Data**.

## Deactivating

Deactivating Pro stops the daily clean-up job and removes nothing else. Reposts stay in the activity stream.

## Deleting the plugin

Delete Pro from **Plugins** to remove its data. On a multisite network this runs for every site. It removes:

- the settings, the stored version, the license key entry and the Most shared widget settings,
- the `bpas_pro_events` table,
- share counts on activity items and posts,
- each member's "Allow other members to repost my posts" choice and email choice,
- the group sharing choices,
- every repost notification and the repost email,
- the generated share images and the scheduled clean-up.

It keeps:

- **Reposts.** They are the members' own posts, with their own comments and reactions, so they stay in the activity stream.
- **Private messages** sent with Send to a friend.
- **The free plugin's settings.** Activity Share for BuddyPress has its own clean-up.

Because the license key entry is removed, paste your key again on the **License** tab if you install Pro again.
