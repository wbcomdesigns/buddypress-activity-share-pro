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
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		// if ( 'wb-plugins_page_buddypress-share' !== $hook ) {
		// return;
		// }

		$rtl_css = is_rtl() ? '-rtl' : '';

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$css_extension = '.css';
		} else {
			$css_extension = '.min.css';
		}

		if ( ! wp_style_is( 'font-awesome', 'enqueued' ) ) {
			wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), $this->version, 'all' );
		}
		wp_enqueue_style( 'wp-color-picker' );
		if ( ! isset( $_GET['page'] ) ) { //phpcs:ignore
			return;
		}
		if ( isset( $_GET['page'] ) && 'wbcom-support-page' == $_GET['page'] || 'wb-plugins_page_buddypress-share' === $hook || 'wbcomplugins' === $_GET['page'] || 'wbcom-plugins-page' === $_GET['page'] || 'buddypress-share' === $_GET['page'] ) { //phpcs:ignore

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css' . $rtl_css . '/buddypress-share-admin' . $css_extension, array(), $this->version, 'all' );

		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
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
	 * @access public
	 * @author  Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_plugin_menu() {
		if ( empty( $GLOBALS['admin_page_hooks']['wbcomplugins'] ) ) {
			add_menu_page( esc_html__( 'WB Plugins', 'buddypress-share' ), esc_html__( 'WB Plugins', 'buddypress-share' ), 'manage_options', 'wbcomplugins', array( $this, 'bp_share_plugin_options' ), 'dashicons-lightbulb', 59 );
			add_submenu_page( 'wbcomplugins', esc_html__( 'General', 'buddypress-share' ), esc_html__( 'General', 'buddypress-share' ), 'manage_options', 'wbcomplugins' );
		}
		add_submenu_page( 'wbcomplugins', esc_html__( 'BuddyPress Share', 'buddypress-share' ), esc_html__( 'BuddyPress Share', 'buddypress-share' ), 'manage_options', $this->plugin_name, array( $this, 'bp_share_plugin_options' ) );
	}

	/**
	 * Build the admin options page.
	 *
	 * @access public
	 * @author  Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_plugin_options() {
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'bpas_welcome'; //phpcs:ignore
		// admin check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'buddypress-share' ) );
		}
		?>
		<div class="wrap">
			<div class="wbcom-bb-plugins-offer-wrapper">
					<div id="wb_admin_logo">
					</div>
				</div>
			<div class="wbcom-wrap">
				<div class="bpss-header">
					<div class="wbcom_admin_header-wrapper">
						<div id="wb_admin_plugin_name">
							<?php esc_html_e( 'BuddyPress Activity Share Pro Settings', 'buddypress-share' ); ?>
							<?php /* translators: %s: */ ?>
							<span><?php printf( esc_html__( 'Version %s', 'buddypress-share' ), esc_attr( BP_ACTIVITY_SHARE_PLUGIN_VERSION ) ); ?></span>
						</div>
						<?php echo do_shortcode( '[wbcom_admin_setting_header]' ); ?>
					</div>
				</div>
				<div class="wbcom-admin-settings-page">
					<?php
					$this->bpas_plugin_settings_tabs( $tab );
					?>
				</div>
			</div>
		</div>
			<?php
	}

	/**
	 * Tab listing
	 *
	 * @param current $current the current tab.
	 * @since    1.0.0
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
			$tab_html .= '<li class="' . esc_attr( $bpas_tab ) . '"><a class="nav-tab ' . esc_attr( $class ) . '" href="admin.php?page=buddypress-share&tab=' . $bpas_tab . '">' . esc_html( $bpas_name ) . '</a></li>';
		}
		$tab_html .= '</div></ul></div>';
		echo $tab_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$this->bpas_include_admin_setting_tabs( $current );
	}


	/**
	 * Tab listing
	 *
	 * @param bpas_tab $bpas_tab the current tab.
	 * @since    1.0.0
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
	 * welcome template
	 *
	 * @since    1.0.0
	 */
	public function bpas_welcome_section() {
		if ( file_exists( BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-welcome-page.php' ) ) {
			require_once BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-welcome-page.php';
		}
	}

	/**
	 * Social service settig template
	 *
	 * @since    1.0.0
	 */
	public function bpas_general_setting_section() {
		if ( file_exists( BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-general-settings-page.php' ) ) {
			require_once BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-general-settings-page.php';
		}
	}

	/**
	 * reshare settig template
	 *
	 * @since    1.0.0
	 */
	public function bpas_reshare_setting_section() {
		if ( file_exists( BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-reshare-setting-page.php' ) ) {
			require_once BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-reshare-setting-page.php';
		}
	}

	/**
	 * Social service Icons settig template
	 *
	 * @since    1.0.0
	 */
	public function bpas_icon_color_setting_section() {
		if ( file_exists( BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-icon-color-page.php' ) ) {
			require_once BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/templates/bpas-icon-color-page.php';
		}
	}

	public function bpas_register_setting() {
		register_setting( 'bpas_icon_color_settings', 'bpas_icon_color_settings' );
	}

	/**
	 * Intialize plugin admin settings.
	 *
	 * @access public
	 * @author  Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_settings_init() {

		if ( isset( $_POST['bpas_submit_general_options'] ) && ! defined( 'DOING_AJAX' ) ) {
			
			$service_enable = isset( $_POST['bp_share_services_enable'] ) ? sanitize_text_field( $_POST['bp_share_services_enable'] ) : '';
			update_site_option( 'bp_share_services_enable', $service_enable );

			$service_enable_logout = isset( $_POST['bp_share_services_logout_enable'] ) ? sanitize_text_field( $_POST['bp_share_services_logout_enable'] ) : '';
			update_site_option( 'bp_share_services_logout_enable', $service_enable_logout );

			/**
			 * We are saving the popup option as array again as it was previously saved in a similar manner.
			 */
			$popup_option['bp_share_services_open'] = isset( $_POST['bp_share_services_open'] ) ? sanitize_text_field( $_POST['bp_share_services_open'] ) : '';
			update_site_option( 'bp_share_services_extra', $popup_option );

		}

		if ( isset( $_POST['bpas_submit_reshare_options'] ) && ! defined( 'DOING_AJAX' ) ) {
			$share_options = isset( $_POST['bp_reshare_settings'] ) ? $_POST['bp_reshare_settings'] : '';
			update_site_option( 'bp_reshare_settings', $share_options );
			wp_redirect( $_POST['_wp_http_referer'].'&settings-updated=true' ); //phpcs:ignore
			exit();

		}

	}

	/**
	 * This function is for that save social icon values in database
	 **/
	public function wss_social_icons() {
		$nonce = ! empty( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bp_share_nonce' ) && ! current_user_can( 'manage_options' ) ) {
			$error = new WP_Error( '001', 'Sorry, your nonce did not verify.', 'Some information' );
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
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * This function is for that remove social icon when drag social icon in disable section
	 **/
	public function wss_social_remove_icons() {
		$nonce = ! empty( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bp_share_nonce' ) && ! current_user_can( 'manage_options' ) ) {
			$error = new WP_Error( '001', 'Sorry, your nonce did not verify.', 'Some information' );
			wp_send_json_error( $error );
		}
		$success_icon_val = isset( $_POST['icon_name'] ) ? sanitize_text_field( wp_unslash( $_POST['icon_name'] ) ) : '';

		$icon_value_array      = array();
		$wss_admin_icon_remove = get_site_option( 'bp_share_services' );
		foreach ( $wss_admin_icon_remove as $key => $value ) {
			if ( $key === $success_icon_val ) {
				unset( $wss_admin_icon_remove[ $key ] );
				$update_drag_value = update_site_option( 'bp_share_services', $wss_admin_icon_remove );
			}
		}
		if ( $update_drag_value ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}
}
