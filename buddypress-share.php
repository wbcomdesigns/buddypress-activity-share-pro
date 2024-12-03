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
 * Description:       This plugin will add an extended feature to BuddyPress, which allows users to share Activity “Post Updates” on social sites.
 * Version:           1.4.0
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
	define( 'BP_ACTIVITY_SHARE_PLUGIN_VERSION', '1.4.0' );
}
if ( ! defined( 'BP_SHARE' ) ) {
	define( 'BP_SHARE', 'buddypress-share' );
	define( 'BP_ACTIVITY_SHARE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'BP_ACTIVITY_SHARE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	define( 'BP_ACTIVITY_SHARE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
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

	if ( in_array( 'buddypress-activity-social-share/buddypress-share.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		deactivate_plugins( 'buddypress-activity-social-share/buddypress-share.php' );
	}
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-buddypress-share-activator.php';
	Buddypress_Share_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_buddypress_share_pro' );

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
 * @desc Adds the Settings link to the plugin activate/deactivate page
 */
function bp_activity_share_pro_plugin_actions( $links, $file ) {

	if ( class_exists( 'BuddyPress' ) && current_user_can( 'manage_options' ) ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=buddypress-share' ) ) . '">' . esc_html__( 'Settings', 'buddypress-share' ) . '</a>';
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
 */
function run_buddypress_share_pro() {

	$plugin = new Buddypress_Share();
	$plugin->run();
}

/**
 * Check plugin requirement on plugins loaded
 * this plugin requires buddypress to be installed and active
 */
add_action( 'bp_loaded', 'bpshare_pro_plugin_init' );

/**
 * Plugin init
 */
function bpshare_pro_plugin_init() {
	if ( class_exists( 'BuddyPress' ) && bp_activity_share_pro_check_config() ) {
		run_buddypress_share_pro();
	}
}

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
 *  Check if buddypress activate.
 */
function bpshare_pro_requires_buddypress() {
	// Check if in the admin area and current user has permission to manage options.
	if ( is_admin() && current_user_can( 'activate_plugins' ) && ! class_exists( 'BuddyPress' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'bpshare_pro_required_plugin_admin_notice' );

		// Safely unset 'activate' parameter to prevent activation notice.
        if ( isset( $_GET['activate'] ) ) { // phpcs:ignore
            unset( $_GET['activate'] ); // phpcs:ignore
		}
	}
}

add_action( 'admin_init', 'bpshare_pro_requires_buddypress' );

/**
 * Throw an Alert to tell the Admin why it didn't activate.
 *
 * @author wbcomdesigns
 * @since  2.2.2
 */
function bpshare_pro_required_plugin_admin_notice() {
	$bpquotes_plugin = esc_html__( 'BuddyPress Activity Share Pro', 'buddypress-share' );
	$bp_plugin       = esc_html__( 'BuddyPress', 'buddypress-share' );
	echo '<div class="error"><p>';
	printf(
	/* translators: 1: Name of the plugin 2: Name of the dependent plugin */
		esc_html__( '%1$s is ineffective now as it requires %2$s to be installed and active.', 'buddypress-share' ),
		'<strong>' . esc_html( $bpquotes_plugin ) . '</strong>',
		'<strong>' . esc_html( $bp_plugin ) . '</strong>'
	);
	echo '</p></div>';
	if ( isset( $_GET['activate'] ) ) { //phpcs:ignore
		unset( $_GET['activate'] ); //phpcs:ignore
	}
}

/**
 *  Add notice with youzify plugin.
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
 * Redirect to plugin settings page after activated
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
				wp_redirect( admin_url( 'admin.php?page=buddypress-share' ) );
				exit;
			}
		}
	}
}

add_filter( 'bp_activity_reshare_post_type', 'bp_activity_reshare_post_disable' );
function bp_activity_reshare_post_disable( $post_type ) {
	$bp_reshare_settings = get_site_option( 'bp_reshare_settings' );
	if ( isset( $bp_reshare_settings['disable_post_reshare_activity'] ) && $bp_reshare_settings['disable_post_reshare_activity'] == 1 ) {

		if ( ( $key = array_search( 'post', $post_type ) ) !== false ) {
			unset( $post_type[ $key ] );
		}
	}
	return $post_type;
}

function bp_share_pro_set_default_option() {
	// Retrieve current services and flag.
	$services = get_site_option( 'bp_share_services', array() );
	$get_flag = get_site_option( 'bp_share_flag' );

	// Correct default structure for services.
	$default_services = array(
		'facebook'  => array( 'chb_facebook' => 1 ),  // Enabled.
		'twitter'   => array( 'chb_twitter' => 1 ),   // Enabled.
		'linkedin'  => array( 'chb_linkedin' => 1 ),  // Enabled.
		'email'     => array( 'chb_email' => 1 ),     // Enabled.
		'whatsapp'  => array( 'chb_whatsapp' => 1 ),  // Enabled.
		'pinterest' => array( 'chb_pinterest' => 0 ), // Disabled.
		'Facebook'  => array( 'chb_Facebook' => 0 ),  // Disabled.
		'Twitter'   => array( 'chb_Twitter' => 0 ),   // Disabled.
		'Linkedin'  => array( 'chb_Linkedin' => 0 ),  // Disabled.
		'Pinterest' => array( 'chb_Pinterest' => 0 ), // Disabled.
		'E-mail'    => array( 'chb_E-mail' => 1 ),    // Disabled.
		'Whatsapp'  => array( 'chb_Whatsapp' => 0 ),  // Disabled.
	);

	// Merge existing services with defaults only if flag is not set.
	if ( is_array( $services ) && empty( $get_flag ) ) {
		$services = array_merge( $default_services, $services );

		// Save merged defaults.
		update_site_option( 'bp_share_services', $services );
		update_site_option( 'bp_share_flag', 1 );
	}

	// Initialize with defaults if services are empty.
	if ( empty( $services ) ) {
		update_site_option( 'bp_share_services', $default_services );
		update_site_option( 'bp_share_flag', 1 );
	}
}
add_action( 'admin_init', 'bp_share_pro_set_default_option' );

require plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://demos.wbcomdesigns.com/exporter/free-plugins/buddypress-activity-share-pro.json',
	__FILE__, // Full path to the main plugin file or functions.php.
	'buddypress-activity-share-pro'
);

/**
 * This function handles sharing an activity URL on the compose message box in BuddyPress or BuddyBoss.
 */
function bp_share_pro_share_activity_url_on_compose() {
	if ( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) ) {
		$check_bb_bp = 'buddyboss';
	} else {
		$check_bb_bp = 'buddypress';
	}

	if ( isset( $_GET['activity_url'] ) ) { // phpcs:ignore
		$activity_url = esc_url( $_GET['activity_url'] ); // phpcs:ignore
		?>
		<script>
			jQuery(document).ready(function(){
				var url = "<?php echo esc_js( $activity_url ); ?>";
				var active_bb_bp = "<?php echo esc_js( $check_bb_bp ); ?>";
				
				if( 'buddyboss' === active_bb_bp ){
					jQuery('#bp-message-content').addClass('focus-in--content');
					jQuery('#bp-message-content div #message_content').removeAttr("data-placeholder");
					jQuery('#bp-message-content div #message_content').append(url);
				} else if( 'buddypress' === active_bb_bp ){
					jQuery('#message_content').val(url);
				}
			});
		</script>
		<?php
	}
}

add_action( 'wp_footer', 'bp_share_pro_share_activity_url_on_compose' );
