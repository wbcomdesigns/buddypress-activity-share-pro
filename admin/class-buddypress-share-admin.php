<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin
 */

if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * UPDATED: Modern CDN integration with Font Awesome 5.15.4 and optimized asset loading.
 * UPDATED: Added license management system integration.
 * UPDATED: Added simple asset helper functions for minification and RTL support.
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 * @since      1.0.0
 */
class Buddypress_Share_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name The name of this plugin.
	 * @param    string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Display social networks settings section.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function bp_share_social_networks_page() {
		// Get current settings
		$bp_share_services_enable = get_site_option( 'bp_share_services_enable', 1 );
		$bp_share_services_logout_enable = get_site_option( 'bp_share_services_logout_enable', 1 );
		$bp_share_services_extra = get_site_option( 'bp_share_services_extra', array( 'bp_share_services_open' => 'on' ) );
		$bp_share_services_open = isset( $bp_share_services_extra['bp_share_services_open'] ) ? $bp_share_services_extra['bp_share_services_open'] : 'on';
		
		// Get enabled services
		$enabled_services = get_site_option( 'bp_share_services', array() );
		if ( ! is_array( $enabled_services ) ) {
			$enabled_services = array();
		}
		
		// Migrate Twitter to X if it exists
		if ( isset( $enabled_services['Twitter'] ) ) {
			$new_services = array();
			foreach ( $enabled_services as $key => $value ) {
				if ( $key === 'Twitter' ) {
					$new_services['X'] = 'X (Twitter)';
				} else {
					$new_services[$key] = $value;
				}
			}
			$enabled_services = $new_services;
			update_site_option( 'bp_share_services', $enabled_services );
		}
		
		// Services should already be set by activator, but handle edge case
		if ( empty( $enabled_services ) ) {
			// Use the default services method
			$enabled_services = $this->get_default_services();
			// Save to database for consistency
			update_site_option( 'bp_share_services', $enabled_services );
		}
		
		$all_services = $this->get_all_available_services();
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
						<h3><?php esc_html_e( 'Sharing Settings', 'buddypress-share' ); ?></h3>
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
								<span class="toggle-label"><?php esc_html_e( 'Enable Social Sharing', 'buddypress-share' ); ?></span>
								<p class="description"><?php esc_html_e( 'Allow users to share activities on social networks', 'buddypress-share' ); ?></p>
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
								<span class="toggle-label"><?php esc_html_e( 'Guest Sharing', 'buddypress-share' ); ?></span>
								<p class="description"><?php esc_html_e( 'Allow visitors to share public activities', 'buddypress-share' ); ?></p>
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
								<span class="toggle-label"><?php esc_html_e( 'Popup Windows', 'buddypress-share' ); ?></span>
								<p class="description"><?php esc_html_e( 'Open sharing links in small popup windows', 'buddypress-share' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<h2><?php esc_html_e( 'Available Social Networks', 'buddypress-share' ); ?></h2>
			<p><?php esc_html_e( 'Drag social networks between the lists to enable or disable them. Reorder enabled services by dragging them within the list.', 'buddypress-share' ); ?></p>
			
			<input type="hidden" name="bp_share_services_serialized" id="bp_share_services_serialized" value="<?php echo esc_attr( serialize( $enabled_services ) ); ?>" />
			
			<div class="social_icon_section">
				<div class="social-services-list enabled-services">
					<h3><?php esc_html_e( 'Active Networks', 'buddypress-share' ); ?></h3>
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
								<?php esc_html_e( 'No networks enabled. Drag networks from the inactive list to enable them.', 'buddypress-share' ); ?>
							</li>
						<?php endif; ?>
					</ul>
				</div>

				<div class="social-services-list disabled-services">
					<h3><?php esc_html_e( 'Inactive Networks', 'buddypress-share' ); ?></h3>
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
								<?php esc_html_e( 'All networks are active. Drag networks from the active list to disable them.', 'buddypress-share' ); ?>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>

			<?php submit_button( __( 'Save Settings', 'buddypress-share' ) ); ?>
		</form>
		
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			function updateServicesHiddenField() {
				var enabledServices = {};
				$('#drag_icon_ul .socialicon[data-service]').each(function() {
					var service = $(this).data('service');
					var name = $(this).text().trim();
					if (service && name) {
						enabledServices[service] = name;
					}
				});
				$('#bp_share_services_serialized').val(JSON.stringify(enabledServices));
			}
			
			$('#drag_icon_ul, #drag_social_icon').on('sortstop', function(event, ui) {
				updateServicesHiddenField();
				
				var $item = ui.item;
				var serviceName = $item.data('service') || $item.text().trim();
				var isInEnabledList = $item.closest('#drag_icon_ul').length > 0;
				
				var ajaxAction = isInEnabledList ? 'wss_social_icons' : 'wss_social_remove_icons';
				var dataField = isInEnabledList ? 'term_name' : 'icon_name';
				
				$.ajax({
					url: bp_share_admin_vars.ajax_url,
					type: 'POST',
					data: {
						action: ajaxAction,
						[dataField]: serviceName,
						nonce: bp_share_admin_vars.nonce
					},
					success: function(response) {
						if (response.success) {
							updateNoServicesMessages();
						} else {
							// Failed to update service
						}
					},
					error: function(xhr, status, error) {
						// AJAX request failed
					}
				});
			});
			
			function updateNoServicesMessages() {
				var $enabledList = $('#drag_icon_ul');
				var $enabledItems = $enabledList.find('.socialicon[data-service]');
				var $enabledMessage = $enabledList.find('.no-services-message');
				
				if ($enabledItems.length === 0) {
					if ($enabledMessage.length === 0) {
						$enabledList.append('<li class="no-services-message">No networks enabled. Drag networks from the inactive list to enable them.</li>');
					}
				} else {
					$enabledMessage.remove();
				}

				var $availableList = $('#drag_social_icon');
				var $availableItems = $availableList.find('.socialicon[data-service]');
				var $availableMessage = $availableList.find('.no-services-message');
				
				if ($availableItems.length === 0) {
					if ($availableMessage.length === 0) {
						$availableList.append('<li class="no-services-message">All networks are active. Drag networks from the active list to disable them.</li>');
					}
				} else {
					$availableMessage.remove();
				}
			}
			
			updateNoServicesMessages();
		});
		</script>
		<?php
	}

	/**
	 * Display restrictions page for content sharing controls.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function bp_share_restrictions_page() {
		// Get current settings
		$bp_reshare_settings = get_site_option( 'bp_reshare_settings', array() );
		$bp_reshare_settings_activity = isset( $bp_reshare_settings['reshare_share_activity'] ) ? $bp_reshare_settings['reshare_share_activity'] : 'parent';
		?>
		
		<form method="post" action="options.php">
			<?php
			settings_fields( 'bp_reshare_settings' );
			do_settings_sections( 'bp_reshare_settings' );
			?>

			<h2><?php esc_html_e( 'Sharing Restrictions', 'buddypress-share' ); ?></h2>
			<p><?php esc_html_e( 'Control which types of content can be shared and how they are displayed.', 'buddypress-share' ); ?></p>

			<div class="bp-share-settings-grid">
				<div class="bp-share-settings-card">
					<div class="card-header">
						<h3><?php esc_html_e( 'Content Restrictions', 'buddypress-share' ); ?></h3>
					</div>
					<div class="card-body">
						<p class="card-description"><?php esc_html_e( 'Disable sharing for specific content types', 'buddypress-share' ); ?></p>
						
						<div class="bp-share-checkbox-group">
							<label class="bp-share-checkbox-item">
								<input type="checkbox" 
								       name="bp_reshare_settings[disable_post_reshare_activity]" 
								       id="disable_post_reshare_activity"
								       value="1" 
								       <?php checked( 1, isset( $bp_reshare_settings['disable_post_reshare_activity'] ) ? $bp_reshare_settings['disable_post_reshare_activity'] : 0 ); ?> />
								<span class="checkbox-label">
									<span class="checkbox-icon dashicons dashicons-admin-post"></span>
									<?php esc_html_e( 'Blog Posts', 'buddypress-share' ); ?>
								</span>
							</label>
							
							<label class="bp-share-checkbox-item">
								<input type="checkbox" 
								       name="bp_reshare_settings[disable_my_profile_reshare_activity]" 
								       id="disable_my_profile_reshare_activity"
								       value="1" 
								       <?php checked( 1, isset( $bp_reshare_settings['disable_my_profile_reshare_activity'] ) ? $bp_reshare_settings['disable_my_profile_reshare_activity'] : 0 ); ?> />
								<span class="checkbox-label">
									<span class="checkbox-icon dashicons dashicons-admin-users"></span>
									<?php esc_html_e( 'User Profiles', 'buddypress-share' ); ?>
								</span>
							</label>
							
							<label class="bp-share-checkbox-item">
								<input type="checkbox" 
								       name="bp_reshare_settings[disable_group_reshare_activity]" 
								       id="disable_group_reshare_activity"
								       value="1" 
								       <?php checked( 1, isset( $bp_reshare_settings['disable_group_reshare_activity'] ) ? $bp_reshare_settings['disable_group_reshare_activity'] : 0 ); ?> />
								<span class="checkbox-label">
									<span class="checkbox-icon dashicons dashicons-groups"></span>
									<?php esc_html_e( 'Groups', 'buddypress-share' ); ?>
								</span>
							</label>
						</div>
					</div>
				</div>

				<div class="bp-share-settings-card">
					<div class="card-header">
						<h3><?php esc_html_e( 'Display Options', 'buddypress-share' ); ?></h3>
					</div>
					<div class="card-body">
						<p class="card-description"><?php esc_html_e( 'Choose how shared activities appear in the feed', 'buddypress-share' ); ?></p>
						
						<div class="bp-share-radio-group">
							<label class="bp-share-radio-item">
								<input type="radio" 
								       name="bp_reshare_settings[reshare_share_activity]" 
								       id="reshare_share_activity_parent"
								       value="parent" 
								       <?php checked( 'parent', $bp_reshare_settings_activity ); ?> />
								<span class="radio-label">
									<strong><?php esc_html_e( 'Simple View', 'buddypress-share' ); ?></strong>
									<span class="radio-description"><?php esc_html_e( 'Show only the original activity content', 'buddypress-share' ); ?></span>
								</span>
							</label>
							
							<label class="bp-share-radio-item">
								<input type="radio" 
								       name="bp_reshare_settings[reshare_share_activity]" 
								       id="reshare_share_activity_child"
								       value="child" 
								       <?php checked( 'child', $bp_reshare_settings_activity ); ?> />
								<span class="radio-label">
									<strong><?php esc_html_e( 'Detailed View', 'buddypress-share' ); ?></strong>
									<span class="radio-description"><?php esc_html_e( 'Include nested content and full context', 'buddypress-share' ); ?></span>
								</span>
							</label>
						</div>
					</div>
				</div>
			</div>

			<?php submit_button( __( 'Save Restrictions', 'buddypress-share' ) ); ?>
		</form>
		<?php
	}

	/**
	 * Display settings page combining icon styles and visual settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function bp_share_display_settings_page() {
		// Get current icon settings
		$bpas_icon_color_settings = get_option( 'bpas_icon_color_settings', array() );
		$current_style = isset( $bpas_icon_color_settings['icon_style'] ) ? $bpas_icon_color_settings['icon_style'] : 'circle';
		
		// Available styles
		$icon_styles = array(
			'circle'     => __( 'Circle Style', 'buddypress-share' ),
			'rec'        => __( 'Rectangle Style', 'buddypress-share' ),
			'blackwhite' => __( 'Black & White', 'buddypress-share' ),
			'baricon'    => __( 'Bar Style', 'buddypress-share' ),
		);
		?>
		
		<form method="post" action="options.php">
			<?php
			settings_fields( 'bpas_icon_color_settings' );
			do_settings_sections( 'bpas_icon_color_settings' );
			?>

			<h2><?php esc_html_e( 'Display Settings', 'buddypress-share' ); ?></h2>
			<p><?php esc_html_e( 'Customize how the sharing buttons appear on your site.', 'buddypress-share' ); ?></p>

			<div class="bp-share-settings-grid">
				<div class="bp-share-settings-card">
					<div class="card-header">
						<h3><?php esc_html_e( 'Icon Style', 'buddypress-share' ); ?></h3>
					</div>
					<div class="card-body">
						<div class="bp-share-style-selector">
							<?php foreach ( $icon_styles as $style_key => $style_name ) : ?>
								<label class="style-option <?php echo esc_attr( $current_style === $style_key ? 'selected' : '' ); ?>">
									<input type="radio" 
									       name="bpas_icon_color_settings[icon_style]" 
									       id="icon_style_<?php echo esc_attr( $style_key ); ?>"
									       value="<?php echo esc_attr( $style_key ); ?>" 
									       <?php checked( $style_key, $current_style ); ?> />
									<span class="style-preview <?php echo esc_attr( $style_key ); ?>"></span>
									<span class="style-name"><?php echo esc_html( $style_name ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="bp-share-settings-card color-settings-card">
					<div class="card-header">
						<h3><?php esc_html_e( 'Color Settings', 'buddypress-share' ); ?></h3>
					</div>
					<div class="card-body">
						<div class="color-setting-group">
							<label for="bg_color"><?php esc_html_e( 'Background Color', 'buddypress-share' ); ?></label>
							<div class="color-input-wrapper">
								<input type="text" 
								       name="bpas_icon_color_settings[bg_color]" 
								       id="bg_color"
								       value="<?php echo esc_attr( $bpas_icon_color_settings['bg_color'] ?? '#667eea' ); ?>" 
								       class="bp-share-color-picker" />
								<p class="description"><?php esc_html_e( 'Primary background color for sharing icons', 'buddypress-share' ); ?></p>
							</div>
						</div>
						
						<div class="color-setting-group">
							<label for="text_color"><?php esc_html_e( 'Icon Color', 'buddypress-share' ); ?></label>
							<div class="color-input-wrapper">
								<input type="text" 
								       name="bpas_icon_color_settings[text_color]" 
								       id="text_color"
								       value="<?php echo esc_attr( $bpas_icon_color_settings['text_color'] ?? '#ffffff' ); ?>" 
								       class="bp-share-color-picker" />
								<p class="description"><?php esc_html_e( 'Color for icons and text', 'buddypress-share' ); ?></p>
							</div>
						</div>
						
						<div class="color-setting-group">
							<label for="hover_color"><?php esc_html_e( 'Hover Color', 'buddypress-share' ); ?></label>
							<div class="color-input-wrapper">
								<input type="text" 
								       name="bpas_icon_color_settings[hover_color]" 
								       id="hover_color"
								       value="<?php echo esc_attr( $bpas_icon_color_settings['hover_color'] ?? '#5a6fd8' ); ?>" 
								       class="bp-share-color-picker" />
								<p class="description"><?php esc_html_e( 'Color when hovering over icons', 'buddypress-share' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php submit_button( __( 'Save Display Settings', 'buddypress-share' ) ); ?>
		</form>
		<?php
	}

	// License functions removed - plugin runs without restrictions

	/**
	 * Register plugin settings.
	 * 
	 * @since    1.0.0
	 * @access   public
	 */
	public function bpas_register_setting() {
		register_setting( 'bp_share_general_settings', 'bp_share_services_enable', 'absint' );
		register_setting( 'bp_share_general_settings', 'bp_share_services_logout_enable', 'absint' );
		register_setting( 'bp_share_general_settings', 'bp_share_services_extra', array( $this, 'sanitize_extra_settings' ) );
		register_setting( 'bp_share_general_settings', 'bp_share_services_serialized', 'sanitize_text_field' );
		
		register_setting( 'bp_reshare_settings', 'bp_reshare_settings', array( $this, 'sanitize_reshare_settings' ) );
		register_setting( 'bpas_icon_color_settings', 'bpas_icon_color_settings', array( $this, 'sanitize_icon_settings' ) );
		
		add_action( 'update_option_bp_share_services_enable', array( $this, 'sync_to_site_option' ), 10, 3 );
		add_action( 'update_option_bp_share_services_logout_enable', array( $this, 'sync_to_site_option' ), 10, 3 );
		add_action( 'update_option_bp_share_services_extra', array( $this, 'sync_extra_to_site_option' ), 10, 3 );
		add_action( 'update_option_bp_share_services_serialized', array( $this, 'sync_services_to_site_option' ), 10, 3 );
		add_action( 'update_option_bp_reshare_settings', array( $this, 'sync_reshare_to_site_option' ), 10, 3 );
	}

	/**
	 * AJAX handler for adding social icons.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function wss_social_icons() {
		// Verify nonce
		$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! wp_verify_nonce( $nonce, 'bp_share_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security nonce check failed.', 'buddypress-share' ) ) );
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'buddypress-share' ) ) );
		}
		
		$service_name = filter_input( INPUT_POST, 'term_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		
		if ( empty( $service_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Service name is required.', 'buddypress-share' ) ) );
		}
		
		$allowed_services = $this->get_all_available_services();
		if ( ! array_key_exists( $service_name, $allowed_services ) ) {
			wp_send_json_error( array( 
				'message' => sprintf( __( 'Invalid service name: %s', 'buddypress-share' ), $service_name ),
				'allowed_services' => array_keys( $allowed_services ),
				'received_service' => $service_name
			) );
		}
		
		$current_services = get_site_option( 'bp_share_services', array() );
		if ( ! is_array( $current_services ) ) {
			$current_services = array();
		}
		
		$current_services[ $service_name ] = $allowed_services[ $service_name ];
		$updated = update_site_option( 'bp_share_services', $current_services );
		
		if ( $updated || isset( $current_services[ $service_name ] ) ) {
			wp_send_json_success( array( 
				'message' => sprintf( __( 'Service "%s" added successfully.', 'buddypress-share' ), $service_name ),
				'service' => $service_name,
				'all_services' => $current_services
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to add service to database.', 'buddypress-share' ) ) );
		}
	}

	/**
	 * AJAX handler for removing social icons.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function wss_social_remove_icons() {
		// Verify nonce
		$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! wp_verify_nonce( $nonce, 'bp_share_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security nonce check failed.', 'buddypress-share' ) ) );
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'buddypress-share' ) ) );
		}
		
		$service_name = filter_input( INPUT_POST, 'icon_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		
		if ( empty( $service_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Service name is required.', 'buddypress-share' ) ) );
		}
		
		$current_services = get_site_option( 'bp_share_services', array() );
		if ( ! is_array( $current_services ) ) {
			$current_services = array();
		}
		
		if ( isset( $current_services[ $service_name ] ) ) {
			unset( $current_services[ $service_name ] );
			$updated = update_site_option( 'bp_share_services', $current_services );
			
			if ( $updated || ! isset( $current_services[ $service_name ] ) ) {
				wp_send_json_success( array( 
					'message' => sprintf( __( 'Service "%s" removed successfully.', 'buddypress-share' ), $service_name ),
					'service' => $service_name,
					'all_services' => $current_services
				) );
			} else {
				wp_send_json_error( array( 'message' => __( 'Failed to remove service from database.', 'buddypress-share' ) ) );
			}
		} else {
			wp_send_json_error( array( 
				'message' => sprintf( __( 'Service "%s" not found in enabled services.', 'buddypress-share' ), $service_name ),
				'current_services' => array_keys( $current_services )
			) );
		}
	}

	/**
	 * Get all available social services.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   array All available social services.
	 */
	private function get_all_available_services() {
		// Define all available services in one place
		$services = array(
			'Facebook'  => 'Facebook',
			'X'         => 'X (Twitter)',
			'LinkedIn'  => 'LinkedIn',
			'Pinterest' => 'Pinterest',
			'Reddit'    => 'Reddit',
			'WordPress' => 'WordPress',
			'Pocket'    => 'Pocket',
			'Telegram'  => 'Telegram',
			'Bluesky'   => 'Bluesky',
			'WhatsApp'  => 'WhatsApp',
			'E-mail'    => 'E-mail',
			'Copy-Link' => 'Copy Link',
		);
		
		/**
		 * Filter the available social sharing services.
		 *
		 * @since 1.5.2
		 * @param array $services Array of available services (key => label).
		 */
		return apply_filters( 'bp_share_available_services', $services );
	}
	
	/**
	 * Get default enabled services for first install.
	 *
	 * @since    1.5.3
	 * @access   private
	 * @return   array Default enabled services.
	 */
	private function get_default_services() {
		return array(
			'Facebook'  => 'Facebook',
			'X'         => 'X (Twitter)',
			'LinkedIn'  => 'LinkedIn',
			'WhatsApp'  => 'WhatsApp',
			'E-mail'    => 'E-mail',
			'Copy-Link' => 'Copy Link',
		);
	}

	/**
	 * Clear public settings cache when admin settings are updated.
	 *
	 * @since    1.5.1
	 * @access   public
	 */
	public function clear_public_settings_cache() {
		wp_cache_delete( 'bp_share_plugin_settings', 'buddypress_share' );
		delete_transient( 'bp_share_settings_cache' );
		do_action( 'bp_share_clear_public_cache' );
	}

	/**
	 * Sync methods that don't cause recursion.
	 *
	 * @since    1.5.2
	 * @access   public
	 */
	public function sync_to_site_option( $old_value, $value, $option ) {
		update_site_option( $option, $value );
	}

	/**
	 * Sync extra settings to site option.
	 *
	 * @since    1.5.2
	 * @access   public
	 * @param    mixed  $old_value The old option value.
	 * @param    mixed  $value     The new option value.
	 * @param    string $option    The option name.
	 * @return   void
	 */
	public function sync_extra_to_site_option( $old_value, $value, $option ) {
		update_site_option( 'bp_share_services_extra', $value );
	}

	/**
	 * Sync services to site option.
	 *
	 * @since    1.5.2
	 * @access   public
	 * @param    mixed  $old_value The old option value.
	 * @param    mixed  $value     The new option value.
	 * @param    string $option    The option name.
	 * @return   void
	 */
	public function sync_services_to_site_option( $old_value, $value, $option ) {
		$services = json_decode( $value, true );
		if ( ! is_array( $services ) && is_string( $value ) ) {
			// Safely attempt to unserialize without error suppression
			$services = maybe_unserialize( $value );
		}
		
		if ( is_array( $services ) ) {
			$sanitized_services = $this->sanitize_services_array( $services );
			update_site_option( 'bp_share_services', $sanitized_services );
		}
	}

	/**
	 * Sync reshare settings to site option.
	 *
	 * @since    1.5.2
	 * @access   public
	 * @param    mixed  $old_value The old option value.
	 * @param    mixed  $value     The new option value.
	 * @param    string $option    The option name.
	 * @return   void
	 */
	public function sync_reshare_to_site_option( $old_value, $value, $option ) {
		update_site_option( 'bp_reshare_settings', $value );
	}

	/**
	 * Sanitize extra settings input.
	 *
	 * @since    1.5.2
	 * @access   public
	 * @param    array $input The settings input to sanitize.
	 * @return   array Sanitized settings.
	 */
	public function sanitize_extra_settings( $input ) {
		if ( ! is_array( $input ) ) {
			$input = array();
		}
		
		/**
		 * Action before sanitizing extra settings.
		 *
		 * @since 1.5.2
		 * @param array $input The raw input data.
		 */
		do_action( 'bp_share_before_sanitize_extra_settings', $input );
		
		$sanitized = array();
		if ( isset( $input['bp_share_services_open'] ) ) {
			$sanitized['bp_share_services_open'] = sanitize_text_field( $input['bp_share_services_open'] );
		} else {
			$sanitized['bp_share_services_open'] = '';
		}
		
		/**
		 * Filter the sanitized extra settings.
		 *
		 * @since 1.5.2
		 * @param array $sanitized The sanitized settings.
		 * @param array $input     The raw input data.
		 */
		$sanitized = apply_filters( 'bp_share_sanitized_extra_settings', $sanitized, $input );
		
		/**
		 * Action after sanitizing extra settings.
		 *
		 * @since 1.5.2
		 * @param array $sanitized The sanitized settings.
		 * @param array $input     The raw input data.
		 */
		do_action( 'bp_share_after_sanitize_extra_settings', $sanitized, $input );
		
		return $sanitized;
	}

	public function sanitize_services_array( $services ) {
		if ( ! is_array( $services ) ) {
			return array();
		}
		
		$sanitized = array();
		$allowed_services = $this->get_all_available_services();
		$has_x = false;
		
		foreach ( $services as $key => $value ) {
			$sanitized_key = sanitize_text_field( $key );
			
			// Skip Twitter entries completely
			if ( $sanitized_key === 'Twitter' ) {
				// If we don't have X yet, add it instead of Twitter
				if ( ! $has_x && ! isset( $sanitized['X'] ) ) {
					$sanitized['X'] = 'X (Twitter)';
					$has_x = true;
				}
				continue;
			}
			
			if ( ! array_key_exists( $sanitized_key, $allowed_services ) ) {
				continue;
			}
			
			// Track if we've added X
			if ( $sanitized_key === 'X' ) {
				$has_x = true;
			}
			
			$sanitized_value = $allowed_services[ $sanitized_key ];
			
			if ( ! empty( $sanitized_key ) && ! empty( $sanitized_value ) ) {
				$sanitized[ $sanitized_key ] = $sanitized_value;
			}
		}
		
		return $sanitized;
	}

	/**
	 * Sanitize icon color settings.
	 *
	 * @since    1.5.2
	 * @access   public
	 * @param    array $settings The icon settings input to sanitize.
	 * @return   array Sanitized icon settings.
	 */
	public function sanitize_icon_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return array( 'icon_style' => 'circle' );
		}
		
		$sanitized = array();
		$allowed_styles = array( 'circle', 'rec', 'blackwhite', 'baricon' );
		
		$sanitized['icon_style'] = isset( $settings['icon_style'] ) && 
			in_array( $settings['icon_style'], $allowed_styles, true ) ? 
			$settings['icon_style'] : 'circle';
		
		$color_fields = array( 'bg_color', 'text_color', 'hover_color', 'border_color' );
		foreach ( $color_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$color = sanitize_hex_color( $settings[ $field ] );
				if ( $color ) {
					$sanitized[ $field ] = $color;
				}
			}
		}
		
		return $sanitized;
	}

	/**
	 * Sanitize reshare settings.
	 *
	 * @since    1.5.2
	 * @access   public
	 * @param    array $settings The reshare settings input to sanitize.
	 * @return   array Sanitized reshare settings.
	 */
	public function sanitize_reshare_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return array( 'reshare_share_activity' => 'parent' );
		}
		
		$sanitized = array();
		
		$boolean_fields = array(
			'disable_post_reshare_activity',
			'disable_my_profile_reshare_activity',
			'disable_group_reshare_activity',
		);
		
		foreach ( $boolean_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$sanitized[ $field ] = (bool) $settings[ $field ];
			}
		}
		
		$allowed_modes = array( 'parent', 'child' );
		$reshare_mode = isset( $settings['reshare_share_activity'] ) ? $settings['reshare_share_activity'] : 'parent';
		$sanitized['reshare_share_activity'] = in_array( $reshare_mode, $allowed_modes, true ) ? $reshare_mode : 'parent';
		
		return $sanitized;
	}

	/**
	 * Display FAQ section.
	 *
	 * @since    1.5.2
	 * @access   private
	 */
	public function bp_share_faq_page() {
		?>
		<div class="bp-share-faq-section">
			<h2><?php esc_html_e( 'Frequently Asked Questions', 'buddypress-share' ); ?></h2>
			
			<div class="faq-item">
				<h3><?php esc_html_e( 'How do I enable social sharing on BuddyPress activities?', 'buddypress-share' ); ?></h3>
				<p><?php esc_html_e( 'Social sharing is enabled by default. You can toggle it on/off from the Social Networks tab. Make sure at least one social service is enabled in the services section.', 'buddypress-share' ); ?></p>
			</div>

			<div class="faq-item">
				<h3><?php esc_html_e( 'Can users share activities without being logged in?', 'buddypress-share' ); ?></h3>
				<p><?php esc_html_e( 'Yes! Enable "Guest Sharing" in the Social Networks tab to allow logged-out users to share public activities on social media platforms.', 'buddypress-share' ); ?></p>
			</div>

			<div class="faq-item">
				<h3><?php esc_html_e( 'How do I customize the sharing button appearance?', 'buddypress-share' ); ?></h3>
				<p><?php esc_html_e( 'Navigate to the Display Settings tab where you can choose from different icon styles (Circle, Rectangle, Black & White, Bar) and customize colors for background, text, and hover states.', 'buddypress-share' ); ?></p>
			</div>

			<div class="faq-item">
				<h3><?php esc_html_e( 'Which social platforms are supported?', 'buddypress-share' ); ?></h3>
				<p><?php esc_html_e( 'The plugin supports Facebook, X (Twitter), LinkedIn, Pinterest, Reddit, WordPress, Pocket, Telegram, Bluesky, WhatsApp, and Email sharing. You can enable/disable each service individually.', 'buddypress-share' ); ?></p>
			</div>

			<div class="faq-item">
				<h3><?php esc_html_e( 'Can I disable sharing for specific activity types?', 'buddypress-share' ); ?></h3>
				<p><?php esc_html_e( 'Yes! In the Restrictions tab, you can disable sharing for Blog Posts, User Profiles, and Groups activities.', 'buddypress-share' ); ?></p>
			</div>

			<div class="faq-item">
				<h3><?php esc_html_e( 'How does the reshare functionality work?', 'buddypress-share' ); ?></h3>
				<p><?php esc_html_e( 'When a user reshares an activity, you can choose to display either just the original activity or the complete activity with nested content. Configure this in the Restrictions tab.', 'buddypress-share' ); ?></p>
			</div>

			<div class="faq-item">
				<h3><?php esc_html_e( 'Do sharing links open in popup windows?', 'buddypress-share' ); ?></h3>
				<p><?php esc_html_e( 'By default, yes. Social sharing links open in popup windows for a better user experience. You can disable this behavior in the Social Networks tab.', 'buddypress-share' ); ?></p>
			</div>

			<div class="faq-item">
				<h3><?php esc_html_e( 'Is the plugin compatible with BuddyBoss Platform?', 'buddypress-share' ); ?></h3>
				<p><?php esc_html_e( 'Yes! The plugin automatically detects and adapts to work with both BuddyPress and BuddyBoss Platform.', 'buddypress-share' ); ?></p>
			</div>

			<div class="faq-item">
				<h3><?php esc_html_e( 'Where can I get support?', 'buddypress-share' ); ?></h3>
				<p>
					<?php 
					printf(
						esc_html__( 'For premium support, please visit our %1$ssupport portal%2$s. You can also check our %3$sdocumentation%4$s for detailed guides.', 'buddypress-share' ),
						'<a href="https://wbcomdesigns.com/support/" target="_blank">',
						'</a>',
						'<a href="https://docs.wbcomdesigns.com/buddypress-activity-share-pro/" target="_blank">',
						'</a>'
					);
					?>
				</p>
			</div>

		</div>
		<?php
	}

	/**
	 * Display post type sharing settings section.
	 *
	 * @since    2.1.0
	 * @access   private
	 */
	public function bp_share_post_types_page() {
		// Check if settings classes exist
		if ( ! class_exists( 'BP_Share_Post_Type_Settings' ) ) {
			// Try to include the required files
			$base_path = plugin_dir_path( dirname( __FILE__ ) );
			$files = array(
				$base_path . 'includes/post-types/class-bp-share-post-type-settings.php',
				$base_path . 'includes/post-types/class-bp-share-post-type-controller.php',
				$base_path . 'includes/post-types/class-bp-share-post-type-frontend.php'
			);
			
			foreach ( $files as $file ) {
				if ( file_exists( $file ) ) {
					require_once $file;
				}
			}
		}
		
		// Handle form submission
		if ( isset( $_POST['bp_share_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bp_share_nonce'] ) ), 'bp_share_post_type_settings' ) ) {
			if ( class_exists( 'BP_Share_Post_Type_Settings' ) ) {
				$settings_manager = BP_Share_Post_Type_Settings::get_instance();
				
				// Prepare settings data
				$settings_data = array(
					'enabled_post_types' => isset( $_POST['bp_share_settings']['enabled_post_types'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['bp_share_settings']['enabled_post_types'] ) ) : array(),
					'post_type_services' => isset( $_POST['bp_share_settings']['post_type_services'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['bp_share_settings']['post_type_services'] ) ) : array(),
					'display_position' => isset( $_POST['bp_share_settings']['display_position'] ) ? sanitize_text_field( wp_unslash ( $_POST['bp_share_settings']['display_position'] ) ) : 'right',
					'display_style' => isset( $_POST['bp_share_settings']['display_style'] ) ? sanitize_text_field( wp_unslash ( $_POST['bp_share_settings']['display_style'] ) ) : 'floating',
					'mobile_behavior' => isset( $_POST['bp_share_settings']['mobile_behavior'] ) ? sanitize_text_field( wp_unslash ( $_POST['bp_share_settings']['mobile_behavior'] ) ) : 'bottom',
					'default_services' => isset( $_POST['bp_share_settings']['default_services'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['bp_share_settings']['default_services'] ) ) : array()
				);
				
				$result = $settings_manager->save_settings( $settings_data );
				
				if ( $result ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully!', 'buddypress-share' ) . '</p></div>';
				} else {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Error saving settings. Please try again.', 'buddypress-share' ) . '</p></div>';
				}
			}
		}
		
		// Include the settings template
		$template_file = plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/bp-share-post-type-settings.php';
		
		if ( file_exists( $template_file ) ) {
			include $template_file;
		} else {
			?>
			<div class="bp-share-post-type-settings">
				<h2><?php esc_html_e( 'Post Type Sharing Settings', 'buddypress-share' ); ?></h2>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'Post type sharing functionality is coming soon in version 3.0!', 'buddypress-share' ); ?></p>
					<p><?php esc_html_e( 'This feature will allow you to:', 'buddypress-share' ); ?></p>
					<ul style="list-style: disc; margin-left: 30px;">
						<li><?php esc_html_e( 'Enable sharing buttons on any WordPress post type', 'buddypress-share' ); ?></li>
						<li><?php esc_html_e( 'Configure different social services for each post type', 'buddypress-share' ); ?></li>
						<li><?php esc_html_e( 'Display a floating share widget on posts and pages', 'buddypress-share' ); ?></li>
						<li><?php esc_html_e( 'Track sharing analytics per post type', 'buddypress-share' ); ?></li>
						<li><?php esc_html_e( 'Customize the appearance and position of share buttons', 'buddypress-share' ); ?></li>
					</ul>
				</div>
				
				<div style="margin-top: 30px; padding: 20px; background: #f5f5f5; border-radius: 5px;">
					<h3><?php esc_html_e( 'Preview: Admin Interface', 'buddypress-share' ); ?></h3>
					<img src="<?php echo esc_url( BP_ACTIVITY_SHARE_PLUGIN_URL . 'admin/images/post-type-sharing-preview.png' ); ?>" 
					     alt="<?php esc_attr_e( 'Post Type Sharing Preview', 'buddypress-share' ); ?>" 
					     style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 5px;">
				</div>
			</div>
			<?php
		}
	}
}