<?php
/**
 * Pro 2.x -> 3.6.0: settings, events, counts, and removal of every Pro-only legacy key (plan 12.4).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Migrations;

use BPAS_Pro\Settings;
use BPAS_Pro\Tracking\Events;
use BPAS_Pro\Notifications\Notifications;

defined( 'ABSPATH' ) || exit;

/**
 * Idempotent: every step checks before it changes anything.
 */
final class Migration_3_6_0 {

	/**
	 * Options only Pro 2.x wrote.
	 */
	const LEGACY_KEYS = array(
		'bp_reshare_settings',
		'bp_share_general_settings',
		'bp_share_plugin_settings',
		'bp_share_post_type_settings',
		'bp_share_plugin_version',
		'bp_share_install_date',
		'bp_share_last_activation',
		'bp_share_needs_upgrade_tasks',
		'bp_share_license_key',
		'bp_share_license_status',
		'bp_share_license_data',
		'bp_activity_share_plugin_license_key',
		'bp_activity_share_plugin_license_status',
		'bp_activity_share_plugin_license_data',
	);

	/**
	 * Run. Returns false to retry later.
	 */
	public static function run(): bool {
		global $wpdb;

		Events::install();

		if ( false === get_option( Settings::OPTION, false ) ) {
			self::import_settings();
		}

		$legacy_tracking = $wpdb->prefix . 'bp_share_post_tracking';
		if ( self::table_exists( $legacy_tracking ) ) {
			$events = Events::table();
			// Visitor IPs are not carried over.
			$wpdb->query( "INSERT INTO {$events} (event, object_type, object_id, service, user_id, created_at) SELECT 'share', 'post', post_id, LEFT(service, 30), user_id, shared_at FROM {$legacy_tracking}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- fixed table names.
			// Post share counts are ours only for posts in the legacy tracking table.
			$wpdb->query( "UPDATE {$wpdb->postmeta} SET meta_key = '_bpas_share_count' WHERE meta_key = 'share_count' AND post_id IN ( SELECT DISTINCT post_id FROM {$legacy_tracking} )" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- fixed table names.
			$wpdb->query( "DROP TABLE IF EXISTS {$legacy_tracking}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange -- legacy table.
		}
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bp_share_post_type_settings" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange -- legacy table.

		if ( function_exists( 'buddypress' ) && ! empty( buddypress()->activity->table_name_meta ) ) {
			$activity_meta = buddypress()->activity->table_name_meta;
			$wpdb->query( "UPDATE {$activity_meta} SET meta_key = '_bpas_share_count' WHERE meta_key = 'share_count'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- BP table.
			// Original ID already lives in secondary_item_id; stats aggregates were never populated.
			$wpdb->query( "DELETE FROM {$activity_meta} WHERE meta_key IN ( 'shared_activity_id', 'bp_share_activity_stats', 'bp_share_visit_stats' )" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- BP table.
			self::drop_index( $activity_meta, 'idx_bp_share_count' );
		}
		self::drop_index( $wpdb->postmeta, 'idx_bp_post_share_count' );

		delete_metadata( 'post', 0, '_bp_share_visits', '', true ); // Contained visitor IPs.
		delete_metadata( 'user', 0, 'bp_share_user_stats', '', true );

		if ( function_exists( 'bp_is_active' ) && bp_is_active( 'notifications' ) ) {
			$notifications = buddypress()->notifications->table_name;
			$wpdb->update( $notifications, array( 'component_name' => Notifications::COMPONENT ), array( 'component_name' => 'bp_share' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- BP table.
		}

		foreach ( self::LEGACY_KEYS as $key ) {
			delete_option( $key );
			delete_site_option( $key );
		}
		wp_clear_scheduled_hook( 'bp_share_weekly_cleanup' );
		wp_clear_scheduled_hook( 'bp_share_daily_cache_cleanup' );

		Notifications::install_email();
		update_option( 'bpas_pro_flush_rewrites', 1, false );
		wp_cache_delete( 'alloptions', 'options' );
		return true;
	}

	/**
	 * Carry over what 2.x users chose: reposting on/off and the post types they share on.
	 */
	private static function import_settings(): void {
		$reshare = get_site_option( 'bp_reshare_settings', get_option( 'bp_reshare_settings', array() ) );
		$changes = array();

		if ( is_array( $reshare ) && ! empty( $reshare ) ) {
			$all_off             = ! empty( $reshare['disable_my_profile_reshare_activity'] ) && ! empty( $reshare['disable_group_reshare_activity'] );
			$changes['features'] = array(
				'repost'      => ! $all_off,
				'send_friend' => empty( $reshare['disable_friends_reshare_activity'] ),
			);
		}

		$types = get_option( 'bp_share_post_type_settings', array() );
		if ( is_array( $types ) && ! empty( $types['enabled_post_types'] ) ) {
			$placement                = 'floating' === ( $types['display_style'] ?? 'floating' ) ? 'floating' : 'below';
			$changes['content_types'] = array_fill_keys( array_map( 'sanitize_key', (array) $types['enabled_post_types'] ), $placement );
		}

		Settings::update( $changes );
	}

	/**
	 * True when a table exists.
	 *
	 * @param string $table Table name.
	 */
	private static function table_exists( string $table ): bool {
		global $wpdb;
		return $table === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- schema check.
	}

	/**
	 * Drop an index a previous version added to a core table.
	 *
	 * @param string $table Table.
	 * @param string $index Index name.
	 */
	private static function drop_index( string $table, string $index ): void {
		global $wpdb;
		$exists = $wpdb->get_var( $wpdb->prepare( "SHOW INDEX FROM {$table} WHERE Key_name = %s", $index ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table from core globals.
		if ( $exists ) {
			$wpdb->query( "ALTER TABLE {$table} DROP INDEX {$index}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.SchemaChange -- removing our own legacy index.
		}
	}
}
