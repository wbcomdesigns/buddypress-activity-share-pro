<?php
/**
 * BuddyPress Share Helper Functions
 * 
 * Simple helper functions for conditional asset loading
 * 
 * @package BuddyPress_Share
 * @since 1.5.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check if we should use minified assets
 *
 * @return bool True if minified assets should be used
 */
function bp_share_use_minified() {
    // Use minified unless we're debugging
    $use_minified = ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) && 
                    ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
    
    return apply_filters( 'bp_share_use_minified_assets', $use_minified );
}

/**
 * Get asset suffix for minification
 *
 * @return string '.min' if minified should be used, empty string otherwise
 */
function bp_share_get_asset_suffix() {
    return bp_share_use_minified() ? '.min' : '';
}

/**
 * Enqueue style with automatic minification and RTL support
 *
 * @param string $handle     Style handle
 * @param string $src        Style URL (without .css extension)
 * @param array  $deps       Dependencies
 * @param string $version    Version
 * @param string $media      Media type
 */
function bp_share_enqueue_style( $handle, $src, $deps = array(), $version = false, $media = 'all' ) {
    $suffix = bp_share_get_asset_suffix();
    
    // Enqueue main style
    wp_enqueue_style( $handle, $src . $suffix . '.css', $deps, $version, $media );
    
    // Add RTL support - WordPress will automatically load -rtl version when is_rtl() is true
    if ( is_rtl() ) {
        wp_style_add_data( $handle, 'rtl', 'replace' );
        wp_style_add_data( $handle, 'suffix', $suffix );
    }
}

/**
 * Enqueue script with automatic minification
 *
 * @param string $handle     Script handle
 * @param string $src        Script URL (without .js extension)
 * @param array  $deps       Dependencies
 * @param string $version    Version
 * @param bool   $in_footer  Whether to load in footer
 */
function bp_share_enqueue_script( $handle, $src, $deps = array(), $version = false, $in_footer = true ) {
    $suffix = bp_share_get_asset_suffix();
    
    wp_enqueue_script( $handle, $src . $suffix . '.js', $deps, $version, $in_footer );
}

/**
 * Check if BuddyPress functions are available
 *
 * @return bool
 */
function bp_share_is_bp_active() {
    return function_exists( 'buddypress' ) || function_exists( 'bp_is_active' );
}

/**
 * Safe wrapper for bp_get_activity_id
 *
 * @return int|false
 */
function bp_share_get_activity_id() {
    if ( function_exists( 'bp_get_activity_id' ) ) {
        return bp_get_activity_id();
    }
    return false;
}

/**
 * Safe wrapper for bp_get_activity_type
 *
 * @return string|false
 */
function bp_share_get_activity_type() {
    if ( function_exists( 'bp_get_activity_type' ) ) {
        return bp_get_activity_type();
    }
    return false;
}

/**
 * Safe wrapper for bp_is_active
 *
 * @param string $component Component name
 * @return bool
 */
function bp_share_is_component_active( $component ) {
    if ( function_exists( 'bp_is_active' ) ) {
        return bp_is_active( $component );
    }
    return false;
}

/**
 * Safe wrapper for is_buddypress
 *
 * @return bool
 */
function bp_share_is_buddypress_page() {
    if ( function_exists( 'is_buddypress' ) ) {
        return is_buddypress();
    }
    return false;
}

/**
 * Safe wrapper for bp_get_activity_feed_item_title
 *
 * @return string
 */
function bp_share_get_activity_title() {
    if ( function_exists( 'bp_get_activity_feed_item_title' ) ) {
        return bp_get_activity_feed_item_title();
    }
    return '';
}

/**
 * Read a bundled SVG file from disk and return its raw markup.
 *
 * Markup is cached per request so repeated icon calls do not re-read the file.
 *
 * @since 2.3.0
 * @param string $path Absolute path to the SVG file.
 * @return string Raw SVG markup, or empty string if the file is missing.
 */
