<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks. Optimized for large sites with improved performance.
 * Updated to use independent menu system without wbcom wrapper.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Buddypress_Share {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Buddypress_Share_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct() {
		$this->plugin_name = 'buddypress-share';
		$this->version     = defined( 'BP_ACTIVITY_SHARE_PLUGIN_VERSION' ) ? BP_ACTIVITY_SHARE_PLUGIN_VERSION : '1.5.1';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Buddypress_Share_Loader. Orchestrates the hooks of the plugin.
	 * - Buddypress_Share_i18n. Defines internationalization functionality.
	 * - Buddypress_Share_Admin. Defines all hooks for the admin area.
	 * - Buddypress_Share_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-buddypress-share-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-buddypress-share-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-buddypress-share-admin.php';

		/**
		 * The class responsible for display admin notice for review after 7 days.
		 * FIXED: Make this optional if file doesn't exist
		 */
		$feedback_file = plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-buddypress-share-feedback.php';
		if ( file_exists( $feedback_file ) ) {
			require_once $feedback_file;
		}

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-buddypress-share-public.php';

		$this->loader = new Buddypress_Share_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Buddypress_Share_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Buddypress_Share_i18n();

		// Hook to plugins_loaded instead of init for earlier loading
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'bp_share_load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * Updated to use independent menu system and optimized with cache clearing hooks.
	 * FIXED: Removed deprecated bp_share_settings_init method call
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Buddypress_Share_Admin( $this->get_plugin_name(), $this->get_version() );

		// Core admin hooks
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		// Use standard admin_menu hook instead of bp_core_admin_hook for independent menu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'bp_share_plugin_menu' );
		
		// FIXED: Removed the deprecated bp_share_settings_init method call
		// $this->loader->add_action( 'admin_init', $plugin_admin, 'bp_share_settings_init' );
		
		$this->loader->add_action( 'admin_init', $plugin_admin, 'wbcom_hide_all_admin_notices_from_setting_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'bpas_register_setting' );

		// AJAX handlers for admin
		$this->loader->add_action( 'wp_ajax_wss_social_icons', $plugin_admin, 'wss_social_icons' );
		$this->loader->add_action( 'wp_ajax_wss_social_remove_icons', $plugin_admin, 'wss_social_remove_icons' );

		// Cache clearing hooks for performance optimization
		$this->loader->add_action( 'update_site_option_bp_share_services', $plugin_admin, 'clear_public_settings_cache' );
		$this->loader->add_action( 'update_site_option_bp_share_services_extra', $plugin_admin, 'clear_public_settings_cache' );
		$this->loader->add_action( 'update_site_option_bp_reshare_settings', $plugin_admin, 'clear_public_settings_cache' );
		$this->loader->add_action( 'update_option_bpas_icon_color_settings', $plugin_admin, 'clear_public_settings_cache' );

		// Add custom admin body classes for better styling
		$this->loader->add_filter( 'admin_body_class', $this, 'add_admin_body_classes' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * Optimized with new AJAX handlers for better performance on large sites.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$theme_support = apply_filters( 'buddypress_share_theme_support', array( 'reign-theme', 'buddyx-pro' ) );
		$theme_name    = wp_get_theme();

		$plugin_public = new Buddypress_Share_Public( $this->get_plugin_name(), $this->get_version() );
		
		// Core public hooks
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'language_attributes', $plugin_public, 'bp_share_doctype_opengraph' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'bp_share_opengraph', 999 );
		$this->loader->add_action( 'bp_init', $plugin_public, 'bp_activity_share_button_dis' );
		$this->loader->add_action( 'body_class', $plugin_public, 'add_bp_share_services_logout_body_class' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'bp_activity_share_popup_box', 999 );
		
		// Theme-specific content hooks
		if ( ! in_array( $theme_name->template, $theme_support ) ) {
			$this->loader->add_filter( 'the_content', $plugin_public, 'bp_activity_post_share_button_action', 999 );
		}

		// BuddyPress activity hooks
		$this->loader->add_action( 'bp_register_activity_actions', $plugin_public, 'bp_share_register_activity_actions' );
		$this->loader->add_action( 'bp_activity_entry_content', $plugin_public, 'bp_activity_share_entry_content' );

		// Shortcode registration
		$this->loader->add_shortcode( 'bp_activity_post_reshare', $plugin_public, 'bp_activity_post_reshare' );

		// REST API integration
		$this->loader->add_filter( 'bp_rest_activity_prepare_value', $plugin_public, 'bp_activity_post_reshare_data_embed_rest_api', 10, 3 );
		
		// AJAX handlers for public functionality
		$this->loader->add_action( 'wp_ajax_bp_activity_create_reshare_ajax', $plugin_public, 'bp_activity_create_reshare_ajax' );
		$this->loader->add_action( 'wp_ajax_bp_share_get_activity_content', $plugin_public, 'bp_share_get_activity_content' );
		
		// NEW: Optimized AJAX handler for loading groups and friends dynamically
		$this->loader->add_action( 'wp_ajax_bp_get_user_share_options', $plugin_public, 'get_user_share_options_ajax' );
	}

	/**
	 * Add custom admin body classes for better styling control.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @param    string $classes Current admin body classes.
	 * @return   string Modified admin body classes.
	 */
	public function add_admin_body_classes( $classes ) {
		$screen = get_current_screen();
		
		if ( isset( $screen->id ) ) {
			// Add class for all plugin admin pages
			$plugin_pages = array(
				'settings_page_buddypress-share'
			);
			
			if ( in_array( $screen->id, $plugin_pages, true ) ) {
				$classes .= ' bp-activity-share-admin';
			}
			
			// Add specific class for the settings page
			if ( $screen->id === 'settings_page_buddypress-share' ) {
				$classes .= ' bp-share-settings-page';
				
				// Add section-specific classes
				$current_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'general';
				$classes .= ' bp-share-section-' . $current_section;
			}
		}
		
		return $classes;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   Buddypress_Share_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get plugin information for display.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @return   array Plugin information array.
	 */
	public function get_plugin_info() {
		return array(
			'name'        => __( 'BuddyPress Activity Share Pro', 'buddypress-share' ),
			'version'     => $this->version,
			'description' => __( 'Share BuddyPress activities on social media and within your community.', 'buddypress-share' ),
			'author'      => 'Wbcom Designs',
			'author_uri'  => 'https://wbcomdesigns.com',
			'plugin_uri'  => 'https://wbcomdesigns.com/downloads/buddypress-activity-social-share/',
			'text_domain' => 'buddypress-share',
		);
	}

	/**
	 * Check plugin requirements and compatibility.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @return   array Requirements check results.
	 */
	public function check_requirements() {
		$requirements = array(
			'php_version'        => array(
				'required' => '7.4',
				'current'  => PHP_VERSION,
				'met'      => version_compare( PHP_VERSION, '7.4', '>=' ),
			),
			'wordpress_version'  => array(
				'required' => '5.0',
				'current'  => get_bloginfo( 'version' ),
				'met'      => version_compare( get_bloginfo( 'version' ), '5.0', '>=' ),
			),
			'buddypress_active'  => array(
				'required' => true,
				'current'  => class_exists( 'BuddyPress' ),
				'met'      => class_exists( 'BuddyPress' ),
			),
			'buddypress_version' => array(
				'required' => '8.0',
				'current'  => function_exists( 'bp_get_version' ) ? bp_get_version() : 'N/A',
				'met'      => function_exists( 'bp_get_version' ) ? version_compare( bp_get_version(), '8.0', '>=' ) : false,
			),
		);

		return $requirements;
	}

	/**
	 * Get plugin status information.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @return   array Plugin status information.
	 */
	public function get_plugin_status() {
		$requirements = $this->check_requirements();
		$all_met = true;
		
		foreach ( $requirements as $requirement ) {
			if ( ! $requirement['met'] ) {
				$all_met = false;
				break;
			}
		}
		
		return array(
			'requirements_met' => $all_met,
			'requirements'     => $requirements,
			'active'           => is_plugin_active( plugin_basename( __FILE__ ) ),
			'version'          => $this->version,
			'database_version' => get_option( 'bp_share_db_version', '1.0' ),
		);
	}

	/**
	 * Handle plugin upgrade routines.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @param    string $old_version Previous version.
	 * @param    string $new_version New version.
	 */
	public function handle_upgrade( $old_version, $new_version ) {
		// Clear caches during upgrade
		wp_cache_flush();
		
		// Version-specific upgrade routines
		if ( version_compare( $old_version, '1.5.0', '<' ) ) {
			$this->upgrade_to_150();
		}
		
		// Update version option
		update_option( 'bp_share_plugin_version', $new_version );
		
		// Fire upgrade hook for extensions
		do_action( 'bp_share_plugin_upgraded', $old_version, $new_version );
	}

	/**
	 * Upgrade routines for version 1.5.0.
	 *
	 * @since    1.5.1
	 * @access   private
	 */
	private function upgrade_to_150() {
		// Migrate old settings format if needed
		$old_services = get_site_option( 'bp_share_services_old' );
		if ( ! empty( $old_services ) ) {
			// Convert and migrate old settings
			$new_services = array();
			foreach ( (array) $old_services as $service ) {
				$new_services[ $service ] = $service;
			}
			update_site_option( 'bp_share_services', $new_services );
			delete_site_option( 'bp_share_services_old' );
		}
		
		// Clear legacy caches
		wp_cache_delete_group( 'bp_share_legacy' );
	}

	/**
	 * Deactivation cleanup.
	 *
	 * @since    1.5.1
	 * @access   public
	 */
	public function deactivate() {
		// Clear caches
		wp_cache_flush();
		
		// Clear scheduled hooks
		wp_clear_scheduled_hook( 'bp_share_cleanup' );
		
		// Fire deactivation hook
		do_action( 'bp_share_deactivated' );
	}

	/**
	 * Uninstall cleanup.
	 *
	 * @since    1.5.1
	 * @access   public
	 */
	public static function uninstall() {
		// Remove plugin options
		$options_to_remove = array(
			'bp_share_services',
			'bp_share_services_enable',
			'bp_share_services_logout_enable',
			'bp_share_services_extra',
			'bp_reshare_settings',
			'bpas_icon_color_settings',
			'bp_share_plugin_version',
			'bp_share_db_version',
		);
		
		foreach ( $options_to_remove as $option ) {
			delete_site_option( $option );
			delete_option( $option );
		}
		
		// Clear all caches
		wp_cache_flush();
		
		// Fire uninstall hook
		do_action( 'bp_share_uninstalled' );
	}
}