<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin
 */

if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * UPDATED: Modern CDN integration with Font Awesome 5.15.4 and optimized asset loading.
 * UPDATED: Added license management system integration.
 * UPDATED: Added simple asset helper functions for minification and RTL support.
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 * @since      1.0.0
 */
class Buddypress_Share_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name The name of this plugin.
	 * @param    string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Display social networks settings section.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function bp_share_social_networks_page() {
		$legacy_admin = $this;
		include BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/views/settings-networks.php';
	}

	/**
	 * Display restrictions page for content sharing controls.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function bp_share_restrictions_page() {
		$legacy_admin = $this;
		include BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/views/settings-restrictions.php';
	}

	/**
	 * Display settings page combining icon styles and visual settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function bp_share_display_settings_page() {
		$legacy_admin = $this;
		include BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/views/settings-display.php';
	}

	// License functions removed - plugin runs without restrictions

	/**
	 * Register plugin settings.
	 * 
	 * @since    1.0.0
	 * @access   public
	 */
	public function bpas_register_setting() {
		register_setting( 'bp_share_general_settings', 'bp_share_services_enable', 'absint' );
		register_setting( 'bp_share_general_settings', 'bp_share_services_logout_enable', 'absint' );
		register_setting( 'bp_share_general_settings', 'bp_share_services_extra', array( $this, 'sanitize_extra_settings' ) );
		register_setting( 'bp_share_general_settings', 'bp_share_services_serialized', 'sanitize_text_field' );
		
		register_setting( 'bp_reshare_settings', 'bp_reshare_settings', array( $this, 'sanitize_reshare_settings' ) );
		register_setting( 'bpas_icon_color_settings', 'bpas_icon_color_settings', array( $this, 'sanitize_icon_settings' ) );
		
		add_action( 'update_option_bp_share_services_enable', array( $this, 'sync_to_site_option' ), 10, 3 );
		add_action( 'update_option_bp_share_services_logout_enable', array( $this, 'sync_to_site_option' ), 10, 3 );
		add_action( 'update_option_bp_share_services_extra', array( $this, 'sync_extra_to_site_option' ), 10, 3 );
		add_action( 'update_option_bp_share_services_serialized', array( $this, 'sync_services_to_site_option' ), 10, 3 );
		add_action( 'update_option_bp_reshare_settings', array( $this, 'sync_reshare_to_site_option' ), 10, 3 );
	}

	/**
	 * AJAX handler for adding social icons.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function wss_social_icons() {
		// Verify nonce
		$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! wp_verify_nonce( $nonce, 'bp_share_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security nonce check failed.', 'buddypress-share' ) ) );
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'buddypress-share' ) ) );
		}
		
		$service_name = filter_input( INPUT_POST, 'term_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		
		if ( empty( $service_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Service name is required.', 'buddypress-share' ) ) );
		}
		
		$allowed_services = $this->get_all_available_services();
		if ( ! array_key_exists( $service_name, $allowed_services ) ) {
			wp_send_json_error( array( 
				'message' => sprintf( __( 'Invalid service name: %s', 'buddypress-share' ), $service_name ),
				'allowed_services' => array_keys( $allowed_services ),
				'received_service' => $service_name
			) );
		}
		
		$current_services = get_site_option( 'bp_share_services', array() );
		if ( ! is_array( $current_services ) ) {
			$current_services = array();
		}
		
		$current_services[ $service_name ] = $allowed_services[ $service_name ];
		$updated = update_site_option( 'bp_share_services', $current_services );
		
		if ( $updated || isset( $current_services[ $service_name ] ) ) {
			wp_send_json_success( array( 
				'message' => sprintf( __( 'Service "%s" added successfully.', 'buddypress-share' ), $service_name ),
				'service' => $service_name,
				'all_services' => $current_services
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to add service to database.', 'buddypress-share' ) ) );
		}
	}

	/**
	 * AJAX handler for removing social icons.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function wss_social_remove_icons() {
		// Verify nonce
		$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! wp_verify_nonce( $nonce, 'bp_share_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security nonce check failed.', 'buddypress-share' ) ) );
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'buddypress-share' ) ) );
		}
		
		$service_name = filter_input( INPUT_POST, 'icon_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		
		if ( empty( $service_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Service name is required.', 'buddypress-share' ) ) );
		}
		
		$current_services = get_site_option( 'bp_share_services', array() );
		if ( ! is_array( $current_services ) ) {
			$current_services = array();
		}
		
		if ( isset( $current_services[ $service_name ] ) ) {
			unset( $current_services[ $service_name ] );
			$updated = update_site_option( 'bp_share_services', $current_services );
			
			if ( $updated || ! isset( $current_services[ $service_name ] ) ) {
				wp_send_json_success( array( 
					'message' => sprintf( __( 'Service "%s" removed successfully.', 'buddypress-share' ), $service_name ),
					'service' => $service_name,
					'all_services' => $current_services
				) );
			} else {
				wp_send_json_error( array( 'message' => __( 'Failed to remove service from database.', 'buddypress-share' ) ) );
			}
		} else {
			wp_send_json_error( array( 
				'message' => sprintf( __( 'Service "%s" not found in enabled services.', 'buddypress-share' ), $service_name ),
				'current_services' => array_keys( $current_services )
			) );
		}
	}

	/**
	 * Get all available social services.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   array All available social services.
	 */
	public function get_all_available_services() {
		// Define all available services in one place
		$services = array(
			'Facebook'  => 'Facebook',
			'X'         => 'X (Twitter)',
			'LinkedIn'  => 'LinkedIn',
			'Pinterest' => 'Pinterest',
			'Reddit'    => 'Reddit',
			'WordPress' => 'WordPress',
			'Pocket'    => 'Pocket',
			'Telegram'  => 'Telegram',
			'Bluesky'   => 'Bluesky',
			'WhatsApp'  => 'WhatsApp',
			'E-mail'    => 'E-mail',
			'Copy-Link' => 'Copy Link',
		);
		
		/**
		 * Filter the available social sharing services.
		 *
		 * @since 1.5.2
		 * @param array $services Array of available services (key => label).
		 */
		return apply_filters( 'bp_share_available_services', $services );
	}
	
	/**
	 * Get default enabled services for first install.
	 *
	 * @since    1.5.3
	 * @access   private
	 * @return   array Default enabled services.
	 */
	public function get_default_services() {
		return array(
			'Facebook'  => 'Facebook',
			'X'         => 'X (Twitter)',
			'LinkedIn'  => 'LinkedIn',
			'WhatsApp'  => 'WhatsApp',
			'E-mail'    => 'E-mail',
			'Copy-Link' => 'Copy Link',
		);
	}

	/**
	 * Clear public settings cache when admin settings are updated.
	 *
	 * @since    1.5.1
	 * @access   public
	 */
	public function clear_public_settings_cache() {
		wp_cache_delete( 'bp_share_plugin_settings', 'buddypress_share' );
		delete_transient( 'bp_share_settings_cache' );
		do_action( 'bp_share_clear_public_cache' );
	}

	/**
	 * Sync methods that don't cause recursion.
	 *
	 * @since    1.5.2
	 * @access   public
	 */
	public function sync_to_site_option( $old_value, $value, $option ) {
		update_site_option( $option, $value );
	}

	/**
	 * Sync extra settings to site option.
	 *
	 * @since    1.5.2
	 * @access   public
	 * @param    mixed  $old_value The old option value.
	 * @param    mixed  $value     The new option value.
	 * @param    string $option    The option name.
	 * @return   void
	 */
	public function sync_extra_to_site_option( $old_value, $value, $option ) {
		update_site_option( 'bp_share_services_extra', $value );
	}

	/**
	 * Sync services to site option.
	 *
	 * @since    1.5.2
	 * @access   public
	 * @param    mixed  $old_value The old option value.
	 * @param    mixed  $value     The new option value.
	 * @param    string $option    The option name.
	 * @return   void
	 */
	public function sync_services_to_site_option( $old_value, $value, $option ) {
		$services = json_decode( $value, true );
		if ( ! is_array( $services ) && is_string( $value ) ) {
			// Safely attempt to unserialize without error suppression
			$services = maybe_unserialize( $value );
		}
		
		if ( is_array( $services ) ) {
			$sanitized_services = $this->sanitize_services_array( $services );
			update_site_option( 'bp_share_services', $sanitized_services );
		}
	}

	/**
	 * Sync reshare settings to site option.
	 *
	 * @since    1.5.2
	 * @access   public
	 * @param    mixed  $old_value The old option value.
	 * @param    mixed  $value     The new option value.
	 * @param    string $option    The option name.
	 * @return   void
	 */
	public function sync_reshare_to_site_option( $old_value, $value, $option ) {
		update_site_option( 'bp_reshare_settings', $value );
	}

	/**
	 * Sanitize extra settings input.
	 *
	 * @since    1.5.2
	 * @access   public
	 * @param    array $input The settings input to sanitize.
	 * @return   array Sanitized settings.
	 */
	public function sanitize_extra_settings( $input ) {
		if ( ! is_array( $input ) ) {
			$input = array();
		}
		
		/**
		 * Action before sanitizing extra settings.
		 *
		 * @since 1.5.2
		 * @param array $input The raw input data.
		 */
		do_action( 'bp_share_before_sanitize_extra_settings', $input );
		
		$sanitized = array();
		if ( isset( $input['bp_share_services_open'] ) ) {
			$sanitized['bp_share_services_open'] = sanitize_text_field( $input['bp_share_services_open'] );
		} else {
			$sanitized['bp_share_services_open'] = '';
		}
		
		/**
		 * Filter the sanitized extra settings.
		 *
		 * @since 1.5.2
		 * @param array $sanitized The sanitized settings.
		 * @param array $input     The raw input data.
		 */
		$sanitized = apply_filters( 'bp_share_sanitized_extra_settings', $sanitized, $input );
		
		/**
		 * Action after sanitizing extra settings.
		 *
		 * @since 1.5.2
		 * @param array $sanitized The sanitized settings.
		 * @param array $input     The raw input data.
		 */
		do_action( 'bp_share_after_sanitize_extra_settings', $sanitized, $input );
		
		return $sanitized;
	}

	public function sanitize_services_array( $services ) {
		if ( ! is_array( $services ) ) {
			return array();
		}
		
		$sanitized = array();
		$allowed_services = $this->get_all_available_services();
		$has_x = false;
		
		foreach ( $services as $key => $value ) {
			$sanitized_key = sanitize_text_field( $key );
			
			// Skip Twitter entries completely
			if ( $sanitized_key === 'Twitter' ) {
				// If we don't have X yet, add it instead of Twitter
				if ( ! $has_x && ! isset( $sanitized['X'] ) ) {
					$sanitized['X'] = 'X (Twitter)';
					$has_x = true;
				}
				continue;
			}
			
			if ( ! array_key_exists( $sanitized_key, $allowed_services ) ) {
				continue;
			}
			
			// Track if we've added X
			if ( $sanitized_key === 'X' ) {
				$has_x = true;
			}
			
			$sanitized_value = $allowed_services[ $sanitized_key ];
			
			if ( ! empty( $sanitized_key ) && ! empty( $sanitized_value ) ) {
				$sanitized[ $sanitized_key ] = $sanitized_value;
			}
		}
		
		return $sanitized;
	}

	/**
	 * Sanitize icon color settings.
	 *
	 * @since    1.5.2
	 * @access   public
	 * @param    array $settings The icon settings input to sanitize.
	 * @return   array Sanitized icon settings.
	 */
	public function sanitize_icon_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return array( 'icon_style' => 'circle' );
		}
		
		$sanitized = array();
		$allowed_styles = array( 'circle', 'rec', 'blackwhite', 'baricon' );
		
		$sanitized['icon_style'] = isset( $settings['icon_style'] ) && 
			in_array( $settings['icon_style'], $allowed_styles, true ) ? 
			$settings['icon_style'] : 'circle';
		
		$color_fields = array( 'bg_color', 'text_color', 'hover_color', 'border_color' );
		foreach ( $color_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$color = sanitize_hex_color( $settings[ $field ] );
				if ( $color ) {
					$sanitized[ $field ] = $color;
				}
			}
		}
		
		return $sanitized;
	}

	/**
	 * Sanitize reshare settings.
	 *
	 * @since    1.5.2
	 * @access   public
	 * @param    array $settings The reshare settings input to sanitize.
	 * @return   array Sanitized reshare settings.
	 */
	public function sanitize_reshare_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return array( 'reshare_share_activity' => 'parent' );
		}
		
		$sanitized = array();
		
		$boolean_fields = array(
			'disable_post_reshare_activity',
			'disable_my_profile_reshare_activity',
			'disable_group_reshare_activity',
		);
		
		foreach ( $boolean_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$sanitized[ $field ] = (bool) $settings[ $field ];
			}
		}
		
		$allowed_modes = array( 'parent', 'child' );
		$reshare_mode = isset( $settings['reshare_share_activity'] ) ? $settings['reshare_share_activity'] : 'parent';
		$sanitized['reshare_share_activity'] = in_array( $reshare_mode, $allowed_modes, true ) ? $reshare_mode : 'parent';
		
		return $sanitized;
	}

	/**
	 * Display FAQ section.
	 *
	 * @since    1.5.2
	 * @access   private
	 */
	public function bp_share_faq_page() {
		$legacy_admin = $this;
		include BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/views/faq.php';
	}

	/**
	 * Display post type sharing settings section.
	 *
	 * @since    2.1.0
	 * @access   private
	 */
	public function bp_share_post_types_page() {
		$legacy_admin = $this;
		include BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/views/settings-post-types.php';
	}
}