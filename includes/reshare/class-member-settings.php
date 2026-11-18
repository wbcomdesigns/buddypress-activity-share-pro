<?php
/**
 * Member control: "Allow others to repost my posts" (Settings > General).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Reshare;

defined( 'ABSPATH' ) || exit;

/**
 * One checkbox, default on. Stored as user meta `bpas_pro_allow_repost` ('no' when off).
 */
final class Member_Settings {

	const META = 'bpas_pro_allow_repost';

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_action( 'bp_core_general_settings_before_submit', array( __CLASS__, 'field' ) );
		add_action( 'bp_core_general_settings_after_save', array( __CLASS__, 'save' ) );
	}

	/**
	 * Checkbox.
	 */
	public static function field(): void {
		$allowed = 'no' !== get_user_meta( bp_displayed_user_id(), self::META, true );
		wp_nonce_field( 'bpas_member_repost', 'bpas_member_repost_nonce' );
		?>
		<p class="bpas-pro-member-setting">
			<input type="hidden" name="bpas_allow_repost_present" value="1" />
			<label for="bpas-allow-repost">
				<input type="checkbox" id="bpas-allow-repost" name="bpas_allow_repost" value="yes" <?php checked( $allowed ); ?> />
				<?php esc_html_e( 'Allow other members to repost my posts', 'buddypress-activity-share-pro' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Save for the displayed user (BuddyPress already checked they may edit these settings).
	 */
	public static function save(): void {
		if ( empty( $_POST['bpas_allow_repost_present'] ) || ! isset( $_POST['bpas_member_repost_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bpas_member_repost_nonce'] ) ), 'bpas_member_repost' ) ) {
			return;
		}
		$user_id = bp_displayed_user_id();
		if ( ! $user_id || ( get_current_user_id() !== $user_id && ! current_user_can( 'bp_moderate' ) ) ) {
			return;
		}
		if ( isset( $_POST['bpas_allow_repost'] ) ) {
			delete_user_meta( $user_id, self::META );
		} else {
			update_user_meta( $user_id, self::META, 'no' );
		}
	}
}
