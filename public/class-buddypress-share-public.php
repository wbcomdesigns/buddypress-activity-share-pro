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
 * Optimized for large sites with improved performance and reduced database queries.
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
	 * @since    1.5.1
	 * @access   private
	 * @var      array|null    $cached_settings    Plugin settings cache.
	 */
	private $cached_settings = null;

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
	 * Optimized to only load assets where needed for better performance.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_styles() {
		// Only load styles where needed
		if ( ! $this->should_load_assets() ) {
			return;
		}

		$rtl_css = is_rtl() ? '-rtl' : '';

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$css_extension = '.css';
		} else {
			$css_extension = '.min.css';
		}

		if ( ! wp_style_is( 'wb-font-awesome', 'enqueued' ) ) {
			wp_enqueue_style( 'wb-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), $this->version, 'all' );
		}
		wp_enqueue_style( 'bootstrap-css', plugin_dir_url( __FILE__ ) . 'css/vendor/bootstrap.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'select2-css', plugin_dir_url( __FILE__ ) . 'css/vendor/select2.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'icons-css', plugin_dir_url( __FILE__ ) . 'css' . $rtl_css . '/as-icons' . $css_extension, array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css' . $rtl_css . '/buddypress-share-public' . $css_extension, array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * Optimized asset loading and reduced database calls for better performance.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_scripts() {
		// Only load scripts where needed
		if ( ! $this->should_load_assets() ) {
			return;
		}

		// Get settings once and cache
		$settings = $this->get_plugin_settings();
		$reshare_share_activity = isset( $settings['reshare_settings']['reshare_share_activity'] ) ? $settings['reshare_settings']['reshare_share_activity'] : 'parent';

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$js_extension = '.js';
		} else {
			$js_extension = '.min.js';
		}

		wp_enqueue_script( 'jquery-ui-tooltip' );
		
		// Handle Bootstrap conflicts more efficiently
		if ( ! ( class_exists( 'WeDevs_Dokan' ) && function_exists( 'dokan_is_seller_dashboard' ) && dokan_is_seller_dashboard() ) ) {
			wp_enqueue_script( 'bootstrap-js', plugin_dir_url( __FILE__ ) . 'js/vendor/bootstrap.min.js', array( 'jquery' ), $this->version, false );
		}
		
		wp_enqueue_script( 'select2-js', plugin_dir_url( __FILE__ ) . 'js/vendor/select2.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/buddypress-share-public' . $js_extension, array( 'jquery', 'wp-i18n' ), $this->version, false );
		wp_set_script_translations( $this->plugin_name, 'buddypress-share', BP_ACTIVITY_SHARE_PLUGIN_PATH . 'languages/' );

		wp_localize_script(
			$this->plugin_name,
			'bp_activity_share_vars',
			array(
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'current_user_id'        => get_current_user_id(),
				'reshare_share_activity' => $reshare_share_activity,
				'ajax_nonce'             => wp_create_nonce( 'bp-activity-share-nonce' ),
				'member_profile_url'     => bp_loggedin_user_domain() . 'messages/compose/',
			)
		);
	}

	/**
	 * Check if assets should be loaded on current page.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @return   bool True if assets should be loaded, false otherwise.
	 */
	private function should_load_assets() {
		// Only load on BP pages, single posts, or when explicitly requested
		return ( function_exists( 'is_buddypress' ) && is_buddypress() ) 
			|| is_single() 
			|| apply_filters( 'bp_activity_share_load_assets', false );
	}

	/**
	 * Get plugin settings with static caching to avoid repeated database calls.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @return   array Plugin settings array.
	 */
	private function get_plugin_settings() {
		if ( null === $this->cached_settings ) {
			$this->cached_settings = array(
				'services' => get_site_option( 'bp_share_services', array() ),
				'extra_options' => get_site_option( 'bp_share_services_extra', array() ),
				'reshare_settings' => get_site_option( 'bp_reshare_settings', array() ),
				'icon_settings' => get_option( 'bpas_icon_color_settings', array() )
			);
		}
		
		return $this->cached_settings;
	}

	/**
	 * Display share button in activity stream.
	 *
	 * Optimized to reduce database calls and improve performance.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_activity_share_button_dis() {
		$settings = $this->get_plugin_settings();
		$bp_share_services_logout_enable = get_site_option( 'bp_share_services_logout_enable' );

		if ( ( is_user_logged_in() ) || ( ! is_user_logged_in() && $bp_share_services_logout_enable == 1 ) ) {
			add_action( 'bp_activity_entry_meta', array( $this, 'bp_share_inner_activity_filter' ) );
		}
	}

	/**
	 * Adds a custom body class based on the 'bp_share_services_logout_enable' setting.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array $classes Existing array of body classes.
	 * @return   array Modified array of body classes.
	 */
	public function add_bp_share_services_logout_body_class( $classes ) {
		if ( ! is_user_logged_in() ) {
			$bp_share_services_logout_enable = get_site_option( 'bp_share_services_logout_enable' );

			if ( $bp_share_services_logout_enable ) {
				$classes[] = 'bpss-logout-enabled';
			}
		}

		return $classes;
	}

	/**
	 * Filter hook to modify BuddyPress inner activity content before sharing.
	 *
	 * Optimized to avoid expensive database queries for groups and friends.
	 * Groups and friends are now loaded dynamically via AJAX when needed.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function bp_share_inner_activity_filter() {
		$activity_id = bp_get_activity_id();
		
		// Get share count directly without caching for logged-in users
		$share_count = bp_activity_get_meta( $activity_id, 'share_count', true );
		$share_count = $share_count ? $share_count : '';

		global $activities_template;
		
		// Use cached plugin settings
		$settings = $this->get_plugin_settings();
		$social_service = $settings['services'];
		$extra_options = $settings['extra_options'];
		$bp_reshare_settings = $settings['reshare_settings'];
		
		$activity_type  = bp_get_activity_type();
		$activity_link  = site_url() . '/' . bp_get_members_slug() . '/' . $activities_template->activity->user_nicename . '/' . bp_get_activity_slug() . '/' . $activities_template->activity->id . '/';
		$activity_title = bp_get_activity_feed_item_title();
		$mail_subject   = wp_strip_all_tags( $activities_template->activity->action );
		
		if ( ! is_user_logged_in() ) {
			echo '<div class = "activity-meta" >';
		}

		$theme_support = apply_filters( 'buddypress_reactions_theme_support', array( 'reign-theme', 'buddyx-pro' ) );
		$theme_name    = wp_get_theme();

		$icon_settings = $settings['icon_settings'];
		$style = isset( $icon_settings['icon_style'] ) ? $icon_settings['icon_style'] : 'circle';
		?>
	
		<div class="generic-button bp-activity-share-dropdown-toggle ">
			<a class="button dropdown-toggle" rel="nofollow">
				<span class="bp-activity-reshare-icon">	
					<i class="as-icon as-icon-share-square"></i>
				</span>
				<span class="bp-share-text"><?php esc_html_e( 'Share', 'buddypress-share' ); ?></span>
				<span id="bp-activity-reshare-count-<?php echo esc_attr( bp_get_activity_id() ); ?>" class="reshare-count bp-activity-reshare-count"><?php echo esc_html( $share_count ); ?></span>
			</a>
			<div class="bp-activity-share-dropdown-menu activity-share-dropdown-menu-container <?php echo esc_attr( $activity_type . ' ' . $style ); ?>">
				<?php if ( is_user_logged_in() ) { ?>
					<?php if ( ! isset( $bp_reshare_settings['disable_my_profile_reshare_activity'] ) ) { ?>
						<div class="bp-activity-share-btn bp-activity-reshare-btn" data-reshare="my-profile" data-title="<?php esc_attr_e( 'My Profile', 'buddypress-share' ); ?>">
							<a class="button item-button bp-secondary-action bp-activity-share-button" data-bs-toggle="modal" data-bs-target="#activity-share-modal" data-activity-id="<?php echo esc_attr( bp_get_activity_id() ); ?>" rel="nofollow">
								<span class="bp-activity-reshare-icon">	
									<span class="dashicons dashicons-admin-users"></span>
								</span>
								<span class="bp-share-text bp-share-label"><?php esc_html_e( 'Share to My Profile', 'buddypress-share' ); ?></span>
							</a>
						</div>
					<?php } ?>
					<?php if ( ! isset( $bp_reshare_settings['disable_message_reshare_activity'] ) ) { ?>
						<div class="bp-activity-share-btn" data-reshare="message" data-title="<?php esc_attr_e( 'Message', 'buddypress-share' ); ?>">
							<a href="<?php echo esc_attr( bp_loggedin_user_domain() . 'messages/compose/?activity_url=' . bp_loggedin_user_domain() . 'activity/' . bp_get_activity_id() ); ?>" class="button item-button bp-secondary-action" rel="nofollow">
								<span class="bp-activity-reshare-icon">
									<span class="dashicons dashicons-email"></span>
								</span>
								<span class="bp-share-text bp-share-label"><?php esc_html_e( 'Share to Message', 'buddypress-share' ); ?></span>					
							</a>
						</div>
					<?php } ?>
					<?php if ( ! isset( $bp_reshare_settings['disable_group_reshare_activity'] ) ) { ?>
						<div class="bp-activity-share-btn bp-activity-reshare-btn" data-reshare="groups" data-title="<?php esc_attr_e( 'Select Group', 'buddypress-share' ); ?>">
							<a class="button item-button bp-secondary-action bp-activity-share-button" data-bs-toggle="modal" data-bs-target="#activity-share-modal" data-activity-id="<?php echo esc_attr( bp_get_activity_id() ); ?>" rel="nofollow">
								<span class="bp-activity-reshare-icon">	
									<span class="dashicons dashicons-groups"></span>
								</span>
								<span class="bp-share-text bp-share-label"><?php esc_html_e( 'Share to a group', 'buddypress-share' ); ?></span>
							</a>
						</div>
					<?php } ?>
					<?php if ( ! isset( $bp_reshare_settings['disable_friends_reshare_activity'] ) ) { ?>
						<div class="bp-activity-share-btn bp-activity-reshare-btn" data-reshare="friends" data-title="<?php esc_attr_e( 'Select Friend', 'buddypress-share' ); ?>">
							<a class="button item-button bp-secondary-action bp-activity-share-button" data-bs-toggle="modal" data-bs-target="#activity-share-modal" data-activity-id="<?php echo esc_attr( bp_get_activity_id() ); ?>" rel="nofollow">
								<span class="bp-activity-reshare-icon">	
									<span class="dashicons dashicons-share-alt2"></span>
								</span>
								<span class="bp-share-text bp-share-label"><?php esc_html_e( 'Share with Friends', 'buddypress-share' ); ?></span>
							</a>
						</div>
					<?php } ?>
				<?php } ?>
				
				<?php
				$bp_share_services_enable = get_site_option( 'bp_share_services_enable' );
				if ( $bp_share_services_enable == 1 ) {
					?>
					<div class="bp-share-activity-share-to-wrapper">
					<?php
					if ( ! empty( $social_service ) ) {
						$this->render_social_sharing_buttons( $activity_link, $activity_title, $mail_subject, $social_service );
					} else {
						esc_html_e( 'Please enable share services!', 'buddypress-share' );
					}
				}
				do_action( 'bp_share_user_services', $services = array(), $activity_link, $activity_title );
				?>
			</div>
			<?php if ( in_array( $theme_name->template, $theme_support ) ) { ?>
				<div class="bp-share-service-popup-overlay"></div>
			<?php } ?>
			
			<?php
			if ( ! is_user_logged_in() ) {
				echo '<div>';
			}
			?>
			<script>
				jQuery( document ).ready( function () {
					var pop_active = '<?php echo isset( $extra_options['bp_share_services_open'] ) ? esc_html( $extra_options['bp_share_services_open'] ) : ''; ?>';
					if ( pop_active == 'on' ) {
						jQuery( '.bp-share' ).not('#bp_whatsapp_share, #bp_email_share').addClass( 'has-popup' );
					}
				} );
			</script>
			<?php
			if ( ! is_user_logged_in() ) {
				echo '</div>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render social sharing buttons with proper escaping and optimization.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $activity_link  Activity permalink.
	 * @param    string $activity_title Activity title.
	 * @param    string $mail_subject   Email subject.
	 * @param    array  $social_service Enabled social services.
	 */
	private function render_social_sharing_buttons( $activity_link, $activity_title, $mail_subject, $social_service ) {
		$description = '';
		$media       = '';
		$video       = '';
		$img         = '';
		
		$sharing_social_services = array(
			'Facebook' => array(
				'url' => esc_url( 'https://www.facebook.com/sharer.php?u=' . urlencode( $activity_link ) ),
				'icon' => 'dashicons-facebook-alt',
				'label' => __( 'Facebook', 'buddypress-share' )
			), 
			'Twitter' => array(
				'url' => esc_url( 'https://twitter.com/share?url=' . urlencode( $activity_link ) . '&text=' . urlencode( $activity_title ) ),
				'icon' => 'dashicons-twitter',
				'label' => __( 'X', 'buddypress-share' )
			),
			'Pinterest' => array(
				'url' => esc_url( 'https://pinterest.com/pin/create/bookmarklet/?media=' . urlencode( $media ) . '&url=' . urlencode( $activity_link ) . '&is_video=' . urlencode( $video ) . '&description=' . urlencode( $activity_title ) ),
				'icon' => 'dashicons-pinterest',
				'label' => __( 'Pinterest', 'buddypress-share' )
			),
			'Reddit' => array(
				'url' => esc_url( 'http://reddit.com/submit?url=' . urlencode( $activity_link ) . '&title=' . urlencode( $activity_title ) ),
				'icon' => 'dashicons-reddit',
				'label' => __( 'Reddit', 'buddypress-share' )
			),
			'WordPress' => array(
				'url' => esc_url( 'https://wordpress.com/wp-admin/press-this.php?u=' . urlencode( $activity_link ) . '&t=' . urlencode( $activity_title ) . '&s=' . urlencode( $description ) . '&i=' . urlencode( $img ) ),
				'icon' => 'dashicons-wordpress',
				'label' => __( 'WordPress', 'buddypress-share' )
			),
			'Pocket' => array(
				'url' => esc_url( 'https://getpocket.com/save?url=' . urlencode( $activity_link ) . '&title=' . urlencode( $activity_title ) ),
				'icon' => 'dashicons-arrow-down-alt2',
				'label' => __( 'Pocket', 'buddypress-share' )
			),
			'Telegram' => array(
				'url' => esc_url( 'https://t.me/share/url?url=' . urlencode( $activity_link ) . '&title=' . urlencode( $activity_title ) ),
				'icon' => 'dashicons-telegram',
				'label' => __( 'Telegram', 'buddypress-share' )
			),
			'Bluesky' => array(
				'url' => esc_url( 'https://bsky.app/intent/compose?text=' . urlencode( 'Check this out! ' . $activity_title . ' ' . $activity_link ) ),
				'icon' => 'dashicons-bluesky',
				'label' => __( 'Bluesky', 'buddypress-share' )
			),
			'Linkedin' => array(
				'url' => esc_url( 'http://www.linkedin.com/shareArticle?mini=true&url=' . urlencode( $activity_link ) . '&text=' . urlencode( $activity_title ) ),
				'icon' => 'dashicons-linkedin',
				'label' => __( 'Linkedin', 'buddypress-share' )
			),
			'Whatsapp' => array(
				'url' => esc_url( 'https://wa.me/?text=' . urlencode( $activity_link ) ),
				'icon' => 'dashicons-whatsapp',
				'label' => __( 'Whatsapp', 'buddypress-share' )
			)
		);

		// Add email sharing if enabled
		if ( isset( $social_service['E-mail'] ) && ! empty( $social_service['E-mail'] ) ) {
			$site_title = get_bloginfo( 'name' );
			$site_url   = home_url();

			$email_subject = 'New Activity on ' . esc_html( $site_title ) . ': ' . esc_html( $mail_subject );
			$email_body    = "Hi,\n\nI wanted to share this activity with you from " . esc_html( $site_title ) . ":\n\n" . esc_url( $activity_link ) . "\n\nYou can explore more activities here: " . esc_url( $site_url ) . "\n\nBest regards,\nThe " . esc_html( $site_title ) . ' Team';

			$sharing_social_services['E-mail'] = array(
				'url' => 'mailto:?subject=' . rawurlencode( $email_subject ) . '&body=' . rawurlencode( $email_body ),
				'icon' => 'dashicons-email',
				'label' => __( 'E-mail', 'buddypress-share' )
			);
		}

		foreach ( $sharing_social_services as $service => $details ) {
			if ( ! empty( $social_service[ $service ] ) ) {
				$service = ( 'E-mail' === $service ) ? 'Email' : $service;
				$button_id = "bp_" . strtolower( str_replace( '-', '_', $service ) ) . "_share";
				echo '<div class="bp-share-wrapper">';
				echo '<a class="button bp-share" id="' . esc_attr( $button_id ) . '" href="' . esc_url( $details['url'] ) . '" target="_blank">';
				echo '<span class="dashicons ' . esc_attr( $details['icon'] ) . '"></span>';
				echo '<span class="bp-share-label">' . esc_html( $details['label'] ) . '</span>';
				echo '</a>';
				echo '</div>';
			}
		}

		echo '<div class="bp-share-wrapper bp-cpoy-wrapper">';
		echo '<a class="button bp-share bp-cpoy" href="#" data-href="' . esc_attr( $activity_link ) . '" attr-display="no-popup"><span class="dashicons dashicons-admin-links"></span><span class="bp-share-label">' . esc_html__( 'Copy Link', 'buddypress-share' ) . '</span></a>';
		echo '<span class="tooltiptext tooltip-hide">' . esc_attr__( 'Link Copied!', 'buddypress-share' ) . '</span>';
		echo '</div>';
		echo '</div>';
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
		return $output . '
    xmlns:og="http://opengraphprotocol.org/schema/"
    xmlns:fb="http://www.facebook.com/2008/fbml"';
	}

	/**
	 * Share activity with OpenGraph meta values for better social media integration.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function bp_share_opengraph() {
		global $bp, $post;
		if ( ( bp_is_active( 'activity' ) && bp_is_current_component( 'activity' ) && ! empty( $bp->current_action ) && is_numeric( $bp->current_action ) && bp_is_single_activity() ) ) {
			$activity_img       = null;
			$activity_assets    = array();
			$activity_content   = null;
			$first_img_src      = null;
			$title              = null;
			$og_image           = null;
			$activity_permalink = null;
			$activity_obj       = new BP_Activity_Activity( $bp->current_action );
			$activity_permalink = bp_activity_get_permalink( $bp->current_action );
			preg_match_all( '/(src|width|height)=("[^"]*")/', $activity_obj->content, $result );

			if ( isset( $result[2] ) && ! empty( $result[2] ) ) {
				$result_new = array_map(
					function ( $i ) {
						return trim( $i, '"' );
					},
					$result[2]
				);
				foreach ( $result[1] as $key => $result_key ) {
					$activity_assets[ $result_key ] = $result_new[ $key ];
				}
			}
			if ( ! empty( $activity_obj->action ) ) {
				$content = $activity_obj->action;
			} else {
				$content = $activity_obj->content;
			}

			$content = explode( '<span', $content );
			$title   = wp_strip_all_tags( ent2ncr( trim( convert_chars( $content[0] ) ) ) );

			if ( ':' === substr( $title, -1 ) ) {
				$title = substr( $title, 0, -1 );
			}

			$activity_content = preg_replace( '#<ul class="rtmedia-list(.*?)</ul>#', ' ', $activity_obj->content );

			if ( ! empty( $activity_assets['src'] ) ) {
				$activity_content = explode( '<span>', $activity_content );
				$activity_content = wp_strip_all_tags( ent2ncr( trim( convert_chars( $activity_content[1] ) ) ) );
			} else {
				$activity_content = $activity_obj->content;
			}

			preg_match_all( '/<img.*?src\s*=.*?>/', $activity_obj->content, $matches );
			if ( isset( $matches[0][0] ) ) {
				preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $matches[0][0], $matches2 );
				if ( isset( $matches2[1][0] ) ) {
					$first_img_src = $matches2[1][0];
				}
			}

			$og_image = '';

			if ( class_exists( 'BP_Media' ) ) {
				$media_ids = bp_activity_get_meta( $activity_obj->id, 'bp_media_ids', true );
				$media_ids = explode( ',', $media_ids );

				if ( ! empty( $media_ids[0] ) ) {
					$media_data = new BP_Media( $media_ids[0] );
					$og_image   = esc_attr( wp_get_attachment_image_url( $media_data->attachment_id, 'full' ) );
				}
			}

			// Youzer media support.
			if ( class_exists( 'Youzer' ) || class_exists( 'Youzify' ) ) {
				$media_ids = ! empty( bp_activity_get_meta( $activity_obj->id, 'yz_attachments', true ) ) ? bp_activity_get_meta( $activity_obj->id, 'yz_attachments', true ) : bp_activity_get_meta( $activity_obj->id, 'youzify_attachments', true );
				if ( ! empty( $media_ids ) ) {
					$media_id = array_key_first( $media_ids );
					$og_image = esc_attr( wp_get_attachment_image_url( $media_id, 'full' ) );
				}
			}

			$activity_content   = wp_strip_all_tags( $activity_content );
			$activity_content   = stripslashes( $activity_content );

			if ( ! empty( $first_img_src ) ) {
				$og_image = $first_img_src;
			}
			?>
				<meta property="og:type"   content="article" />
				<meta property="og:url"    content="<?php echo esc_url( $activity_permalink ); ?>" />
				<meta property="og:title"  content="<?php echo esc_html( $title ); ?>" />
				<meta property="og:description" content="<?php echo esc_html( $activity_content ); ?>" />
				<meta property="og:image" content="<?php echo esc_url( $og_image ); ?>" />
				<meta property="og:image:secure_url" content="<?php echo esc_url( $og_image ); ?>" />
				<meta property="og:image:width" content="400" />
				<meta property="og:image:height" content="300" />
				<?php
		} else {
			return;
		}
	}

	/**
	 * Function to register custom actions for the custom activity types.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function bp_share_register_activity_actions() {
		$bp = buddypress();
		bp_activity_set_action(
			$bp->activity->id,
			'activity_share',
			esc_html__( 'Shared an activity', 'buddypress-share' ),
			array( $this, 'bp_share_activity_format_action_activity_reshare' ),
			esc_html__( 'Activity Share', 'buddypress-share' ),
			array( 'activity', 'group', 'member', 'member_groups' )
		);

		bp_activity_set_action(
			$bp->activity->id,
			'post_share',
			esc_html__( 'Shared an activity', 'buddypress-share' ),
			array( $this, 'bp_share_activity_format_action_activity_reshare' ),
			esc_html__( 'Post Activity Share', 'buddypress-share' ),
			array( 'activity', 'group', 'member', 'member_groups' )
		);

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
				esc_html__( 'Shared an activity', 'buddypress-share' ),
				array( $this, 'bp_share_activity_format_action_group_reshare' ),
				esc_html__( 'Post Activity Share', 'buddypress-share' ),
				array( 'activity', 'group', 'member', 'member_groups' )
			);
		}
	}

	/**
	 * Function formats the action of the reshared activity.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $action   Registered action.
	 * @param    object $activity Activity object.
	 * @return   string Activity action.
	 */
	public function bp_share_activity_format_action_activity_reshare( $action, $activity ) {
		$user_link = bp_core_get_userlink( $activity->user_id );
		// Set the Activity update posted in a Group action.
		$action = sprintf(
			/* translators: 1: the user link. */
			esc_html__( '%1$s Shared an activity', 'buddypress-share' ),
			$user_link
		);

		return $action;
	}

	/**
	 * Function formats the action of the activity shared in the group.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $action   Registered action.
	 * @param    object $activity Activity object.
	 * @return   string Activity action.
	 */
	public function bp_share_activity_format_action_group_reshare( $action, $activity ) {
		$user_link = bp_core_get_userlink( $activity->user_id );
		$group     = bp_groups_get_activity_group( $activity->item_id );
		if ( function_exists( 'buddypress' ) && version_compare( buddypress()->version, '12.0', '>=' ) ) {
			$group_link = '<a href="' . esc_url( bp_get_group_url( $group ) ) . '">' . esc_html( $group->name ) . '</a>';
		} else {
			$group_link = '<a href="' . esc_url( bp_get_group_permalink( $group ) ) . '">' . esc_html( $group->name ) . '</a>';
		}

		// Set the Activity update posted in a Group action.
		$action = sprintf(
			/* translators: 1: the user link. 2: the group link. */
			esc_html__( '%1$s shared an activity in the group %2$s', 'buddypress-share' ),
			$user_link,
			$group_link
		);

		return $action;
	}

	/**
	 * Function formats the activity of the post shared by the member.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string $content Activity content.
	 * @return   string Activity content.
	 */
	public function bp_activity_post_share_button_action( $content ) {
		$settings = $this->get_plugin_settings();
		$bp_reshare_settings = $settings['reshare_settings'];

		if ( is_single() && get_post_type() == 'post' && ! isset( $bp_reshare_settings['disable_post_reshare_activity'] ) ) {
			ob_start();

			$share_count = get_post_meta( get_the_ID(), 'share_count', true );
			$share_count = ( $share_count ) ? $share_count : '';
			?>
			<div class="bp-activity-post-share-btn bp-activity-share-btn generic-button">
				<a class="button item-button bp-secondary-action bp-activity-share-button" data-bs-toggle="modal" data-bs-target="#activity-share-modal" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>" rel="nofollow">
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

		return $content;
	}

	/**
	 * Function used to create a popup box holding the details of the activity/post being shared.
	 *
	 * Optimized to avoid loading groups and friends on every page load.
	 * Groups and friends are now loaded dynamically via AJAX when modal opens.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function bp_activity_share_popup_box() {
		/*  Activity Share Popup */
		$reshare_post_type = apply_filters( 'bp_activity_reshare_post_type', array( 'post' ) );

		if ( is_user_logged_in() && ( is_buddypress() || ( is_single() && in_array( get_post_type(), $reshare_post_type ) ) || apply_filters( 'bp_activity_reshare_action', false ) ) ) {
			
			if ( function_exists( 'buddypress' ) && version_compare( buddypress()->version, '12.0', '>=' ) ) {
				$user_name = esc_html( bp_members_get_user_slug( bp_loggedin_user_id() ) );
			} else {
				$user_name = esc_html( bp_core_get_username( bp_loggedin_user_id() ) );
			}
			?>
			<div class="modal fade activity-share-modal" id="activity-share-modal" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
					<button type="button" class="close activity-share-modal-close" data-bs-dismiss="modal" aria-label="Close">
						<i class="as-icon as-icon-times"></i>
					</button>
						<!-- Modal header -->
						<div class="modal-header">
							<div class="quick-post-header-filters-wrap">
								<div class="bp-activity-share-avatar">
									<a href="<?php echo esc_attr( bp_loggedin_user_domain() ); ?>">
										<?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
									</a>
									<span class="user-name"><?php echo esc_html( $user_name ); ?></span>
									<small class="user-status-text"><?php esc_html_e( 'Status Update', 'buddypress-share' ); ?></small>
									<small class="bp_activity_share_modal_error_message" hidden><?php esc_html_e( 'Please select post in field to post activity.', 'buddypress-share' );?></small>
								</div>
								<div class="bp-activity-share-filter">
									<div class="form-item">
										<div class="form-select">
											<label for="post-in"><?php esc_html_e( 'Post in', 'buddypress-share' ); ?></label>
											<select id="post-in" name="postIn">
												<option value="0"><?php esc_html_e( 'My Profile', 'buddypress-share' ); ?></option>
												<option value="message"><?php esc_html_e( 'Message', 'buddypress-share' ); ?></option>
												<!-- Groups and friends will be loaded dynamically via AJAX -->
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- Modal Body -->
						<div class="modal-body">
							<form class="form">
								<div class="form-textarea"> 
									<?php
									$placeholder_text = sprintf(
										/* translators: Placeholder is for the username */
										esc_html__( 'Hi %s! Write something here. Use @ to mention someone...', 'buddypress-share' ),
										$user_name
									);
									?>
									<textarea id="bp-activity-share-text" name="bp-activity-share-text" class="" placeholder="<?php echo esc_attr( $placeholder_text ); ?>" maxlength="1000" spellcheck="false"></textarea>
								</div>

								<input type="hidden" id="bp-reshare-activity-id" name="activity-id" value="" />
								<input type="hidden" id="bp-reshare-activity-user-id" name="user-id" value="<?php echo bp_loggedin_user_id(); //phpcs:ignore ?>" />
								
								<?php if ( is_buddypress() || apply_filters( 'bp_activity_reshare_action', false ) ) : ?>
									<input type="hidden" id="bp-reshare-activity-current-component" name="current_component" value="<?php echo bp_current_component(); //phpcs:ignore ?>" />
									<input type="hidden" id="bp-reshare-type" name="bp-reshare-type" value="activity_share" />
								<?php else : ?>
									<input type="hidden" id="bp-reshare-activity-current-component" name="current_component" value="activity" />
									<input type="hidden" id="bp-reshare-type" name="bp-reshare-type" value="post_share" />
								<?php endif; ?>
							</form>
							<div id="bp-activity-share-widget-box-status-header">
								<?php $this->bp_activity_share_single_post_formate(); ?>
							</div>
						</div>
						<!-- Modal Footer -->
						<div class="modal-footer">
							<div class="bp-activity-share-post-footer-actions">
								<p class="button small void bp-activity-share-close"><?php esc_html_e( 'Discard', 'buddypress-share' ); ?></p>
								<p class="button small secondary bp-activity-share-activity"><?php esc_html_e( 'Post', 'buddypress-share' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Function used to format the post inside the popup box at the time of resharing activity/post.
	 * 
	 * @since 1.0.0
	 * @access public
	 */
	public function bp_activity_share_single_post_formate() {
		$reshare_post_type = apply_filters( 'bp_activity_reshare_post_type', array( 'post' ) );

		if ( ( is_single() && in_array( get_post_type(), $reshare_post_type ) ) || apply_filters( 'bp_activity_reshare_action', false ) ) {
			?>
			<div class="post-preview animate-slide-down entry-wrapper ">
				<?php if ( has_post_thumbnail() ) { ?>

					<div class="entry-thumbnail">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>

				<?php } ?>
				
				<div class="post-preview-info fixed-height entry-content">
					<div class="post-preview-info-top entry-header">
						<p class="post-preview-timestamp">
							<?php $this->bp_activity_post_meta(); ?>
						</p>
						<p class="post-preview-title entry-title">
							<?php the_title( '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a>' ); ?>
						</p>
					</div>
					<div class="post-preview-info-bottom post-open-body">
						<p class="post-preview-text entry-excerpt">
							<?php the_excerpt(); ?>
						</p>
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="post-preview-link color-primary read-more"><?php echo esc_html__( 'Read More', 'buddypress-share' ) . '...'; ?></a>							
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Handles the AJAX request for resharing an activity or post in BuddyPress.
	 *
	 * This function processes resharing, validates requests, and updates share counts.
	 * Enhanced with better validation and security for large sites.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void Outputs JSON response and terminates the script.
	 */
	public function bp_activity_create_reshare_ajax() {
		// Verify nonce for security
		if ( ! wp_verify_nonce( $_POST['_ajax_nonce'] ?? '', 'bp-activity-share-nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'buddypress-share' ) ) );
		}

		// Bail early if user is not logged in
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'User not logged in.', 'buddypress-share' ) ) );
		}

		// Validate and sanitize required parameters
		$user_id       = get_current_user_id();
		$activity_id   = absint( $_POST['activity_id'] ?? 0 );
		$activity_type = sanitize_key( $_POST['type'] ?? '' );

		if ( ! $activity_id || ! $activity_type ) {
			wp_send_json_error( array( 'message' => __( 'Missing required parameters.', 'buddypress-share' ) ) );
		}

		// Validate activity type
		$allowed_types = array( 'activity_share', 'post_share' );
		if ( ! in_array( $activity_type, $allowed_types, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid activity type.', 'buddypress-share' ) ) );
		}

		// Validate activity exists and user has permission
		if ( 'activity_share' === $activity_type ) {
			$activity = new BP_Activity_Activity( $activity_id );
			if ( empty( $activity->id ) ) {
				wp_send_json_error( array( 'message' => __( 'Activity not found.', 'buddypress-share' ) ) );
			}
		} elseif ( 'post_share' === $activity_type ) {
			$post = get_post( $activity_id );
			if ( empty( $post ) || 'publish' !== $post->post_status ) {
				wp_send_json_error( array( 'message' => __( 'Post not found or not published.', 'buddypress-share' ) ) );
			}
		}

		// Handle user mentions in activity content
		$activity_content = sanitize_textarea_field( wp_unslash( $_POST['activity_content'] ?? '' ) );
		$activity_in      = absint( $_POST['activity_in'] ?? 0 );
		
		if ( isset( $_POST['activity_in_type'] ) && 'user' === $_POST['activity_in_type'] ) {
			$username = function_exists( 'buddypress' ) && version_compare( buddypress()->version, '12.0', '>=' ) 
				? bp_members_get_user_slug( $activity_in ) 
				: bp_core_get_username( $activity_in );
			
			if ( $username ) {
				$activity_content = "@{$username} \r\n{$activity_content}";
			}
			$activity_in = 0;
		}

		// Validate group permissions if sharing to group
		if ( $activity_in > 0 ) {
			if ( ! bp_is_active( 'groups' ) || ! groups_is_user_member( $user_id, $activity_in ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to post in this group.', 'buddypress-share' ) ) );
			}
		}

		// Prepare activity arguments
		$activity_args = array(
			'user_id'           => $user_id,
			'component'         => ( $activity_in > 0 ) ? 'groups' : 'activity',
			'type'              => $activity_type,
			'content'           => $activity_content,
			'secondary_item_id' => $activity_id,
			'item_id'           => $activity_in,
		);

		// Add the activity
		$new_activity_id = bp_activity_add( $activity_args );

		if ( ! $new_activity_id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create activity.', 'buddypress-share' ) ) );
		}

		// Update share count with better atomic operation
		$meta_key    = 'share_count';
		$is_post     = ( 'post_share' === $activity_type );
		
		if ( $is_post ) {
			$current_count = (int) get_post_meta( $activity_id, $meta_key, true );
			$new_count = $current_count + 1;
			update_post_meta( $activity_id, $meta_key, $new_count );
		} else {
			$current_count = (int) bp_activity_get_meta( $activity_id, $meta_key, true );
			$new_count = $current_count + 1;
			bp_activity_update_meta( $activity_id, $meta_key, $new_count );
		}

		wp_send_json_success( array( 
			'share_count' => $new_count,
			'activity_id' => $new_activity_id,
			'message' => __( 'Activity shared successfully.', 'buddypress-share' )
		) );
	}

	/**
	 * Renders the shared activity or post content in the activity stream.
	 *
	 * Optimized version that removes caching for better performance on large sites.
	 * Direct execution without caching is more efficient for logged-in users.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @global   object $activities_template Holds the activity template data.
	 * @return   void
	 */
	public function bp_activity_share_entry_content() {
		global $activities_template;

		$settings = $this->get_plugin_settings();
		$reshare_share_activity = isset( $settings['reshare_settings']['reshare_share_activity'] ) ? $settings['reshare_settings']['reshare_share_activity'] : 'parent';

		$activity_id   = $activities_template->activity->id;
		$activity_type = $activities_template->activity->type;

		// Direct execution without caching for better performance on large sites
		if ( $activity_type === 'activity_share' && $activities_template->activity->secondary_item_id != 0 ) {
			$secondary_item_id = $activities_template->activity->secondary_item_id;
			$this->bp_render_shared_activity( $secondary_item_id, $reshare_share_activity );
		}

		if ( $activity_type === 'post_share' && $activities_template->activity->secondary_item_id != 0 ) {
			$secondary_item_id = $activities_template->activity->secondary_item_id;
			$this->bp_render_shared_post( $secondary_item_id );
		}
	}

	/**
	 * Render shared BuddyPress activity.
	 *
	 * Optimized to avoid global filters and reduce memory usage.
	 * 
	 * @since    1.5.1
	 * @access   public
	 * @param    int    $activity_id Activity ID to render.
	 * @param    string $display_mode Parent or child display mode.
	 */
	public function bp_render_shared_activity( $activity_id, $display_mode ) {
		global $activities_template;
		
		// Store the original template
		$temp_activities_template = $activities_template;
		
		// Direct activity fetch without global filters for better performance
		$shared_activity = new BP_Activity_Activity( $activity_id );
		
		if ( empty( $shared_activity->id ) ) {
			return;
		}
		
		// Create minimal activities query for this specific activity
		$args = array( 
			'include' => $activity_id,
			'per_page' => 1,
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
			
			// Add user details if GamiPress is active
			if ( function_exists( 'gamipress_bp_user_details_display' ) ) {
				$user_id = $activities_template->activity->user_id;
				gamipress_bp_user_details_display( $user_id, 'activity' );
			}
			
			// Display the activity
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
		
		// Restore filters and template
		if ( 'parent' === $display_mode ) {
			add_action( 'bp_activity_entry_content', array( $this, 'bp_activity_share_entry_content' ) );
		}
		$activities_template = $temp_activities_template;
	}

	/**
	 * Render shared WordPress post.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @param    int $post_id Post ID to render.
	 */
	public function bp_render_shared_post( $post_id ) {
		$query = new WP_Query(
			array(
				'p'         => $post_id,
				'post_type' => get_post_type( $post_id ),
			)
		);

		if ( ! $query->have_posts() ) {
			return;
		}

		while ( $query->have_posts() ) {
			$query->the_post();
			?>
			<div id="bp-reshare-activity-<?php echo esc_attr( get_the_ID() ); ?>" 
				class="post-reshare-item-container activity-reshare-item-container" 
				data-bp-activity-id="<?php echo esc_attr( bp_get_activity_id() ); ?>"> 
				<div class="post-preview animate-slide-down entry-wrapper">
					<?php if ( has_post_thumbnail() ) { ?>
						<div class="entry-thumbnail">
							<?php the_post_thumbnail( 'large' ); ?>
						</div>
					<?php } ?>
					<div class="post-preview-info fixed-height entry-content">
						<div class="post-preview-info-top entry-header">
							<p class="post-preview-timestamp">
								<?php $this->bp_activity_post_meta(); ?>
							</p>
							<p class="post-preview-title entry-title">
								<?php the_title( '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a>' ); ?>
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
			</div>
			<?php
		}

		wp_reset_postdata();
	}

	/**
	 * Display post meta information for shared posts.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function bp_activity_post_meta() {
		// Before post meta action.
		do_action( 'bp_activity_share_before_post_meta' );

		// Post date.
		printf( '<span class="link date-links"><i class="as-icon-calendar"></i><a href="%s">%s</a></span>', esc_url( get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) ), get_the_date() );

		// translators: used between list items, there is a space after the comma.
		$categories_list = get_the_category_list( esc_html__( ', ', 'buddypress-share' ) );
		if ( $categories_list ) {
			printf( '<span class="link cat-links"><i class="as-icon-folder"></i>%1$s</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// translators: used between list items, there is a space after the comma.
		if ( is_single() ) {
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'buddypress-share' ) );
			if ( $tags_list ) {
				printf( '<span class="link tags-links"><i class="uil-tag-alt"></i>%1$s</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		// After post meta action.
		do_action( 'bp_activity_share_after_post_meta' );
	}

	/**
	 * Function to render the share count in the post/activity shared.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @param    array  $atts    Shortcode attributes.
	 * @param    string $content Activity content.
	 * @return   string Activity share count HTML.
	 */
	public function bp_activity_post_reshare( $atts, $content = null ) {
		ob_start();

		$share_count = get_post_meta( get_the_ID(), 'share_count', true );
		$share_count = ( $share_count ) ? $share_count : '';
		?>
		<div class="bp-activity-post-share-btn bp-activity-share-btn generic-button">
			<a class="button item-button bp-secondary-action bp-activity-share-button" data-bs-toggle="modal" data-bs-target="#activity-share-modal" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>" rel="nofollow">
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
	 * Embed bp activity link preview data in rest api activity endpoint.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    object $response Get response data.
	 * @param    object $request  Get request data.
	 * @param    array  $activity Get activity data.
	 * @return   object Modified response with share count data.
	 */
	public function bp_activity_post_reshare_data_embed_rest_api( $response, $request, $activity ) {
		$bp_activity_link_data                     = bp_activity_get_meta( $activity->id, 'share_count', true );
		$response->data['bp_activity_share_count'] = $bp_activity_link_data;
		return $response;
	}

	/**
	 * Load the single activity loop for the reshare object via AJAX.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   void Outputs JSON response with activity content.
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
		$result['contents'] = ob_get_contents();
		ob_end_clean();
		wp_send_json_success( $result );
	}

	/**
	 * AJAX handler for loading groups and friends when modal opens.
	 *
	 * Optimized to load data only when needed, reducing initial page load time.
	 * Limits results for better performance on large sites.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @return   void Outputs JSON response with groups and friends data.
	 */
	public function get_user_share_options_ajax() {
		check_ajax_referer( 'bp-activity-share-nonce', '_ajax_nonce' );
		
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'User not logged in.', 'buddypress-share' ) ) );
		}
		
		$user_id = get_current_user_id();
		$data = array();
		
		// Load groups only when requested - limit to 50 for performance
		if ( bp_is_active( 'groups' ) ) {
			$groups = groups_get_groups( array( 
				'user_id' => $user_id,
				'per_page' => 50,
				'populate_extras' => false // Skip extra queries
			) );
			$data['groups'] = isset( $groups['groups'] ) ? $groups['groups'] : array();
		}
		
		// Load friends only when requested - limit to 50 for performance
		if ( bp_is_active( 'friends' ) && function_exists( 'friends_get_friend_user_ids' ) ) {
			$friends_ids = friends_get_friend_user_ids( $user_id );
			$friends_data = array();
			
			// Get friend details for first 50 friends only
			$limited_friends = array_slice( $friends_ids, 0, 50 );
			foreach ( $limited_friends as $friend_id ) {
				$user_data = get_userdata( $friend_id );
				if ( $user_data ) {
					$friends_data[] = array(
						'id' => $friend_id,
						'display_name' => $user_data->display_name
					);
				}
			}
			$data['friends'] = $friends_data;
		}
		
		wp_send_json_success( $data );
	}

	/**
	 * Batch load share counts for multiple activities to reduce database queries.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @param    array $activities Array of activity objects.
	 * @return   array Modified activities with share_count property.
	 */
	public function batch_load_share_counts( $activities ) {
		if ( empty( $activities ) ) {
			return $activities;
		}
		
		$activity_ids = wp_list_pluck( $activities, 'id' );
		
		// Single query to get all share counts
		global $wpdb, $bp;
		$table = $bp->activity->table_name_meta;
		
		$placeholders = implode( ',', array_fill( 0, count( $activity_ids ), '%d' ) );
		$query = $wpdb->prepare(
			"SELECT activity_id, meta_value FROM {$table} WHERE meta_key = 'share_count' AND activity_id IN ({$placeholders})",
			$activity_ids
		);
		
		$share_counts = $wpdb->get_results( $query, OBJECT_K );
		
		// Attach to activity objects
		foreach ( $activities as &$activity ) {
			$activity->share_count = isset( $share_counts[ $activity->id ] ) 
				? (int) $share_counts[ $activity->id ]->meta_value 
				: 0;
		}
		
		return $activities;
	}

	/**
	 * Preload shared activities and posts to avoid N+1 queries.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @param    array $activities Array of activity objects.
	 * @return   void
	 */
	public function preload_shared_activities( $activities ) {
		$share_activity_ids = array();
		$post_ids = array();
		
		foreach ( $activities as $activity ) {
			if ( $activity->type === 'activity_share' && $activity->secondary_item_id ) {
				$share_activity_ids[] = $activity->secondary_item_id;
			} elseif ( $activity->type === 'post_share' && $activity->secondary_item_id ) {
				$post_ids[] = $activity->secondary_item_id;
			}
		}
		
		// Preload shared activities
		if ( ! empty( $share_activity_ids ) ) {
			bp_has_activities( array( 'include' => $share_activity_ids ) );
		}
		
		// Preload shared posts
		if ( ! empty( $post_ids ) ) {
			get_posts( array( 
				'include' => $post_ids, 
				'post_type' => 'any',
				'suppress_filters' => false
			) );
		}
	}

	/**
	 * Clear plugin settings cache when settings are updated.
	 *
	 * @since    1.5.1
	 * @access   public
	 */
	public function clear_settings_cache() {
		$this->cached_settings = null;
	}

	/**
	 * Get a specific shared activity by ID without global filters.
	 *
	 * @since    1.5.1
	 * @access   public
	 * @param    int $activity_id Activity ID.
	 * @return   BP_Activity_Activity|null Activity object or null if not found.
	 */
	public function get_shared_activity( $activity_id ) {
		$activity = new BP_Activity_Activity( $activity_id );
		return ! empty( $activity->id ) ? $activity : null;
	}

	/**
	 * Validate user permissions for sharing activities.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    int    $user_id     User ID.
	 * @param    int    $activity_id Activity ID.
	 * @param    string $activity_type Activity type.
	 * @return   bool True if user can share, false otherwise.
	 */
	private function can_user_share_activity( $user_id, $activity_id, $activity_type ) {
		// Basic permission check
		if ( ! $user_id ) {
			return false;
		}

		// Check if activity exists and is accessible
		if ( 'activity_share' === $activity_type ) {
			$activity = $this->get_shared_activity( $activity_id );
			if ( ! $activity ) {
				return false;
			}
			
			// Check if activity is hidden or private
			if ( $activity->hide_sitewide ) {
				return false;
			}
		}

		// Additional permission checks can be added here
		return apply_filters( 'bp_activity_share_user_can_share', true, $user_id, $activity_id, $activity_type );
	}

	/**
	 * Log plugin errors for debugging purposes.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    string $message Error message.
	 * @param    array  $data    Additional error data.
	 */
	private function log_error( $message, $data = array() ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[BP Activity Share Pro] ' . $message . ' ' . wp_json_encode( $data ) );
		}
	}

	/**
	 * Sanitize and validate share options data.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @param    array $options Raw options data.
	 * @return   array Sanitized options data.
	 */
	private function sanitize_share_options( $options ) {
		$sanitized = array();
		
		if ( isset( $options['groups'] ) && is_array( $options['groups'] ) ) {
			$sanitized['groups'] = array_map( 'absint', $options['groups'] );
		}
		
		if ( isset( $options['friends'] ) && is_array( $options['friends'] ) ) {
			$sanitized['friends'] = array_map( 'absint', $options['friends'] );
		}
		
		return $sanitized;
	}
}