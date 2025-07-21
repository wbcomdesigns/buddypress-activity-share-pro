<?php
/**
 * Fired during plugin activation
 *
 * @link       http://wbcomdesigns.com
 * @since      1.0.0
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 * Enhanced with database optimizations and backward compatibility for existing sites.
 *
 * @since      1.0.0
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Buddypress_Share_Activator {

	/**
	 * Plugin activation handler.
	 *
	 * Sets up default options, creates database indexes for performance,
	 * and ensures backward compatibility with existing installations.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public static function activate() {
		// Set up default plugin options
		self::setup_default_options();
		
		// Create database indexes for performance (safe for existing sites)
		self::create_database_indexes();
		
		// Set up plugin version tracking
		self::setup_version_tracking();
		
		// Schedule cleanup tasks
		self::schedule_cleanup_tasks();
	}

	/**
	 * Set up default plugin options if they don't exist.
	 *
	 * This method is safe for existing installations as it only creates
	 * options that don't already exist.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private static function setup_default_options() {
		// Set default social services if not already configured
		if ( false === get_site_option( 'bp_share_services' ) ) {
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

		// Set default service state if not configured
		if ( false === get_site_option( 'bp_share_all_services_disable' ) ) {
			update_site_option( 'bp_share_all_services_disable', 'enable' );
		}

		// Enable services by default if not configured
		if ( false === get_site_option( 'bp_share_services_enable' ) ) {
			update_site_option( 'bp_share_services_enable', 1 );
		}

		// Enable logout sharing by default if not configured
		if ( false === get_site_option( 'bp_share_services_logout_enable' ) ) {
			update_site_option( 'bp_share_services_logout_enable', 1 );
		}

		// Set default icon settings if not configured
		if ( false === get_site_option( 'bpas_icon_color_settings' ) ) {
			$icon_settings = array(
				'icon_style' => 'circle',
			);
			update_site_option( 'bpas_icon_color_settings', $icon_settings );
		}

		// Set default extra options if not configured
		if ( false === get_site_option( 'bp_share_services_extra' ) ) {
			$extra_options = array(
				'bp_share_services_open' => 'on',
			);
			update_site_option( 'bp_share_services_extra', $extra_options );
		}

		// Set default reshare settings if not configured
		if ( false === get_site_option( 'bp_reshare_settings' ) ) {
			$reshare_settings = array(
				'reshare_share_activity' => 'parent',
			);
			update_site_option( 'bp_reshare_settings', $reshare_settings );
		}
	}

	/**
	 * Create database indexes for better performance on large sites.
	 *
	 * This method safely adds indexes without affecting existing data.
	 * Indexes are created only if they don't already exist.
	 *
	 * @since    1.5.1
	 * @access   private
	 */
	private static function create_database_indexes() {
		global $wpdb;

		// Only proceed if BuddyPress is active and tables exist
		if ( ! function_exists( 'buddypress' ) ) {
			return;
		}

		$bp = buddypress();
		
		// Check if activity component is active
		if ( ! bp_is_active( 'activity' ) ) {
			return;
		}

		// Get activity meta table name
		$activity_meta_table = $bp->activity->table_name_meta;
		
		// Check if table exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $activity_meta_table ) );
		
		if ( ! $table_exists ) {
			return;
		}

		try {
			// Create index for share_count meta queries (if not exists)
			$index_name = 'idx_bp_share_count';
			$index_exists = self::index_exists( $activity_meta_table, $index_name );
			
			if ( ! $index_exists ) {
				$sql = "ALTER TABLE `{$activity_meta_table}` ADD INDEX `{$index_name}` (`meta_key`(20), `activity_id`)";
				$wpdb->query( $sql );
			}

			// Create index for post meta share_count (if not exists)
			$post_meta_table = $wpdb->postmeta;
			$post_index_name = 'idx_bp_post_share_count';
			$post_index_exists = self::index_exists( $post_meta_table, $post_index_name );
			
			if ( ! $post_index_exists ) {
				$sql = "ALTER TABLE `{$post_meta_table}` ADD INDEX `{$post_index_name}` (`meta_key`(20), `post_id`) ";
				$wpdb->query( $sql );
			}

		} catch ( Exception $e ) {
			// Silently fail if indexes can't be created
		}
	}

	/**
	 * Check if a database index exists.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $table_name Table name to check.
	 * @param    string $index_name Index name to check for.
	 * @return   bool True if index exists, false otherwise.
	 */
	private static function index_exists( $table_name, $index_name ) {
		global $wpdb;

		$result = $wpdb->get_row( 
			$wpdb->prepare( 
				"SHOW INDEX FROM `{$table_name}` WHERE Key_name = %s", 
				$index_name 
			) 
		);

		return ! empty( $result );
	}

	/**
	 * Set up version tracking for future updates.
	 *
	 * @since    1.5.1
	 * @access   private
	 */
	private static function setup_version_tracking() {
		$current_version = defined( 'BP_ACTIVITY_SHARE_PLUGIN_VERSION' ) ? BP_ACTIVITY_SHARE_PLUGIN_VERSION : '1.5.2';
		$installed_version = get_site_option( 'bp_share_plugin_version' );
		
		// Track installation/upgrade
		if ( false === $installed_version ) {
			// Fresh installation
			update_site_option( 'bp_share_plugin_version', $current_version );
			update_site_option( 'bp_share_install_date', time() );
		} elseif ( version_compare( $installed_version, $current_version, '<' ) ) {
			// Upgrade detected
			self::handle_plugin_upgrade( $installed_version, $current_version );
			update_site_option( 'bp_share_plugin_version', $current_version );
		}
	}

	/**
	 * Handle plugin upgrade tasks.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $old_version Previous version.
	 * @param    string $new_version New version.
	 */
	private static function handle_plugin_upgrade( $old_version, $new_version ) {
		// Clear any existing caches that might be incompatible
		wp_cache_flush();
		
		// Version-specific upgrade tasks
		if ( version_compare( $old_version, '1.5.0', '<' ) ) {
			// Upgrade tasks for versions before 1.5.0
			self::upgrade_to_150();
		}

		if ( version_compare( $old_version, '1.5.2', '<' ) ) {
			// Upgrade tasks for versions before 1.5.2
			self::upgrade_to_152();
		}
	}

	/**
	 * Upgrade tasks for version 1.5.0 and above.
	 *
	 * @since    1.5.1
	 * @access   private
	 */
	private static function upgrade_to_150() {
		// Clear any legacy caches
		wp_cache_delete( 'bp_share_legacy_cache', 'buddypress_share' );
	}

	/**
	 * Upgrade tasks for version 1.5.2 and above.
	 *
	 * @since    1.5.2
	 * @access   private
	 */
	private static function upgrade_to_152() {
		// Clean up old feedback-related options
		delete_site_option( 'bp_social_share_activation_date' );
		delete_site_option( 'bp_social_share_no_bug' );
		
		// Clear any legacy caches
		wp_cache_delete( 'bp_share_feedback_cache', 'buddypress_share' );
	}

	/**
	 * Schedule cleanup tasks for better performance.
	 *
	 * @since    1.5.1
	 * @access   private
	 */
	private static function schedule_cleanup_tasks() {
		// Schedule weekly cleanup of orphaned share data
		if ( ! wp_next_scheduled( 'bp_share_weekly_cleanup' ) ) {
			wp_schedule_event( time(), 'weekly', 'bp_share_weekly_cleanup' );
		}
	}

	/**
	 * Clean up orphaned data (called by scheduled task).
	 *
	 * @since    1.5.1
	 * @access   public
	 */
	public static function cleanup_orphaned_data() {
		global $wpdb;

		if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'activity' ) ) {
			return;
		}

		$bp = buddypress();
		$activity_table = $bp->activity->table_name;
		$activity_meta_table = $bp->activity->table_name_meta;

		try {
			// Clean up share_count meta for deleted activities
			$wpdb->query( 
				"DELETE am FROM {$activity_meta_table} am 
				 LEFT JOIN {$activity_table} a ON am.activity_id = a.id 
				 WHERE a.id IS NULL AND am.meta_key = 'share_count'"
			);

			// Clean up share_count meta for deleted posts
			$wpdb->query(
				"DELETE pm FROM {$wpdb->postmeta} pm 
				 LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
				 WHERE p.ID IS NULL AND pm.meta_key = 'share_count'"
			);

		} catch ( Exception $e ) {
			// Log errors but don't fail
			// Cleanup failed
		}
	}

	/**
	 * Deactivation cleanup (called from deactivation hook).
	 *
	 * @since    1.5.1
	 * @access   public
	 */
	public static function deactivate() {
		// Clear scheduled tasks
		wp_clear_scheduled_hook( 'bp_share_weekly_cleanup' );

		// Clear caches
		wp_cache_flush();
	}

	/**
	 * Uninstall cleanup (called from uninstall hook).
	 *
	 * @since    1.5.2
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
			'bp_share_install_date',
			'bp_share_db_version',
			'bp_share_all_services_disable',
			// Clean up any legacy feedback options
			'bp_social_share_activation_date',
			'bp_social_share_no_bug',
		);
		
		foreach ( $options_to_remove as $option ) {
			delete_site_option( $option );
			delete_option( $option );
		}

		// Clear scheduled hooks
		wp_clear_scheduled_hook( 'bp_share_weekly_cleanup' );
		
		// Clear all caches
		wp_cache_flush();
		
		// Fire uninstall hook
		do_action( 'bp_share_uninstalled' );
	}
}