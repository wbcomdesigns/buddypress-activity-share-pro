<?php
/**
 * Asset management class for BuddyPress Activity Share Pro.
 *
 * Historically this class enqueued Font Awesome (CDN or local) with a
 * dashicons fallback. As of 2.3.0 the plugin ships inline Lucide chrome SVGs
 * and bundled brand SVGs (see bp_share_icon() / bp_share_brand_svg()), so no
 * icon font / CDN is loaded on the frontend. The public methods are kept as
 * compatibility shims for any third-party code that still references them.
 *
 * @link       http://wbcomdesigns.com
 * @since      1.5.3
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * Enqueue the icon library.
	 *
	 * No-op since 2.3.0 — icons are inline SVGs, no font or CDN is required.
	 * Retained as a compatibility shim for any code that still calls it.
	 *
	 * @since    1.5.3
	 * @return   void
	 */
	public static function enqueue_icon_library() {
		// Intentionally empty. Icons are bundled inline SVGs as of 2.3.0.
	}

	/**
	 * Get an icon identifier for a social service.
	 *
	 * Previously returned a Font Awesome / dashicons class string. Now returns
	 * the stable lowercase service slug used by bp_share_service_icon() to
	 * resolve the bundled brand SVG (or Lucide chrome glyph). Callers that
	 * render this value should pass it through bp_share_service_icon().
	 *
	 * @since    1.5.3
	 * @param    string $service The social service name.
	 * @return   string Lowercased service slug.
	 */
	public static function get_social_icon_class( $service ) {
		$service = strtolower( (string) $service );

		// Normalise the legacy "twitter" alias to the canonical X slug.
		if ( 'twitter' === $service ) {
			$service = 'x';
		}

		return $service;
	}
}
