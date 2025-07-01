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
		
		// Log activation for debugging
		self::log_activation();
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
			$bp_share_pro_icon_default = array(
				'Facebook' => 'Facebook',
				'Twitter'  => 'Twitter',
				'Linkedin' => 'Linkedin',
				'Whatsapp' => 'Whatsapp',
			);
			update_site_option( 'bp_share_services', $bp_share_pro_icon_default );
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
			$bpas_icon_color_settings = array(
				'icon_style' => 'circle',
			);
			update_site_option( 'bpas_icon_color_settings', $bpas_icon_color_settings );
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
			self::log_error( 'Activity meta table does not exist: ' . $activity_meta_table );
			return;
		}

		try {
			// Create index for share_count meta queries (if not exists)
			$index_name = 'idx_bp_share_count';
			$index_exists = self::index_exists( $activity_meta_table, $index_name );
			
			if ( ! $index_exists ) {
				$sql = "ALTER TABLE `{$activity_meta_table}` ADD INDEX `{$index_name}` (`meta_key`(20), `activity_id`)";
				$result = $wpdb->query( $sql );
				
				if ( false === $result ) {
					self::log_error( 'Failed to create share_count index: ' . $wpdb->last_error );
				} else {
					self::log_success( 'Created share_count index successfully' );
				}
			}

			// Create index for activity_id + meta_key combination (if not exists)
			$combined_index = 'idx_bp_activity_meta_combo';
			$combined_exists = self::index_exists( $activity_meta_table, $combined_index );
			
			if ( ! $combined_exists ) {
				$sql = "ALTER TABLE `{$activity_meta_table}` ADD INDEX `{$combined_index}` (`activity_id`, `meta_key`(50))";
				$result = $wpdb->query( $sql );
				
				if ( false === $result ) {
					self::log_error( 'Failed to create combined index: ' . $wpdb->last_error );
				} else {
					self::log_success( 'Created combined activity meta index successfully' );
				}
			}

			// Create index for post meta share_count (if not exists)
			$post_meta_table = $wpdb->postmeta;
			$post_index_name = 'idx_bp_post_share_count';
			$post_index_exists = self::index_exists( $post_meta_table, $post_index_name );
			
			if ( ! $post_index_exists ) {
				$sql = "ALTER TABLE `{$post_meta_table}` ADD INDEX `{$post_index_name}` (`meta_key`(20), `post_id`) ";
				$result = $wpdb->query( $sql );
				
				if ( false === $result ) {
					self::log_error( 'Failed to create post share_count index: ' . $wpdb->last_error );
				} else {
					self::log_success( 'Created post share_count index successfully' );
				}
			}

		} catch ( Exception $e ) {
			self::log_error( 'Exception while creating indexes: ' . $e->getMessage() );
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
		$current_version = defined( 'BP_ACTIVITY_SHARE_PLUGIN_VERSION' ) ? BP_ACTIVITY_SHARE_PLUGIN_VERSION : '1.5.1';
		$installed_version = get_site_option( 'bp_share_plugin_version' );
		
		// Track installation/upgrade
		if ( false === $installed_version ) {
			// Fresh installation
			update_site_option( 'bp_share_plugin_version', $current_version );
			update_site_option( 'bp_share_install_date', time() );
			self::log_success( 'Fresh installation detected, version: ' . $current_version );
		} elseif ( version_compare( $installed_version, $current_version, '<' ) ) {
			// Upgrade detected
			self::handle_plugin_upgrade( $installed_version, $current_version );
			update_site_option( 'bp_share_plugin_version', $current_version );
			self::log_success( 'Plugin upgraded from ' . $installed_version . ' to ' . $current_version );
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
		
		// Future upgrade tasks can be added here
		// if ( version_compare( $old_version, '2.0.0', '<' ) ) {
		//     self::upgrade_to_200();
		// }
		
		// Set upgrade flag for one-time tasks
		update_site_option( 'bp_share_needs_upgrade_tasks', true );
	}

	/**
	 * Upgrade tasks for version 1.5.0 and above.
	 *
	 * @since    1.5.1
	 * @access   private
	 */
	private static function upgrade_to_150() {
		// Migrate old settings format if needed
		$old_services = get_site_option( 'bp_share_services_old_format' );
		if ( ! empty( $old_services ) && is_array( $old_services ) ) {
			// Convert old format to new format if needed
			$new_services = array();
			foreach ( $old_services as $service ) {
				$new_services[ $service ] = $service;
			}
			update_site_option( 'bp_share_services', $new_services );
			delete_site_option( 'bp_share_services_old_format' );
		}

		// Clear any legacy caches
		wp_cache_delete( 'bp_share_legacy_cache', 'buddypress_share' );
		
		self::log_success( 'Completed upgrade to version 1.5.0' );
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

		// Schedule daily cache cleanup
		if ( ! wp_next_scheduled( 'bp_share_daily_cache_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'bp_share_daily_cache_cleanup' );
		}
	}

	/**
	 * Log successful activation.
	 *
	 * @since    1.5.1
	 * @access   private
	 */
	private static function log_activation() {
		$activation_data = array(
			'version' => defined( 'BP_ACTIVITY_SHARE_PLUGIN_VERSION' ) ? BP_ACTIVITY_SHARE_PLUGIN_VERSION : 'unknown',
			'timestamp' => current_time( 'mysql' ),
			'bp_version' => function_exists( 'bp_get_version' ) ? bp_get_version() : 'not_active',
			'wp_version' => get_bloginfo( 'version' ),
			'php_version' => PHP_VERSION,
			'multisite' => is_multisite() ? 'yes' : 'no',
		);

		self::log_success( 'Plugin activated successfully', $activation_data );
	}

	/**
	 * Log success messages for debugging.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $message Success message.
	 * @param    array  $data    Additional data to log.
	 */
	private static function log_success( $message, $data = array() ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$log_message = '[BP Activity Share Pro Activator] SUCCESS: ' . $message;
			if ( ! empty( $data ) ) {
				$log_message .= ' Data: ' . wp_json_encode( $data );
			}
			error_log( $log_message );
		}
	}

	/**
	 * Log error messages for debugging.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $message Error message.
	 * @param    array  $data    Additional error data.
	 */
	private static function log_error( $message, $data = array() ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$log_message = '[BP Activity Share Pro Activator] ERROR: ' . $message;
			if ( ! empty( $data ) ) {
				$log_message .= ' Data: ' . wp_json_encode( $data );
			}
			error_log( $log_message );
		}
	}

	/**
	 * Check system requirements before activation.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @return   bool True if requirements are met, false otherwise.
	 */
	public static function check_requirements() {
		$requirements_met = true;
		$errors = array();

		// Check PHP version
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			$errors[] = sprintf( 
				__( 'PHP version 7.4 or higher is required. You are running version %s.', 'buddypress-share' ), 
				PHP_VERSION 
			);
			$requirements_met = false;
		}

		// Check WordPress version
		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$errors[] = sprintf( 
				__( 'WordPress version 5.0 or higher is required. You are running version %s.', 'buddypress-share' ), 
				get_bloginfo( 'version' ) 
			);
			$requirements_met = false;
		}

		// Check if BuddyPress is active
		if ( ! function_exists( 'buddypress' ) ) {
			$errors[] = __( 'BuddyPress plugin must be installed and activated.', 'buddypress-share' );
			$requirements_met = false;
		}

		// Check BuddyPress version if active
		if ( function_exists( 'bp_get_version' ) ) {
			if ( version_compare( bp_get_version(), '8.0', '<' ) ) {
				$errors[] = sprintf( 
					__( 'BuddyPress version 8.0 or higher is recommended. You are running version %s.', 'buddypress-share' ), 
					bp_get_version() 
				);
				// This is a warning, not a hard requirement
			}
		}

		// Log requirements check
		if ( ! $requirements_met ) {
			self::log_error( 'System requirements not met', $errors );
		} else {
			self::log_success( 'System requirements check passed' );
		}

		return $requirements_met;
	}

	/**
	 * Get plugin activation status and information.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @return   array Plugin status information.
	 */
	public static function get_activation_status() {
		return array(
			'version' => get_site_option( 'bp_share_plugin_version', 'unknown' ),
			'install_date' => get_site_option( 'bp_share_install_date', 0 ),
			'last_activation' => get_site_option( 'bp_share_last_activation', 0 ),
			'database_version' => get_site_option( 'bp_share_db_version', '1.0' ),
			'indexes_created' => self::check_indexes_exist(),
		);
	}

	/**
	 * Check if database indexes exist.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @return   bool True if indexes exist, false otherwise.
	 */
	private static function check_indexes_exist() {
		if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'activity' ) ) {
			return false;
		}

		$bp = buddypress();
		$activity_meta_table = $bp->activity->table_name_meta;
		
		$share_count_index = self::index_exists( $activity_meta_table, 'idx_bp_share_count' );
		$combo_index = self::index_exists( $activity_meta_table, 'idx_bp_activity_meta_combo' );
		
		return $share_count_index && $combo_index;
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
			$orphaned_meta = $wpdb->query( 
				"DELETE am FROM {$activity_meta_table} am 
				 LEFT JOIN {$activity_table} a ON am.activity_id = a.id 
				 WHERE a.id IS NULL AND am.meta_key = 'share_count'"
			);

			if ( $orphaned_meta > 0 ) {
				self::log_success( "Cleaned up {$orphaned_meta} orphaned share_count records" );
			}

			// Clean up share_count meta for deleted posts
			$orphaned_post_meta = $wpdb->query(
				"DELETE pm FROM {$wpdb->postmeta} pm 
				 LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
				 WHERE p.ID IS NULL AND pm.meta_key = 'share_count'"
			);

			if ( $orphaned_post_meta > 0 ) {
				self::log_success( "Cleaned up {$orphaned_post_meta} orphaned post share_count records" );
			}

		} catch ( Exception $e ) {
			self::log_error( 'Error during cleanup: ' . $e->getMessage() );
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
		wp_clear_scheduled_hook( 'bp_share_daily_cache_cleanup' );

		// Clear caches
		wp_cache_flush();

		// Log deactivation
		self::log_success( 'Plugin deactivated and cleanup completed' );
	}

	/**
	 * Emergency rollback method for problematic upgrades.
	 *
	 * @since    1.5.1
	 * @access   public
	 */
	public static function emergency_rollback() {
		// Remove problematic indexes if they cause issues
		global $wpdb;

		if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'activity' ) ) {
			return;
		}

		$bp = buddypress();
		$activity_meta_table = $bp->activity->table_name_meta;

		try {
			// Drop our custom indexes if they exist
			$indexes_to_remove = array( 'idx_bp_share_count', 'idx_bp_activity_meta_combo' );
			
			foreach ( $indexes_to_remove as $index_name ) {
				if ( self::index_exists( $activity_meta_table, $index_name ) ) {
					$wpdb->query( "ALTER TABLE `{$activity_meta_table}` DROP INDEX `{$index_name}`" );
					self::log_success( "Removed index: {$index_name}" );
				}
			}

			// Remove post meta index
			if ( self::index_exists( $wpdb->postmeta, 'idx_bp_post_share_count' ) ) {
				$wpdb->query( "ALTER TABLE `{$wpdb->postmeta}` DROP INDEX `idx_bp_post_share_count`" );
				self::log_success( 'Removed post meta index' );
			}

		} catch ( Exception $e ) {
			self::log_error( 'Error during rollback: ' . $e->getMessage() );
		}
	}
}