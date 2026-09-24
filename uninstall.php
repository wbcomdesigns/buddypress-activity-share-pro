<?php
/**
 * Uninstall Activity Share Pro: settings, events table, counts, group / member / notification data, cards.
 *
 * @package BPAS_Pro
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$bpas_pro_cleanup = static function (): void {
	global $wpdb;

	foreach ( array( 'bpas_pro_settings', 'bpas_pro_db_version', 'bpas_pro_flush_rewrites', 'bpas_pro_license_key', 'widget_bpas_pro_trending' ) as $bpas_pro_key ) {
		delete_option( $bpas_pro_key );
	}
	// Cached trending lists and the per-original email throttle.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", $wpdb->esc_like( '_transient_bpas_' ) . '%', $wpdb->esc_like( '_transient_timeout_bpas_' ) . '%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- uninstall.
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bpas_pro_events" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.SchemaChange -- uninstall.

	delete_metadata( 'post', 0, '_bpas_share_count', '', true );
	delete_metadata( 'user', 0, 'bpas_pro_allow_repost', '', true );
	delete_metadata( 'user', 0, 'notification_bpas_repost', '', true );

	$bp_prefix = function_exists( 'bp_core_get_table_prefix' ) ? bp_core_get_table_prefix() : $wpdb->base_prefix;
	foreach ( array(
		'bp_activity_meta'    => array( '_bpas_share_count', '_bpas_original_type' ),
		'bp_groups_groupmeta' => array( 'bpas_allow_external', 'bpas_allow_repost_out' ),
	) as $bpas_pro_table => $bpas_pro_keys ) {
		foreach ( (array) $bpas_pro_keys as $bpas_pro_meta ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp_prefix}{$bpas_pro_table} WHERE meta_key = %s", $bpas_pro_meta ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- BuddyPress tables.
		}
	}
	// Notification meta first (it is keyed by notification id), then the notifications.
	$wpdb->query( $wpdb->prepare( "DELETE m FROM {$bp_prefix}bp_notifications_meta m INNER JOIN {$bp_prefix}bp_notifications n ON n.id = m.notification_id WHERE n.component_name = %s", 'bpas' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- BuddyPress table.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp_prefix}bp_notifications WHERE component_name = %s", 'bpas' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- BuddyPress table.

	// The "repost" email: its template posts, then the email-type term (works whether or not BuddyPress is active).
	$bpas_pro_term = $wpdb->get_row( $wpdb->prepare( "SELECT t.term_id, tt.term_taxonomy_id FROM {$wpdb->terms} t INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.slug = %s", 'bp-email-type', 'bpas-repost' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- uninstall.
	if ( $bpas_pro_term ) {
		foreach ( (array) $wpdb->get_col( $wpdb->prepare( "SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = %d", $bpas_pro_term->term_taxonomy_id ) ) as $bpas_pro_email ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- uninstall.
			wp_delete_post( (int) $bpas_pro_email, true );
		}
		$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => (int) $bpas_pro_term->term_taxonomy_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- uninstall.
		$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => (int) $bpas_pro_term->term_taxonomy_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- uninstall.
		$wpdb->delete( $wpdb->termmeta, array( 'term_id' => (int) $bpas_pro_term->term_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- uninstall.
		$wpdb->delete( $wpdb->terms, array( 'term_id' => (int) $bpas_pro_term->term_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- uninstall.
	}

	/*
	 * Reposts are members' own posts (with their own comments and reactions), so they stay in the
	 * activity stream, as BuddyPress keeps activity when a component is removed.
	 */

	if ( function_exists( 'as_unschedule_all_actions' ) ) {
		as_unschedule_all_actions( 'bpas_pro_cleanup' );
	}
	wp_clear_scheduled_hook( 'bpas_pro_cleanup' );

	$bpas_pro_uploads = wp_upload_dir( null, false );
	foreach ( (array) glob( trailingslashit( $bpas_pro_uploads['basedir'] ) . 'bpas-cards/*.png' ) as $bpas_pro_file ) {
		wp_delete_file( $bpas_pro_file );
	}
};

if ( is_multisite() ) {
	foreach ( get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	) as $bpas_pro_site_id ) {
		switch_to_blog( (int) $bpas_pro_site_id );
		$bpas_pro_cleanup();
		restore_current_blog();
	}
} else {
	$bpas_pro_cleanup();
}
