<?php
/**
 * Quoted original inside a repost, with a safe fallback.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Reshare;

defined( 'ABSPATH' ) || exit;

/**
 * Renders after the repost's own comment. The viewer's read access is checked at render time.
 */
final class Reposted_Card {

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_filter( 'bp_has_activities', array( __CLASS__, 'prefetch' ), 10, 2 );
		add_action( 'bp_activity_entry_content', array( __CLASS__, 'render' ) );
	}

	/**
	 * Load every original on the page in one query (no N+1 in the stream).
	 *
	 * @param bool   $has      Has activities.
	 * @param object $template Activities template.
	 * @return bool
	 */
	public static function prefetch( $has, $template ) {
		if ( ! $has || empty( $template->activities ) ) {
			return $has;
		}
		$activity_ids = array();
		$post_ids     = array();
		$group_ids    = array();
		// Every item on the page, replies included, so each menu knows "already reposted" for free.
		Reshare_Service::prefetch_mine( get_current_user_id(), 'activity', self::ids_with_replies( $template->activities ) );
		foreach ( $template->activities as $activity ) {
			Reshare_Service::remember( $activity );
			if ( 'groups' === $activity->component ) {
				$group_ids[] = (int) $activity->item_id;
			}
			if ( 'activity_share' === $activity->type ) {
				$activity_ids[] = (int) $activity->secondary_item_id;
			} elseif ( 'post_share' === $activity->type ) {
				$post_ids[] = (int) $activity->secondary_item_id;
			}
		}
		Reshare_Service::prefetch( $activity_ids );
		if ( $post_ids ) {
			_prime_post_caches( array_unique( $post_ids ), false, true );
		}
		// Group sharing controls are read per group: load them all at once.
		if ( $group_ids && function_exists( 'bp_groups_update_meta_cache' ) ) {
			bp_groups_update_meta_cache( array_unique( $group_ids ) );
		}
		return $has;
	}

	/**
	 * IDs of the loop's activities and their threaded replies.
	 *
	 * @param array $activities Activities (with ->children when threaded).
	 * @return int[]
	 */
	private static function ids_with_replies( array $activities ): array {
		$ids = array();
		foreach ( $activities as $activity ) {
			$ids[] = (int) $activity->id;
			if ( ! empty( $activity->children ) ) {
				$ids = array_merge( $ids, self::ids_with_replies( (array) $activity->children ) );
			}
		}
		return $ids;
	}

	/**
	 * Print the card for the current loop item.
	 */
	public static function render(): void {
		$activity = $GLOBALS['activities_template']->activity ?? null;
		if ( ! $activity || ! in_array( $activity->type, array( 'activity_share', 'post_share' ), true ) ) {
			return;
		}
		echo self::card( $activity ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built escaped.
	}

	/**
	 * Card HTML for a repost.
	 *
	 * @param object $repost Repost activity.
	 */
	public static function card( object $repost ): string {
		$user_id = get_current_user_id();
		$id      = (int) $repost->secondary_item_id;

		if ( 'post_share' === $repost->type ) {
			$post = get_post( $id );
			if ( ! $post || ! is_post_publicly_viewable( $post ) ) {
				return self::unavailable( __( 'This post is no longer available.', 'buddypress-activity-share-pro' ) );
			}
			$thumb = get_the_post_thumbnail(
				$post,
				'medium',
				array(
					'class'   => 'bpas-pro-card__thumb',
					'loading' => 'lazy',
					'alt'     => '',
				)
			);
			return sprintf(
				'<a class="bpas-pro-card bpas-pro-card--post" href="%1$s">%2$s<span class="bpas-pro-card__body"><strong class="bpas-pro-card__title">%3$s</strong><span class="bpas-pro-card__text">%4$s</span></span></a>',
				esc_url( get_permalink( $post ) ),
				$thumb,
				esc_html( get_the_title( $post ) ),
				esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post ) ), 30 ) )
			);
		}

		$original = Reshare_Service::original_activity( $id );
		$is_reply = $original ? 'activity_comment' === $original->type : 'activity_comment' === bp_activity_get_meta( (int) $repost->id, '_bpas_original_type', true );
		$target   = $original ? array(
			'type'   => 'activity',
			'object' => $original,
		) : null;

		if ( ! $target || ! Reshare_Service::can_read( $target, $user_id ) ) {
			return self::unavailable( $is_reply ? __( 'This reply is no longer available.', 'buddypress-activity-share-pro' ) : __( 'This post is no longer available.', 'buddypress-activity-share-pro' ) );
		}

		$author = (int) $original->user_id;
		// Items without text (joined a group, new friendship...) show their action line instead.
		$text = trim( wp_strip_all_tags( (string) $original->content ) );
		if ( '' === $text ) {
			$text = trim( wp_strip_all_tags( (string) $original->action ) );
		}
		return sprintf(
			'<div class="bpas-pro-card"><div class="bpas-pro-card__head">%1$s<span><a class="bpas-pro-card__author" href="%2$s">%3$s</a> <span class="bpas-pro-card__time">%4$s</span></span></div><div class="bpas-pro-card__text">%5$s</div><a class="bpas-pro-card__link" href="%6$s">%7$s</a></div>',
			bp_core_fetch_avatar(
				array(
					'item_id' => $author,
					'type'    => 'thumb',
					'width'   => 32,
					'height'  => 32,
					'class'   => 'bpas-pro-card__avatar',
				)
			),
			esc_url( bpas_member_url( $author ) ),
			esc_html( bp_core_get_user_displayname( $author ) ),
			esc_html( bp_core_time_since( $original->date_recorded ) ),
			wp_kses_post( wpautop( esc_html( wp_trim_words( $text, 60 ) ) ) ),
			esc_url( bp_activity_get_permalink( (int) $original->id, $original ) ),
			$is_reply ? esc_html__( 'View reply', 'buddypress-activity-share-pro' ) : esc_html__( 'View post', 'buddypress-activity-share-pro' )
		);
	}

	/**
	 * Placeholder when the original is gone or private to the viewer.
	 *
	 * @param string $message Message.
	 */
	private static function unavailable( string $message ): string {
		return '<div class="bpas-pro-card bpas-pro-card--gone">' . esc_html( $message ) . '</div>';
	}
}
