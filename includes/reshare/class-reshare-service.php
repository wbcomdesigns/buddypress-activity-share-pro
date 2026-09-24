<?php
/**
 * Reposting and sending - every rule lives here (UI and REST both call this).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Reshare;

use BPAS_Pro\Settings;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Plan 12.3 rules: readable originals only, repost the original, own posts only into
 * groups, private-group posts stay in their group, member opt-out, group controls.
 */
final class Reshare_Service {

	const COUNT_META = '_bpas_share_count';

	/**
	 * Request cache of originals prefetched for the stream.
	 *
	 * @var array<int,object|false>
	 */
	private static array $originals = array();

	/**
	 * Request cache: "activity:ID" / "post:ID" => the member's own profile repost: its ID for a plain
	 * repost, -1 for a quote (it has a comment, so Undo would delete words), 0 for none.
	 *
	 * @var array<string,int>
	 */
	private static array $mine = array();

	/**
	 * Create a repost.
	 *
	 * @param int    $user_id     Reposting member.
	 * @param string $object_type 'activity' | 'post'.
	 * @param int    $object_id   Original ID.
	 * @param array  $args        destination ('profile'|'group'), group_id, comment.
	 * @return int|WP_Error New activity ID.
	 */
	public static function repost( int $user_id, string $object_type, int $object_id, array $args = array() ) {
		if ( ! Settings::feature( 'repost' ) ) {
			return self::error( 'bpas_repost_off', __( 'Reposting is turned off.', 'buddypress-activity-share-pro' ), 403 );
		}
		if ( $user_id <= 0 ) {
			return self::error( 'bpas_login', __( 'Please log in to repost.', 'buddypress-activity-share-pro' ), 401 );
		}

		$target = self::resolve_target( $user_id, $object_type, $object_id );
		if ( is_wp_error( $target ) ) {
			return $target;
		}

		$destination = 'group' === ( $args['destination'] ?? 'profile' ) ? 'group' : 'profile';
		$group_id    = 'group' === $destination ? absint( $args['group_id'] ?? 0 ) : 0;

		$check = self::check_destination( $user_id, $target, $group_id );
		if ( is_wp_error( $check ) ) {
			return $check;
		}

		if ( self::already_reposted( $user_id, $target, $group_id ) ) {
			return self::error( 'bpas_already_reposted', __( 'You already reposted this here.', 'buddypress-activity-share-pro' ), 409 );
		}

		/** Return a WP_Error to stop the repost. */
		$pre = apply_filters( 'bpas_pro_before_reshare', null, $user_id, $target, $group_id, $args );
		if ( is_wp_error( $pre ) ) {
			return $pre;
		}

		$group   = $group_id ? groups_get_group( $group_id ) : null;
		$comment = trim( (string) ( $args['comment'] ?? '' ) );

		$activity_id = bp_activity_add(
			array(
				'user_id'           => $user_id,
				'component'         => $group_id ? 'groups' : 'activity',
				'type'              => 'post' === $target['type'] ? 'post_share' : 'activity_share',
				'content'           => '' === $comment ? '' : bp_activity_filter_kses( wp_unslash( $comment ) ),
				'item_id'           => $group_id,
				'secondary_item_id' => $target['id'],
				'hide_sitewide'     => $group && 'public' !== $group->status,
				'action'            => '',
			)
		);
		if ( ! $activity_id ) {
			return self::error( 'bpas_repost_failed', __( 'The repost could not be saved. Please try again.', 'buddypress-activity-share-pro' ), 500 );
		}

		// Remember a reply was reposted, so the fallback still says "reply" if it is deleted later.
		if ( 'activity' === $target['type'] && 'activity_comment' === $target['object']->type ) {
			bp_activity_update_meta( (int) $activity_id, '_bpas_original_type', 'activity_comment' );
		}

		// Mentions in the comment notify, like any update.
		if ( '' !== $comment && function_exists( 'bp_activity_at_name_filter_updates' ) ) {
			$new = new \BP_Activity_Activity( (int) $activity_id );
			bp_activity_at_name_filter_updates( $new );
		}

		self::bump_count( $target['type'], $target['id'], 1 );
		do_action( 'bpas_pro_after_reshare', (int) $activity_id, $user_id, $target, $group_id );
		return (int) $activity_id;
	}

