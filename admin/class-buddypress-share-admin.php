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
 * Enhanced with independent menu system and better security, performance optimizations, and cache management.
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
		$rtl_css = is_rtl() ? '-rtl' : '';

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$css_extension = '.css';
		} else {
			$css_extension = '.min.css';
		}

		// Only load on plugin pages for better performance
		$plugin_pages = array(
			'toplevel_page_buddypress-share',
			'activity-share_page_buddypress-share-settings',
			'activity-share_page_buddypress-share-icons',
			'buddypress-share',
			'buddypress-share-settings',
			'buddypress-share-icons'
		);
		
		$is_plugin_page = in_array( $hook, $plugin_pages, true ) || 
		                 strpos( $hook, 'buddypress-share' ) !== false;

		if ( ! $is_plugin_page ) {
			return;
		}

		if ( ! wp_style_is( 'font-awesome', 'enqueued' ) ) {
			wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), $this->version, 'all' );
		}
		
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css' . $rtl_css . '/buddypress-share-admin' . $css_extension, array(), $this->version, 'all' );
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
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		
		$plugin_pages = array(
			'toplevel_page_buddypress-share',
			'activity-share_page_buddypress-share-settings',
			'activity-share_page_buddypress-share-icons',
		);
		
		if ( ! in_array( $hook, $plugin_pages, true ) ) {
			return;
		}
		
		wp_enqueue_script( 'wp-color-picker' );

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$js_extension = '.js';
		} else {
			$js_extension = '.min.js';
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/buddypress-share-admin' . $js_extension, array( 'jquery' ), $this->version, true );
		wp_localize_script(
			$this->plugin_name,
			'my_ajax_object',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'bp_share_nonce' ),
			)
		);
	}

	/**
	 * Function to hide all the admin notices from plugin settings page.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function wbcom_hide_all_admin_notices_from_setting_page() {
		$plugin_pages = array( 
			'buddypress-share', 
			'buddypress-share-settings', 
			'buddypress-share-icons' 
		);
		$current_page = filter_input( INPUT_GET, 'page' ) ? filter_input( INPUT_GET, 'page' ) : '';

		if ( in_array( $current_page, $plugin_pages, true ) ) {
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
			esc_html__( 'Activity Share', 'buddypress-share' ),
			esc_html__( 'Activity Share', 'buddypress-share' ),
			$capability,
			'buddypress-share',
			array( $this, 'bp_share_plugin_options' ),
			'dashicons-share',
			59
		);

		// Add submenu pages
		add_submenu_page(
			'buddypress-share',
			esc_html__( 'General Settings', 'buddypress-share' ),
			esc_html__( 'General Settings', 'buddypress-share' ),
			$capability,
			'buddypress-share',
			array( $this, 'bp_share_plugin_options' )
		);

		add_submenu_page(
			'buddypress-share',
			esc_html__( 'Share Settings', 'buddypress-share' ),
			esc_html__( 'Share Settings', 'buddypress-share' ),
			$capability,
			'buddypress-share-settings',
			array( $this, 'bp_share_settings_page' )
		);

		add_submenu_page(
			'buddypress-share',
			esc_html__( 'Icon Settings', 'buddypress-share' ),
			esc_html__( 'Icon Settings', 'buddypress-share' ),
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
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'bpas_welcome'; //phpcs:ignore
		
		// Admin capability check
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
									<?php printf( esc_html__( 'v%s', 'buddypress-share' ), esc_attr( BP_ACTIVITY_SHARE_PLUGIN_VERSION ) ); ?>
								</span>
							</h1>
							<p class="bp-share-plugin-description">
								<?php esc_html_e( 'Share BuddyPress activities on social media and within your community.', 'buddypress-share' ); ?>
							</p>
						</div>
					</div>
					<div class="bp-share-header-actions">
						<a href="https://docs.wbcomdesigns.com/doc_category/buddypress-activity-social-share/" class="button button-secondary" target="_blank">
							<span class="dashicons dashicons-book"></span>
							<?php esc_html_e( 'Documentation', 'buddypress-share' ); ?>
						</a>
						<a href="https://wbcomdesigns.com/support/" class="button button-secondary" target="_blank">
							<span class="dashicons dashicons-sos"></span>
							<?php esc_html_e( 'Support', 'buddypress-share' ); ?>
						</a>
					</div>
				</div>
			</div>

			<div class="bp-share-admin-content">
				<div class="bp-share-tabs-wrapper">
					<?php $this->bpas_plugin_settings_tabs( $tab ); ?>
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
				<?php $this->bpas_reshare_setting_section(); ?>
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
				<?php $this->bpas_icon_color_setting_section(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Tab listing with enhanced navigation.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $current The current tab.
	 */
	public function bpas_plugin_settings_tabs( $current ) {
		$bpas_tabs = array(
			'bpas_welcome'             => esc_html__( 'Welcome', 'buddypress-share' ),
			'bpas_general_settings'    => esc_html__( 'General Settings', 'buddypress-share' ),
		);
		
		$tab_html  = '<div class="bp-share-tabs-section">';
		$tab_html .= '<div class="nav-tab-wrapper">';
		$tab_html .= '<div class="bp-share-responsive-menu">';
		$tab_html .= '<span>' . esc_html__( 'Menu', 'buddypress-share' ) . '</span>';
		$tab_html .= '<input class="bp-share-toggle-btn" type="checkbox" id="bp-share-toggle-btn">';
		$tab_html .= '<label class="bp-share-toggle-icon" for="bp-share-toggle-btn">';
		$tab_html .= '<span class="bp-share-icon-bars">☰</span>';
		$tab_html .= '</label>';
		$tab_html .= '</div>';
		$tab_html .= '<ul class="bp-share-nav-tabs">';
		
		foreach ( $bpas_tabs as $bpas_tab => $bpas_name ) {
			$class     = ( $bpas_tab === $current ) ? 'nav-tab-active' : '';
			$tab_html .= '<li class="' . esc_attr( $bpas_tab ) . '">';
			$tab_html .= '<a class="nav-tab ' . esc_attr( $class ) . '" href="admin.php?page=buddypress-share&tab=' . esc_attr( $bpas_tab ) . '">';
			$tab_html .= esc_html( $bpas_name );
			$tab_html .= '</a>';
			$tab_html .= '</li>';
		}
		
		$tab_html .= '</ul>';
		$tab_html .= '</div>';
		$tab_html .= '</div>';
		
		echo $tab_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$this->bpas_include_admin_setting_tabs( $current );
	}

	/**
	 * Include the appropriate admin setting tab content.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $bpas_tab The current tab.
	 */
	public function bpas_include_admin_setting_tabs( $bpas_tab ) {
		$bpas_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'bpas_welcome'; //phpcs:ignore
		
		switch ( $bpas_tab ) {
			case 'bpas_welcome':
				$this->bpas_welcome_section();
				break;
			case 'bpas_general_settings':
				$this->bpas_general_setting_section();
				break;
			default:
				$this->bpas_welcome_section();
				break;
		}
	}

	/**
	 * Display welcome template.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bpas_welcome_section() {
		// Check if the template file exists and use it, otherwise use inline content
		$template_path = BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-welcome-page.php';
		
		if ( file_exists( $template_path ) ) {
			require_once $template_path;
		} else {
			// Fallback inline content if template file doesn't exist
			$this->render_welcome_content_inline();
		}
	}

	/**
	 * Render welcome content inline as fallback.
	 *
	 * @since    1.5.1
	 * @access   private
	 */
	private function render_welcome_content_inline() {
		?>
		<div class="bp-share-welcome-content">
			<div class="bp-share-welcome-header">
				<h2><?php esc_html_e( 'Welcome to BuddyPress Activity Share Pro', 'buddypress-share' ); ?></h2>
				<p class="bp-share-welcome-description">
					<?php esc_html_e( 'Transform your BuddyPress community with powerful sharing capabilities. Enable your members to share activities across social media platforms and within your community, boosting engagement and extending your reach.', 'buddypress-share' ); ?>
				</p>
			</div>

			<div class="bp-share-quick-setup">
				<div class="bp-share-setup-content">
					<div class="bp-share-setup-info">
						<h3><?php esc_html_e( 'Quick Setup Guide', 'buddypress-share' ); ?></h3>
						<p><?php esc_html_e( 'Get your sharing system up and running in just a few steps:', 'buddypress-share' ); ?></p>
						<ol class="bp-share-setup-steps">
							<li><?php esc_html_e( 'Enable social sharing in General Settings', 'buddypress-share' ); ?></li>
							<li><?php esc_html_e( 'Configure which social services to display', 'buddypress-share' ); ?></li>
							<li><?php esc_html_e( 'Customize your sharing options in Share Settings', 'buddypress-share' ); ?></li>
							<li><?php esc_html_e( 'Style your icons to match your brand', 'buddypress-share' ); ?></li>
						</ol>
					</div>
					<div class="bp-share-setup-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=buddypress-share&tab=bpas_general_settings' ) ); ?>" class="bp-share-cta-button primary">
							<span class="dashicons dashicons-admin-generic"></span>
							<?php esc_html_e( 'Start Configuration', 'buddypress-share' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<style>
		.bp-share-welcome-content {
			padding: 20px 0;
		}

		.bp-share-welcome-header {
			text-align: center;
			margin-bottom: 40px;
		}

		.bp-share-welcome-header h2 {
			font-size: 28px;
			color: #333;
			margin-bottom: 16px;
			font-weight: 600;
		}

		.bp-share-welcome-description {
			font-size: 16px;
			line-height: 1.6;
			color: #666;
			max-width: 700px;
			margin: 0 auto;
		}

		.bp-share-quick-setup {
			background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
			border-radius: 12px;
			padding: 40px;
			margin-bottom: 30px;
		}

		.bp-share-setup-content {
			display: grid;
			grid-template-columns: 2fr 1fr;
			gap: 40px;
			align-items: center;
		}

		.bp-share-setup-info h3 {
			font-size: 22px;
			color: #333;
			margin: 0 0 16px 0;
			font-weight: 600;
		}

		.bp-share-setup-info p {
			color: #666;
			line-height: 1.6;
			margin: 0 0 20px 0;
		}

		.bp-share-setup-steps {
			padding-left: 20px;
			color: #555;
			line-height: 1.8;
		}

		.bp-share-setup-steps li {
			margin-bottom: 8px;
		}

		.bp-share-setup-actions {
			text-align: center;
		}

		.bp-share-cta-button {
			display: inline-flex;
			align-items: center;
			gap: 10px;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: #fff;
			text-decoration: none;
			padding: 16px 32px;
			border-radius: 8px;
			font-weight: 600;
			font-size: 16px;
			transition: all 0.3s ease;
			box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
		}

		.bp-share-cta-button:hover {
			background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
			transform: translateY(-2px);
			box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
			color: #fff;
		}

		@media (max-width: 768px) {
			.bp-share-setup-content {
				grid-template-columns: 1fr;
				gap: 30px;
			}
		}
		</style>
		<?php
	}

	/**
	 * Display general settings template.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bpas_general_setting_section() {
		if ( file_exists( BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-general-settings-page.php' ) ) {
			require_once BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-general-settings-page.php';
		}
	}

	/**
	 * Display reshare settings template.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bpas_reshare_setting_section() {
		if ( file_exists( BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-reshare-setting-page.php' ) ) {
			require_once BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-reshare-setting-page.php';
		}
	}

	/**
	 * Display icon color settings template.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bpas_icon_color_setting_section() {
		if ( file_exists( BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-icon-color-page.php' ) ) {
			require_once BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-icon-color-page.php';
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
		register_setting( 'bp_share_general_settings', 'bp_share_services_enable' );
		register_setting( 'bp_share_general_settings', 'bp_share_services_logout_enable' );
		register_setting( 'bp_share_general_settings', 'bp_share_services_open' );
		register_setting( 'bp_share_general_settings', 'bp_share_services' );
		
		// Register icon color settings
		register_setting( 'bpas_icon_color_settings', 'bpas_icon_color_settings' );
		
		// Register reshare settings
		register_setting( 'bp_reshare_settings', 'bp_reshare_settings' );
	}

	/**
	 * Initialize plugin admin settings with enhanced security and validation.
	 *
	 * Enhanced with proper nonce verification and capability checks for security.
	 * Updated to work without wbcom dependencies.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_settings_init() {
		// Handle general settings form submission
		if ( isset( $_POST['bpas_submit_general_options'] ) && ! defined( 'DOING_AJAX' ) ) {
			
			// Enhanced security checks
			if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'bp_share_general_settings-options' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'buddypress-share' ) );
			}
			
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Insufficient permissions.', 'buddypress-share' ) );
			}
			
			// Save general settings
			$service_enable = isset( $_POST['bp_share_services_enable'] ) ? sanitize_text_field( wp_unslash( $_POST['bp_share_services_enable'] ) ) : '';
			update_site_option( 'bp_share_services_enable', $service_enable );

			$service_enable_logout = isset( $_POST['bp_share_services_logout_enable'] ) ? sanitize_text_field( wp_unslash( $_POST['bp_share_services_logout_enable'] ) ) : '';
			update_site_option( 'bp_share_services_logout_enable', $service_enable_logout );

			$popup_option = array();
			$popup_option['bp_share_services_open'] = isset( $_POST['bp_share_services_open'] ) ? sanitize_text_field( wp_unslash( $_POST['bp_share_services_open'] ) ) : '';
			update_site_option( 'bp_share_services_extra', $popup_option );

			// Clear cache after settings update
			$this->clear_public_settings_cache();
			
			// Redirect with success message
			wp_redirect( add_query_arg( 'settings-updated', 'true', $_POST['_wp_http_referer'] ?? admin_url( 'admin.php?page=buddypress-share&tab=bpas_general_settings' ) ) );
			exit();
		}

		// Handle reshare settings form submission
		if ( isset( $_POST['bpas_submit_reshare_options'] ) && ! defined( 'DOING_AJAX' ) ) {
			
			// Enhanced security checks
			if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'bp_reshare_settings-options' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'buddypress-share' ) );
			}
			
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Insufficient permissions.', 'buddypress-share' ) );
			}
			
			$share_options = isset( $_POST['bp_reshare_settings'] ) ? array_map( 'sanitize_text_field', $_POST['bp_reshare_settings'] ) : array();
			update_site_option( 'bp_reshare_settings', $share_options );
			
			// Clear cache after settings update
			$this->clear_public_settings_cache();
			
			wp_redirect( add_query_arg( 'settings-updated', 'true', $_POST['_wp_http_referer'] ?? admin_url( 'admin.php?page=buddypress-share-settings' ) ) );
			exit();
		}

		// Handle icon settings form submission
		if ( isset( $_POST['submit'] ) && isset( $_POST['option_page'] ) && $_POST['option_page'] === 'bpas_icon_color_settings' ) {
			// Let WordPress handle this through the normal settings API
			// This will be processed by the options.php handler
		}
	}

	/**
	 * AJAX handler for adding social icons with enhanced security.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function wss_social_icons() {
		$nonce = ! empty( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		
		if ( ! wp_verify_nonce( $nonce, 'bp_share_nonce' ) || ! current_user_can( 'manage_options' ) ) {
			$error = new WP_Error( '001', esc_html__( 'Sorry, your nonce did not verify.', 'buddypress-share' ), 'Security check failed' );
			wp_send_json_error( $error );
		}
		
		$success                = isset( $_POST['term_name'] ) ? sanitize_text_field( wp_unslash( $_POST['term_name'] ) ) : '';
		$icon_value             = array();
		$wss_admin_icon_setting = get_site_option( 'bp_share_services' );
		
		if ( empty( $wss_admin_icon_setting ) ) {
			$icon_value[ $success ] = $success;
			$update_drag_value      = update_site_option( 'bp_share_services', $icon_value );
		} else {
			$new_icon_value[ $success ] = $success;
			$merge                      = array_merge( $wss_admin_icon_setting, $new_icon_value );
			$update_drag_value          = update_site_option( 'bp_share_services', $merge );
		}
		
		if ( $update_drag_value ) {
			// Clear cache after update
			$this->clear_public_settings_cache();
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * AJAX handler for removing social icons with enhanced security.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function wss_social_remove_icons() {
		$nonce = ! empty( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		$update_drag_value = '';
		
		if ( ! wp_verify_nonce( $nonce, 'bp_share_nonce' ) || ! current_user_can( 'manage_options' ) ) {
			$error = new WP_Error( '001', esc_html__( 'Sorry, your nonce did not verify.', 'buddypress-share' ), 'Security check failed' );
			wp_send_json_error( $error );
		}
		
		$success_icon_val = isset( $_POST['icon_name'] ) ? sanitize_text_field( wp_unslash( $_POST['icon_name'] ) ) : '';

		$wss_admin_icon_remove = get_site_option( 'bp_share_services' );
		
		if ( is_array( $wss_admin_icon_remove ) ) {
			foreach ( $wss_admin_icon_remove as $key => $value ) {
				if ( $key === $success_icon_val ) {
					unset( $wss_admin_icon_remove[ $key ] );
					$update_drag_value = update_site_option( 'bp_share_services', $wss_admin_icon_remove );
					break;
				}
			}
		}
		
		if ( $update_drag_value ) {
			// Clear cache after update
			$this->clear_public_settings_cache();
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Clear public settings cache when admin settings are updated.
	 *
	 * This method is called when plugin settings are updated to ensure
	 * the public-facing cache is cleared for immediate effect.
	 *
	 * @since    1.5.1
	 * @access   public
	 */
	public function clear_public_settings_cache() {
		// Clear any object cache for the public class if it exists
		wp_cache_delete( 'bp_share_plugin_settings', 'buddypress_share' );
		
		// Fire action hook for other components to clear their caches
		do_action( 'bp_share_clear_public_cache' );
		
		// Log cache clearing in debug mode
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[BP Activity Share Pro] Public settings cache cleared after admin update.' );
		}
	}

	/**
	 * Validate and sanitize plugin settings before saving.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    array $settings Raw settings array.
	 * @return   array Sanitized settings array.
	 */
	private function sanitize_plugin_settings( $settings ) {
		$sanitized = array();
		
		if ( isset( $settings['bp_share_services_enable'] ) ) {
			$sanitized['bp_share_services_enable'] = absint( $settings['bp_share_services_enable'] );
		}
		
		if ( isset( $settings['bp_share_services_logout_enable'] ) ) {
			$sanitized['bp_share_services_logout_enable'] = absint( $settings['bp_share_services_logout_enable'] );
		}
		
		if ( isset( $settings['bp_share_services_open'] ) ) {
			$sanitized['bp_share_services_open'] = sanitize_text_field( $settings['bp_share_services_open'] );
		}
		
		return $sanitized;
	}

	/**
	 * Log admin errors for debugging purposes.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $message Error message.
	 * @param    array  $data    Additional error data.
	 */
	private function log_admin_error( $message, $data = array() ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[BP Activity Share Pro Admin] ' . $message . ' ' . wp_json_encode( $data ) );
		}
	}

	/**
	 * Check if current user can manage plugin settings.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @return   bool True if user can manage settings, false otherwise.
	 */
	private function can_manage_settings() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Verify admin nonce for security.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $nonce_field Nonce field name.
	 * @param    string $action      Nonce action.
	 * @return   bool True if nonce is valid, false otherwise.
	 */
	private function verify_admin_nonce( $nonce_field, $action ) {
		$nonce = $_POST[ $nonce_field ] ?? '';
		return wp_verify_nonce( $nonce, $action );
	}
}