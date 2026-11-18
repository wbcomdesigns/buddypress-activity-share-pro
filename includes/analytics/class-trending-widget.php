<?php
/**
 * Legacy "Most shared" widget.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Analytics;

defined( 'ABSPATH' ) || exit;

/**
 * Thin wrapper around Trending::render().
 */
final class Trending_Widget extends \WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'bpas_pro_trending', __( 'Most shared', 'buddypress-activity-share-pro' ), array( 'description' => __( 'The community posts shared most recently.', 'buddypress-activity-share-pro' ) ) );
	}

	/**
	 * Output.
	 *
	 * @param array $args     Sidebar args.
	 * @param array $instance Settings.
	 */
	public function widget( $args, $instance ): void {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Most shared', 'buddypress-activity-share-pro' );
		echo $args['before_widget'] . $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-provided wrappers.
		echo Trending::render( array( 'days' => (int) ( $instance['days'] ?? 7 ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in render().
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-provided wrapper.
	}

	/**
	 * Settings form.
	 *
	 * @param array $instance Settings.
	 */
	public function form( $instance ): string {
		$title = (string) ( $instance['title'] ?? '' );
		$days  = (int) ( $instance['days'] ?? 7 );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'buddypress-activity-share-pro' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'days' ) ); ?>"><?php esc_html_e( 'Period', 'buddypress-activity-share-pro' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'days' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'days' ) ); ?>">
				<option value="7" <?php selected( 7, $days ); ?>><?php esc_html_e( 'Last 7 days', 'buddypress-activity-share-pro' ); ?></option>
				<option value="30" <?php selected( 30, $days ); ?>><?php esc_html_e( 'Last 30 days', 'buddypress-activity-share-pro' ); ?></option>
			</select>
		</p>
		<?php
		return '';
	}

	/**
	 * Save.
	 *
	 * @param array $new_instance New.
	 * @param array $old_instance Old.
	 */
	public function update( $new_instance, $old_instance ): array {
		unset( $old_instance );
		return array(
			'title' => sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) ),
			'days'  => 30 === (int) ( $new_instance['days'] ?? 7 ) ? 30 : 7,
		);
	}
}
