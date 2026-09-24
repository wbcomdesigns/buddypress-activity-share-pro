<?php
/**
 * Pro tabs on Free's admin page (WB Plugins > Activity Share) - no second admin shell.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Admin;

use BPAS_Pro\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Features, Content types, Analytics, License.
 */
final class Admin {

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_filter( 'bpas_admin_tabs', array( __CLASS__, 'tabs' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Add Pro tabs after Free's.
	 *
	 * @param array $tabs Tabs.
	 */
	public static function tabs( $tabs ): array {
		$tabs = (array) $tabs;
		return $tabs + array(
			'features'  => array(
				'label'  => __( 'Features', 'buddypress-activity-share-pro' ),
				'render' => array( __CLASS__, 'render_features' ),
				'icon'   => 'dashicons-admin-plugins',
				'group'  => _x( 'Pro', 'settings section', 'buddypress-activity-share-pro' ),
			),
			'content'   => array(
				'label'  => __( 'Content types', 'buddypress-activity-share-pro' ),
				'render' => array( __CLASS__, 'render_content' ),
				'icon'   => 'dashicons-media-document',
				'group'  => _x( 'Pro', 'settings section', 'buddypress-activity-share-pro' ),
			),
			'analytics' => array(
				'label'  => __( 'Analytics', 'buddypress-activity-share-pro' ),
				'render' => array( __CLASS__, 'render_analytics' ),
				'icon'   => 'dashicons-chart-bar',
				'group'  => _x( 'Pro', 'settings section', 'buddypress-activity-share-pro' ),
			),
			'license'   => array(
				'label'  => __( 'License', 'buddypress-activity-share-pro' ),
				'render' => array( __CLASS__, 'render_license' ),
				'icon'   => 'dashicons-admin-network',
				'group'  => __( 'Account', 'buddypress-activity-share-pro' ),
			),
		);
	}

	/**
	 * Features tab.
	 */
	public static function render_features(): void {
		$features = Settings::all()['features'];
		$rows     = array(
			'repost'      => array( _x( 'Repost', 'feature name', 'buddypress-activity-share-pro' ), __( 'Members repost updates to their profile or a group, with or without a comment. Quick repost has an Undo.', 'buddypress-activity-share-pro' ) ),
			'send_friend' => array( __( 'Send to a friend', 'buddypress-activity-share-pro' ), __( 'Members send a post to a friend as a private message.', 'buddypress-activity-share-pro' ) ),
			'reply_share' => array( __( 'Share replies', 'buddypress-activity-share-pro' ), __( 'Replies get their own share menu and can be reposted.', 'buddypress-activity-share-pro' ) ),
			'counts'      => array( __( 'Counts and who reposted', 'buddypress-activity-share-pro' ), __( 'Show the number of shares next to Share, and who reposted.', 'buddypress-activity-share-pro' ) ),
			'image_card'  => array( __( 'Share image for text posts', 'buddypress-activity-share-pro' ), __( 'Posts without a picture get a branded image when shared to Facebook, LinkedIn or WhatsApp.', 'buddypress-activity-share-pro' ) ),
			'short_links' => array( __( 'Short share links', 'buddypress-activity-share-pro' ), __( 'Shared links look like yoursite.com/s/abc123 and credit the member who shared.', 'buddypress-activity-share-pro' ) ),
			'analytics'   => array( __( 'Share analytics', 'buddypress-activity-share-pro' ), __( 'Count clicks on share buttons and visits from shared links. Visitors are counted without storing IP addresses.', 'buddypress-activity-share-pro' ) ),
		);
		include BPAS_PRO_DIR . 'includes/admin/views/tab-features.php';
	}

	/**
	 * Content types tab.
	 */
	public static function render_content(): void {
		$selected = Settings::content_types();
		$types    = get_post_types( array( 'public' => true ), 'objects' );
		// Media files and BuddyPress's own directory pages are not content people share.
		unset( $types['attachment'], $types['buddypress'] );
		include BPAS_PRO_DIR . 'includes/admin/views/tab-content.php';
	}

	/**
	 * Analytics tab.
	 */
	public static function render_analytics(): void {
		$tracking = Settings::feature( 'analytics' );
		include BPAS_PRO_DIR . 'includes/admin/views/tab-analytics.php';
	}

	/**
	 * License tab.
	 */
	public static function render_license(): void {
		$license = bpas_pro_license();
		include BPAS_PRO_DIR . 'includes/admin/views/tab-license.php';
	}

	/**
	 * Pro admin script on the Activity Share page.
	 */
	public static function enqueue(): void {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- screen check only.
		if ( 'buddypress-share' !== $page ) {
			return;
		}
		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_style( 'bpas-pro-admin', BPAS_PRO_URL . "assets/css/bpas-pro-admin{$min}.css", array( 'bpas-admin' ), BPAS_PRO_VERSION );
		wp_style_add_data( 'bpas-pro-admin', 'rtl', 'replace' );
		wp_style_add_data( 'bpas-pro-admin', 'suffix', $min );
		wp_enqueue_script( 'bpas-pro-admin', BPAS_PRO_URL . "assets/js/bpas-pro-admin{$min}.js", array( 'wp-api-fetch', 'bpas-admin' ), BPAS_PRO_VERSION, array( 'in_footer' => true ) );
		wp_add_inline_script(
			'bpas-pro-admin',
			'window.bpasProAdmin = ' . wp_json_encode(
				array(
					'ns'     => '/' . bpas_rest_namespace(),
					// Numbers follow the admin's WordPress language, not the browser's.
					'locale' => str_replace( '_', '-', get_user_locale() ),
					'i18n'   => array(
						'saved'      => __( 'Settings saved.', 'buddypress-activity-share-pro' ),
						'failed'     => __( 'Settings could not be saved. Please try again.', 'buddypress-activity-share-pro' ),
						'loading'    => __( 'Loading...', 'buddypress-activity-share-pro' ),
						'empty'      => __( 'Nothing shared in this period yet.', 'buddypress-activity-share-pro' ),
						'noNetworks' => __( 'No shares to other sites in this period.', 'buddypress-activity-share-pro' ),
						'error'      => __( 'The report could not be loaded. Please try again.', 'buddypress-activity-share-pro' ),
						'item'       => __( 'Item', 'buddypress-activity-share-pro' ),
						'type'       => __( 'Type', 'buddypress-activity-share-pro' ),
						'shares'     => _x( 'Shares', 'number of shares', 'buddypress-activity-share-pro' ),
						'url'        => __( 'URL', 'buddypress-activity-share-pro' ),
						'exporting'  => __( 'Preparing the file...', 'buddypress-activity-share-pro' ),
						'activity'   => _x( 'Activity', 'content type', 'buddypress-activity-share-pro' ),
						'post'       => _x( 'Post', 'content type', 'buddypress-activity-share-pro' ),
						'prev'       => __( 'Previous', 'buddypress-activity-share-pro' ),
						'next'       => __( 'Next', 'buddypress-activity-share-pro' ),
						/* translators: 1: current page, 2: total pages. */
						'pageOf'     => __( 'Page %1$s of %2$s', 'buddypress-activity-share-pro' ),
					),
				)
			) . ';',
			'before'
		);

		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- screen check only.
		if ( 'license' === $tab && class_exists( '\EasyDigitalDownloads\Updater\Utilities\Path' ) ) {
			$url = \EasyDigitalDownloads\Updater\Utilities\Path::get_url();
			$ver = \EasyDigitalDownloads\Updater\Utilities\Path::get_version();
			wp_enqueue_style( 'edd-sdk-notice', $url . 'assets/build/css/style-edd-sl-sdk.css', array(), $ver );
			wp_enqueue_script( 'edd-sdk-notice', $url . 'assets/build/js/edd-sl-sdk.js', array(), $ver, true );
			wp_localize_script(
				'edd-sdk-notice',
				'edd_sdk_notice',
				array(
					'ajax_url'     => admin_url( 'admin-ajax.php' ),
					'nonce'        => wp_create_nonce( 'edd_sdk_notice' ),
					'activating'   => esc_html__( 'Activating', 'buddypress-activity-share-pro' ),
					'deactivating' => esc_html__( 'Deactivating', 'buddypress-activity-share-pro' ),
					'error'        => esc_html__( 'Something went wrong. Please try again.', 'buddypress-activity-share-pro' ),
				)
			);
		}
	}
}
