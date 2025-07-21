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