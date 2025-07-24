<?php
/**
 * BuddyPress Activity Share Pro - Share Tracking Foundation
 *
 * This class provides the foundation for tracking share events,
 * both internal (reshares) and external (social media shares).
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 * @since      2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Share tracking class.
 *
 * @since 2.0.0
 */
class Buddypress_Share_Tracker {

	/**
	 * The single instance of the class.
	 *
	 * @since  2.0.0
	 * @access protected
	 * @var    Buddypress_Share_Tracker
	 */
	protected static $_instance = null;

	/**
	 * Main instance.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return Buddypress_Share_Tracker Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 *
	 * @since  2.0.0
	 * @access public
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since  2.0.0
	 * @access private
	 */
	private function init_hooks() {
		// Track internal reshares
		add_action( 'bp_share_user_reshared_activity', array( $this, 'track_internal_share' ), 10, 4 );
		
		// Hook for processing incoming tracking parameters (when users visit tracked links)
		add_action( 'init', array( $this, 'process_tracking_parameters' ) );
		
		// AJAX endpoint for tracking external shares (future implementation)
		add_action( 'wp_ajax_bp_share_track_external', array( $this, 'ajax_track_external_share' ) );
		add_action( 'wp_ajax_nopriv_bp_share_track_external', array( $this, 'ajax_track_external_share' ) );
	}

	/**
	 * Track internal share (reshare) events.
	 *
	 * @since  2.0.0
	 * @access public
	 * @param  int    $user_id           The user who performed the reshare.
	 * @param  string $reshare_type      Type of reshare (profile, group, friend).
	 * @param  int    $original_activity The original activity ID that was reshared.
	 * @param  int    $new_activity_id   The newly created share activity ID.
	 */
	public function track_internal_share( $user_id, $reshare_type, $original_activity, $new_activity_id ) {
		// Store share event data
		$share_data = array(
			'user_id'          => $user_id,
			'activity_id'      => $original_activity,
			'new_activity_id'  => $new_activity_id,
			'share_type'       => 'internal',
			'destination_type' => $reshare_type,
			'timestamp'        => current_time( 'mysql' ),
			'ip_address'       => $this->get_user_ip(),
		);
		
		/**
		 * Action hook for processing internal share tracking data.
		 * This is where point systems, analytics plugins, or custom implementations
		 * can hook in to process the share data.
		 *
		 * @since 2.0.0
		 * @param array $share_data The share tracking data.
		 * @param int   $user_id    The user who shared.
		 */
		do_action( 'bp_share_internal_share_tracked', $share_data, $user_id );
		
		// Store in user meta for basic tracking (can be extended)
		$this->update_user_share_stats( $user_id, 'internal', $reshare_type );
		
		// Store in activity meta for the original activity
		$this->update_activity_share_stats( $original_activity, 'internal', $user_id );
	}

