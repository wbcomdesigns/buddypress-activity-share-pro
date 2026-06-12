---
title: "Integration & API Reference"
slug: "integration-bpas"
category: "developer-guide-bpas"
---

# Integration & API Reference

This page covers how BuddyPress Activity Share Pro exposes its functionality to other code: the AJAX endpoints it registers, the one REST API extension it makes to BuddyPress's own responses, and the action/filter hooks that are the primary integration surface.

---

## No Custom REST Namespace

BuddyPress Activity Share Pro does **not** register a custom REST API namespace. There is no `bp-share/v1` route, and no endpoints under `/wp-json/bp-share/`. Calling those paths returns a 404.

Some earlier documentation drafts showed fabricated endpoints like `GET /wp-json/bp-share/v1/statistics/{activity_id}` and `POST /wp-json/bp-share/v1/reshare`. Those routes do not exist and were never part of the plugin. Do not rely on them.

All sharing and tracking operations are handled through WordPress AJAX (`admin-ajax.php`) and the WordPress hook system.

---

## BuddyPress REST API Extension

The plugin hooks into BuddyPress's own activity REST API to add one extra field to activity responses.

**Filter used:** `bp_rest_activity_prepare_value` (a BuddyPress core filter)

**What it adds:**

When BuddyPress returns an activity item via its REST API (e.g., `GET /wp-json/buddypress/v1/activity/{id}`), the plugin appends a `bp_activity_share_count` field containing the integer share count for that activity:

```json
{
  "id": 123,
  "content": { "rendered": "..." },
  "bp_activity_share_count": 7
}
```

This is a read-only field. There is no corresponding write endpoint.

**Source:** `public/class-buddypress-share-public.php` — method `bp_activity_post_reshare_data_embed_rest_api`

---

## AJAX Endpoints

All plugin functionality is wired through `admin-ajax.php`. Requests are POST unless noted. Every endpoint requires a valid WordPress nonce.

### Activity Resharing

#### `bp_activity_create_reshare_ajax`

Creates a new reshare of an existing BuddyPress activity.

- **Authentication:** Logged-in users only (`wp_ajax_` prefix)
- **Nonce action:** `bp-activity-share-nonce`
- **Parameters:**
  - `activity_id` (int) — ID of the activity to reshare
  - `type` (string) — Share destination: `activity_share`, `post_share`
  - `activity_content` (string) — Optional message to attach
  - `activity_in` (int) — Group ID when sharing to a group
  - `activity_in_type` (string) — `user` or `group`
- **Source:** `public/class-buddypress-share-public.php`

#### `bp_share_get_activity_content`

Fetches the content of a specific activity for display in the share modal.

- **Authentication:** Logged-in users only
- **Source:** `public/class-buddypress-share-public.php`

#### `bp_get_user_share_options`

Returns the list of groups and friends available as reshare destinations for the current user.

- **Authentication:** Logged-in users only
- **Source:** `public/class-buddypress-share-public.php`

### Post-Type Sharing

#### `bp_share_post`

Records a share event for a WordPress post (non-activity content). Accepts both authenticated and anonymous requests.

- **Authentication:** `wp_ajax_` and `wp_ajax_nopriv_` (accepts logged-out users)
- **Source:** `includes/post-types/class-bp-share-post-type-controller.php`

### External Share Tracking

#### `bp_share_track_external`

Records that a user clicked an external share link (Facebook, X, etc.).

- **Authentication:** `wp_ajax_` and `wp_ajax_nopriv_` (accepts logged-out users)
- **Source:** `includes/class-buddypress-share-tracker.php`

### Admin AJAX (Admin-Only)

#### `wss_social_icons`

Saves a newly added social service to the admin settings.

- **Authentication:** Logged-in users only (admin context)
- **Source:** `admin/class-buddypress-share-admin.php`

#### `wss_social_remove_icons`

Removes a social service from the admin settings.

- **Authentication:** Logged-in users only (admin context)
- **Source:** `admin/class-buddypress-share-admin.php`

---

## Hooks

Hooks are the recommended integration point for custom code. The plugin fires a set of action hooks after key events and provides filters to modify data before it is used.

See the Hooks & Filters reference doc in this developer guide for full parameter lists and code examples. Key integration hooks include:

- `bp_share_after_create_reshare` — fires after a reshare activity is created; use this to trigger notifications or point awards
- `bp_share_user_reshared_activity` — purpose-built for gamification integrations (myCRED, GamiPress)
- `bp_share_external_share_tracked` — fires when an external share is recorded
- `bp_share_services_config` — filter to add, remove, or modify social sharing services
- `bp_share_tracking_parameters` — filter to customize the UTM and custom parameters appended to tracked links

---

## What Is Not Available

| Capability | Available? | Alternative |
|---|---|---|
| Custom REST namespace (`bp-share/v1`) | No | Use AJAX endpoints or hooks |
| REST endpoint to list share statistics | No | Query `bp_activity_get_meta( $id, 'share_count', true )` directly, or use `Buddypress_Share_Tracker::get_activity_stats()` |
| REST endpoint to create a reshare | No | Use the `bp_activity_create_reshare_ajax` AJAX action |
| REST endpoint to list enabled services | No | Use the `bp_share_available_services` filter |
| Webhook / push notification on share | No | Hook into `bp_share_after_create_reshare` or `bp_share_external_share_tracked` |
