<?php
/**
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://wbcomdesigns.com
 * @since             1.0.0
 * @package           Buddypress_Share
 *
 * @wordpress-plugin
 * Plugin Name:       Wbcom Designs - BuddyPress Activity Share Pro
 * Plugin URI:        https://www.wbcomdesigns.com
 * Description:       This plugin adds an extended feature to BuddyPress, allowing users to share Activity 'Post Updates' on social sites.
 * Version:           1.5.2
 * Author:            Wbcom Designs<admin@wbcomdesigns.com>
 * Author URI:        https://www.wbcomdesigns.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       buddypress-share
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'BP_ACTIVITY_SHARE_PLUGIN_VERSION' ) ) {
	define( 'BP_ACTIVITY_SHARE_PLUGIN_VERSION', '1.5.2' );
}

if ( ! defined( 'BP_SHARE' ) ) {
	define( 'BP_SHARE', 'buddypress-share' );
	define( 'BP_ACTIVITY_SHARE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'BP_ACTIVITY_SHARE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	define( 'BP_ACTIVITY_SHARE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

// EDD License constants
if ( ! defined( 'BP_ACTIVITY_SHARE_STORE_URL' ) ) {
	define( 'BP_ACTIVITY_SHARE_STORE_URL', 'https://wbcomdesigns.com/' );
	define( 'BP_ACTIVITY_SHARE_ITEM_ID', 1234567 ); // Replace with your actual EDD item ID
	define( 'BP_ACTIVITY_SHARE_ITEM_NAME', 'BuddyPress Activity Share Pro' );
}

/**
 * Load license system early with proper loading order
 */
function bp_share_load_license_system() {
	// Only load if user has permissions
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	// Define license files in the correct loading order
	$license_files = array(
		'EDD_SL_Plugin_Updater' => BP_ACTIVITY_SHARE_PLUGIN_PATH . 'license/EDD_SL_Plugin_Updater.php',
		'BP_Share_EDD_Updater_Wrapper' => BP_ACTIVITY_SHARE_PLUGIN_PATH . 'license/class-bp-share-edd-updater-wrapper.php',
		'BP_Share_License_Manager' => BP_ACTIVITY_SHARE_PLUGIN_PATH . 'license/class-bp-share-license-manager.php',
		'BP_Share_License_Updater' => BP_ACTIVITY_SHARE_PLUGIN_PATH . 'license/class-bp-share-license-updater.php'
	);
	
	// Check if all files exist first
	$missing_files = array();
	foreach ( $license_files as $class_name => $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			$missing_files[] = $file_path;
			// License file missing - handled by admin notice below
		}
	}
	
	if ( ! empty( $missing_files ) ) {
		add_action( 'admin_notices', function() use ( $missing_files ) {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'BuddyPress Activity Share Pro: License management files are missing.', 'buddypress-share' ); ?></p>
				<p><strong><?php esc_html_e( 'Missing files:', 'buddypress-share' ); ?></strong></p>
				<ul>
					<?php foreach ( $missing_files as $file ) : ?>
						<li><code><?php echo esc_html( str_replace( ABSPATH, '', $file ) ); ?></code></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
		});
		return;
	}
	
	// Load files in the correct order to avoid class dependency issues
	foreach ( $license_files as $class_name => $file_path ) {
		if ( ! class_exists( $class_name ) ) {
			require_once $file_path;
			
			// Verify the class was loaded
			if ( ! class_exists( $class_name ) ) {
				// Failed to load class - handled by admin notice below
				add_action( 'admin_notices', function() use ( $class_name, $file_path ) {
					?>
					<div class="notice notice-error">
						<p><?php printf( esc_html__( 'BuddyPress Activity Share Pro: Failed to load license class %s from %s', 'buddypress-share' ), $class_name, basename( $file_path ) ); ?></p>
					</div>
					<?php
				});
				return;
			}
		}
	}
	
	// Initialize license components only after all classes are loaded
	if ( class_exists( 'BP_Share_License_Manager' ) ) {
		BP_Share_License_Manager::get_instance();
	}
	
	if ( class_exists( 'BP_Share_License_Updater' ) ) {
		BP_Share_License_Updater::get_instance();
	}
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-buddypress-share-activator.php
 *
 * @access public
 * @author   Wbcom Designs
 * @since    1.0.0
 */
