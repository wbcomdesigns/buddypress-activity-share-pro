# Requirements

Activity Share Pro is an add-on to the free **Activity Share for BuddyPress** plugin. It does nothing on its own.

| Requirement | Minimum |
|---|---|
| WordPress | 6.7 |
| PHP | 8.1 |
| BuddyPress | 12.0 or later, or BuddyBoss Platform |
| Activity Share for BuddyPress | Active, 3.6.0 or later. Install the same version as Pro (3.6.1 with Pro 3.6.1). |

Pro stays inactive while **Youzify** is active, because Youzify has its own sharing.

## What each feature needs

| Feature | Needs |
|---|---|
| Repost, Share replies, Counts and who reposted | The BuddyPress **Activity** component. Share replies also needs Repost to be on. |
| Send to a friend | The BuddyPress **Friend Connections** and **Private Messaging** components. |
| Group controls | The BuddyPress **User Groups** component. |
| Repost notifications | The BuddyPress **Notifications** component. The notification email uses BuddyPress emails. |
| Member setting "Allow other members to repost my posts" | The BuddyPress **Account Settings** component. |
| Share image for text posts | The PHP GD extension with FreeType support. Without it the feature does nothing. |
| Share rewards | WB Gamification, GamiPress or myCRED. |
| Short share links | The PHP OpenSSL extension. Without it, members share the full link. |

Turn BuddyPress components on under **BuddyPress > Settings > Components**.
