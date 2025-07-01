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
 * Simplified admin interface using native WordPress UI patterns.
 * Uses existing admin CSS/JS files with simplified functionality.
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
	 * Use only buddypress-share-admin.css
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

		$rtl_css = is_rtl() ? '-rtl' : '';
		$css_extension = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.css' : '.min.css';

		// Load main admin stylesheet only
		wp_enqueue_style( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'css' . $rtl_css . '/buddypress-share-admin' . $css_extension, 
			array(), 
			$this->version, 
			'all' 
		);
		
		// Load color picker for icon settings
		if ( isset( $_GET['section'] ) && 'icons' === $_GET['section'] ) {
			wp_enqueue_style( 'wp-color-picker' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 * Use only buddypress-share-admin.js
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

		$js_extension = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.js' : '.min.js';

		// Load jQuery UI components for drag/drop
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		
		// Load color picker for icon settings
		if ( isset( $_GET['section'] ) && 'icons' === $_GET['section'] ) {
			wp_enqueue_script( 'wp-color-picker' );
		}

		// Main admin script
		wp_enqueue_script( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'js/buddypress-share-admin' . $js_extension, 
			array( 'jquery', 'jquery-ui-sortable' ), 
			$this->version, 
			true 
		);

		// Localize script
		wp_localize_script(
			$this->plugin_name,
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
		add_options_page(
			__( 'BuddyPress Activity Share', 'buddypress-share' ),
			__( 'Activity Share', 'buddypress-share' ),
			'manage_options',
			'buddypress-share',
			array( $this, 'bp_share_plugin_options' )
		);
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

		// Get current section
		$current_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'general';
		
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'BuddyPress Activity Share Settings', 'buddypress-share' ); ?></h1>
			
			<?php
			// Show success message if settings were updated
			if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved successfully!', 'buddypress-share' ); ?></p>
				</div>
				<?php
			}
			?>

			<!-- Native WordPress Navigation Tabs -->
			<nav class="nav-tab-wrapper">
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=buddypress-share&section=general' ) ); ?>" 
				   class="nav-tab <?php echo 'general' === $current_section ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'General Settings', 'buddypress-share' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=buddypress-share&section=sharing' ) ); ?>" 
				   class="nav-tab <?php echo 'sharing' === $current_section ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Sharing Options', 'buddypress-share' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=buddypress-share&section=icons' ) ); ?>" 
				   class="nav-tab <?php echo 'icons' === $current_section ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Icon Styles', 'buddypress-share' ); ?>
				</a>
			</nav>

			<div class="tab-content">
				<?php
				switch ( $current_section ) {
					case 'sharing':
						$this->render_sharing_settings();
						break;
					case 'icons':
						$this->render_icon_settings();
						break;
					default:
						$this->render_general_settings();
						break;
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render general settings section.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function render_general_settings() {
		// Get current settings
		$bp_share_services_enable = get_site_option( 'bp_share_services_enable', 1 );
		$bp_share_services_logout_enable = get_site_option( 'bp_share_services_logout_enable', 1 );
		$bp_share_services_extra = get_site_option( 'bp_share_services_extra', array( 'bp_share_services_open' => 'on' ) );
		$bp_share_services_open = isset( $bp_share_services_extra['bp_share_services_open'] ) ? $bp_share_services_extra['bp_share_services_open'] : 'on';
		
		// Get enabled services
		$enabled_services = get_site_option( 'bp_share_services', array(
			'Facebook'  => 'Facebook',
			'Twitter'   => 'Twitter', 
			'LinkedIn'  => 'LinkedIn',
			'E-mail'    => 'E-mail',
			'WhatsApp'  => 'WhatsApp',
			'Pinterest' => 'Pinterest',
		));
		
		$all_services = $this->get_all_available_services();
		$disabled_services = array_diff_key( $all_services, $enabled_services );
		?>
		
		<form method="post" action="options.php">
			<?php
			settings_fields( 'bp_share_general_settings' );
			do_settings_sections( 'bp_share_general_settings' );
			?>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable Social Sharing', 'buddypress-share' ); ?></th>
						<td>
							<label for="bp_share_services_enable">
								<input type="checkbox" 
								       name="bp_share_services_enable" 
								       id="bp_share_services_enable"
								       value="1" 
								       <?php checked( 1, $bp_share_services_enable ); ?> />
								<?php esc_html_e( 'Enable social sharing on activities', 'buddypress-share' ); ?>
							</label>
						</td>
					</tr>
					
					<tr id="logout_sharing_row" style="<?php echo $bp_share_services_enable ? '' : 'display:none;'; ?>">
						<th scope="row"><?php esc_html_e( 'Guest Sharing', 'buddypress-share' ); ?></th>
						<td>
							<label for="bp_share_services_logout_enable">
								<input type="checkbox" 
								       name="bp_share_services_logout_enable" 
								       id="bp_share_services_logout_enable"
								       value="1" 
								       <?php checked( 1, $bp_share_services_logout_enable ); ?> />
								<?php esc_html_e( 'Allow logged-out users to share activities', 'buddypress-share' ); ?>
							</label>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php esc_html_e( 'Popup Windows', 'buddypress-share' ); ?></th>
						<td>
							<label for="bp_share_services_open">
								<input type="checkbox" 
								       name="bp_share_services_open" 
								       id="bp_share_services_open"
								       value="on" 
								       <?php checked( 'on', $bp_share_services_open ); ?> />
								<?php esc_html_e( 'Open social sharing links in popup windows', 'buddypress-share' ); ?>
							</label>
						</td>
					</tr>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Social Services', 'buddypress-share' ); ?></h2>
			<p><?php esc_html_e( 'Drag social services between the lists to enable or disable them.', 'buddypress-share' ); ?></p>
			
			<div class="social_icon_section">
				<div class="social-services-list enabled-services">
					<h3><?php esc_html_e( 'Enabled Services', 'buddypress-share' ); ?></h3>
					<ul id="drag_icon_ul" class="enabled-services-list">
						<?php if ( ! empty( $enabled_services ) ) : ?>
							<?php foreach ( $enabled_services as $service_key => $service_name ) : ?>
								<li class="socialicon icon_<?php echo esc_attr( sanitize_title( $service_key ) ); ?>">
									<?php echo esc_html( $service_name ); ?>
								</li>
							<?php endforeach; ?>
						<?php else : ?>
							<li class="no-services-message">
								<?php esc_html_e( 'No services enabled. Drag services from the available list to enable them.', 'buddypress-share' ); ?>
							</li>
						<?php endif; ?>
					</ul>
				</div>

				<div class="social-services-list disabled-services">
					<h3><?php esc_html_e( 'Available Services', 'buddypress-share' ); ?></h3>
					<ul id="drag_social_icon" class="disabled-services-list">
						<?php if ( ! empty( $disabled_services ) ) : ?>
							<?php foreach ( $disabled_services as $service_key => $service_name ) : ?>
								<li class="socialicon icon_<?php echo esc_attr( sanitize_title( $service_key ) ); ?>">
									<?php echo esc_html( $service_name ); ?>
								</li>
							<?php endforeach; ?>
						<?php else : ?>
							<li class="no-services-message">
								<?php esc_html_e( 'All services are enabled. Drag services from the enabled list to disable them.', 'buddypress-share' ); ?>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>

			<?php submit_button( __( 'Save General Settings', 'buddypress-share' ) ); ?>
		</form>
		<?php
	}

	/**
	 * Render sharing settings section.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function render_sharing_settings() {
		// Get current settings
		$bp_reshare_settings = get_site_option( 'bp_reshare_settings', array() );
		$bp_reshare_settings_activity = isset( $bp_reshare_settings['reshare_share_activity'] ) ? $bp_reshare_settings['reshare_share_activity'] : 'parent';
		?>
		
		<form method="post" action="options.php">
			<?php
			settings_fields( 'bp_reshare_settings' );
			do_settings_sections( 'bp_reshare_settings' );
			?>

			<h2><?php esc_html_e( 'Content Type Controls', 'buddypress-share' ); ?></h2>
			<p><?php esc_html_e( 'Control which types of content can be shared within your BuddyPress community.', 'buddypress-share' ); ?></p>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Disable Sharing For', 'buddypress-share' ); ?></th>
						<td>
							<fieldset>
								<label for="disable_post_reshare_activity">
									<input type="checkbox" 
									       name="bp_reshare_settings[disable_post_reshare_activity]" 
									       id="disable_post_reshare_activity"
									       value="1" 
									       <?php checked( 1, isset( $bp_reshare_settings['disable_post_reshare_activity'] ) ? $bp_reshare_settings['disable_post_reshare_activity'] : 0 ); ?> />
									<?php esc_html_e( 'Blog Posts', 'buddypress-share' ); ?>
								</label><br>
								
								<label for="disable_my_profile_reshare_activity">
									<input type="checkbox" 
									       name="bp_reshare_settings[disable_my_profile_reshare_activity]" 
									       id="disable_my_profile_reshare_activity"
									       value="1" 
									       <?php checked( 1, isset( $bp_reshare_settings['disable_my_profile_reshare_activity'] ) ? $bp_reshare_settings['disable_my_profile_reshare_activity'] : 0 ); ?> />
									<?php esc_html_e( 'Profile Sharing', 'buddypress-share' ); ?>
								</label><br>
								
								<label for="disable_message_reshare_activity">
									<input type="checkbox" 
									       name="bp_reshare_settings[disable_message_reshare_activity]" 
									       id="disable_message_reshare_activity"
									       value="1" 
									       <?php checked( 1, isset( $bp_reshare_settings['disable_message_reshare_activity'] ) ? $bp_reshare_settings['disable_message_reshare_activity'] : 0 ); ?> />
									<?php esc_html_e( 'Private Messages', 'buddypress-share' ); ?>
								</label><br>
								
								<label for="disable_group_reshare_activity">
									<input type="checkbox" 
									       name="bp_reshare_settings[disable_group_reshare_activity]" 
									       id="disable_group_reshare_activity"
									       value="1" 
									       <?php checked( 1, isset( $bp_reshare_settings['disable_group_reshare_activity'] ) ? $bp_reshare_settings['disable_group_reshare_activity'] : 0 ); ?> />
									<?php esc_html_e( 'Groups', 'buddypress-share' ); ?>
								</label><br>
								
								<label for="disable_friends_reshare_activity">
									<input type="checkbox" 
									       name="bp_reshare_settings[disable_friends_reshare_activity]" 
									       id="disable_friends_reshare_activity"
									       value="1" 
									       <?php checked( 1, isset( $bp_reshare_settings['disable_friends_reshare_activity'] ) ? $bp_reshare_settings['disable_friends_reshare_activity'] : 0 ); ?> />
									<?php esc_html_e( 'Friends', 'buddypress-share' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php esc_html_e( 'Activity Display', 'buddypress-share' ); ?></th>
						<td>
							<fieldset>
								<label for="reshare_share_activity_parent">
									<input type="radio" 
									       name="bp_reshare_settings[reshare_share_activity]" 
									       id="reshare_share_activity_parent"
									       value="parent" 
									       <?php checked( 'parent', $bp_reshare_settings_activity ); ?> />
									<?php esc_html_e( 'Show original activity only', 'buddypress-share' ); ?>
								</label><br>
								
								<label for="reshare_share_activity_child">
									<input type="radio" 
									       name="bp_reshare_settings[reshare_share_activity]" 
									       id="reshare_share_activity_child"
									       value="child" 
									       <?php checked( 'child', $bp_reshare_settings_activity ); ?> />
									<?php esc_html_e( 'Show complete activity with nested content', 'buddypress-share' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button( __( 'Save Sharing Settings', 'buddypress-share' ) ); ?>
		</form>
		<?php
	}

	/**
	 * Render icon settings section.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function render_icon_settings() {
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

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Icon Style', 'buddypress-share' ); ?></th>
						<td>
							<fieldset>
								<?php foreach ( $icon_styles as $style_key => $style_name ) : ?>
									<label for="icon_style_<?php echo esc_attr( $style_key ); ?>">
										<input type="radio" 
										       name="bpas_icon_color_settings[icon_style]" 
										       id="icon_style_<?php echo esc_attr( $style_key ); ?>"
										       value="<?php echo esc_attr( $style_key ); ?>" 
										       <?php checked( $style_key, $current_style ); ?> />
										<?php echo esc_html( $style_name ); ?>
									</label><br>
								<?php endforeach; ?>
							</fieldset>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php esc_html_e( 'Background Color', 'buddypress-share' ); ?></th>
						<td>
							<input type="text" 
							       name="bpas_icon_color_settings[bg_color]" 
							       id="bg_color"
							       value="<?php echo esc_attr( $bpas_icon_color_settings['bg_color'] ?? '#667eea' ); ?>" 
							       class="bp-share-color-picker" />
							<p class="description"><?php esc_html_e( 'Choose the background color for sharing icons.', 'buddypress-share' ); ?></p>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php esc_html_e( 'Text Color', 'buddypress-share' ); ?></th>
						<td>
							<input type="text" 
							       name="bpas_icon_color_settings[text_color]" 
							       id="text_color"
							       value="<?php echo esc_attr( $bpas_icon_color_settings['text_color'] ?? '#ffffff' ); ?>" 
							       class="bp-share-color-picker" />
							<p class="description"><?php esc_html_e( 'Choose the text/icon color for sharing buttons.', 'buddypress-share' ); ?></p>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php esc_html_e( 'Hover Color', 'buddypress-share' ); ?></th>
						<td>
							<input type="text" 
							       name="bpas_icon_color_settings[hover_color]" 
							       id="hover_color"
							       value="<?php echo esc_attr( $bpas_icon_color_settings['hover_color'] ?? '#5a6fd8' ); ?>" 
							       class="bp-share-color-picker" />
							<p class="description"><?php esc_html_e( 'Choose the hover color for sharing icons.', 'buddypress-share' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button( __( 'Save Icon Settings', 'buddypress-share' ) ); ?>
		</form>
		<?php
	}

	/**
	 * Register plugin settings.
	 * 
	 * @since    1.0.0
	 * @access   public
	 */
	public function bpas_register_setting() {
		// General settings
		register_setting( 'bp_share_general_settings', 'bp_share_services_enable', 'absint' );
		register_setting( 'bp_share_general_settings', 'bp_share_services_logout_enable', 'absint' );
		register_setting( 'bp_share_general_settings', 'bp_share_services_open', 'sanitize_text_field' );
		register_setting( 'bp_share_general_settings', 'bp_share_services', array( $this, 'sanitize_services_array' ) );
		
		// Reshare settings
		register_setting( 'bp_reshare_settings', 'bp_reshare_settings', array( $this, 'sanitize_reshare_settings' ) );
		
		// Icon settings
		register_setting( 'bpas_icon_color_settings', 'bpas_icon_color_settings', array( $this, 'sanitize_icon_settings' ) );
	}

	/**
	 * Process settings updates.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_settings_init() {
		// Process general settings
		if ( isset( $_POST['bp_share_services_enable'] ) && wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'bp_share_general_settings-options' ) ) {
			$this->process_general_settings();
		}

		// Process extra options (popup setting)
		if ( isset( $_POST['bp_share_services_open'] ) && wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'bp_share_general_settings-options' ) ) {
			$popup_option = array(
				'bp_share_services_open' => sanitize_text_field( $_POST['bp_share_services_open'] ?? '' )
			);
			update_site_option( 'bp_share_services_extra', $popup_option );
		}
	}

	/**
	 * Process general settings form.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function process_general_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Sanitize and save settings
		$service_enable = isset( $_POST['bp_share_services_enable'] ) ? absint( $_POST['bp_share_services_enable'] ) : 0;
		update_site_option( 'bp_share_services_enable', $service_enable );

		$service_enable_logout = isset( $_POST['bp_share_services_logout_enable'] ) ? absint( $_POST['bp_share_services_logout_enable'] ) : 0;
		update_site_option( 'bp_share_services_logout_enable', $service_enable_logout );
	}

	/**
	 * AJAX handler for adding social icons.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function wss_social_icons() {
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'bp_share_admin_nonce' ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'buddypress-share' ) ) );
		}
		
		$service_name = sanitize_text_field( wp_unslash( $_POST['term_name'] ?? '' ) );
		
		if ( empty( $service_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Service name is required.', 'buddypress-share' ) ) );
		}
		
		$current_services = get_site_option( 'bp_share_services', array() );
		if ( ! is_array( $current_services ) ) {
			$current_services = array();
		}
		
		$current_services[ $service_name ] = $service_name;
		$updated = update_site_option( 'bp_share_services', $current_services );
		
		if ( $updated ) {
			wp_send_json_success( array( 'message' => __( 'Service added successfully.', 'buddypress-share' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to add service.', 'buddypress-share' ) ) );
		}
	}

	/**
	 * AJAX handler for removing social icons.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function wss_social_remove_icons() {
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'bp_share_admin_nonce' ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'buddypress-share' ) ) );
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
			
			if ( $updated ) {
				wp_send_json_success( array( 'message' => __( 'Service removed successfully.', 'buddypress-share' ) ) );
			} else {
				wp_send_json_error( array( 'message' => __( 'Failed to remove service.', 'buddypress-share' ) ) );
			}
		} else {
			wp_send_json_error( array( 'message' => __( 'Service not found.', 'buddypress-share' ) ) );
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
	 * Check if current page is a plugin admin page.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string $hook Current admin page hook.
	 * @return   bool True if plugin admin page, false otherwise.
	 */
	private function is_plugin_admin_page( $hook ) {
		return ( $hook === 'settings_page_buddypress-share' || 
		         ( isset( $_GET['page'] ) && $_GET['page'] === 'buddypress-share' ) );
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
	 * Sanitize services array.
	 *
	 * @since    1.0.0
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
			$sanitized_key = sanitize_text_field( $key );
			$sanitized_value = sanitize_text_field( $value );
			
			if ( ! empty( $sanitized_key ) && ! empty( $sanitized_value ) ) {
				$sanitized[ $sanitized_key ] = $sanitized_value;
			}
		}
		
		return $sanitized;
	}

	/**
	 * Sanitize icon settings.
	 *
	 * @since    1.0.0
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
	 * @since    1.0.0
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
		);
		
		foreach ( $boolean_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$sanitized[ $field ] = (bool) $settings[ $field ];
			}
		}
		
		// Reshare activity display mode
		$allowed_modes = array( 'parent', 'child' );
		$reshare_mode = isset( $settings['reshare_share_activity'] ) ? $settings['reshare_share_activity'] : 'parent';
		$sanitized['reshare_share_activity'] = in_array( $reshare_mode, $allowed_modes, true ) ? $reshare_mode : 'parent';
		
		return $sanitized;
	}
}