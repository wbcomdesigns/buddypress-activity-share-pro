<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/public
 */

if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for public-facing functionality.
 * Updated with modern CDN integration, Font Awesome 5.15.4, and simple asset helper functions.
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/public
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 * @since      1.0.0
 */
class Buddypress_Share_Public {

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
	 * Cached plugin settings to avoid repeated database calls.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @var      array|null    $cached_settings    Plugin settings cache.
	 */
	private $cached_settings = null;

	/**
	 * Current nesting depth while rendering reshared activities.
	 *
	 * Reshare chains (a reshare of a reshare) can otherwise re-enter
	 * bp_share_display_activity() through the bp_activity_entry_content hook
	 * and recurse without bound, producing a PHP fatal error / stack overflow.
	 * This counter hard-caps how deep nested originals are rendered.
	 *
	 * @since    2.3.0
	 * @access   private
	 * @var      int
	 */
	private static $reshare_render_depth = 0;

	/**
	 * Maximum reshare nesting levels rendered in the stream.
	 *
	 * 1 = render the immediate original beneath a reshare, but stop before
	 * rendering an original that is itself a reshare. This is what users expect
	 * and what prevents unbounded recursion on reshare-of-reshare chains.
	 *
	 * @since    2.3.0
	 * @access   private
	 * @var      int
	 */
	private static $reshare_max_depth = 1;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name The name of the plugin.
	 * @param    string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_styles() {
		// Only load on relevant pages
		if ( ! $this->should_load_assets() ) {
			return;
		}

		$plugin_url = $this->get_plugin_url();

		// Icons are now bundled as inline Lucide chrome SVGs + bundled brand
		// SVGs (see bp_share_icon() / bp_share_brand_svg()). No Font Awesome,
		// no icon-font, no CDN enqueue is needed on the frontend anymore.

		// Bootstrap CSS - bundled locally (no CDN). Load only if not conflicting.
		if ( ! $this->has_bootstrap_conflict() && ! wp_style_is( 'bootstrap', 'enqueued' ) ) {
			wp_enqueue_style(
				'bp-share-bootstrap',
				$plugin_url . 'public/vendor/bootstrap/css/bootstrap.min.css',
				array(),
				'4.6.2',
				'all'
			);
		}

		// Select2 CSS - bundled locally (no CDN).
		wp_enqueue_style(
			'bp-share-select2',
			$plugin_url . 'public/vendor/select2/css/select2.min.css',
			array(),
			'4.1.0',
			'all'
		);

		// Main plugin CSS with auto min/RTL support. The as-icons icon font was
		// retired in 2.3.0 in favour of inline Lucide / brand SVGs.
		bp_share_enqueue_style(
			$this->plugin_name,
			$plugin_url . 'public/css/buddypress-share-public', // Without .css
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_scripts() {
		// Only load on relevant pages
		if ( ! $this->should_load_assets() ) {
			return;
		}

		$plugin_url = $this->get_plugin_url();

		// jQuery UI tooltip
		wp_enqueue_script( 'jquery-ui-tooltip' );
		
		// Bootstrap JS - bundled locally (no CDN). Load only if not conflicting.
		if ( ! $this->has_bootstrap_conflict() && ! wp_script_is( 'bootstrap', 'enqueued' ) ) {
			wp_enqueue_script(
				'bp-share-bootstrap',
				$plugin_url . 'public/vendor/bootstrap/js/bootstrap.bundle.min.js',
				array( 'jquery' ),
				'4.6.2',
				true
			);
		}

		// Select2 JS - bundled locally (no CDN).
		wp_enqueue_script(
			'bp-share-select2',
			$plugin_url . 'public/vendor/select2/js/select2.min.js',
			array( 'jquery' ),
			'4.1.0',
			true
		);
		
		// Main plugin script with auto minification
		// Get platform-specific dependencies
		$script_deps = function_exists( 'bp_share_get_script_dependencies' ) ? bp_share_get_script_dependencies() : array( 'jquery' );
		$script_deps[] = 'wp-i18n'; // Add i18n support
		
		bp_share_enqueue_script(
			$this->plugin_name,
			$plugin_url . 'public/js/buddypress-share-public', // Without .js
			$script_deps,
			$this->version,
			true
		);
		
		// Set script translations
		wp_set_script_translations( $this->plugin_name, 'buddypress-share', BP_ACTIVITY_SHARE_PLUGIN_PATH . 'languages/' );

		// Localize script with necessary data
		$this->localize_script();
	}


	/**
	 * Check for Bootstrap conflicts with other plugins/themes.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @return   bool True if conflict exists, false otherwise.
	 */
	private function has_bootstrap_conflict() {
		// Known conflicting plugins/themes
		$conflicts = array(
			class_exists( 'WeDevs_Dokan' ) && function_exists( 'dokan_is_seller_dashboard' ) && dokan_is_seller_dashboard(),
			wp_style_is( 'bootstrap', 'enqueued' ),
			wp_style_is( 'bootstrap-css', 'enqueued' ),
		);

		return in_array( true, $conflicts, true );
	}

	/**
	 * Get plugin URL dynamically.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @return   string Plugin URL.
	 */
	private function get_plugin_url() {
		$plugin_folder = basename( dirname( dirname( __FILE__ ) ) );
		return plugins_url( $plugin_folder ) . '/';
	}

	/**
	 * Check whether current global post contains the activity-listing shortcode.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @return   bool True when shortcode exists in current post content.
	 */
	private function current_post_has_activity_listing_shortcode() {
		global $post;

		if ( ! ( $post instanceof WP_Post ) || empty( $post->post_content ) ) {
			return false;
		}

		return has_shortcode( $post->post_content, 'activity-listing' );
	}

	/**
	 * Check if modal should render on current page (excluding certain shortcodes).
	 *
	 * @since 2.2.3
	 * @access private
	 * @return bool True if modal should render, false otherwise.
	 */
	private function should_render_modal_on_current_page() {
		global $post;

		if ( $post instanceof WP_Post && ! empty( $post->post_content ) ) {
			$excluded_shortcodes = array( 'members-listing', 'groups-listing' );
			foreach ( $excluded_shortcodes as $shortcode ) {
				if ( has_shortcode( $post->post_content, $shortcode ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Check if assets should be loaded on current page.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @return   bool True if assets should be loaded, false otherwise.
	 */
	private function should_load_assets() {
		// Load on BP pages, single posts, or when explicitly requested
		return bp_share_is_buddypress_page() || is_single() || apply_filters( 'bp_activity_share_load_assets', false ) || $this->current_post_has_activity_listing_shortcode();
	}

	/**
	 * Localize script with settings and data.
	 *
	 * @since    1.5.2
	 * @access   private
	 */
	private function localize_script() {
		$settings = $this->get_plugin_settings();
		$reshare_share_activity = isset( $settings['reshare_settings']['reshare_share_activity'] ) ? $settings['reshare_settings']['reshare_share_activity'] : 'parent';
		$extra_options          = isset( $settings['extra_options'] ) ? $settings['extra_options'] : array();
		$popup_active           = isset( $extra_options['bp_share_services_open'] ) ? $extra_options['bp_share_services_open'] : '';

		wp_localize_script(
			$this->plugin_name,
			'bp_activity_share_vars',
			array(
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'current_user_id'        => get_current_user_id(),
				'reshare_share_activity' => $reshare_share_activity,
				'ajax_nonce'             => wp_create_nonce( 'bp-activity-share-nonce' ),
				'member_profile_url'     => function_exists('bp_loggedin_user_domain') ? bp_loggedin_user_domain() . 'messages/compose/' : '',
				// Whether the "open in popup window" behaviour is enabled (was an inline script).
				'popup_active'           => ( 'on' === $popup_active ) ? 1 : 0,
				// Translatable button labels (were hardcoded English in the JS).
				'i18n'                   => array(
					'post'    => __( 'Post', 'buddypress-share' ),
					'sharing' => __( 'Sharing…', 'buddypress-share' ),
					'close'   => __( 'Close', 'buddypress-share' ),
				),
			)
		);
	}

	/**
	 * Get plugin settings with static caching to avoid repeated database calls.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @return   array Plugin settings array.
	 */
	private function get_plugin_settings() {
		if ( null === $this->cached_settings ) {
			$services = get_site_option( 'bp_share_services', array() );
			$services_enable = get_site_option( 'bp_share_services_enable', 1 );
			
			$this->cached_settings = array(
				'services'         => $services,
				'extra_options'    => get_site_option( 'bp_share_services_extra', array() ),
				'reshare_settings' => get_site_option( 'bp_reshare_settings', array() ),
				'icon_settings'    => get_option( 'bpas_icon_color_settings', array() ),
				'services_enable'  => $services_enable,
				'logout_enable'    => get_site_option( 'bp_share_services_logout_enable', 1 ),
			);
		}
		
		return $this->cached_settings;
	}

	/**
	 * Display share button in activity stream.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_activity_share_button_dis() {
		$settings = $this->get_plugin_settings();
		$logout_enable = isset( $settings['logout_enable'] ) ? $settings['logout_enable'] : 1;

		if ( is_user_logged_in() || ( ! is_user_logged_in() && $logout_enable ) ) {
			add_action( 'bp_activity_entry_meta', array( $this, 'bp_share_inner_activity_filter' ) );
		}
	}

	/**
	 * Adds a custom body class based on the logout sharing setting.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array $classes Existing array of body classes.
	 * @return   array Modified array of body classes.
	 */
	public function add_bp_share_services_logout_body_class( $classes ) {
		if ( ! is_user_logged_in() ) {
			$settings = $this->get_plugin_settings();
			$logout_enable = isset( $settings['logout_enable'] ) ? $settings['logout_enable'] : 1;
			
			if ( $logout_enable ) {
				$classes[] = 'bpss-logout-enabled';
			}
		}

		return $classes;
	}

	/**
	 * Render the main sharing interface for activities.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_inner_activity_filter() {
		$settings      			= $this->get_plugin_settings();
		$bp_reshare_settings 	= isset( $settings['reshare_settings'] ) ? $settings['reshare_settings'] : array();

		$disable_profile_reshare = isset( $bp_reshare_settings['disable_my_profile_reshare_activity'] ) ? $bp_reshare_settings['disable_my_profile_reshare_activity'] : 0;
		$disable_group_reshare   = isset( $bp_reshare_settings['disable_group_reshare_activity'] ) ? $bp_reshare_settings['disable_group_reshare_activity'] : 0;

		// Check if reshare is disabled for profile or group pages
		if (
			( bp_is_user() && $disable_profile_reshare ) ||
			( bp_is_group() && $disable_group_reshare )
		) {
			return; // Do not show reshare button
		}
		
		// Check if BP functions are available
		if ( ! bp_share_is_bp_active() ) {
			return;
		}
		
		$activity_id = bp_share_get_activity_id();
		if ( ! $activity_id ) {
			return;
		}
		
		// Get share count
		$share_count = function_exists( 'bp_activity_get_meta' ) ? bp_activity_get_meta( $activity_id, 'share_count', true ) : '';
		$share_count = $share_count ? $share_count : '';

		global $activities_template;

		// Use cached plugin settings
		$settings = $this->get_plugin_settings();
		$social_service = isset( $settings['services'] ) ? $settings['services'] : array();
		$bp_reshare_settings = isset( $settings['reshare_settings'] ) ? $settings['reshare_settings'] : array();

		// Whether the reshare count should be displayed. Default ON when the
		// setting has never been saved, so existing behaviour is unchanged.
		$show_share_count = ! array_key_exists( 'enable_share_count', $bp_reshare_settings ) || ! empty( $bp_reshare_settings['enable_share_count'] );
		
		$activity_type  = bp_share_get_activity_type();
		
		// Check if this activity type should have share button
		if ( function_exists( 'bp_share_should_show_share_button' ) && ! bp_share_should_show_share_button( $activity_type ) ) {
			return;
		}
		
		$activity_link  = $this->get_activity_permalink( $activities_template->activity );
		$activity_title = bp_share_get_activity_title();
		$mail_subject   = isset( $activities_template->activity->action ) ? wp_strip_all_tags( $activities_template->activity->action ) : '';
		
		/**
		 * Filter the activity share data before rendering.
		 *
		 * @since 1.5.2
		 * @param array $share_data {
		 *     Array of share data.
		 *     @type string $activity_link  The activity permalink URL.
		 *     @type string $activity_title The activity title for sharing.
		 *     @type string $mail_subject   The email subject for mail sharing.
		 * }
		 * @param object $activity The current activity object.
		 */
		$share_data = apply_filters( 'bp_share_activity_data', array(
			'activity_link'  => $activity_link,
			'activity_title' => $activity_title,
			'mail_subject'   => $mail_subject,
		), $activities_template->activity );
		
		$activity_link  = $share_data['activity_link'];
		$activity_title = $share_data['activity_title'];
		$mail_subject   = $share_data['mail_subject'];
		
		if ( ! is_user_logged_in() ) {
			echo '<div class="activity-meta">';
		}

		$icon_settings = isset( $settings['icon_settings'] ) ? $settings['icon_settings'] : array();
		$style = isset( $icon_settings['icon_style'] ) ? $icon_settings['icon_style'] : 'circle';
		// Emit the admin Display-tab colors as CSS custom properties on the
		// dropdown container so the activity-stream share buttons honour them
		// (same --bp-share-btn-* contract the post-type widget already uses).
		$color_style = $this->bp_share_get_color_css_vars( $icon_settings );
		?>

		<div class="generic-button bp-activity-share-dropdown-toggle">
			<a class="button dropdown-toggle" rel="nofollow">
				<span class="bp-activity-reshare-icon">
					<?php bp_share_the_icon( 'share-2' ); ?>
				</span>
				<span class="bp-share-text"><?php esc_html_e( 'Share', 'buddypress-share' ); ?></span>
				<?php if ( $show_share_count ) : ?>
					<span id="bp-activity-reshare-count-<?php echo esc_attr( bp_get_activity_id() ); ?>" class="reshare-count bp-activity-reshare-count"><?php echo esc_html( $share_count ); ?></span>
				<?php endif; ?>
			</a>

			<div class="bp-activity-share-dropdown-menu activity-share-dropdown-menu-container <?php echo esc_attr( $activity_type . ' ' . $style ); ?>"<?php echo '' !== $color_style ? ' style="' . esc_attr( $color_style ) . '"' : ''; ?>>
				<?php // Mobile bottom-drawer dismiss affordance (visible only at <=640px). ?>
				<div class="bp-share-drawer-header">
					<span class="bp-share-drawer-handle" aria-hidden="true"></span>
					<button type="button" class="bp-share-drawer-close" aria-label="<?php esc_attr_e( 'Close', 'buddypress-share' ); ?>">
						<?php bp_share_the_icon( 'x' ); ?>
					</button>
				</div>
				<?php if ( is_user_logged_in() ) : ?>
					<?php $this->bp_share_user_services_button( $bp_reshare_settings ); ?>
				<?php endif; ?>
				
				<?php if ( isset( $settings['services_enable'] ) && $settings['services_enable'] ) : ?>
					<div class="bp-share-activity-share-to-wrapper">
						<?php
						if ( ! empty( $social_service ) ) {
							$this->bp_share_social_buttons( $activity_link, $activity_title, $mail_subject, $social_service );
						} else {
							esc_html_e( 'Please enable share services!', 'buddypress-share' );
						}
						?>
					</div>
				<?php endif; ?>
				
				<?php do_action( 'bp_share_user_services', array(), $activity_link, $activity_title ); ?>

				<?php $this->bp_share_popup_overlay(); ?>
			</div>
		</div>

		<?php
		if ( ! is_user_logged_in() ) {
			echo '</div>';
		}
	}

	/**
	 * Display logged in user share button.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    array $bp_reshare_settings Reshare settings.
	 */
	private function bp_share_user_services_button( $bp_reshare_settings ) {
		// Hide the reshare affordance entirely when the current user does not
		// meet the "who can reshare" capability gate (mirrors the AJAX guard).
		if ( ! $this->current_user_can_reshare() ) {
			return;
		}

		// Hide the reshare affordance on a member's own activity when
		// "prevent self-share" is enabled (mirrors the AJAX-side guard).
		if ( ! empty( $bp_reshare_settings['prevent_self_share'] )
			&& function_exists( 'bp_get_activity_user_id' )
			&& (int) bp_get_activity_user_id() === (int) get_current_user_id()
		) {
			return;
		}

		// Check if any reshare option is enabled
		$reshare_enabled = false;
		$reshare_types = array(
			'disable_my_profile_reshare_activity',
			'disable_group_reshare_activity',
			'disable_friends_reshare_activity'
		);

		foreach ( $reshare_types as $type ) {
			if ( ! isset( $bp_reshare_settings[ $type ] ) || ! $bp_reshare_settings[ $type ] ) {
				$reshare_enabled = true;
				break;
			}
		}

		// Render single reshare button if any option is enabled
		if ( $reshare_enabled ) {
			?>
			<div class="bp-activity-share-btn bp-activity-reshare-btn" data-reshare="all" data-title="<?php esc_attr_e( 'Reshare Activity', 'buddypress-share' ); ?>">
				<a class="button item-button bp-secondary-action bp-activity-share-button" data-toggle="modal" data-target="#activity-share-modal" data-bs-toggle="modal" data-bs-target="#activity-share-modal" data-activity-id="<?php echo esc_attr( bp_get_activity_id() ); ?>" rel="nofollow">
					<span class="bp-activity-reshare-icon">
						<?php bp_share_the_icon( 'share-2' ); ?>
					</span>
					<span class="bp-share-text bp-share-label"><?php esc_html_e( 'Reshare', 'buddypress-share' ); ?></span>
				</a>
			</div>
			<?php
		}
	}


	/**
	 * Build the CSS custom-property declaration string for the configured
	 * share-button colors (Display tab → "Colors").
	 *
	 * Mirrors the --bp-share-btn-* contract the post-type sharing widget uses
	 * (includes/post-types/class-bp-share-post-type-frontend.php) so the same
	 * admin settings drive both surfaces. Returns an empty string when no color
	 * is configured, so the theme/token defaults remain untouched in that case.
	 *
	 * @since    2.3.0
	 * @access   private
	 * @param    array $icon_settings The bpas_icon_color_settings option value.
	 * @return   string Inline style declarations (without the surrounding attribute), or ''.
	 */
	private function bp_share_get_color_css_vars( $icon_settings ) {
		if ( ! is_array( $icon_settings ) ) {
			return '';
		}

		$map = array(
			'bg_color'     => '--bp-share-btn-bg',
			'text_color'   => '--bp-share-btn-color',
			'hover_color'  => '--bp-share-btn-hover',
			'border_color' => '--bp-share-btn-border',
		);

		$style = '';
		foreach ( $map as $setting_key => $css_var ) {
			if ( ! empty( $icon_settings[ $setting_key ] ) ) {
				$color = sanitize_hex_color( $icon_settings[ $setting_key ] );
				if ( null === $color ) {
					// Allow non-hex CSS color keywords/rgb() as a fallback while
					// still rejecting anything with quotes/semicolons/braces.
					$raw = trim( (string) $icon_settings[ $setting_key ] );
					if ( '' !== $raw && ! preg_match( '/[;{}"\'<>]/', $raw ) ) {
						$color = $raw;
					}
				}
				if ( ! empty( $color ) ) {
					$style .= $css_var . ':' . $color . ';';
				}
			}
		}

		return $style;
	}

	/**
	 * Display social share service buttons.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    string $activity_link  Activity permalink.
	 * @param    string $activity_title Activity title.
	 * @param    string $mail_subject   Email subject.
	 * @param    array  $social_service Enabled social services.
	 */
	private function bp_share_social_buttons( $activity_link, $activity_title, $mail_subject, $social_service ) {
		$sharing_services = $this->get_sharing_services_config( $activity_link, $activity_title, $mail_subject );

		foreach ( $sharing_services as $service => $details ) {
			if ( ! empty( $social_service[ $service ] ) ) {
				$service_key = ( 'E-mail' === $service ) ? 'Email' : $service;
				$button_id = "bp_" . strtolower( str_replace( '-', '_', $service_key ) ) . "_share";
				
				$button_html = '<div class="bp-share-wrapper">';
				$button_html .= '<a class="button bp-share" id="' . esc_attr( $button_id ) . '" href="' . esc_url( $details['url'] ) . '" target="_blank">';
				$button_html .= bp_share_service_icon( $service );
				$button_html .= '<span class="bp-share-label">' . esc_html( $details['label'] ) . '</span>';
				$button_html .= '</a>';
				$button_html .= '</div>';
				
				/**
				 * Filter the social share button HTML.
				 *
				 * @since 1.5.2
				 * @param string $button_html The HTML for the share button.
				 * @param string $service     The service name (Facebook, Twitter, etc.).
				 * @param array  $details     The service configuration details.
				 * @param string $activity_link The activity permalink.
				 */
				echo apply_filters( 'bp_share_social_button_html', $button_html, $service, $details, $activity_link ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		// Add copy link button if enabled
		if ( ! empty( $social_service['Copy-Link'] ) ) {
			$tracked_copy_link = $this->add_share_tracking_params( $activity_link, 'copy-link' );
			echo '<div class="bp-share-wrapper bp-copy-wrapper">';
			echo '<a class="button bp-share bp-copy" href="#" data-href="' . esc_attr( $tracked_copy_link ) . '" attr-display="no-popup">';
			echo bp_share_icon( 'link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted bundled SVG asset.
			echo '<span class="bp-share-label">' . esc_html__( 'Copy Link', 'buddypress-share' ) . '</span>';
			echo '</a>';
			echo '<span class="tooltiptext tooltip-hide">' . esc_attr__( 'Link Copied!', 'buddypress-share' ) . '</span>';
			echo '</div>';
		}
	}

	/**
	 * Get sharing services configuration with Font Awesome 5 icons.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    string $activity_link  Activity link.
	 * @param    string $activity_title Activity title.
	 * @param    string $mail_subject   Mail subject.
	 * @return   array Sharing services configuration.
	 */
	private function get_sharing_services_config( $activity_link, $activity_title, $mail_subject ) {
		// Store original link for non-tracked services
		$original_link = $activity_link;
		
		// The 'icon' value is the stable service slug; the rendered glyph comes
		// from bp_share_service_icon() (bundled brand SVG or Lucide chrome icon).
		// Font Awesome class strings were removed in 2.3.0.
		$services = array(
			'Facebook' => array(
				'url'   => 'https://www.facebook.com/sharer.php?u=' . urlencode( $this->add_share_tracking_params( $activity_link, 'facebook' ) ),
				'icon'  => 'facebook',
				'label' => __( 'Facebook', 'buddypress-share' )
			),
			'X' => array(
				'url'   => 'https://twitter.com/share?url=' . urlencode( $this->add_share_tracking_params( $activity_link, 'x-twitter' ) ) . '&text=' . urlencode( $activity_title ),
				'icon'  => 'x',
				'label' => __( 'X', 'buddypress-share' )
			),
			'LinkedIn' => array(
				'url'   => 'http://www.linkedin.com/shareArticle?mini=true&url=' . urlencode( $this->add_share_tracking_params( $activity_link, 'linkedin' ) ) . '&text=' . urlencode( $activity_title ),
				'icon'  => 'linkedin',
				'label' => __( 'LinkedIn', 'buddypress-share' )
			),
			'Pinterest' => array(
				'url'   => 'https://pinterest.com/pin/create/bookmarklet/?url=' . urlencode( $this->add_share_tracking_params( $activity_link, 'pinterest' ) ) . '&description=' . urlencode( $activity_title ),
				'icon'  => 'pinterest',
				'label' => __( 'Pinterest', 'buddypress-share' )
			),
			'Reddit' => array(
				'url'   => 'http://reddit.com/submit?url=' . urlencode( $this->add_share_tracking_params( $activity_link, 'reddit' ) ) . '&title=' . urlencode( $activity_title ),
				'icon'  => 'reddit',
				'label' => __( 'Reddit', 'buddypress-share' )
			),
			'WordPress' => array(
				'url'   => 'https://wordpress.com/wp-admin/press-this.php?u=' . urlencode( $this->add_share_tracking_params( $activity_link, 'wordpress' ) ) . '&t=' . urlencode( $activity_title ),
				'icon'  => 'wordpress',
				'label' => __( 'WordPress', 'buddypress-share' )
			),
			'Pocket' => array(
				'url'   => 'https://getpocket.com/save?url=' . urlencode( $this->add_share_tracking_params( $activity_link, 'pocket' ) ) . '&title=' . urlencode( $activity_title ),
				'icon'  => 'pocket',
				'label' => __( 'Pocket', 'buddypress-share' )
			),
			'Telegram' => array(
				'url'   => 'https://t.me/share/url?url=' . urlencode( $this->add_share_tracking_params( $activity_link, 'telegram' ) ) . '&title=' . urlencode( $activity_title ),
				'icon'  => 'telegram',
				'label' => __( 'Telegram', 'buddypress-share' )
			),
			'Bluesky' => array(
				'url'   => 'https://bsky.app/intent/compose?text=' . urlencode( 'Check this out! ' . $activity_title . ' ' . $this->add_share_tracking_params( $activity_link, 'bluesky' ) ),
				'icon'  => 'bluesky',
				'label' => __( 'Bluesky', 'buddypress-share' )
			),
			'WhatsApp' => array(
				'url'   => 'https://wa.me/?text=' . urlencode( $this->add_share_tracking_params( $activity_link, 'whatsapp' ) ),
				'icon'  => 'whatsapp',
				'label' => __( 'WhatsApp', 'buddypress-share' )
			),
		);

		// Add email service if enabled
		$social_service = $this->get_plugin_settings()['services'];
		if ( isset( $social_service['E-mail'] ) && ! empty( $social_service['E-mail'] ) ) {
			$site_title = get_bloginfo( 'name' );
			$site_url = home_url();

			$email_subject = 'New Activity on ' . esc_html( $site_title ) . ': ' . esc_html( $mail_subject );
			$tracked_email_link = $this->add_share_tracking_params( $activity_link, 'email' );
			$email_body = "Hi,\n\nI wanted to share this activity with you from " . esc_html( $site_title ) . ":\n\n" . esc_url( $tracked_email_link ) . "\n\nYou can explore more activities here: " . esc_url( $site_url ) . "\n\nBest regards,\nThe " . esc_html( $site_title ) . ' Team';

			$services['E-mail'] = array(
				'url'   => 'mailto:?subject=' . rawurlencode( $email_subject ) . '&body=' . rawurlencode( $email_body ),
				'icon'  => 'email',
				'label' => __( 'E-mail', 'buddypress-share' )
			);
		}

		/**
		 * Filter the sharing services configuration.
		 *
		 * @since 1.5.2
		 * @param array  $services       Array of sharing services configuration.
		 * @param string $activity_link  The activity permalink.
		 * @param string $activity_title The activity title.
		 * @param string $mail_subject   The mail subject.
		 */
		return apply_filters( 'bp_share_services_config', $services, $activity_link, $activity_title, $mail_subject );
	}

	/**
	 * Add tracking parameters to share links for analytics.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @param    string $url     The URL to add tracking parameters to.
	 * @param    string $service Optional. The service name for specific tracking.
	 * @return   string URL with tracking parameters.
	 */
	private function add_share_tracking_params( $url, $service = '' ) {
		// UTM tracking opt-out (P1-3). When the admin disables UTM tracking the
		// share link is returned untouched — no utm_* and no bps_* params, so
		// no user id leaks into shared URLs (GDPR). Default ON preserves the
		// historical behaviour for sites that never saved the setting.
		$extra_options = get_site_option( 'bp_share_services_extra', array() );
		$utm_enabled   = ! array_key_exists( 'enable_utm_tracking', $extra_options ) || ! empty( $extra_options['enable_utm_tracking'] );
		if ( ! $utm_enabled ) {
			return $url;
		}

		// Custom campaign name (P2-7). Falls back to the historical default.
		$campaign = '';
		if ( isset( $extra_options['utm_campaign'] ) && '' !== trim( (string) $extra_options['utm_campaign'] ) ) {
			$campaign = sanitize_text_field( $extra_options['utm_campaign'] );
		}
		if ( '' === $campaign ) {
			$campaign = 'activity_share';
		}

		// Get current user ID (0 if not logged in)
		$user_id = get_current_user_id();

		// Get current activity ID from the global template
		global $activities_template;
		$activity_id = isset( $activities_template->activity->id ) ? $activities_template->activity->id : 0;

		// Build tracking parameters
		$tracking_params = array(
			'utm_source'   => 'buddypress_share',
			'utm_medium'   => 'social',
			'utm_campaign' => $campaign,
			'bps_aid'      => $activity_id,  // BuddyPress Share Activity ID
			'bps_uid'      => $user_id,      // BuddyPress Share User ID
			'bps_time'     => time(),        // Timestamp for tracking
		);
		
		// Add service-specific tracking if provided
		if ( ! empty( $service ) ) {
			$tracking_params['utm_content'] = sanitize_key( $service );
			$tracking_params['bps_service'] = sanitize_key( $service );
		}
		
		/**
		 * Filter the tracking parameters before adding to URL.
		 *
		 * @since 2.0.0
		 * @param array  $tracking_params Array of tracking parameters.
		 * @param string $url            The original URL.
		 * @param string $service        The service name.
		 * @param int    $activity_id    The activity being shared.
		 * @param int    $user_id        The user sharing the activity.
		 */
		$tracking_params = apply_filters( 'bp_share_tracking_parameters', $tracking_params, $url, $service, $activity_id, $user_id );
		
		// Add parameters to URL
		return add_query_arg( $tracking_params, $url );
	}

	/**
	 * Get activity permalink with proper fallback.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    object $activity Activity object.
	 * @return   string Activity permalink.
	 */
	private function get_activity_permalink( $activity ) {
		if (function_exists('bp_activity_get_permalink')) {
			return bp_activity_get_permalink($activity->id);
		}
		return site_url() . '/' . bp_get_members_slug() . '/' . $activity->user_nicename . '/' . bp_get_activity_slug() . '/' . $activity->id . '/';
	}

	/**
	 * Output popup overlay for theme compatibility.
	 *
	 * @since    1.5.2
	 * @access   private
	 */
	private function bp_share_popup_overlay() {
		$theme_support = apply_filters( 'buddypress_reactions_theme_support', array( 'reign-theme', 'buddyx-pro' ) );
		$theme_name = wp_get_theme();

		if ( in_array( $theme_name->template, $theme_support ) ) {
			echo '<div class="bp-share-service-popup-overlay"></div>';
		}
	}

	/**
	 * Clear cached settings when settings are updated.
	 *
	 * @since    1.5.2
	 * @access   public
	 */
	public function clear_settings_cache() {
		$this->cached_settings = null;
	}

	/**
	 * Register custom activity actions.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function bp_share_register_activity_actions() {
		$bp = buddypress();
		
		// Register activity share action
		bp_activity_set_action(
			$bp->activity->id,
			'activity_share',
			esc_html__( 'Shared an activity', 'buddypress-share' ),
			array( $this, 'bp_share_activity_format_action_activity_reshare' ),
			esc_html__( 'Activity Share', 'buddypress-share' ),
			array( 'activity', 'group', 'member', 'member_groups' )
		);

		// Register post share action
		bp_activity_set_action(
			$bp->activity->id,
			'post_share',
			esc_html__( 'Shared a post', 'buddypress-share' ),
			array( $this, 'bp_share_activity_format_action_activity_reshare' ),
			esc_html__( 'Post Activity Share', 'buddypress-share' ),
			array( 'activity', 'group', 'member', 'member_groups' )
		);

		// Register group actions if groups component is active
		if ( bp_is_active( 'groups' ) ) {
			bp_activity_set_action(
				$bp->groups->id,
				'activity_share',
				esc_html__( 'Shared an activity', 'buddypress-share' ),
				array( $this, 'bp_share_activity_format_action_group_reshare' ),
				esc_html__( 'Activity Share', 'buddypress-share' ),
				array( 'activity', 'group', 'member', 'member_groups' )
			);

			bp_activity_set_action(
				$bp->groups->id,
				'post_share',
				esc_html__( 'Shared a post', 'buddypress-share' ),
				array( $this, 'bp_share_activity_format_action_group_reshare' ),
				esc_html__( 'Post Activity Share', 'buddypress-share' ),
				array( 'activity', 'group', 'member', 'member_groups' )
			);
		}
	}

	/**
	 * Format activity share action text.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $action   Registered action.
	 * @param    object $activity Activity object.
	 * @return   string Activity action.
	 */
	public function bp_share_activity_format_action_activity_reshare( $action, $activity ) {
		$user_link = bp_core_get_userlink( $activity->user_id );
		
		return sprintf(
			/* translators: %s: user link */
			esc_html__( '%s shared an activity', 'buddypress-share' ),
			$user_link
		);
	}

	/**
	 * Format group share action text.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $action   Registered action.
	 * @param    object $activity Activity object.
	 * @return   string Activity action.
	 */
	public function bp_share_activity_format_action_group_reshare( $action, $activity ) {
		$user_link = bp_core_get_userlink( $activity->user_id );
		$group = bp_groups_get_activity_group( $activity->item_id );
		
		if ( ! $group ) {
			return $action;
		}
		
		// Handle BP version compatibility
		if ( function_exists( 'buddypress' ) && version_compare( buddypress()->version, '12.0', '>=' ) ) {
			$group_link = '<a href="' . esc_url( bp_get_group_url( $group ) ) . '">' . esc_html( $group->name ) . '</a>';
		} else {
			$group_link = '<a href="' . esc_url( bp_get_group_permalink( $group ) ) . '">' . esc_html( $group->name ) . '</a>';
		}

		return sprintf(
			/* translators: 1: user link, 2: group link */
			esc_html__( '%1$s shared an activity in the group %2$s', 'buddypress-share' ),
			$user_link,
			$group_link
		);
	}

	/**
	 * Handle AJAX request for creating activity reshare.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function bp_activity_create_reshare_ajax() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'bp-activity-share-nonce', '_ajax_nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'buddypress-share' ) ) );
		}

		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'User not logged in.', 'buddypress-share' ) ) );
		}

		// Enforce the "who can reshare" capability gate (P2-5). When an admin
		// has set a minimum capability, a user lacking it cannot create a
		// reshare. Default (empty) means any logged-in member may reshare, so
		// existing behaviour is unchanged.
		if ( ! $this->current_user_can_reshare() ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to reshare activity.', 'buddypress-share' ) ) );
		}

		// Validate and sanitize input
		$user_id = get_current_user_id();
		$activity_id = absint( $_POST['activity_id'] ?? 0 );
		$activity_type = sanitize_key( $_POST['type'] ?? '' );
		$activity_content = sanitize_textarea_field( wp_unslash( $_POST['activity_content'] ?? '' ) );
		$activity_in = absint( $_POST['activity_in'] ?? 0 );

		if ( ! $activity_id || ! $activity_type ) {
			wp_send_json_error( array( 'message' => __( 'Missing required parameters.', 'buddypress-share' ) ) );
		}

		// Validate activity type
		$allowed_types = array( 'activity_share', 'post_share' );
		if ( ! in_array( $activity_type, $allowed_types, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid activity type.', 'buddypress-share' ) ) );
		}

		// Enforce "prevent self-share": block a user from resharing their own
		// activity when the setting is enabled. Applies to activity reshares
		// only; post shares are not authored within the activity stream.
		if ( 'activity_share' === $activity_type && $this->is_self_share( $user_id, $activity_id ) ) {
			$reshare_settings = get_site_option( 'bp_reshare_settings', array() );
			if ( ! empty( $reshare_settings['prevent_self_share'] ) ) {
				wp_send_json_error( array( 'message' => __( 'You cannot reshare your own activity.', 'buddypress-share' ) ) );
			}
		}

		// Handle different share destinations
		$destination_type = 'profile'; // default
		
		if ( $activity_in > 0 ) {
			// Check if it's a group or user
			if ( isset( $_POST['activity_in_type'] ) ) {
				$destination_type = sanitize_key( $_POST['activity_in_type'] );
				
				if ( 'user' === $destination_type ) {
					// Add mention for user
					$username = $this->get_user_name_by_id( $activity_in );
					if ( $username ) {
						$activity_content = "@{$username} \r\n{$activity_content}";
					}
					$activity_in = 0; // Post to main activity stream with mention
				} elseif ( 'group' === $destination_type ) {
					// Keep activity_in as group_id for group posting
					$destination_type = 'group';
				}
			}
		}

		// Validate group permissions
		if ( $activity_in > 0 && ! $this->user_can_post_to_group( $user_id, $activity_in ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to post in this group.', 'buddypress-share' ) ) );
		}
		
		/**
		 * Filter to allow modification of reshare data before creating activity.
		 *
		 * @since 1.5.2
		 * @param array $reshare_data {
		 *     Array of reshare data.
		 *     @type int    $user_id         The user creating the reshare.
		 *     @type int    $activity_id     The original activity ID.
		 *     @type string $activity_type   The type of share (activity_share, post_share).
		 *     @type string $activity_content The reshare content/message.
		 *     @type int    $activity_in     The group ID if sharing to a group, 0 otherwise.
		 *     @type string $destination_type The destination type (profile, group).
		 * }
		 */
		$reshare_data = apply_filters( 'bp_share_before_create_reshare', array(
			'user_id'         => $user_id,
			'activity_id'     => $activity_id,
			'activity_type'   => $activity_type,
			'activity_content' => $activity_content,
			'activity_in'     => $activity_in,
			'destination_type' => $destination_type,
		) );

		// Create activity
		$new_activity_id = $this->create_share_activity( 
			$reshare_data['user_id'], 
			$reshare_data['activity_id'], 
			$reshare_data['activity_type'], 
			$reshare_data['activity_content'], 
			$reshare_data['activity_in'] 
		);

		if ( ! $new_activity_id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create activity.', 'buddypress-share' ) ) );
		}
		
		/**
		 * Action hook fired after a successful reshare.
		 *
		 * @since 1.5.2
		 * @param int   $new_activity_id The ID of the newly created share activity.
		 * @param array $reshare_data    The reshare data array.
		 */
		do_action( 'bp_share_after_create_reshare', $new_activity_id, $reshare_data );
		
		/**
		 * Specific trigger for point/reward systems after reshare.
		 * This hook is designed for integration with point systems, gamification plugins,
		 * or custom tagging systems.
		 *
		 * @since 2.0.0
		 * @param int    $user_id           The user who performed the reshare.
		 * @param string $reshare_type      Type of reshare (profile, group, friend).
		 * @param int    $original_activity The original activity ID that was reshared.
		 * @param int    $new_activity_id   The newly created share activity ID.
		 */
		do_action( 'bp_share_user_reshared_activity', 
			$reshare_data['user_id'], 
			$reshare_data['destination_type'], 
			$reshare_data['activity_id'],
			$new_activity_id
		);

		// Update share count
		$new_count = $this->update_share_count( $activity_id, $activity_type );

		wp_send_json_success( array( 
			'share_count' => $new_count,
			'activity_id' => $new_activity_id,
			'message'     => __( 'Activity shared successfully.', 'buddypress-share' )
		) );
	}

	/**
	 * Get username by user ID with version compatibility.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    int $user_id User ID.
	 * @return   string|false Username or false on failure.
	 */
	private function get_user_name_by_id( $user_id ) {
		if ( function_exists( 'bp_members_get_user_slug' ) ) {
			return bp_members_get_user_slug( $user_id );
		}
		return bp_core_get_username( $user_id );
	}

	/**
	 * Check if user can post to group.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    int $user_id  User ID.
	 * @param    int $group_id Group ID.
	 * @return   bool True if user can post, false otherwise.
	 */
	private function user_can_post_to_group( $user_id, $group_id ) {
		return bp_is_active( 'groups' ) && groups_is_user_member( $user_id, $group_id );
	}

	/**
	 * Determine whether the given user is the author of the given activity.
	 *
	 * Used to enforce the "prevent self-share" setting and to hide the reshare
	 * affordance on a member's own activity.
	 *
	 * @since    2.3.0
	 * @access   private
	 * @param    int $user_id     The acting user ID.
	 * @param    int $activity_id The original activity ID being reshared.
	 * @return   bool True when the user authored the activity.
	 */
	private function is_self_share( $user_id, $activity_id ) {
		if ( ! $user_id || ! $activity_id || ! function_exists( 'bp_activity_get_specific' ) ) {
			return false;
		}

		$activity = bp_activity_get_specific( array( 'activity_ids' => array( $activity_id ) ) );
		if ( empty( $activity['activities'][0] ) ) {
			return false;
		}

		return (int) $activity['activities'][0]->user_id === (int) $user_id;
	}

	/**
	 * Determine whether the current user is permitted to reshare.
	 *
	 * Reads the "minimum capability to reshare" admin setting
	 * (`bp_reshare_settings['min_reshare_capability']`). An empty value means
	 * any logged-in member may reshare (the historical default), so existing
	 * sites are unaffected. When a capability is set, the user must have it.
	 *
	 * @since    2.3.0
	 * @access   private
	 * @return   bool True when the current user may reshare.
	 */
	private function current_user_can_reshare() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$reshare_settings = get_site_option( 'bp_reshare_settings', array() );
		$min_cap          = isset( $reshare_settings['min_reshare_capability'] ) ? (string) $reshare_settings['min_reshare_capability'] : '';

		// No restriction configured: any logged-in member may reshare.
		if ( '' === $min_cap ) {
			$can = true;
		} else {
			$can = current_user_can( $min_cap );
		}

		/**
		 * Filter whether the current user may reshare activity.
		 *
		 * @since 2.3.0
		 * @param bool   $can     Whether the user may reshare.
		 * @param string $min_cap The configured minimum capability ('' = none).
		 */
		return (bool) apply_filters( 'bp_share_user_can_reshare', $can, $min_cap );
	}

	/**
	 * Create share activity.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    int    $user_id         User ID.
	 * @param    int    $activity_id     Activity/Post ID being shared.
	 * @param    string $activity_type   Type of activity.
	 * @param    string $activity_content Share content.
	 * @param    int    $activity_in     Group ID if sharing to group.
	 * @return   int|false New activity ID or false on failure.
	 */
	private function create_share_activity( $user_id, $activity_id, $activity_type, $activity_content, $activity_in ) {
		// Prepare activity arguments
		$activity_args = array(
			'user_id'           => $user_id,
			'component'         => ( $activity_in > 0 ) ? 'groups' : 'activity',
			'type'              => $activity_type,
			'content'           => $activity_content,
			'secondary_item_id' => $activity_id,
			'item_id'           => $activity_in,
			'hide_sitewide'     => false,
		);
		
		// For group activities, ensure proper privacy
		if ( $activity_in > 0 && bp_is_active( 'groups' ) ) {
			$group = groups_get_group( $activity_in );
			if ( $group && 'public' !== $group->status ) {
				$activity_args['hide_sitewide'] = true;
			}
		}

		// Respect privacy: when enabled, the reshare must never be more visible
		// than the activity it reshares. If the original activity is hidden from
		// the sitewide stream, the reshare inherits that visibility so private
		// content cannot be surfaced to a public profile. This only ever
		// tightens visibility — it never relaxes the group rule above.
		if ( 'activity_share' === $activity_type ) {
			$reshare_settings = get_site_option( 'bp_reshare_settings', array() );
			$respect_privacy  = ! array_key_exists( 'respect_privacy', $reshare_settings ) || ! empty( $reshare_settings['respect_privacy'] );

			if ( $respect_privacy && function_exists( 'bp_activity_get_specific' ) ) {
				$original = bp_activity_get_specific( array( 'activity_ids' => array( $activity_id ) ) );
				if ( ! empty( $original['activities'][0] ) && ! empty( $original['activities'][0]->hide_sitewide ) ) {
					$activity_args['hide_sitewide'] = true;
				}
			}
		}

		// Add the activity
		$new_activity_id = bp_activity_add( $activity_args );
		
		// Store original activity reference as meta
		if ( $new_activity_id && 'activity_share' === $activity_type ) {
			bp_activity_update_meta( $new_activity_id, 'shared_activity_id', $activity_id );
		}
		
		return $new_activity_id;
	}

	/**
	 * Update share count for activity or post.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    int    $activity_id   Activity/Post ID.
	 * @param    string $activity_type Activity type.
	 * @return   int New share count.
	 */
	private function update_share_count( $activity_id, $activity_type ) {
		$meta_key = 'share_count';
		
		if ( 'post_share' === $activity_type ) {
			$current_count = (int) get_post_meta( $activity_id, $meta_key, true );
			$new_count = $current_count + 1;
			update_post_meta( $activity_id, $meta_key, $new_count );
		} else {
			$current_count = (int) bp_activity_get_meta( $activity_id, $meta_key, true );
			$new_count = $current_count + 1;
			bp_activity_update_meta( $activity_id, $meta_key, $new_count );
		}

		return $new_count;
	}

	/**
	 * Load activity content via AJAX.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_get_activity_content() {
		check_ajax_referer( 'bp-activity-share-nonce', '_ajax_nonce' );

		$activity_id = ! empty( $_POST['activity_id'] ) ? sanitize_text_field( wp_unslash( $_POST['activity_id'] ) ) : 0;
		
		if ( ! $activity_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid activity ID.', 'buddypress-share' ) ) );
		}

		ob_start();
		if ( bp_has_activities( 'include=' . $activity_id ) ) {
			while ( bp_activities() ) {
				bp_the_activity();
				bp_get_template_part( 'activity/entry' );
			}
		}
		$result = array( 'contents' => ob_get_clean() );
		wp_send_json_success( $result );
	}
	
	/**
	 * AJAX handler for loading groups and friends dynamically.
	 *
	 * @since    1.5.2
	 * @access   public
	 */
	public function get_user_share_options_ajax() {
		check_ajax_referer( 'bp-activity-share-nonce', '_ajax_nonce' );
		
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'User not logged in.', 'buddypress-share' ) ) );
		}
		
		$user_id = get_current_user_id();
		$data = array();
		
		// Load groups (limit to 50 for performance)
		if ( bp_is_active( 'groups' ) ) {
			$groups = groups_get_groups( array( 
				'user_id'          => $user_id,
				'per_page'         => 50,
				'populate_extras'  => false
			) );
			$data['groups'] = isset( $groups['groups'] ) ? $groups['groups'] : array();
		}
		
		// Load friends (limit to 50 for performance)
		if ( bp_is_active( 'friends' ) && function_exists( 'friends_get_friend_user_ids' ) ) {
			$friends_ids = friends_get_friend_user_ids( $user_id );
			$friends_data = array();
			
			$limited_friends = array_slice( $friends_ids, 0, 50 );
			foreach ( $limited_friends as $friend_id ) {
				$user_data = get_userdata( $friend_id );
				if ( $user_data ) {
					$friends_data[] = array(
						'id'           => $friend_id,
						'display_name' => $user_data->display_name
					);
				}
			}
			$data['friends'] = $friends_data;
		}
		
		wp_send_json_success( $data );
	}

	/**
	 * Add OpenGraph attributes to HTML element.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $output Current HTML attributes.
	 * @return   string Modified HTML attributes with OpenGraph namespaces.
	 */
	public function bp_share_doctype_opengraph( $output ) {
		return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
	}

	/**
	 * Add OpenGraph meta tags for better social media sharing.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_share_opengraph() {
		global $bp;
		
		if ( ! ( bp_is_active( 'activity' ) && bp_is_current_component( 'activity' ) && ! empty( $bp->current_action ) && is_numeric( $bp->current_action ) && bp_is_single_activity() ) ) {
			return;
		}

		$activity_obj = new BP_Activity_Activity( $bp->current_action );
		if ( empty( $activity_obj->id ) ) {
			return;
		}

		$activity_permalink = bp_activity_get_permalink( $bp->current_action );
		$og_data = $this->prepare_opengraph_data( $activity_obj );

		$this->bp_share_opengraph_meta_tags( $activity_permalink, $og_data );
	}

	/**
	 * Prepare OpenGraph data from activity.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    object $activity_obj Activity object.
	 * @return   array OpenGraph data.
	 */
	private function prepare_opengraph_data( $activity_obj ) {
		$data = array(
			'title'       => '',
			'description' => '',
			'image'       => '',
		);

		// Prepare title
		$content = ! empty( $activity_obj->action ) ? $activity_obj->action : $activity_obj->content;
		$content = explode( '<span', $content );
		$title = wp_strip_all_tags( ent2ncr( trim( convert_chars( $content[0] ?? '' ) ) ) );
		
		if ( ':' === substr( $title, -1 ) ) {
			$title = substr( $title, 0, -1 );
		}
		$data['title'] = $title;

		// Prepare description
		$activity_content = preg_replace( '#<ul class="rtmedia-list(.*?)</ul>#', ' ', $activity_obj->content );
		$data['description'] = wp_strip_all_tags( stripslashes( $activity_content ) );

		// Prepare image
		$data['image'] = $this->get_activity_image( $activity_obj );

		return $data;
	}

	/**
	 * Get activity image for OpenGraph.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    object $activity_obj Activity object.
	 * @return   string Image URL.
	 */
	private function get_activity_image( $activity_obj ) {
		$og_image = '';

		// Check for BP Media
		if ( class_exists( 'BP_Media' ) ) {
			$media_ids = bp_activity_get_meta( $activity_obj->id, 'bp_media_ids', true );
			if ( ! empty( $media_ids ) ) {
				$media_ids = explode( ',', $media_ids );
				if ( ! empty( $media_ids[0] ) ) {
					$media_data = new BP_Media( $media_ids[0] );
					$og_image = wp_get_attachment_image_url( $media_data->attachment_id, 'full' );
				}
			}
		}

		// Check for Youzer/Youzify media
		if ( empty( $og_image ) && ( class_exists( 'Youzer' ) || class_exists( 'Youzify' ) ) ) {
			$media_ids = bp_activity_get_meta( $activity_obj->id, 'yz_attachments', true );
			if ( empty( $media_ids ) ) {
				$media_ids = bp_activity_get_meta( $activity_obj->id, 'youzify_attachments', true );
			}
			
			if ( ! empty( $media_ids ) && is_array( $media_ids ) ) {
				// PHP 5.6+ compatible way to get first array key
				reset( $media_ids );
				$media_id = key( $media_ids );
				if ( $media_id !== null ) {
					$og_image = wp_get_attachment_image_url( $media_id, 'full' );
				}
			}
		}

		// Fallback to first image in content
		if ( empty( $og_image ) ) {
			preg_match_all( '/<img.*?src\s*=.*?>/', $activity_obj->content, $matches );
			if ( isset( $matches[0][0] ) ) {
				preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $matches[0][0], $matches2 );
				if ( isset( $matches2[1][0] ) ) {
					$og_image = $matches2[1][0];
				}
			}
		}

		return $og_image;
	}

	/**
	 * Output OpenGraph meta tags.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    string $permalink Activity permalink.
	 * @param    array  $data      OpenGraph data.
	 */
	private function bp_share_opengraph_meta_tags( $permalink, $data ) {
		?>
		<meta property="og:type" content="article" />
		<meta property="og:url" content="<?php echo esc_url( $permalink ); ?>" />
		<meta property="og:title" content="<?php echo esc_attr( $data['title'] ); ?>" />
		<meta property="og:description" content="<?php echo esc_attr( $data['description'] ); ?>" />
		<?php if ( ! empty( $data['image'] ) ) : ?>
			<meta property="og:image" content="<?php echo esc_url( $data['image'] ); ?>" />
			<meta property="og:image:secure_url" content="<?php echo esc_url( $data['image'] ); ?>" />
			<meta property="og:image:width" content="400" />
			<meta property="og:image:height" content="300" />
		<?php endif; ?>
		<?php
	}

	/**
	 * Add share button to post content.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $content Post content.
	 * @return   string Modified post content.
	 */
	public function bp_activity_post_share_button_action( $content ) {
		$settings = $this->get_plugin_settings();
		$bp_reshare_settings = isset( $settings['reshare_settings'] ) ? $settings['reshare_settings'] : array();

		if ( ! is_single() || 'post' !== get_post_type() || isset( $bp_reshare_settings['disable_post_reshare_activity'] ) ) {
			return $content;
		}

		$share_count = get_post_meta( get_the_ID(), 'share_count', true );
		$share_count = $share_count ? $share_count : '';
		
		ob_start();
		?>
		<div class="bp-activity-post-share-btn bp-activity-share-btn generic-button">
			<a class="button item-button bp-secondary-action bp-activity-share-button" 
			   data-bs-toggle="modal" 
			   data-bs-target="#activity-share-modal" 
			   data-post-id="<?php echo esc_attr( get_the_ID() ); ?>" 
			   rel="nofollow">
				<span class="bp-activity-reshare-icon">
					<?php bp_share_the_icon( 'share-2' ); ?>
				</span>
				<span class="bp-share-text"><?php esc_html_e( 'Share', 'buddypress-share' ); ?></span>
				<span id="bp-activity-reshare-count-<?php echo esc_attr( get_the_ID() ); ?>" class="reshare-count bp-post-reshare-count"><?php echo esc_html( $share_count ); ?></span>
			</a>
		</div>
		<?php

		return $content . ob_get_clean();
	}

	/**
	 * Render shared activity/post content in activity stream.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_activity_share_entry_content() {
		global $activities_template;

		$settings = $this->get_plugin_settings();
		$reshare_share_activity = isset( $settings['reshare_settings']['reshare_share_activity'] ) ? $settings['reshare_settings']['reshare_share_activity'] : 'parent';

		$activity_type = $activities_template->activity->type;
		$secondary_item_id = $activities_template->activity->secondary_item_id;

		if ( 0 === $secondary_item_id ) {
			return;
		}

		if ( 'activity_share' === $activity_type ) {
			$this->bp_share_display_activity( $secondary_item_id, $reshare_share_activity );
		} elseif ( 'post_share' === $activity_type ) {
			$this->bp_share_display_post( $secondary_item_id );
		}
	}

	/**
	 * Filter activity content to show original content for reshares.
	 *
	 * @since    2.1.1
	 * @access   public
	 * @param    string $content Activity content.
	 * @param    object $activity Activity object.
	 * @return   string Modified content.
	 */
	public function bp_share_filter_read_more_activity_content( $content, $activity = null ) {
		// $activity is optional: some BuddyPress paths / sibling plugins apply
		// `bp_get_activity_content_body` with only the content arg, which on PHP 8
		// would otherwise fatal with ArgumentCountError. Bail unless we have a
		// real activity object on the matching AJAX request.
		if ( ! is_object( $activity ) || ! wp_doing_ajax() || empty( $_REQUEST['action'] ) || $_REQUEST['action'] !== 'get_single_activity_content' ) { //phpcs:ignore
			return $content;
		}

		$requested_activity_id = ! empty( $_POST['id'] ) ? (int) $_POST['id'] : 0; //phpcs:ignore
		if ( empty( $requested_activity_id ) || $requested_activity_id !== (int) $activity->id ) {
			return $content;
		}
		
		if ( ! empty( $activity->type ) && in_array( $activity->type, array( 'activity_share' ), true ) ) {
			$original_activity_id = ! empty( $activity->secondary_item_id ) ? $activity->secondary_item_id : bp_activity_get_meta( $activity->id, 'shared_activity_id', true );

			while ( ! empty( $original_activity_id ) ) {
				$original_activity = new BP_Activity_Activity( $original_activity_id );

				if ( empty( $original_activity->id ) || $original_activity->type !== 'activity_share' ) {
					break;
				}

				$original_activity_id = ! empty( $original_activity->secondary_item_id )
					? $original_activity->secondary_item_id
					: bp_activity_get_meta( $original_activity->id, 'shared_activity_id', true );
			}

			if ( ! empty( $original_activity->content ) ) {
				return $original_activity->content;
			}
		}
		
		return $content;
	}

	

	/**
	 * Display shared BuddyPress activity.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    int    $activity_id Activity ID to render.
	 * @param    string $display_mode Parent or child display mode.
	 */
	private function bp_share_display_activity( $activity_id, $display_mode ) {
		global $activities_template;

		// Recursion guard. A reshare-of-a-reshare ("activity_share" whose
		// original is also "activity_share") would otherwise re-enter this
		// method through the bp_activity_entry_content hook and recurse without
		// bound, fataling the page. Stop once we exceed the allowed nesting.
		if ( self::$reshare_render_depth >= self::$reshare_max_depth ) {
			return;
		}

		// Store the original template
		$temp_activities_template = $activities_template;

		// Fetch the shared activity
		$shared_activity = new BP_Activity_Activity( $activity_id );

		if ( empty( $shared_activity->id ) ) {
			return;
		}

		// Create minimal activities query for this specific activity
		$args = array(
			'include'     => $activity_id,
			'per_page'    => 1,
			'show_hidden' => true
		);

		if ( ! bp_has_activities( $args ) ) {
			$activities_template = $temp_activities_template;
			return;
		}

		++self::$reshare_render_depth;

		// Detach this callback from the content hook for BOTH display modes
		// while we render the nested original. The original may itself be an
		// "activity_share" (a reshare chain); leaving the hook live would let
		// it fire again inside this loop and recurse. The depth guard above is
		// the hard cap; removing the hook is the clean, mode-independent guard.
		remove_action( 'bp_activity_entry_content', array( $this, 'bp_activity_share_entry_content' ) );

		while ( bp_activities() ) {
			bp_the_activity();
			$this->bp_share_activity_container();
		}

		// Restore the hook and template.
		add_action( 'bp_activity_entry_content', array( $this, 'bp_activity_share_entry_content' ) );
		$activities_template = $temp_activities_template;

		--self::$reshare_render_depth;
	}

	/**
	 * Display shared activity container.
	 *
	 * @since    1.5.2
	 * @access   private
	 */
	private function bp_share_activity_container() {
		?>
		<div id="bp-reshare-activity-<?php echo esc_attr( bp_get_activity_id() ); ?>" 
			 class="activity-reshare-item-container" 
			 data-bp-activity-id="<?php echo esc_attr( bp_get_activity_id() ); ?>"> 
			<div class="activity-item" data-bp-activity-id="<?php echo esc_attr( bp_get_activity_id() ); ?>"> 
				<div class="activity-avatar item-avatar">
					<a href="<?php bp_activity_user_link(); ?>">
						<?php bp_activity_avatar( array( 'type' => 'full' ) ); ?>
					</a>
				</div>
				<div class="activity-content">
					<div class="activity-header">
						<?php bp_activity_action(); ?>
					</div>
					<?php if ( function_exists( 'bp_nouveau_activity_has_content' ) && bp_nouveau_activity_has_content() ) : ?>
						<div class="activity-inner">
							<?php
							if ( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) ) {
								bp_nouveau_activity_content();
							} else {
								bp_get_template_part( 'activity/type-parts/content', bp_activity_type_part() );
							}
							?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Display shared WordPress post.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    int $post_id Post ID to render.
	 */
	private function bp_share_display_post( $post_id ) {
		$post = get_post( $post_id );
		
		if ( ! $post || 'publish' !== $post->post_status ) {
			return;
		}

		// Set up post data for template functions
		setup_postdata( $post );
		?>
		<div id="bp-reshare-activity-<?php echo esc_attr( $post_id ); ?>" 
			 class="post-reshare-item-container activity-reshare-item-container" 
			 data-bp-activity-id="<?php echo esc_attr( bp_get_activity_id() ); ?>"> 
			<div class="post-preview animate-slide-down entry-wrapper">
				<?php if ( has_post_thumbnail( $post_id ) ) : ?>
					<div class="entry-thumbnail">
						<?php echo get_the_post_thumbnail( $post_id, 'large' ); ?>
					</div>
				<?php endif; ?>
				
				<div class="post-preview-info fixed-height entry-content">
					<div class="post-preview-info-top entry-header">
						<p class="post-preview-timestamp">
							<?php $this->bp_activity_post_meta( $post ); ?>
						</p>
						<p class="post-preview-title entry-title">
							<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" rel="bookmark">
								<?php echo esc_html( get_the_title( $post_id ) ); ?>
							</a>
						</p>
					</div>
					<div class="post-preview-info-bottom post-open-body">
						<p class="post-preview-text entry-excerpt">
							<?php echo esc_html( get_the_excerpt( $post_id ) ); ?>
						</p>
						<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="post-preview-link color-primary read-more">
							<?php echo esc_html__( 'Read More', 'buddypress-share' ) . '...'; ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
		wp_reset_postdata();
	}

	/**
	 * Display post meta information for shared posts.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    WP_Post $post Post object.
	 */
	private function bp_activity_post_meta( $post ) {
		// Before post meta action
		do_action( 'bp_activity_share_before_post_meta' );

		// Post date
		printf(
			'<span class="link date-links">%1$s<a href="%2$s">%3$s</a></span>',
			bp_share_icon( 'calendar' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted bundled SVG asset.
			esc_url( get_month_link( get_the_time( 'Y', $post ), get_the_time( 'm', $post ) ) ),
			esc_html( get_the_date( '', $post ) )
		);

		// Categories
		$categories_list = get_the_category_list( esc_html__( ', ', 'buddypress-share' ), '', $post->ID );
		if ( $categories_list ) {
			printf( '<span class="link cat-links">%1$s%2$s</span>', bp_share_icon( 'folder' ), $categories_list ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// After post meta action
		do_action( 'bp_activity_share_after_post_meta' );
	}

	/**
	 * Create sharing modal popup.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function bp_activity_share_popup_box() {
		
		if ( ! $this->should_render_modal_on_current_page() ) {
			return;
		}

		$reshare_post_type = apply_filters( 'bp_activity_reshare_post_type', array( 'post' ) );
		
		if ( ! is_user_logged_in() || ! ( is_buddypress() || ( is_single() && in_array( get_post_type(), $reshare_post_type ) ) || apply_filters( 'bp_activity_reshare_action', false ) ) ) {

			return;
		}
					
		$user_name = $this->get_current_user_name();
		$bp_reshare_settings = get_site_option( 'bp_reshare_settings', array() );
		
		// Get modal classes with platform compatibility
		$modal_classes = function_exists( 'bp_share_get_modal_classes' ) ? bp_share_get_modal_classes() : 'activity-share-modal modal fade';
		?>
		<div class="<?php echo esc_attr( $modal_classes ); ?>" id="activity-share-modal" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="activity-share-modal-title" style="display: none;">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<!-- Close button with proper Bootstrap 4 attributes -->
					<div class="modal-header">
						<h5 class="modal-title" id="activity-share-modal-title"><?php esc_html_e( 'Share Activity', 'buddypress-share' ); ?></h5>
						<button type="button" class="close activity-share-modal-close" data-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'buddypress-share' ); ?>">
							<?php bp_share_the_icon( 'x' ); ?>
						</button>
					</div>
					
					<!-- Modal header content -->
					<div class="modal-header-content">
						<div class="quick-post-header-filters-wrap">
							<div class="bp-activity-share-avatar">
								<a href="<?php echo esc_url( bp_loggedin_user_domain() ); ?>">
									<?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
								</a>
								<div class="user-name-wrapper">
									<span class="user-name"><?php echo esc_html( $user_name ); ?></span>
									<small class="user-status-text"><?php esc_html_e( 'Status Update', 'buddypress-share' ); ?></small>
								</div>
							</div>
							<div class="bp-activity-share-filter">
								<div class="form-item">
									<div class="form-select">
										<label id="post-in-label" for="post-in"><?php esc_html_e( 'Post in', 'buddypress-share' ); ?></label>
										<!-- Select2 replaces the native select and reassigns its id, so the
										     <label for> link breaks. aria-labelledby keeps the accessible
										     name attached to the visible label on the Select2 widget too. -->
										<select id="post-in" name="postIn" class="bp-share-select2" style="width: 100%;" aria-labelledby="post-in-label" aria-label="<?php esc_attr_e( 'Post in', 'buddypress-share' ); ?>">
											<option value="0"><?php esc_html_e( 'My Profile', 'buddypress-share' ); ?></option>
											<?php if ( bp_is_active( 'groups' ) && empty( $bp_reshare_settings['disable_group_reshare_activity'] ) ) : ?>
												<optgroup label="<?php esc_attr_e( 'Groups', 'buddypress-share' ); ?>" id="bp-share-groups-options">
													<!-- Groups will be loaded dynamically -->
												</optgroup>
											<?php endif; ?>
											<?php if ( bp_is_active( 'friends' ) && empty( $bp_reshare_settings['disable_friends_reshare_activity'] ) ) : ?>
												<optgroup label="<?php esc_attr_e( 'Friends', 'buddypress-share' ); ?>" id="bp-share-friends-options">
													<!-- Friends will be loaded dynamically -->
												</optgroup>
											<?php endif; ?>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<!-- Modal Body -->
					<div class="modal-body">
						<?php $this->bp_activity_share_form( $user_name ); ?>
						<div id="bp-activity-share-widget-box-status-header">
							<?php $this->bp_activity_share_single_post_formate(); ?>
						</div>
					</div>
					
					<!-- Modal Footer -->
					<div class="modal-footer">
						<div class="bp-activity-share-post-footer-actions">
							<button type="button" class="btn btn-secondary bp-activity-share-close" data-dismiss="modal">
								<?php esc_html_e( 'Discard', 'buddypress-share' ); ?>
							</button>
							<button type="button" class="btn btn-primary bp-activity-share-activity">
								<?php esc_html_e( 'Post', 'buddypress-share' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		// Modal init, Select2 binding, close / backdrop / ESC handling all live in
		// public/js/buddypress-share-public.js (enqueued). The previous inline
		// <script> here duplicated that logic and caused a double-initialisation;
		// it was removed in 2.3.0.
	}

	/**
	 * Get current user name with version compatibility.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @return   string Current user name.
	 */
	private function get_current_user_name() {
		if ( function_exists( 'bp_members_get_user_slug' ) ) {
			return bp_members_get_user_slug( bp_loggedin_user_id() );
		}
		return bp_core_get_username( bp_loggedin_user_id() );
	}

	/**
	 * Display activity share form.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    string $user_name Current user name.
	 */
	private function bp_activity_share_form( $user_name ) {
		$placeholder_text = sprintf(
			/* translators: %s: username */
			esc_html__( 'Hi %s! Write something here. Use @ to mention someone...', 'buddypress-share' ),
			$user_name
		);
		?>
		<form class="form">
			<div class="form-textarea"> 
				<textarea id="bp-activity-share-text" 
						  name="bp-activity-share-text" 
						  placeholder="<?php echo esc_attr( $placeholder_text ); ?>" 
						  maxlength="1000" 
						  spellcheck="false"></textarea>
			</div>

			<input type="hidden" id="bp-reshare-activity-id" name="activity-id" value="" />
			<input type="hidden" id="bp-reshare-activity-user-id" name="user-id" value="<?php echo esc_attr( bp_loggedin_user_id() ); ?>" />
			
			<?php if ( is_buddypress() || apply_filters( 'bp_activity_reshare_action', false ) ) : ?>
				<input type="hidden" id="bp-reshare-activity-current-component" name="current_component" value="<?php echo esc_attr( bp_current_component() ); ?>" />
				<input type="hidden" id="bp-reshare-type" name="bp-reshare-type" value="activity_share" />
			<?php else : ?>
				<input type="hidden" id="bp-reshare-activity-current-component" name="current_component" value="activity" />
				<input type="hidden" id="bp-reshare-type" name="bp-reshare-type" value="post_share" />
			<?php endif; ?>
		</form>
		<?php
	}

	/**
	 * Format single post for sharing preview.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function bp_activity_share_single_post_formate() {
		$reshare_post_type = apply_filters( 'bp_activity_reshare_post_type', array( 'post' ) );

		if ( ! ( is_single() && in_array( get_post_type(), $reshare_post_type ) ) && ! apply_filters( 'bp_activity_reshare_action', false ) ) {
			return;
		}
		?>
		<div class="post-preview animate-slide-down entry-wrapper">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="entry-thumbnail">
					<?php the_post_thumbnail( 'large' ); ?>
				</div>
			<?php endif; ?>
			
			<div class="post-preview-info fixed-height entry-content">
				<div class="post-preview-info-top entry-header">
					<p class="post-preview-timestamp">
						<?php $this->bp_activity_post_meta( get_post() ); ?>
					</p>
					<p class="post-preview-title entry-title">
						<a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark">
							<?php the_title(); ?>
						</a>
					</p>
				</div>
				<div class="post-preview-info-bottom post-open-body">
					<p class="post-preview-text entry-excerpt">
						<?php the_excerpt(); ?>
					</p>
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="post-preview-link color-primary read-more">
						<?php echo esc_html__( 'Read More', 'buddypress-share' ) . '...'; ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Shortcode for post sharing button.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @param    array  $atts    Shortcode attributes.
	 * @param    string $content Shortcode content.
	 * @return   string Share button HTML.
	 */
	public function bp_activity_post_reshare( $atts, $content = null ) {
		if ( ! is_single() || 'post' !== get_post_type() ) {
			return '';
		}

		$share_count = get_post_meta( get_the_ID(), 'share_count', true );
		$share_count = $share_count ? $share_count : '';
		
		ob_start();
		?>
		<div class="bp-activity-post-share-btn bp-activity-share-btn generic-button">
			<a class="button item-button bp-secondary-action bp-activity-share-button" 
			   data-bs-toggle="modal" 
			   data-bs-target="#activity-share-modal" 
			   data-post-id="<?php echo esc_attr( get_the_ID() ); ?>" 
			   rel="nofollow">
				<span class="bp-activity-reshare-icon">
					<?php bp_share_the_icon( 'share-2' ); ?>
				</span>
				<span class="bp-share-text"><?php esc_html_e( 'Share', 'buddypress-share' ); ?></span>
				<span id="bp-activity-reshare-count-<?php echo esc_attr( get_the_ID() ); ?>" class="reshare-count bp-post-reshare-count"><?php echo esc_html( $share_count ); ?></span>
			</a>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Add share count to REST API response.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    object $response REST response.
	 * @param    object $request  REST request.
	 * @param    array  $activity Activity data.
	 * @return   object Modified response.
	 */
	public function bp_activity_post_reshare_data_embed_rest_api( $response, $request, $activity ) {
		$share_count = bp_activity_get_meta( $activity->id, 'share_count', true );
		$response->data['bp_activity_share_count'] = $share_count;
		return $response;
	}
}
