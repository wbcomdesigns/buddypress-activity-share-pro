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
		$kind = 'post';
		if ( 'activity_share' === $activity->type ) {
			$original = Reshare_Service::original_activity( (int) $activity->secondary_item_id );
			$kind     = $original && 'activity_comment' === $original->type ? 'reply' : 'update';
		}

		$group = null;
		if ( 'groups' === $activity->component && function_exists( 'groups_get_group' ) ) {
			$group = groups_get_group( (int) $activity->item_id );
			$group = empty( $group->id ) ? null : $group;
		}

		// Whole sentences only, so translators control word order (no "... in the group ..." glued on).
		if ( $group ) {
			$link  = '<a href="' . esc_url( bp_get_group_url( $group ) ) . '">' . esc_html( $group->name ) . '</a>';
			$texts = array(
				/* translators: 1: member link, 2: group link. */
				'update' => __( '%1$s reposted an update in the group %2$s', 'buddypress-activity-share-pro' ),
				/* translators: 1: member link, 2: group link. */
				'reply'  => __( '%1$s reposted a reply in the group %2$s', 'buddypress-activity-share-pro' ),
				/* translators: 1: member link, 2: group link. */
				'post'   => __( '%1$s reposted a post in the group %2$s', 'buddypress-activity-share-pro' ),
			);
			$text  = sprintf( $texts[ $kind ], $user, $link );
		} else {
			$texts = array(
				/* translators: %s: member link. */
				'update' => __( '%s reposted an update', 'buddypress-activity-share-pro' ),
				/* translators: %s: member link. */
				'reply'  => __( '%s reposted a reply', 'buddypress-activity-share-pro' ),
				/* translators: %s: member link. */
				'post'   => __( '%s reposted a post', 'buddypress-activity-share-pro' ),
			);
			$text  = sprintf( $texts[ $kind ], $user );
		}

		return (string) apply_filters( 'bpas_pro_repost_action', $text, $activity );
	}
}
