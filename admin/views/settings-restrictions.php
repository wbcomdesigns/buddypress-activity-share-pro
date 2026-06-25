<?php
/**
 * Restrictions settings tab body.
 *
 * Moved verbatim from Buddypress_Share_Admin::bp_share_restrictions_page()
 * during the 2.3.0 admin UX migration. bp_reshare_settings group + keys
 * preserved.
 *
 * @package Buddypress_Share
 * @since   2.3.0
 */

defined( 'ABSPATH' ) || exit;

$bp_reshare_settings          = get_site_option( 'bp_reshare_settings', array() );
$bp_reshare_settings_activity = isset( $bp_reshare_settings['reshare_share_activity'] ) ? $bp_reshare_settings['reshare_share_activity'] : 'parent';

$bpas_reshare_notifications = ! empty( $bp_reshare_settings['enable_reshare_notifications'] );
$bpas_min_reshare_cap       = isset( $bp_reshare_settings['min_reshare_capability'] ) ? (string) $bp_reshare_settings['min_reshare_capability'] : '';

// Capability whitelist for the "who can reshare" gate. Mirrors the admin
// sanitizer so an out-of-list value never renders as selected.
$bpas_reshare_caps = array(
	''                  => __( 'Any logged-in member', 'buddypress-share' ),
	'edit_posts'        => __( 'Contributors and above', 'buddypress-share' ),
	'publish_posts'     => __( 'Authors and above', 'buddypress-share' ),
	'edit_others_posts' => __( 'Editors and above', 'buddypress-share' ),
	'manage_options'    => __( 'Administrators only', 'buddypress-share' ),
);
if ( ! array_key_exists( $bpas_min_reshare_cap, $bpas_reshare_caps ) ) {
	$bpas_min_reshare_cap = '';
}
?>