function bp_share_read_svg_file( $path ) {
	static $cache = array();

	if ( isset( $cache[ $path ] ) ) {
		return $cache[ $path ];
	}

	$markup = '';
	if ( is_string( $path ) && '' !== $path && file_exists( $path ) ) {
		$markup = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local bundled SVG asset, not a remote request.
		$markup = trim( $markup );
	}

	$cache[ $path ] = $markup;
	return $markup;
}

/**
 * Wrap a bundled SVG in an <i> box and apply the icon class + a11y attributes.
 *
 * The SVG is wrapped in an <i class="$icon_class"> element so the plugin's
 * existing icon-box CSS (sizing, circle/baricon backgrounds, brand colours —
 * all of which historically targeted the Font Awesome <i> element) keeps
 * working unchanged. The inner <svg> is sized to 1em and inherits currentColor,
 * so the <i>'s font-size drives the glyph footprint exactly as the icon font.
 *
 * @since 2.3.0
 * @param string $svg        Raw SVG markup.
 * @param string $icon_class Class for the wrapping <i> element.
 * @param string $label      Accessible label. When empty the icon is decorative.
 * @return string Icon markup ( <i><svg/></i> ), or empty string if SVG missing.
 */
function bp_share_apply_svg_attrs( $svg, $icon_class = 'bpas-icon', $label = '' ) {
	if ( '' === $svg || '<svg' !== substr( $svg, 0, 4 ) ) {
		return '';
	}

	// Mark the inner SVG as decorative; the <i> wrapper carries the semantics.
	$svg = preg_replace( '/^<svg/', '<svg class="bpas-svg" aria-hidden="true" focusable="false"', $svg, 1 );

	$i_attrs = ' class="' . esc_attr( $icon_class ) . '"';
	if ( '' !== $label ) {
		$i_attrs .= ' role="img" aria-label="' . esc_attr( $label ) . '"';
	} else {
		$i_attrs .= ' aria-hidden="true"';
	}

	return '<i' . $i_attrs . '>' . $svg . '</i>';
}

/**
 * Map of supported Lucide chrome/UI icons to their bundled file names.
 *
 * Lucide is used ONLY for chrome/UI glyphs (share, close, calendar, folder,
 * mail, link, printer). Network brand marks use bp_share_brand_svg() instead.
 *
 * @since 2.3.0
 * @return array<string,string> Icon name => file slug.
 */
function bp_share_lucide_icons() {
	return array(
		'share-2'  => 'share-2',
		'share'    => 'share',
		'x'        => 'x',
		'calendar' => 'calendar',
		'folder'   => 'folder',
		'mail'     => 'mail',
		'link'     => 'link',
		'printer'  => 'printer',
	);
}

/**
 * Return inline markup for a bundled Lucide chrome/UI icon.
 *
 * Decorative by default ( aria-hidden ). Pass $label for a semantic icon
 * ( icon-only button ) to expose an accessible name.
 *
 * @since 2.3.0
 * @param string $name       Lucide icon name ( see bp_share_lucide_icons() ).
 * @param string $extra_class Extra class( es ) to add alongside the base .bpas-icon class.
 * @param string $label      Optional accessible label. Empty means decorative.
 * @return string Inline icon markup ( trusted bundled asset ).
 */
function bp_share_icon( $name, $extra_class = '', $label = '' ) {
	$icons = bp_share_lucide_icons();
	$slug  = isset( $icons[ $name ] ) ? $icons[ $name ] : 'share';

	$path = BP_ACTIVITY_SHARE_PLUGIN_PATH . 'assets/icons/lucide/' . $slug . '.svg';
	$svg  = bp_share_read_svg_file( $path );

	$css_class = 'bpas-icon bpas-icon-' . sanitize_html_class( $name );
	if ( '' !== $extra_class ) {
		$css_class .= ' ' . $extra_class;
	}

	return bp_share_apply_svg_attrs( $svg, $css_class, $label );
}

