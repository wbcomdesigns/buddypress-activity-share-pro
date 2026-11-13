<?php
/**
 * Share count next to Share, and "who reposted" (X2).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Reshare;

defined( 'ABSPATH' ) || exit;

/**
 * Count shown only when above zero.
 */
final class Counts {

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_action( 'bpas_share_toggle_after', array( __CLASS__, 'toggle_count' ) );
		add_action( 'bpas_share_menu_items', array( __CLASS__, 'reposters_row' ), 20 );
	}

	/**
	 * Count inside the Share toggle.
	 *
	 * @param object $ctx Share context.
	 */
	public static function toggle_count( $ctx ): void {
		echo self::badge( self::count_for( $ctx ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in badge().
	}

	/**
	 * Count badge markup, or '' for zero. Shared by the toggle and the REST replies so the
	 * badge a member sees after a repost or undo matches the one a page load renders.
	 *
	 * @param int $count Share count.
	 */
	public static function badge( int $count ): string {
		if ( $count < 1 ) {
			return '';
		}
		return sprintf(
			'<span class="bpas-pro-count" aria-label="%1$s">%2$s</span>',
			/* translators: %s: number of shares. */
			esc_attr( sprintf( _n( '%s share', '%s shares', $count, 'buddypress-activity-share-pro' ), number_format_i18n( $count ) ) ),
			esc_html( number_format_i18n( $count ) )
		);
	}

	/**
	 * New count for the object a repost counts toward, for REST replies.
	 *
	 * @param object $repost The repost activity (activity_share / post_share).
	 * @return array{type:string,id:int,count:int,badge:string}
	 */
	public static function for_repost( $repost ): array {
		$type  = 'post_share' === $repost->type ? 'post' : 'activity';
		$id    = (int) $repost->secondary_item_id;
		$count = Reshare_Service::count( $type, $id );
		return array(
			'type'  => $type,
			'id'    => $id,
			'count' => $count,
			'badge' => self::badge( $count ),
		);
	}

	/**
	 * "See who reposted" row.
	 *
	 * @param object $ctx Share context.
	 */
	public static function reposters_row( $ctx ): void {
		if ( 'activity' !== $ctx->type || self::count_for( $ctx ) < 1 ) {
			return;
		}
		Composer::needed();
		printf(
			'<ul class="bpas-share__list bpas-pro-rows"><li class="bpas-share__item"><button type="button" class="bpas-share__link bpas-pro-row" data-bpas-reposters data-bpas-object-id="%1$d">%2$s<span class="bpas-share__name">%3$s</span></button></li></ul>',
			(int) $ctx->id,
			bpas_icon( 'users' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG.
			esc_html__( 'See who reposted', 'buddypress-activity-share-pro' )
		);
	}

	/**
	 * Count for a context.
	 *
	 * @param object $ctx Share context.
	 */
	private static function count_for( $ctx ): int {
		return in_array( $ctx->type, array( 'activity', 'post' ), true ) ? Reshare_Service::count( $ctx->type, (int) $ctx->id ) : 0;
	}
}
