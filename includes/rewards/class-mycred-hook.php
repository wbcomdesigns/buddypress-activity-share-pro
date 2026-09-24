<?php
/**
 * The myCRED hook - loaded only from the mycred_setup_hooks filter (myCRED_Hook exists).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Rewards;

defined( 'ABSPATH' ) || exit;

/**
 * Configured under Points > Hooks > Activity Share.
 */
class Mycred_Hook extends \myCRED_Hook {

	/**
	 * Constructor.
	 *
	 * @param array  $hook_prefs Saved prefs.
	 * @param string $type       Point type.
	 */
	public function __construct( $hook_prefs, $type = 'mycred_default' ) {
		parent::__construct(
			array(
				'id'       => 'bpas_share',
				'defaults' => array(
					'visit'  => array(
						'creds' => 1,
						'log'   => __( 'Shared post brought a visitor', 'buddypress-activity-share-pro' ),
					),
					'signup' => array(
						'creds' => 10,
						'log'   => __( 'Shared post brought a new member', 'buddypress-activity-share-pro' ),
					),
				),
			),
			$hook_prefs,
			$type
		);
	}

	/**
	 * Attach listeners.
	 */
	public function run() {
		add_action( 'bpas_pro_share_visit', array( $this, 'visit' ), 10, 3 );
		add_action( 'bpas_pro_share_signup', array( $this, 'signup' ), 10, 2 );
	}

	/**
	 * Visit points.
	 *
	 * @param int    $sharer Member.
	 * @param string $type   Object type.
	 * @param int    $id     Object ID.
	 */
	public function visit( $sharer, $type = '', $id = 0 ) {
		unset( $type );
		$this->core->add_creds( 'bpas_share_visit', (int) $sharer, $this->prefs['visit']['creds'], $this->prefs['visit']['log'], (int) $id, '', $this->mycred_type );
	}

	/**
	 * Sign-up points.
	 *
	 * @param int $sharer   Member.
	 * @param int $new_user New member.
	 */
	public function signup( $sharer, $new_user = 0 ) {
		$this->core->add_creds( 'bpas_share_signup', (int) $sharer, $this->prefs['signup']['creds'], $this->prefs['signup']['log'], (int) $new_user, '', $this->mycred_type );
	}

	/**
	 * Hook settings in Points > Hooks.
	 */
	public function preferences() {
		foreach ( array( 'visit', 'signup' ) as $key ) {
			?>
			<div class="hook-instance">
				<label class="subheader"><?php echo 'visit' === $key ? esc_html__( 'Visitor from a shared post', 'buddypress-activity-share-pro' ) : esc_html__( 'New member from a shared post', 'buddypress-activity-share-pro' ); ?></label>
				<div class="form-group">
					<input type="text" name="<?php echo esc_attr( $this->field_name( array( $key => 'creds' ) ) ); ?>" value="<?php echo esc_attr( $this->core->number( $this->prefs[ $key ]['creds'] ) ); ?>" class="form-control" />
				</div>
				<div class="form-group">
					<input type="text" name="<?php echo esc_attr( $this->field_name( array( $key => 'log' ) ) ); ?>" value="<?php echo esc_attr( $this->prefs[ $key ]['log'] ); ?>" class="form-control" />
				</div>
			</div>
			<?php
		}
	}
}
