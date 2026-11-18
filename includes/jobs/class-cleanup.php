<?php
/**
 * Daily retention cleanup (Action Scheduler when present, WP-Cron otherwise).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Jobs;

use BPAS_Pro\Tracking\Events;

defined( 'ABSPATH' ) || exit;

/**
 * Deletes events older than the retention window, in bounded batches.
 */
final class Cleanup {

	const HOOK = 'bpas_pro_cleanup';

	/**
	 * Hook registration + schedule.
	 */
	public static function init(): void {
		add_action( self::HOOK, array( __CLASS__, 'run' ) );
		add_action( 'init', array( __CLASS__, 'schedule' ), 30 );
	}

	/**
	 * Schedule once.
	 */
	public static function schedule(): void {
		if ( function_exists( 'as_has_scheduled_action' ) && function_exists( 'as_schedule_recurring_action' ) ) {
			if ( ! as_has_scheduled_action( self::HOOK ) ) {
				as_schedule_recurring_action( time() + HOUR_IN_SECONDS, DAY_IN_SECONDS, self::HOOK, array(), 'bpas-pro' );
			}
			return;
		}
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::HOOK );
		}
	}

	/**
	 * Delete old rows: up to 10 batches of 1000 per run.
	 */
	public static function run(): void {
		$days   = max( 30, (int) apply_filters( 'bpas_pro_retention_days', 365 ) );
		$before = gmdate( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS );
		for ( $i = 0; $i < 10; $i++ ) {
			if ( Events::delete_before( $before, 1000 ) < 1000 ) {
				break;
			}
		}
	}

	/**
	 * Remove the schedule (uninstall).
	 */
	public static function unschedule(): void {
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( self::HOOK );
		}
		wp_clear_scheduled_hook( self::HOOK );
	}
}
