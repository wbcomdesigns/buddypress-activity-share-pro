<?php
/**
 * Share analytics: indexed, paginated, cached (big-site ready).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Analytics;

use BPAS_Pro\Tracking\Events;

defined( 'ABSPATH' ) || exit;

/**
 * Reads only the events table.
 */
final class Analytics {

	const GROUP = 'bpas_pro_events';

	/**
	 * Report for the last N days.
	 *
	 * @param int    $days        7 | 30 | 90.
	 * @param int    $page        1-based page of top content.
	 * @param int    $per_page    Rows per page.
	 * @param string $object_type '' | 'activity' | 'post'.
	 */
	public static function report( int $days, int $page = 1, int $per_page = 20, string $object_type = '' ): array {
		$days     = in_array( $days, array( 7, 30, 90 ), true ) ? $days : 30;
		$per_page = min( 100, max( 1, $per_page ) );
		$page     = max( 1, $page );
		$key      = md5( wp_json_encode( array( $days, $page, $per_page, $object_type ) ) ) . ':' . wp_cache_get_last_changed( self::GROUP );
		$cached   = wp_cache_get( $key, self::GROUP );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		$table = Events::table();
		$from  = gmdate( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS );
		$type  = in_array( $object_type, array( 'activity', 'post' ), true ) ? $object_type : '';
		$where = $type ? $wpdb->prepare( 'created_at >= %s AND object_type = %s', $from, $type ) : $wpdb->prepare( 'created_at >= %s', $from );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table; $where is prepared above.
		$totals   = $wpdb->get_results( "SELECT event, COUNT(*) AS n FROM {$table} WHERE {$where} GROUP BY event", OBJECT_K );
		$networks = $wpdb->get_results( "SELECT service, COUNT(*) AS n FROM {$table} WHERE {$where} AND event = 'share' GROUP BY service ORDER BY n DESC LIMIT 20" );
		// "Most shared" counts shares, reposts and sends - visits are not shares.
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM ( SELECT 1 FROM {$table} WHERE {$where} AND event <> 'visit' GROUP BY object_type, object_id ) t" );
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT object_type, object_id, COUNT(*) AS n FROM {$table} WHERE {$where} AND event <> 'visit' GROUP BY object_type, object_id ORDER BY n DESC, object_id DESC LIMIT %d OFFSET %d", $per_page, ( $page - 1 ) * $per_page ) );
		// phpcs:enable

		$report = array(
			'days'     => $days,
			'totals'   => array(
				'share'  => (int) ( $totals['share']->n ?? 0 ),
				'repost' => (int) ( $totals['repost']->n ?? 0 ),
				'send'   => (int) ( $totals['send']->n ?? 0 ),
				'visit'  => (int) ( $totals['visit']->n ?? 0 ),
			),
			'networks' => array_map(
				static fn( $r ) => array(
					'network' => (string) $r->service,
					'count'   => (int) $r->n,
				),
				(array) $networks
			),
			'top'      => self::describe( (array) $rows ),
			'total'    => $total,
			'pages'    => (int) ceil( $total / $per_page ),
			'has_more' => ( ( $page - 1 ) * $per_page + count( (array) $rows ) ) < $total,
		);

		wp_cache_set( $key, $report, self::GROUP, HOUR_IN_SECONDS );
		return $report;
	}

	/**
	 * Titles and links for top rows - one query per object type (no N+1).
	 *
	 * @param array $rows Rows (object_type, object_id, n).
	 */
	private static function describe( array $rows ): array {
		$activity_ids = array();
		$post_ids     = array();
		foreach ( $rows as $row ) {
			if ( 'post' === $row->object_type ) {
				$post_ids[] = (int) $row->object_id;
			} else {
				$activity_ids[] = (int) $row->object_id;
			}
		}
		if ( $post_ids ) {
			_prime_post_caches( $post_ids, false, false );
		}
		$activities = array();
		if ( $activity_ids && class_exists( '\BP_Activity_Activity' ) ) {
			$found = \BP_Activity_Activity::get(
				array(
					'in'               => $activity_ids,
					'per_page'         => count( $activity_ids ),
					'show_hidden'      => true,
					'display_comments' => 'stream',
				)
			);
			foreach ( (array) $found['activities'] as $a ) {
				$activities[ (int) $a->id ] = $a;
			}
		}

		$out = array();
		foreach ( $rows as $row ) {
			$id = (int) $row->object_id;
			if ( 'post' === $row->object_type ) {
				$post  = get_post( $id );
				$title = $post ? get_the_title( $post ) : '';
				$url   = $post ? get_permalink( $post ) : '';
			} else {
				$a     = $activities[ $id ] ?? null;
				$title = $a ? wp_html_excerpt( wp_strip_all_tags( $a->content ? $a->content : $a->action ), 90, '...' ) : '';
				$url   = $a ? bp_activity_get_permalink( $id, $a ) : '';
			}
			$out[] = array(
				'type'  => (string) $row->object_type,
				'id'    => $id,
				'title' => '' !== $title ? html_entity_decode( $title, ENT_QUOTES ) : __( '(deleted)', 'buddypress-activity-share-pro' ),
				'url'   => (string) $url,
				'count' => (int) $row->n,
			);
		}
		return $out;
	}

	/**
	 * Most shared public items (trending block).
	 *
	 * @param int $days  7 | 30.
	 * @param int $limit Items.
	 */
	public static function trending( int $days, int $limit = 5 ): array {
		$report = self::report( $days, 1, $limit * 3 );
		$items  = array();
		foreach ( $report['top'] as $row ) {
			$ctx = bpas_share_context( $row['type'], $row['id'] );
			if ( $ctx && bpas_can_share_externally( $ctx ) ) {
				$items[] = $row;
			}
			if ( count( $items ) >= $limit ) {
				break;
			}
		}
		return $items;
	}
}
