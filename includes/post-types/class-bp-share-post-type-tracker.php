<?php
/**
 * Post Type Share Tracking
 *
 * @package BuddyPress_Share_Pro
 * @subpackage Post_Types
 * @since 2.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tracker class for post type sharing.
 *
 * @since 2.1.0
 */
class BP_Share_Post_Type_Tracker {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Table name for tracking.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Initialize the tracker.
	 */
	private function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'bp_share_post_tracking';
		
		// Create table on activation
		add_action( 'bp_share_activate', array( $this, 'create_tables' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Create database tables for tracking.
	 */
	public function create_tables() {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		
		// Post tracking table
		$sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			post_id bigint(20) NOT NULL,
			post_type varchar(50) NOT NULL,
			service varchar(50) NOT NULL,
			user_id bigint(20) DEFAULT NULL,
			ip_address varchar(45) DEFAULT NULL,
			user_agent text DEFAULT NULL,
			referrer text DEFAULT NULL,
			shared_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_post_shares (post_id, service),
			KEY idx_user_shares (user_id),
			KEY idx_date_shares (shared_at)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		// Settings table
		$settings_table = $wpdb->prefix . 'bp_share_post_type_settings';
		$sql = "CREATE TABLE IF NOT EXISTS {$settings_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			post_type varchar(50) NOT NULL,
			enabled_services text,
			position varchar(20) DEFAULT 'right',
			style varchar(20) DEFAULT 'floating',
			custom_settings text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY idx_post_type (post_type)
		) $charset_collate;";
		
		dbDelta( $sql );
	}

	/**
	 * Track a share event.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $service  Service name.
	 * @param array  $metadata Additional metadata.
	 * @return int|false Insert ID or false on failure.
	 */
	public function track_share( $post_id, $service, $metadata = array() ) {
		global $wpdb;
		
		// Validate inputs
		$post_id = absint( $post_id );
		$service = sanitize_text_field( $service );
		
		if ( ! $post_id || ! $service ) {
			return false;
		}
		
		// Get post type
		$post_type = get_post_type( $post_id );
		if ( ! $post_type ) {
			return false;
		}
		
		// Prepare data
		$data = array(
			'post_id' => $post_id,
			'post_type' => $post_type,
			'service' => $service,
			'user_id' => get_current_user_id() ?: null,
			'ip_address' => $this->get_user_ip(),
			'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
			'referrer' => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '',
			'shared_at' => current_time( 'mysql' )
		);
		
		// Insert tracking record
		$result = $wpdb->insert( $this->table_name, $data );
		
		if ( $result === false ) {
			return false;
		}
		
		// Clear cache
		$this->clear_cache( $post_id );
		
		// Fire action for developers
		do_action( 'bp_share_post_shared', $post_id, $service, $data['user_id'], $metadata );
		
		return $wpdb->insert_id;
	}

	/**
	 * Get share statistics for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array Share statistics.
	 */
	public function get_post_stats( $post_id ) {
		global $wpdb;
		
		$cache_key = 'bp_share_post_stats_' . $post_id;
		$stats = wp_cache_get( $cache_key );
		
		if ( false !== $stats ) {
			return $stats;
		}
		
		// Total shares
		$total = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$this->table_name} WHERE post_id = %d",
			$post_id
		) );
		
		// Shares by service
		$by_service = $wpdb->get_results( $wpdb->prepare(
			"SELECT service, COUNT(*) as count 
			FROM {$this->table_name} 
			WHERE post_id = %d 
			GROUP BY service 
			ORDER BY count DESC",
			$post_id
		), ARRAY_A );
		
		// Recent shares
		$recent = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$this->table_name} 
			WHERE post_id = %d 
			ORDER BY shared_at DESC 
			LIMIT 10",
			$post_id
		), ARRAY_A );
		
		$stats = array(
			'total' => intval( $total ),
			'by_service' => $by_service,
			'recent' => $recent
		);
		
		wp_cache_set( $cache_key, $stats, '', 3600 ); // Cache for 1 hour
		
		return $stats;
	}

	/**
	 * Get user share statistics.
	 *
	 * @param int $user_id User ID.
	 * @return array User statistics.
	 */
	public function get_user_stats( $user_id ) {
		global $wpdb;
		
		$cache_key = 'bp_share_user_stats_' . $user_id;
		$stats = wp_cache_get( $cache_key );
		
		if ( false !== $stats ) {
			return $stats;
		}
		
		// Total shares by user
		$total = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d",
			$user_id
		) );
		
		// Favorite services
		$favorite_services = $wpdb->get_results( $wpdb->prepare(
			"SELECT service, COUNT(*) as count 
			FROM {$this->table_name} 
			WHERE user_id = %d 
			GROUP BY service 
			ORDER BY count DESC 
			LIMIT 5",
			$user_id
		), ARRAY_A );
		
		// Recent shares
		$recent_shares = $wpdb->get_results( $wpdb->prepare(
			"SELECT t.*, p.post_title 
			FROM {$this->table_name} t
			LEFT JOIN {$wpdb->posts} p ON t.post_id = p.ID
			WHERE t.user_id = %d 
			ORDER BY t.shared_at DESC 
			LIMIT 10",
			$user_id
		), ARRAY_A );
		
		$stats = array(
			'total' => intval( $total ),
			'favorite_services' => $favorite_services,
			'recent_shares' => $recent_shares
		);
		
		wp_cache_set( $cache_key, $stats, '', 3600 ); // Cache for 1 hour
		
		return $stats;
	}

	/**
	 * Get overall sharing statistics.
	 *
	 * @param array $args Query arguments.
	 * @return array Statistics.
	 */
	public function get_overall_stats( $args = array() ) {
		global $wpdb;
		
		$defaults = array(
			'date_from' => date( 'Y-m-d', strtotime( '-30 days' ) ),
			'date_to' => date( 'Y-m-d' ),
			'post_type' => '',
			'service' => ''
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		// Build WHERE clause
		$where = array( '1=1' );
		$values = array();
		
		if ( $args['date_from'] ) {
			$where[] = 'shared_at >= %s';
			$values[] = $args['date_from'] . ' 00:00:00';
		}
		
		if ( $args['date_to'] ) {
			$where[] = 'shared_at <= %s';
			$values[] = $args['date_to'] . ' 23:59:59';
		}
		
		if ( $args['post_type'] ) {
			$where[] = 'post_type = %s';
			$values[] = $args['post_type'];
		}
		
		if ( $args['service'] ) {
			$where[] = 'service = %s';
			$values[] = $args['service'];
		}
		
		$where_clause = implode( ' AND ', $where );
		
		// Get statistics
		$query = "SELECT 
			COUNT(*) as total_shares,
			COUNT(DISTINCT post_id) as unique_posts,
			COUNT(DISTINCT user_id) as unique_users,
			COUNT(DISTINCT ip_address) as unique_ips
			FROM {$this->table_name} 
			WHERE {$where_clause}";
		
		if ( ! empty( $values ) ) {
			$query = $wpdb->prepare( $query, $values );
		}
		
		$stats = $wpdb->get_row( $query, ARRAY_A );
		
		// Get top posts
		$top_posts_query = "SELECT 
			post_id, 
			COUNT(*) as share_count,
			post_type
			FROM {$this->table_name} 
			WHERE {$where_clause}
			GROUP BY post_id 
			ORDER BY share_count DESC 
			LIMIT 10";
		
		if ( ! empty( $values ) ) {
			$top_posts_query = $wpdb->prepare( $top_posts_query, $values );
		}
		
		$stats['top_posts'] = $wpdb->get_results( $top_posts_query, ARRAY_A );
		
		// Get service breakdown
		$services_query = "SELECT 
			service, 
			COUNT(*) as count 
			FROM {$this->table_name} 
			WHERE {$where_clause}
			GROUP BY service 
			ORDER BY count DESC";
		
		if ( ! empty( $values ) ) {
			$services_query = $wpdb->prepare( $services_query, $values );
		}
		
		$stats['services'] = $wpdb->get_results( $services_query, ARRAY_A );
		
		return $stats;
	}

	/**
	 * Clear cache for a post.
	 *
	 * @param int $post_id Post ID.
	 */
	private function clear_cache( $post_id ) {
		wp_cache_delete( 'bp_share_count_' . $post_id );
		wp_cache_delete( 'bp_share_post_stats_' . $post_id );
	}

	/**
	 * Get user IP address.
	 *
	 * @return string IP address.
	 */
	private function get_user_ip() {
		// Check if IP tracking is disabled for GDPR compliance
		if ( apply_filters( 'bp_share_disable_ip_tracking', false ) ) {
			return 'anonymous';
		}
		
		$ip_keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		
		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				$ips = explode( ',', $_SERVER[ $key ] );
				foreach ( $ips as $ip ) {
					$ip = trim( $ip );
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						// Optionally anonymize IP for GDPR
						if ( apply_filters( 'bp_share_anonymize_ip', false ) ) {
							// Remove last octet for IPv4
							if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
								$parts = explode( '.', $ip );
								$parts[3] = '0';
								return implode( '.', $parts );
							}
						}
						return $ip;
					}
				}
			}
		}
		
		return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
	}

	/**
	 * Clean old tracking data.
	 *
	 * @param int $days Number of days to keep.
	 * @return int Number of deleted rows.
	 */
	public function clean_old_data( $days = 90 ) {
		global $wpdb;
		
		$date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
		
		$deleted = $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$this->table_name} WHERE shared_at < %s",
			$date
		) );
		
		return $deleted;
	}

	/**
	 * Track a visit from a shared link.
	 * Follows the same approach as activity sharing.
	 *
	 * @param array $visit_data Visit tracking data.
	 * @return bool Success.
	 */
	public function track_visit( $visit_data ) {
		// Store visit data in meta or custom table as needed
		// This can be extended by analytics plugins
		
		/**
		 * Action hook for processing visit tracking.
		 *
		 * @param array $visit_data The visit tracking data.
		 */
		do_action( 'bp_share_post_visit_tracked_data', $visit_data );
		
		// Basic implementation: Store in post meta
		if ( ! empty( $visit_data['post_id'] ) ) {
			$visits = get_post_meta( $visit_data['post_id'], '_bp_share_visits', true );
			if ( ! is_array( $visits ) ) {
				$visits = array();
			}
			
			// Add visit data
			$visits[] = array(
				'service'    => $visit_data['service'],
				'timestamp'  => $visit_data['timestamp'],
				'shared_by'  => $visit_data['shared_by'],
				'visitor_ip' => $visit_data['visitor_ip'],
			);
			
			// Keep only last 100 visits to prevent bloat
			if ( count( $visits ) > 100 ) {
				$visits = array_slice( $visits, -100 );
			}
			
			update_post_meta( $visit_data['post_id'], '_bp_share_visits', $visits );
			
			return true;
		}
		
		return false;
	}
}