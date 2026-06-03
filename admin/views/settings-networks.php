<?php
/**
 * Social Networks settings tab body.
 *
 * Moved verbatim from Buddypress_Share_Admin::bp_share_social_networks_page()
 * during the 2.3.0 admin UX migration (playbook Part 5 — view extraction).
 * The drag-drop DOM (#drag_icon_ul / #drag_social_icon / .socialicon
 * [data-service]), the hidden bp_share_services_serialized field, the
 * bp_share_general_settings form group, and the wss_social_icons /
 * wss_social_remove_icons AJAX wiring are kept byte-for-byte — they are
 * the data contract.
 *
 * Rendered by Bpas_Admin_Panel via Buddypress_Share_Admin::bp_share_social_networks_page().
 *
 * @var Buddypress_Share_Admin $legacy_admin Owns the helper methods used below.
 *
 * @package Buddypress_Share
 * @since   2.3.0
 */

defined( 'ABSPATH' ) || exit;

// Current settings (site_option scope preserved).
$bp_share_services_enable        = get_site_option( 'bp_share_services_enable', 1 );
$bp_share_services_logout_enable = get_site_option( 'bp_share_services_logout_enable', 1 );
$bp_share_services_extra         = get_site_option( 'bp_share_services_extra', array( 'bp_share_services_open' => 'on' ) );
$bp_share_services_open          = isset( $bp_share_services_extra['bp_share_services_open'] ) ? $bp_share_services_extra['bp_share_services_open'] : 'on';

$enabled_services = get_site_option( 'bp_share_services', array() );
if ( ! is_array( $enabled_services ) ) {
	$enabled_services = array();
}

// Migrate Twitter to X if it exists.
if ( isset( $enabled_services['Twitter'] ) ) {
	$new_services = array();
	foreach ( $enabled_services as $key => $value ) {
		if ( 'Twitter' === $key ) {
			$new_services['X'] = 'X (Twitter)';
		} else {
			$new_services[ $key ] = $value;
		}
	}
	$enabled_services = $new_services;
	update_site_option( 'bp_share_services', $enabled_services );
}

// Services should already be set by activator, but handle edge case.
if ( empty( $enabled_services ) ) {
	$enabled_services = $legacy_admin->get_default_services();
	update_site_option( 'bp_share_services', $enabled_services );
}

$all_services      = $legacy_admin->get_all_available_services();
$disabled_services = array_diff_key( $all_services, $enabled_services );
?>

<form method="post" action="options.php">
	<?php
	settings_fields( 'bp_share_general_settings' );
	do_settings_sections( 'bp_share_general_settings' );
	?>

	<div class="bp-share-settings-grid">
		<div class="bp-share-settings-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Sharing options', 'buddypress-share' ); ?></h3>
			</div>
			<div class="card-body">
				<div class="bp-share-toggle-setting">
					<label class="bp-share-toggle">
						<input type="checkbox"
							name="bp_share_services_enable"
							id="bp_share_services_enable"
							value="1"
							<?php checked( 1, $bp_share_services_enable ); ?> />
						<span class="toggle-slider"></span>
					</label>
					<div class="toggle-content">
						<span class="toggle-label"><?php esc_html_e( 'Turn on social sharing', 'buddypress-share' ); ?></span>
						<p class="description"><?php esc_html_e( 'Let members share activity posts to social networks.', 'buddypress-share' ); ?></p>
					</div>
				</div>

				<div class="bp-share-toggle-setting" id="logout_sharing_row" style="<?php echo esc_attr( $bp_share_services_enable ? '' : 'display:none;' ); ?>">
					<label class="bp-share-toggle">
						<input type="checkbox"
							name="bp_share_services_logout_enable"
							id="bp_share_services_logout_enable"
							value="1"
							<?php checked( 1, $bp_share_services_logout_enable ); ?> />
						<span class="toggle-slider"></span>
					</label>
					<div class="toggle-content">
						<span class="toggle-label"><?php esc_html_e( 'Allow guest sharing', 'buddypress-share' ); ?></span>
						<p class="description"><?php esc_html_e( 'Let logged-out visitors share public activity posts.', 'buddypress-share' ); ?></p>
					</div>
				</div>

				<div class="bp-share-toggle-setting">
					<label class="bp-share-toggle">
						<input type="checkbox"
							name="bp_share_services_extra[bp_share_services_open]"
							id="bp_share_services_open"
							value="on"
							<?php checked( 'on', $bp_share_services_open ); ?> />
						<span class="toggle-slider"></span>
					</label>
					<div class="toggle-content">
						<span class="toggle-label"><?php esc_html_e( 'Open links in a popup window', 'buddypress-share' ); ?></span>
						<p class="description"><?php esc_html_e( 'Sharing links open in a small popup instead of a new tab.', 'buddypress-share' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<h2 class="bpas-section-title"><?php esc_html_e( 'Available networks', 'buddypress-share' ); ?></h2>
	<p class="bpas-section-intro"><?php esc_html_e( 'Drag a network between the lists to turn it on or off. Drag within a list to change the order.', 'buddypress-share' ); ?></p>

	<?php
	// Contract: bp_share_services_serialized keeps its legacy PHP-serialized
	// initial payload (the sync handler reads json_decode first, then falls
	// back to maybe_unserialize). The admin JS rewrites this with
	// JSON.stringify on any drag, so the value is never unserialized from
	// untrusted input. Preserved byte-for-byte for this UX release.
	?>
	<input type="hidden" name="bp_share_services_serialized" id="bp_share_services_serialized" value="<?php echo esc_attr( serialize( $enabled_services ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize ?>" />

	<div class="social_icon_section">
		<div class="social-services-list enabled-services">
			<h3><?php esc_html_e( 'Active networks', 'buddypress-share' ); ?></h3>
			<ul id="drag_icon_ul" class="enabled-services-list">
				<?php if ( ! empty( $enabled_services ) ) : ?>
					<?php foreach ( $enabled_services as $service_key => $service_name ) : ?>
						<?php
						if ( is_array( $service_name ) ) {
							$service_name = $service_key;
						}
						?>
						<li class="socialicon icon_<?php echo esc_attr( sanitize_title( $service_key ) ); ?>" data-service="<?php echo esc_attr( $service_key ); ?>">
							<?php echo esc_html( $service_name ); ?>
						</li>
					<?php endforeach; ?>
				<?php else : ?>
					<li class="no-services-message">
						<?php esc_html_e( 'No networks are active. Drag a network here to turn it on.', 'buddypress-share' ); ?>
					</li>
				<?php endif; ?>
			</ul>
		</div>

		<div class="social-services-list disabled-services">
			<h3><?php esc_html_e( 'Inactive networks', 'buddypress-share' ); ?></h3>
			<ul id="drag_social_icon" class="disabled-services-list">
				<?php if ( ! empty( $disabled_services ) ) : ?>
					<?php foreach ( $disabled_services as $service_key => $service_name ) : ?>
						<?php
						if ( is_array( $service_name ) ) {
							$service_name = $service_key;
						}
						?>
						<li class="socialicon icon_<?php echo esc_attr( sanitize_title( $service_key ) ); ?>" data-service="<?php echo esc_attr( $service_key ); ?>">
							<?php echo esc_html( $service_name ); ?>
						</li>
					<?php endforeach; ?>
				<?php else : ?>
					<li class="no-services-message">
						<?php esc_html_e( 'Every network is active. Drag one here to turn it off.', 'buddypress-share' ); ?>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>

	<?php submit_button( __( 'Save settings', 'buddypress-share' ), 'primary', 'submit', true, array( 'class' => 'button button-primary' ) ); ?>
</form>
