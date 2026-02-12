<?php
/**
 * Asset management class for BuddyPress Activity Share Pro.
 *
 * Handles loading of CSS and JavaScript assets with local fallbacks.
 *
 * @link       http://wbcomdesigns.com
 * @since      1.5.3
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 */

/**
 * Asset management class.
 *
 * @since      1.5.3
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Buddypress_Share_Assets {

	/**
	 * Get Font Awesome style handle.
	 *
	 * Uses dashicons as a fallback for Font Awesome icons.
	 *
	 * @since    1.5.3
	 * @return   void
	 */
	public static function enqueue_icon_library() {
		// Allow sites to use their own Font Awesome if already loaded
		if ( wp_style_is( 'font-awesome', 'registered' ) || 
		     wp_style_is( 'fontawesome', 'registered' ) ||
		     wp_style_is( 'fa', 'registered' ) ) {
			return;
		}

		// Check if site has local Font Awesome copy
		$local_fa = BP_ACTIVITY_SHARE_PLUGIN_PATH . 'assets/vendor/fontawesome/css/all.min.css';
		if ( file_exists( $local_fa ) ) {
			wp_enqueue_style(
				'bp-share-font-awesome',
				BP_ACTIVITY_SHARE_PLUGIN_URL . 'assets/vendor/fontawesome/css/all.min.css',
				array(),
				'5.15.4'
			);
			return;
		}

		// Allow CDN usage via filter
		$use_cdn = apply_filters( 'bp_share_use_cdn_assets', false );
		if ( $use_cdn ) {
			wp_enqueue_style(
				'bp-share-font-awesome',
				'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
				array(),
				'5.15.4'
			);
			
			// Add privacy attributes
			add_filter( 'style_loader_tag', array( __CLASS__, 'add_cdn_attributes' ), 10, 3 );
			return;
		}

		// Use dashicons as fallback
		wp_enqueue_style( 'dashicons' );
		
		// Add inline CSS for icon mapping
		$icon_mapping = self::get_dashicon_mapping_css();
		wp_add_inline_style( 'dashicons', $icon_mapping );
	}

	/**
	 * Get dashicon mapping CSS.
	 *
	 * Maps Font Awesome classes to dashicons.
	 *
	 * @since    1.5.3
	 * @return   string CSS for icon mapping.
	 */
	private static function get_dashicon_mapping_css() {
		$css = '
		/* Font Awesome to Dashicons mapping */
		.bp-share-social-icon .fa-facebook:before,
		.bp-share-social-icon .fab.fa-facebook:before { content: "\f304"; font-family: dashicons; }
		
		.bp-share-social-icon .fa-twitter:before,
		.bp-share-social-icon .fab.fa-twitter:before,
		.bp-share-social-icon .fab.fa-x-twitter:before { content: "\f301"; font-family: dashicons; }
		
		.bp-share-social-icon .fa-linkedin:before,
		.bp-share-social-icon .fab.fa-linkedin:before { content: "\f207"; font-family: dashicons; }
		
		.bp-share-social-icon .fa-envelope:before,
		.bp-share-social-icon .fas.fa-envelope:before { content: "\f465"; font-family: dashicons; }
		
		.bp-share-social-icon .fa-whatsapp:before,
		.bp-share-social-icon .fab.fa-whatsapp:before { content: "\f242"; font-family: dashicons; }
		
		.bp-share-social-icon .fa-pinterest:before,
		.bp-share-social-icon .fab.fa-pinterest:before { content: "\f209"; font-family: dashicons; }
		
		.bp-share-social-icon .fa-reddit:before,
		.bp-share-social-icon .fab.fa-reddit:before { content: "\f303"; font-family: dashicons; }
		
		.bp-share-social-icon .fa-wordpress:before,
		.bp-share-social-icon .fab.fa-wordpress:before { content: "\f324"; font-family: dashicons; }
		
		.bp-share-social-icon .fa-link:before,
		.bp-share-social-icon .fas.fa-link:before { content: "\f103"; font-family: dashicons; }
		
		.bp-share-social-icon .fa-share:before,
		.bp-share-social-icon .fas.fa-share:before { content: "\f237"; font-family: dashicons; }
		';
		
		return $css;
	}

	/**
	 * Add CDN attributes for privacy and performance.
	 *
	 * @since    1.5.3
	 * @param    string $tag    The link tag.
	 * @param    string $handle The style handle.
	 * @param    string $href   The stylesheet URL.
	 * @return   string Modified link tag.
	 */
	public static function add_cdn_attributes( $tag, $handle, $href ) {
		if ( 'bp-share-font-awesome' === $handle && strpos( $href, 'cdnjs.cloudflare.com' ) !== false ) {
			$tag = str_replace( 
				' href=', 
				' crossorigin="anonymous" referrerpolicy="no-referrer" href=', 
				$tag 
			);
		}
		return $tag;
	}

	/**
	 * Get social icon class.
	 *
	 * Returns the appropriate icon class based on available icon library.
	 *
	 * @since    1.5.3
	 * @param    string $service The social service name.
	 * @return   string Icon class.
	 */
	public static function get_social_icon_class( $service ) {
		$has_fa = wp_style_is( 'bp-share-font-awesome', 'enqueued' ) || 
		          wp_style_is( 'font-awesome', 'enqueued' ) ||
		          wp_style_is( 'fontawesome', 'enqueued' );

		$service = strtolower( $service );
		
		// Font Awesome classes
		if ( $has_fa ) {
			$fa_icons = array(
				'facebook'  => 'fab fa-facebook',
				'x'         => 'fab fa-x-twitter',
				'twitter'   => 'fab fa-twitter',
				'linkedin'  => 'fab fa-linkedin',
				'pinterest' => 'fab fa-pinterest',
				'reddit'    => 'fab fa-reddit',
				'wordpress' => 'fab fa-wordpress',
				'pocket'    => 'fab fa-get-pocket',
				'telegram'  => 'fab fa-telegram',
				'whatsapp'  => 'fab fa-whatsapp',
				'e-mail'    => 'fas fa-envelope',
				'email'     => 'fas fa-envelope',
				'copy-link' => 'fas fa-link',
			);
			
			return isset( $fa_icons[ $service ] ) ? $fa_icons[ $service ] : 'fas fa-share';
		}
		
		// Dashicon classes (with Font Awesome class for CSS mapping)
		$dashicons = array(
			'facebook'  => 'dashicons dashicons-facebook fab fa-facebook',
			'x'         => 'dashicons dashicons-twitter fab fa-x-twitter',
			'twitter'   => 'dashicons dashicons-twitter fab fa-twitter',
			'linkedin'  => 'dashicons dashicons-linkedin fab fa-linkedin',
			'pinterest' => 'dashicons dashicons-pinterest fab fa-pinterest',
			'reddit'    => 'dashicons dashicons-reddit fab fa-reddit',
			'wordpress' => 'dashicons dashicons-wordpress fab fa-wordpress',
			'whatsapp'  => 'dashicons dashicons-format-chat fab fa-whatsapp',
			'e-mail'    => 'dashicons dashicons-email fas fa-envelope',
			'email'     => 'dashicons dashicons-email fas fa-envelope',
			'copy-link' => 'dashicons dashicons-admin-links fas fa-link',
		);
		
		return isset( $dashicons[ $service ] ) ? $dashicons[ $service ] : 'dashicons dashicons-share fas fa-share';
	}
}