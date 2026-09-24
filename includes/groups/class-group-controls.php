<?php
/**
 * Group admin sharing controls (X5): public groups only; private / hidden never share outside.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Groups;

use BPAS_Pro\Reshare\Reshare_Service;

defined( 'ABSPATH' ) || exit;

/**
 * Group > Manage > Settings: "Allow sharing outside this group", "Allow reposting out of this group".
 */
final class Group_Controls {

	const EXTERNAL   = 'bpas_allow_external';
	const REPOST_OUT = 'bpas_allow_repost_out';

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_action( 'bp_after_group_settings_admin', array( __CLASS__, 'fields' ) );
		add_action( 'groups_group_settings_edited', array( __CLASS__, 'save' ) );
		add_filter( 'bpas_can_share_externally', array( __CLASS__, 'filter_external' ), 10, 2 );
	}

	/**
	 * Settings fields (public groups only).
	 */
	public static function fields(): void {
		$group = groups_get_current_group();
		if ( empty( $group->id ) || 'public' !== $group->status ) {
			return;
		}
		wp_nonce_field( 'bpas_group_sharing', 'bpas_group_sharing_nonce' );
		?>
		<fieldset class="group-create-privacy bpas-pro-group-sharing">
			<legend><?php esc_html_e( 'Sharing', 'buddypress-activity-share-pro' ); ?></legend>
			<label for="bpas-allow-external">
				<input type="checkbox" id="bpas-allow-external" name="<?php echo esc_attr( self::EXTERNAL ); ?>" value="yes" <?php checked( self::allows( (int) $group->id, self::EXTERNAL ) ); ?> />
				<?php esc_html_e( 'Members can share posts from this group to other sites (Facebook, X, WhatsApp...)', 'buddypress-activity-share-pro' ); ?>
			</label>
			<label for="bpas-allow-repost-out">
				<input type="checkbox" id="bpas-allow-repost-out" name="<?php echo esc_attr( self::REPOST_OUT ); ?>" value="yes" <?php checked( self::allows( (int) $group->id, self::REPOST_OUT ) ); ?> />
				<?php esc_html_e( 'Members can repost posts from this group to their profile or other groups', 'buddypress-activity-share-pro' ); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Save (group admins / site moderators only - BuddyPress already gates the screen).
	 *
	 * @param int $group_id Group.
	 */
	public static function save( $group_id ): void {
		$group_id = (int) $group_id;
		if ( ! isset( $_POST['bpas_group_sharing_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bpas_group_sharing_nonce'] ) ), 'bpas_group_sharing' ) ) {
			return;
		}
		if ( ! groups_is_user_admin( get_current_user_id(), $group_id ) && ! current_user_can( 'bp_moderate' ) ) {
			return;
		}
		foreach ( array( self::EXTERNAL, self::REPOST_OUT ) as $key ) {
			groups_update_groupmeta( $group_id, $key, isset( $_POST[ $key ] ) ? 'yes' : 'no' );
		}
	}

	/**
	 * Free's external-share rule, narrowed by the group's own setting.
	 *
	 * @param bool   $public Free's decision.
	 * @param object $ctx    Share context.
	 */
	public static function filter_external( $public, $ctx ): bool {
		if ( ! $public ) {
			return false;
		}
		if ( 'group' === $ctx->type ) {
			return self::allows( (int) $ctx->id, self::EXTERNAL );
		}
		if ( 'activity' === $ctx->type ) {
			Reshare_Service::remember( $ctx->object );
			$group_id = Reshare_Service::group_of( $ctx->object );
			return ! $group_id || self::allows( $group_id, self::EXTERNAL );
		}
		return true;
	}

	/**
	 * Default: allowed.
	 *
	 * @param int    $group_id Group.
	 * @param string $key      Meta key.
	 */
	private static function allows( int $group_id, string $key ): bool {
		return 'no' !== groups_get_groupmeta( $group_id, $key );
	}
}
