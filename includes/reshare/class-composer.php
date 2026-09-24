<?php
/**
 * Repost / send / log-in dialog - printed once per page, only when a menu offered a Pro row.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Reshare;

use BPAS_Pro\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * Native <dialog>: focus trap + Escape for free.
 */
final class Composer {

	/**
	 * Whether a menu on this page needs the dialog.
	 *
	 * @var bool
	 */
	private static bool $needed = false;

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'prepare_on_buddypress' ), 12 );
		add_action( 'wp_footer', array( __CLASS__, 'print_dialog' ), 5 );
	}

	/**
	 * BuddyPress loads the stream by AJAX, so rows are rendered in another request:
	 * prepare the dialog and script on every BuddyPress page up front.
	 */
	public static function prepare_on_buddypress(): void {
		if ( function_exists( 'is_buddypress' ) && is_buddypress() ) {
			self::needed();
		}
	}

	/**
	 * Called by the menu rows.
	 */
	public static function needed(): void {
		if ( self::$needed ) {
			return;
		}
		self::$needed = true;
		Assets::enqueue_front();
		if ( is_user_logged_in() && wp_script_is( 'bp-mentions', 'registered' ) ) {
			wp_enqueue_script( 'bp-mentions' );
			wp_enqueue_style( 'bp-mentions-css' );
		}
	}

	/**
	 * Print the dialog.
	 */
	public static function print_dialog(): void {
		if ( ! self::$needed ) {
			return;
		}
		$view = array(
			'logged_in'    => is_user_logged_in(),
			'login_url'    => wp_login_url( (string) ( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- passed through wp_login_url (esc_url on output).
			'register_url' => function_exists( 'bp_get_signup_page' ) && bp_get_signup_allowed() ? bp_get_signup_page() : '',
			'groups_on'    => bp_is_active( 'groups' ),
		);
		include BPAS_PRO_DIR . 'templates/composer.php';
	}
}
