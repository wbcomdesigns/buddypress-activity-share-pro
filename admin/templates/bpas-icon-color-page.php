<?php
/**
 * Clean Icon Settings Template for BuddyPress Activity Share Pro
 *
 * This template displays the icon settings page without inline styles/scripts.
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

// Get current icon settings
$bpas_icon_color_settings = get_option( 'bpas_icon_color_settings', array() );

// Determine if settings were saved
$bp_share_icon_settings_save_notice = 'display:none';
if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) { //phpcs:ignore
	$bp_share_icon_settings_save_notice = '';
}

// Available icon styles
$icon_styles = array(
	'circle'     => array(
		'name'        => __( 'Circle Style', 'buddypress-share' ),
		'description' => __( 'Modern circular icons with clean borders', 'buddypress-share' ),
		'image'       => BP_ACTIVITY_SHARE_PLUGIN_URL . 'admin/images/style_01.jpg',
		'preview'     => 'circle-preview'
	),
	'rec'        => array(
		'name'        => __( 'Rectangle Style', 'buddypress-share' ),
		'description' => __( 'Rectangular icons with rounded corners', 'buddypress-share' ),
		'image'       => BP_ACTIVITY_SHARE_PLUGIN_URL . 'admin/images/style_02.jpg',
		'preview'     => 'rectangle-preview'
	),
	'blackwhite' => array(
		'name'        => __( 'Black & White', 'buddypress-share' ),
		'description' => __( 'Minimalist monochrome design', 'buddypress-share' ),
		'image'       => BP_ACTIVITY_SHARE_PLUGIN_URL . 'admin/images/style_03.jpg',
		'preview'     => 'blackwhite-preview'
	),
	'baricon'    => array(
		'name'        => __( 'Bar Style', 'buddypress-share' ),
		'description' => __( 'Horizontal bar layout for sharing icons', 'buddypress-share' ),
		'image'       => BP_ACTIVITY_SHARE_PLUGIN_URL . 'admin/images/style_04.jpg',
		'preview'     => 'bar-preview'
	),
);

// Color settings with defaults
$color_settings = array(
	'bg_color' => array(
		'label' => __( 'Background Color', 'buddypress-share' ),
		'default' => '#667eea',
		'description' => __( 'Choose the background color for sharing icons.', 'buddypress-share' )
	),
	'text_color' => array(
		'label' => __( 'Text Color', 'buddypress-share' ),
		'default' => '#ffffff',
		'description' => __( 'Choose the text/icon color for sharing buttons.', 'buddypress-share' )
	),
	'hover_color' => array(
		'label' => __( 'Hover Color', 'buddypress-share' ),
		'default' => '#5a6fd8',
		'description' => __( 'Choose the hover color for sharing icons.', 'buddypress-share' )
	),
	'border_color' => array(
		'label' => __( 'Border Color', 'buddypress-share' ),
		'default' => '#e1e5e9',
		'description' => __( 'Choose the border color for sharing icons.', 'buddypress-share' )
	)
);

?>
<div class="bp-share-admin-content">
	<div class="bp-share-form-wrapper">
		
		<!-- Success Message -->
		<div class="bp-share-notice notice-success" style="<?php echo esc_attr( $bp_share_icon_settings_save_notice ); ?>">
			<p><strong><?php esc_html_e( 'Icon settings saved successfully.', 'buddypress-share' ); ?></strong></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'buddypress-share' ); ?></span>
			</button>
		</div>

		<!-- Settings Form -->
		<form method="post" action="options.php" id="bp_icon_form" class="bp-share-icon-settings">
			<?php
			settings_fields( 'bpas_icon_color_settings' );
			do_settings_sections( 'bpas_icon_color_settings' );
			?>

			<!-- Icon Style Selection -->
			<div class="bp-share-form-section">
				<div class="bp-share-section-header">
					<h3 class="bp-share-section-title">
						<?php esc_html_e( 'Choose Icon Style', 'buddypress-share' ); ?>
					</h3>
					<p class="bp-share-section-description">
						<?php esc_html_e( 'Select the visual style for sharing icons that best matches your site design.', 'buddypress-share' ); ?>
					</p>
				</div>

				<div class="bp-share-icon-styles-grid">
					<?php foreach ( $icon_styles as $style_key => $style_data ) : ?>
						<div class="bp-share-icon-style-option">
							<label for="icon_style_<?php echo esc_attr( $style_key ); ?>" class="bp-share-style-label">
								<input type="radio" 
								       id="icon_style_<?php echo esc_attr( $style_key ); ?>"
								       class="bp-share-style-radio" 
								       name="bpas_icon_color_settings[icon_style]" 
								       value="<?php echo esc_attr( $style_key ); ?>"
								       <?php checked( $style_key, $bpas_icon_color_settings['icon_style'] ?? 'circle' ); ?> />
								
								<div class="bp-share-style-preview">
									<?php if ( file_exists( str_replace( BP_ACTIVITY_SHARE_PLUGIN_URL, BP_ACTIVITY_SHARE_PLUGIN_PATH, $style_data['image'] ) ) ) : ?>
										<img src="<?php echo esc_url( $style_data['image'] ); ?>" 
										     alt="<?php echo esc_attr( $style_data['name'] ); ?>"
										     class="bp-share-style-image" 
										     loading="lazy" />
									<?php else : ?>
										<div class="bp-share-style-placeholder <?php echo esc_attr( $style_data['preview'] ); ?>">
											<span class="dashicons dashicons-format-image"></span>
											<p><?php echo esc_html( $style_data['name'] ); ?></p>
										</div>
									<?php endif; ?>
									
									<div class="bp-share-style-overlay">
										<span class="bp-share-style-check">
											<span class="dashicons dashicons-yes"></span>
										</span>
									</div>
								</div>
								
								<div class="bp-share-style-info">
									<h4 class="bp-share-style-name"><?php echo esc_html( $style_data['name'] ); ?></h4>
									<p class="bp-share-style-description"><?php echo esc_html( $style_data['description'] ); ?></p>
								</div>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Icon Color Customization -->
			<div class="bp-share-form-section">
				<div class="bp-share-section-header">
					<h3 class="bp-share-section-title">
						<?php esc_html_e( 'Color Customization', 'buddypress-share' ); ?>
					</h3>
					<p class="bp-share-section-description">
						<?php esc_html_e( 'Customize the colors of your sharing icons to match your brand.', 'buddypress-share' ); ?>
					</p>
				</div>

				<div class="bp-share-color-options">
					<?php foreach ( $color_settings as $color_key => $color_data ) : ?>
						<div class="bp-share-color-field">
							<label for="icon_<?php echo esc_attr( $color_key ); ?>" class="bp-share-color-label">
								<?php echo esc_html( $color_data['label'] ); ?>
							</label>
							<input type="text" 
							       id="icon_<?php echo esc_attr( $color_key ); ?>"
							       name="bpas_icon_color_settings[<?php echo esc_attr( $color_key ); ?>]" 
							       value="<?php echo esc_attr( $bpas_icon_color_settings[ $color_key ] ?? $color_data['default'] ); ?>" 
							       class="bp-share-color-picker" 
							       data-preview-target=".preview-icon"
							       data-default-color="<?php echo esc_attr( $color_data['default'] ); ?>" />
							<p class="description"><?php echo esc_html( $color_data['description'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>

				<!-- Live Preview Section -->
				<div class="bp-share-preview-section">
					<h4><?php esc_html_e( 'Live Preview', 'buddypress-share' ); ?></h4>
					<div class="bp-share-preview-container">
						<div class="bp-share-preview-icons">
							<span class="bp-share-preview-icon facebook" data-service="facebook">
								<span class="dashicons dashicons-facebook-alt"></span>
								<span class="icon-label"><?php esc_html_e( 'Facebook', 'buddypress-share' ); ?></span>
							</span>
							<span class="bp-share-preview-icon twitter" data-service="twitter">
								<span class="dashicons dashicons-twitter"></span>
								<span class="icon-label"><?php esc_html_e( 'Twitter', 'buddypress-share' ); ?></span>
							</span>
							<span class="bp-share-preview-icon linkedin" data-service="linkedin">
								<span class="dashicons dashicons-linkedin"></span>
								<span class="icon-label"><?php esc_html_e( 'LinkedIn', 'buddypress-share' ); ?></span>
							</span>
							<span class="bp-share-preview-icon email" data-service="email">
								<span class="dashicons dashicons-email"></span>
								<span class="icon-label"><?php esc_html_e( 'Email', 'buddypress-share' ); ?></span>
							</span>
						</div>
					</div>
					<p class="bp-share-preview-help">
						<?php esc_html_e( 'The preview above shows how your icons will appear on the frontend. Changes are applied immediately.', 'buddypress-share' ); ?>
					</p>
				</div>
			</div>

			<!-- Advanced Settings -->
			<div class="bp-share-form-section">
				<div class="bp-share-section-header">
					<h3 class="bp-share-section-title">
						<?php esc_html_e( 'Advanced Settings', 'buddypress-share' ); ?>
					</h3>
					<p class="bp-share-section-description">
						<?php esc_html_e( 'Additional customization options for icon display.', 'buddypress-share' ); ?>
					</p>
				</div>

				<div class="bp-share-advanced-options">
					<div class="bp-share-form-field">
						<label class="bp-share-toggle">
							<input type="checkbox" 
							       name="bpas_icon_color_settings[show_labels]" 
							       value="1"
							       <?php checked( 1, $bpas_icon_color_settings['show_labels'] ?? 1 ); ?> />
							<span class="bp-share-slider"></span>
						</label>
						<span class="bp-share-toggle-label">
							<?php esc_html_e( 'Show service labels with icons', 'buddypress-share' ); ?>
						</span>
						<p class="bp-share-field-help">
							<?php esc_html_e( 'When enabled, service names will be displayed alongside icons.', 'buddypress-share' ); ?>
						</p>
					</div>

					<div class="bp-share-form-field">
						<label class="bp-share-toggle">
							<input type="checkbox" 
							       name="bpas_icon_color_settings[animate_icons]" 
							       value="1"
							       <?php checked( 1, $bpas_icon_color_settings['animate_icons'] ?? 1 ); ?> />
							<span class="bp-share-slider"></span>
						</label>
						<span class="bp-share-toggle-label">
							<?php esc_html_e( 'Enable icon animations', 'buddypress-share' ); ?>
						</span>
						<p class="bp-share-field-help">
							<?php esc_html_e( 'Add smooth hover animations to sharing icons.', 'buddypress-share' ); ?>
						</p>
					</div>

					<div class="bp-share-form-field">
						<label for="icon_size" class="bp-share-field-label">
							<?php esc_html_e( 'Icon Size', 'buddypress-share' ); ?>
						</label>
						<select name="bpas_icon_color_settings[icon_size]" id="icon_size" class="bp-share-select">
							<option value="small" <?php selected( 'small', $bpas_icon_color_settings['icon_size'] ?? 'medium' ); ?>>
								<?php esc_html_e( 'Small', 'buddypress-share' ); ?>
							</option>
							<option value="medium" <?php selected( 'medium', $bpas_icon_color_settings['icon_size'] ?? 'medium' ); ?>>
								<?php esc_html_e( 'Medium', 'buddypress-share' ); ?>
							</option>
							<option value="large" <?php selected( 'large', $bpas_icon_color_settings['icon_size'] ?? 'medium' ); ?>>
								<?php esc_html_e( 'Large', 'buddypress-share' ); ?>
							</option>
						</select>
						<p class="description">
							<?php esc_html_e( 'Choose the size of sharing icons displayed on your site.', 'buddypress-share' ); ?>
						</p>
					</div>
				</div>
			</div>

			<!-- Submit Button -->
			<div class="bp-share-submit-section">
				<button type="submit" 
				        name="submit" 
				        class="bp-share-submit-button">
					<span class="dashicons dashicons-saved"></span>
					<?php esc_html_e( 'Save Icon Settings', 'buddypress-share' ); ?>
				</button>
				<span class="bp-share-spinner"></span>
				
				<div class="bp-share-save-info">
					<p class="description">
						<?php esc_html_e( 'Icon changes will be applied immediately across your site.', 'buddypress-share' ); ?>
					</p>
				</div>
			</div>
		</form>

		<!-- Reset to Defaults -->
		<div class="bp-share-reset-section">
			<h3><?php esc_html_e( 'Reset Settings', 'buddypress-share' ); ?></h3>
			<p><?php esc_html_e( 'Reset all icon settings to their default values.', 'buddypress-share' ); ?></p>
			<button type="button" class="button button-secondary bp-share-reset-button" data-confirm="<?php esc_attr_e( 'Are you sure you want to reset all icon settings to defaults? This action cannot be undone.', 'buddypress-share' ); ?>">
				<span class="dashicons dashicons-backup"></span>
				<?php esc_html_e( 'Reset to Defaults', 'buddypress-share' ); ?>
			</button>
		</div>
	</div>
</div>