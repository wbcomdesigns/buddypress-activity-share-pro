<?php
/**
 * Licensing: EDD Software Licensing SDK (updates only - never gates features).
 *
 * Loaded at file load (not plugins_loaded) because the SDK registers on its own boot.
 *
 * @package BPAS_Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * SDK product arguments (one source for the registry and the License tab).
 */
function bpas_pro_license_args(): array {
	return array(
		'id'          => 'buddypress-activity-share-pro',
		'url'         => BPAS_PRO_EDD_STORE_URL,
		'item_id'     => BPAS_PRO_EDD_ITEM_ID,
		'item_name'   => 'BuddyPress Activity Share Pro',
		'version'     => BPAS_PRO_VERSION,
		'file'        => BPAS_PRO_FILE,
		'type'        => 'plugin',
		'slug'        => 'buddypress-activity-share-pro',
		'option_name' => 'bpas_pro_license_key',
	);
}

/**
 * The SDK License object, or null when the SDK is unavailable.
 *
 * @return \EasyDigitalDownloads\Updater\Licensing\License|null
 */
function bpas_pro_license() {
	static $license = null;
	if ( null === $license && class_exists( '\EasyDigitalDownloads\Updater\Licensing\License' ) ) {
		$license = new \EasyDigitalDownloads\Updater\Licensing\License( 'buddypress-activity-share-pro', bpas_pro_license_args() );
	}
	return $license;
}

add_action(
	'edd_sl_sdk_registry',
	static function ( $registry ): void {
		$registry->register( bpas_pro_license_args() );
	}
);

/*
 * Guard the SDK overlay endpoint before ANY copy of the SDK handles it. The newest
 * SDK copy on a site wins arbitration, so another plugin's unpatched 1.0.3 may be
 * the one running: its handler includes "{templates}/{template}.php" unchecked.
 */
add_action(
	'wp_ajax_edd_sdk_get_notice_buddypress-activity-share-pro',
	static function (): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to do this.', 'buddypress-activity-share-pro' ), 403 );
		}
		$template = filter_input( INPUT_GET, 'template', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( 'license-control' !== (string) $template ) {
			wp_send_json_error( esc_html__( 'Invalid template.', 'buddypress-activity-share-pro' ), 400 );
		}
	},
	1
);

// Load the vendored SDK only when complete; a stripped build degrades to "updates off".
if ( file_exists( BPAS_PRO_DIR . 'libs/edd-sl-sdk/edd-sl-sdk.php' ) && file_exists( BPAS_PRO_DIR . 'libs/edd-sl-sdk/src/Versions.php' ) ) {
	require_once BPAS_PRO_DIR . 'libs/edd-sl-sdk/edd-sl-sdk.php';
}
