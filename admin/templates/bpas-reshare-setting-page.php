<?php
/**
 * Share Settings Template for BuddyPress Activity Share Pro
 *
 * This template displays the share/reshare settings page for the plugin.
 * Updated for the independent menu system without wbcom wrapper.
 * Enhanced with modern styling and better UX.
 *
 * @link       http://wbcomdesigns.com
 * @since      1.0.0
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

				<!-- Post Share Activity -->
				<div class="bp-share-setting-item">
					<div class="bp-share-setting-control">
						<label class="bp-share-toggle">
							<input type="checkbox" 
							       name="bp_reshare_settings[disable_post_reshare_activity]" 
							       value="1" 
							       <?php checked( 1, isset( $bp_reshare_settings['disable_post_reshare_activity'] ) ? $bp_reshare_settings['disable_post_reshare_activity'] : 0 ); ?> />
							<span class="bp-share-slider"></span>
						</label>
					</div>
					<div class="bp-share-setting-info">
						<h4 class="bp-share-setting-title"><?php esc_html_e( 'Disable Post Share Activity', 'buddypress-share' ); ?></h4>
						<p class="bp-share-setting-description"><?php esc_html_e( 'Enable this option to prevent sharing of blog posts according to your preference.', 'buddypress-share' ); ?></p>
					</div>
				</div>

				<!-- Profile Sharing -->
				<div class="bp-share-setting-item">
					<div class="bp-share-setting-control">
						<label class="bp-share-toggle">
							<input type="checkbox" 
							       name="bp_reshare_settings[disable_my_profile_reshare_activity]" 
							       value="1" 
							       <?php checked( 1, isset( $bp_reshare_settings['disable_my_profile_reshare_activity'] ) ? $bp_reshare_settings['disable_my_profile_reshare_activity'] : 0 ); ?> />
							<span class="bp-share-slider"></span>
						</label>
					</div>
					<div class="bp-share-setting-info">
						<h4 class="bp-share-setting-title"><?php esc_html_e( 'Disable My Profile Sharing', 'buddypress-share' ); ?></h4>
						<p class="bp-share-setting-description"><?php esc_html_e( 'Enable this option to prevent users from sharing activities to their own profiles.', 'buddypress-share' ); ?></p>
					</div>
				</div>

				<!-- Message Sharing -->
				<div class="bp-share-setting-item">
					<div class="bp-share-setting-control">
						<label class="bp-share-toggle">
							<input type="checkbox" 
							       name="bp_reshare_settings[disable_message_reshare_activity]" 
							       value="1" 
							       <?php checked( 1, isset( $bp_reshare_settings['disable_message_reshare_activity'] ) ? $bp_reshare_settings['disable_message_reshare_activity'] : 0 ); ?> />
							<span class="bp-share-slider"></span>
						</label>
					</div>
					<div class="bp-share-setting-info">
						<h4 class="bp-share-setting-title"><?php esc_html_e( 'Disable Message Sharing', 'buddypress-share' ); ?></h4>
						<p class="bp-share-setting-description"><?php esc_html_e( 'Enable this option to prevent sharing activities via private messages.', 'buddypress-share' ); ?></p>
					</div>
				</div>

				<!-- Group Sharing -->
				<div class="bp-share-setting-item">
					<div class="bp-share-setting-control">
						<label class="bp-share-toggle">
							<input type="checkbox" 
							       name="bp_reshare_settings[disable_group_reshare_activity]" 
							       value="1" 
							       <?php checked( 1, isset( $bp_reshare_settings['disable_group_reshare_activity'] ) ? $bp_reshare_settings['disable_group_reshare_activity'] : 0 ); ?> />
							<span class="bp-share-slider"></span>
						</label>
					</div>
					<div class="bp-share-setting-info">
						<h4 class="bp-share-setting-title"><?php esc_html_e( 'Disable Group Sharing', 'buddypress-share' ); ?></h4>
						<p class="bp-share-setting-description"><?php esc_html_e( 'Enable this option to prevent sharing activities to groups.', 'buddypress-share' ); ?></p>
					</div>
				</div>

				<!-- Friends Sharing -->
				<div class="bp-share-setting-item">
					<div class="bp-share-setting-control">
						<label class="bp-share-toggle">
							<input type="checkbox" 
							       name="bp_reshare_settings[disable_friends_reshare_activity]" 
							       value="1" 
							       <?php checked( 1, isset( $bp_reshare_settings['disable_friends_reshare_activity'] ) ? $bp_reshare_settings['disable_friends_reshare_activity'] : 0 ); ?> />
							<span class="bp-share-slider"></span>
						</label>
					</div>
					<div class="bp-share-setting-info">
						<h4 class="bp-share-setting-title"><?php esc_html_e( 'Disable Friends Sharing', 'buddypress-share' ); ?></h4>
						<p class="bp-share-setting-description"><?php esc_html_e( 'Enable this option to prevent sharing activities with friends.', 'buddypress-share' ); ?></p>
					</div>
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
					<div class="bp-share-radio-item">
						<label class="bp-share-radio-label">
							<input type="radio" 
							       name="bp_reshare_settings[reshare_share_activity]" 
							       value="parent" 
							       <?php checked( 'parent', $bp_reshare_settings_activity ); ?> />
							<span class="bp-share-radio-indicator"></span>
							<div class="bp-share-radio-content">
								<h4><?php esc_html_e( 'Parent Activity', 'buddypress-share' ); ?></h4>
								<p><?php esc_html_e( 'Display the original activity without nested content when sharing.', 'buddypress-share' ); ?></p>
							</div>
						</label>
					</div>

					<div class="bp-share-radio-item">
						<label class="bp-share-radio-label">
							<input type="radio" 
							       name="bp_reshare_settings[reshare_share_activity]" 
							       value="child" 
							       <?php checked( 'child', $bp_reshare_settings_activity ); ?> />
							<span class="bp-share-radio-indicator"></span>
							<div class="bp-share-radio-content">
								<h4><?php esc_html_e( 'Child Activity', 'buddypress-share' ); ?></h4>
								<p><?php esc_html_e( 'Display the complete activity including any nested shared content.', 'buddypress-share' ); ?></p>
							</div>
						</label>
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
	</div>
</div>

<style>
/* Additional CSS for Share Settings Page */
.bp-share-setting-item {
	display: flex;
	align-items: flex-start;
	gap: 20px;
	padding: 20px 0;
	border-bottom: 1px solid #f0f0f0;
}

