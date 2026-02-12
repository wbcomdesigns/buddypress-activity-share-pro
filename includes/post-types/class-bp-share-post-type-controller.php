<?php
/**
 * Post Type Sharing Controller
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
 * Main controller class for post type sharing functionality.
 *
 * @since 2.1.0
 */
class BP_Share_Post_Type_Controller {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the controller.
	 */
	private function __construct() {
		$this->setup_hooks();
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
	 * Setup hooks and filters.
	 */
	private function setup_hooks() {
		// Initialize post type support
		add_action( 'init', array( $this, 'register_post_type_support' ), 20 );
		
		// Frontend rendering - floating style
		add_action( 'wp_footer', array( $this, 'maybe_render_floating_wrapper' ) );
		
		// Frontend rendering - inline style (after content)
		add_filter( 'the_content', array( $this, 'maybe_render_inline_buttons' ), 999 );
		
		// AJAX handlers
		add_action( 'wp_ajax_bp_share_post', array( $this, 'handle_ajax_share' ) );
		add_action( 'wp_ajax_nopriv_bp_share_post', array( $this, 'handle_ajax_share' ) );
		
		// Process tracking parameters when users visit shared links
		add_action( 'init', array( $this, 'process_tracking_parameters' ) );
		
		// Enqueue scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Register post type support based on settings.
	 */
	public function register_post_type_support() {
		$settings = BP_Share_Post_Type_Settings::get_instance();
		$enabled_post_types = $settings->get_enabled_post_types();
		
		foreach ( $enabled_post_types as $post_type ) {
			add_post_type_support( $post_type, 'bp-share' );
		}
	}

	/**
	 * Maybe render the floating wrapper on supported post types.
	 */
	public function maybe_render_floating_wrapper() {
		if ( ! is_singular() ) {
			return;
		}
		
		$post_type = get_post_type();
		$settings = BP_Share_Post_Type_Settings::get_instance();
		
		// Check if post type is enabled in settings
		if ( ! $settings->is_post_type_enabled( $post_type ) ) {
			return;
		}
		
		// Only render floating wrapper if style is 'floating'
		$style = $settings->get_display_style();
		if ( 'floating' !== $style ) {
			return;
		}
		
		$frontend = BP_Share_Post_Type_Frontend::get_instance();
		$frontend->render_sticky_wrapper();
	}

	/**
	 * Maybe render inline share buttons after content.
	 *
	 * @param string $content Post content.
	 * @return string Modified content.
	 */
	public function maybe_render_inline_buttons( $content ) {
		// Track which posts have already had buttons rendered to prevent duplicates
		// BuddyBoss and other systems may run the_content filter multiple times
		static $rendered_posts = array();
		$post_id = get_the_ID();
		
		// Skip if already rendered for this post (prevents DOM corruption)
		if ( isset( $rendered_posts[ $post_id ] ) ) {
			return $content;
		}
		
		// Skip in admin/backend context
		if ( is_admin() ) {
			return $content;
		}
		
		// Skip if not singular post type
		if ( ! is_singular() ) {
			return $content;
		}
		
		$post_type = get_post_type();
		$settings = BP_Share_Post_Type_Settings::get_instance();
		
		if ( ! $settings->is_post_type_enabled( $post_type ) ) {
			return $content;
		}
		
		$style = $settings->get_display_style();
		if ( 'inline' !== $style ) {
			return $content;
		}
		
		// Mark this post as rendered to prevent duplicate appends
		$rendered_posts[ $post_id ] = true;
		
		$services = $settings->get_services_for_post_type( $post_type );
		
		$frontend = BP_Share_Post_Type_Frontend::get_instance();
		ob_start();
		$frontend->render_inline_buttons( array(
			'post_id' => $post_id,
			'services' => $services,
			'show_count' => true,
			'show_labels' => true
		) );
		$buttons_html = ob_get_clean();
		
		return $content . $buttons_html;
	}

	/**
	 * Process tracking parameters when users visit tracked links.
	 * Follows the same approach as activity sharing.
	 */
	public function process_tracking_parameters() {
		// Check if we have post tracking parameters
		$post_id = filter_input( INPUT_GET, 'bps_pid', FILTER_VALIDATE_INT );
		$service = filter_input( INPUT_GET, 'bps_service', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		
		if ( ! $post_id || ! $service ) {
			return;
		}
		
		$service = sanitize_key( $service );
		$user_id = filter_input( INPUT_GET, 'bps_uid', FILTER_VALIDATE_INT ) ?: 0;
		$timestamp = filter_input( INPUT_GET, 'bps_time', FILTER_VALIDATE_INT ) ?: 0;
		$post_type = filter_input( INPUT_GET, 'bps_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ?: '';
		
		// Validate post exists
		if ( ! get_post( $post_id ) ) {
			return;
		}
		
		// Track the visit
		$visit_data = array(
			'post_id'     => $post_id,
			'post_type'   => $post_type,
			'service'     => $service,
			'shared_by'   => $user_id,
			'visitor_ip'  => $this->get_user_ip(),
			'timestamp'   => current_time( 'mysql' ),
			'referrer'    => wp_get_referer(),
		);
		
		/**
		 * Action hook for processing tracked visits from share links.
		 *
		 * @param array $visit_data The visit tracking data.
		 * @param int   $post_id    The post that was shared.
		 */
		do_action( 'bp_share_post_visit_tracked', $visit_data, $post_id );
		
		// Store visit tracking (can be extended by other plugins)
		$tracker = BP_Share_Post_Type_Tracker::get_instance();
		$tracker->track_visit( $visit_data );
	}

	/**
	 * Get user IP address.
	 *
	 * @return string User IP address.
	 */
	private function get_user_ip() {
		$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		
		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				$ips = explode( ',', $_SERVER[ $key ] );
				foreach ( $ips as $ip ) {
					$ip = trim( $ip );
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}
		
		return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
	}

	/**
	 * Handle AJAX share requests.
	 */
	public function handle_ajax_share() {
		check_ajax_referer( 'bp-share-post', 'nonce' );
		
		// Check if user can read (basic capability check)
		if ( ! current_user_can( 'read' ) && ! apply_filters( 'bp_share_allow_anonymous_sharing', true ) ) {
			wp_die( json_encode( array( 
				'success' => false, 
				'message' => __( 'You do not have permission to share.', 'buddypress-share' ) 
			) ) );
		}
		
		// Rate limiting check
		$user_id = get_current_user_id();
		$ip = $this->get_user_ip();
		$rate_limit_key = 'bp_share_rate_' . md5( $user_id . '_' . $ip );
		$share_count = get_transient( $rate_limit_key );
		
		if ( $share_count && $share_count >= apply_filters( 'bp_share_rate_limit', 20 ) ) {
			wp_die( json_encode( array( 
				'success' => false, 
				'message' => __( 'You\'ve reached the sharing limit. Please wait a moment before sharing again.', 'buddypress-share' ) 
			) ) );
		}
		
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$service = isset( $_POST['service'] ) ? sanitize_text_field( $_POST['service'] ) : '';
		
		if ( ! $post_id || ! $service ) {
			wp_die( json_encode( array( 'success' => false, 'message' => __( 'Unable to process your share request. Please try again.', 'buddypress-share' ) ) ) );
		}
		
		// Validate post exists and is public
		$post = get_post( $post_id );
		if ( ! $post || $post->post_status !== 'publish' ) {
			wp_die( json_encode( array( 
				'success' => false, 
				'message' => __( 'This content is no longer available for sharing.', 'buddypress-share' ) 
			) ) );
		}
		
		// Track the share
		$tracker = BP_Share_Post_Type_Tracker::get_instance();
		$result = $tracker->track_share( $post_id, $service );
		
		if ( ! $result ) {
			wp_die( json_encode( array( 
				'success' => false, 
				'message' => __( 'Your content was shared, but we couldn\'t track the statistics.', 'buddypress-share' ) 
			) ) );
		}
		
		// Update rate limit counter
		set_transient( $rate_limit_key, $share_count + 1, HOUR_IN_SECONDS );
		
		// Get updated count
		$count = $this->get_share_count( $post_id );
		
		wp_die( json_encode( array( 
			'success' => true, 
			'count' => $count,
			'message' => __( 'Content shared successfully!', 'buddypress-share' )
		) ) );
	}

	/**
	 * Enqueue required scripts and styles.
	 */
	public function enqueue_scripts() {
		if ( ! is_singular() ) {
			return;
		}
		
		$settings = BP_Share_Post_Type_Settings::get_instance();
		if ( ! $settings->is_post_type_enabled( get_post_type() ) ) {
			return;
		}
		
		// Enqueue Font Awesome if not already loaded
		if ( ! wp_style_is( 'font-awesome', 'enqueued' ) && ! wp_style_is( 'fontawesome', 'enqueued' ) ) {
			wp_enqueue_style(
				'bp-share-font-awesome',
				'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
				array(),
				'5.15.4'
			);
		}
		
		wp_enqueue_style( 
			'bp-share-post-type', 
			BP_ACTIVITY_SHARE_PLUGIN_URL . 'public/css/bp-share-post-type.css',
			array(),
			BP_ACTIVITY_SHARE_PLUGIN_VERSION
		);
		
		wp_enqueue_script(
			'bp-share-post-type',
			BP_ACTIVITY_SHARE_PLUGIN_URL . 'public/js/bp-share-post-type.js',
			array( 'jquery' ),
			BP_ACTIVITY_SHARE_PLUGIN_VERSION,
			true
		);
		
		wp_localize_script( 'bp-share-post-type', 'bp_share_post_vars', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'bp-share-post' ),
			'post_id' => get_the_ID(),
			'post_type' => get_post_type(),
			'share_text' => __( 'Share', 'buddypress-share' ),
			'copied_text' => __( 'Link copied!', 'buddypress-share' ),
			'error_text' => __( 'Unable to share at this time', 'buddypress-share' )
		) );
	}

	/**
	 * Get share count for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return int Share count.
	 */
	public function get_share_count( $post_id ) {
		global $wpdb;
		
		$cache_key = 'bp_share_count_' . $post_id;
		$count = wp_cache_get( $cache_key );
		
		if ( false === $count ) {
			$table_name = $wpdb->prefix . 'bp_share_post_tracking';
			
			// Check if table exists
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name ) {
				$count = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM {$table_name} WHERE post_id = %d",
					$post_id
				) );
				
				if ( $count === null ) {
					$count = 0;
				}
			} else {
				$count = 0;
			}
			
			wp_cache_set( $cache_key, $count, '', 3600 ); // Cache for 1 hour
		}
		
