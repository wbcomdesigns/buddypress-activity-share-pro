<?php
/**
 * Repost / Send rows inside Free's share menu (via bpas_share_menu_items).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Reshare;

use BPAS_Pro\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Rows reuse Free's `.bpas-share__link` markup contract, so no duplicate styling.
 */
final class Menu_Item {

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_action( 'bpas_share_menu_items', array( __CLASS__, 'render' ), 10, 2 );
	}

	/**
	 * Print the rows.
	 *
	 * @param object $ctx      Free's share context (type, id, object).
	 * @param bool   $external Whether outside sharing is allowed.
	 */
	public static function render( $ctx, bool $external ): void {
		if ( ! in_array( $ctx->type, array( 'activity', 'post' ), true ) ) {
			return;
		}
		if ( 'post' === $ctx->type && ! array_key_exists( get_post_type( $ctx->id ), Settings::content_types() ) ) {
			return;
		}

		$repost = Settings::feature( 'repost' );
		$send   = Settings::feature( 'send_friend' ) && bp_is_active( 'messages' ) && $external;
		$rows   = array();
		$data   = sprintf( 'data-bpas-object="%s" data-bpas-object-id="%d"', esc_attr( $ctx->type ), (int) $ctx->id );

		if ( ! is_user_logged_in() ) {
			if ( $repost ) {
				$rows[] = self::row( 'repeat', _x( 'Repost', 'verb', 'buddypress-activity-share-pro' ), 'data-bpas-login ' . $data );
			}
			if ( $send ) {
				$rows[] = self::row( 'send', __( 'Send to a friend', 'buddypress-activity-share-pro' ), 'data-bpas-login ' . $data );
			}
		} else {
			$user_id = get_current_user_id();
			if ( 'activity' === $ctx->type ) {
				Reshare_Service::remember( $ctx->object );
			}
			$target = Reshare_Service::resolve_target( $user_id, $ctx->type, (int) $ctx->id );
			if ( is_wp_error( $target ) ) {
				$repost = false;
			}

			if ( $repost ) {
				$own      = $target['author_id'] === $user_id;
				$in_group = (int) $target['group_id'];
				$stay     = $in_group && ( ! $external || 'no' === groups_get_groupmeta( $in_group, 'bpas_allow_repost_out' ) );

				if ( $stay ) {
					$rows[] = self::row( 'repeat', __( 'Repost in this group', 'buddypress-activity-share-pro' ), sprintf( 'data-bpas-compose data-bpas-group="%d" ', $in_group ) . $data );
				} elseif ( $own ) {
					// Own posts go only to a group, so members without one get no repost row.
					if ( bp_is_active( 'groups' ) && bp_get_total_group_count_for_user( $user_id ) > 0 ) {
						$rows[] = self::row( 'repeat', __( 'Repost to a group', 'buddypress-activity-share-pro' ), 'data-bpas-compose data-bpas-groups-only ' . $data );
					}
				} else {
					// Plain repost -> Undo; quote (-1) -> no quick row, the quote is managed from the post itself.
					$mine = Reshare_Service::my_repost( $user_id, $ctx->type, (int) $ctx->id );
					if ( $mine > 0 ) {
						$rows[] = self::row( 'repeat', __( 'Undo repost', 'buddypress-activity-share-pro' ), sprintf( 'data-bpas-undo="%d" ', $mine ) . $data );
					} elseif ( 0 === $mine ) {
						$rows[] = self::row( 'repeat', _x( 'Repost', 'verb', 'buddypress-activity-share-pro' ), 'data-bpas-repost ' . $data );
					}
					$rows[] = self::row( 'message-square-quote', __( 'Repost with comment', 'buddypress-activity-share-pro' ), 'data-bpas-compose ' . $data );
				}
			}
			if ( $send ) {
				$rows[] = self::row( 'send', __( 'Send to a friend', 'buddypress-activity-share-pro' ), 'data-bpas-send ' . $data );
			}
		}

		if ( $rows ) {
			Composer::needed();
			echo '<ul class="bpas-share__list bpas-pro-rows">' . implode( '', $rows ) . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rows escaped in row().
		}
	}

	/**
	 * One row.
	 *
	 * @param string $icon  UI icon name.
	 * @param string $label Label.
	 * @param string $attrs Pre-escaped data attributes.
	 */
	private static function row( string $icon, string $label, string $attrs ): string {
		return sprintf(
			'<li class="bpas-share__item"><button type="button" class="bpas-share__link bpas-pro-row" %1$s>%2$s<span class="bpas-share__name">%3$s</span></button></li>',
			$attrs,
			bpas_icon( $icon ),
			esc_html( $label )
		);
	}
}