<form method="post" action="options.php">
	<?php
	settings_fields( 'bp_reshare_settings' );
	do_settings_sections( 'bp_reshare_settings' );
	?>

	<h2 class="bpas-section-title"><?php esc_html_e( 'Sharing restrictions', 'buddypress-share' ); ?></h2>
	<p class="bpas-section-intro"><?php esc_html_e( 'Control what can be shared and how reshared activity appears in the feed.', 'buddypress-share' ); ?></p>

	<div class="bp-share-settings-grid">
		<div class="bp-share-settings-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Content restrictions', 'buddypress-share' ); ?></h3>
			</div>
			<div class="card-body">
				<p class="card-description"><?php esc_html_e( 'Turn off resharing for specific content types.', 'buddypress-share' ); ?></p>

				<div class="bp-share-checkbox-group">
					<label class="bp-share-checkbox-item">
						<input type="checkbox"
							name="bp_reshare_settings[disable_post_reshare_activity]"
							id="disable_post_reshare_activity"
							value="1"
							<?php checked( 1, isset( $bp_reshare_settings['disable_post_reshare_activity'] ) ? $bp_reshare_settings['disable_post_reshare_activity'] : 0 ); ?> />
						<span class="checkbox-label">
							<span class="checkbox-icon dashicons dashicons-admin-post" aria-hidden="true"></span>
							<?php esc_html_e( 'Blog posts', 'buddypress-share' ); ?>
						</span>
					</label>

					<label class="bp-share-checkbox-item">
						<input type="checkbox"
							name="bp_reshare_settings[disable_my_profile_reshare_activity]"
							id="disable_my_profile_reshare_activity"
							value="1"
							<?php checked( 1, isset( $bp_reshare_settings['disable_my_profile_reshare_activity'] ) ? $bp_reshare_settings['disable_my_profile_reshare_activity'] : 0 ); ?> />
						<span class="checkbox-label">
							<span class="checkbox-icon dashicons dashicons-admin-users" aria-hidden="true"></span>
							<?php esc_html_e( 'Member profiles', 'buddypress-share' ); ?>
						</span>
					</label>

					<label class="bp-share-checkbox-item">
						<input type="checkbox"
							name="bp_reshare_settings[disable_group_reshare_activity]"
							id="disable_group_reshare_activity"
							value="1"
							<?php checked( 1, isset( $bp_reshare_settings['disable_group_reshare_activity'] ) ? $bp_reshare_settings['disable_group_reshare_activity'] : 0 ); ?> />
						<span class="checkbox-label">
							<span class="checkbox-icon dashicons dashicons-groups" aria-hidden="true"></span>
							<?php esc_html_e( 'Groups', 'buddypress-share' ); ?>
						</span>
					</label>

					<label class="bp-share-checkbox-item">
						<input type="checkbox"
							name="bp_reshare_settings[disable_friends_reshare_activity]"
							id="disable_friends_reshare_activity"
							value="1"
							<?php checked( 1, isset( $bp_reshare_settings['disable_friends_reshare_activity'] ) ? $bp_reshare_settings['disable_friends_reshare_activity'] : 0 ); ?> />
						<span class="checkbox-label">
							<span class="checkbox-icon dashicons dashicons-buddicons-friends" aria-hidden="true"></span>
							<?php esc_html_e( 'Friends', 'buddypress-share' ); ?>
						</span>
					</label>
				</div>

				<p class="card-description"><?php esc_html_e( 'Tick a content type to remove it as a reshare destination. The Friends option is only shown to members when the Friends component is active.', 'buddypress-share' ); ?></p>
			</div>
		</div>

		<div class="bp-share-settings-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'How reshares appear', 'buddypress-share' ); ?></h3>
			</div>
			<div class="card-body">
				<p class="card-description"><?php esc_html_e( 'Choose how a reshared activity shows up in the feed.', 'buddypress-share' ); ?></p>

				<div class="bp-share-radio-group">
					<label class="bp-share-radio-item">
						<input type="radio"
							name="bp_reshare_settings[reshare_share_activity]"
							id="reshare_share_activity_parent"
							value="parent"
							<?php checked( 'parent', $bp_reshare_settings_activity ); ?> />
						<span class="radio-label">
							<strong><?php esc_html_e( 'Simple', 'buddypress-share' ); ?></strong>
							<span class="radio-description"><?php esc_html_e( 'Show only the original activity.', 'buddypress-share' ); ?></span>
						</span>
					</label>

					<label class="bp-share-radio-item">
						<input type="radio"
							name="bp_reshare_settings[reshare_share_activity]"
							id="reshare_share_activity_child"
							value="child"
							<?php checked( 'child', $bp_reshare_settings_activity ); ?> />
						<span class="radio-label">
							<strong><?php esc_html_e( 'Detailed', 'buddypress-share' ); ?></strong>
							<span class="radio-description"><?php esc_html_e( 'Include the nested content and full context.', 'buddypress-share' ); ?></span>
						</span>
					</label>
				</div>
			</div>
		</div>

		<div class="bp-share-settings-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Who can reshare &amp; notifications', 'buddypress-share' ); ?></h3>
			</div>
			<div class="card-body">
				<p class="card-description"><?php esc_html_e( 'Decide which members may reshare activity, and whether authors are told when their activity is reshared.', 'buddypress-share' ); ?></p>

				<div class="bp-share-field">
					<label for="min_reshare_capability" class="bp-share-field__label"><?php esc_html_e( 'Minimum role to reshare', 'buddypress-share' ); ?></label>
					<select name="bp_reshare_settings[min_reshare_capability]" id="min_reshare_capability">
						<?php foreach ( $bpas_reshare_caps as $bpas_cap_value => $bpas_cap_label ) : ?>
							<option value="<?php echo esc_attr( $bpas_cap_value ); ?>" <?php selected( $bpas_min_reshare_cap, $bpas_cap_value ); ?>>
								<?php echo esc_html( $bpas_cap_label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="card-description"><?php esc_html_e( 'Members below this role will not see the Reshare button.', 'buddypress-share' ); ?></p>
				</div>

				<label class="bp-share-checkbox-item">
					<input type="checkbox"
						name="bp_reshare_settings[enable_reshare_notifications]"
						id="enable_reshare_notifications"
						value="1"
						<?php checked( true, $bpas_reshare_notifications ); ?> />
					<span class="checkbox-label">
						<span class="checkbox-icon dashicons dashicons-bell" aria-hidden="true"></span>
						<?php esc_html_e( 'Notify the author when their activity is reshared', 'buddypress-share' ); ?>
					</span>
				</label>
				<p class="card-description"><?php esc_html_e( 'Sends a BuddyPress notification to the original author each time their activity is reshared (self-reshares are skipped).', 'buddypress-share' ); ?></p>
			</div>
		</div>
	</div>

	<?php submit_button( __( 'Save settings', 'buddypress-share' ), 'primary', 'submit', true, array( 'class' => 'button button-primary' ) ); ?>
</form>
