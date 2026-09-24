<?php
/**
 * The only class that touches the events table.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Tracking;

defined( 'ABSPATH' ) || exit;

/**
 * {prefix}bpas_pro_events: one row per share / repost / send / visit.
 */
final class Events {

	/**
	 * Table name.
	 */
	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'bpas_pro_events';
	}

	/**
	 * Create / update the table (dbDelta).
	 */
	public static function install(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();
		// Indexes follow the queries: time range first (analytics report, retention cleanup), event + time
		// for the per-network breakdown, object for counts/undo, user for privacy. Measured at 1M rows.
		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				event varchar(20) NOT NULL,
				object_type varchar(20) NOT NULL,
				object_id bigint(20) unsigned NOT NULL,
				service varchar(30) NOT NULL DEFAULT '',
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				visitor_hash char(64) NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY object_time (object_type,object_id,created_at),
				KEY time_event_object (created_at,event,object_type,object_id),
				KEY event_time_service (event,created_at,service),
				KEY user_id (user_id)
			) ENGINE=InnoDB {$charset};"
		);
	}

	/**
	 * Record an event.
	 *
	 * @param string $event        'share' | 'repost' | 'send' | 'visit'.
	 * @param string $object_type  'activity' | 'post'.
	 * @param int    $object_id    ID.
	 * @param string $service      Network slug or ''.
	 * @param int    $user_id      Member (0 = visitor).
	 * @param string $visitor_hash Salted daily hash or ''.
	 */
	public static function add( string $event, string $object_type, int $object_id, string $service = '', int $user_id = 0, string $visitor_hash = '' ): void {
		global $wpdb;
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table.
			self::table(),
			array(
				'event'        => substr( $event, 0, 20 ),
				'object_type'  => substr( $object_type, 0, 20 ),
				'object_id'    => $object_id,
				'service'      => substr( $service, 0, 30 ),
				'user_id'      => $user_id,
				'visitor_hash' => '' === $visitor_hash ? null : $visitor_hash,
				'created_at'   => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%d', '%s', '%d', '%s', '%s' )
		);
		wp_cache_set_last_changed( 'bpas_pro_events' );
	}

	/**
	 * Remove the latest matching event (an undone repost should not count).
	 *
	 * @param string $event       Event.
	 * @param string $object_type Object type.
	 * @param int    $object_id   Object ID.
	 * @param int    $user_id     Member.
	 */
	public static function delete_last( string $event, string $object_type, int $object_id, int $user_id ): void {
		global $wpdb;
		$table = self::table();
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE event = %s AND object_type = %s AND object_id = %d AND user_id = %d ORDER BY id DESC LIMIT 1", $event, $object_type, $object_id, $user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table name.
		wp_cache_set_last_changed( 'bpas_pro_events' );
	}

	/**
	 * Delete events older than a date, in one bounded batch.
	 *
	 * @param string $before UTC datetime.
	 * @param int    $limit  Batch size.
	 * @return int Rows deleted.
	 */
	public static function delete_before( string $before, int $limit = 1000 ): int {
		global $wpdb;
		$table = self::table();
		$rows  = (int) $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s LIMIT %d", $before, $limit ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table name.
		if ( $rows ) {
			wp_cache_set_last_changed( 'bpas_pro_events' );
		}
		return $rows;
	}

	/**
	 * A member's events (privacy export).
	 *
	 * @param int $user_id User.
	 * @param int $page    1-based page.
	 * @param int $per     Per page.
	 */
	public static function for_user( int $user_id, int $page = 1, int $per = 500 ): array {
		global $wpdb;
		$table = self::table();
		return (array) $wpdb->get_results( $wpdb->prepare( "SELECT id, event, object_type, object_id, service, created_at FROM {$table} WHERE user_id = %d ORDER BY id LIMIT %d OFFSET %d", $user_id, $per, ( $page - 1 ) * $per ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table name.
	}

	/**
	 * Delete a member's events (privacy erase).
	 *
	 * @param int $user_id User.
	 * @return int Rows deleted.
	 */
	public static function delete_for_user( int $user_id ): int {
		global $wpdb;
		$rows = (int) $wpdb->delete( self::table(), array( 'user_id' => $user_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table.
		wp_cache_set_last_changed( 'bpas_pro_events' );
		return $rows;
	}
}
