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
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/public
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @access public
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Buddypress_Share_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Buddypress_Share_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( ! wp_style_is( 'wb-font-awesome', 'enqueued' ) ) {
			wp_enqueue_style( 'wb-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), $this->version, 'all' );
		}
		wp_enqueue_style( 'bootstrap-css', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'select2-css', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'icons-css', plugin_dir_url( __FILE__ ) . 'css/as-icons.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/buddypress-share-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @access public
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Buddypress_Share_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Buddypress_Share_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		global $bp_reshare_settings;
		$bp_reshare_settings    = get_site_option( 'bp_reshare_settings' );
		$reshare_share_activity = isset( $bp_reshare_settings['reshare_share_activity'] ) ? $bp_reshare_settings['reshare_share_activity'] : 'parent';

		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'bootstrap-js', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'select2-js', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/buddypress-share-public.js', array( 'jquery' ), $this->version, false );

		wp_localize_script(
			$this->plugin_name,
			'bp_activity_sjare_vars',
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
	 * Display share button in front page.
	 *
	 * @access public
	 * @since    1.0.0
	 */
	public function bp_activity_share_button_dis() {
		$all_services             = get_site_option( 'bp_share_all_services_disable' );
		$bp_share_services_enable = get_site_option( 'bp_share_services_enable' );

		$theme_support = apply_filters( 'buddyPress_reactions_theme_suuport', array( 'reign-theme', 'buddyx-pro' ) );
		$theme_name    = wp_get_theme();

		if ( is_user_logged_in() && 'enable' === $all_services && $bp_share_services_enable == 1 ) {
			if ( in_array( $theme_name->template, $theme_support ) ) {
				add_action( 'bp_activity_entry_dropdown_toggle_meta', array( $this, 'bp_share_activity_filter' ), 999 );
				add_action( 'bp_activity_entry_top_meta', array( $this, 'bp_share_activity_filter' ), 999 );
			} else {
				add_action( 'bp_activity_entry_meta', array( $this, 'bp_share_activity_filter' ), 999 );
			}
		}
		if ( is_user_logged_in() ) {
			add_action( 'bp_activity_entry_meta', array( $this, 'bp_share_inner_activity_filter' ) );
		}
	}

	public function bp_share_inner_activity_filter() {

		$share_count = bp_activity_get_meta( bp_get_activity_id(), 'share_count', true );
		$share_count = ( $share_count ) ? $share_count : 0;
		?>
		<div class="bp-activity-share-btn generic-button">
			<a class="button item-button bp-secondary-action bp-activity-share-button" data-bs-toggle="modal" data-bs-target="#activity-share-modal" data-activity-id="<?php echo esc_attr( bp_get_activity_id() ); ?>" rel="nofollow">
				<span class="bp-activity-reshare-icon">	
					<i class="as-icon as-icon-share-square"></i>
				</span>
				<span class="bp-share-text"><?php esc_html_e( 'Share', 'buddypress-share' ); ?></span>
				<span id="bp-activity-reshare-count-<?php echo esc_attr( bp_get_activity_id() ); ?>" class="reshare-count bp-activity-reshare-count"><?php echo esc_html( $share_count ); ?></span>
			</a>
		</div>
		<?php
	}

	/**
	 * BP Share activity filter
	 *
	 * @access public
	 * @since    1.0.0
	 */
	function bp_share_activity_filter() {
		global $activities_template;
		$social_service = get_site_option( 'bp_share_services' );
		$extra_options  = get_site_option( 'bp_share_services_extra' );
		$activity_type  = bp_get_activity_type();
		$activity_link  = $activities_template->activity->primary_link . bp_get_activity_slug() . '/' . $activities_template->activity->id . '/';
		$activity_title = bp_get_activity_feed_item_title(); // use for description : bp_get_activity_feed_item_description()
		$plugin_path    = plugins_url();
		if ( ! is_user_logged_in() ) {
			echo '<div class = "activity-meta" >';
		}

		$bpas_icon_color_settings = get_option( 'bpas_icon_color_settings' );
		if ( isset( $bpas_icon_color_settings['icon_style'] ) ) {
			$style = $bpas_icon_color_settings['icon_style'];
		} else {
			$style = 'circle';
		}

		$updated_text = apply_filters( 'bpas_share_button_text_override', 'Share' );
		if ( isset( $updated_text ) ) {
			$share_button_text = $updated_text;
		}

		$theme_support = apply_filters( 'buddyPress_reactions_theme_suuport', array( 'reign-theme', 'buddyx-pro' ) );
		$theme_name    = wp_get_theme();

		$bp_share_class = '';
		if ( in_array( $theme_name->template, $theme_support ) ) {
			$bp_share_class = 'bp-share-service-popup';
		}
		?>
		<div class="bp-share-btn generic-button"> 
			<a class="button item-button bp-secondary-action bp-share-button" rel="nofollow"><span><?php esc_html_e( 'Share', 'buddypress-share' ); ?></span></a>
		</div>
		</div>
		<div class="service-buttons bpas-social-share-container <?php echo esc_html( $activity_type . ' ' . $bp_share_class . ' ' . $style ); ?>" style="display: none;">
		<?php
		if ( ! empty( $social_service ) ) {
			if ( isset( $social_service ) && ! empty( $social_service['Facebook'] ) ) {
				echo '<a href="https://www.facebook.com/sharer.php?u=' . esc_url( $activity_link ) . '" class="bp-share" id="bp_facebook_share"><span class="dashicons dashicons-facebook-alt"></span></a>';
			}
			if ( isset( $social_service ) && ! empty( $social_service['Twitter'] ) ) {
				$twitter_title = urlencode( html_entity_decode( get_the_title(), ENT_COMPAT, 'UTF-8' ) );
				echo '<a class="bp-share" id="bp_twitter_share"  href="https://twitter.com/share?url=' . esc_url( $activity_link ) . '&text=' . esc_html( $activity_title ) . '"><span class="dashicons dashicons-twitter"></span></a>';
			}
			if ( isset( $social_service ) && ! empty( $social_service['Pinterest'] ) ) {
				$media = '';
				$video = '';
				echo '<a class="bp-share" id="bp_pinterest_share"  href="https://pinterest.com/pin/create/bookmarklet/?media=' . esc_url( $media ) . '&url=' . esc_url( $activity_link ) . '&is_video=' . esc_url( $video ) . '&description=' . esc_html( $activity_title ) . '"><span class="dashicons dashicons-pinterest
				"></span></a>';
			}
			if ( isset( $social_service ) && ! empty( $social_service['Reddit'] ) ) {
				echo '<a class="bp-share" id="bp_reddit_share"  href="http://reddit.com/submit?url=' . esc_url( $activity_link ) . '&title=' . esc_html( $activity_title ) . '"><span class="dashicons dashicons-reddit"></span></a>';
			}
			if ( isset( $social_service ) && ! empty( $social_service['WordPress'] ) ) {
				$description = '';
				$img         = '';
				echo '<a class="bp-share" id="bp_wordpress_share"  href="https://wordpress.com/wp-admin/press-this.php?u=' . esc_url( $activity_link ) . '&t=' . esc_html( $activity_title ) . '&s=' . esc_url( $description ) . '&i= ' . esc_url( $img ) . ' "><span class="dashicons dashicons-wordpress"></span></a>';
			}
			if ( isset( $social_service ) && ! empty( $social_service['Pocket'] ) ) {
				$description = '';
				$img         = '';
				echo '<a class="bp-share" id="bp_pocket_share"  href="https://getpocket.com/save?url=' . esc_url( $activity_link ) . '&title=' . esc_html( $activity_title ) . '"><span class="dashicons dashicons-arrow-down-alt2"></span></a>';
			}
			if ( isset( $social_service ) && ! empty( $social_service['Linkedin'] ) ) {
				echo '<a class="bp-share" id="bp_linkedin_share" href="http://www.linkedin.com/shareArticle?mini=true&url=' . esc_url( $activity_link ) . '&text=' . esc_html( $activity_title ) . '"><span class="dashicons dashicons-linkedin"></span></a>';
			}
			if ( isset( $social_service ) && ! empty( $social_service['Whatsapp'] ) ) {
				echo '<a class="bp-share" id="bp_whatsapp_share" href="https://api.whatsapp.com/send?text=' . esc_url( $activity_link ) . '&image_sharer=1"><span class="dashicons dashicons-whatsapp"></span></a>';
			}
			if ( isset( $social_service ) && ! empty( $social_service['E-mail'] ) ) {
				$email = 'mailto:?subject=' . esc_url( $activity_link ) . '&body=Check out this site: ' . esc_html( $activity_title ) . '" title="Share by Email';
				echo '<a class="bp-share" id="bp_email_share" href="' . esc_url( $email ) . '"><span class="dashicons dashicons-email"></span></a>';
			}
			echo '<div class="bp-cpoy-wrapper">';
			echo '<a class="bp-share bp-cpoy" href="#" data-href="' . esc_attr( $activity_link ) . '" attr-display="no-popup"><span class="dashicons dashicons-admin-page"></span></a>';
			echo '<span class="tooltiptext tooltip-hide">' . esc_attr__( 'Link Copied!', 'buddypress-share' ) . '</span>';
			echo '</div>';
		} else {
			esc_html_e( 'Please enable share services!', 'buddypress-share' );
		}
			do_action( 'bp_share_user_services', $services = array(), $activity_link, $activity_title );
		?>
		</div>
		<?php if ( in_array( $theme_name->template, $theme_support ) ) { ?>
			<div class="bp-share-service-popup-overlay"></div>
		<?php } ?>

		<div>
			<script>
				jQuery( document ).ready( function () {
					var pop_active = '<?php echo isset( $extra_options['bp_share_services_open'] ) ? esc_html( $extra_options['bp_share_services_open'] ) : ''; ?>';
					if ( pop_active == 'on' ) {
						jQuery( '.bp-share' ).addClass( 'has-popup' );
					}
				} );
			</script>
			<?php
			if ( ! is_user_logged_in() ) {
				echo '</div>';
			}
	}

	public function bp_share_doctype_opengraph( $output ) {
		return $output . '
    xmlns:og="http://opengraphprotocol.org/schema/"
    xmlns:fb="http://www.facebook.com/2008/fbml"';
	}

	/**
	 * Share activity with og meta values
	 *
	 * @return
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
			$extra_options      = get_site_option( 'bp_share_services_extra' );
			$enable_user_avatar = false;

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

	public function bp_share_activity_format_action_activity_reshare( $action, $activity ) {
		$user_link = bp_core_get_userlink( $activity->user_id );
		// Set the Activity update posted in a Group action.
		$action = sprintf(
			/* translators: 1: the user link. 2: the group link. */
			esc_html__( '%1$s Shared an activity', 'buddypress-share' ),
			$user_link,
		);

		return $action;
	}

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

	public function bp_activity_post_share_button_action( $content ) {
		$bp_reshare_settings = get_site_option( 'bp_reshare_settings' );

		if ( is_single() && get_post_type() == 'post' && ! isset( $bp_reshare_settings['disable_post_reshare_activity'] ) ) {
			ob_start();

			$share_count = get_post_meta( get_the_ID(), 'share_count', true );
			$share_count = ( $share_count ) ? $share_count : 0;
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

	public function bp_activity_share_popup_box() {

		/*  Activity Share Popup */
		$reshare_post_type = apply_filters( 'bp_activity_reshare_post_type', array( 'post' ) );

		if ( is_user_logged_in() && ( is_buddypress() || ( is_single() && in_array( get_post_type(), $reshare_post_type ) ) || apply_filters( 'bp_activity_reshare_action', false ) ) ) {
			$bp_reshare_settings = get_site_option( 'bp_reshare_settings' );

			$groups = array();
			if ( bp_is_active( 'groups' ) ) {
				$groups = groups_get_groups( array( 'user_id' => bp_loggedin_user_id() ) );
			}
			$friends = ( function_exists( 'friends_get_friend_user_ids' ) ) ? friends_get_friend_user_ids( bp_loggedin_user_id() ) : array();
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
									<?php
									if ( function_exists( 'buddypress' ) && version_compare( buddypress()->version, '12.0', '>=' ) ) {
										echo esc_html( bp_members_get_user_slug( bp_loggedin_user_id() ) );
									} else {
										echo esc_html( bp_core_get_username( bp_loggedin_user_id() ) );
									}
									?>
									<small class="user-status-text"><?php esc_html_e( 'Status Update', 'buddypress-share' ); ?></small>
								</div>
								<div class="bp-activity-share-filter">
									<div class="form-item">
										<div class="form-select">
											<label for="post-in"><?php esc_html_e( 'Post In', 'buddypress-share' ); ?></label>
											<select id="post-in" name="postIn">
												<option value="0"><?php esc_html_e( 'My Profile', 'buddypress-share' ); ?></option>
												<option value="message"><?php esc_html_e( 'Message', 'buddypress-share' ); ?></option>
												<?php if ( ! empty( $groups ) ) : ?>
													<optgroup label="<?php esc_html_e( 'Group lists', 'buddypress-share' ); ?>">
													<?php foreach ( $groups['groups'] as $group ) : ?>
														<option value="<?php echo esc_attr( $group->id ); ?>" data-type="group"><?php echo esc_attr( $group->name ); ?></option>
													<?php endforeach; ?>
													</optgroup>
												<?php endif; ?>
												
												<?php if ( ! empty( $friends ) ) : ?>
													<optgroup label="<?php esc_html_e( 'Friend lists', 'buddypress-share' ); ?>">
													<?php foreach ( $friends as $friend ) : ?>
														<option value="<?php echo esc_attr( $friend ); ?>" data-type="user"><?php echo esc_attr( get_user_by( 'ID', $friend )->display_name ); ?></option>
													<?php endforeach; ?>
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
							<form class="form">
								<div class="form-textarea">
									<textarea id="bp-activity-share-text" name="bp-activity-share-text" class=" " placeholder="<?php esc_html_e( 'Hi admin! Write something here, use @ to mention someone...', 'buddypress-share' ); ?>" maxlength="1000" spellcheck="false"></textarea>
								</div>
								<input type="hidden" id="bp-reshare-activity-id" name="activity-id" value="" />
								<input type="hidden" id="bp-reshare-activity-user-id" name="user-id" value="<?php echo bp_loggedin_user_id(); //phpcs:ignore ?>" />
								
								
								<?php if ( is_buddypress() ) : ?>
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

	public function bp_activity_create_reshare_ajax() {
		check_ajax_referer( 'bp-activity-share-nonce', '_ajax_nonce' );

		$user_id = get_current_user_id();

		// Add the activity.
		if ( isset( $_POST['activity_in_type'] ) && $_POST['activity_in_type'] == 'user' ) {
			if ( function_exists( 'buddypress' ) && version_compare( buddypress()->version, '12.0', '>=' ) ) {
				$username = bp_members_get_user_slug( $_POST['activity_in'] );
			} else {
				$username = bp_core_get_username( $_POST['activity_in'] );
			}
			$_POST['activity_content'] = "@$username \r\n" . $_POST['activity_content'];
			$_POST['activity_in']      = '0';

		}

		$activity_id = bp_activity_add(
			array(
				'user_id'           => $user_id,
				'component'         => ( isset( $_POST['activity_in'] ) && $_POST['activity_in'] != 0 ) ? 'groups' : 'activity',
				'type'              => $_POST['type'],
				'content'           => $_POST['activity_content'],
				'secondary_item_id' => $_POST['activity_id'],
				'item_id'           => $_POST['activity_in'],

			)
		);

		if ( $activity_id && $_POST['type'] == 'activity_share' ) {

			/* Count reshare activity */
			$share_count = bp_activity_get_meta( $_POST['activity_id'], 'share_count', true );
			if ( ! $share_count ) {
				bp_activity_update_meta( $_POST['activity_id'], 'share_count', 1 );
			} else {
				bp_activity_update_meta( $_POST['activity_id'], 'share_count', $share_count + 1 );
			}
		}

		/* Share Post as activity */
		if ( $activity_id && $_POST['type'] == 'post_share' ) {

			/* Count reshare activity */
			$share_count = get_post_meta( $_POST['activity_id'], 'share_count', true );
			if ( ! $share_count ) {
				update_post_meta( $_POST['activity_id'], 'share_count', 1 );
			} else {
				update_post_meta( $_POST['activity_id'], 'share_count', $share_count + 1 );
			}
		}
		die();
	}


	public function bp_activity_share_get_where_conditions( $where_conditions ) {
		unset( $where_conditions['filter_sql'] );
		unset( $where_conditions['scope_query_sql'] );
		return $where_conditions;
	}

	public function bp_activity_share_entry_content() {
		global $activities_template, $bp_reshare_settings;

		if ( ! empty( $bp_reshare_settings ) ) {
			$bp_reshare_settings = get_site_option( 'bp_reshare_settings' );
		}
		$reshare_share_activity = isset( $bp_reshare_settings['reshare_share_activity'] ) ? $bp_reshare_settings['reshare_share_activity'] : 'parent';

		$activity_id   = $activities_template->activity->id;
		$activity_type = $activities_template->activity->type;

		if ( $activity_type == 'activity_share' && $activities_template->activity->secondary_item_id != 0 ) {
			$secondary_item_id        = $activities_template->activity->secondary_item_id;
			$temp_activities_template = $activities_template;
			$args                     = array( 'in' => $secondary_item_id );
			add_filter( 'bp_activity_get_where_conditions', array( $this, 'bp_activity_share_get_where_conditions' ), 999, 1 );
			$_REQUEST['search_terms'] = $secondary_item_id;
			if ( bp_has_activities( $args ) ) {
				if ( $reshare_share_activity == 'parent' ) {
					remove_action( 'bp_activity_entry_content', array( $this, 'bp_activity_share_entry_content' ) );
				}
				while ( bp_activities() ) :
					bp_the_activity();
					?>
					<div id="bp-reshare-activity-<?php echo bp_get_activity_id(); //phpcs:ignore ?>" class="activity-reshare-item-container"> 
						<div class="activity-item"> 
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
							<!--div class="content-action">
								<div class="meta-line">
									<p class="meta-line-text"><?php // echo wbcom_get_comment_count( bp_get_activity_id() ) . ' ' . esc_html( 'Comments' ); ?></p>
								</div>
								<div class="meta-line">
									<p class="meta-line-text"><?php // echo wbcom_get_share_count( bp_get_activity_id() ) . ' ' . esc_html( 'Share' ); ?></p>
								</div>
							</div-->
						</div>
					</div>					
					<?php
				endwhile;
			}
			remove_filter( 'bp_activity_get_where_conditions', array( $this, 'bp_activity_share_get_where_conditions' ), 999, 1 );
			if ( $reshare_share_activity == 'parent' ) {
				add_action( 'bp_activity_entry_content', array( $this, 'bp_activity_share_entry_content' ) );
			}

			$activities_template = $temp_activities_template;
		}

		/* Post share activity type */
		if ( $activity_type == 'post_share' && $activities_template->activity->secondary_item_id != 0 ) {
			$post_id = $activities_template->activity->secondary_item_id;
			$query   = new WP_Query(
				array(
					'p'         => $post_id,
					'post_type' => get_post_type( $post_id ),
				)
			);
			// The Loop
			if ( $query->have_posts() ) {

				while ( $query->have_posts() ) {
					$query->the_post();
					?>
					<div id="bp-reshare-activity-<?php echo get_the_ID(); ?>" class="post-reshare-item-container activity-reshare-item-container"> 
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
					</div>
					<?php
				}
			}
			/* Restore original Post Data */
			wp_reset_postdata();
		}
	}

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

	public function bp_activity_post_reshare( $atts, $content = null ) {
		ob_start();

		$share_count = get_post_meta( get_the_ID(), 'share_count', true );
		$share_count = ( $share_count ) ? $share_count : 0;
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
	 * @param  object $response get response data.
	 * @param  object $request get request data.
	 * @param  array  $activity get activity data.
	 * @return $response
	 */
	public function bp_activity_post_reshare_data_embed_rest_api( $response, $request, $activity ) {
		$bp_activity_link_data                     = bp_activity_get_meta( $activity->id, 'share_count', true );
		$response->data['bp_activity_share_count'] = $bp_activity_link_data;
		return $response;
	}
}
