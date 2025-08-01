<?php
/**
 * Post Type Sharing Settings Manager
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
 * Settings manager for post type sharing.
 *
 * @since 2.1.0
 */
class BP_Share_Post_Type_Settings {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Settings option name.
	 *
	 * @var string
	 */
	private $option_name = 'bp_share_post_type_settings';

	/**
	 * Default services.
	 *
	 * @var array
	 */
	private $default_services = array(
		'facebook' => array(
			'name' => 'Facebook',
			'icon' => 'fab fa-facebook-f',
			'enabled_by_default' => true
		),
		'twitter' => array(
			'name' => 'Twitter/X',
			'icon' => 'fab fa-twitter',
			'enabled_by_default' => true
		),
		'linkedin' => array(
			'name' => 'LinkedIn',
			'icon' => 'fab fa-linkedin-in',
			'enabled_by_default' => false
		),
		'whatsapp' => array(
			'name' => 'WhatsApp',
			'icon' => 'fab fa-whatsapp',
			'enabled_by_default' => false
		),
		'telegram' => array(
			'name' => 'Telegram',
			'icon' => 'fab fa-telegram-plane',
			'enabled_by_default' => false
		),
		'pinterest' => array(
			'name' => 'Pinterest',
			'icon' => 'fab fa-pinterest-p',
			'enabled_by_default' => false
		),
		'reddit' => array(
			'name' => 'Reddit',
			'icon' => 'fab fa-reddit-alien',
			'enabled_by_default' => false
		),
		'wordpress' => array(
			'name' => 'WordPress',
			'icon' => 'fab fa-wordpress',
			'enabled_by_default' => false
		),
		'pocket' => array(
			'name' => 'Pocket',
			'icon' => 'fab fa-get-pocket',
			'enabled_by_default' => false
		),
		'bluesky' => array(
			'name' => 'Bluesky',
			'icon' => 'fas fa-bluesky',
			'enabled_by_default' => false
		),
		'email' => array(
			'name' => 'Email',
			'icon' => 'fas fa-envelope',
			'enabled_by_default' => false
		),
		'print' => array(
			'name' => 'Print',
			'icon' => 'fas fa-print',
			'enabled_by_default' => false
		),
		'copy' => array(
			'name' => 'Copy Link',
			'icon' => 'fas fa-link',
			'enabled_by_default' => true
		)
	);

