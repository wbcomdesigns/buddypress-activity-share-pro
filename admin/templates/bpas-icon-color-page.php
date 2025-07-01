<?php
/**
 * Icon Settings Template for BuddyPress Activity Share Pro
 *
 * This template displays the icon settings page for the plugin.
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
	),
	'rec'        => array(
		'name'        => __( 'Rectangle Style', 'buddypress-share' ),
		'description' => __( 'Rectangular icons with rounded corners', 'buddypress-share' ),
		'image'       => BP_ACTIVITY_SHARE_PLUGIN_URL . 'admin/images/style_02.jpg',
	),
	'blackwhite' => array(
		'name'        => __( 'Black & White', 'buddypress-share' ),
		'description' => __( 'Minimalist monochrome design', 'buddypress-share' ),
		'image'       => BP_ACTIVITY_SHARE_PLUGIN_URL . 'admin/images/style_03.jpg',
	),
	'baricon'    => array(
		'name'        => __( 'Bar Style', 'buddypress-share' ),
		'description' => __( 'Horizontal bar layout for sharing icons', 'buddypress-share' ),
		'image'       => BP_ACTIVITY_SHARE_PLUGIN_URL . 'admin/images/style_04.jpg',
	),
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
		<form method="post" action="options.php" id="bp_icon_form">
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
										     class="bp-share-style-image" />
									<?php else : ?>
										<div class="bp-share-style-placeholder">
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
					<div class="bp-share-color-field">
						<label for="icon_bg_color" class="bp-share-color-label">
							<?php esc_html_e( 'Background Color', 'buddypress-share' ); ?>
						</label>
						<input type="text" 
						       id="icon_bg_color"
						       name="bpas_icon_color_settings[bg_color]" 
						       value="<?php echo esc_attr( $bpas_icon_color_settings['bg_color'] ?? '#667eea' ); ?>" 
						       class="bp-share-color-picker" />
						<p class="description"><?php esc_html_e( 'Choose the background color for sharing icons.', 'buddypress-share' ); ?></p>
					</div>

					<div class="bp-share-color-field">
						<label for="icon_text_color" class="bp-share-color-label">
							<?php esc_html_e( 'Text Color', 'buddypress-share' ); ?>
						</label>
						<input type="text" 
						       id="icon_text_color"
						       name="bpas_icon_color_settings[text_color]" 
						       value="<?php echo esc_attr( $bpas_icon_color_settings['text_color'] ?? '#ffffff' ); ?>" 
						       class="bp-share-color-picker" />
						<p class="description"><?php esc_html_e( 'Choose the text/icon color for sharing buttons.', 'buddypress-share' ); ?></p>
					</div>

					<div class="bp-share-color-field">
						<label for="icon_hover_color" class="bp-share-color-label">
							<?php esc_html_e( 'Hover Color', 'buddypress-share' ); ?>
						</label>
						<input type="text" 
						       id="icon_hover_color"
						       name="bpas_icon_color_settings[hover_color]" 
						       value="<?php echo esc_attr( $bpas_icon_color_settings['hover_color'] ?? '#5a6fd8' ); ?>" 
						       class="bp-share-color-picker" />
						<p class="description"><?php esc_html_e( 'Choose the hover color for sharing icons.', 'buddypress-share' ); ?></p>
					</div>
				</div>

				<!-- Live Preview -->
				<div class="bp-share-preview-section">
					<h4><?php esc_html_e( 'Live Preview', 'buddypress-share' ); ?></h4>
					<div class="bp-share-preview-container">
						<div class="bp-share-preview-icons">
							<span class="bp-share-preview-icon facebook" data-service="facebook">
								<i class="fab fa-facebook-f"></i>
								<span>Facebook</span>
							</span>
							<span class="bp-share-preview-icon twitter" data-service="twitter">
								<i class="fab fa-twitter"></i>
								<span>Twitter</span>
							</span>
							<span class="bp-share-preview-icon linkedin" data-service="linkedin">
								<i class="fab fa-linkedin-in"></i>
								<span>LinkedIn</span>
							</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Submit Button -->
			<div class="bp-share-submit-section">
				<?php submit_button( 
					__( 'Save Icon Settings', 'buddypress-share' ), 
					'primary', 
					'submit', 
					true, 
					array( 'class' => 'bp-share-submit-button' )
				); ?>
				
				<div class="bp-share-save-info">
					<p class="description">
						<?php esc_html_e( 'Icon changes will be applied immediately across your site.', 'buddypress-share' ); ?>
					</p>
				</div>
			</div>
		</form>
	</div>
</div>

<style>
/* Icon Settings Specific Styles */
.bp-share-icon-styles-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
	gap: 20px;
	margin: 20px 0;
}

