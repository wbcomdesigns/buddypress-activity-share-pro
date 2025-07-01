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
	 * Modern CDN URLs for external libraries.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @var      array    CDN asset URLs.
	 */
	const CDN_ASSETS = [
		'font_awesome' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
		'bootstrap_css' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css',
		'bootstrap_js' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js',
		'select2_css' => 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css',
		'select2_js' => 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js',
	];

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

		// Font Awesome 5.15.4 - Load only if not already loaded
		if ( ! $this->is_fontawesome_loaded() ) {
			wp_enqueue_style( 
				'bp-share-fontawesome', 
				self::CDN_ASSETS['font_awesome'],
				array(), 
				'5.15.4', 
				'all' 
			);
		}

		// Bootstrap CSS - Load only if not conflicting
		if ( ! $this->has_bootstrap_conflict() && ! wp_style_is( 'bootstrap', 'enqueued' ) ) {
			wp_enqueue_style( 
				'bp-share-bootstrap', 
				self::CDN_ASSETS['bootstrap_css'],
				array(), 
				'4.6.2', 
				'all' 
			);
		}
		
		// Select2 CSS - For enhanced dropdowns
		wp_enqueue_style( 
			'bp-share-select2', 
			self::CDN_ASSETS['select2_css'],
			array(), 
			'4.1.0', 
			'all' 
		);

		// Custom AS-Icons font with auto min/RTL support
		bp_share_enqueue_style(
			'bp-share-as-icons',
			$plugin_url . 'public/css/as-icons', // Without .css
			array(),
			$this->version,
			'all'
		);
		
		// Main plugin CSS with auto min/RTL support
		bp_share_enqueue_style(
			$this->plugin_name,
			$plugin_url . 'public/css/buddypress-share-public', // Without .css
			array( 'bp-share-as-icons' ),
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
		
		// Bootstrap JS - Load only if not conflicting
		if ( ! $this->has_bootstrap_conflict() && ! wp_script_is( 'bootstrap', 'enqueued' ) ) {
			wp_enqueue_script( 
				'bp-share-bootstrap', 
				self::CDN_ASSETS['bootstrap_js'],
				array( 'jquery' ), 
				'4.6.2', 
				true 
			);
		}
		
		// Select2 JS - For enhanced dropdowns
		wp_enqueue_script( 
			'bp-share-select2', 
			self::CDN_ASSETS['select2_js'],
			array( 'jquery' ), 
			'4.1.0', 
			true 
		);
		
		// Main plugin script with auto minification
		bp_share_enqueue_script(
			$this->plugin_name,
			$plugin_url . 'public/js/buddypress-share-public', // Without .js
			array( 'jquery', 'wp-i18n' ),
			$this->version,
			true
		);
		
		// Set script translations
		wp_set_script_translations( $this->plugin_name, 'buddypress-share', BP_ACTIVITY_SHARE_PLUGIN_PATH . 'languages/' );

		// Localize script with necessary data
		$this->localize_script();
	}

	/**
	 * Check if Font Awesome is already loaded by other plugins/themes.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @return   bool True if Font Awesome is already loaded, false otherwise.
	 */
	private function is_fontawesome_loaded() {
		global $wp_styles;
		
		if ( ! $wp_styles ) {
			return false;
		}

		// Check for various Font Awesome handles
		$fa_handles = [
			'font-awesome',
			'fontawesome', 
			'fa',
			'font-awesome-5',
			'fontawesome-5',
			'wp-fontawesome',
			'elementor-icons-fa-solid',
			'elementor-icons-fa-brands'
		];

		foreach ( $fa_handles as $handle ) {
			if ( wp_style_is( $handle, 'enqueued' ) || wp_style_is( $handle, 'registered' ) ) {
				return true;
			}
		}

		return false;
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
		$conflicts = [
			class_exists( 'WeDevs_Dokan' ) && function_exists( 'dokan_is_seller_dashboard' ) && dokan_is_seller_dashboard(),
			wp_style_is( 'bootstrap', 'enqueued' ),
			wp_style_is( 'bootstrap-css', 'enqueued' ),
		];

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
	 * Check if assets should be loaded on current page.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @return   bool True if assets should be loaded, false otherwise.
	 */
	private function should_load_assets() {
		// Load on BP pages, single posts, or when explicitly requested
		return ( function_exists( 'is_buddypress' ) && is_buddypress() ) 
			|| is_single() 
			|| apply_filters( 'bp_activity_share_load_assets', false );
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

		wp_localize_script(
			$this->plugin_name,
			'bp_activity_share_vars',
			array(
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'current_user_id'        => get_current_user_id(),
				'reshare_share_activity' => $reshare_share_activity,
				'ajax_nonce'             => wp_create_nonce( 'bp-activity-share-nonce' ),
				'member_profile_url'     => function_exists('bp_loggedin_user_domain') ? bp_loggedin_user_domain() . 'messages/compose/' : '',
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

		if ( is_user_logged_in() || ( ! is_user_logged_in() && $settings['logout_enable'] ) ) {
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
			
			if ( $settings['logout_enable'] ) {
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
		$activity_id = bp_get_activity_id();
		
		// Get share count
		$share_count = bp_activity_get_meta( $activity_id, 'share_count', true );
		$share_count = $share_count ? $share_count : '';

		global $activities_template;
		
		// Use cached plugin settings
		$settings = $this->get_plugin_settings();
		$social_service = $settings['services'];
		$extra_options = $settings['extra_options'];
		$bp_reshare_settings = $settings['reshare_settings'];
		
		$activity_type  = bp_get_activity_type();
		$activity_link  = $this->get_activity_permalink( $activities_template->activity );
		$activity_title = bp_get_activity_feed_item_title();
		$mail_subject   = wp_strip_all_tags( $activities_template->activity->action );
		
		if ( ! is_user_logged_in() ) {
			echo '<div class="activity-meta">';
		}

		$icon_settings = $settings['icon_settings'];
		$style = isset( $icon_settings['icon_style'] ) ? $icon_settings['icon_style'] : 'circle';
		?>
	
		<div class="generic-button bp-activity-share-dropdown-toggle">
			<a class="button dropdown-toggle" rel="nofollow">
				<span class="bp-activity-reshare-icon">	
					<i class="as-icon as-icon-share-square"></i>
				</span>
				<span class="bp-share-text"><?php esc_html_e( 'Share', 'buddypress-share' ); ?></span>
				<span id="bp-activity-reshare-count-<?php echo esc_attr( bp_get_activity_id() ); ?>" class="reshare-count bp-activity-reshare-count"><?php echo esc_html( $share_count ); ?></span>
			</a>
			
			<div class="bp-activity-share-dropdown-menu activity-share-dropdown-menu-container <?php echo esc_attr( $activity_type . ' ' . $style ); ?>">
				<?php if ( is_user_logged_in() ) : ?>
					<?php $this->render_logged_in_share_options( $bp_reshare_settings ); ?>
				<?php endif; ?>
				
				<?php if ( $settings['services_enable'] ) : ?>
					<div class="bp-share-activity-share-to-wrapper">
						<?php
						if ( ! empty( $social_service ) ) {
							$this->render_social_sharing_buttons( $activity_link, $activity_title, $mail_subject, $social_service );
						} else {
							esc_html_e( 'Please enable share services!', 'buddypress-share' );
						}
						?>
					</div>
				<?php endif; ?>
				
				<?php do_action( 'bp_share_user_services', array(), $activity_link, $activity_title ); ?>
			</div>
			
			<?php $this->render_popup_overlay(); ?>
			
			<?php $this->render_popup_script( $extra_options ); ?>
		</div>
		
		<?php
		if ( ! is_user_logged_in() ) {
			echo '</div>';
		}
	}

	/**
	 * Render share options for logged-in users.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    array $bp_reshare_settings Reshare settings.
	 */
	private function render_logged_in_share_options( $bp_reshare_settings ) {
		$share_options = array(
			'my-profile' => array(
				'setting' => 'disable_my_profile_reshare_activity',
				'icon'    => 'fas fa-user',
				'label'   => __( 'Share to My Profile', 'buddypress-share' ),
				'title'   => __( 'My Profile', 'buddypress-share' ),
			),
			'message' => array(
				'setting' => 'disable_message_reshare_activity',
				'icon'    => 'fas fa-envelope',
				'label'   => __( 'Share to Message', 'buddypress-share' ),
				'title'   => __( 'Message', 'buddypress-share' ),
				'is_link' => true,
				'url'     => function_exists('bp_loggedin_user_domain') ? bp_loggedin_user_domain() . 'messages/compose/?activity_url=' . bp_loggedin_user_domain() . 'activity/' . bp_get_activity_id() : '#',
			),
			'groups' => array(
				'setting' => 'disable_group_reshare_activity',
				'icon'    => 'fas fa-users',
				'label'   => __( 'Share to a group', 'buddypress-share' ),
				'title'   => __( 'Select Group', 'buddypress-share' ),
			),
			'friends' => array(
				'setting' => 'disable_friends_reshare_activity',
				'icon'    => 'fas fa-user-plus',
				'label'   => __( 'Share with Friends', 'buddypress-share' ),
				'title'   => __( 'Select Friend', 'buddypress-share' ),
			),
		);

		foreach ( $share_options as $key => $option ) {
			if ( ! isset( $bp_reshare_settings[ $option['setting'] ] ) ) {
				$this->render_share_option( $key, $option );
			}
		}
	}

	/**
	 * Render individual share option.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    string $key Share option key.
	 * @param    array  $option Share option data.
	 */
	private function render_share_option( $key, $option ) {
		$css_class = isset( $option['is_link'] ) ? 'bp-activity-share-btn' : 'bp-activity-share-btn bp-activity-reshare-btn';
		?>
		<div class="<?php echo esc_attr( $css_class ); ?>" data-reshare="<?php echo esc_attr( $key ); ?>" data-title="<?php echo esc_attr( $option['title'] ); ?>">
			<?php if ( isset( $option['is_link'] ) ) : ?>
				<a href="<?php echo esc_url( $option['url'] ); ?>" class="button item-button bp-secondary-action" rel="nofollow">
			<?php else : ?>
				<a class="button item-button bp-secondary-action bp-activity-share-button" data-bs-toggle="modal" data-bs-target="#activity-share-modal" data-activity-id="<?php echo esc_attr( bp_get_activity_id() ); ?>" rel="nofollow">
			<?php endif; ?>
				<span class="bp-activity-reshare-icon">	
					<i class="<?php echo esc_attr( $option['icon'] ); ?>"></i>
				</span>
				<span class="bp-share-text bp-share-label"><?php echo esc_html( $option['label'] ); ?></span>
			</a>
		</div>
		<?php
	}

	/**
	 * Render social sharing buttons with Font Awesome 5 icons.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    string $activity_link  Activity permalink.
	 * @param    string $activity_title Activity title.
	 * @param    string $mail_subject   Email subject.
	 * @param    array  $social_service Enabled social services.
	 */
	private function render_social_sharing_buttons( $activity_link, $activity_title, $mail_subject, $social_service ) {
		$sharing_services = $this->get_sharing_services_config( $activity_link, $activity_title, $mail_subject );

		foreach ( $sharing_services as $service => $details ) {
			if ( ! empty( $social_service[ $service ] ) ) {
				$service_key = ( 'E-mail' === $service ) ? 'Email' : $service;
				$button_id = "bp_" . strtolower( str_replace( '-', '_', $service_key ) ) . "_share";
				
				echo '<div class="bp-share-wrapper">';
				echo '<a class="button bp-share" id="' . esc_attr( $button_id ) . '" href="' . esc_url( $details['url'] ) . '" target="_blank">';
				echo '<i class="' . esc_attr( $details['icon'] ) . '"></i>';
				echo '<span class="bp-share-label">' . esc_html( $details['label'] ) . '</span>';
				echo '</a>';
				echo '</div>';
			}
		}

		// Add copy link button
		echo '<div class="bp-share-wrapper bp-copy-wrapper">';
		echo '<a class="button bp-share bp-copy" href="#" data-href="' . esc_attr( $activity_link ) . '" attr-display="no-popup">';
		echo '<i class="fas fa-link"></i>';
		echo '<span class="bp-share-label">' . esc_html__( 'Copy Link', 'buddypress-share' ) . '</span>';
		echo '</a>';
		echo '<span class="tooltiptext tooltip-hide">' . esc_attr__( 'Link Copied!', 'buddypress-share' ) . '</span>';
		echo '</div>';
		echo '</div>';
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
		$services = array(
			'Facebook' => array(
				'url'   => 'https://www.facebook.com/sharer.php?u=' . urlencode( $activity_link ),
				'icon'  => 'fab fa-facebook-f',
				'label' => __( 'Facebook', 'buddypress-share' )
			),
			'X' => array(
				'url'   => 'https://twitter.com/share?url=' . urlencode( $activity_link ) . '&text=' . urlencode( $activity_title ),
				'icon'  => 'fab fa-twitter',
				'label' => __( 'X', 'buddypress-share' )
			),
			'LinkedIn' => array(
				'url'   => 'http://www.linkedin.com/shareArticle?mini=true&url=' . urlencode( $activity_link ) . '&text=' . urlencode( $activity_title ),
				'icon'  => 'fab fa-linkedin-in',
				'label' => __( 'LinkedIn', 'buddypress-share' )
			),
			'Pinterest' => array(
				'url'   => 'https://pinterest.com/pin/create/bookmarklet/?url=' . urlencode( $activity_link ) . '&description=' . urlencode( $activity_title ),
				'icon'  => 'fab fa-pinterest-p',
				'label' => __( 'Pinterest', 'buddypress-share' )
			),
			'Reddit' => array(
				'url'   => 'http://reddit.com/submit?url=' . urlencode( $activity_link ) . '&title=' . urlencode( $activity_title ),
				'icon'  => 'fab fa-reddit-alien',
				'label' => __( 'Reddit', 'buddypress-share' )
			),
			'WordPress' => array(
				'url'   => 'https://wordpress.com/wp-admin/press-this.php?u=' . urlencode( $activity_link ) . '&t=' . urlencode( $activity_title ),
				'icon'  => 'fab fa-wordpress',
				'label' => __( 'WordPress', 'buddypress-share' )
			),
			'Pocket' => array(
				'url'   => 'https://getpocket.com/save?url=' . urlencode( $activity_link ) . '&title=' . urlencode( $activity_title ),
				'icon'  => 'fab fa-get-pocket',
				'label' => __( 'Pocket', 'buddypress-share' )
			),
			'Telegram' => array(
				'url'   => 'https://t.me/share/url?url=' . urlencode( $activity_link ) . '&title=' . urlencode( $activity_title ),
				'icon'  => 'fab fa-telegram-plane',
				'label' => __( 'Telegram', 'buddypress-share' )
			),
			'Bluesky' => array(
				'url'   => 'https://bsky.app/intent/compose?text=' . urlencode( 'Check this out! ' . $activity_title . ' ' . $activity_link ),
				'icon'  => 'fas fa-cloud',
				'label' => __( 'Bluesky', 'buddypress-share' )
			),
			'WhatsApp' => array(
				'url'   => 'https://wa.me/?text=' . urlencode( $activity_link ),
				'icon'  => 'fab fa-whatsapp',
				'label' => __( 'WhatsApp', 'buddypress-share' )
			),
		);

		// Add email service if enabled
		$social_service = $this->get_plugin_settings()['services'];
		if ( isset( $social_service['E-mail'] ) && ! empty( $social_service['E-mail'] ) ) {
			$site_title = get_bloginfo( 'name' );
			$site_url = home_url();

			$email_subject = 'New Activity on ' . esc_html( $site_title ) . ': ' . esc_html( $mail_subject );
			$email_body = "Hi,\n\nI wanted to share this activity with you from " . esc_html( $site_title ) . ":\n\n" . esc_url( $activity_link ) . "\n\nYou can explore more activities here: " . esc_url( $site_url ) . "\n\nBest regards,\nThe " . esc_html( $site_title ) . ' Team';

			$services['E-mail'] = array(
				'url'   => 'mailto:?subject=' . rawurlencode( $email_subject ) . '&body=' . rawurlencode( $email_body ),
				'icon'  => 'fas fa-envelope',
				'label' => __( 'E-mail', 'buddypress-share' )
			);
		}

		return $services;
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
	 * Render popup overlay for theme compatibility.
	 *
	 * @since    1.5.2
	 * @access   private
	 */
	private function render_popup_overlay() {
		$theme_support = apply_filters( 'buddypress_reactions_theme_support', array( 'reign-theme', 'buddyx-pro' ) );
		$theme_name = wp_get_theme();

		if ( in_array( $theme_name->template, $theme_support ) ) {
			echo '<div class="bp-share-service-popup-overlay"></div>';
		}
	}

	/**
	 * Render popup activation script.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    array $extra_options Extra plugin options.
	 */
	private function render_popup_script( $extra_options ) {
		$popup_active = isset( $extra_options['bp_share_services_open'] ) ? $extra_options['bp_share_services_open'] : '';
		?>
		<script>
			jQuery(document).ready(function() {
				var popActive = '<?php echo esc_js( $popup_active ); ?>';
				if (popActive === 'on') {
					jQuery('.bp-share').not('#bp_whatsapp_share, #bp_email_share').addClass('has-popup');
				}
			});
		</script>
		<?php
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
		if ( ! wp_verify_nonce( $_POST['_ajax_nonce'] ?? '', 'bp-activity-share-nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'buddypress-share' ) ) );
		}

		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'User not logged in.', 'buddypress-share' ) ) );
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

		// Handle user mentions
		if ( isset( $_POST['activity_in_type'] ) && 'user' === $_POST['activity_in_type'] ) {
			$username = $this->get_user_name_by_id( $activity_in );
			if ( $username ) {
				$activity_content = "@{$username} \r\n{$activity_content}";
			}
			$activity_in = 0;
		}

		// Validate group permissions
		if ( $activity_in > 0 && ! $this->user_can_post_to_group( $user_id, $activity_in ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to post in this group.', 'buddypress-share' ) ) );
		}

		// Create activity
		$new_activity_id = $this->create_share_activity( $user_id, $activity_id, $activity_type, $activity_content, $activity_in );

		if ( ! $new_activity_id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create activity.', 'buddypress-share' ) ) );
		}

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
		if ( function_exists( 'buddypress' ) && version_compare( buddypress()->version, '12.0', '>=' ) ) {
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
		$activity_args = array(
			'user_id'           => $user_id,
			'component'         => ( $activity_in > 0 ) ? 'groups' : 'activity',
			'type'              => $activity_type,
			'content'           => $activity_content,
			'secondary_item_id' => $activity_id,
			'item_id'           => $activity_in,
		);

		return bp_activity_add( $activity_args );
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

		$this->render_opengraph_meta( $activity_permalink, $og_data );
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
		$title = wp_strip_all_tags( ent2ncr( trim( convert_chars( $content[0] ) ) ) );
		
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
				$media_id = array_key_first( $media_ids );
				$og_image = wp_get_attachment_image_url( $media_id, 'full' );
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
	 * Render OpenGraph meta tags.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    string $permalink Activity permalink.
	 * @param    array  $data      OpenGraph data.
	 */
	private function render_opengraph_meta( $permalink, $data ) {
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
		$bp_reshare_settings = $settings['reshare_settings'];

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
					<i class="as-icon as-icon-share-square"></i>
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

		if ( 0 == $secondary_item_id ) {
			return;
		}

		if ( 'activity_share' === $activity_type ) {
			$this->bp_render_shared_activity( $secondary_item_id, $reshare_share_activity );
		} elseif ( 'post_share' === $activity_type ) {
			$this->bp_render_shared_post( $secondary_item_id );
		}
	}

	/**
	 * Render shared BuddyPress activity.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    int    $activity_id Activity ID to render.
	 * @param    string $display_mode Parent or child display mode.
	 */
	private function bp_render_shared_activity( $activity_id, $display_mode ) {
		global $activities_template;
		
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
		
		// Temporarily remove this function from the content hook if showing parent
		if ( 'parent' === $display_mode ) {
			remove_action( 'bp_activity_entry_content', array( $this, 'bp_activity_share_entry_content' ) );
		}
		
		while ( bp_activities() ) {
			bp_the_activity();
			$this->render_shared_activity_html();
		}
		
		// Restore filters and template
		if ( 'parent' === $display_mode ) {
			add_action( 'bp_activity_entry_content', array( $this, 'bp_activity_share_entry_content' ) );
		}
		$activities_template = $temp_activities_template;
	}

	/**
	 * Render shared activity HTML.
	 *
	 * @since    1.5.2
	 * @access   private
	 */
	private function render_shared_activity_html() {
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
	 * Render shared WordPress post.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    int $post_id Post ID to render.
	 */
	private function bp_render_shared_post( $post_id ) {
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
			'<span class="link date-links"><i class="as-icon-calendar"></i><a href="%s">%s</a></span>', 
			esc_url( get_month_link( get_the_time( 'Y', $post ), get_the_time( 'm', $post ) ) ), 
			get_the_date( '', $post )
		);

		// Categories
		$categories_list = get_the_category_list( esc_html__( ', ', 'buddypress-share' ), '', $post->ID );
		if ( $categories_list ) {
			printf( '<span class="link cat-links"><i class="as-icon-folder"></i>%s</span>', $categories_list );
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
		$reshare_post_type = apply_filters( 'bp_activity_reshare_post_type', array( 'post' ) );

		if ( ! is_user_logged_in() || ! ( is_buddypress() || ( is_single() && in_array( get_post_type(), $reshare_post_type ) ) || apply_filters( 'bp_activity_reshare_action', false ) ) ) {
			return;
		}

		$user_name = $this->get_current_user_name();
		?>
		<div class="modal fade activity-share-modal" id="activity-share-modal" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="activity-share-modal-title">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<!-- Close button with proper Bootstrap 4 attributes -->
					<div class="modal-header">
						<h5 class="modal-title" id="activity-share-modal-title"><?php esc_html_e( 'Share Activity', 'buddypress-share' ); ?></h5>
						<button type="button" class="close activity-share-modal-close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true"><i class="as-icon as-icon-times"></i></span>
						</button>
					</div>
					
					<!-- Modal header content -->
					<div class="modal-header-content">
						<div class="quick-post-header-filters-wrap">
							<div class="bp-activity-share-avatar">
								<a href="<?php echo esc_url( bp_loggedin_user_domain() ); ?>">
									<?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
								</a>
								<span class="user-name"><?php echo esc_html( $user_name ); ?></span>
								<small class="user-status-text"><?php esc_html_e( 'Status Update', 'buddypress-share' ); ?></small>
							</div>
							<div class="bp-activity-share-filter">
								<div class="form-item">
									<div class="form-select">
										<label for="post-in"><?php esc_html_e( 'Post in', 'buddypress-share' ); ?></label>
										<!-- Select2 dropdown with proper initialization -->
										<select id="post-in" name="postIn" class="bp-share-select2" style="width: 100%;">
											<option value="0"><?php esc_html_e( 'My Profile', 'buddypress-share' ); ?></option>
											<option value="message"><?php esc_html_e( 'Message', 'buddypress-share' ); ?></option>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<!-- Modal Body -->
					<div class="modal-body">
						<?php $this->render_share_form( $user_name ); ?>
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

		<!-- Modal and Select2 initialization script -->
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			// Fix Bootstrap modal initialization
			if (typeof $.fn.modal !== 'undefined') {
				// Ensure modal is properly initialized
				$('#activity-share-modal').modal({
					show: false,
					backdrop: true,
					keyboard: true
				});
			}

			// Fix Select2 initialization
			if (typeof $.fn.select2 !== 'undefined') {
				// Initialize Select2 with proper configuration
				$('#post-in').select2({
					dropdownParent: $('#activity-share-modal'),
					placeholder: 'Select where to share...',
					allowClear: false,
					minimumResultsForSearch: 10,
					width: '100%'
				});
			}

			// Fix close button functionality
			$(document).on('click', '.activity-share-modal-close, .bp-activity-share-close', function(e) {
				e.preventDefault();
				$('#activity-share-modal').modal('hide');
			});

			// Fix backdrop click to close
			$('#activity-share-modal').on('click', function(e) {
				if (e.target === this) {
					$(this).modal('hide');
				}
			});

			// Fix ESC key to close
			$(document).on('keydown', function(e) {
				if (e.keyCode === 27 && $('#activity-share-modal').hasClass('show')) {
					$('#activity-share-modal').modal('hide');
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Get current user name with version compatibility.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @return   string Current user name.
	 */
	private function get_current_user_name() {
		if ( function_exists( 'buddypress' ) && version_compare( buddypress()->version, '12.0', '>=' ) ) {
			return bp_members_get_user_slug( bp_loggedin_user_id() );
		}
		return bp_core_get_username( bp_loggedin_user_id() );
	}

	/**
	 * Render share form.
	 *
	 * @since    1.5.2
	 * @access   private
	 * @param    string $user_name Current user name.
	 */
	private function render_share_form( $user_name ) {
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
					<i class="as-icon as-icon-share-square"></i>
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