.bp-share-setting-item:last-child {
	border-bottom: none;
}

.bp-share-setting-control {
	flex-shrink: 0;
}

.bp-share-setting-info {
	flex: 1;
}

.bp-share-setting-title {
	margin: 0 0 8px 0;
	font-size: 16px;
	font-weight: 600;
	color: #333;
}

.bp-share-setting-description {
	margin: 0;
	font-size: 14px;
	color: #666;
	line-height: 1.5;
}

.bp-share-radio-group {
	display: flex;
	flex-direction: column;
	gap: 15px;
}

.bp-share-radio-item {
	background: #fff;
	border: 2px solid #e1e5e9;
	border-radius: 8px;
	transition: all 0.3s ease;
}

.bp-share-radio-item:hover {
	border-color: #667eea;
	box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}

.bp-share-radio-label {
	display: flex;
	align-items: flex-start;
	gap: 15px;
	padding: 20px;
	cursor: pointer;
	margin: 0;
}

.bp-share-radio-indicator {
	position: relative;
	width: 20px;
	height: 20px;
	border: 2px solid #ddd;
	border-radius: 50%;
	background: #fff;
	flex-shrink: 0;
	margin-top: 2px;
	transition: all 0.3s ease;
}

.bp-share-radio-label input[type="radio"] {
	position: absolute;
	opacity: 0;
	width: 0;
	height: 0;
}

.bp-share-radio-label input[type="radio"]:checked + .bp-share-radio-indicator {
	border-color: #667eea;
	background: #667eea;
}