	/**
	 * Process tracking parameters when users visit tracked links.
	 *
	 * @since  2.0.0
	 * @access public
	 */
	public function process_tracking_parameters() {
		// Check if we have tracking parameters
		$activity_id = filter_input( INPUT_GET, 'bps_aid', FILTER_VALIDATE_INT );
		$service = filter_input( INPUT_GET, 'bps_service', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		
		if ( ! $activity_id || ! $service ) {
			return;
		}
		
		$service = sanitize_key( $service );
		$user_id = filter_input( INPUT_GET, 'bps_uid', FILTER_VALIDATE_INT ) ?: 0;
		$timestamp = filter_input( INPUT_GET, 'bps_time', FILTER_VALIDATE_INT ) ?: 0;
		
		// Validate activity exists
		if ( ! bp_activity_get_specific( array( 'activity_ids' => $activity_id ) ) ) {
			return;
		}
		
		// Track the visit
		$visit_data = array(
			'activity_id' => $activity_id,
			'service'     => $service,
			'shared_by'   => $user_id,
			'visitor_ip'  => $this->get_user_ip(),
			'timestamp'   => current_time( 'mysql' ),
			'referrer'    => wp_get_referer(),
		);
		
		/**
		 * Action hook for processing external share visit tracking.
		 *
		 * @since 2.0.0
		 * @param array $visit_data The visit tracking data.
		 */
		do_action( 'bp_share_external_visit_tracked', $visit_data );
		
		// Update visit count for the activity
		$this->update_activity_visit_stats( $activity_id, $service );
	}

	/**
	 * AJAX handler for tracking external shares (for future implementation).
	 *
	 * @since  2.0.0
	 * @access public
	 */
	public function ajax_track_external_share() {
		// Verify nonce
		if ( ! check_ajax_referer( 'bp-activity-share-nonce', '_ajax_nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'buddypress-share' ) ) );
		}
		
		$activity_id = filter_input( INPUT_POST, 'activity_id', FILTER_VALIDATE_INT ) ?: 0;
		$service = filter_input( INPUT_POST, 'service', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$service = $service ? sanitize_key( $service ) : '';
		$user_id = get_current_user_id();
		
		if ( ! $activity_id || ! $service ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters.', 'buddypress-share' ) ) );
		}
		
		// Track the external share
		$share_data = array(
			'user_id'     => $user_id,
			'activity_id' => $activity_id,
			'share_type'  => 'external',
			'service'     => $service,
			'timestamp'   => current_time( 'mysql' ),
			'ip_address'  => $this->get_user_ip(),
		);
		
		/**
		 * Action hook for processing external share tracking data.
		 *
		 * @since 2.0.0
		 * @param array $share_data The share tracking data.
		 */
		do_action( 'bp_share_external_share_tracked', $share_data );
		
		// Update stats
		if ( $user_id ) {
			$this->update_user_share_stats( $user_id, 'external', $service );
		}
		$this->update_activity_share_stats( $activity_id, 'external', $user_id );
		
		wp_send_json_success( array( 'message' => __( 'Share tracked successfully.', 'buddypress-share' ) ) );
	}

	/**
	 * Update user share statistics.
	 *
	 * @since  2.0.0
	 * @access private
	 * @param  int    $user_id    User ID.
	 * @param  string $share_type Share type (internal/external).
	 * @param  string $subtype    Subtype (destination or service).
	 */
	private function update_user_share_stats( $user_id, $share_type, $subtype ) {
		$stats_key = 'bp_share_user_stats';
		$stats = get_user_meta( $user_id, $stats_key, true );
		
		if ( ! is_array( $stats ) ) {
			$stats = array(
				'total_shares' => 0,
				'internal_shares' => 0,
				'external_shares' => 0,
				'last_share_date' => '',
				'share_breakdown' => array(),
			);
		}
		
		// Update counters
		$stats['total_shares']++;
		$stats[ $share_type . '_shares' ]++;
		$stats['last_share_date'] = current_time( 'mysql' );
		
		// Update breakdown
		if ( ! isset( $stats['share_breakdown'][ $subtype ] ) ) {
			$stats['share_breakdown'][ $subtype ] = 0;
		}
		$stats['share_breakdown'][ $subtype ]++;
		
		update_user_meta( $user_id, $stats_key, $stats );
		
		/**
		 * Action hook after updating user share stats.
		 *
		 * @since 2.0.0
		 * @param int   $user_id User ID.
		 * @param array $stats   Updated statistics.
		 */
		do_action( 'bp_share_user_stats_updated', $user_id, $stats );
	}

	/**
	 * Update activity share statistics.
	 *
	 * @since  2.0.0
	 * @access private
	 * @param  int    $activity_id Activity ID.
	 * @param  string $share_type  Share type (internal/external).
	 * @param  int    $user_id     User ID who shared.
	 */
	private function update_activity_share_stats( $activity_id, $share_type, $user_id ) {
		$stats_key = 'bp_share_activity_stats';
		$stats = bp_activity_get_meta( $activity_id, $stats_key, true );
		
		if ( ! is_array( $stats ) ) {
			$stats = array(
				'total_shares' => 0,
				'internal_shares' => 0,
				'external_shares' => 0,
				'unique_sharers' => array(),
				'last_share_date' => '',
			);
		}
		
		// Update counters
		$stats['total_shares']++;
		$stats[ $share_type . '_shares' ]++;
		$stats['last_share_date'] = current_time( 'mysql' );
		
		// Track unique sharers
		if ( $user_id && ! in_array( $user_id, $stats['unique_sharers'] ) ) {
			$stats['unique_sharers'][] = $user_id;
		}
		
		bp_activity_update_meta( $activity_id, $stats_key, $stats );
		
		/**
		 * Action hook after updating activity share stats.
		 *
		 * @since 2.0.0
		 * @param int   $activity_id Activity ID.
		 * @param array $stats       Updated statistics.
		 */
		do_action( 'bp_share_activity_stats_updated', $activity_id, $stats );
	}

	/**
	 * Update activity visit statistics (from tracked links).
	 *
	 * @since  2.0.0
	 * @access private
	 * @param  int    $activity_id Activity ID.
	 * @param  string $service     Service that generated the visit.
	 */
	private function update_activity_visit_stats( $activity_id, $service ) {
		$stats_key = 'bp_share_visit_stats';
		$stats = bp_activity_get_meta( $activity_id, $stats_key, true );
		
		if ( ! is_array( $stats ) ) {
			$stats = array(
				'total_visits' => 0,
				'service_visits' => array(),
				'last_visit_date' => '',
			);
		}
		
		// Update counters
		$stats['total_visits']++;
		$stats['last_visit_date'] = current_time( 'mysql' );
		
		// Update service-specific visits
		if ( ! isset( $stats['service_visits'][ $service ] ) ) {
			$stats['service_visits'][ $service ] = 0;
		}
		$stats['service_visits'][ $service ]++;
		
		bp_activity_update_meta( $activity_id, $stats_key, $stats );
		
		/**
		 * Action hook after updating activity visit stats.
		 *
		 * @since 2.0.0
		 * @param int   $activity_id Activity ID.
		 * @param array $stats       Updated statistics.
		 */
		do_action( 'bp_share_activity_visit_stats_updated', $activity_id, $stats );
	}

	/**
	 * Get user IP address.
	 *
	 * @since  2.0.0
	 * @access private
	 * @return string User IP address.
	 */
	private function get_user_ip() {
		$ip = '';
		
		// Try different methods to get IP address
		$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		
		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) && ! empty( $_SERVER[ $key ] ) ) {
				$ip = $_SERVER[ $key ];
				break;
			}
		}
		
		// Handle multiple IPs (from proxy)
		if ( strpos( $ip, ',' ) !== false ) {
			$ip = explode( ',', $ip );
			$ip = trim( $ip[0] );
		}
		
		return sanitize_text_field( $ip );
	}

	/**
	 * Get user share statistics.
	 *
	 * @since  2.0.0
	 * @access public
	 * @param  int $user_id User ID.
	 * @return array User share statistics.
	 */
	public static function get_user_stats( $user_id ) {
		return get_user_meta( $user_id, 'bp_share_user_stats', true );
	}

	/**
	 * Get activity share statistics.
	 *
	 * @since  2.0.0
	 * @access public
	 * @param  int $activity_id Activity ID.
	 * @return array Activity share statistics.
	 */
	public static function get_activity_stats( $activity_id ) {
		return bp_activity_get_meta( $activity_id, 'bp_share_activity_stats', true );
	}

	/**
	 * Get activity visit statistics.
	 *
	 * @since  2.0.0
	 * @access public
	 * @param  int $activity_id Activity ID.
	 * @return array Activity visit statistics.
	 */
	public static function get_activity_visit_stats( $activity_id ) {
		return bp_activity_get_meta( $activity_id, 'bp_share_visit_stats', true );
	}
}

// Initialize the tracker
add_action( 'bp_loaded', array( 'Buddypress_Share_Tracker', 'instance' ) );