<?php
/**
 * Repost activity types. Names kept from 2.x (BuddyBoss core and BuddyPress Stats read them).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Reshare;

defined( 'ABSPATH' ) || exit;

/**
 * `activity_share` (original = activity or reply) and `post_share` (original = post).
 * Original ID lives in `secondary_item_id`; group ID (if posted in a group) in `item_id`.
 */
final class Activity_Types {

	/**
	 * Register action strings for both components.
	 */
	public static function register(): void {
		foreach ( array( 'activity', 'groups' ) as $component ) {
			if ( ! bp_is_active( $component ) ) {
				continue;
			}
			$contexts = 'groups' === $component ? array( 'group', 'member_groups' ) : array( 'activity', 'member' );
			bp_activity_set_action( $component, 'activity_share', __( 'Reposted an update', 'buddypress-activity-share-pro' ), array( __CLASS__, 'format_action' ), __( 'Reposts', 'buddypress-activity-share-pro' ), $contexts );
			bp_activity_set_action( $component, 'post_share', __( 'Reposted a post', 'buddypress-activity-share-pro' ), array( __CLASS__, 'format_action' ), __( 'Reposts', 'buddypress-activity-share-pro' ), $contexts );
		}
	}

	/**
	 * "Anna reposted an update" / "... a post" / "... a reply" [in the group G].
	 *
	 * @param string $action   Default action.
	 * @param object $activity Activity.
	 */
	public static function format_action( $action, $activity ): string {
		$user = bp_core_get_userlink( (int) $activity->user_id );

		if ( 'post_share' === $activity->type ) {
			/* translators: %s: member link. */
			$text = sprintf( __( '%s reposted a post', 'buddypress-activity-share-pro' ), $user );
		} else {
			$original = Reshare_Service::original_activity( (int) $activity->secondary_item_id );
			$is_reply = $original && 'activity_comment' === $original->type;
			/* translators: %s: member link. */
			$text = $is_reply ? sprintf( __( '%s reposted a reply', 'buddypress-activity-share-pro' ), $user ) : sprintf( __( '%s reposted an update', 'buddypress-activity-share-pro' ), $user );
		}

		if ( 'groups' === $activity->component && function_exists( 'groups_get_group' ) ) {
			$group = groups_get_group( (int) $activity->item_id );
			if ( ! empty( $group->id ) ) {
				/* translators: 1: "Anna reposted an update", 2: group link. */
				$text = sprintf( __( '%1$s in the group %2$s', 'buddypress-activity-share-pro' ), $text, '<a href="' . esc_url( bp_get_group_url( $group ) ) . '">' . esc_html( $group->name ) . '</a>' );
			}
		}

		return (string) apply_filters( 'bpas_pro_repost_action', $text, $activity );
	}
}
