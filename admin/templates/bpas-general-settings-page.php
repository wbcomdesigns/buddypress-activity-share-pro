<?php
/**
 * General Settings Template for BuddyPress Activity Share Pro
 *
 * This template displays the general settings page for the plugin.
 * Optimized for better performance and security on large sites.
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

// Optimize by loading all settings once instead of multiple get_site_option calls
$plugin_settings = array(
	'services_enable'        => get_site_option( 'bp_share_services_enable' ),
	'services_logout_enable' => get_site_option( 'bp_share_services_logout_enable' ),
	'extra_options'          => get_site_option( 'bp_share_services_extra' ),
	'social_services'        => get_site_option( 'bp_share_services', array() ),
);

// Determine if settings were saved
$bp_share_settings_save_notice = 'display:none';
if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) { //phpcs:ignore
	$bp_share_settings_save_notice = '';
}

/**
 * Available social services configuration.
 * Centralized configuration for better maintainability.
 *
 * @since 1.5.1
 */
$available_social_services = array(
	'Facebook'  => array(
		'label' => __( 'Facebook', 'buddypress-share' ),
		'icon'  => 'icon_Facebook',
	),
	'Twitter'   => array(
		'label' => __( 'Twitter', 'buddypress-share' ),
		'icon'  => 'icon_Twitter',
	),
	'Pinterest' => array(
		'label' => __( 'Pinterest', 'buddypress-share' ),
		'icon'  => 'icon_Pinterest',
	),
	'Linkedin'  => array(
		'label' => __( 'Linkedin', 'buddypress-share' ),
		'icon'  => 'icon_LinkedIn',
	),
	'Reddit'    => array(
		'label' => __( 'Reddit', 'buddypress-share' ),
		'icon'  => 'icon_Reddit',
	),
	'WordPress' => array(
		'label' => __( 'WordPress', 'buddypress-share' ),
		'icon'  => 'icon_WordPress',
	),
	'Pocket'    => array(
		'label' => __( 'Pocket', 'buddypress-share' ),
		'icon'  => 'icon_Pocket',
	),
	'Telegram'  => array(
		'label' => __( 'Telegram', 'buddypress-share' ),
		'icon'  => 'icon_Telegram',
	),
	'Bluesky'   => array(
		'label' => __( 'Bluesky', 'buddypress-share' ),
		'icon'  => 'icon_Bluesky',
	),
	'E-mail'    => array(
		'label' => __( 'E-mail', 'buddypress-share' ),
		'icon'  => 'icon_Gmail',
	),
	'Whatsapp'  => array(
		'label' => __( 'WhatsApp', 'buddypress-share' ),
		'icon'  => 'icon_WhatAapp',
	),
);

/**
 * Helper function to render social service items.
 *
 * @since 1.5.1
 * @param array  $services_config Available services configuration.
 * @param array  $enabled_services Currently enabled services.
 * @param string $list_type Type of list ('enabled' or 'disabled').
 */
function render_social_service_items( $services_config, $enabled_services, $list_type = 'disabled' ) {
	foreach ( $services_config as $service_key => $service_data ) {
		$is_enabled = ! empty( $enabled_services[ $service_key ] );
		
		// Show in disabled list if not enabled, and vice versa
		if ( ( 'disabled' === $list_type && ! $is_enabled ) || ( 'enabled' === $list_type && $is_enabled ) ) {
			$icon_class = esc_attr( $service_data['icon'] );
			$service_label = esc_html( $service_data['label'] );
			$service_name = esc_attr( 'icon_' . strtolower( str_replace( '-', '_', $service_key ) ) );
			
			echo '<li class="socialicon ' . $icon_class . '" name="' . $service_name . '">' . $service_label . '</li>';
		}
	}
}