		return intval( $count );
	}

	/**
	 * Get share URL for a service.
	 *
	 * @param string $service Service name.
	 * @param int    $post_id Post ID.
	 * @return string Share URL.
	 */
	public static function get_share_url( $service, $post_id ) {
		$url = get_permalink( $post_id );
		$title = get_the_title( $post_id );
		$excerpt = wp_trim_words( get_the_excerpt( $post_id ), 20 );
		$site_title = get_bloginfo( 'name' );
		$site_url = home_url();
		
		// Add tracking parameters to URL (following activity sharing approach)
		$tracked_url = self::add_share_tracking_params( $url, $service, $post_id );
		
		$share_urls = array(
			'facebook' => 'https://www.facebook.com/sharer.php?u=' . urlencode( $tracked_url ),
			'twitter' => 'https://twitter.com/share?url=' . urlencode( $tracked_url ) . '&text=' . urlencode( $title ),
			'linkedin' => 'http://www.linkedin.com/shareArticle?mini=true&url=' . urlencode( $tracked_url ) . '&text=' . urlencode( $title ),
			'whatsapp' => 'https://wa.me/?text=' . urlencode( $tracked_url ),
			'telegram' => 'https://t.me/share/url?url=' . urlencode( $tracked_url ) . '&title=' . urlencode( $title ),
			'pinterest' => 'https://pinterest.com/pin/create/bookmarklet/?url=' . urlencode( $tracked_url ) . '&description=' . urlencode( $title ),
			'reddit' => 'http://reddit.com/submit?url=' . urlencode( $tracked_url ) . '&title=' . urlencode( $title ),
			'wordpress' => 'https://wordpress.com/wp-admin/press-this.php?u=' . urlencode( $tracked_url ) . '&t=' . urlencode( $title ),
			'pocket' => 'https://getpocket.com/save?url=' . urlencode( $tracked_url ) . '&title=' . urlencode( $title ),
			'bluesky' => 'https://bsky.app/intent/compose?text=' . urlencode( $title . ' ' . $tracked_url ),
			'print' => 'javascript:window.print();',
			'copy' => $tracked_url
		);
		
		// Add email with same format as activity sharing
		if ( $service === 'email' ) {
			$email_subject = 'New Content on ' . esc_html( $site_title ) . ': ' . esc_html( $title );
			$email_body = "Hi,\n\nI wanted to share this post with you from " . esc_html( $site_title ) . ":\n\n" . esc_url( $tracked_url ) . "\n\nYou can explore more content here: " . esc_url( $site_url ) . "\n\nBest regards,\nThe " . esc_html( $site_title ) . ' Team';
			$share_urls['email'] = 'mailto:?subject=' . rawurlencode( $email_subject ) . '&body=' . rawurlencode( $email_body );
		}
		
		return apply_filters( 'bp_share_post_url', $share_urls[ $service ] ?? '', $service, $post_id );
	}

	/**
	 * Add tracking parameters to share links for analytics.
	 * Follows the same approach as activity sharing.
	 *
	 * @param string $url     The URL to add tracking parameters to.
	 * @param string $service The service name for specific tracking.
	 * @param int    $post_id The post ID being shared.
	 * @return string URL with tracking parameters.
	 */
	private static function add_share_tracking_params( $url, $service, $post_id ) {
		// Get current user ID (0 if not logged in)
		$user_id = get_current_user_id();
		$post_type = get_post_type( $post_id );
		
		// Build tracking parameters (matching activity sharing approach)
		$tracking_params = array(
			'utm_source'   => 'buddypress_share',
			'utm_medium'   => 'social',
			'utm_campaign' => 'post_share',
			'bps_pid'      => $post_id,      // BuddyPress Share Post ID
			'bps_uid'      => $user_id,      // BuddyPress Share User ID
			'bps_time'     => time(),        // Timestamp for tracking
			'bps_type'     => $post_type,    // Post type for analytics
		);
		
		// Add service-specific tracking
		if ( ! empty( $service ) ) {
			$tracking_params['utm_content'] = sanitize_key( $service );
			$tracking_params['bps_service'] = sanitize_key( $service );
		}
		
		// Add parameters to URL
		$url = add_query_arg( $tracking_params, $url );
		
		/**
		 * Filter the tracking parameters.
		 *
		 * @param string $url            URL with tracking parameters.
		 * @param array  $tracking_params The tracking parameters array.
		 * @param string $service        The service being used.
		 * @param int    $post_id        The post ID.
		 */
		return apply_filters( 'bp_share_post_tracking_url', $url, $tracking_params, $service, $post_id );
	}
}