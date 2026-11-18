<?php
/**
 * Admin REST: GET/PATCH bpas/v1/pro-settings, GET bpas/v1/analytics (manage_options).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Api;

use BPAS_Pro\Analytics\Analytics;
use BPAS_Pro\Settings;
use WP_Error;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/**
 * The Pro admin tabs use these; apps may too.
 */
final class Admin_Controller {

	/**
	 * Register routes.
	 */
	public static function register(): void {
		$ns = bpas_rest_namespace();
		register_rest_route(
			$ns,
			'/pro-settings',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => static fn() => rest_ensure_response( Settings::all() ),
					'permission_callback' => array( __CLASS__, 'can_manage' ),
				),
				array(
					'methods'             => 'PATCH',
					'callback'            => array( __CLASS__, 'patch' ),
					'permission_callback' => array( __CLASS__, 'can_manage' ),
					'args'                => array(
						'features'      => array( 'type' => 'object' ),
						'content_types' => array( 'type' => 'object' ),
					),
				),
			)
		);
		register_rest_route(
			$ns,
			'/analytics',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'analytics' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'args'                => array(
					'days'        => array(
						'type'    => 'integer',
						'enum'    => array( 7, 30, 90 ),
						'default' => 30,
					),
					'page'        => array(
						'type'    => 'integer',
						'default' => 1,
						'minimum' => 1,
					),
					'per_page'    => array(
						'type'    => 'integer',
						'default' => 20,
						'minimum' => 1,
						'maximum' => 100,
					),
					'object_type' => array(
						'type'    => 'string',
						'enum'    => array( '', 'activity', 'post' ),
						'default' => '',
					),
				),
			)
		);
	}

	/**
	 * Administrators only.
	 *
	 * @return true|WP_Error
	 */
	public static function can_manage() {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'bpas_authentication_required', __( 'Authentication required.', 'buddypress-activity-share-pro' ), array( 'status' => 401 ) );
		}
		return current_user_can( 'manage_options' ) ? true : new WP_Error( 'bpas_forbidden', __( 'You are not allowed to do this.', 'buddypress-activity-share-pro' ), array( 'status' => 403 ) );
	}

	/**
	 * Save settings; turning short links on/off refreshes rewrite rules.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public static function patch( WP_REST_Request $request ) {
		$before = Settings::feature( 'short_links' );
		$saved  = Settings::update( (array) $request->get_json_params() );
		if ( ! empty( $saved['features']['short_links'] ) !== $before ) {
			update_option( 'bpas_pro_flush_rewrites', 1, false );
		}
		return rest_ensure_response( $saved );
	}

	/**
	 * Analytics report.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public static function analytics( WP_REST_Request $request ) {
		return rest_ensure_response( Analytics::report( (int) $request['days'], (int) $request['page'], (int) $request['per_page'], (string) $request['object_type'] ) );
	}
}