.bp-share-icon-style-option {
	position: relative;
}

.bp-share-style-label {
	display: block;
	cursor: pointer;
	border: 2px solid #e1e5e9;
	border-radius: 12px;
	background: #fff;
	transition: all 0.3s ease;
	overflow: hidden;
	margin: 0;
}

.bp-share-style-label:hover {
	border-color: #667eea;
	transform: translateY(-2px);
	box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
}

.bp-share-style-radio {
	position: absolute;
	opacity: 0;
	width: 0;
	height: 0;
}

.bp-share-style-preview {
	position: relative;
	height: 150px;
	overflow: hidden;
	background: #f8f9fa;
	display: flex;
	align-items: center;
	justify-content: center;
}

.bp-share-style-image {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.bp-share-style-placeholder {
	text-align: center;
	color: #666;
}

.bp-share-style-placeholder .dashicons {
	font-size: 48px;
	margin-bottom: 10px;
	color: #ccc;
}

.bp-share-style-overlay {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(102, 126, 234, 0.9);
	display: flex;
	align-items: center;
	justify-content: center;
	opacity: 0;
	transition: opacity 0.3s ease;
}

.bp-share-style-check {
	background: #fff;
	border-radius: 50%;
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	color: #667eea;
	font-size: 20px;
}

.bp-share-style-radio:checked + .bp-share-style-preview .bp-share-style-overlay {
	opacity: 1;
}

.bp-share-style-radio:checked ~ .bp-share-style-label,
.bp-share-style-radio:checked + .bp-share-style-preview + .bp-share-style-info + .bp-share-style-label {
	border-color: #667eea;
	background: linear-gradient(135deg, #f0f8ff 0%, #e8f4fd 100%);
}

.bp-share-style-info {
	padding: 20px;
}

.bp-share-style-name {
	margin: 0 0 8px 0;
	font-size: 16px;
	font-weight: 600;
	color: #333;
}

.bp-share-style-description {
	margin: 0;
	font-size: 14px;
	color: #666;
	line-height: 1.5;
}

.bp-share-color-options {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 30px;
	margin: 20px 0;
}

.bp-share-color-field {
	background: #fff;
	padding: 20px;
	border: 1px solid #e1e5e9;
	border-radius: 8px;
}

.bp-share-color-label {
	display: block;
	font-weight: 600;
	margin-bottom: 10px;
	color: #333;
}

.bp-share-color-picker {
	width: 100%;
	height: 40px;
	border: 2px solid #e1e5e9;
	border-radius: 6px;
	padding: 0 10px;
	margin-bottom: 10px;
}

.bp-share-preview-section {
	margin-top: 30px;
	padding: 20px;
	background: #f8f9fa;
	border-radius: 8px;
}

.bp-share-preview-section h4 {
	margin: 0 0 15px 0;
	color: #333;
}

.bp-share-preview-container {
	text-align: center;
}

.bp-share-preview-icons {
	display: flex;
	justify-content: center;
	gap: 15px;
	flex-wrap: wrap;
}

.bp-share-preview-icon {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 10px 15px;
	background: #667eea;
	color: #fff;
	border-radius: 6px;
	text-decoration: none;
	transition: all 0.3s ease;
	cursor: pointer;
}

.bp-share-preview-icon:hover {
	background: #5a6fd8;
	transform: translateY(-2px);
}

.bp-share-preview-icon i {
	font-size: 16px;
}

/* Responsive design */
@media (max-width: 768px) {
	.bp-share-icon-styles-grid {
		grid-template-columns: 1fr;
	}
	
	.bp-share-color-options {
		grid-template-columns: 1fr;
		gap: 20px;
	}
	
	.bp-share-preview-icons {
		flex-direction: column;
		align-items: center;
	}
}

/* Focus styles for accessibility */
.bp-share-style-radio:focus + .bp-share-style-preview {
	outline: 2px solid #667eea;
	outline-offset: 2px;
}

.bp-share-color-picker:focus {
	outline: 2px solid #667eea;
	outline-offset: 2px;
	border-color: #667eea;
}
</style>

<script>
jQuery(document).ready(function($) {
	
	// Initialize WordPress color picker if available
	if ($.fn.wpColorPicker) {
		$('.bp-share-color-picker').wpColorPicker({
			change: function(event, ui) {
				updatePreview();
			}
		});
	}

	/**
	 * Update live preview when colors change
	 */
	function updatePreview() {
		const bgColor = $('#icon_bg_color').val() || '#667eea';
		const textColor = $('#icon_text_color').val() || '#ffffff';
		const hoverColor = $('#icon_hover_color').val() || '#5a6fd8';
		
		// Update preview icons
		$('.bp-share-preview-icon').css({
			'background-color': bgColor,
			'color': textColor
		});
		
		// Update hover styles dynamically
		const hoverStyle = `
			<style id="bp-share-preview-hover">
				.bp-share-preview-icon:hover {
					background-color: ${hoverColor} !important;
					color: ${textColor} !important;
				}
			</style>
		`;
		
		$('#bp-share-preview-hover').remove();
		$('head').append(hoverStyle);
	}

	/**
	 * Update preview when style changes
	 */
	$('.bp-share-style-radio').on('change', function() {
		const selectedStyle = $(this).val();
		
		// Update preview based on selected style
		$('.bp-share-preview-icon').removeClass('circle rec blackwhite baricon').addClass(selectedStyle);
		
		// Add visual feedback
		$(this).closest('.bp-share-icon-style-option').addClass('selected')
			.siblings().removeClass('selected');
	});

	/**
	 * Form submission with loading state
	 */
	$('#bp_icon_form').on('submit', function() {
		const $submitBtn = $('.bp-share-submit-button');
		const $spinner = $('<span class="bp-share-spinner is-active"></span>');
		
		$submitBtn.prop('disabled', true).after($spinner);
		
		// Re-enable after 5 seconds as fallback
		setTimeout(function() {
			$submitBtn.prop('disabled', false);
			$spinner.remove();
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
	 * Initialize preview and selected state
	 */
	updatePreview();
	$('.bp-share-style-radio:checked').trigger('change');

	/**
	 * Color field enhancements
	 */
	$('.bp-share-color-picker').on('input', function() {
		updatePreview();
		
		// Add visual feedback that settings have changed
		$(this).closest('form').addClass('has-changes');
	});

	/**
	 * Accessibility improvements
	 */
	// Add ARIA labels to style options
	$('.bp-share-style-radio').each(function() {
		const label = $(this).siblings('.bp-share-style-info').find('.bp-share-style-name').text();
		$(this).attr('aria-label', label);
	});

	// Keyboard navigation for style selection
	$('.bp-share-style-radio').on('keydown', function(e) {
		const $current = $(this);
		let $next;
		
		switch(e.which) {
			case 37: // Left arrow
			case 38: // Up arrow
				e.preventDefault();
				$next = $current.closest('.bp-share-icon-style-option').prev().find('.bp-share-style-radio');
				if ($next.length === 0) {
					$next = $('.bp-share-style-radio').last();
				}
				$next.focus().prop('checked', true).trigger('change');
				break;
				
			case 39: // Right arrow
			case 40: // Down arrow
				e.preventDefault();
				$next = $current.closest('.bp-share-icon-style-option').next().find('.bp-share-style-radio');
				if ($next.length === 0) {
					$next = $('.bp-share-style-radio').first();
				}
				$next.focus().prop('checked', true).trigger('change');
				break;
		}
	});
});
</script>