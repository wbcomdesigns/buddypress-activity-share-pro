<?php
/**
 * Plugin Name:       Activity Share Pro
 * Plugin URI:        https://wbcomdesigns.com/downloads/buddypress-activity-share-pro/
 * Description:       Reposting inside your BuddyPress community, sending posts to friends, share buttons on any content, share counts, trending posts, rewards and share analytics. Extends Activity Share for BuddyPress.
 * Version:           3.6.0
 * Requires at least: 6.7
 * Requires PHP:      8.1
 * Requires Plugins:  buddypress-activity-social-share
 * Author:            Wbcom Designs
 * Author URI:        https://wbcomdesigns.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       buddypress-activity-share-pro
 * Domain Path:       /languages
 *
 * @package BPAS_Pro
 */

defined( 'ABSPATH' ) || exit;

define( 'BPAS_PRO_VERSION', '3.6.0' );
define( 'BPAS_PRO_REQUIRES_API', '3.6' ); // Free public API version this build is written against.
define( 'BPAS_PRO_DB_VERSION', '3.6.0' );
define( 'BPAS_PRO_FILE', __FILE__ );
define( 'BPAS_PRO_DIR', plugin_dir_path( __FILE__ ) );
define( 'BPAS_PRO_URL', plugin_dir_url( __FILE__ ) );
define( 'BPAS_PRO_EDD_ITEM_ID', 1634903 );
define( 'BPAS_PRO_EDD_STORE_URL', 'https://wbcomdesigns.com/' );
define( 'BPAS_PRO_FREE_FILE', 'bp-activity-social-share/buddypress-share.php' );

require_once BPAS_PRO_DIR . 'includes/licensing.php';

add_action( 'plugins_loaded', 'bpas_pro_boot', 20 );

/**
 * Boot once Free is known to be present and compatible.
 */
function bpas_pro_boot(): void {
	if ( class_exists( 'Youzify' ) ) {
		bpas_pro_notice( static fn() => __( 'Activity Share Pro does not support Youzify and is inactive while Youzify is active.', 'buddypress-activity-share-pro' ) );
		return;
	}

	if ( ! function_exists( 'bpas_api_version' ) ) {
		add_action( 'admin_notices', 'bpas_pro_missing_free_notice' );
		return;
	}

	if ( version_compare( bpas_api_version(), BPAS_PRO_REQUIRES_API, '<' ) ) {
		// Free is older than this Pro: pause (Pro may call API Free lacks). Sharing keeps working from Free.
		add_action( 'admin_notices', 'bpas_pro_update_free_notice' );
		return;
	}

	if ( bpas_version() !== BPAS_PRO_VERSION ) {
		// Free is newer: keep running, ask for the matching Pro version.
		bpas_pro_notice(
			static fn() => sprintf(
				/* translators: %s: Activity Share for BuddyPress version. */
				__( 'Please update Activity Share Pro to version %s to match Activity Share for BuddyPress.', 'buddypress-activity-share-pro' ),
				bpas_version()
			)
		);
	}

	bpas_register_autoload( 'BPAS_Pro', BPAS_PRO_DIR . 'includes' );
	BPAS_Pro\Plugin::boot();
}

/**
 * Plain notice for administrators (message built in the callback: no translation before init).
 *
 * @param callable $message Returns the message.
 */
function bpas_pro_notice( callable $message ): void {
	add_action(
		'admin_notices',
		static function () use ( $message ) {
			if ( current_user_can( 'activate_plugins' ) ) {
				printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_html( (string) $message() ) );
			}
		}
	);
}

/**
 * Free is not active: one-click install or activate.
 */
function bpas_pro_missing_free_notice(): void {
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	$installed = file_exists( WP_PLUGIN_DIR . '/' . BPAS_PRO_FREE_FILE );
	$url       = $installed
		? wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( BPAS_PRO_FREE_FILE ) ), 'activate-plugin_' . BPAS_PRO_FREE_FILE )
		: wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=bp-activity-social-share' ), 'install-plugin_bp-activity-social-share' );
	printf(
		'<div class="notice notice-warning"><p>%1$s</p><p><a class="button button-primary" href="%2$s">%3$s</a></p></div>',
		esc_html__( 'Activity Share Pro needs the free Activity Share for BuddyPress plugin. Your Pro settings are safe and sharing resumes as soon as it is active.', 'buddypress-activity-share-pro' ),
		esc_url( $url ),
		$installed ? esc_html__( 'Activate Activity Share for BuddyPress', 'buddypress-activity-share-pro' ) : esc_html__( 'Install and activate Activity Share for BuddyPress', 'buddypress-activity-share-pro' )
	);
}

/**
 * Free is older than Pro: ask to update Free.
 */
function bpas_pro_update_free_notice(): void {
	if ( ! current_user_can( 'update_plugins' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p>%1$s</p><p><a class="button button-primary" href="%2$s">%3$s</a></p></div>',
		esc_html__( 'Activity Share Pro is paused until Activity Share for BuddyPress is updated. Sharing keeps working meanwhile.', 'buddypress-activity-share-pro' ),
		esc_url( self_admin_url( 'plugins.php?plugin_status=upgrade' ) ),
		esc_html__( 'Update Activity Share for BuddyPress', 'buddypress-activity-share-pro' )
	);
}
