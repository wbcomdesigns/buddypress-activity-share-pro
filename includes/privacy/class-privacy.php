<?php
/**
 * Personal data export / erase + suggested privacy-policy text (P14).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Privacy;

use BPAS_Pro\Tracking\Events;

defined( 'ABSPATH' ) || exit;

/**
 * Tools > Export / Erase Personal Data.
 */
final class Privacy {

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'exporters' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'erasers' ) );
		add_action( 'admin_init', array( __CLASS__, 'policy_text' ) );
	}

	/**
	 * Register exporter.
	 *
	 * @param array $exporters Exporters.
	 */
	public static function exporters( $exporters ): array {
		$exporters                              = (array) $exporters;
		$exporters['buddypress-activity-share'] = array(
			'exporter_friendly_name' => __( 'Activity Share', 'buddypress-activity-share-pro' ),
			'callback'               => array( __CLASS__, 'export' ),
		);
		return $exporters;
	}

	/**
	 * Register eraser.
	 *
	 * @param array $erasers Erasers.
	 */
	public static function erasers( $erasers ): array {
		$erasers                              = (array) $erasers;
		$erasers['buddypress-activity-share'] = array(
			'eraser_friendly_name' => __( 'Activity Share', 'buddypress-activity-share-pro' ),
			'callback'             => array( __CLASS__, 'erase' ),
		);
		return $erasers;
	}

	/**
	 * Export a member's share events.
	 *
	 * @param string $email Email.
	 * @param int    $page  Page.
	 */
	public static function export( $email, $page = 1 ): array {
		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			return array(
				'data' => array(),
				'done' => true,
			);
		}
		$per  = 500;
		$rows = Events::for_user( (int) $user->ID, (int) $page, $per );
		$data = array();
		foreach ( $rows as $row ) {
			$data[] = array(
				'group_id'    => 'bpas-events',
				'group_label' => __( 'Shares and reposts', 'buddypress-activity-share-pro' ),
				'item_id'     => 'bpas-event-' . (int) $row->id,
				'data'        => array(
					array(
						'name'  => __( 'Action', 'buddypress-activity-share-pro' ),
						'value' => $row->event,
					),
					array(
						'name'  => __( 'Item', 'buddypress-activity-share-pro' ),
						'value' => $row->object_type . ' #' . (int) $row->object_id,
					),
					array(
						'name'  => __( 'Where', 'buddypress-activity-share-pro' ),
						'value' => $row->service,
					),
					array(
						'name'  => __( 'Date', 'buddypress-activity-share-pro' ),
						'value' => $row->created_at,
					),
				),
			);
		}
		return array(
			'data' => $data,
			'done' => count( $rows ) < $per,
		);
	}

	/**
	 * Erase a member's share events.
	 *
	 * @param string $email Email.
	 */
	public static function erase( $email ): array {
		$user    = get_user_by( 'email', $email );
		$removed = $user ? Events::delete_for_user( (int) $user->ID ) : 0;
		return array(
			'items_removed'  => $removed > 0,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	/**
	 * Suggested privacy-policy text.
	 */
	public static function policy_text(): void {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		wp_add_privacy_policy_content(
			__( 'Activity Share Pro', 'buddypress-activity-share-pro' ),
			wp_kses_post(
				'<p>' . __( 'When members repost or send a post to a friend, we record who did it, what was shared and when, so we can show share counts and the most shared posts.', 'buddypress-activity-share-pro' ) . '</p>'
				. '<p>' . __( 'If share analytics is turned on, we also count clicks on share buttons and visits from shared links. Visitors are counted with a one-way code that changes every day; we do not store IP addresses.', 'buddypress-activity-share-pro' ) . '</p>'
				. '<p>' . __( 'Share records are kept for 12 months and are included when you export or erase your personal data.', 'buddypress-activity-share-pro' ) . '</p>'
			)
		);
	}
}
