---
title: Resharing to Your Profile, a Group, or a Friend
slug: resharing-internally-bpas
description: How to reshare a BuddyPress activity to your own profile, a group you belong to, or a friend's timeline.
category: user-guide-bpas
order: 2
---

# Resharing to Your Profile, a Group, or a Friend

The **Reshare** option lets you repost any activity inside your community without leaving the site. The original content is preserved and attributed to its author; your reshare appears in the activity feed with a note showing who shared it.

You must be logged in to reshare.

## How resharing works

When you reshare an activity:

1. A new activity entry appears in the feed from your account (or in the group you chose).
2. The original activity content is embedded in your reshare.
3. A "shared an activity" label connects your name to the original.
4. The share count on the original activity goes up by one.

Your site administrator can choose whether reshared activities show only the original content ("Simple View") or the full nested detail ("Detailed View"). That setting affects how reshares look to everyone — you cannot change it per reshare.

## Step by step

1. Find the activity you want to reshare.
2. Click the **Share** button in the activity's action bar.
3. Click **Reshare** at the top of the Share menu.
4. The Reshare modal opens. It shows your profile photo and name.

### Choose where to post

Use the **Post in** dropdown to pick a destination:

| Option | Who sees the reshare |
|---|---|
| My Profile | Anyone who can view your profile's activity feed |
| A group (listed by name) | Members of that group |
| A friend (listed by name) | Appears as a mention in the main activity stream |

The dropdown shows only groups you are a member of and only confirmed friends. If your site does not have the BuddyPress Groups or Friends components active, those options will not appear.

### Add a message (optional)

Below the destination selector is a text area where you can type your own comment or context before posting. This is optional — you can reshare without adding anything.

### Post the reshare

Click **Post**. The modal closes and the reshare appears in the chosen location. If you change your mind before posting, click **Discard** to cancel.

## When reshare destinations are missing

A site administrator can turn off individual reshare destinations:

- If "User Profiles" resharing is disabled, the Reshare button will not appear on profile pages.
- If "Groups" resharing is disabled, the Groups option is removed from the "Post in" dropdown.
- If "Friends" resharing is disabled, the Friends option is removed from the "Post in" dropdown.

If none of the three destinations are available, the Reshare button does not appear at all.

## What cannot be reshared

Blog post activities can be disabled for resharing by the administrator (under **Restrictions > Content Restrictions > Blog Posts**). If that setting is on, blog-type activities will not have a Reshare button.

---

**Previous:** [Sharing Activities to Social Networks](./sharing-to-social-networks.md)  
**Next:** [Supported Networks Reference](./supported-networks.md)
