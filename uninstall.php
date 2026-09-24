<?php
/**
 * Uninstall Activity Share Pro: settings, events table, counts, group / member / notification data, cards.
 *
 * @package BPAS_Pro
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$bpas_pro_cleanup = static function (): void {
	global $wpdb;

	foreach ( array( 'bpas_pro_settings', 'bpas_pro_db_version', 'bpas_pro_flush_rewrites', 'bpas_pro_license_key' ) as $bpas_pro_key ) {
		delete_option( $bpas_pro_key );
	}
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
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$bp_prefix}bp_notifications WHERE component_name = %s", 'bpas' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- BuddyPress table.

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