	/**
	 * Undo a repost (author only).
	 *
	 * @param int $user_id     Member.
	 * @param int $activity_id Repost activity ID.
	 * @return true|WP_Error
	 */
	public static function undo( int $user_id, int $activity_id ) {
		$activity = new \BP_Activity_Activity( $activity_id );
		if ( empty( $activity->id ) || ! in_array( $activity->type, array( 'activity_share', 'post_share' ), true ) ) {
			return self::error( 'bpas_not_found', __( 'Repost not found.', 'buddypress-activity-share-pro' ), 404 );
		}
		if ( (int) $activity->user_id !== $user_id && ! current_user_can( 'bp_moderate' ) ) {
			return self::error( 'bpas_forbidden', __( 'You can only undo your own reposts.', 'buddypress-activity-share-pro' ), 403 );
		}
		bp_activity_delete( array( 'id' => $activity_id ) );
		self::bump_count( 'post_share' === $activity->type ? 'post' : 'activity', (int) $activity->secondary_item_id, -1 );
		do_action( 'bpas_pro_after_undo_reshare', $activity_id, $user_id, 'post_share' === $activity->type ? 'post' : 'activity', (int) $activity->secondary_item_id );
		return true;
	}

	/**
	 * Send an item to a friend by private message.
	 *
	 * @param int    $user_id     Sender.
	 * @param string $object_type 'activity' | 'post'.
	 * @param int    $object_id   Item ID.
	 * @param int    $friend_id   Recipient.
	 * @param string $note        Optional note.
	 * @return int|WP_Error Message thread ID.
	 */
	public static function send( int $user_id, string $object_type, int $object_id, int $friend_id, string $note = '' ) {
		if ( ! Settings::feature( 'send_friend' ) || ! bp_is_active( 'messages' ) ) {
			return self::error( 'bpas_send_off', __( 'Sending to friends is turned off.', 'buddypress-activity-share-pro' ), 403 );
		}
		if ( $user_id <= 0 ) {
			return self::error( 'bpas_login', __( 'Please log in to send this.', 'buddypress-activity-share-pro' ), 401 );
		}
		if ( $friend_id <= 0 || $friend_id === $user_id || ! get_userdata( $friend_id ) ) {
			return self::error( 'bpas_friend', __( 'Choose a friend to send this to.', 'buddypress-activity-share-pro' ), 400 );
		}
		if ( bp_is_active( 'friends' ) && ! friends_check_friendship( $user_id, $friend_id ) ) {
			return self::error( 'bpas_not_friend', __( 'You can only send posts to your friends.', 'buddypress-activity-share-pro' ), 403 );
		}

		$target = self::resolve_target( $user_id, $object_type, $object_id, false );
		if ( is_wp_error( $target ) ) {
			return $target;
		}
		if ( ! self::can_read( $target, $friend_id ) ) {
			return self::error( 'bpas_friend_cannot_read', __( 'Your friend cannot see this post, so it cannot be sent to them.', 'buddypress-activity-share-pro' ), 403 );
		}

		$ctx     = bpas_share_context( $target['type'], $target['id'] );
		$content = trim( sanitize_textarea_field( $note ) . "\n\n" . ( $ctx ? $ctx->url : '' ) );
		$thread  = messages_new_message(
			array(
				'sender_id'  => $user_id,
				'recipients' => array( $friend_id ),
				/* translators: %s: title of the shared item. */
				'subject'    => sprintf( __( 'Shared with you: %s', 'buddypress-activity-share-pro' ), $ctx ? wp_html_excerpt( $ctx->title, 80, '...' ) : '' ),
				'content'    => $content,
				'error_type' => 'wp_error',
			)
		);
		if ( is_wp_error( $thread ) || ! $thread ) {
			return self::error( 'bpas_send_failed', __( 'The message could not be sent. Please try again.', 'buddypress-activity-share-pro' ), 500 );
		}

		do_action( 'bpas_pro_after_send', (int) $thread, $user_id, $friend_id, $target );
		return (int) $thread;
	}