.bp-share-radio-label input[type="radio"]:checked + .bp-share-radio-indicator::after {
	content: '';
	position: absolute;
	top: 50%;
	left: 50%;
	width: 6px;
	height: 6px;
	background: #fff;
	border-radius: 50%;
	transform: translate(-50%, -50%);
}

.bp-share-radio-content h4 {
	margin: 0 0 8px 0;
	font-size: 16px;
	font-weight: 600;
	color: #333;
}

.bp-share-radio-content p {
	margin: 0;
	font-size: 14px;
	color: #666;
	line-height: 1.5;
}

.bp-share-radio-label input[type="radio"]:checked ~ .bp-share-radio-content h4 {
	color: #667eea;
}

/* Focus styles for accessibility */
.bp-share-radio-label input[type="radio"]:focus + .bp-share-radio-indicator {
	outline: 2px solid #667eea;
	outline-offset: 2px;
}

.bp-share-toggle input:focus + .bp-share-slider {
	outline: 2px solid #667eea;
	outline-offset: 2px;
}

/* Responsive design */
@media (max-width: 768px) {
	.bp-share-setting-item {
		flex-direction: column;
		gap: 15px;
	}
	
	.bp-share-radio-label {
		padding: 15px;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	
	/**
	 * Form submission with loading state
	 */
	$('#bp_reshare_form').on('submit', function() {
		const $submitBtn = $('.bp-share-submit-button');
		const $spinner = $('.bp-share-spinner');
		
		$submitBtn.prop('disabled', true);
		$spinner.addClass('is-active');
		
		// Re-enable after 5 seconds as fallback
		setTimeout(function() {
			$submitBtn.prop('disabled', false);
			$spinner.removeClass('is-active');
		}, 5000);
	});

	/**
	 * Enhanced notice dismissal
	 */
	$(document).on('click', '.notice-dismiss', function() {
		$(this).closest('.bp-share-notice').fadeOut(300, function() {
			$(this).remove();
		});
	});

	/**
	 * Auto-hide success messages after 5 seconds
	 */
	setTimeout(function() {
		$('.bp-share-notice:visible').fadeOut(500);
	}, 5000);

	/**
	 * Enhanced radio button interactions
	 */
	$('.bp-share-radio-label').on('click', function() {
		const $this = $(this);
		const $radioGroup = $this.closest('.bp-share-radio-group');
		
		// Remove active state from siblings
		$radioGroup.find('.bp-share-radio-item').removeClass('active');
		
		// Add active state to clicked item
		$this.closest('.bp-share-radio-item').addClass('active');
	});

	// Set initial active state
	$('input[type="radio"]:checked').each(function() {
		$(this).closest('.bp-share-radio-item').addClass('active');
	});

	/**
	 * Accessibility improvements
	 */
	// Add ARIA labels to toggles
	$('.bp-share-toggle input').each(function() {
		const label = $(this).closest('.bp-share-setting-item').find('.bp-share-setting-title').text();
		$(this).attr('aria-label', label);
	});

	// Add ARIA labels to radio buttons
	$('.bp-share-radio-label input[type="radio"]').each(function() {
		const label = $(this).siblings('.bp-share-radio-content').find('h4').text();
		$(this).attr('aria-label', label);
	});

	/**
	 * Visual feedback for settings changes
	 */
	$('.bp-share-toggle input, .bp-share-radio-label input[type="radio"]').on('change', function() {
		// Add visual feedback that settings have changed
		const $form = $(this).closest('form');
		$form.addClass('has-changes');
		
		// Show save reminder
		if (!$('.bp-share-save-reminder').length) {
			const $reminder = $('<div class="bp-share-save-reminder">')
				.html('<p><strong>Settings changed.</strong> Don\'t forget to save your changes.</p>')
				.hide()
				.prependTo('.bp-share-form-wrapper')
				.fadeIn();
		}
	});

	// Remove reminder when form is submitted
	$('#bp_reshare_form').on('submit', function() {
		$('.bp-share-save-reminder').fadeOut(function() {
			$(this).remove();
		});
	});
});
</script>