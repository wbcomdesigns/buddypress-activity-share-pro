<?php
/**
 * Post Type Sharing settings tab body.
 *
 * Moved from Buddypress_Share_Admin::bp_share_post_types_page() during the
 * 2.3.0 admin UX migration. The bp_share_post_type_settings nonce, the
 * custom save handler (BP_Share_Post_Type_Settings::save_settings), and the
 * reused admin/partials/bp-share-post-type-settings.php template are kept.
 *
 * @package Buddypress_Share
 * @since   2.3.0
 */

defined( 'ABSPATH' ) || exit;

// Ensure the post-type settings classes are loaded.
if ( ! class_exists( 'BP_Share_Post_Type_Settings' ) ) {
	$bpas_base_path = plugin_dir_path( dirname( __DIR__ ) );
	$bpas_files     = array(
		$bpas_base_path . 'includes/post-types/class-bp-share-post-type-settings.php',
		$bpas_base_path . 'includes/post-types/class-bp-share-post-type-controller.php',
		$bpas_base_path . 'includes/post-types/class-bp-share-post-type-frontend.php',
	);
	foreach ( $bpas_files as $bpas_file ) {
		if ( file_exists( $bpas_file ) ) {
			require_once $bpas_file;
		}
	}
}

// Handle form submission (nonce + handler preserved byte-for-byte).
if ( isset( $_POST['bp_share_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bp_share_nonce'] ) ), 'bp_share_post_type_settings' ) ) {
	if ( class_exists( 'BP_Share_Post_Type_Settings' ) ) {
		$bpas_settings_manager = BP_Share_Post_Type_Settings::get_instance();

		$bpas_settings_data = array(
			'enabled_post_types' => isset( $_POST['bp_share_settings']['enabled_post_types'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['bp_share_settings']['enabled_post_types'] ) ) : array(),
			'post_type_services' => isset( $_POST['bp_share_settings']['post_type_services'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['bp_share_settings']['post_type_services'] ) ) : array(),
			'display_position'   => isset( $_POST['bp_share_settings']['display_position'] ) ? sanitize_text_field( wp_unslash( $_POST['bp_share_settings']['display_position'] ) ) : 'right',
			'display_style'      => isset( $_POST['bp_share_settings']['display_style'] ) ? sanitize_text_field( wp_unslash( $_POST['bp_share_settings']['display_style'] ) ) : 'floating',
			'mobile_behavior'    => isset( $_POST['bp_share_settings']['mobile_behavior'] ) ? sanitize_text_field( wp_unslash( $_POST['bp_share_settings']['mobile_behavior'] ) ) : 'bottom',
			'default_services'   => isset( $_POST['bp_share_settings']['default_services'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['bp_share_settings']['default_services'] ) ) : array(),
		);

		$bpas_result = $bpas_settings_manager->save_settings( $bpas_settings_data );

		if ( $bpas_result ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'buddypress-share' ) . '</p></div>';
		} else {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Could not save. Please try again.', 'buddypress-share' ) . '</p></div>';
		}
	}
}

// Include the settings template (reused partial).
$bpas_template_file = plugin_dir_path( dirname( __DIR__ ) ) . 'admin/partials/bp-share-post-type-settings.php';

if ( file_exists( $bpas_template_file ) ) {
	include $bpas_template_file;
} else {
	?>
	<div class="bpas-empty-state">
		<span class="bpas-empty-state__icon" aria-hidden="true">
			<span class="dashicons dashicons-admin-post"></span>
		</span>
		<p class="bpas-empty-state__title"><?php esc_html_e( 'Post type sharing is unavailable', 'buddypress-share' ); ?></p>
		<p class="bpas-empty-state__desc"><?php esc_html_e( 'The settings template could not be found. Please reinstall the plugin.', 'buddypress-share' ); ?></p>
	</div>
	<?php
}
