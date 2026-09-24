<?php
/**
 * "Anna and 5 others reposted your update" - grouped on-site notification + throttled email.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Notifications;

defined( 'ABSPATH' ) || exit;

/**
 * BuddyPress notification component `bpas` + email situation `bpas-repost`.
 */
final class Notifications {

	const COMPONENT = 'bpas';
	const ACTION    = 'repost';
	const SITUATION = 'bpas-repost';
	const PREF_META = 'notification_bpas_repost';

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_filter( 'bp_notifications_get_registered_components', array( __CLASS__, 'register_component' ) );
		add_filter( 'bp_notifications_get_notifications_for_user', array( __CLASS__, 'format' ), 10, 9 );
		add_action( 'bpas_pro_after_reshare', array( __CLASS__, 'on_repost' ), 10, 4 );
		add_action( 'bp_notification_settings', array( __CLASS__, 'settings_row' ), 20 );
		add_filter( 'bp_email_get_unsubscribe_type_schema', array( __CLASS__, 'unsubscribe_schema' ) );
		add_action( 'template_redirect', array( __CLASS__, 'mark_read' ) );
	}

	/**
	 * Mark a repost notification read when its link is opened by its owner.
	 */
	public static function mark_read(): void {
		$id = isset( $_GET['bpas_read'] ) ? absint( $_GET['bpas_read'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-state only, owner checked below.
		if ( ! $id || ! is_user_logged_in() ) {
			return;
		}
		$note = new \BP_Notifications_Notification( $id );
		if ( get_current_user_id() === (int) $note->user_id && self::COMPONENT === $note->component_name ) {
			\BP_Notifications_Notification::update( array( 'is_new' => 0 ), array( 'id' => $id ) );
		}
	}

	/**
	 * Register our component with BuddyPress notifications.
	 *
	 * @param array $components Components.
	 */
	public static function register_component( $components ): array {
		$components   = (array) $components;
		$components[] = self::COMPONENT;
		return $components;
	}

	/**
	 * A repost happened: notify the original author (grouped) and maybe email.
	 *
	 * @param int   $repost_id Repost activity.
	 * @param int   $user_id   Reposter.
	 * @param array $target    Original (type, id, author_id).
	 */
	public static function on_repost( int $repost_id, int $user_id, array $target ): void {
		$author = (int) $target['author_id'];
		if ( ! $author || $author === $user_id || ! bp_is_active( 'notifications' ) ) {
			return;
		}

		$existing = \BP_Notifications_Notification::get(
			array(
				'user_id'          => $author,
				'component_name'   => self::COMPONENT,
				'component_action' => self::ACTION,
				'item_id'          => (int) $target['id'],
				'is_new'           => 1,
			)
		);

		if ( $existing ) {
			$note  = $existing[0];
			$count = (int) bp_notifications_get_meta( $note->id, 'bpas_count', true ) + 1;
			\BP_Notifications_Notification::update(
				array(
					'secondary_item_id' => $user_id,
					'date_notified'     => bp_core_current_time(),
				),
				array( 'id' => $note->id )
			);
			bp_notifications_update_meta( $note->id, 'bpas_count', $count );
		} else {
			$id = bp_notifications_add_notification(
				array(
					'user_id'           => $author,
					'item_id'           => (int) $target['id'],
					'secondary_item_id' => $user_id,
					'component_name'    => self::COMPONENT,
					'component_action'  => self::ACTION,
					'date_notified'     => bp_core_current_time(),
					'is_new'            => 1,
				)
			);
			if ( $id ) {
				bp_notifications_update_meta( $id, 'bpas_count', 1 );
				bp_notifications_update_meta( $id, 'bpas_type', $target['type'] );
			}
		}

		self::maybe_email( $author, $user_id, $target, $repost_id );
	}

	/**
	 * Format for the notification list / toolbar.
	 *
	 * @param mixed  $content           Current content.
	 * @param int    $item_id           Original ID.
	 * @param int    $secondary_item_id Latest reposter.
	 * @param int    $total             Total items.
	 * @param string $format            'string' | 'object' | 'array'.
	 * @param string $action            Component action.
	 * @param string $component         Component.
	 * @param int    $id                Notification ID.
	 * @param string $screen            Screen.
	 * @return mixed
	 */
	public static function format( $content, $item_id, $secondary_item_id, $total, $format, $action, $component, $id = 0, $screen = 'web' ) {
		if ( self::COMPONENT !== $component || self::ACTION !== $action ) {
			return $content;
		}
		unset( $total, $screen );

		$count = max( 1, (int) bp_notifications_get_meta( (int) $id, 'bpas_count', true ) );
		$type  = (string) bp_notifications_get_meta( (int) $id, 'bpas_type', true );
		$name  = bp_core_get_user_displayname( (int) $secondary_item_id );
		$others = $count - 1;
		if ( 'post' === $type ) {
			$text = $others
				/* translators: 1: member name, 2: number of other members. */
				? sprintf( _n( '%1$s and %2$s other reposted your post', '%1$s and %2$s others reposted your post', $others, 'buddypress-activity-share-pro' ), $name, number_format_i18n( $others ) )
				/* translators: %s: member name. */
				: sprintf( __( '%s reposted your post', 'buddypress-activity-share-pro' ), $name );
		} else {
			$text = $others
				/* translators: 1: member name, 2: number of other members. */
				? sprintf( _n( '%1$s and %2$s other reposted your update', '%1$s and %2$s others reposted your update', $others, 'buddypress-activity-share-pro' ), $name, number_format_i18n( $others ) )
				/* translators: %s: member name. */
				: sprintf( __( '%s reposted your update', 'buddypress-activity-share-pro' ), $name );
		}
		$link = 'post' === $type ? get_permalink( (int) $item_id ) : bp_activity_get_permalink( (int) $item_id );
		$link = (string) add_query_arg( 'bpas_read', (int) $id, (string) $link );

		if ( 'string' === $format ) {
			return sprintf( '<a href="%1$s">%2$s</a>', esc_url( $link ), esc_html( $text ) );
		}
		return array(
			'text' => $text,
			'link' => $link,
		);
	}

	/**
	 * Email the author, at most once per original per day, if they allow it.
	 *
	 * @param int   $author    Author.
	 * @param int   $user_id   Reposter.
	 * @param array $target    Original.
	 * @param int   $repost_id Repost.
	 */
	private static function maybe_email( int $author, int $user_id, array $target, int $repost_id ): void {
		if ( 'no' === bp_get_user_meta( $author, self::PREF_META, true ) ) {
			return;
		}
		$key  = 'bpas_mail_' . $target['type'] . '_' . $target['id'];
		$sent = get_transient( $key );
		if ( $sent ) {
			return;
		}
		set_transient( $key, 1, DAY_IN_SECONDS );

		$url = 'post' === $target['type'] ? get_permalink( (int) $target['id'] ) : bp_activity_get_permalink( (int) $target['id'] );
		bp_send_email(
			self::SITUATION,
			$author,
			array(
				'tokens' => array(
					'reposter.name' => bp_core_get_user_displayname( $user_id ),
					'reposter.url'  => esc_url( bp_members_get_user_url( $user_id ) ),
					'item.url'      => esc_url( (string) $url ),
					'repost.url'    => esc_url( bp_activity_get_permalink( $repost_id ) ),
				),
			)
		);
	}

	/**
	 * Member setting under Settings > Email.
	 */
	public static function settings_row(): void {
		$value = bp_get_user_meta( bp_displayed_user_id(), self::PREF_META, true );
		$value = 'no' === $value ? 'no' : 'yes';
		?>
		<table class="notification-settings" id="bpas-notification-settings">
			<thead>
				<tr>
					<th class="icon"></th>
					<th class="title"><?php esc_html_e( 'Reposts', 'buddypress-activity-share-pro' ); ?></th>
					<th class="yes"><?php esc_html_e( 'Yes', 'buddypress-activity-share-pro' ); ?></th>
					<th class="no"><?php esc_html_e( 'No', 'buddypress-activity-share-pro' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td></td>
					<td><?php esc_html_e( 'A member reposts one of your updates', 'buddypress-activity-share-pro' ); ?></td>
					<td class="yes"><input type="radio" name="notifications[<?php echo esc_attr( self::PREF_META ); ?>]" value="yes" <?php checked( 'yes', $value ); ?> aria-label="<?php esc_attr_e( 'Yes, email me about reposts', 'buddypress-activity-share-pro' ); ?>" /></td>
					<td class="no"><input type="radio" name="notifications[<?php echo esc_attr( self::PREF_META ); ?>]" value="no" <?php checked( 'no', $value ); ?> aria-label="<?php esc_attr_e( 'No, do not email me about reposts', 'buddypress-activity-share-pro' ); ?>" /></td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * One-click unsubscribe support for our email.
	 *
	 * @param array $schema Schema.
	 */
	public static function unsubscribe_schema( $schema ): array {
		$schema                    = (array) $schema;
		$schema[ self::SITUATION ] = array(
			'description' => __( 'A member reposts one of your updates.', 'buddypress-activity-share-pro' ),
			'unsubscribe' => array(
				'meta_key' => self::PREF_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_key -- BuddyPress schema key.
				'message'  => __( 'You will no longer receive emails when someone reposts your updates.', 'buddypress-activity-share-pro' ),
			),
		);
		return $schema;
	}

	/**
	 * Create the email template once (BuddyPress stores emails as posts).
	 */
	public static function install_email(): void {
		if ( ! function_exists( 'bp_get_email_post_type' ) || term_exists( self::SITUATION, bp_get_email_tax_type() ) ) {
			return;
		}
		$post_id = wp_insert_post(
			array(
				'post_status'  => 'publish',
				'post_type'    => bp_get_email_post_type(),
				/* translators: do not translate the {{tokens}}. */
				'post_title'   => __( '[{{{site.name}}}] {{reposter.name}} reposted your update', 'buddypress-activity-share-pro' ),
				/* translators: do not translate the {{tokens}}. */
				'post_content' => __( '<a href="{{{reposter.url}}}">{{reposter.name}}</a> reposted your update.<br /><br /><a href="{{{repost.url}}}">See the repost</a> or <a href="{{{item.url}}}">view your update</a>.', 'buddypress-activity-share-pro' ),
				/* translators: do not translate the {{tokens}}. */
				'post_excerpt' => __( "{{reposter.name}} reposted your update.\n\nSee the repost: {{{repost.url}}}\n\nView your update: {{{item.url}}}", 'buddypress-activity-share-pro' ),
			)
		);
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			$term = wp_insert_term( self::SITUATION, bp_get_email_tax_type(), array( 'description' => __( 'A member reposts one of your updates.', 'buddypress-activity-share-pro' ) ) );
			if ( ! is_wp_error( $term ) ) {
				wp_set_post_terms( $post_id, array( (int) $term['term_id'] ), bp_get_email_tax_type() );
			}
		}
	}
}
