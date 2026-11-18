<?php
/**
 * Share on activity replies (X7) - reuses Free's share menu for the reply.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Reshare;

defined( 'ABSPATH' ) || exit;

/**
 * Adds the share menu to each reply's options.
 */
final class Reply_Share {

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_action( 'bp_activity_comment_options', array( __CLASS__, 'render' ) );
	}

	/**
	 * Print the menu for the current reply.
	 */
	public static function render(): void {
		$comment = function_exists( 'bp_activity_current_comment' ) ? bp_activity_current_comment() : null;
		if ( ! is_object( $comment ) || empty( $comment->id ) ) {
			return;
		}
		$ctx = bpas_activity_share_context( $comment );
		if ( $ctx ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Free's template escapes.
			echo bpas_render_share_menu(
				$ctx,
				array(
					'class'    => 'bpas-share--reply',
					'position' => 'after',
				)
			);
		}
	}
}