	/**
	 * Resolve what is really being reposted, applying the read + "repost the original" rules.
	 *
	 * @param int    $user_id      Member.
	 * @param string $object_type  'activity' | 'post'.
	 * @param int    $object_id    ID.
	 * @param bool   $check_author Apply the author opt-out (reposts only).
	 * @return array|WP_Error {type, id, object, group_id, author_id}
	 */
	public static function resolve_target( int $user_id, string $object_type, int $object_id, bool $check_author = true ) {
		if ( 'post' === $object_type ) {
			$post = get_post( $object_id );
			if ( ! $post || ! is_post_publicly_viewable( $post ) || ! array_key_exists( $post->post_type, Settings::content_types() ) ) {
				return self::error( 'bpas_not_found', __( 'This post is not available.', 'buddypress-activity-share-pro' ), 404 );
			}
			$target = array(
				'type'      => 'post',
				'id'        => (int) $post->ID,
				'object'    => $post,
				'group_id'  => 0,
				'author_id' => (int) $post->post_author,
			);
		} else {
			$activity = self::original_activity( $object_id );
			if ( ! $activity ) {
				return self::error( 'bpas_not_found', __( 'This post is not available.', 'buddypress-activity-share-pro' ), 404 );
			}

			// Repost of a repost -> the original.
			if ( in_array( $activity->type, array( 'activity_share', 'post_share' ), true ) && $activity->secondary_item_id ) {
				return self::resolve_target( $user_id, 'post_share' === $activity->type ? 'post' : 'activity', (int) $activity->secondary_item_id, $check_author );
			}
			if ( 'activity_comment' === $activity->type && ! Settings::feature( 'reply_share' ) ) {
				return self::error( 'bpas_reply_off', __( 'Sharing replies is turned off.', 'buddypress-activity-share-pro' ), 403 );
			}

			$target = array(
				'type'      => 'activity',
				'id'        => (int) $activity->id,
				'object'    => $activity,
				'group_id'  => self::group_of( $activity ),
				'author_id' => (int) $activity->user_id,
			);
		}

		if ( ! self::can_read( $target, $user_id ) ) {
			return self::error( 'bpas_not_found', __( 'This post is not available.', 'buddypress-activity-share-pro' ), 404 );
		}

		if ( $check_author && $target['author_id'] !== $user_id && 'no' === get_user_meta( $target['author_id'], 'bpas_pro_allow_repost', true ) ) {
			return self::error( 'bpas_author_opt_out', __( 'The author does not allow reposts of their posts.', 'buddypress-activity-share-pro' ), 403 );
		}

		if ( ! (bool) apply_filters( 'bpas_pro_user_can_repost', true, $user_id, $target ) ) {
			return self::error( 'bpas_forbidden', __( 'You cannot repost this.', 'buddypress-activity-share-pro' ), 403 );
		}

		return $target;
	}

