<?php
/**
 * Records shares, reposts and sends (only when the owner turned analytics on).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Tracking;

use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/**
 * No raw IPs: visitors are a salted daily hash. Public items only. Deduplicated per hour.
 */
final class Tracker {

	/**
	 * Members' own reposts and sends: always recorded (no visitor data) - trending and counts use them.
	 */
	public static function init_internal(): void {
		add_action( 'bpas_pro_after_reshare', array( __CLASS__, 'on_repost' ), 20, 4 );
		add_action( 'bpas_pro_after_send', array( __CLASS__, 'on_send' ), 20, 4 );
		add_action( 'bpas_pro_after_undo_reshare', array( __CLASS__, 'on_undo' ), 20, 4 );
	}

	/**
	 * Undo removes the repost event.
	 *
	 * @param int    $activity_id Repost.
	 * @param int    $user_id     Member.
	 * @param string $type        Original type.
	 * @param int    $id          Original ID.
	 */
	public static function on_undo( int $activity_id, int $user_id, string $type = 'activity', int $id = 0 ): void {
		unset( $activity_id );
		Events::delete_last( 'repost', $type, $id, $user_id );
	}

	/**
	 * Clicks to other sites and visits from shared links: only when the owner turned analytics on.
	 */
	public static function init_external(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register_route' ) );
	}

	/**
	 * POST bpas/v1/track (public, beacon from the share menu).
	 */
	public static function register_route(): void {
		register_rest_route(
			bpas_rest_namespace(),
			'/track',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'track' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'object_type' => array(
						'type'     => 'string',
						'enum'     => array( 'activity', 'post', 'member', 'group' ),
						'required' => true,
					),
					'object_id'   => array(
						'type'     => 'integer',
						'minimum'  => 1,
						'required' => true,
					),
					'network'     => array(
						'type'     => 'string',
						'required' => true,
					),
				),
			)
		);
	}

	/**
	 * Record a network share click.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public static function track( WP_REST_Request $request ) {
		$type    = (string) $request['object_type'];
		$id      = (int) $request['object_id'];
		$network = sanitize_key( (string) $request['network'] );
		$known   = array_merge( array_keys( bpas_networks( false ) ), array( 'native' ) );

		if ( ! in_array( $network, $known, true ) || ! in_array( $type, array( 'activity', 'post' ), true ) ) {
			return rest_ensure_response( array( 'recorded' => false ) );
		}
		$ctx = bpas_share_context( $type, $id );
		if ( ! $ctx || ! bpas_can_share_externally( $ctx ) ) {
			return rest_ensure_response( array( 'recorded' => false ) );
		}

		$hash = self::visitor_hash();
		$key  = 'bpas_t_' . md5( $hash . '|' . $type . $id . '|' . $network );
		if ( get_transient( $key ) ) {
			return rest_ensure_response( array( 'recorded' => false ) );
		}
		set_transient( $key, 1, HOUR_IN_SECONDS );

		Events::add( 'share', $type, $id, $network, get_current_user_id(), $hash );
		return rest_ensure_response( array( 'recorded' => true ) );
	}

	/**
	 * Repost event.
	 *
	 * @param int   $repost_id Repost.
	 * @param int   $user_id   Member.
	 * @param array $target    Original.
	 */
	public static function on_repost( int $repost_id, int $user_id, array $target ): void {
		unset( $repost_id );
		Events::add( 'repost', $target['type'], (int) $target['id'], 'repost', $user_id );
	}

	/**
	 * Send event.
	 *
	 * @param int   $thread    Message thread.
	 * @param int   $user_id   Sender.
	 * @param int   $friend_id Recipient.
	 * @param array $target    Item.
	 */
	public static function on_send( int $thread, int $user_id, int $friend_id, array $target ): void {
		unset( $thread, $friend_id );
		Events::add( 'send', $target['type'], (int) $target['id'], 'message', $user_id );
	}

	/**
	 * Salted, daily-rotating visitor hash. Never stores the IP.
	 */
	public static function visitor_hash(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		/** Sites behind a trusted proxy may return the forwarded client IP here. */
		$ip = (string) apply_filters( 'bpas_pro_client_ip', $ip );
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		return hash_hmac( 'sha256', $ip . '|' . $ua . '|' . gmdate( 'Y-m-d' ), wp_salt( 'auth' ) );
	}
}
