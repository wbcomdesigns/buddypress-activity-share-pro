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
 * Enhanced with better security, performance optimizations, and cache management.
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
			'wbcom-support-page',
			'wb-plugins_page_buddypress-share',
			'wbcomplugins',
			'wbcom-plugins-page',
			'buddypress-share'
		);
		
		$current_page = $_GET['page'] ?? '';
		$is_plugin_page = in_array( $current_page, $plugin_pages, true ) || 
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
		
		if ( 'wb-plugins_page_buddypress-share' !== $hook ) {
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
		$wbcom_pages_array  = array( 'wbcomplugins', 'wbcom-plugins-page', 'wbcom-support-page', 'buddypress-share' );
		$wbcom_setting_page = filter_input( INPUT_GET, 'page' ) ? filter_input( INPUT_GET, 'page' ) : '';

		if ( in_array( $wbcom_setting_page, $wbcom_pages_array, true ) ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	/**
	 * Function for add plugin menu.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_plugin_menu() {
		if ( empty( $GLOBALS['admin_page_hooks']['wbcomplugins'] ) ) {
			add_menu_page( esc_html__( 'WB Plugins', 'buddypress-share' ), esc_html__( 'WB Plugins', 'buddypress-share' ), 'manage_options', 'wbcomplugins', array( $this, 'bp_share_plugin_options' ), 'dashicons-lightbulb', 59 );
			add_submenu_page( 'wbcomplugins', esc_html__( 'General', 'buddypress-share' ), esc_html__( 'General', 'buddypress-share' ), 'manage_options', 'wbcomplugins' );
		}
		add_submenu_page( 'wbcomplugins', esc_html__( 'Activity Share Pro', 'buddypress-share' ), esc_html__( 'Activity Share Pro', 'buddypress-share' ), 'manage_options', $this->plugin_name, array( $this, 'bp_share_plugin_options' ) );
	}

	/**
	 * Build the admin options page.
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
			<div class="wbcom-bb-plugins-offer-wrapper">
				<div id="wb_admin_logo"></div>
			</div>
			<div class="wbcom-wrap">
				<div class="bpss-header">
					<div class="wbcom_admin_header-wrapper">
						<div id="wb_admin_plugin_name">
							<?php esc_html_e( 'BuddyPress Activity Share Pro Settings', 'buddypress-share' ); ?>
							<?php /* translators: %s: Plugin version */ ?>
							<span><?php printf( esc_html__( 'Version %s', 'buddypress-share' ), esc_attr( BP_ACTIVITY_SHARE_PLUGIN_VERSION ) ); ?></span>
						</div>
						<?php echo do_shortcode( '[wbcom_admin_setting_header]' ); ?>
					</div>
				</div>
				<div class="wbcom-admin-settings-page">
					<?php $this->bpas_plugin_settings_tabs( $tab ); ?>
				</div>
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
			'bpas_reshare_settings'    => esc_html__( 'Share Settings', 'buddypress-share' ),
			'bpas_icon_color_settings' => esc_html__( 'Icon Settings', 'buddypress-share' ),
		);
		
		$tab_html  = '<div class="wbcom-tabs-section"><div class="nav-tab-wrapper"><div class="wb-responsive-menu"><span>' . esc_html( 'Menu' ) . '</span><input class="wb-toggle-btn" type="checkbox" id="wb-toggle-btn"><label class="wb-toggle-icon" for="wb-toggle-btn"><span class="wb-icon-bars"></span></label></div><ul>';
		
		foreach ( $bpas_tabs as $bpas_tab => $bpas_name ) {
			$class     = ( $bpas_tab === $current ) ? 'nav-tab-active' : '';
			$tab_html .= '<li class="' . esc_attr( $bpas_tab ) . '"><a class="nav-tab ' . esc_attr( $class ) . '" href="admin.php?page=buddypress-share&tab=' . esc_attr( $bpas_tab ) . '">' . esc_html( $bpas_name ) . '</a></li>';
		}
		
		$tab_html .= '</div></ul></div>';
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
			case 'bpas_reshare_settings':
				$this->bpas_reshare_setting_section();
				break;
			case 'bpas_icon_color_settings':
				$this->bpas_icon_color_setting_section();
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
		if ( file_exists( BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-welcome-page.php' ) ) {
			require_once BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-welcome-page.php';
		}
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
	 * Function to register icon color settings.
	 * 
	 * @since    1.0.0
	 * @access   public
	 */
	public function bpas_register_setting() {
		register_setting( 'bpas_icon_color_settings', 'bpas_icon_color_settings' );
	}

	/**
	 * Initialize plugin admin settings with enhanced security and validation.
	 *
	 * Enhanced with proper nonce verification and capability checks for security.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_settings_init() {
		//phpcs:disable
		if ( isset( $_POST['bpas_submit_general_options'] ) && ! defined( 'DOING_AJAX' ) ) {
			
			// Enhanced security checks
			if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'update-options' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'buddypress-share' ) );
			}
			
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Insufficient permissions.', 'buddypress-share' ) );
			}
			
			$service_enable = isset( $_POST['bp_share_services_enable'] ) ? sanitize_text_field( wp_unslash( $_POST['bp_share_services_enable'] ) ) : '';
			update_site_option( 'bp_share_services_enable', $service_enable );

			$service_enable_logout = isset( $_POST['bp_share_services_logout_enable'] ) ? sanitize_text_field( wp_unslash( $_POST['bp_share_services_logout_enable'] ) ) : '';
			update_site_option( 'bp_share_services_logout_enable', $service_enable_logout );

			/**
			 * We are saving the popup option as array again as it was previously saved in a similar manner.
			 */
			$popup_option['bp_share_services_open'] = isset( $_POST['bp_share_services_open'] ) ? sanitize_text_field( wp_unslash( $_POST['bp_share_services_open'] ) ) : '';
			update_site_option( 'bp_share_services_extra', $popup_option );

			// Clear cache after settings update
			$this->clear_public_settings_cache();
		}

		if ( isset( $_POST['bpas_submit_reshare_options'] ) && ! defined( 'DOING_AJAX' ) ) {
			
			// Enhanced security checks
			if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'update-options' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'buddypress-share' ) );
			}
			
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Insufficient permissions.', 'buddypress-share' ) );
			}
			
			$share_options = isset( $_POST['bp_reshare_settings'] ) ? array_map( 'sanitize_text_field', $_POST['bp_reshare_settings'] ) : array();
			update_site_option( 'bp_reshare_settings', $share_options );
			
			// Clear cache after settings update
			$this->clear_public_settings_cache();
			
			wp_redirect( add_query_arg( 'settings-updated', 'true', $_POST['_wp_http_referer'] ) );
			exit();
		}
		//phpcs:enable
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