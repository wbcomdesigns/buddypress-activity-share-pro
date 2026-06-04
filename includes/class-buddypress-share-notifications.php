<?php
/**
 * Reshare notifications for the original activity author.
 *
 * Wires the existing `bp_share_after_create_reshare` action into the
 * BuddyPress notifications component so the author of an activity is told when
 * someone reshares it. Entirely additive and gated behind the
 * `bp_reshare_settings['enable_reshare_notifications']` toggle — when the
 * toggle is off (the default for existing installs) no notification component
 * is registered and no notification is ever created.
 *
 * @link       http://wbcomdesigns.com
 * @since      2.3.0
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders reshare notifications.
 *
 * @since 2.3.0
 */
class Buddypress_Share_Notifications {

	/**
	 * The notification component id used by BuddyPress.
	 *
	 * Namespaced so it never collides with another component. Additive — not a
	 * rename of any existing key.
	 *
	 * @since 2.3.0
	 * @var   string
	 */
	const COMPONENT = 'bp_share';

	/**
	 * The notification action (sub-type) for a reshare event.
	 *
	 * @since 2.3.0
	 * @var   string
	 */
	const ACTION = 'bp_share_reshared';

	/**
	 * Whether reshare notifications are enabled in the admin.
	 *
	 * Defaults to OFF when the key has never been saved, so existing sites do
	 * not suddenly start emitting notifications after an upgrade.
	 *
	 * @since  2.3.0
	 * @return bool True when the admin toggle is on.
	 */
	public static function is_enabled() {
		$settings = get_site_option( 'bp_reshare_settings', array() );
		return ! empty( $settings['enable_reshare_notifications'] );
	}

	/**
	 * Register all hooks. Called from the public hook bootstrap.
	 *
	 * The notifications component is only registered with BuddyPress when the
	 * feature is enabled, so a disabled feature has zero footprint on the
	 * notifications screen.
	 *
	 * @since 2.3.0
	 * @param Buddypress_Share_Loader $loader The plugin hook loader.
	 * @return void
	 */
	public function register( $loader ) {
		// Always listen for the reshare event; the callback re-checks the
		// toggle so a freshly enabled feature works without a reload.
		$loader->add_action( 'bp_share_after_create_reshare', $this, 'notify_author', 20, 2 );

		// Only register the BP notifications component + formatter when enabled.
		if ( ! self::is_enabled() ) {
			return;
		}

		$loader->add_filter( 'bp_notifications_get_registered_components', $this, 'register_component' );
		$loader->add_filter( 'bp_notifications_get_notifications_for_user', $this, 'format_notification', 10, 8 );
	}

	/**
	 * Add our component to BuddyPress's registered notification components.
	 *
	 * @since 2.3.0
	 * @param array $components Registered notification components.
	 * @return array Modified components.
	 */
	public function register_component( $components ) {
		if ( ! is_array( $components ) ) {
			$components = array();
		}
		if ( ! in_array( self::COMPONENT, $components, true ) ) {
			$components[] = self::COMPONENT;
		}
		return $components;
	}

	/**
	 * Create a notification for the original activity author after a reshare.
	 *
	 * Hooked to `bp_share_after_create_reshare`. Skips self-reshares (you are
	 * not notified that you reshared your own activity) and only fires for
	 * activity reshares — post shares have no in-stream author to notify.
	 *
	 * @since 2.3.0
	 * @param int   $new_activity_id The newly created reshare activity ID.
	 * @param array $reshare_data    The reshare data array (see the action docblock).
	 * @return void
	 */
	public function notify_author( $new_activity_id, $reshare_data ) {
		if ( ! self::is_enabled() || ! function_exists( 'bp_notifications_add_notification' ) ) {
			return;
		}

		if ( empty( $reshare_data['activity_type'] ) || 'activity_share' !== $reshare_data['activity_type'] ) {
			return;
		}

		$original_activity_id = isset( $reshare_data['activity_id'] ) ? (int) $reshare_data['activity_id'] : 0;
		$resharer_id          = isset( $reshare_data['user_id'] ) ? (int) $reshare_data['user_id'] : 0;
		if ( ! $original_activity_id || ! $resharer_id ) {
			return;
		}

		$author_id = $this->get_activity_author_id( $original_activity_id );
		if ( ! $author_id || $author_id === $resharer_id ) {
			return; // No author, or a self-reshare — nothing to notify.
		}

		bp_notifications_add_notification(
			array(
				'user_id'           => $author_id,
				'item_id'           => $original_activity_id,
				'secondary_item_id' => $resharer_id,
				'component_name'    => self::COMPONENT,
				'component_action'  => self::ACTION,
				'date_notified'     => bp_core_current_time(),
				'is_new'            => 1,
			)
		);
	}

	/**
	 * Resolve the author (user_id) of an activity.
	 *
	 * @since 2.3.0
	 * @param int $activity_id The activity ID.
	 * @return int Author user ID, or 0 when it cannot be resolved.
	 */
	private function get_activity_author_id( $activity_id ) {
		if ( ! function_exists( 'bp_activity_get_specific' ) ) {
			return 0;
		}
		$activity = bp_activity_get_specific( array( 'activity_ids' => array( $activity_id ) ) );
		if ( empty( $activity['activities'][0] ) ) {
			return 0;
		}
		return (int) $activity['activities'][0]->user_id;
	}

	/**
	 * Render the notification string on the notifications screen.
	 *
	 * @since 2.3.0
	 * @param string|array $action            Default action string / format args.
	 * @param int          $item_id           The reshared (original) activity ID.
	 * @param int          $secondary_item_id The resharing user ID.
	 * @param int          $total_items       Number of grouped notifications.
	 * @param string       $format            'string' or 'array'.
	 * @param string       $component_action  The component action.
	 * @param string       $component_name    The component name.
	 * @param int          $notification_id   The notification ID (part of the BP filter signature; unused here).
	 * @return string|array The formatted notification.
	 */
	public function format_notification( $action, $item_id, $secondary_item_id, $total_items, $format = 'string', $component_action = '', $component_name = '', $notification_id = 0 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Signature mandated by bp_notifications_get_notifications_for_user.
		if ( self::COMPONENT !== $component_name || self::ACTION !== $component_action ) {
			return $action;
		}

		$resharer_name = bp_core_get_user_displayname( $secondary_item_id );
		$link          = function_exists( 'bp_activity_get_permalink' ) ? bp_activity_get_permalink( $item_id ) : '';

		if ( $total_items > 1 ) {
			$text = sprintf(
				/* translators: %d: number of members who reshared. */
				_n( '%d member reshared your activity', '%d members reshared your activity', $total_items, 'buddypress-share' ),
				$total_items
			);
		} else {
			$text = sprintf(
				/* translators: %s: the resharing member's name. */
				esc_html__( '%s reshared your activity', 'buddypress-share' ),
				$resharer_name
			);
		}

		if ( 'array' === $format ) {
			return array(
				'text' => $text,
				'link' => $link,
			);
		}

		if ( $link ) {
			return '<a href="' . esc_url( $link ) . '">' . esc_html( $text ) . '</a>';
		}
		return esc_html( $text );
	}
}
