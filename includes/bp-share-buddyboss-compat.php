<?php
/**
 * BuddyBoss Platform Compatibility Functions
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 * @since      1.5.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if BuddyBoss Platform is active
 *
 * @since 1.5.2
 * @return bool
 */
function bp_share_is_buddyboss() {
	return defined( 'BP_PLATFORM_VERSION' );
}

/**
 * Get platform name for display
 *
 * @since 1.5.2
 * @return string
 */
function bp_share_get_platform_name() {
	if ( bp_share_is_buddyboss() ) {
		return 'BuddyBoss Platform';
	}
	return 'BuddyPress';
}

/**
 * Get activity CSS classes based on platform
 *
 * @since 1.5.2
 * @return array
 */
function bp_share_get_activity_classes() {
	$classes = array(
		'activity_stream' => 'activity-list',
		'activity_item'   => 'activity-item',
		'activity_meta'   => 'activity-meta',
		'activity_button' => 'generic-button',
	);
	
	if ( bp_share_is_buddyboss() ) {
		// BuddyBoss specific classes
		$classes['activity_stream'] = 'bb-activity-list activity-list';
		$classes['activity_meta']   = 'bb-activity-meta activity-meta';
	}
	
	return apply_filters( 'bp_share_activity_classes', $classes );
}

/**
 * Check if media component is active (BuddyBoss specific)
 *
 * @since 1.5.2
 * @return bool
 */
function bp_share_is_media_active() {
	if ( ! bp_share_is_buddyboss() ) {
		return false;
	}
	
	return function_exists( 'bp_is_active' ) && bp_is_active( 'media' );
}

/**
 * Check if document component is active (BuddyBoss specific)
 *
 * @since 1.5.2
 * @return bool
 */
function bp_share_is_document_active() {
	if ( ! bp_share_is_buddyboss() ) {
		return false;
	}
	
	return function_exists( 'bp_is_active' ) && bp_is_active( 'document' );
}

/**
 * Get compose message URL based on platform
 *
 * @since 1.5.2
 * @return string
 */
function bp_share_get_compose_message_url() {
	$url = '';
	
	if ( function_exists( 'bp_loggedin_user_domain' ) && function_exists( 'bp_get_messages_slug' ) ) {
		$url = trailingslashit( bp_loggedin_user_domain() . bp_get_messages_slug() ) . 'compose/';
		
		if ( bp_share_is_buddyboss() ) {
			// BuddyBoss might use different URL structure
			$url = apply_filters( 'bb_get_messages_compose_url', $url );
		}
	}
	
	return $url;
}

/**
 * Get activity button args with platform compatibility
 *
 * @since 1.5.2
 * @param array $args Button arguments
 * @return array
 */
function bp_share_filter_button_args( $args ) {
	if ( bp_share_is_buddyboss() ) {
		// BuddyBoss specific button classes
		if ( isset( $args['link_class'] ) ) {
			$args['link_class'] .= ' bb-button';
		}
	}
	
	return $args;
}

/**
 * Check if activity type should have share button
 *
 * @since 1.5.2
 * @param string $activity_type Activity type
 * @return bool
 */
function bp_share_should_show_share_button( $activity_type ) {
	// Common activity types that shouldn't have share button
	$excluded_types = array(
		'friendship_accepted',
		'friendship_created',
		'joined_group',
		'left_group',
		'created_group',
		'group_details_updated',
		'bbp_topic_create',
		'bbp_reply_create',
		'new_avatar',
		'new_member',
		'updated_profile',
	);
	
	// BuddyBoss specific exclusions
	if ( bp_share_is_buddyboss() ) {
		$excluded_types = array_merge( $excluded_types, array(
			'bb_video_activity',
			'bb_document_activity',
			'bb_media_photo_upload', // Album uploads
			'bb_groups_featured_activity',
		) );
	}
	
	return ! in_array( $activity_type, $excluded_types, true );
}

/**
 * Get Select2 version based on platform
 *
 * @since 1.5.2
 * @return string
 */
function bp_share_get_select2_version() {
	if ( bp_share_is_buddyboss() ) {
		// BuddyBoss might use a different Select2 version
		return '4.1.0-rc.0';
	}
	return '4.0.13';
}

/**
 * Platform-specific script dependencies
 *
 * @since 1.5.2
 * @return array
 */
function bp_share_get_script_dependencies() {
	$deps = array( 'jquery' );
	
	if ( bp_share_is_buddyboss() ) {
		// BuddyBoss might have additional dependencies
		if ( wp_script_is( 'bb-activity', 'registered' ) ) {
			$deps[] = 'bb-activity';
		}
	} else {
		// BuddyPress dependencies
		if ( wp_script_is( 'bp-activity', 'registered' ) ) {
			$deps[] = 'bp-activity';
		}
	}
	
	return $deps;
}

/**
 * Get modal classes based on platform
 *
 * @since 1.5.2
 * @return string
 */
function bp_share_get_modal_classes() {
	$classes = 'activity-share-modal modal fade';
	
	if ( bp_share_is_buddyboss() ) {
		$classes .= ' bb-modal bb-activity-modal';
	}
	
	return $classes;
}

/**
 * Compatibility notice for admin
 *
 * @since 1.5.2
 */
function bp_share_platform_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	$platform = bp_share_get_platform_name();
	$screen = get_current_screen();
	
	// Only show on our plugin pages
	if ( ! $screen || strpos( $screen->id, 'buddypress-share' ) === false ) {
		return;
	}
	
	?>
	<div class="notice notice-info is-dismissible">
		<p><?php printf( esc_html__( 'BuddyPress Activity Share Pro is running in %s compatibility mode.', 'buddypress-share' ), '<strong>' . esc_html( $platform ) . '</strong>' ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'bp_share_platform_admin_notice' );