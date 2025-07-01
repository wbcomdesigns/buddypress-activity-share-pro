<?php
/**
 * Clean Share Settings Template for BuddyPress Activity Share Pro
 *
 * This template displays the share/reshare settings page without inline styles/scripts.
 * All styling moved to separate CSS file for better maintainability.
 *
 * @link       http://wbcomdesigns.com
 * @since      1.5.1
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin/templates
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Security check - ensure user has proper capabilities
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'buddypress-share' ) );
}

// Get current settings
$bp_reshare_settings = get_site_option( 'bp_reshare_settings', array() );
$bp_reshare_settings_activity = isset( $bp_reshare_settings['reshare_share_activity'] ) ? $bp_reshare_settings['reshare_share_activity'] : 'parent';

// Determine if settings were saved
$bp_reshare_settings_save_notice = 'display:none';
if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) { //phpcs:ignore
	$bp_reshare_settings_save_notice = '';
}

// Share settings configuration
$share_settings = array(
	'disable_post_reshare_activity' => array(
		'title' => __( 'Disable Post Share Activity', 'buddypress-share' ),
		'description' => __( 'Enable this option to prevent sharing of blog posts according to your preference.', 'buddypress-share' ),
		'icon' => 'dashicons-admin-post'
	),
	'disable_my_profile_reshare_activity' => array(
		'title' => __( 'Disable My Profile Sharing', 'buddypress-share' ),
		'description' => __( 'Enable this option to prevent users from sharing activities to their own profiles.', 'buddypress-share' ),
		'icon' => 'dashicons-admin-users'
	),
	'disable_message_reshare_activity' => array(
		'title' => __( 'Disable Message Sharing', 'buddypress-share' ),
		'description' => __( 'Enable this option to prevent sharing activities via private messages.', 'buddypress-share' ),
		'icon' => 'dashicons-email'
	),
	'disable_group_reshare_activity' => array(
		'title' => __( 'Disable Group Sharing', 'buddypress-share' ),
		'description' => __( 'Enable this option to prevent sharing activities to groups.', 'buddypress-share' ),
		'icon' => 'dashicons-groups'
	),
	'disable_friends_reshare_activity' => array(
		'title' => __( 'Disable Friends Sharing', 'buddypress-share' ),
		'description' => __( 'Enable this option to prevent sharing activities with friends.', 'buddypress-share' ),
		'icon' => 'dashicons-share-alt2'
	)
);

// Activity display options
$activity_display_options = array(
	'parent' => array(
		'title' => __( 'Parent Activity', 'buddypress-share' ),
		'description' => __( 'Display the original activity without nested content when sharing.', 'buddypress-share' ),
		'icon' => 'dashicons-arrow-up-alt'
	),
	'child' => array(
		'title' => __( 'Child Activity', 'buddypress-share' ),
		'description' => __( 'Display the complete activity including any nested shared content.', 'buddypress-share' ),
		'icon' => 'dashicons-arrow-down-alt'
	)
);

?>
<div class="bp-share-admin-content">
	<div class="bp-share-form-wrapper">
		
		<!-- Success Message -->
		<div class="bp-share-notice notice-success" style="<?php echo esc_attr( $bp_reshare_settings_save_notice ); ?>">
			<p><strong><?php esc_html_e( 'Share settings saved successfully.', 'buddypress-share' ); ?></strong></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'buddypress-share' ); ?></span>
			</button>
		</div>

		<!-- Settings Form -->
		<form method="post" action="options.php" id="bp_reshare_form">
			<?php 
			// Security nonces
			settings_fields( 'bp_reshare_settings' );
			do_settings_sections( 'bp_reshare_settings' );
			?>

			<!-- Content Type Sharing Controls -->
			<div class="bp-share-form-section">
				<div class="bp-share-section-header">
					<h3 class="bp-share-section-title">
						<?php esc_html_e( 'Content Type Controls', 'buddypress-share' ); ?>
					</h3>
					<p class="bp-share-section-description">
						<?php esc_html_e( 'Control which types of content can be shared within your BuddyPress community.', 'buddypress-share' ); ?>
					</p>
				</div>

				<div class="bp-share-settings-grid">
					<?php foreach ( $share_settings as $setting_key => $setting_data ) : ?>
						<div class="bp-share-setting-item">
							<div class="bp-share-setting-icon">
								<span class="dashicons <?php echo esc_attr( $setting_data['icon'] ); ?>"></span>
							</div>
							<div class="bp-share-setting-info">
								<h4 class="bp-share-setting-title"><?php echo esc_html( $setting_data['title'] ); ?></h4>
								<p class="bp-share-setting-description"><?php echo esc_html( $setting_data['description'] ); ?></p>
							</div>
							<div class="bp-share-setting-control">
								<label class="bp-share-toggle">
									<input type="checkbox" 
									       name="bp_reshare_settings[<?php echo esc_attr( $setting_key ); ?>]" 
									       value="1" 
									       <?php checked( 1, isset( $bp_reshare_settings[ $setting_key ] ) ? $bp_reshare_settings[ $setting_key ] : 0 ); ?> />
									<span class="bp-share-slider"></span>
								</label>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Reshare Activity Behavior -->
			<div class="bp-share-form-section">
				<div class="bp-share-section-header">
					<h3 class="bp-share-section-title">
						<?php esc_html_e( 'Reshare Activity Display', 'buddypress-share' ); ?>
					</h3>
					<p class="bp-share-section-description">
						<?php esc_html_e( 'Choose how shared activities are displayed in the activity stream.', 'buddypress-share' ); ?>
					</p>
				</div>

				<div class="bp-share-radio-group">
					<?php foreach ( $activity_display_options as $option_key => $option_data ) : ?>
						<div class="bp-share-radio-item">
							<label class="bp-share-radio-label">
								<input type="radio" 
								       name="bp_reshare_settings[reshare_share_activity]" 
								       value="<?php echo esc_attr( $option_key ); ?>" 
								       <?php checked( $option_key, $bp_reshare_settings_activity ); ?> />
								<span class="bp-share-radio-indicator">
									<span class="dashicons <?php echo esc_attr( $option_data['icon'] ); ?>"></span>
								</span>
								<div class="bp-share-radio-content">
									<h4><?php echo esc_html( $option_data['title'] ); ?></h4>
									<p><?php echo esc_html( $option_data['description'] ); ?></p>
								</div>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Advanced Sharing Options -->
			<div class="bp-share-form-section">
				<div class="bp-share-section-header">
					<h3 class="bp-share-section-title">
						<?php esc_html_e( 'Advanced Options', 'buddypress-share' ); ?>
					</h3>
					<p class="bp-share-section-description">
						<?php esc_html_e( 'Additional settings to fine-tune sharing behavior.', 'buddypress-share' ); ?>
					</p>
				</div>

				<div class="bp-share-advanced-options">
					<div class="bp-share-form-field">
						<label class="bp-share-toggle">
							<input type="checkbox" 
							       name="bp_reshare_settings[enable_share_count]" 
							       value="1"
							       <?php checked( 1, $bp_reshare_settings['enable_share_count'] ?? 1 ); ?> />
							<span class="bp-share-slider"></span>
						</label>
						<span class="bp-share-toggle-label">
							<?php esc_html_e( 'Display share count', 'buddypress-share' ); ?>
						</span>
						<p class="bp-share-field-help">
							<?php esc_html_e( 'Show the number of times content has been shared.', 'buddypress-share' ); ?>
						</p>
					</div>

					<div class="bp-share-form-field">
						<label class="bp-share-toggle">
							<input type="checkbox" 
							       name="bp_reshare_settings[prevent_self_share]" 
							       value="1"
							       <?php checked( 1, $bp_reshare_settings['prevent_self_share'] ?? 1 ); ?> />
							<span class="bp-share-slider"></span>
						</label>
						<span class="bp-share-toggle-label">
							<?php esc_html_e( 'Prevent users from sharing their own content', 'buddypress-share' ); ?>
						</span>
						<p class="bp-share-field-help">
							<?php esc_html_e( 'Users cannot share activities they created themselves.', 'buddypress-share' ); ?>
						</p>
					</div>

					<div class="bp-share-form-field">
						<label for="max_share_depth" class="bp-share-field-label">
							<?php esc_html_e( 'Maximum Share Depth', 'buddypress-share' ); ?>
						</label>
						<select name="bp_reshare_settings[max_share_depth]" id="max_share_depth" class="bp-share-select">
							<option value="1" <?php selected( 1, $bp_reshare_settings['max_share_depth'] ?? 3 ); ?>>
								<?php esc_html_e( '1 Level', 'buddypress-share' ); ?>
							</option>
							<option value="2" <?php selected( 2, $bp_reshare_settings['max_share_depth'] ?? 3 ); ?>>
								<?php esc_html_e( '2 Levels', 'buddypress-share' ); ?>
							</option>
							<option value="3" <?php selected( 3, $bp_reshare_settings['max_share_depth'] ?? 3 ); ?>>
								<?php esc_html_e( '3 Levels', 'buddypress-share' ); ?>
							</option>
							<option value="unlimited" <?php selected( 'unlimited', $bp_reshare_settings['max_share_depth'] ?? 3 ); ?>>
								<?php esc_html_e( 'Unlimited', 'buddypress-share' ); ?>
							</option>
						</select>
						<p class="description">
							<?php esc_html_e( 'Limit how many times content can be re-shared (shares of shares).', 'buddypress-share' ); ?>
						</p>
					</div>
				</div>
			</div>

			<!-- Privacy & Permissions -->
			<div class="bp-share-form-section">
				<div class="bp-share-section-header">
					<h3 class="bp-share-section-title">
						<?php esc_html_e( 'Privacy & Permissions', 'buddypress-share' ); ?>
					</h3>
					<p class="bp-share-section-description">
						<?php esc_html_e( 'Control who can share content and under what conditions.', 'buddypress-share' ); ?>
					</p>
				</div>

				<div class="bp-share-privacy-options">
					<div class="bp-share-form-field">
						<label class="bp-share-toggle">
							<input type="checkbox" 
							       name="bp_reshare_settings[respect_privacy]" 
							       value="1"
							       <?php checked( 1, $bp_reshare_settings['respect_privacy'] ?? 1 ); ?> />
							<span class="bp-share-slider"></span>
						</label>
						<span class="bp-share-toggle-label">
							<?php esc_html_e( 'Respect activity privacy settings', 'buddypress-share' ); ?>
						</span>
						<p class="bp-share-field-help">
							<?php esc_html_e( 'Private activities cannot be shared outside their original context.', 'buddypress-share' ); ?>
						</p>
					</div>

					<div class="bp-share-form-field">
						<label class="bp-share-toggle">
							<input type="checkbox" 
							       name="bp_reshare_settings[require_permission]" 
							       value="1"
							       <?php checked( 1, $bp_reshare_settings['require_permission'] ?? 0 ); ?> />
							<span class="bp-share-slider"></span>
						</label>
						<span class="bp-share-toggle-label">
							<?php esc_html_e( 'Require permission to share others\' content', 'buddypress-share' ); ?>
						</span>
						<p class="bp-share-field-help">
							<?php esc_html_e( 'Users must approve before their content can be shared by others.', 'buddypress-share' ); ?>
						</p>
					</div>
				</div>
			</div>

			<!-- Submit Button -->
			<div class="bp-share-submit-section">
				<button type="submit" 
				        name="bpas_submit_reshare_options" 
				        class="bp-share-submit-button">
					<span class="dashicons dashicons-saved"></span>
					<?php esc_html_e( 'Save Share Settings', 'buddypress-share' ); ?>
				</button>
				<span class="bp-share-spinner"></span>
				
				<div class="bp-share-save-info">
					<p class="description">
						<?php esc_html_e( 'These settings control how content sharing works in your BuddyPress community.', 'buddypress-share' ); ?>
					</p>
				</div>
			</div>
		</form>

		<!-- Settings Preview -->
		<div class="bp-share-preview-section">
			<h3><?php esc_html_e( 'Settings Preview', 'buddypress-share' ); ?></h3>
			<div class="bp-share-preview-content">
				<div class="bp-share-preview-item">
					<h4><?php esc_html_e( 'Enabled Sharing Methods', 'buddypress-share' ); ?></h4>
					<ul class="bp-share-preview-list" id="enabled-sharing-methods">
						<!-- Populated by JavaScript -->
					</ul>
				</div>
				<div class="bp-share-preview-item">
					<h4><?php esc_html_e( 'Activity Display Mode', 'buddypress-share' ); ?></h4>
					<p class="bp-share-preview-text" id="activity-display-mode">
						<!-- Populated by JavaScript -->
					</p>
				</div>
			</div>
		</div>

		<!-- Help Section -->
		<div class="bp-share-help-section">
			<h3><?php esc_html_e( 'Need Help?', 'buddypress-share' ); ?></h3>
			<div class="bp-share-help-content">
				<div class="bp-share-help-item">
					<h4><?php esc_html_e( 'Share Settings Guide', 'buddypress-share' ); ?></h4>
					<p><?php esc_html_e( 'Learn how to configure sharing settings for your community.', 'buddypress-share' ); ?></p>
					<a href="https://docs.wbcomdesigns.com/doc_category/buddypress-activity-social-share/" target="_blank" class="button button-secondary">
						<?php esc_html_e( 'View Documentation', 'buddypress-share' ); ?>
					</a>
				</div>
				<div class="bp-share-help-item">
					<h4><?php esc_html_e( 'Privacy Best Practices', 'buddypress-share' ); ?></h4>
					<p><?php esc_html_e( 'Understand how to balance sharing features with user privacy.', 'buddypress-share' ); ?></p>
					<a href="https://wbcomdesigns.com/support/" target="_blank" class="button button-secondary">
						<?php esc_html_e( 'Get Support', 'buddypress-share' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>