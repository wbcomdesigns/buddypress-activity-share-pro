<?php
/**
 * Pro bootstrap. Everything reaches Free through its public API and hooks only.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro;

defined( 'ABSPATH' ) || exit;

/**
 * Wires modules. Each module loads only when its feature is on.
 */
final class Plugin {

	/**
	 * Called from bpas_pro_boot() once Free is compatible.
	 */
	public static function boot(): void {
		add_action( 'init', array( __CLASS__, 'load_textdomain' ), 1 );
		Upgrade::maybe_run();

		add_action( 'bp_register_activity_actions', array( Reshare\Activity_Types::class, 'register' ) );
		add_action( 'bp_init', array( __CLASS__, 'load_modules' ), 20 );
		add_action( 'rest_api_init', array( Api\Rest_Controller::class, 'register' ) );
		add_action( 'rest_api_init', array( Api\Admin_Controller::class, 'register' ) );
		add_action( 'init', array( Posts\Post_Share::class, 'register_block' ) );
		add_shortcode( 'bpas_share', array( Posts\Post_Share::class, 'shortcode' ) );

		Analytics\Trending::init();
		Rewards\Rewards::init();
		Privacy\Privacy::init();
		Jobs\Cleanup::init();

		if ( is_admin() ) {
			Admin\Admin::init();
		}
	}

	/**
	 * Translations bundled with Pro (not hosted on wp.org).
	 */
	public static function load_textdomain(): void {
		load_plugin_textdomain( 'buddypress-activity-share-pro', false, dirname( plugin_basename( BPAS_PRO_FILE ) ) . '/languages' );
	}

	/**
	 * Modules that need BuddyPress components.
	 */
	public static function load_modules(): void {
		Assets::init();
		Posts\Post_Share::init();

		if ( bp_is_active( 'activity' ) ) {
			if ( Settings::feature( 'repost' ) || Settings::feature( 'send_friend' ) ) {
				Reshare\Menu_Item::init();
				Reshare\Composer::init();
			}
			Reshare\Reposted_Card::init();
			if ( Settings::feature( 'repost' ) && bp_is_active( 'settings' ) ) {
				Reshare\Member_Settings::init();
			}
			if ( Settings::feature( 'reply_share' ) && Settings::feature( 'repost' ) ) {
				Reshare\Reply_Share::init();
			}
			if ( Settings::feature( 'counts' ) ) {
				Reshare\Counts::init();
			}
			Notifications\Notifications::init();
		}
		if ( bp_is_active( 'groups' ) ) {
			Groups\Group_Controls::init();
		}
		Tracking\Tracker::init_internal();
		if ( Settings::feature( 'analytics' ) ) {
			Tracking\Tracker::init_external();
		}
		if ( Settings::feature( 'short_links' ) ) {
			Links\Short_Links::init();
		}
		if ( Settings::feature( 'image_card' ) ) {
			Cards\Image_Card::init();
		}
	}
}
