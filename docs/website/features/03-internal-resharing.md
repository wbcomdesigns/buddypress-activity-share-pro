# Internal Resharing

Internal resharing lets a logged-in member repost an activity inside your community without leaving the site. The reshare is saved as a new BuddyPress activity of type `activity_share`, with the original activity ID kept in `secondary_item_id` and in the `shared_activity_id` meta key.

## What members can do

From the Reshare modal a member can post the activity to:

- **Their own profile.**
- **A group** they belong to (when the Groups component is active).
- **A friend**, as an @mention (when the Friends component is active).

Groups and friends load into the modal on open, over a separate AJAX call, capped at 50 of each. A member can add an optional message before posting. For the step-by-step member view, see [Resharing to Your Profile, a Group, or a Friend](../usage/02-resharing-internally.md).

## Reshare display mode

When a reshared activity appears in the feed, you choose how much of the original it shows:

- **Simple view** shows only the original activity content.
- **Detailed view** shows the full nested content.

This is a site-wide choice stored in `bp_reshare_settings[reshare_share_activity]` (`parent` for simple, `child` for detailed). Members cannot change it per reshare. Set it on the **Restrictions** tab.

## Content restrictions

On the **Restrictions** tab you can turn resharing off for specific content types, independently:

- **Blog Posts** (`disable_post_reshare_activity`)
- **User Profiles** (`disable_my_profile_reshare_activity`)
- **Groups** (`disable_group_reshare_activity`)

When a type is disabled, neither the Share nor the Reshare button appears on the matching page. The Reshare button only renders when at least one reshare destination is still available; if all three are disabled it does not appear at all.

## Extend it

- `bp_share_before_create_reshare` - change the reshare data before the activity is created.
- `bp_share_after_create_reshare` - run code after a reshare is created (notifications, logging).
- `bp_share_user_reshared_activity` - built for point systems such as GamiPress or myCRED; fires with the user, destination type, original activity ID, and new activity ID.

See the [Hooks and Filters Reference](../developer-guide/01-hooks.md).