	/**
	 * Destination rules.
	 *
	 * @param int   $user_id  Member.
	 * @param array $target   Resolved target.
	 * @param int   $group_id Destination group (0 = own profile).
	 * @return true|WP_Error
	 */
	private static function check_destination( int $user_id, array $target, int $group_id ) {
		$source_group = (int) $target['group_id'];

		if ( $source_group ) {
			$group = groups_get_group( $source_group );
			$stay  = 'public' !== $group->status || 'no' === groups_get_groupmeta( $source_group, 'bpas_allow_repost_out' );
			if ( $stay && $group_id !== $source_group ) {
				return self::error( 'bpas_stay_in_group', __( 'Posts from this group can only be reposted inside the group.', 'buddypress-activity-share-pro' ), 403 );
			}
		}

		if ( $target['author_id'] === $user_id && ! $group_id ) {
			return self::error( 'bpas_own_post', __( 'You can repost your own posts into a group.', 'buddypress-activity-share-pro' ), 403 );
		}

		if ( $group_id ) {
			if ( ! bp_is_active( 'groups' ) || ! groups_is_user_member( $user_id, $group_id ) || groups_is_user_banned( $user_id, $group_id ) ) {
				return self::error( 'bpas_not_member', __( 'You can only repost into groups you belong to.', 'buddypress-activity-share-pro' ), 403 );
			}
		}
		return true;
	}

	/**
	 * Has the member already reposted this original to the same place?
	 *
	 * @param int   $user_id  Member.
	 * @param array $target   Original.
	 * @param int   $group_id Destination group (0 = profile).
	 */
	private static function already_reposted( int $user_id, array $target, int $group_id ): bool {
		$found = \BP_Activity_Activity::get(
			array(
				'filter'           => array(
					'user_id'      => $user_id,
					'object'       => $group_id ? 'groups' : 'activity',
					'action'       => 'post' === $target['type'] ? 'post_share' : 'activity_share',
					'primary_id'   => $group_id,
					'secondary_id' => (int) $target['id'],
				),
				'per_page'         => 1,
				'show_hidden'      => true,
				'display_comments' => false,
				'count_total'      => false,
			)
		);
		return ! empty( $found['activities'] );
	}

	/**
	 * Can a user read the target?
	 *
	 * @param array $target  Target.
	 * @param int   $user_id User.
	 */
	public static function can_read( array $target, int $user_id ): bool {
		if ( 'post' === $target['type'] ) {
			return is_post_publicly_viewable( $target['object'] );
		}
		$activity = $target['object'];
		if ( ! empty( $activity->is_spam ) ) {
			return false;
		}
		// Comments inherit the root post's privacy.
		if ( 'activity_comment' === $activity->type && $activity->item_id ) {
			$root = self::original_activity( (int) $activity->item_id );
			return $root && bp_activity_user_can_read( $root, $user_id );
		}
		return bp_activity_user_can_read( $activity, $user_id );
	}

	/**
	 * Group of an activity (a reply's is its root's), 0 when none.
	 *
	 * @param object $activity Activity.
	 */
	public static function group_of( object $activity ): int {
		if ( 'activity_comment' === $activity->type && $activity->item_id ) {
			$root = self::original_activity( (int) $activity->item_id );
			return $root ? self::group_of( $root ) : 0;
		}
		return 'groups' === $activity->component ? (int) $activity->item_id : 0;
	}

	/**
	 * Load an activity (request cache; the stream prefetches in one query).
	 *
	 * @param int $id Activity ID.
	 * @return object|null
	 */
	public static function original_activity( int $id ): ?object {
		if ( $id <= 0 ) {
			return null;
		}
		if ( ! array_key_exists( $id, self::$originals ) ) {
			$activity               = new \BP_Activity_Activity( $id );
			self::$originals[ $id ] = empty( $activity->id ) ? false : $activity;
		}
		return self::$originals[ $id ] ? self::$originals[ $id ] : null;
	}

	/**
	 * Seed the cache with an activity the loop already loaded (no query).
	 *
	 * @param object $activity Activity.
	 */
	public static function remember( object $activity ): void {
		if ( ! empty( $activity->id ) ) {
			self::$originals[ (int) $activity->id ] = $activity;
		}
	}

