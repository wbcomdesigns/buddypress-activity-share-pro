# Share Tracking and Analytics

The plugin records who shares what and how visitors arrive from shared links, then surfaces the totals in an Analytics tab.

## Tracking parameters on share links

Every external activity share link is tagged with UTM and custom parameters:

- `utm_source`, `utm_medium`, `utm_campaign`, `utm_content`
- `bps_aid` (activity ID), `bps_uid` (user ID, `0` when logged out), `bps_service`, `bps_time`

When a visitor follows one of these links back to your site, the plugin reads the parameters and records the visit. External social-share clicks are counted this way, on the return visit; the plugin does not intercept the outbound click itself.

You can change or drop parameters (for example remove `bps_uid` for privacy) with the `bp_share_tracking_parameters` filter.

## What gets stored

- **User stats** in `bp_share_user_stats` user meta: total, internal, and external share counts, a per-type breakdown, and the last share date.
- **Activity stats** in `bp_share_activity_stats` activity meta: total, internal, and external counts, unique sharers, and the last share date.
- **Visit stats** in `bp_share_visit_stats` activity meta: total visits, per-service visits, and the last visit date.
- **Post-type shares** as rows in the `{prefix}bp_share_post_tracking` table.

## Analytics tab

The **Analytics** settings tab (added in 2.3.0) shows the data the plugin collects:

- A date-range filter: last 7 days, 30 days, 90 days, or 12 months.
- Post-type share totals, read from the tracking table with indexed `COUNT(*)` / `GROUP BY` aggregates.
- The top reshared activities (all-time, since reshare counts have no date column), from the `share_count` meta, limited to the top 10.

Results are cached in a five-minute transient keyed by the date range, and the tab handles empty and error states.

## Privacy

Two filters control IP handling for post-type tracking:

- `bp_share_disable_ip_tracking` - return `true` to skip IP collection entirely (the stored value becomes `anonymous`).
- `bp_share_anonymize_ip` - return `true` to drop the last octet of IPv4 addresses before storage.

## REST API

The plugin adds a read-only `bp_activity_share_count` field to every activity returned by the BuddyPress REST API, via the core `bp_rest_activity_prepare_value` filter. There is no custom REST namespace; see [Integration and API Reference](../developer-guide/02-integration.md).
