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
		$count = self::count_for( $ctx );
		if ( $count > 0 ) {
			printf(
				'<span class="bpas-pro-count" aria-label="%1$s">%2$s</span>',
				/* translators: %s: number of shares. */
				esc_attr( sprintf( _n( '%s share', '%s shares', $count, 'buddypress-activity-share-pro' ), number_format_i18n( $count ) ) ),
				esc_html( number_format_i18n( $count ) )
			);
		}
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