	/**
	 * The member's own profile repost of an item (ID, -1 for a quote, 0 = none), so the menu can offer Undo.
	 * The stream primes this for the whole page in one query (prefetch_mine).
	 *
	 * @param int    $user_id Member.
	 * @param string $type    'activity' | 'post'.
	 * @param int    $id      Original ID.
	 */
	public static function my_repost( int $user_id, string $type, int $id ): int {
		$key = $type . ':' . $id;
		if ( ! array_key_exists( $key, self::$mine ) ) {
			self::prefetch_mine( $user_id, $type, array( $id ) );
		}
		return self::$mine[ $key ];
	}

	/**
	 * Load the member's profile reposts of many items in one query.
	 *
	 * @param int    $user_id Member.
	 * @param string $type    'activity' | 'post'.
	 * @param int[]  $ids     Original IDs.
	 */
	public static function prefetch_mine( int $user_id, string $type, array $ids ): void {
		$ids = array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
		if ( ! $user_id || empty( $ids ) ) {
			return;
		}
		foreach ( $ids as $id ) {
			self::$mine[ $type . ':' . $id ] = 0;
		}
		$found = \BP_Activity_Activity::get(
			array(
				'filter'           => array(
					'user_id'      => $user_id,
					'object'       => 'activity',
					'action'       => 'post' === $type ? 'post_share' : 'activity_share',
					'secondary_id' => $ids,
				),
				'per_page'         => count( $ids ),
				'show_hidden'      => true,
				'display_comments' => false,
				'count_total'      => false,
				'update_meta_cache' => false,
			)
		);
		foreach ( (array) ( $found['activities'] ?? array() ) as $repost ) {
			self::$mine[ $type . ':' . (int) $repost->secondary_item_id ] = '' === trim( wp_strip_all_tags( (string) $repost->content ) ) ? (int) $repost->id : -1;
		}
	}

	/**
	 * Prefetch originals for many reposts in one query.
	 *
	 * @param int[] $ids Activity IDs.
	 */
	public static function prefetch( array $ids ): void {
		$ids = array_values( array_diff( array_unique( array_filter( array_map( 'intval', $ids ) ) ), array_keys( self::$originals ) ) );
		if ( empty( $ids ) ) {
			return;
		}
		$found = \BP_Activity_Activity::get(
			array(
				'in'               => $ids,
				'per_page'         => count( $ids ),
				'show_hidden'      => true,
				'display_comments' => 'stream',
				'spam'             => 'all',
				'filter_query'     => false,
			)
		);
		foreach ( $ids as $id ) {
			self::$originals[ $id ] = false;
		}
		foreach ( (array) ( $found['activities'] ?? array() ) as $activity ) {
			self::$originals[ (int) $activity->id ] = $activity;
		}
	}

	/**
	 * Current share count for an item.
	 *
	 * @param string $type 'activity' | 'post'.
	 * @param int    $id   ID.
	 */
	public static function count( string $type, int $id ): int {
		return 'post' === $type ? (int) get_post_meta( $id, self::COUNT_META, true ) : (int) bp_activity_get_meta( $id, self::COUNT_META, true );
	}

	/**
	 * Change a share count.
	 *
	 * @param string $type  'activity' | 'post'.
	 * @param int    $id    ID.
	 * @param int    $delta +1 / -1.
	 */
	public static function bump_count( string $type, int $id, int $delta ): void {
		$count = max( 0, self::count( $type, $id ) + $delta );
		if ( 'post' === $type ) {
			update_post_meta( $id, self::COUNT_META, $count );
		} else {
			bp_activity_update_meta( $id, self::COUNT_META, $count );
		}
		wp_cache_set_last_changed( 'bpas_pro_events' );
	}

	/**
	 * WP_Error with an HTTP status.
	 *
	 * @param string $code    Code.
	 * @param string $message Message.
	 * @param int    $status  HTTP status.
	 */
	private static function error( string $code, string $message, int $status ): WP_Error {
		return new WP_Error( $code, $message, array( 'status' => $status ) );
	}
}
