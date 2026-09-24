<?php
/**
 * Pro front-end assets (depend on Free's handles - no duplicate tokens or menu CSS).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and enqueues only when a Pro row or card is on the page.
 */
final class Assets {

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register' ), 6 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue' ), 11 );
	}

	/**
	 * Register handles.
	 */
	public static function register(): void {
		$min = self::suffix();
		wp_register_style( 'bpas-pro', BPAS_PRO_URL . "assets/css/bpas-pro{$min}.css", array( 'bpas-share' ), BPAS_PRO_VERSION );
		wp_style_add_data( 'bpas-pro', 'rtl', 'replace' );
		wp_style_add_data( 'bpas-pro', 'suffix', $min );

		wp_register_script(
			'bpas-pro',
			BPAS_PRO_URL . "assets/js/bpas-pro{$min}.js",
			array( 'wp-api-fetch', 'bpas-share' ),
			BPAS_PRO_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
		wp_add_inline_script(
			'bpas-pro',
			'window.bpasPro = ' . wp_json_encode(
				array(
					'ns'       => bpas_rest_namespace(),
					'root'     => esc_url_raw( rest_url() ),
					'loggedIn' => is_user_logged_in(),
					'track'    => Settings::feature( 'analytics' ),
					'i18n'     => array(
						'repost'        => __( 'Repost', 'buddypress-activity-share-pro' ),
						'repostComment' => __( 'Repost with comment', 'buddypress-activity-share-pro' ),
						'repostGroup'   => __( 'Repost to a group', 'buddypress-activity-share-pro' ),
						'repostHere'    => __( 'Repost in this group', 'buddypress-activity-share-pro' ),
						'send'          => __( 'Send to a friend', 'buddypress-activity-share-pro' ),
						'sendBtn'       => __( 'Send', 'buddypress-activity-share-pro' ),
						'note'          => __( 'Add a note (optional)', 'buddypress-activity-share-pro' ),
						'comment'       => __( 'Add a comment (optional)', 'buddypress-activity-share-pro' ),
						'reposted'      => __( 'Reposted.', 'buddypress-activity-share-pro' ),
						'undo'          => __( 'Undo', 'buddypress-activity-share-pro' ),
						'undoRepost'    => __( 'Undo repost', 'buddypress-activity-share-pro' ),
						'view'          => __( 'View', 'buddypress-activity-share-pro' ),
						'undone'        => __( 'Repost removed.', 'buddypress-activity-share-pro' ),
						'sending'       => __( 'Sending...', 'buddypress-activity-share-pro' ),
						'reposting'     => __( 'Reposting...', 'buddypress-activity-share-pro' ),
						/* translators: %s: friend's name. */
						'sentTo'        => __( 'Sent to %s.', 'buddypress-activity-share-pro' ),
						'chooseGroup'   => __( 'Choose a group', 'buddypress-activity-share-pro' ),
						'noFriendsYet'  => __( 'You have no friends yet. Add friends to send them posts.', 'buddypress-activity-share-pro' ),
						'noReposters'   => __( 'No reposts yet.', 'buddypress-activity-share-pro' ),
						'failed'        => __( 'Something went wrong. Please try again.', 'buddypress-activity-share-pro' ),
						'pickFriend'    => __( 'Choose a friend first.', 'buddypress-activity-share-pro' ),
						'pickGroup'     => __( 'Choose a group first.', 'buddypress-activity-share-pro' ),
						'noFriends'     => __( 'No friends match that name.', 'buddypress-activity-share-pro' ),
						'loading'       => __( 'Loading...', 'buddypress-activity-share-pro' ),
						'reposters'     => __( 'Reposted by', 'buddypress-activity-share-pro' ),
						'login'         => __( 'Join the conversation', 'buddypress-activity-share-pro' ),
					),
				)
			) . ';',
			'before'
		);
	}

	/**
	 * Cards appear in the stream on BuddyPress pages.
	 */
	public static function maybe_enqueue(): void {
		if ( function_exists( 'is_buddypress' ) && is_buddypress() ) {
			wp_enqueue_style( 'bpas-pro' );
		}
	}

	/**
	 * Enqueue both (safe during render).
	 */
	public static function enqueue_front(): void {
		bpas_enqueue_assets();
		if ( ! wp_script_is( 'bpas-pro', 'registered' ) ) {
			self::register();
		}
		wp_enqueue_style( 'bpas-pro' );
		wp_enqueue_script( 'bpas-pro' );
	}

	/**
	 * '.min' unless SCRIPT_DEBUG.
	 */
	private static function suffix(): string {
		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	}
}
