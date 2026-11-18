<?php
/**
 * Share rewards (X4): points for shares that bring visits or sign-ups.
 *
 * No settings here - point values are set in the points plugin (WB Gamification, GamiPress, myCRED).
 * Adapters load only when that plugin is active.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Rewards;

defined( 'ABSPATH' ) || exit;

/**
 * Listens to bpas_pro_share_visit( $sharer, $type, $id ) and bpas_pro_share_signup( $sharer, $new_user ).
 */
final class Rewards {

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		// WB Gamification: registered actions are configured in its own UI.
		// Its registry opens at plugins_loaded:6, before Pro boots - register directly when it already ran.
		if ( function_exists( 'wb_gam_register_action' ) ) {
			if ( did_action( 'wb_gam_register' ) ) {
				// Labels are translated, so wait for init (WP 6.7 warns about earlier translation loads).
				add_action( 'init', array( __CLASS__, 'register_wb_gamification' ), 5 );
			} else {
				add_action( 'wb_gam_register', array( __CLASS__, 'register_wb_gamification' ) );
			}
		}

		// GamiPress: triggers appear in its requirement builder.
		if ( function_exists( 'gamipress_trigger_event' ) ) {
			add_filter( 'gamipress_activity_triggers', array( __CLASS__, 'gamipress_triggers' ) );
			add_action( 'bpas_pro_share_visit', array( __CLASS__, 'gamipress_visit' ) );
			add_action( 'bpas_pro_share_signup', array( __CLASS__, 'gamipress_signup' ) );
		}

		// myCRED: a hook configured under Points > Hooks.
		if ( class_exists( 'myCRED_Hook' ) ) {
			add_filter( 'mycred_setup_hooks', array( __CLASS__, 'mycred_hooks' ) );
		}
	}

	/**
	 * WB Gamification actions.
	 */
	public static function register_wb_gamification(): void {
		wb_gam_register_action(
			array(
				'id'             => 'bpas_share_visit',
				'label'          => __( 'A shared post brought a visitor', 'buddypress-activity-share-pro' ),
				'hook'           => 'bpas_pro_share_visit',
				'user_callback'  => static fn( $sharer ) => (int) $sharer,
				'default_points' => 1,
				'repeatable'     => true,
				'daily_cap'      => 20,
			)
		);
		wb_gam_register_action(
			array(
				'id'             => 'bpas_share_signup',
				'label'          => __( 'A shared post brought a new member', 'buddypress-activity-share-pro' ),
				'hook'           => 'bpas_pro_share_signup',
				'user_callback'  => static fn( $sharer ) => (int) $sharer,
				'default_points' => 10,
				'repeatable'     => true,
			)
		);
	}

	/**
	 * GamiPress triggers.
	 *
	 * @param array $triggers Triggers.
	 */
	public static function gamipress_triggers( $triggers ): array {
		$triggers = (array) $triggers;
		$triggers[ __( 'Activity Share', 'buddypress-activity-share-pro' ) ] = array(
			'bpas_pro_share_visit'  => __( 'A shared post brought a visitor', 'buddypress-activity-share-pro' ),
			'bpas_pro_share_signup' => __( 'A shared post brought a new member', 'buddypress-activity-share-pro' ),
		);
		return $triggers;
	}

	/**
	 * GamiPress visit.
	 *
	 * @param int $sharer Member.
	 */
	public static function gamipress_visit( $sharer ): void {
		gamipress_trigger_event(
			array(
				'event'   => 'bpas_pro_share_visit',
				'user_id' => (int) $sharer,
			)
		);
	}

	/**
	 * GamiPress sign-up.
	 *
	 * @param int $sharer Member.
	 */
	public static function gamipress_signup( $sharer ): void {
		gamipress_trigger_event(
			array(
				'event'   => 'bpas_pro_share_signup',
				'user_id' => (int) $sharer,
			)
		);
	}

	/**
	 * Register the myCRED hook.
	 *
	 * @param array $hooks Hooks.
	 */
	public static function mycred_hooks( $hooks ): array {
		require_once __DIR__ . '/class-mycred-hook.php';
		$hooks               = (array) $hooks;
		$hooks['bpas_share'] = array(
			'title'       => __( 'Activity Share', 'buddypress-activity-share-pro' ),
			'description' => __( 'Points when a member\'s shared post brings a visitor or a new member.', 'buddypress-activity-share-pro' ),
			'callback'    => array( Mycred_Hook::class ),
		);
		return $hooks;
	}
}