	/**
	 * Initialize the settings manager.
	 */
	private function __construct() {
		$this->init();
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
	 * Initialize settings.
	 */
	private function init() {
		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting( $this->option_name, $this->option_name, array(
			'sanitize_callback' => array( $this, 'sanitize_settings' ),
			'default' => $this->get_default_settings()
		) );
	}

	/**
	 * Get default settings.
	 *
	 * @return array Default settings.
	 */
	private function get_default_settings() {
		return array(
			'enabled_post_types' => array(), // No post types enabled by default
			'post_type_services' => array(),
			'display_position' => 'right',
			'display_style' => 'floating',
			'mobile_behavior' => 'bottom',
			'default_services' => array( 'facebook', 'twitter', 'linkedin', 'copy' )
		);
	}

	/**
	 * Get all settings.
	 *
	 * @return array Settings.
	 */
	public function get_settings() {
		$settings = get_option( $this->option_name, $this->get_default_settings() );
		return wp_parse_args( $settings, $this->get_default_settings() );
	}

	/**
	 * Get enabled post types.
	 *
	 * @return array Enabled post types.
	 */
	public function get_enabled_post_types() {
		$settings = $this->get_settings();
		$enabled = $settings['enabled_post_types'] ?? array();
		
		// Filter out any internal post types that might have been saved
		$enabled = array_filter( $enabled, array( $this, 'is_valid_post_type' ) );
		
		return array_values( $enabled );
	}

	/**
	 * Check if post type is enabled.
	 *
	 * @param string $post_type Post type.
	 * @return bool Whether post type is enabled.
	 */
	public function is_post_type_enabled( $post_type ) {
		$enabled = $this->get_enabled_post_types();
		return in_array( $post_type, $enabled, true );
	}

	/**
	 * Get services for a specific post type.
	 *
	 * @param string $post_type Post type.
	 * @return array Enabled services.
	 */
	public function get_services_for_post_type( $post_type ) {
		$settings = $this->get_settings();
		
		// If post type has specific services configured
		if ( isset( $settings['post_type_services'][ $post_type ] ) ) {
			return $settings['post_type_services'][ $post_type ];
		}
		
		// Return default services
		return $settings['default_services'] ?? array( 'facebook', 'twitter', 'copy' );
	}

	/**
	 * Get all available services.
	 *
	 * @return array Available services.
	 */
	public function get_available_services() {
		return apply_filters( 'bp_share_post_type_available_services', $this->default_services );
	}

	/**
	 * Get service info.
	 *
	 * @param string $service Service key.
	 * @return array Service info.
	 */
	public function get_service_info( $service ) {
		$services = $this->get_available_services();
		return $services[ $service ] ?? null;
	}

	/**
	 * Save settings.
	 *
	 * @param array $settings Settings to save.
	 * @return bool Whether settings were saved.
	 */
	public function save_settings( $settings ) {
		$sanitized = $this->sanitize_settings( $settings );
		return update_option( $this->option_name, $sanitized );
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $settings Raw settings.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $settings ) {
		$clean = array();
		
		// Sanitize enabled post types
		if ( isset( $settings['enabled_post_types'] ) && is_array( $settings['enabled_post_types'] ) ) {
			$clean['enabled_post_types'] = array_map( 'sanitize_text_field', $settings['enabled_post_types'] );
		}
		
		// Sanitize post type services
		if ( isset( $settings['post_type_services'] ) && is_array( $settings['post_type_services'] ) ) {
			$clean['post_type_services'] = array();
			foreach ( $settings['post_type_services'] as $post_type => $services ) {
				$post_type = sanitize_text_field( $post_type );
				if ( is_array( $services ) ) {
					$clean['post_type_services'][ $post_type ] = array_map( 'sanitize_text_field', $services );
				}
			}
		}
		
		// Sanitize display settings
		$clean['display_position'] = isset( $settings['display_position'] ) ? 
			sanitize_text_field( $settings['display_position'] ) : 'right';
			
		$clean['display_style'] = isset( $settings['display_style'] ) ? 
			sanitize_text_field( $settings['display_style'] ) : 'floating';
			
		$clean['mobile_behavior'] = isset( $settings['mobile_behavior'] ) ? 
			sanitize_text_field( $settings['mobile_behavior'] ) : 'bottom';
		
		// Sanitize default services
		if ( isset( $settings['default_services'] ) && is_array( $settings['default_services'] ) ) {
			$clean['default_services'] = array_map( 'sanitize_text_field', $settings['default_services'] );
		}
		
		return $clean;
	}

	/**
	 * Get display position.
	 *
	 * @return string Display position (left/right).
	 */
	public function get_display_position() {
		$settings = $this->get_settings();
		return $settings['display_position'] ?? 'right';
	}

	/**
	 * Get display style.
	 *
	 * @return string Display style (floating/inline).
	 */
	public function get_display_style() {
		$settings = $this->get_settings();
		return $settings['display_style'] ?? 'floating';
	}

	/**
	 * Get mobile behavior.
	 *
	 * @return string Mobile behavior (bottom/hidden/same).
	 */
	public function get_mobile_behavior() {
		$settings = $this->get_settings();
		return $settings['mobile_behavior'] ?? 'bottom';
	}

	/**
	 * Enable post type.
	 *
	 * @param string $post_type Post type to enable.
	 * @param array  $services  Optional services to enable.
	 * @return bool Success.
	 */
	public function enable_post_type( $post_type, $services = null ) {
		$settings = $this->get_settings();
		
		// Add to enabled post types
		if ( ! in_array( $post_type, $settings['enabled_post_types'], true ) ) {
			$settings['enabled_post_types'][] = $post_type;
		}
		
		// Set services if provided, otherwise use defaults
		if ( $services !== null ) {
			$settings['post_type_services'][ $post_type ] = $services;
		}
		
		return $this->save_settings( $settings );
	}

	/**
	 * Disable post type.
	 *
	 * @param string $post_type Post type to disable.
	 * @return bool Success.
	 */
	public function disable_post_type( $post_type ) {
		$settings = $this->get_settings();
		
		// Remove from enabled post types
		$key = array_search( $post_type, $settings['enabled_post_types'], true );
		if ( $key !== false ) {
			unset( $settings['enabled_post_types'][ $key ] );
			$settings['enabled_post_types'] = array_values( $settings['enabled_post_types'] );
		}
		
		// Remove services configuration
		if ( isset( $settings['post_type_services'][ $post_type ] ) ) {
			unset( $settings['post_type_services'][ $post_type ] );
		}
		
		return $this->save_settings( $settings );
	}

	/**
	 * Check if a post type is valid for sharing.
	 *
	 * @param string $post_type Post type name.
	 * @return bool Whether the post type is valid.
	 */
	public function is_valid_post_type( $post_type ) {
		// Get post type object
		$post_type_obj = get_post_type_object( $post_type );
		
		if ( ! $post_type_obj ) {
			return false;
		}
		
		// Must be public
		if ( ! $post_type_obj->public ) {
			return false;
		}
		
		// Must have UI
		if ( ! $post_type_obj->show_ui ) {
			return false;
		}
		
		// Must be publicly queryable (with exceptions for bbPress)
		if ( ! $post_type_obj->publicly_queryable ) {
			// Exception for bbPress post types
			if ( ! in_array( $post_type, array( 'forum', 'topic', 'reply' ), true ) ) {
				return false;
			}
		}
		
		// Skip attachments
		if ( $post_type === 'attachment' ) {
			return false;
		}
		
		// Core post types that should always be allowed
		$core_types = array( 'post', 'page' );
		if ( in_array( $post_type, $core_types, true ) ) {
			return true;
		}
		
		// Whitelist of allowed post types that might fail other checks
		$whitelist = array(
			'forum', 'topic', 'reply',     // bbPress
			'product',                     // WooCommerce
			'course', 'lesson', 'quiz',    // LMS plugins
			'sfwd-courses', 'sfwd-lessons', 'sfwd-quiz', // LearnDash
			'event', 'event-recurring',    // Event plugins
			'tribe_events', 'tribe_venue', 'tribe_organizer', // The Events Calendar
			'portfolio', 'project',        // Portfolio plugins
			'testimonial', 'team',         // Common custom types
			'download',                    // Easy Digital Downloads
			'book', 'movie', 'recipe',     // Common content types
			'job_listing', 'resume',       // Job manager
			'property', 'listing',         // Real estate
			'donation', 'cause',           // Donation plugins
		);
		
		// Apply filter to allow customization of whitelist
		$whitelist = apply_filters( 'bp_share_post_type_whitelist', $whitelist );
		
		if ( in_array( $post_type, $whitelist, true ) ) {
			return true;
		}
		
		// Skip if excluded from search (but not if it's whitelisted)
		if ( $post_type_obj->exclude_from_search ) {
			return false;
		}
		
		// Check for internal post type patterns
		$internal_patterns = array(
			'elementor', 'e-floating', 'e-landing', 'vc_', 'fl-', 'et_', 'fusion_', 'brizy', 'oxygen',
			'acf', 'wpcf7_', 'mc4wp', 'shop_order', 'shop_coupon', 'wpforms',
			'ninja', 'tablepress', 'wp_block', 'wp_template', 'wp_navigation',
			'customize_', 'oembed_', '_'
		);
		
		foreach ( $internal_patterns as $pattern ) {
			if ( strpos( $post_type, $pattern ) !== false ) {
				$is_valid = false;
				// Apply filter to override internal pattern check
				$is_valid = apply_filters( 'bp_share_post_type_is_valid', $is_valid, $post_type, $post_type_obj );
				return $is_valid;
			}
		}
		
		// Final filter to allow complete control over validation
		return apply_filters( 'bp_share_post_type_is_valid', true, $post_type, $post_type_obj );
	}

	/**
	 * Get valid post types for sharing.
	 *
	 * @return array Array of post type objects.
	 */
	public function get_valid_post_types() {
		$all_post_types = get_post_types( array(), 'objects' );
		$valid_types = array();
		
		foreach ( $all_post_types as $post_type_name => $post_type_obj ) {
			if ( $this->is_valid_post_type( $post_type_name ) ) {
				$valid_types[ $post_type_name ] = $post_type_obj;
			}
		}
		
		return apply_filters( 'bp_share_valid_post_types', $valid_types );
	}
}