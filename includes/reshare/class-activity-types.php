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
		if ( ! bp_is_active( 'activity' ) ) {
			return;
		}
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
	 * "Anna reposted an update" / "... a reply" / "... a post" / "... a page" [in the group G].
	 * Any other content type names itself: "Anna reposted Blue Hoodie (Product)".
	 *
	 * @param string $action   Default action.
	 * @param object $activity Activity.
	 */
	public static function format_action( $action, $activity ): string {
		$user = bp_core_get_userlink( (int) $activity->user_id );
		$kind = 'post';
		$item = '';
		$type = '';
		if ( 'activity_share' === $activity->type ) {
			$original = Reshare_Service::original_activity( (int) $activity->secondary_item_id );
			$kind     = $original && 'activity_comment' === $original->type ? 'reply' : 'update';
		} else {
			$post      = get_post( (int) $activity->secondary_item_id );
			$type_info = $post ? get_post_type_object( $post->post_type ) : null;
			if ( $post && 'page' === $post->post_type ) {
				$kind = 'page';
			} elseif ( $post && $type_info && 'post' !== $post->post_type ) {
				// A label in the middle of a sentence breaks grammar in many languages ("a"/"an",
				// gender, case), so other types read as "<title> (<type>)" instead.
				$kind = 'item';
				$item = '<a href="' . esc_url( (string) get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a>';
				$type = esc_html( $type_info->labels->singular_name );
			}
		}

		$group = null;
		if ( 'groups' === $activity->component && function_exists( 'groups_get_group' ) ) {
			$group = groups_get_group( (int) $activity->item_id );
			$group = empty( $group->id ) ? null : $group;
		}

		// Whole sentences only, so translators control word order (no "... in the group ..." glued on).
		if ( $group ) {
			$link  = '<a href="' . esc_url( bpas_group_url( $group ) ) . '">' . esc_html( $group->name ) . '</a>';
			$texts = array(
				/* translators: 1: member link, 2: group link. */
				'update' => __( '%1$s reposted an update in the group %2$s', 'buddypress-activity-share-pro' ),
				/* translators: 1: member link, 2: group link. */
				'reply'  => __( '%1$s reposted a reply in the group %2$s', 'buddypress-activity-share-pro' ),
				/* translators: 1: member link, 2: group link. */
				'post'   => __( '%1$s reposted a post in the group %2$s', 'buddypress-activity-share-pro' ),
				/* translators: 1: member link, 2: group link. */
				'page'   => __( '%1$s reposted a page in the group %2$s', 'buddypress-activity-share-pro' ),
				/* translators: 1: member link, 2: group link, 3: linked title of the shared item, 4: its content type, e.g. Product. */
				'item'   => __( '%1$s reposted %3$s (%4$s) in the group %2$s', 'buddypress-activity-share-pro' ),
			);
			$text  = sprintf( $texts[ $kind ], $user, $link, $item, $type );
		} else {
			$texts = array(
				/* translators: %s: member link. */
				'update' => __( '%s reposted an update', 'buddypress-activity-share-pro' ),
				/* translators: %s: member link. */
				'reply'  => __( '%s reposted a reply', 'buddypress-activity-share-pro' ),
				/* translators: %s: member link. */
				'post'   => __( '%s reposted a post', 'buddypress-activity-share-pro' ),
				/* translators: %s: member link. */
				'page'   => __( '%s reposted a page', 'buddypress-activity-share-pro' ),
				/* translators: 1: member link, 2: linked title of the shared item, 3: its content type, e.g. Product. */
				'item'   => __( '%1$s reposted %2$s (%3$s)', 'buddypress-activity-share-pro' ),
			);
			$text  = sprintf( $texts[ $kind ], $user, $item, $type );
		}

		return (string) apply_filters( 'bpas_pro_repost_action', $text, $activity );
	}
}
