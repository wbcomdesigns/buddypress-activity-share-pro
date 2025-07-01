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
 * Defines the plugin name, version, and optimized hooks for admin functionality.
 * Enhanced with independent menu system, better security, performance optimizations, and cache management.
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
	 * Register the stylesheets for the admin area.
	 *
	 * Optimized to load assets only on relevant admin pages for better performance.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $hook The current admin page hook.
	 */
	public function enqueue_styles( $hook ) {
		// Only load on plugin pages for better performance
		if ( ! $this->is_plugin_admin_page( $hook ) ) {
			return;
		}

		$rtl_css = is_rtl() ? '-rtl' : '';
		$css_extension = $this->get_css_extension();

		// Load Font Awesome only if not already loaded
		if ( ! wp_style_is( 'font-awesome', 'enqueued' ) ) {
			wp_enqueue_style( 
				'font-awesome', 
				'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', 
				array(), 
				$this->version, 
				'all' 
			);
		}
		
		// Load color picker for icon settings page
		if ( strpos( $hook, 'buddypress-share-icons' ) !== false ) {
			wp_enqueue_style( 'wp-color-picker' );
		}
		
		// Main admin stylesheet
		wp_enqueue_style( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'css' . $rtl_css . '/buddypress-share-admin' . $css_extension, 
			array(), 
			$this->version, 
			'all' 
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * Optimized to load scripts only on relevant admin pages.
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

		$js_extension = $this->get_js_extension();

		// Load jQuery UI components
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		
		// Load color picker for icon settings page
		if ( strpos( $hook, 'buddypress-share-icons' ) !== false ) {
			wp_enqueue_script( 'wp-color-picker' );
		}

		// Main admin script
		wp_enqueue_script( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'js/buddypress-share-admin' . $js_extension, 
			array( 'jquery', 'wp-color-picker' ), 
			$this->version, 
			true 
		);

		// Localize script with admin data
		wp_localize_script(
			$this->plugin_name,
			'bp_share_admin_vars',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'bp_share_admin_nonce' ),
				'strings'  => array(
					'loading'           => __( 'Loading...', 'buddypress-share' ),
					'saving'            => __( 'Saving...', 'buddypress-share' ),
					'saved'             => __( 'Settings saved successfully!', 'buddypress-share' ),
					'error'             => __( 'An error occurred. Please try again.', 'buddypress-share' ),
					'confirm_reset'     => __( 'Are you sure you want to reset all settings? This action cannot be undone.', 'buddypress-share' ),
					'settings_changed'  => __( 'You have unsaved changes. Are you sure you want to leave?', 'buddypress-share' ),
				),
				'auto_save_enabled' => false, // Can be enabled in future versions
			)
		);
	}

	/**
	 * Function to hide all the admin notices from plugin settings page.
	 * 
	 * @since    1.0.0
	 * @access   public
	 */
	public function wbcom_hide_all_admin_notices_from_setting_page() {
		$plugin_pages = array( 
			'buddypress-share', 
			'buddypress-share-settings', 
			'buddypress-share-icons' 
		);
		$current_page = filter_input( INPUT_GET, 'page' );

		if ( $current_page && in_array( $current_page, $plugin_pages, true ) ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	/**
	 * Function for add plugin menu - Independent menu system.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_plugin_menu() {
		$capability = 'manage_options';
		
		// Add main menu page
		add_menu_page(
			__( 'Activity Share', 'buddypress-share' ),
			__( 'Activity Share', 'buddypress-share' ),
			$capability,
			'buddypress-share',
			array( $this, 'bp_share_plugin_options' ),
			'dashicons-share',
			59
		);

		// Add submenu pages
		add_submenu_page(
			'buddypress-share',
			__( 'General Settings', 'buddypress-share' ),
			__( 'General Settings', 'buddypress-share' ),
			$capability,
			'buddypress-share',
			array( $this, 'bp_share_plugin_options' )
		);

		add_submenu_page(
			'buddypress-share',
			__( 'Share Settings', 'buddypress-share' ),
			__( 'Share Settings', 'buddypress-share' ),
			$capability,
			'buddypress-share-settings',
			array( $this, 'bp_share_settings_page' )
		);

		add_submenu_page(
			'buddypress-share',
			__( 'Icon Settings', 'buddypress-share' ),
			__( 'Icon Settings', 'buddypress-share' ),
			$capability,
			'buddypress-share-icons',
			array( $this, 'bp_share_icons_page' )
		);
	}

	/**
	 * Build the main admin options page.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_plugin_options() {
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'bpas_welcome';
		
		// Security check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'buddypress-share' ) );
		}
		?>
		<div class="wrap">
			<div class="bp-share-admin-header">
				<div class="bp-share-header-content">
					<div class="bp-share-plugin-info">
						<div class="bp-share-plugin-icon">
							<span class="dashicons dashicons-share"></span>
						</div>
						<div class="bp-share-plugin-details">
							<h1 class="bp-share-plugin-title">
								<?php esc_html_e( 'BuddyPress Activity Share Pro', 'buddypress-share' ); ?>
								<span class="bp-share-version">
									<?php 
									printf( 
										esc_html__( 'v%s', 'buddypress-share' ), 
										esc_attr( BP_ACTIVITY_SHARE_PLUGIN_VERSION ) 
									); 
									?>
								</span>
							</h1>
							<p class="bp-share-plugin-description">
								<?php esc_html_e( 'Share BuddyPress activities on social media and within your community.', 'buddypress-share' ); ?>
							</p>
						</div>
					</div>
					<div class="bp-share-header-actions">
						<a href="https://docs.wbcomdesigns.com/doc_category/buddypress-activity-social-share/" 
						   class="button button-secondary" 
						   target="_blank" 
						   rel="noopener noreferrer">
							<span class="dashicons dashicons-book"></span>
							<?php esc_html_e( 'Documentation', 'buddypress-share' ); ?>
						</a>
						<a href="https://wbcomdesigns.com/support/" 
						   class="button button-secondary" 
						   target="_blank" 
						   rel="noopener noreferrer">
							<span class="dashicons dashicons-sos"></span>
							<?php esc_html_e( 'Support', 'buddypress-share' ); ?>
						</a>
					</div>
				</div>
			</div>

			<div class="bp-share-admin-content">
				<div class="bp-share-tabs-wrapper">
					<?php $this->render_admin_tabs( $tab ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Share Settings page.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'buddypress-share' ) );
		}
		
		?>
		<div class="wrap">
			<div class="bp-share-admin-header">
				<div class="bp-share-header-content">
					<h1><?php esc_html_e( 'Share Settings', 'buddypress-share' ); ?></h1>
					<p><?php esc_html_e( 'Configure which content types can be shared and how sharing works.', 'buddypress-share' ); ?></p>
				</div>
			</div>
			<div class="bp-share-admin-content">
				<?php $this->render_template( 'bpas-reshare-setting-page' ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Icon Settings page.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_icons_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'buddypress-share' ) );
		}
		
		?>
		<div class="wrap">
			<div class="bp-share-admin-header">
				<div class="bp-share-header-content">
					<h1><?php esc_html_e( 'Icon Settings', 'buddypress-share' ); ?></h1>
					<p><?php esc_html_e( 'Customize the appearance and style of sharing icons.', 'buddypress-share' ); ?></p>
				</div>
			</div>
			<div class="bp-share-admin-content">
				<?php $this->render_template( 'bpas-icon-color-page' ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render admin tabs with enhanced navigation.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string $current The current tab.
	 */
	private function render_admin_tabs( $current ) {
		$tabs = array(
			'bpas_welcome'          => __( 'Welcome', 'buddypress-share' ),
			'bpas_general_settings' => __( 'General Settings', 'buddypress-share' ),
		);
		
		echo '<div class="bp-share-tabs-section">';
		echo '<div class="nav-tab-wrapper">';
		
		// Responsive menu toggle
		echo '<div class="bp-share-responsive-menu">';
		echo '<span>' . esc_html__( 'Menu', 'buddypress-share' ) . '</span>';
		echo '<input class="bp-share-toggle-btn" type="checkbox" id="bp-share-toggle-btn">';
		echo '<label class="bp-share-toggle-icon" for="bp-share-toggle-btn">';
		echo '<span class="bp-share-icon-bars">☰</span>';
		echo '</label>';
		echo '</div>';
		
		echo '<ul class="bp-share-nav-tabs">';
		
		foreach ( $tabs as $tab_key => $tab_name ) {
			$class = ( $tab_key === $current ) ? 'nav-tab-active' : '';
			$url = add_query_arg( array( 
				'page' => 'buddypress-share',
				'tab'  => $tab_key 
			), admin_url( 'admin.php' ) );
			
			echo '<li class="' . esc_attr( $tab_key ) . '">';
			echo '<a class="nav-tab ' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">';
			echo esc_html( $tab_name );
			echo '</a>';
			echo '</li>';
		}
		
		echo '</ul>';
		echo '</div>';
		echo '</div>';
		
		$this->render_tab_content( $current );
	}

	/**
	 * Render the appropriate tab content.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string $tab The current tab.
	 */
	private function render_tab_content( $tab ) {
		switch ( $tab ) {
			case 'bpas_welcome':
				$this->render_template( 'bpas-welcome-page' );
				break;
			case 'bpas_general_settings':
				$this->render_general_settings_content();
				break;
			default:
				$this->render_template( 'bpas-welcome-page' );
				break;
		}
	}

	/**
	 * Render general settings content with social services selector.
	 *
	 * @since    1.5.1
	 * @access   private
	 */
	private function render_general_settings_content() {
		// Get current settings with proper fallbacks for better UX
		$bp_share_services_enable = get_site_option( 'bp_share_services_enable', 1 );
		$bp_share_services_logout_enable = get_site_option( 'bp_share_services_logout_enable', 1 );
		$bp_share_services_extra = get_site_option( 'bp_share_services_extra', array(
			'bp_share_services_open' => 'on'
		));
		
		// Safely get popup setting with proper default
		$bp_share_services_open = 'on'; // default enabled for better UX
		if ( is_array( $bp_share_services_extra ) && 
			 isset( $bp_share_services_extra['bp_share_services_open'] ) ) {
			$bp_share_services_open = $bp_share_services_extra['bp_share_services_open'];
		}
		
		// Get enabled services with improved defaults
		$enabled_services = get_site_option( 'bp_share_services', array(
			'Facebook'  => 'Facebook',
			'Twitter'   => 'Twitter', 
			'LinkedIn'  => 'LinkedIn',
			'E-mail'    => 'E-mail',
			'WhatsApp'  => 'WhatsApp',
			'Pinterest' => 'Pinterest',
		));
		
		if ( ! is_array( $enabled_services ) ) {
			$enabled_services = array();
		}
		
		$all_services = $this->get_all_available_services();
		$disabled_services = array_diff_key( $all_services, $enabled_services );

		// Check if settings were saved
		$settings_saved = isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'];
		?>
		<div class="bp-share-admin-content">
			<?php if ( $settings_saved ) : ?>
				<div class="bp-share-notice notice-success">
					<p><strong><?php esc_html_e( 'Settings saved successfully!', 'buddypress-share' ); ?></strong></p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'buddypress-share' ); ?></span>
					</button>
				</div>
			<?php endif; ?>

			<form method="post" action="options.php" id="bp_share_general_form">
				<?php
				settings_fields( 'bp_share_general_settings' );
				do_settings_sections( 'bp_share_general_settings' );
				?>

				<!-- Enable Social Share -->
				<div class="bp-share-form-section">
					<div class="bp-share-section-header">
						<h3 class="bp-share-section-title">
							<?php esc_html_e( 'Social Sharing Settings', 'buddypress-share' ); ?>
						</h3>
						<p class="bp-share-section-description">
							<?php esc_html_e( 'Configure basic social sharing functionality for your community.', 'buddypress-share' ); ?>
						</p>
					</div>

					<div class="bp-share-setting-item">
						<div class="bp-share-setting-icon">
							<span class="dashicons dashicons-share"></span>
						</div>
						<div class="bp-share-setting-info">
							<h4 class="bp-share-setting-title"><?php esc_html_e( 'Enable Social Share', 'buddypress-share' ); ?></h4>
							<p class="bp-share-setting-description"><?php esc_html_e( 'Enable this option to show share button in activity page.', 'buddypress-share' ); ?></p>
						</div>
						<div class="bp-share-setting-control">
							<label class="bp-share-toggle">
								<input type="checkbox" 
								       name="bp_share_services_enable" 
								       id="bp_share_services_enable"
								       value="1" 
								       <?php checked( 1, absint( $bp_share_services_enable ) ); ?> />
								<span class="bp-share-slider"></span>
							</label>
						</div>
					</div>

					<div class="bp-share-setting-item" id="social_share_logout_wrap" style="<?php echo absint( $bp_share_services_enable ) ? '' : 'display:none;'; ?>">
						<div class="bp-share-setting-icon">
							<span class="dashicons dashicons-admin-users"></span>
						</div>
						<div class="bp-share-setting-info">
							<h4 class="bp-share-setting-title"><?php esc_html_e( 'Social Share for Logged Out Users', 'buddypress-share' ); ?></h4>
							<p class="bp-share-setting-description"><?php esc_html_e( 'Enable this option to display social share icons when the user is logged out.', 'buddypress-share' ); ?></p>
						</div>
						<div class="bp-share-setting-control">
							<label class="bp-share-toggle">
								<input type="checkbox" 
								       name="bp_share_services_logout_enable" 
								       value="1" 
								       <?php checked( 1, absint( $bp_share_services_logout_enable ) ); ?> />
								<span class="bp-share-slider"></span>
							</label>
						</div>
					</div>
				</div>

				<!-- Social Services Selection -->
				<div class="bp-share-form-section">
					<div class="bp-share-section-header">
						<h3 class="bp-share-section-title">
							<?php esc_html_e( 'Select Social Services', 'buddypress-share' ); ?>
						</h3>
						<p class="bp-share-section-description">
							<?php esc_html_e( 'Drag and drop social services between the lists to enable or disable them.', 'buddypress-share' ); ?>
						</p>
					</div>

					<div class="social_icon_section">
						<!-- Enabled Services -->
						<div class="social-services-list enabled-services">
							<h3><?php esc_html_e( 'Enabled Services', 'buddypress-share' ); ?></h3>
							<ul id="drag_icon_ul" class="enabled-services-list">
								<?php if ( ! empty( $enabled_services ) && is_array( $enabled_services ) ) : ?>
									<?php foreach ( $enabled_services as $service_key => $service_name ) : ?>
										<?php if ( is_string( $service_name ) && ! empty( $service_name ) ) : ?>
											<li class="socialicon icon_<?php echo esc_attr( sanitize_title( $service_key ) ); ?>">
												<?php echo esc_html( $service_name ); ?>
											</li>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php else : ?>
									<li class="no-services-message">
										<?php esc_html_e( 'No services enabled. Drag services from the available list to enable them.', 'buddypress-share' ); ?>
									</li>
								<?php endif; ?>
							</ul>
						</div>

						<!-- Disabled Services -->
						<div class="social-services-list disabled-services">
							<h3><?php esc_html_e( 'Available Services', 'buddypress-share' ); ?></h3>
							<ul id="drag_social_icon" class="disabled-services-list">
								<?php if ( ! empty( $disabled_services ) && is_array( $disabled_services ) ) : ?>
									<?php foreach ( $disabled_services as $service_key => $service_name ) : ?>
										<?php if ( is_string( $service_name ) && ! empty( $service_name ) ) : ?>
											<li class="socialicon icon_<?php echo esc_attr( sanitize_title( $service_key ) ); ?>">
												<?php echo esc_html( $service_name ); ?>
											</li>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php else : ?>
									<li class="no-services-message">
										<?php esc_html_e( 'All services are enabled. Drag services from the enabled list to disable them.', 'buddypress-share' ); ?>
									</li>
								<?php endif; ?>
							</ul>
						</div>
					</div>

					<div class="drag-drop-instructions">
						<p class="description">
							<?php esc_html_e( 'Drag social service icons between the lists to enable or disable them. Enabled services will appear in the sharing interface for your users.', 'buddypress-share' ); ?>
						</p>
					</div>
				</div>

				<!-- Additional Options -->
				<div class="bp-share-form-section">
					<div class="bp-share-section-header">
						<h3 class="bp-share-section-title">
							<?php esc_html_e( 'Display Options', 'buddypress-share' ); ?>
						</h3>
						<p class="bp-share-section-description">
							<?php esc_html_e( 'Configure how social sharing links are displayed and behave.', 'buddypress-share' ); ?>
						</p>
					</div>

					<div class="bp-share-setting-item">
						<div class="bp-share-setting-icon">
							<span class="dashicons dashicons-external"></span>
						</div>
						<div class="bp-share-setting-info">
							<h4 class="bp-share-setting-title"><?php esc_html_e( 'Open as Popup Window', 'buddypress-share' ); ?></h4>
							<p class="bp-share-setting-description"><?php esc_html_e( 'Enable this to open social sharing links in popup windows instead of new tabs.', 'buddypress-share' ); ?></p>
						</div>
						<div class="bp-share-setting-control">
							<label class="bp-share-toggle">
								<input type="checkbox" 
								       name="bp_share_services_open" 
								       value="on" 
								       <?php checked( 'on', sanitize_text_field( $bp_share_services_open ) ); ?> />
								<span class="bp-share-slider"></span>
							</label>
						</div>
					</div>
				</div>

				<!-- Submit Section -->
				<div class="bp-share-submit-section">
					<button type="submit" 
					        name="bpas_submit_general_options" 
					        class="bp-share-submit-button">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Save General Settings', 'buddypress-share' ); ?>
					</button>
					<span class="bp-share-spinner"></span>
					
					<div class="bp-share-save-info">
						<p class="description">
							<?php esc_html_e( 'These settings control the basic functionality of social sharing in your community.', 'buddypress-share' ); ?>
						</p>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Get all available social services.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @return   array All available social services.
	 */
	private function get_all_available_services() {
		return array(
			'Facebook'  => 'Facebook',
			'Twitter'   => 'Twitter', 
			'X'         => 'X',
			'LinkedIn'  => 'LinkedIn',
			'Pinterest' => 'Pinterest',
			'Reddit'    => 'Reddit',
			'WordPress' => 'WordPress',
			'Pocket'    => 'Pocket',
			'Telegram'  => 'Telegram',
			'Bluesky'   => 'Bluesky',
			'WhatsApp'  => 'WhatsApp',
			'E-mail'    => 'E-mail',
		);
	}

	/**
	 * Render a template file with error handling.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $template_name Template file name without extension.
	 */
	private function render_template( $template_name ) {
		$template_path = BP_ACTIVITY_SHARE_PLUGIN_PATH . "admin/templates/{$template_name}.php";
		
		if ( file_exists( $template_path ) ) {
			include $template_path;
		} else {
			$this->render_template_not_found( $template_name );
		}
	}

	/**
	 * Render template not found error.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $template_name Template name that wasn't found.
	 */
	private function render_template_not_found( $template_name ) {
		?>
		<div class="notice notice-error">
			<p>
				<?php 
				printf( 
					esc_html__( 'Template file "%s" not found.', 'buddypress-share' ), 
					esc_html( $template_name ) 
				); 
				?>
			</p>
		</div>
		<?php
		
		// Log error for debugging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 
				sprintf( 
					'[BP Activity Share Pro] Template not found: %s', 
					$template_name 
				) 
			);
		}
	}

	/**
	 * Function to register plugin settings.
	 * 
	 * @since    1.0.0
	 * @access   public
	 */
	public function bpas_register_setting() {
		// Register general settings
		register_setting( 
			'bp_share_general_settings', 
			'bp_share_services_enable',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'absint',
				'default'           => 1,
			)
		);
		
		register_setting( 
			'bp_share_general_settings', 
			'bp_share_services_logout_enable',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'absint',
				'default'           => 1,
			)
		);
		
		register_setting( 
			'bp_share_general_settings', 
			'bp_share_services_open',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'on',
			)
		);
		
		register_setting( 
			'bp_share_general_settings', 
			'bp_share_services',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_services_array' ),
				'default'           => array(),
			)
		);
		
		// Register icon color settings
		register_setting( 
			'bpas_icon_color_settings', 
			'bpas_icon_color_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_icon_settings' ),
				'default'           => array( 'icon_style' => 'circle' ),
			)
		);
		
		// Register reshare settings
		register_setting( 
			'bp_reshare_settings', 
			'bp_reshare_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_reshare_settings' ),
				'default'           => array( 'reshare_share_activity' => 'parent' ),
			)
		);
	}

	/**
	 * Enhanced plugin admin settings handler with better security and validation.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_settings_init() {
		// Handle general settings form submission
		if ( $this->should_process_form( 'bpas_submit_general_options', 'bp_share_general_settings-options' ) ) {
			$this->process_general_settings();
		}

		// Handle reshare settings form submission
		if ( $this->should_process_form( 'bpas_submit_reshare_options', 'bp_reshare_settings-options' ) ) {
			$this->process_reshare_settings();
		}

		// Handle icon settings (processed by WordPress settings API)
		if ( $this->should_process_form( 'submit', 'bpas_icon_color_settings-options' ) ) {
			// WordPress handles this automatically via options.php
		}
	}

	/**
	 * AJAX handler for adding social icons with enhanced security.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function wss_social_icons() {
		// Verify nonce and permissions
		if ( ! $this->verify_ajax_request() ) {
			wp_send_json_error( 
				array( 'message' => __( 'Security check failed.', 'buddypress-share' ) ) 
			);
		}
		
		$service_name = sanitize_text_field( wp_unslash( $_POST['term_name'] ?? '' ) );
		
		if ( empty( $service_name ) ) {
			wp_send_json_error( 
				array( 'message' => __( 'Service name is required.', 'buddypress-share' ) ) 
			);
		}
		
		$current_services = get_site_option( 'bp_share_services', array() );
		
		// Ensure we're working with an array
		if ( ! is_array( $current_services ) ) {
			$current_services = array();
		}
		
		$current_services[ $service_name ] = $service_name;
		
		$updated = update_site_option( 'bp_share_services', $current_services );
		
		if ( $updated ) {
			$this->clear_public_settings_cache();
			wp_send_json_success( 
				array( 'message' => __( 'Service added successfully.', 'buddypress-share' ) ) 
			);
		} else {
			wp_send_json_error( 
				array( 'message' => __( 'Failed to add service.', 'buddypress-share' ) ) 
			);
		}
	}

	/**
	 * AJAX handler for removing social icons with enhanced security.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function wss_social_remove_icons() {
		// Verify nonce and permissions
		if ( ! $this->verify_ajax_request() ) {
			wp_send_json_error( 
				array( 'message' => __( 'Security check failed.', 'buddypress-share' ) ) 
			);
		}
		
		$service_name = sanitize_text_field( wp_unslash( $_POST['icon_name'] ?? '' ) );
		
		if ( empty( $service_name ) ) {
			wp_send_json_error( 
				array( 'message' => __( 'Service name is required.', 'buddypress-share' ) ) 
			);
		}
		
		$current_services = get_site_option( 'bp_share_services', array() );
		
		// Ensure we're working with an array
		if ( ! is_array( $current_services ) ) {
			$current_services = array();
		}
		
		if ( isset( $current_services[ $service_name ] ) ) {
			unset( $current_services[ $service_name ] );
			$updated = update_site_option( 'bp_share_services', $current_services );
			
			if ( $updated ) {
				$this->clear_public_settings_cache();
				wp_send_json_success( 
					array( 'message' => __( 'Service removed successfully.', 'buddypress-share' ) ) 
				);
			} else {
				wp_send_json_error( 
					array( 'message' => __( 'Failed to remove service.', 'buddypress-share' ) ) 
				);
			}
		} else {
			wp_send_json_error( 
				array( 'message' => __( 'Service not found.', 'buddypress-share' ) ) 
			);
		}
	}

	/**
	 * Clear public settings cache when admin settings are updated.
	 *
	 * @since    1.5.1
	 * @access   public
	 */
	public function clear_public_settings_cache() {
		// Clear WordPress object cache
		wp_cache_delete( 'bp_share_plugin_settings', 'buddypress_share' );
		
		// Clear any transients
		delete_transient( 'bp_share_settings_cache' );
		
		// Fire action hook for other components to clear their caches
		do_action( 'bp_share_clear_public_cache' );
		
		// Log cache clearing in debug mode
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( '[BP Activity Share Pro] Public settings cache cleared after admin update.' );
		}
	}

	/**
	 * Check if current page is a plugin admin page.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $hook Current admin page hook.
	 * @return   bool True if plugin admin page, false otherwise.
	 */
	private function is_plugin_admin_page( $hook ) {
		$plugin_pages = array(
			'toplevel_page_buddypress-share',
			'activity-share_page_buddypress-share-settings',
			'activity-share_page_buddypress-share-icons',
			'buddypress-share',
			'buddypress-share-settings',
			'buddypress-share-icons'
		);
		
		return in_array( $hook, $plugin_pages, true ) || 
		       strpos( $hook, 'buddypress-share' ) !== false;
	}

	/**
	 * Get CSS file extension based on debug mode.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @return   string CSS file extension.
	 */
	private function get_css_extension() {
		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.css' : '.min.css';
	}

	/**
	 * Get JS file extension based on debug mode.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @return   string JS file extension.
	 */
	private function get_js_extension() {
		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.js' : '.min.js';
	}

	/**
	 * Check if form should be processed.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $submit_button Submit button name.
	 * @param    string $nonce_action  Nonce action.
	 * @return   bool True if should process, false otherwise.
	 */
	private function should_process_form( $submit_button, $nonce_action ) {
		return isset( $_POST[ $submit_button ] ) && 
		       ! defined( 'DOING_AJAX' ) &&
		       wp_verify_nonce( $_POST['_wpnonce'] ?? '', $nonce_action ) &&
		       current_user_can( 'manage_options' );
	}

	/**
	 * Process general settings form.
	 *
	 * @since    1.5.1
	 * @access   private
	 */
	private function process_general_settings() {
		// Sanitize and save settings
		$service_enable = isset( $_POST['bp_share_services_enable'] ) ? 
			absint( $_POST['bp_share_services_enable'] ) : 0;
		update_site_option( 'bp_share_services_enable', $service_enable );

		$service_enable_logout = isset( $_POST['bp_share_services_logout_enable'] ) ? 
			absint( $_POST['bp_share_services_logout_enable'] ) : 0;
		update_site_option( 'bp_share_services_logout_enable', $service_enable_logout );

		// Handle popup option properly
		$popup_option = array();
		if ( isset( $_POST['bp_share_services_open'] ) ) {
			$popup_option['bp_share_services_open'] = sanitize_text_field( wp_unslash( $_POST['bp_share_services_open'] ) );
		} else {
			$popup_option['bp_share_services_open'] = '';
		}
		update_site_option( 'bp_share_services_extra', $popup_option );

		// Clear cache and redirect
		$this->clear_public_settings_cache();
		$this->redirect_with_success( 'buddypress-share&tab=bpas_general_settings' );
	}

	/**
	 * Process reshare settings form.
	 *
	 * @since    1.5.1
	 * @access   private
	 */
	private function process_reshare_settings() {
		$share_options = array();
		
		if ( isset( $_POST['bp_reshare_settings'] ) && is_array( $_POST['bp_reshare_settings'] ) ) {
			$share_options = $this->sanitize_reshare_settings( $_POST['bp_reshare_settings'] );
		} else {
			// Set default if no data provided
			$share_options = array( 'reshare_share_activity' => 'parent' );
		}
		
		update_site_option( 'bp_reshare_settings', $share_options );
		
		$this->clear_public_settings_cache();
		$this->redirect_with_success( 'buddypress-share-settings' );
	}

	/**
	 * Verify AJAX request security.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @return   bool True if valid, false otherwise.
	 */
	private function verify_ajax_request() {
		$nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
		
		return wp_verify_nonce( $nonce, 'bp_share_admin_nonce' ) && 
		       current_user_can( 'manage_options' );
	}

	/**
	 * Redirect with success message.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $page Target page.
	 */
	private function redirect_with_success( $page ) {
		$redirect_url = add_query_arg( 
			'settings-updated', 
			'true', 
			admin_url( "admin.php?page={$page}" )
		);
		
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Sanitize services array.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @param    array $services Raw services array.
	 * @return   array Sanitized services array.
	 */
	public function sanitize_services_array( $services ) {
		if ( ! is_array( $services ) ) {
			return array();
		}
		
		$sanitized = array();
		foreach ( $services as $key => $value ) {
			// Ensure both key and value are strings
			$sanitized_key = is_string( $key ) ? sanitize_text_field( $key ) : '';
			$sanitized_value = is_string( $value ) ? sanitize_text_field( $value ) : '';
			
			// Only add if both key and value are non-empty strings
			if ( ! empty( $sanitized_key ) && ! empty( $sanitized_value ) ) {
				$sanitized[ $sanitized_key ] = $sanitized_value;
			}
		}
		
		return $sanitized;
	}

	/**
	 * Sanitize icon settings.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @param    array $settings Raw icon settings.
	 * @return   array Sanitized icon settings.
	 */
	public function sanitize_icon_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return array( 'icon_style' => 'circle' );
		}
		
		$sanitized = array();
		$allowed_styles = array( 'circle', 'rec', 'blackwhite', 'baricon' );
		
		// Sanitize icon style
		$sanitized['icon_style'] = isset( $settings['icon_style'] ) && 
			in_array( $settings['icon_style'], $allowed_styles, true ) ? 
			$settings['icon_style'] : 'circle';
		
		// Sanitize colors
		$color_fields = array( 'bg_color', 'text_color', 'hover_color', 'border_color' );
		foreach ( $color_fields as $field ) {
			if ( isset( $settings[ $field ] ) && is_string( $settings[ $field ] ) ) {
				$color = sanitize_hex_color( $settings[ $field ] );
				if ( $color ) {
					$sanitized[ $field ] = $color;
				}
			}
		}
		
		// Sanitize boolean options
		$boolean_fields = array( 'show_labels', 'animate_icons' );
		foreach ( $boolean_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$sanitized[ $field ] = (bool) $settings[ $field ];
			}
		}
		
		// Sanitize icon size
		$allowed_sizes = array( 'small', 'medium', 'large' );
		if ( isset( $settings['icon_size'] ) && is_string( $settings['icon_size'] ) && 
		     in_array( $settings['icon_size'], $allowed_sizes, true ) ) {
			$sanitized['icon_size'] = $settings['icon_size'];
		} else {
			$sanitized['icon_size'] = 'medium';
		}
		
		return $sanitized;
	}

	/**
	 * Sanitize reshare settings.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @param    array $settings Raw reshare settings.
	 * @return   array Sanitized reshare settings.
	 */
	public function sanitize_reshare_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return array( 'reshare_share_activity' => 'parent' );
		}
		
		$sanitized = array();
		
		// Boolean settings
		$boolean_fields = array(
			'disable_post_reshare_activity',
			'disable_my_profile_reshare_activity',
			'disable_message_reshare_activity',
			'disable_group_reshare_activity',
			'disable_friends_reshare_activity',
			'enable_share_count',
			'prevent_self_share',
			'respect_privacy',
			'require_permission'
		);
		
		foreach ( $boolean_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$sanitized[ $field ] = (bool) $settings[ $field ];
			}
		}
		
		// Reshare activity display mode
		$allowed_modes = array( 'parent', 'child' );
		$reshare_mode = isset( $settings['reshare_share_activity'] ) && is_string( $settings['reshare_share_activity'] ) ? 
			$settings['reshare_share_activity'] : 'parent';
		
		$sanitized['reshare_share_activity'] = in_array( $reshare_mode, $allowed_modes, true ) ? 
			$reshare_mode : 'parent';
		
		// Max share depth
		if ( isset( $settings['max_share_depth'] ) ) {
			if ( is_string( $settings['max_share_depth'] ) && $settings['max_share_depth'] === 'unlimited' ) {
				$sanitized['max_share_depth'] = 'unlimited';
			} else {
				$sanitized['max_share_depth'] = absint( $settings['max_share_depth'] );
			}
		} else {
			$sanitized['max_share_depth'] = 3;
		}
		
		return $sanitized;
	}
}