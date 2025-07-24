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
	 * Admin CDN assets (lighter than frontend).
	 *
	 * @since    1.5.2
	 * @access   private
	 * @var      array    CDN asset URLs for admin.
	 */
	const ADMIN_CDN_ASSETS = [
		'font_awesome' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
	];

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
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $hook The current admin page hook.
	 */
	public function enqueue_styles( $hook ) {
		// Only load on plugin pages
		if ( ! $this->is_plugin_admin_page( $hook ) ) {
			return;
		}

		$plugin_url = $this->get_plugin_url();

		// Font Awesome 5.15.4 - Only if not already loaded
		if ( ! $this->is_fontawesome_loaded() ) {
			wp_enqueue_style( 
				'bp-share-admin-fontawesome', 
				self::ADMIN_CDN_ASSETS['font_awesome'],
				array(), 
				'5.15.4', 
				'all' 
			);
		}
		
		// WordPress Color Picker for icon settings
		if ( isset( $_GET['section'] ) && in_array( $_GET['section'], array( 'icons', 'display' ) ) ) {
			wp_enqueue_style( 'wp-color-picker' );
		}
		
		// License tab styles
		if ( isset( $_GET['section'] ) && 'license' === $_GET['section'] ) {
			wp_enqueue_style(
				'bp-share-license-admin',
				$plugin_url . 'license/license-admin.css',
				array(),
				$this->version,
				'all'
			);
		}

		// Modern shared tab styles - Use centralized version from WBCom Essential if available
		if ( defined( 'WBCOM_ESSENTIAL_URL' ) && file_exists( WP_PLUGIN_DIR . '/wbcom-essential/includes/shared-admin/wbcom-shared-tabs.css' ) ) {
			wp_enqueue_style(
				'wbcom-shared-tabs',
				WBCOM_ESSENTIAL_URL . 'includes/shared-admin/wbcom-shared-tabs.css',
				array(),
				defined( 'WBCOM_ESSENTIAL_VERSION' ) ? WBCOM_ESSENTIAL_VERSION : $this->version,
				'all'
			);
		} else {
			// Fallback to local copy
			wp_enqueue_style(
				'wbcom-shared-tabs',
				$plugin_url . 'includes/shared-admin/wbcom-shared-tabs.css',
				array(),
				$this->version,
				'all'
			);
		}

		// Main admin stylesheet with auto min/RTL support
		bp_share_enqueue_style(
			$this->plugin_name . '-admin',
			$plugin_url . 'admin/css/buddypress-share-admin', // Without .css
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $hook The current admin page hook.
	 */
	public function enqueue_scripts( $hook ) {
		// Only load on plugin pages
		if ( ! $this->is_plugin_admin_page( $hook ) ) {
			return;
		}

		$plugin_url = $this->get_plugin_url();

		// jQuery UI components for drag/drop
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		
		// WordPress Color Picker for icon settings
		if ( isset( $_GET['section'] ) && in_array( $_GET['section'], array( 'icons', 'display' ) ) ) {
			wp_enqueue_script( 'wp-color-picker' );
		}

		// Main admin script with auto minification
		bp_share_enqueue_script(
			$this->plugin_name . '-admin',
			$plugin_url . 'admin/js/buddypress-share-admin', // Without .js
			array( 'jquery', 'jquery-ui-sortable' ),
			$this->version,
			true
		);

		// Localize script
		wp_localize_script(
			$this->plugin_name . '-admin',
			'bp_share_admin_vars',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'bp_share_admin_nonce' ),
				'strings'  => array(
					'loading' => __( 'Loading...', 'buddypress-share' ),
					'saving'  => __( 'Saving...', 'buddypress-share' ),
					'saved'   => __( 'Settings saved successfully!', 'buddypress-share' ),
					'error'   => __( 'An error occurred. Please try again.', 'buddypress-share' ),
				),
			)
		);
	}

	/**
	 * Check if Font Awesome is already loaded by other plugins/themes.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @return   bool True if Font Awesome is already loaded, false otherwise.
	 */
	private function is_fontawesome_loaded() {
		global $wp_styles;
		
		if ( ! $wp_styles ) {
			return false;
		}

		// Check for various Font Awesome handles
		$fa_handles = [
			'font-awesome',
			'fontawesome', 
			'fa',
			'font-awesome-5',
			'fontawesome-5',
			'wp-fontawesome',
			'elementor-icons-fa-solid',
			'elementor-icons-fa-brands'
		];

		foreach ( $fa_handles as $handle ) {
			if ( wp_style_is( $handle, 'enqueued' ) || wp_style_is( $handle, 'registered' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get plugin URL dynamically.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @return   string Plugin URL.
	 */
	private function get_plugin_url() {
		$plugin_folder = basename( dirname( dirname( __FILE__ ) ) );
		return plugins_url( $plugin_folder ) . '/';
	}

	/**
	 * Hide admin notices on plugin pages.
	 * 
	 * @since    1.0.0
	 * @access   public
	 */
	public function wbcom_hide_all_admin_notices_from_setting_page() {
		$current_page = filter_input( INPUT_GET, 'page' );
		if ( $current_page && 'buddypress-share' === $current_page ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	/**
	 * Add plugin menu - Single page under Settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_plugin_menu() {
		// Only add menu if shared wrapper is not active
		if ( ! class_exists( 'Wbcom_Shared_Loader' ) ) {
			add_options_page(
				__( 'BuddyPress Activity Share', 'buddypress-share' ),
				__( 'Activity Share', 'buddypress-share' ),
				'manage_options',
				'buddypress-share',
				array( $this, 'bp_share_plugin_options' )
			);
		}
	}

	/**
	 * Main admin page with native WordPress UI.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_plugin_options() {
		// Security check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'buddypress-share' ) );
		}

		// Get current section - default to empty string for first tab
		$current_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : '';
		
		?>
		<div class="wrap bp-share-admin-wrap">
			<h1 class="bp-share-admin-title">
				<span class="dashicons dashicons-share"></span>
				<?php esc_html_e( 'BuddyPress Activity Share Pro Settings', 'buddypress-share' ); ?>
			</h1>
			
			<?php
			// Show success message if settings were updated
			if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved successfully!', 'buddypress-share' ); ?></p>
				</div>
				<?php
			}
			
			// Show license activation success message
			if ( isset( $_GET['sl_activation'] ) && 'true' === $_GET['sl_activation'] ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'License activated successfully!', 'buddypress-share' ); ?></p>
				</div>
				<?php
			}
			
			// Show license deactivation success message
			if ( isset( $_GET['sl_deactivation'] ) && 'true' === $_GET['sl_deactivation'] ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'License deactivated successfully!', 'buddypress-share' ); ?></p>
				</div>
				<?php
			}
			
			// Show license error message
			if ( isset( $_GET['sl_activation'] ) && 'false' === $_GET['sl_activation'] && isset( $_GET['message'] ) ) {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo esc_html( rawurldecode( $_GET['message'] ) ); ?></p>
				</div>
				<?php
			}
			?>

			<!-- WBCom Shared Tab Navigation -->
			<div class="wbcom-tab-wrapper">
				<nav class="wbcom-nav-tab-wrapper">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wbcom-buddypress-share' ) ); ?>" 
					   class="wbcom-nav-tab <?php echo in_array( $current_section, array( '', 'general', 'services' ) ) ? 'nav-tab-active' : ''; ?>">
						<span class="dashicons dashicons-share-alt2"></span>
						<?php esc_html_e( 'Social Networks', 'buddypress-share' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wbcom-buddypress-share&section=display' ) ); ?>" 
					   class="wbcom-nav-tab <?php echo in_array( $current_section, array( 'display', 'icons' ) ) ? 'nav-tab-active' : ''; ?>">
						<span class="dashicons dashicons-art"></span>
						<?php esc_html_e( 'Display Settings', 'buddypress-share' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wbcom-buddypress-share&section=restrictions' ) ); ?>" 
					   class="wbcom-nav-tab <?php echo in_array( $current_section, array( 'restrictions', 'sharing' ) ) ? 'nav-tab-active' : ''; ?>">
						<span class="dashicons dashicons-admin-settings"></span>
						<?php esc_html_e( 'Restrictions', 'buddypress-share' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wbcom-buddypress-share&section=license' ) ); ?>" 
					   class="wbcom-nav-tab <?php echo 'license' === $current_section ? 'nav-tab-active' : ''; ?>">
						<span class="dashicons dashicons-admin-network"></span>
						<?php esc_html_e( 'License', 'buddypress-share' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wbcom-buddypress-share&section=faq' ) ); ?>" 
					   class="wbcom-nav-tab <?php echo 'faq' === $current_section ? 'nav-tab-active' : ''; ?>">
						<span class="dashicons dashicons-editor-help"></span>
						<?php esc_html_e( 'FAQ', 'buddypress-share' ); ?>
					</a>
				</nav>

				<div class="wbcom-tab-content">
				<?php
				switch ( $current_section ) {
					case '':
					case 'general':
					case 'services':
						$this->bp_share_social_networks_page();
						break;
					case 'display':
					case 'icons':
						$this->bp_share_display_settings_page();
						break;
					case 'restrictions':
					case 'sharing':
						$this->bp_share_restrictions_page();
						break;
					case 'license':
						$this->bp_share_license_settings_page();
						break;
					case 'faq':
						$this->bp_share_faq_page();
						break;
					default:
						$this->bp_share_social_networks_page();
						break;
				}
				?>
				</div><!-- .bp-share-tab-content -->
			</div><!-- .bp-share-admin-wrapper -->
		</div>
		<?php
	}

	/**
	 * Display social networks settings section.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function bp_share_social_networks_page() {
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
						
						<div class="bp-share-toggle-setting" id="logout_sharing_row" style="<?php echo $bp_share_services_enable ? '' : 'display:none;'; ?>">
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
	private function bp_share_restrictions_page() {
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
	private function bp_share_display_settings_page() {
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
								<label class="style-option <?php echo $current_style === $style_key ? 'selected' : ''; ?>">
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

	/**
	 * Display license settings section.
	 *
	 * @since    1.5.2
	 * @access   private
	 */
	private function bp_share_license_settings_page() {
		if ( ! class_exists( 'BP_ACTIVITY_SHARE_PLUGIN_License_Manager' ) ) {
			?>
			<div class="notice notice-warning">
				<p><?php esc_html_e( 'License management is not available. Please contact support.', 'buddypress-share' ); ?></p>
			</div>
			<?php
			return;
		}

		$license_manager = BP_ACTIVITY_SHARE_PLUGIN_License_Manager::get_instance();
		$license_manager->render_license_tab();
		
		// Enqueue license scripts
		$this->enqueue_license_scripts();
	}

	/**
	 * Enqueue license management scripts.
	 *
	 * @since    1.5.2
	 * @access   private
	 */
	private function enqueue_license_scripts() {
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			// Handle license activation
			$('#activate-license').on('click', function(e) {
				e.preventDefault();
				var licenseKey = $('#bp_share_license_key').val().trim();
				
				if (!licenseKey) {
					$('#license-message').html('<div class="notice notice-error"><p><?php esc_html_e( "Please enter a license key", "buddypress-share" ); ?></p></div>');
					return;
				}
				
				$(this).prop('disabled', true).text('<?php esc_html_e( "Activating...", "buddypress-share" ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'bp_share_activate_license',
						license_key: licenseKey,
						nonce: bp_share_admin_vars.nonce
					},
					success: function(response) {
						if (response.success) {
							$('#license-message').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
							setTimeout(function() {
								location.reload();
							}, 1500);
						} else {
							$('#license-message').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
							$('#activate-license').prop('disabled', false).text('<?php esc_html_e( "Activate License", "buddypress-share" ); ?>');
						}
					},
					error: function() {
						$('#license-message').html('<div class="notice notice-error"><p><?php esc_html_e( "An error occurred. Please try again.", "buddypress-share" ); ?></p></div>');
						$('#activate-license').prop('disabled', false).text('<?php esc_html_e( "Activate License", "buddypress-share" ); ?>');
					}
				});
			});
			
			// Handle license deactivation
			$('#deactivate-license').on('click', function(e) {
				e.preventDefault();
				
				if (!confirm('<?php esc_html_e( "Are you sure you want to deactivate your license?", "buddypress-share" ); ?>')) {
					return;
				}
				
				$(this).prop('disabled', true).text('<?php esc_html_e( "Deactivating...", "buddypress-share" ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'bp_share_deactivate_license',
						nonce: bp_share_admin_vars.nonce
					},
					success: function(response) {
						if (response.success) {
							$('#license-message').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
							setTimeout(function() {
								location.reload();
							}, 1500);
						} else {
							$('#license-message').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
							$('#deactivate-license').prop('disabled', false).text('<?php esc_html_e( "Deactivate License", "buddypress-share" ); ?>');
						}
					},
					error: function() {
						$('#license-message').html('<div class="notice notice-error"><p><?php esc_html_e( "An error occurred. Please try again.", "buddypress-share" ); ?></p></div>');
						$('#deactivate-license').prop('disabled', false).text('<?php esc_html_e( "Deactivate License", "buddypress-share" ); ?>');
					}
				});
			});
			
			// Handle license check
			$('#check-license').on('click', function(e) {
				e.preventDefault();
				
				$(this).prop('disabled', true).text('<?php esc_html_e( "Checking...", "buddypress-share" ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'bp_share_check_license',
						nonce: bp_share_admin_vars.nonce
					},
					success: function(response) {
						if (response.success) {
							$('#license-message').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
							setTimeout(function() {
								location.reload();
							}, 1500);
						} else {
							$('#license-message').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
						}
						$('#check-license').prop('disabled', false).text('<?php esc_html_e( "Check License", "buddypress-share" ); ?>');
					},
					error: function() {
						$('#license-message').html('<div class="notice notice-error"><p><?php esc_html_e( "An error occurred. Please try again.", "buddypress-share" ); ?></p></div>');
						$('#check-license').prop('disabled', false).text('<?php esc_html_e( "Check License", "buddypress-share" ); ?>');
					}
				});
			});
		});
		</script>
		<?php
	}

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
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'bp_share_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security nonce check failed.', 'buddypress-share' ) ) );
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'buddypress-share' ) ) );
		}
		
		$service_name = sanitize_text_field( wp_unslash( $_POST['term_name'] ?? '' ) );
		
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
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'bp_share_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security nonce check failed.', 'buddypress-share' ) ) );
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'buddypress-share' ) ) );
		}
		
		$service_name = sanitize_text_field( wp_unslash( $_POST['icon_name'] ?? '' ) );
		
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
		return array(
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
	 * Check if current page is a plugin admin page.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string $hook Current admin page hook.
	 * @return   bool True if plugin admin page, false otherwise.
	 */
	private function is_plugin_admin_page( $hook ) {
		return ( $hook === 'wbcom-designs_page_wbcom-buddypress-share' || 
		         ( isset( $_GET['page'] ) && $_GET['page'] === 'wbcom-buddypress-share' ) );
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

	public function sync_extra_to_site_option( $old_value, $value, $option ) {
		update_site_option( 'bp_share_services_extra', $value );
	}

	public function sync_services_to_site_option( $old_value, $value, $option ) {
		$services = json_decode( $value, true );
		if ( ! is_array( $services ) ) {
			$services = @unserialize( $value );
		}
		
		if ( is_array( $services ) ) {
			$sanitized_services = $this->sanitize_services_array( $services );
			update_site_option( 'bp_share_services', $sanitized_services );
		}
	}

	public function sync_reshare_to_site_option( $old_value, $value, $option ) {
		update_site_option( 'bp_reshare_settings', $value );
	}

	/**
	 * Sanitization methods.
	 *
	 * @since    1.5.2
	 * @access   public
	 */
	public function sanitize_extra_settings( $input ) {
		if ( ! is_array( $input ) ) {
			$input = array();
		}
		
		$sanitized = array();
		if ( isset( $input['bp_share_services_open'] ) ) {
			$sanitized['bp_share_services_open'] = sanitize_text_field( $input['bp_share_services_open'] );
		} else {
			$sanitized['bp_share_services_open'] = '';
		}
		
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
	private function bp_share_faq_page() {
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

			<div class="faq-item">
				<h3><?php esc_html_e( 'How do I update my license key?', 'buddypress-share' ); ?></h3>
				<p><?php esc_html_e( 'Go to the License tab, enter your license key, and click "Activate License". An active license ensures you receive automatic updates and premium support.', 'buddypress-share' ); ?></p>
			</div>
		</div>
		<?php
	}
}