?>
<div class="wbcom-tab-content">
	<div class="wbcom-wrapper-admin">
		<div class="wbcom-admin-title-section wbcom-flex">
			<h3 class="wbcom-welcome-title"><?php esc_html_e( 'General Settings', 'buddypress-share' ); ?></h3>
			<a href="<?php echo esc_url( 'https://docs.wbcomdesigns.com/doc_category/buddypress-activity-social-share/' ); ?>" class="wbcom-docslink" target="_blank"><?php esc_html_e( 'Documentation', 'buddypress-share' ); ?></a>
		</div>

		<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
			
			<!-- Success Message -->
			<div class="bpas-save-option-message notice notice-success is-dismissible" style="<?php echo esc_attr( $bp_share_settings_save_notice ); ?>">
				<p><strong><?php esc_html_e( 'Settings saved successfully.', 'buddypress-share' ); ?></strong></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'buddypress-share' ); ?></span>
				</button>
			</div>
			
			<!-- Error Message Container -->
			<div class="option-not-save-message"></div>
			
			<!-- Settings Form -->
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="bp_share_form">
				<?php 
				// Security nonces
				wp_nonce_field( 'bpas_general_settings_save', 'bpas_general_nonce' );
				?>
				<input type="hidden" name="action" value="bpas_save_general_settings" />
				
				<div class="form-table buddypress-profanity-admin-table">
					
					<!-- Enable Social Share Setting -->
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bp_share_services_enable">
								<strong><?php esc_html_e( 'Enable Social Share', 'buddypress-share' ); ?></strong>
							</label>
							<p class="description"><?php esc_html_e( 'Enable this option to show share button in activity page.', 'buddypress-share' ); ?></p>
						</div>
						<div id="bp_share_chb" class="wbcom-settings-section-options">
							<label class="switch">
								<input type="checkbox" 
								       name="bp_share_services_enable" 
								       id="bp_share_services_enable" 
								       value="1" 
								       <?php checked( '1', $plugin_settings['services_enable'] ); ?> />
								<span class="slider round"></span>
							</label>
						</div>
					</div>

					<!-- Social Share in Logout Mode Setting -->
					<div id="social_share_logout_wrap" class="wbcom-settings-section-wrap" style="<?php echo $plugin_settings['services_enable'] ? '' : 'display:none;'; ?>">
						<div class="wbcom-settings-section-options-heading">
							<label for="bp_share_services_logout_enable">
								<strong><?php esc_html_e( 'Social Share in Logout Mode', 'buddypress-share' ); ?></strong>
							</label>
							<p class="description"><?php esc_html_e( 'Enable this option to display social share icons when the user is logged out.', 'buddypress-share' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
							<label class="switch">
								<input type="checkbox" 
								       name="bp_share_services_logout_enable" 
								       id="bp_share_services_logout_enable" 
								       value="1" 
								       <?php checked( '1', $plugin_settings['services_logout_enable'] ); ?> />
								<span class="slider round"></span>
							</label>
						</div>
					</div>
				</div>

				<!-- Enable Sharing Sites Section -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="wbcom-social-share">
							<strong><?php esc_html_e( 'Enable Sharing Sites', 'buddypress-share' ); ?></strong>
						</label>
						<p class="description"><?php esc_html_e( 'Drag and drop social services between the disabled and enabled lists to configure which services are available for sharing.', 'buddypress-share' ); ?></p>
					</div>

					<div class="wbcom-settings-section-options">
						<section class="social_icon_section">
							
							<!-- Disabled Services List -->
							<ul id="drag_social_icon" class="social-services-list disabled-services">
								<h3><?php esc_html_e( 'Disabled Services', 'buddypress-share' ); ?></h3>
								<li class="list-info">
									<small><?php esc_html_e( 'Drag services from here to enable them', 'buddypress-share' ); ?></small>
								</li>
								<?php render_social_service_items( $available_social_services, $plugin_settings['social_services'], 'disabled' ); ?>
								
								<!-- Show message if no disabled services -->
								<?php if ( count( array_diff_key( $available_social_services, $plugin_settings['social_services'] ) ) === 0 ) : ?>
									<li class="no-services-message">
										<em><?php esc_html_e( 'All services are enabled', 'buddypress-share' ); ?></em>
									</li>
								<?php endif; ?>
							</ul>
							
							<!-- Enabled Services List -->
							<ul id="drag_icon_ul" class="social-services-list enabled-services">
								<h3><?php esc_html_e( 'Enabled Services', 'buddypress-share' ); ?></h3>
								<li class="list-info">
									<small><?php esc_html_e( 'Drag services from here to disable them', 'buddypress-share' ); ?></small>
								</li>
								<?php render_social_service_items( $available_social_services, $plugin_settings['social_services'], 'enabled' ); ?>
								
								<!-- Show message if no enabled services -->
								<?php if ( empty( $plugin_settings['social_services'] ) ) : ?>
									<li class="no-services-message">
										<em><?php esc_html_e( 'No services enabled', 'buddypress-share' ); ?></em>
									</li>
								<?php endif; ?>
							</ul>
						</section>
						
						<!-- Drag and Drop Instructions -->
						<div class="drag-drop-instructions">
							<p class="description">
								<strong><?php esc_html_e( 'Instructions:', 'buddypress-share' ); ?></strong>
								<?php esc_html_e( 'Drag social service icons between the lists to enable or disable them. Changes are saved automatically when you submit the form.', 'buddypress-share' ); ?>
							</p>
						</div>
					</div>
				</div>
				
				<!-- Popup Window Setting -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="bpas-popup-share">
							<strong><?php esc_html_e( 'Open as Popup Window', 'buddypress-share' ); ?></strong>
						</label>
						<p class="description"><?php esc_html_e( 'Default is set to open windows in a popup. If this option is disabled, services will open in a new tab instead of a popup.', 'buddypress-share' ); ?></p>
					</div>

					<div class="wbcom-settings-section-options">
						<label class="switch">
							<input type="checkbox" 
							       name="bp_share_services_open" 
							       id="bpas-popup-share" 
							       value="on"
							       <?php checked( 'on', $plugin_settings['extra_options']['bp_share_services_open'] ?? '' ); ?> />
							<span class="slider round"></span>
						</label>
					</div>
				</div>

				<!-- Hidden Fields for Social Services -->
				<input type="hidden" name="page_options" value="<?php echo esc_attr( $this->get_social_services_string( $plugin_settings['social_services'] ) ); ?>" />
				
				<!-- Submit Button -->
				<div class="wbcom-settings-section-wrap">
					<p class="submit">
						<input type="submit" 
						       name="bpas_submit_general_options" 
						       class="button button-primary bp_share_option_save" 
						       value="<?php esc_attr_e( 'Save Changes', 'buddypress-share' ); ?>" />
						<span class="spinner" style="float: none; margin: 0 10px;"></span>
					</p>
				</div>
			</form>

			<!-- Additional Settings Hook -->
			<?php 
			/**
			 * Action hook to add additional settings sections.
			 *
			 * @since 1.0.0
			 */
			do_action( 'bp_share_add_services_options' ); 
			?>
		</div>
	</div>
</div>

<style>
/* Optimized CSS for better UX */
.social_icon_section {
	display: flex;
	gap: 20px;
	margin: 20px 0;
	min-height: 200px;
}

.social-services-list {
	flex: 1;
	border: 2px dashed #ddd;
	border-radius: 8px;
	padding: 15px;
	background: #fafafa;
	min-height: 180px;
	transition: all 0.3s ease;
}

.social-services-list.ui-droppable-hover {
	border-color: #0073aa;
	background: #f0f8ff;
}

.social-services-list h3 {
	margin: 0 0 10px 0;
	padding: 0;
	font-size: 14px;
	font-weight: 600;
	color: #333;
	border-bottom: 1px solid #ddd;
	padding-bottom: 8px;
}

.social-services-list .list-info {
	list-style: none;
	margin-bottom: 10px;
	opacity: 0.7;
}

.social-services-list .socialicon {
	display: inline-block;
	margin: 5px;
	padding: 8px 12px;
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 4px;
	cursor: move;
	transition: all 0.2s ease;
	font-size: 12px;
}

.social-services-list .socialicon:hover {
	background: #0073aa;
	color: #fff;
	transform: translateY(-2px);
	box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.social-services-list .socialicon.ui-draggable-dragging {
	opacity: 0.7;
	transform: rotate(5deg);
	z-index: 1000;
}

.no-services-message {
	list-style: none;
	text-align: center;
	padding: 20px;
	color: #666;
}

.drag-drop-instructions {
	margin-top: 15px;
	padding: 15px;
	background: #fff;
	border-left: 4px solid #0073aa;
	border-radius: 4px;
}

/* Switch styling for better UX */
.switch {
	position: relative;
	display: inline-block;
	width: 60px;
	height: 34px;
}

.switch input {
	opacity: 0;
	width: 0;
	height: 0;
}

.slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	transition: .4s;
}

.slider:before {
	position: absolute;
	content: "";
	height: 26px;
	width: 26px;
	left: 4px;
	bottom: 4px;
	background-color: white;
	transition: .4s;
}

input:checked + .slider {
	background-color: #0073aa;
}

input:checked + .slider:before {
	transform: translateX(26px);
}

.slider.round {
	border-radius: 34px;
}

.slider.round:before {
	border-radius: 50%;
}

/* Loading state */
.bp_share_option_save:disabled {
	opacity: 0.6;
	cursor: not-allowed;
}

.spinner.is-active {
	visibility: visible;
}

/* Responsive design */
@media (max-width: 768px) {
	.social_icon_section {
		flex-direction: column;
		gap: 15px;
	}
	
	.wbcom-admin-title-section.wbcom-flex {
		flex-direction: column;
		gap: 10px;
	}
}
</style>

<script>
/**
 * Enhanced JavaScript for better UX
 */
jQuery(document).ready(function($) {
	
	/**
	 * Toggle logout option visibility based on main enable option
	 */
	function toggleLogoutOption() {
		const isEnabled = $('#bp_share_services_enable').is(':checked');
		$('#social_share_logout_wrap').toggle(isEnabled);
	}
	
	// Initial check on page load
	toggleLogoutOption();
	
	// Listen for changes on the enable checkbox
	$('#bp_share_services_enable').on('change', toggleLogoutOption);

	/**
	 * Form submission with loading state
	 */
	$('#bp_share_form').on('submit', function() {
		const $submitBtn = $('.bp_share_option_save');
		const $spinner = $('.spinner');
		
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
		$(this).closest('.notice').fadeOut(300, function() {
			$(this).remove();
		});
	});

	/**
	 * Auto-hide success messages after 5 seconds
	 */
	setTimeout(function() {
		$('.bpas-save-option-message:visible').fadeOut(500);
	}, 5000);

	/**
	 * Accessibility improvements
	 */
	// Add ARIA labels to switches
	$('.switch input').each(function() {
		const label = $(this).closest('.wbcom-settings-section-wrap').find('label strong').first().text();
		$(this).attr('aria-label', label);
	});

	// Add focus styles for keyboard navigation
	$('.switch input').on('focus', function() {
		$(this).next('.slider').addClass('focused');
	}).on('blur', function() {
		$(this).next('.slider').removeClass('focused');
	});
});
</script>

<?php
/**
 * Helper method to generate social services string for hidden field.
 * This should be added to the admin class, but included here for completeness.
 *
 * @since 1.5.1
 * @param array $social_services Enabled social services.
 * @return string Comma-separated string of service keys.
 */
if ( ! function_exists( 'get_social_services_string' ) ) {
	function get_social_services_string( $social_services ) {
		if ( empty( $social_services ) || ! is_array( $social_services ) ) {
			return '';
		}
		
		$service_keys = array_keys( $social_services );
		return implode( ',', array_map( 'esc_attr', $service_keys ) );
	}
}

// Use the function for the hidden field
if ( ! empty( $plugin_settings['social_services'] ) ) {
	$social_key_string = get_social_services_string( $plugin_settings['social_services'] );
} else {
	$social_key_string = '';
}
?>

<!-- Update the hidden field value -->
<script>
jQuery(document).ready(function($) {
	$('input[name="page_options"]').val('<?php echo esc_js( $social_key_string ); ?>');
});
</script>