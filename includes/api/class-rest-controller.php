<?php
/**
 * REST routes for members (bpas/v1). Same rules as the UI: everything goes through Reshare_Service.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Api;

use BPAS_Pro\Reshare\Reshare_Service;
use WP_Error;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/**
 * Repost, undo, send, my groups, my friends, shares, reposters.
 */
final class Rest_Controller {

	/**
	 * Register routes.
	 */
	public static function register(): void {
		$ns     = bpas_rest_namespace();
		$object = array(
			'object_type' => array(
				'type'     => 'string',
				'enum'     => array( 'activity', 'post' ),
				'required' => true,
			),
			'object_id'   => array(
				'type'     => 'integer',
				'minimum'  => 1,
				'required' => true,
			),
		);
		$paging = array(
			'page'     => array(
				'type'    => 'integer',
				'default' => 1,
				'minimum' => 1,
			),
			'per_page' => array(
				'type'    => 'integer',
				'default' => 20,
				'minimum' => 1,
				'maximum' => 100,
			),
			'search'   => array(
				'type'    => 'string',
				'default' => '',
			),
		);

		register_rest_route(
			$ns,
			'/reshare',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'repost' ),
				'permission_callback' => array( __CLASS__, 'logged_in' ),
				'args'                => $object + array(
					'destination' => array(
						'type'    => 'string',
						'enum'    => array( 'profile', 'group' ),
						'default' => 'profile',
					),
					'group_id'    => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'comment'     => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);
		register_rest_route(
			$ns,
			'/reshare/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( __CLASS__, 'undo' ),
				'permission_callback' => array( __CLASS__, 'logged_in' ),
			)
		);
		register_rest_route(
			$ns,
			'/send',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'send' ),
				'permission_callback' => array( __CLASS__, 'logged_in' ),
				'args'                => $object + array(
					'friend_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
					'note'      => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);
		register_rest_route(
			$ns,
			'/me/groups',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'my_groups' ),
				'permission_callback' => array( __CLASS__, 'logged_in' ),
				'args'                => $paging,
			)
		);
		register_rest_route(
			$ns,
			'/me/friends',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'my_friends' ),
				'permission_callback' => array( __CLASS__, 'logged_in' ),
				'args'                => $paging,
			)
		);
		register_rest_route(
			$ns,
			'/activity/(?P<id>\d+)/shares',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'shares' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			$ns,
			'/activity/(?P<id>\d+)/reposters',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'reposters' ),
				'permission_callback' => '__return_true',
				'args'                => $paging,
			)
		);
	}

	/**
	 * Logged-in members only; actionable 401 for apps.
	 *
	 * @return true|WP_Error
	 */
	public static function logged_in() {
		return is_user_logged_in() ? true : new WP_Error( 'bpas_authentication_required', __( 'Authentication required.', 'buddypress-activity-share-pro' ), array( 'status' => 401 ) );
	}

	/**
	 * POST /reshare.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public static function repost( WP_REST_Request $request ) {
		$id = Reshare_Service::repost(
			get_current_user_id(),
			(string) $request['object_type'],
			(int) $request['object_id'],
			array(
				'destination' => (string) $request['destination'],
				'group_id'    => (int) $request['group_id'],
				'comment'     => (string) $request['comment'],
			)
		);
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$activity = new \BP_Activity_Activity( $id );
		return rest_ensure_response(
			array(
				'id'         => $id,
				'url'        => bp_activity_get_permalink( $id, $activity ),
				'created_at' => mysql_to_rfc3339( $activity->date_recorded ),
			)
		);
	}

	/**
	 * DELETE /reshare/{id}.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public static function undo( WP_REST_Request $request ) {
		$done = Reshare_Service::undo( get_current_user_id(), (int) $request['id'] );
		return is_wp_error( $done ) ? $done : rest_ensure_response( array( 'deleted' => true ) );
	}

	/**
	 * POST /send.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public static function send( WP_REST_Request $request ) {
		$thread = Reshare_Service::send( get_current_user_id(), (string) $request['object_type'], (int) $request['object_id'], (int) $request['friend_id'], (string) $request['note'] );
		if ( is_wp_error( $thread ) ) {
			return $thread;
		}
		return rest_ensure_response(
			array(
				'thread_id' => $thread,
				'url'       => bp_get_message_thread_view_link( $thread, get_current_user_id() ),
			)
		);
	}

	/**
	 * GET /me/groups - groups the member belongs to (paginated, searchable).
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public static function my_groups( WP_REST_Request $request ) {
		if ( ! bp_is_active( 'groups' ) ) {
			return self::envelope( 'groups', array(), 0, $request );
		}
		$result = groups_get_groups(
			array(
				'user_id'      => get_current_user_id(),
				'search_terms' => '' !== $request['search'] ? (string) $request['search'] : false,
				'per_page'     => (int) $request['per_page'],
				'page'         => (int) $request['page'],
				'orderby'      => 'name',
				'order'        => 'ASC',
				'show_hidden'  => true,
			)
		);
		$items  = array();
		foreach ( (array) $result['groups'] as $group ) {
			$items[] = array(
				'id'   => (int) $group->id,
				'name' => html_entity_decode( (string) $group->name, ENT_QUOTES ),
			);
		}
		return self::envelope( 'groups', $items, (int) $result['total'], $request );
	}

	/**
	 * GET /me/friends - the member's friends (paginated, searchable).
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public static function my_friends( WP_REST_Request $request ) {
		if ( ! bp_is_active( 'friends' ) ) {
			return self::envelope( 'friends', array(), 0, $request );
		}
		$ids = friends_get_friend_user_ids( get_current_user_id() );
		if ( empty( $ids ) ) {
			return self::envelope( 'friends', array(), 0, $request );
		}
		$query = new \BP_User_Query(
			array(
				'include'         => $ids,
				'search_terms'    => '' !== $request['search'] ? (string) $request['search'] : false,
				'per_page'        => (int) $request['per_page'],
				'page'            => (int) $request['page'],
				'type'            => 'alphabetical',
				'populate_extras' => false,
			)
		);
		$items = array();
		foreach ( $query->results as $user ) {
			$items[] = self::member( (int) $user->ID );
		}
		return self::envelope( 'friends', $items, (int) $query->total_users, $request );
	}

	/**
	 * GET /activity/{id}/shares.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public static function shares( WP_REST_Request $request ) {
		$target = self::readable_activity( (int) $request['id'] );
		if ( is_wp_error( $target ) ) {
			return $target;
		}
		return rest_ensure_response(
			array(
				'id'    => (int) $request['id'],
				'count' => Reshare_Service::count( 'activity', (int) $request['id'] ),
			)
		);
	}

	/**
	 * GET /activity/{id}/reposters - members who reposted, only reposts the viewer can see.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public static function reposters( WP_REST_Request $request ) {
		$target = self::readable_activity( (int) $request['id'] );
		if ( is_wp_error( $target ) ) {
			return $target;
		}
		$result = \BP_Activity_Activity::get(
			array(
				'filter'           => array(
					'action'       => 'activity_share',
					'secondary_id' => (int) $request['id'],
				),
				'per_page'         => (int) $request['per_page'],
				'page'             => (int) $request['page'],
				'count_total'      => true,
				'display_comments' => false,
				'show_hidden'      => false,
			)
		);
		$items  = array();
		foreach ( (array) $result['activities'] as $repost ) {
			$uid = (int) $repost->user_id;
			// One entry per member (a member may repost to their profile and a group).
			if ( ! isset( $items[ $uid ] ) && bp_activity_user_can_read( $repost, get_current_user_id() ) ) {
				$items[ $uid ] = self::member( $uid );
			}
		}
		$items = array_values( $items );
		return self::envelope( 'reposters', $items, (int) $result['total'], $request );
	}

	/**
	 * Activity the current viewer can read, else 404.
	 *
	 * @param int $id Activity ID.
	 * @return array|WP_Error
	 */
	private static function readable_activity( int $id ) {
		$activity = Reshare_Service::original_activity( $id );
		$target   = $activity ? array(
			'type'   => 'activity',
			'object' => $activity,
		) : null;
		if ( ! $target || ! Reshare_Service::can_read( $target, get_current_user_id() ) ) {
			return new WP_Error( 'bpas_not_found', __( 'This post is not available.', 'buddypress-activity-share-pro' ), array( 'status' => 404 ) );
		}
		return $target;
	}

	/**
	 * Member summary.
	 *
	 * @param int $user_id User ID.
	 */
	private static function member( int $user_id ): array {
		return array(
			'id'     => $user_id,
			'name'   => bp_core_get_user_displayname( $user_id ),
			'url'    => bp_members_get_user_url( $user_id ),
			// BuddyPress returns the URL HTML-escaped (&#038;); JSON consumers need the raw URL.
			'avatar' => esc_url_raw(
				html_entity_decode(
					(string) bp_core_fetch_avatar(
						array(
							'item_id' => $user_id,
							'type'    => 'thumb',
							'html'    => false,
						)
					),
					ENT_QUOTES
				)
			),
		);
	}

	/**
	 * List envelope: { key: [...], total, pages, has_more }.
	 *
	 * @param string          $key     Items key.
	 * @param array           $items   Items.
	 * @param int             $total   Total rows.
	 * @param WP_REST_Request $request Request.
	 */
	private static function envelope( string $key, array $items, int $total, WP_REST_Request $request ) {
		$per_page = max( 1, (int) $request['per_page'] );
		$offset   = ( max( 1, (int) $request['page'] ) - 1 ) * $per_page;
		return rest_ensure_response(
			array(
				$key       => $items,
				'total'    => $total,
				'pages'    => (int) ceil( $total / $per_page ),
				'has_more' => ( $offset + count( $items ) ) < $total,
			)
		);
	}
}
