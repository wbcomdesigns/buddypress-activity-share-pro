<?php
/**
 * Versioned upgrades on init - never the activation hook (updates do not fire it).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro;

defined( 'ABSPATH' ) || exit;

/**
 * Runs pending migrations once per version.
 */
final class Upgrade {

	const OPTION = 'bpas_pro_db_version';

	/**
	 * Schedule the check.
	 */
	public static function maybe_run(): void {
		add_action( 'init', array( __CLASS__, 'run' ), 21 );
	}

	/**
	 * Run.
	 */
	public static function run(): void {
		$stored = (string) get_option( self::OPTION, '0' );
		if ( version_compare( $stored, BPAS_PRO_DB_VERSION, '>=' ) ) {
			return;
		}
		if ( version_compare( $stored, '3.6.0', '<' ) && ! Migrations\Migration_3_6_0::run() ) {
			return;
		}
		update_option( self::OPTION, BPAS_PRO_DB_VERSION, true );
	}
}