function activate_buddypress_share_pro() {
	// Deactivate free version if active
	if ( in_array( 'buddypress-activity-social-share/buddypress-share.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		deactivate_plugins( 'buddypress-activity-social-share/buddypress-share.php' );
	}
	
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-buddypress-share-activator.php';
	Buddypress_Share_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_buddypress_share_pro' );

/**
 * The code that runs during plugin deactivation.
 *
 * @access public
 * @author   Wbcom Designs
 * @since    1.5.2
 */
function deactivate_buddypress_share_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-buddypress-share-activator.php';
	Buddypress_Share_Activator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_buddypress_share_pro' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
if ( ! class_exists( 'Buddypress_Share' ) ) {
	require plugin_dir_path( __FILE__ ) . 'includes/class-buddypress-share.php';
}

/**
 * Adding setting link on plugin listing page
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bp_activity_share_pro_plugin_actions', 10, 2 );

/**
 * Adds the Settings link to the plugin activate/deactivate page
 *
 * @param array $links Plugin action links.
 * @param string $file Plugin file.
 * @return array Modified action links.
 */
function bp_activity_share_pro_plugin_actions( $links, $file ) {
	if ( class_exists( 'BuddyPress' ) && current_user_can( 'manage_options' ) ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=buddypress-share' ) ) . '">' . esc_html__( 'Settings', 'buddypress-share' ) . '</a>';
		$license_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=buddypress-share&section=license' ) ) . '">' . esc_html__( 'License', 'buddypress-share' ) . '</a>';
		array_unshift( $links, $settings_link, $license_link ); // before other links.
	}
	return $links;
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_buddypress_share_pro() {
	$plugin = new Buddypress_Share();
	$plugin->run();
}

/**
 * Check plugin requirements on plugins loaded
 * This plugin requires BuddyPress to be installed and active
 */
add_action( 'bp_loaded', 'bpshare_pro_plugin_init' );

/**
 * Plugin init
 */
function bpshare_pro_plugin_init() {
	// Check if either BuddyPress or BuddyBoss Platform is active
	$has_buddypress = class_exists( 'BuddyPress' );
	$has_buddyboss = defined( 'BP_PLATFORM_VERSION' );
	
	if ( ( $has_buddypress || $has_buddyboss ) && bp_activity_share_pro_check_config() ) {
		run_buddypress_share_pro();
	}
}

/**
 * Load license system after plugins are loaded with proper dependency order
 */
add_action( 'plugins_loaded', 'bp_share_load_license_system', 5 );

/**
 * Check config
 */
function bp_activity_share_pro_check_config() {
	global $bp;

	if ( ! isset( $bp ) || ! is_object( $bp ) ) {
		return false;
	}

	$config = array(
		'blog_status'    => false,
		'network_active' => false,
		'network_status' => true,
	);

	if ( get_current_blog_id() == bp_get_root_blog_id() ) {
		$config['blog_status'] = true;
	}

	$network_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
	$check           = array( BP_ACTIVITY_SHARE_PLUGIN_BASENAME );

	if ( isset( $bp->basename ) ) {
		$check[] = $bp->basename;
	}

	$network_active = array_diff( $check, array_keys( $network_plugins ) );

	if ( count( $network_active ) === 1 ) {
		$config['network_status'] = false;
	}

	$config['network_active'] = isset( $network_plugins[ BP_ACTIVITY_SHARE_PLUGIN_BASENAME ] );

	if ( ! $config['blog_status'] || ! $config['network_status'] ) {
		if ( ! bp_core_do_network_admin() && ! $config['blog_status'] ) {
			add_action( 'admin_notices', 'bpshare_pro_same_blog' );
		}
		if ( bp_core_do_network_admin() && ! $config['network_status'] ) {
			add_action( 'admin_notices', 'bpshare_pro_same_network_config' );
		}
		return false;
	}
	return true;
}

/**
 * Same Blog
 */
function bpshare_pro_same_blog() {
	echo '<div class="error"><p>'
	. esc_html__( 'BuddyPress Activity Share Pro requires to be activated on the blog where BuddyPress is activated.', 'buddypress-share' )
	. '</p></div>';
}

/**
 * Network config
 */
function bpshare_pro_same_network_config() {
	echo '<div class="error"><p>'
	. esc_html__( 'BuddyPress Activity Share Pro and BuddyPress need to share the same network configuration.', 'buddypress-share' )
	. '</p></div>';
}

/**
 * Check if BuddyPress or BuddyBoss Platform is active.
 */
function bpshare_pro_check_requirements() {
	// Check if in the admin area and current user has permission to manage options.
	if ( ! is_admin() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	
	// Check for BuddyPress or BuddyBoss Platform
	$has_buddypress = class_exists( 'BuddyPress' );
	$has_buddyboss = defined( 'BP_PLATFORM_VERSION' );
	
	// If neither is active, deactivate this plugin
	if ( ! $has_buddypress && ! $has_buddyboss ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'bpshare_pro_required_plugin_admin_notice' );

		// Safely unset 'activate' parameter to prevent activation notice.
		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore
			unset( $_GET['activate'] ); // phpcs:ignore
		}
	}
}
add_action( 'admin_init', 'bpshare_pro_check_requirements' );

/**
 * Throw an Alert to tell the Admin why it didn't activate.
 *
 * @author wbcomdesigns
 * @since  2.2.2
 */
function bpshare_pro_required_plugin_admin_notice() {
	$plugin_name = esc_html__( 'BuddyPress Activity Share Pro', 'buddypress-share' );
	echo '<div class="error"><p>';
	printf(
		/* translators: %s: Name of the plugin */
		esc_html__( '%s requires either BuddyPress or BuddyBoss Platform to be installed and active.', 'buddypress-share' ),
		'<strong>' . esc_html( $plugin_name ) . '</strong>'
	);
	echo '</p></div>';
	if ( isset( $_GET['activate'] ) ) { //phpcs:ignore
		unset( $_GET['activate'] ); //phpcs:ignore
	}
}

/**
 * Add notice with youzify plugin.
 *
 * @author wbcomdesigns
 * @since  1.1.0
 */
function bpshare_pro_youzify() {
	// Check if Youzify is active and the user has permissions to manage plugins.
	if ( class_exists( 'Youzify' ) && current_user_can( 'activate_plugins' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );

		// Admin notice with a descriptive message.
		add_action( 'admin_notices', 'bpshare_pro_youzify_plugin_admin_notice' );

		// Safely unset the 'activate' query parameter to prevent confusion.
		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore
            unset( $_GET['activate'] ); // phpcs:ignore
		}
	}
}
add_action( 'admin_init', 'bpshare_pro_youzify' );

/**
 * Throw an Alert to tell the Admin why it didn't activate.
 *
 * @author wbcomdesigns
 * @since  1.1.0
 */
function bpshare_pro_youzify_plugin_admin_notice() {
	$bpsharepro_plugin = esc_html__( 'BuddyPress Activity Share Pro', 'buddypress-share' );
	$youzify_plugin    = esc_html__( 'Youzify', 'buddypress-share' );
	echo '<div class="error"><p>';
	/* translators: %s: */
	printf( esc_html__( '%1$s plugin can not be use with %2$s plugin.', 'buddypress-share' ), '<strong>' . esc_html( $bpsharepro_plugin ) . '</strong>', '<strong>' . esc_html( $youzify_plugin ) . '</strong>' );
	echo '</p></div>';
	if ( isset( $_GET['activate'] ) ) { //phpcs:ignore
		unset( $_GET['activate'] ); //phpcs:ignore
	}
}

add_action( 'activated_plugin', 'bpshare_pro_activation_redirect_settings' );
/**
 * Redirect to plugin settings page after activation
 */
function bpshare_pro_activation_redirect_settings( $plugin ) {
	// If Youzify is active, no need to proceed further.
	if ( class_exists( 'Youzify' ) ) {
		return;
	}

	// Only proceed if BuddyPress is active and 'page' is not set in the URL.
	if ( class_exists( 'BuddyPress' ) && ! isset( $_GET['page'] ) ) { // phpcs:ignore

		// Sanitize input and check if the correct plugin is being activated.
		if ( sanitize_text_field( $plugin ) === plugin_basename( __FILE__ ) ) {

			// Check if action and plugin match the expected values, ensuring they are sanitized.
			if ( isset( $_REQUEST['action'] ) && 'activate' === sanitize_text_field( $_REQUEST['action'] ) && isset( $_REQUEST['plugin'] ) && sanitize_text_field( $_REQUEST['plugin'] ) === $plugin ) { // phpcs:ignore

				// Redirect to the settings page after plugin activation.
				wp_redirect( admin_url( 'options-general.php?page=buddypress-share' ) );
				exit;
			}
		}
	}
}

add_filter( 'bp_activity_reshare_post_type', 'bp_activity_reshare_post_disable' );

/**
 * Function to disable post sharing if the respective option is disabled
 * @param $post_type array Array of post types.
 * 
 * @since 1.0.0
 * @return $post_type array Modified array of post types.
 */
function bp_activity_reshare_post_disable( $post_type ) {
	$bp_reshare_settings = get_site_option( 'bp_reshare_settings' );
	if ( isset( $bp_reshare_settings['disable_post_reshare_activity'] ) && $bp_reshare_settings['disable_post_reshare_activity'] == 1 ) {

		if ( ( $key = array_search( 'post', $post_type ) ) !== false ) {
			unset( $post_type[ $key ] );
		}
	}
	return $post_type;
}


/**
 * Initialize default options for new installations only.
 * This runs on admin_init to ensure proper initialization without conflicts.
 *
 * @since 1.5.1
 */
function bp_share_pro_init_defaults() {
	// Only run for administrators
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Check if this is a fresh installation (no version set yet)
	$installed_version = get_site_option( 'bp_share_plugin_version' );
	
	if ( false === $installed_version ) {
		// This might be a fresh installation, ensure defaults are set
		bp_share_pro_ensure_defaults();
	}
}
add_action( 'admin_init', 'bp_share_pro_init_defaults' );

/**
 * Ensure default options are set for fresh installations.
 * This function is safe to run multiple times.
 *
 * @since 1.5.1
 */
function bp_share_pro_ensure_defaults() {
	// Set plugin version if not set
	if ( false === get_site_option( 'bp_share_plugin_version' ) ) {
		update_site_option( 'bp_share_plugin_version', BP_ACTIVITY_SHARE_PLUGIN_VERSION );
	}

	// Ensure core services are set with improved defaults
	$current_services = get_site_option( 'bp_share_services' );
	if ( empty( $current_services ) || ! is_array( $current_services ) ) {
		$default_services = array(
			'Facebook'  => 'Facebook',
			'Twitter'   => 'Twitter',
			'LinkedIn'  => 'LinkedIn',
			'E-mail'    => 'E-mail',
			'WhatsApp'  => 'WhatsApp',
			'Pinterest' => 'Pinterest',
		);
		update_site_option( 'bp_share_services', $default_services );
	}

	// Ensure main settings are enabled by default
	if ( false === get_site_option( 'bp_share_services_enable' ) ) {
		update_site_option( 'bp_share_services_enable', 1 );
	}

	if ( false === get_site_option( 'bp_share_services_logout_enable' ) ) {
		update_site_option( 'bp_share_services_logout_enable', 1 );
	}

	// Ensure extra options are set
	$extra_options = get_site_option( 'bp_share_services_extra' );
	if ( empty( $extra_options ) || ! is_array( $extra_options ) ) {
		$extra_options = array(
			'bp_share_services_open' => 'on',
		);
		update_site_option( 'bp_share_services_extra', $extra_options );
	}

	// Ensure reshare settings have good defaults
	$reshare_settings = get_site_option( 'bp_reshare_settings' );
	if ( empty( $reshare_settings ) || ! is_array( $reshare_settings ) ) {
		$reshare_settings = array(
			'reshare_share_activity'               => 'parent',
			'enable_share_count'                   => 1,
			'prevent_self_share'                   => 0,
			'respect_privacy'                      => 1,
			'max_share_depth'                      => 3,
			'require_permission'                   => 0,
			// All content types enabled by default
			'disable_post_reshare_activity'        => 0,
			'disable_my_profile_reshare_activity'  => 0,
			'disable_group_reshare_activity'       => 0,
			'disable_friends_reshare_activity'     => 0,
		);
		update_site_option( 'bp_reshare_settings', $reshare_settings );
	}

	// Ensure icon settings have good defaults
	$icon_settings = get_site_option( 'bpas_icon_color_settings' );
	if ( empty( $icon_settings ) || ! is_array( $icon_settings ) ) {
		$icon_settings = array(
			'icon_style'    => 'circle',
			'show_labels'   => 1,
			'animate_icons' => 1,
			'icon_size'     => 'medium',
			'bg_color'      => '#667eea',
			'text_color'    => '#ffffff',
			'hover_color'   => '#5a6fd8',
			'border_color'  => '#e1e5e9',
		);
		update_site_option( 'bpas_icon_color_settings', $icon_settings );
	}
}