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
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/buddypress-share-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Display share button in front page.
	 *
	 * @access public
	 * @since    1.0.0
	 */
	public function bp_activity_share_button_dis() {
		$all_services = get_site_option( 'bp_share_all_services_disable' );		
		if ( is_user_logged_in() && 'enable' === $all_services ) {
			add_action( 'bp_activity_entry_meta', array( $this, 'bp_share_activity_filter' ),999 );
			add_action( 'bp_activity_entry_meta', array( $this, 'bp_share_inner_activity_filter' ) );
		}
	}
	
	public function bp_share_inner_activity_filter() {
		?>
		<div class="bp-activity-share-btn generic-button">
			<a class="button item-button bp-secondary-action bp-activity-share-button dashicons-controls-repeat" rel="nofollow"><span><?php esc_html_e( 'Share', 'buddypress-share' ); ?></span></a>
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
		$service        = get_site_option( 'bp_share_services' );
		$extra_options  = get_site_option( 'bp_share_services_extra' );
		$activity_type  = bp_get_activity_type();
		$activity_link  = bp_get_activity_thread_permalink();
		$activity_title = bp_get_activity_feed_item_title(); // use for description : bp_get_activity_feed_item_description()
		$plugin_path    = plugins_url();
		if ( ! is_user_logged_in() ) {
			echo '<div class = "activity-meta" >';
		}

		$updated_text = apply_filters( 'bpas_share_button_text_override', 'Share' );
		if ( isset( $updated_text ) ) {
			$share_button_text = $updated_text;
		}
		?>
		<div class="bp-share-btn generic-button">
			<a class="button item-button bp-secondary-action bp-share-button" rel="nofollow"><span><?php esc_html_e( 'Share', 'buddypress-share' ); ?></span></a>
		</div>
		</div>
		<div class="service-buttons <?php echo esc_html( $activity_type ); ?>" style="display: none;">
		<?php
		if ( ! empty( $service ) ) {
			foreach ( $service as $key => $value ) {
				if ( isset( $key ) && 'bp_share_facebook' === $key && 1 === $value[ 'chb_' . $key ] ) {
					echo '<a target="blank" class="bp-share" href="https://www.facebook.com/sharer/sharer.php?u=' . esc_url( $activity_link ) . '" rel="facebook"><span class="fa-stack fa-lg"><i class="' . esc_attr( $value['service_icon'] ) . '"></i></span></a>';
				}
				if ( isset( $key ) && 'bp_share_twitter' === $key && 1 === $value[ 'chb_' . $key ] ) {
					echo '<a target="blank" class="bp-share" href="http://twitter.com/share?text=' . esc_html( $activity_title ) . '&url=' . esc_url( $activity_link ) . '" rel="twitter"><span class="fa-stack fa-lg"><i class="' . esc_attr( $value['service_icon'] ) . '"></i></span></a>';
				}
				if ( isset( $key ) && 'bp_share_pinterest' === $key && 1 === $value[ 'chb_' . $key ] ) {
					$media = '';
					$video = '';
					echo '<a target="blank" class="bp-share" href="https://pinterest.com/pin/create/bookmarklet/?media=' . esc_url( $media ) . '&url=' . esc_url( $activity_link ) . '&is_video=' . esc_url( $video ) . '&description=' . esc_html( $activity_title ) . '" rel="penetrest"><span class="fa-stack fa-lg"><i class="' . esc_attr( $value['service_icon'] ) . '"></i></span></a>';
				}
				if ( isset( $key ) && 'bp_share_linkedin' === $key && 1 === $value[ 'chb_' . $key ] ) {
					echo '<a target="blank" class="bp-share" href="http://www.linkedin.com/shareArticle?url=' . esc_url( $activity_link ) . '&title=' . esc_html( $activity_title ) . '"><span class="fa-stack fa-lg"><i class="' . esc_attr( $value['service_icon'] ) . '"></i></span></a>';
				}
				if ( isset( $key ) && 'bp_share_reddit' === $key && 1 === $value[ 'chb_' . $key ] ) {
					echo '<a target="blank" class="bp-share" href="http://reddit.com/submit?url=' . esc_url( $activity_link ) . '&title=' . esc_html( $activity_title ) . '"><span class="fa-stack fa-lg"><i class="' . esc_attr( $value['service_icon'] ) . '"></i></span></a>';
				}
				if ( isset( $key ) && 'bp_share_wordpress' === $key && 1 === $value[ 'chb_' . $key ] ) {
					$description = '';
					$img         = '';
					echo '<a target="blank" class="bp-share" href="https://wordpress.com/wp-admin/press-this.php?u=' . esc_url( $activity_link ) . '&t=' . esc_html( $activity_title ) . '&s=' . esc_url( $description ) . '&i= ' . esc_url( $img ) . ' "><span class="fa-stack fa-lg"><i class="' . esc_attr( $value['service_icon'] ) . '"></i></span></a>';
				}
				if ( isset( $key ) && 'bp_share_pocket' === $key && 1 === $value[ 'chb_' . $key ] ) {
					echo '<a target="blank" class="bp-share" href="https://getpocket.com/save?url=' . esc_url( $activity_link ) . '&title=' . esc_html( $activity_title ) . '"><span class="fa-stack fa-lg"><i class="' . esc_attr( $value['service_icon'] ) . '"></i></span></a>';
				}
				if ( isset( $key ) && 'bp_share_email' === $key && 1 === $value[ 'chb_' . $key ] ) {
					$email = 'mailto:?subject=' . esc_url( $activity_link ) . '&body=Check out this site: ' . esc_html( $activity_title ) . '" title="Share by Email';
					echo '<a class="bp-share" href="' . esc_url( $email ) . '" attr-display="no-popup"><span class="fa-stack fa-lg"><i class="' . esc_attr( $value['service_icon'] ) . '"></i></span></a>';
				}
				if ( isset( $key ) && 'bp_share_whatsapp' === $key && 1 === $value[ 'chb_' . $key ] ) {
					echo '<a target="blank" class="bp-share" href="https://api.whatsapp.com/send?text=' . esc_url( $activity_link ) . '&image_sharer=1" data-action="share/whatsapp/share" rel="whatsapp"><span class="fa-stack fa-lg"><i class="' . esc_attr( $value['service_icon'] ) . '"></i></span></a>';
				}

				if ( isset( $key ) && 'bp_copy_activity' === $key && 1 === $value[ 'chb_' . $key ] ) {
					echo '<a class="bp-share bp-cpoy" href="#" data-href="' . esc_url( $activity_link ) . '" attr-display="no-popup"><span class="fa-stack fa-lg"><i class="fa fa-copy"></i></span></a>';
					echo '<span class="tooltiptext tooltip-hide">' . esc_attr__( 'Link Copied!', 'buddypress-share' ) . '</span>';

				}
			}
		} else {
			esc_html_e( 'Please enable share services!', 'buddypress-share' );
		}
			do_action( 'bp_share_user_services', $services = array(), $activity_link, $activity_title );
		?>
		</div>
		<div>
			<script>
				jQuery( document ).ready( function () {
					var pop_active = '<?php echo isset( $extra_options['bp_share_services_open'] ) ? esc_html( $extra_options['bp_share_services_open'] ) : ''; ?>';
					if ( pop_active == 1 ) {
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
					function( $i ) {
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
			$title   = strip_tags( ent2ncr( trim( convert_chars( $content[0] ) ) ) );

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

			// Youzer media support
			if ( class_exists( 'Youzer' ) ) {
				$media_ids = bp_activity_get_meta( $activity_obj->id, 'yz_attachments', true );
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
	
	public function bp_activity_share_popup_box() {
		
		$groups = groups_get_groups( array('user_id' =>bp_loggedin_user_id()));		
		?>
		<div class="bp-activity-share-popup-container">
			<div class="bp-activity-share-popup-box share-box-popup animate-slide-down">
				<div class="bp-activity-share-popup_close-button">
					<svg viewBox="0 0 12 12" preserveAspectRatio="xMinYMin meet" class="xm-popup_close-button-icon"><path d="M12,9.6L9.6,12L6,8.399L2.4,12L0,9.6L3.6,6L0,2.4L2.4,0L6,3.6L9.6,0L12,2.4L8.399,6L12,9.6z"></path></svg>
				</div>
				<div class="bp-activity-share-popup-section">
					<div class="bp-activity-share-post-header">
						<div class="bp-activity-share-avatar">
							<a href="<?php echo bp_loggedin_user_domain(); ?>">
								<?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
							</a>
						</div>
						<div class="bp-activity-share-filter">
							<div class="form-item">
								<div class="form-select">
									<label for="post-in"><?php esc_html_e( 'Post In', 'buddypress-share' );?></label>
									<select id="post-in" name="postIn">
										<option value="0"><?php esc_html_e( 'My Profile', 'buddypress-share' );?></option>
										<?php if ( !empty($groups)):?>
											<?php foreach( $groups['groups'] as $group ): ?>
												<option value="<?php echo $group->id; ?>"><?php echo $group->name; ?></option>
											<?php endforeach;?>
										<?php endif;?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="bp-activity-share-post-body">
						<form class="form">
							<div class="form-textarea">
								<textarea name="bp-activity-share-text" class=" " placeholder="<?php esc_html_e( 'Hi admin! Write something here, use @ to mention someone...', 'buddypress-share' );?>" maxlength="1000" spellcheck="false"></textarea>
							</div>
						</form>
					</div>
					<div class="bp-activity-share-post-footer">
						<div class="bp-activity-share-post-footer-actions">
							<p class="button small void"><?php esc_html_e( 'Discard', 'buddypress-share' );?></p>
							<p class="button small secondary"><?php esc_html_e( 'Post', 'buddypress-share' );?></p></div>
					</div>
				</div>
			</div>
		</div>
		
		
		<?php
	}
	
	
}