/**
 * Echo a bundled Lucide chrome/UI icon.
 *
 * @since 2.3.0
 * @param string $name        Lucide icon name.
 * @param string $extra_class Extra class( es ).
 * @param string $label       Optional accessible label.
 * @return void
 */
function bp_share_the_icon( $name, $extra_class = '', $label = '' ) {
	echo bp_share_icon( $name, $extra_class, $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted bundled SVG asset, attributes escaped in helper.
}

/**
 * Map of supported network brand marks to their bundled SVG file slug.
 *
 * Accepts the various aliases used across the codebase ( service labels,
 * post-type keys, fa-derived names ) and normalises them to a single file.
 *
 * @since 2.3.0
 * @return array<string,string> Alias ( lowercased ) => brand file slug.
 */
function bp_share_brand_map() {
	return array(
		'facebook'  => 'facebook',
		'x'         => 'x',
		'x-twitter' => 'x',
		'twitter'   => 'x',
		'linkedin'  => 'linkedin',
		'pinterest' => 'pinterest',
		'reddit'    => 'reddit',
		'wordpress' => 'wordpress',
		'pocket'    => 'pocket',
		'telegram'  => 'telegram',
		'bluesky'   => 'bluesky',
		'whatsapp'  => 'whatsapp',
	);
}

/**
 * Return inline markup for a bundled network brand mark.
 *
 * Brand marks are kept as their own bundled SVGs ( Lucide carries no brand
 * icons ). Decorative by default; the visible service label provides the name.
 *
 * @since 2.3.0
 * @param string $service     Service alias ( Facebook, x, telegram, whatsapp ).
 * @param string $extra_class Extra class( es ) to add alongside the base class.
 * @param string $label       Optional accessible label. Empty means decorative.
 * @return string Inline icon markup, or empty string for an unknown service.
 */
function bp_share_brand_svg( $service, $extra_class = '', $label = '' ) {
	$map = bp_share_brand_map();
	$key = strtolower( (string) $service );

	if ( ! isset( $map[ $key ] ) ) {
		return '';
	}

	$slug = $map[ $key ];
	$path = BP_ACTIVITY_SHARE_PLUGIN_PATH . 'public/images/brand/' . $slug . '.svg';
	$svg  = bp_share_read_svg_file( $path );

	$css_class = 'bpas-icon bpas-icon-brand bpas-icon-brand-' . sanitize_html_class( $slug );
	if ( '' !== $extra_class ) {
		$css_class .= ' ' . $extra_class;
	}

	return bp_share_apply_svg_attrs( $svg, $css_class, $label );
}

/**
 * Return inline icon markup for ANY share service ( brand mark or chrome glyph ).
 *
 * This is the single entry point the templates use. It resolves brand marks
 * first ( Facebook, X ) then falls back to the Lucide chrome glyphs for the
 * non-brand services ( E-mail -> mail, Copy-Link -> link, Print -> printer ).
 *
 * @since 2.3.0
 * @param string $service     Service identifier/label/key.
 * @param string $extra_class Extra class( es ).
 * @param string $label       Optional accessible label. Empty means decorative.
 * @return string Inline icon markup.
 */
function bp_share_service_icon( $service, $extra_class = '', $label = '' ) {
	$key = strtolower( (string) $service );

	// Brand marks take priority.
	$brand = bp_share_brand_svg( $key, $extra_class, $label );
	if ( '' !== $brand ) {
		return $brand;
	}

	// Non-brand share services map to Lucide chrome glyphs.
	$chrome = array(
		'e-mail'    => 'mail',
		'email'     => 'mail',
		'mail'      => 'mail',
		'copy-link' => 'link',
		'copy'      => 'link',
		'link'      => 'link',
		'print'     => 'printer',
		'printer'   => 'printer',
	);

	$name = isset( $chrome[ $key ] ) ? $chrome[ $key ] : 'share';
	return bp_share_icon( $name, $extra_class, $label );
}
