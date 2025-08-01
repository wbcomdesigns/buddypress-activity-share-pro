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
 * Plugin Name:       BuddyPress Activity Share Pro (License-Free)
 * Plugin URI:        https://www.wbcomdesigns.com
 * Description:       Premium BuddyPress social sharing plugin with advanced tracking. All features unlocked - no license required!
 * Version:           2.0.0
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
	define( 'BP_ACTIVITY_SHARE_PLUGIN_VERSION', '2.0.0' );
}

if ( ! defined( 'BP_SHARE' ) ) {
	define( 'BP_SHARE', 'buddypress-share' );
	define( 'BP_ACTIVITY_SHARE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'BP_ACTIVITY_SHARE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	define( 'BP_ACTIVITY_SHARE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

// License system disabled - plugin runs without restrictions

/**
 * Initialize the plugin update checker for automatic updates.
 *
 * @since 2.0.0
 */
require plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://demos.wbcomdesigns.com/exporter/free-plugins/buddypress-activity-share-pro.json',
    __FILE__,
    'buddypress-activity-share-pro'
);

/**
 * The code that runs during plugin activation.
 * 
 * Deactivates the free version if active and runs activation tasks.
 * This action is documented in includes/class-buddypress-share-activator.php
 *
 * @since    1.0.0
 * @access   public
 * @return   void
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
 * Runs cleanup tasks on plugin deactivation.
 *
 * @since    1.5.2
 * @access   public
 * @return   void
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
 * Adds the Settings link to the plugin activate/deactivate page.
 *
 * @since    1.0.0
 * @param    array  $links Plugin action links.
 * @param    string $file  Plugin file.
 * @return   array  Modified action links.
 */
function bp_activity_share_pro_plugin_actions( $links, $file ) {
	if ( class_exists( 'BuddyPress' ) && current_user_can( 'manage_options' ) ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wbcom-buddypress-share' ) ) . '">' . esc_html__( 'Settings', 'buddypress-share' ) . '</a>';
		array_unshift( $links, $settings_link ); // before other links.
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
 * @return   void
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
 * Initialize the plugin when BuddyPress is loaded.
 *
 * Checks for BuddyPress or BuddyBoss Platform and runs the plugin if requirements are met.
 *
 * @since    1.0.0
 * @return   void
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
 * Initialize WBCom integration.
 *
 * Integrates the plugin with WBCom shared admin interface if available.
 *
 * @since    1.5.0
 * @return   void
 */
function bp_share_init_wbcom_integration() {
	// Only register if we have the requirements
	$has_buddypress = class_exists( 'BuddyPress' );
	$has_buddyboss = defined( 'BP_PLATFORM_VERSION' );
	
	if ( ( $has_buddypress || $has_buddyboss ) && bp_activity_share_pro_check_config() ) {
		// First check if wbcom_integrate_plugin is already available (from wbcom-essential or another plugin)
		if ( function_exists( 'wbcom_integrate_plugin' ) ) {
			// Use the existing integration function
			wbcom_integrate_plugin( __FILE__, array(
				'name'         => 'BP Activity Share Pro',
				'menu_title'   => 'BP Activity Share Pro',
				'slug'         => 'buddypress-share',
				'priority'     => 15,
				'icon'         => 'dashicons-share',
				'callback'     => 'bp_share_render_admin_page',
				'settings_url' => admin_url( 'admin.php?page=wbcom-buddypress-share' ),
			) );
			return;
		}
		
		// Otherwise, load our own integration
		if ( class_exists( 'BP_Activity_Share_Wbcom_Integration' ) ) {
			new BP_Activity_Share_Wbcom_Integration();
		}
	}
}

/**
 * Fallback integration if primary fails.
 *
 * Provides fallback integration method if the primary WBCom integration is not available.
 *
 * @since    1.5.0
 * @return   void
 */
function bp_share_init_fallback_integration() {
	// Only run if not already integrated
	if ( ! function_exists( 'wbcom_integrate_plugin' ) && 
	     class_exists( 'BP_Activity_Share_Wbcom_Integration' ) &&
	     bp_activity_share_pro_check_config() ) {
		new BP_Activity_Share_Wbcom_Integration();
	}
}

/**
 * Render admin page for shared wrapper.
 *
 * Callback function for WBCom integration to render the admin page.
 *
 * @since    1.5.0
 * @return   void
 */
function bp_share_render_admin_page() {
	// Make sure admin class is loaded
	if ( ! class_exists( 'Buddypress_Share_Admin' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'admin/class-buddypress-share-admin.php';
	}
	
	// Create an instance and call the method
	$admin = new Buddypress_Share_Admin( 'buddypress-share', BP_ACTIVITY_SHARE_PLUGIN_VERSION );
	$admin->bp_share_plugin_options();
}

/**
 * Initialize WBCom integration
 */
if ( is_admin() ) {
	add_action( 'init', 'bp_share_init_wbcom_integration', 1 );
	add_action( 'plugins_loaded', 'bp_share_init_fallback_integration', 20 );
}

// License system removed - plugin runs without restrictions

/**
 * Check plugin configuration.
 *
 * Verifies that the plugin is activated on the correct blog and with the correct
 * network configuration when used in multisite.
 *
 * @since    1.0.0
 * @return   bool True if configuration is valid, false otherwise.
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

	if ( get_current_blog_id() === bp_get_root_blog_id() ) {
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
 * Display admin notice for incorrect blog activation.
 *
 * Shows an error message when the plugin is not activated on the same blog as BuddyPress.
 *
 * @since    1.0.0
 * @return   void
 */
function bpshare_pro_same_blog() {
	echo '<div class="error"><p>'
	. esc_html__( 'BuddyPress Activity Share Pro requires to be activated on the blog where BuddyPress is activated.', 'buddypress-share' )
	. '</p></div>';
}

/**
 * Display admin notice for network configuration mismatch.
 *
 * Shows an error message when the plugin and BuddyPress have different network configurations.
 *
 * @since    1.0.0
 * @return   void
 */
function bpshare_pro_same_network_config() {
	echo '<div class="error"><p>'
	. esc_html__( 'BuddyPress Activity Share Pro and BuddyPress need to share the same network configuration.', 'buddypress-share' )
	. '</p></div>';
}

/**
 * Check if BuddyPress or BuddyBoss Platform is active.
 *
 * Deactivates the plugin if neither BuddyPress nor BuddyBoss Platform is active.
 *
 * @since    1.0.0
 * @return   void
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

		// Safely handle 'activate' parameter to prevent activation notice.
		if ( filter_input( INPUT_GET, 'activate' ) !== null ) {
			// Clear the activation parameter without modifying superglobal.
			wp_safe_redirect( remove_query_arg( 'activate' ) );
			exit;
		}
	}
}
add_action( 'admin_init', 'bpshare_pro_check_requirements' );

/**
 * Display admin notice when required plugins are missing.
 *
 * Shows an error message when neither BuddyPress nor BuddyBoss Platform is active.
 *
 * @since    2.2.2
 * @return   void
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
	if ( filter_input( INPUT_GET, 'activate' ) !== null ) {
		// Clear the activation parameter properly.
		wp_safe_redirect( remove_query_arg( 'activate' ) );
		exit;
	}
}

/**
 * Check for Youzify plugin compatibility.
 *
 * Deactivates the plugin if Youzify is active due to compatibility issues.
 *
 * @since    1.1.0
 * @return   void
 */
function bpshare_pro_youzify() {
	// Check if Youzify is active and the user has permissions to manage plugins.
	if ( class_exists( 'Youzify' ) && current_user_can( 'activate_plugins' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );

		// Admin notice with a descriptive message.
		add_action( 'admin_notices', 'bpshare_pro_youzify_plugin_admin_notice' );

		// Safely handle the 'activate' query parameter to prevent confusion.
		if ( filter_input( INPUT_GET, 'activate' ) !== null ) {
			// Clear the activation parameter properly.
			wp_safe_redirect( remove_query_arg( 'activate' ) );
			exit;
		}
	}
}
add_action( 'admin_init', 'bpshare_pro_youzify' );

/**
 * Display admin notice for Youzify incompatibility.
 *
 * Shows an error message when the plugin cannot be used with Youzify.
 *
 * @since    1.1.0
 * @return   void
 */
function bpshare_pro_youzify_plugin_admin_notice() {
	$bpsharepro_plugin = esc_html__( 'BuddyPress Activity Share Pro', 'buddypress-share' );
	$youzify_plugin    = esc_html__( 'Youzify', 'buddypress-share' );
	echo '<div class="error"><p>';
	/* translators: %s: */
	printf( esc_html__( '%1$s plugin can not be use with %2$s plugin.', 'buddypress-share' ), '<strong>' . esc_html( $bpsharepro_plugin ) . '</strong>', '<strong>' . esc_html( $youzify_plugin ) . '</strong>' );
	echo '</p></div>';
	if ( filter_input( INPUT_GET, 'activate' ) !== null ) {
		// Clear the activation parameter properly.
		wp_safe_redirect( remove_query_arg( 'activate' ) );
		exit;
	}
}

add_action( 'activated_plugin', 'bpshare_pro_activation_redirect_settings' );
/**
 * Redirect to plugin settings page after activation.
 *
 * Automatically redirects to the plugin settings page after successful activation.
 *
 * @since    1.0.0
 * @param    string $plugin Path to the plugin file relative to the plugins directory.
 * @return   void
 */
function bpshare_pro_activation_redirect_settings( $plugin ) {
	// If Youzify is active, no need to proceed further.
	if ( class_exists( 'Youzify' ) ) {
		return;
	}

	// Only proceed if BuddyPress is active and 'page' is not set in the URL.
	$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	if ( class_exists( 'BuddyPress' ) && ! $page ) {

		// Sanitize input and check if the correct plugin is being activated.
		if ( sanitize_text_field( $plugin ) === plugin_basename( __FILE__ ) ) {

			// Check if action and plugin match the expected values, ensuring they are sanitized.
			$action = filter_input( INPUT_REQUEST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$request_plugin = filter_input( INPUT_REQUEST, 'plugin', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			
			if ( 'activate' === $action && $request_plugin === $plugin ) {
				// Redirect to the settings page after plugin activation.
				wp_safe_redirect( admin_url( 'admin.php?page=wbcom-buddypress-share' ) );
				exit;
			}
		}
	}
}

add_filter( 'bp_activity_reshare_post_type', 'bp_activity_reshare_post_disable' );

/**
 * Disable post sharing if the respective option is disabled.
 *
 * Removes 'post' from the array of shareable post types if post resharing is disabled.
 *
 * @since    1.0.0
 * @param    array $post_type Array of post types.
 * @return   array Modified array of post types.
 */
function bp_activity_reshare_post_disable( $post_type ) {
	$bp_reshare_settings = get_site_option( 'bp_reshare_settings' );
	if ( isset( $bp_reshare_settings['disable_post_reshare_activity'] ) && 1 === $bp_reshare_settings['disable_post_reshare_activity'] ) {

		if ( ( $key = array_search( 'post', $post_type ) ) !== false ) {
			unset( $post_type[ $key ] );
		}
	}
	return $post_type;
}






/**
 * Add filter to customize submenu labels
 */
add_filter( 'wbcom_submenu_label', 'bp_share_customize_submenu_label', 10, 3 );

/**
 * Customize submenu label for BuddyPress Activity Share Pro.
 *
 * Filters the WBCom submenu label to provide a custom label for this plugin.
 *
 * @since    1.5.2
 * @param    string $label  Current menu label.
 * @param    string $slug   Plugin slug.
 * @param    array  $plugin Plugin data.
 * @return   string Modified menu label.
 */
function bp_share_customize_submenu_label( $label, $slug, $plugin ) {
	// Change menu label for this plugin
	if ( $slug === 'buddypress-share' ) {
		return esc_html__( 'BP Activity Share Pro', 'buddypress-share' );
	}
	
	return $label;
}