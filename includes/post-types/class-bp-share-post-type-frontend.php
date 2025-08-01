<?php
/**
 * Post Type Sharing Frontend Renderer
 *
 * @package BuddyPress_Share_Pro
 * @subpackage Post_Types
 * @since 2.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend renderer for post type sharing.
 *
 * @since 2.1.0
 */
class BP_Share_Post_Type_Frontend {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the frontend renderer.
	 */
	private function __construct() {}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Render the sticky sharing wrapper.
	 */
	public function render_sticky_wrapper() {
		$post_id = get_the_ID();
		$post_type = get_post_type();
		
		$settings = BP_Share_Post_Type_Settings::get_instance();
		$services = $settings->get_services_for_post_type( $post_type );
		$position = $settings->get_display_position();
		$style = $settings->get_display_style();
		$mobile = $settings->get_mobile_behavior();
		
		$controller = BP_Share_Post_Type_Controller::get_instance();
		$share_count = $controller->get_share_count( $post_id );
		
		$wrapper_classes = array(
			'bp-share-floating-wrapper',
			'bp-share-position-' . $position,
			'bp-share-style-' . $style,
			'bp-share-mobile-' . $mobile
		);
		
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
		     data-post-id="<?php echo esc_attr( $post_id ); ?>" 
		     data-post-type="<?php echo esc_attr( $post_type ); ?>">
			
			<div class="bp-share-toggle">
				<span class="bp-share-icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="18" cy="5" r="3"></circle>
						<circle cx="6" cy="12" r="3"></circle>
						<circle cx="18" cy="19" r="3"></circle>
						<line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
						<line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
					</svg>
				</span>
				<?php if ( $share_count > 0 ) : ?>
					<span class="bp-share-count"><?php echo $this->format_count( $share_count ); ?></span>
				<?php endif; ?>
			</div>
			
			<div class="bp-share-services">
				<div class="bp-share-services-inner">
					<?php foreach ( $services as $service_key ) : 
						$service = $settings->get_service_info( $service_key );
						if ( ! $service ) continue;
						$share_url = BP_Share_Post_Type_Controller::get_share_url( $service_key, $post_id );
						?>
						<a href="<?php echo esc_url( $share_url ); ?>" 
						   class="bp-share-service bp-share-service-<?php echo esc_attr( $service_key ); ?>" 
						   data-service="<?php echo esc_attr( $service_key ); ?>"
						   title="<?php echo esc_attr( sprintf( __( 'Share on %s', 'buddypress-share' ), $service['name'] ) ); ?>"
						   <?php if ( $service_key !== 'print' && $service_key !== 'copy' && $service_key !== 'email' ) : ?>
						   target="_blank"
						   rel="noopener noreferrer"
						   <?php endif; ?>>
							<i class="<?php echo esc_attr( $service['icon'] ); ?>"></i>
							<span class="service-name"><?php echo esc_html( $service['name'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
			
			<div class="bp-share-tooltip" style="display: none;">
				<span class="bp-share-tooltip-text"></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Render inline share buttons.
	 *
	 * @param array $args Arguments for rendering.
	 */
	public function render_inline_buttons( $args = array() ) {
		$defaults = array(
			'post_id' => get_the_ID(),
			'services' => array(),
			'style' => 'buttons', // buttons, icons, text
			'size' => 'medium', // small, medium, large
			'show_count' => true,
			'show_labels' => true
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		$post_type = get_post_type( $args['post_id'] );
		$settings = BP_Share_Post_Type_Settings::get_instance();
		
		// Use provided services or get from settings
		$services = ! empty( $args['services'] ) ? 
			$args['services'] : 
			$settings->get_services_for_post_type( $post_type );
		
		$wrapper_classes = array(
			'bp-share-inline-wrapper',
			'bp-share-style-' . $args['style'],
			'bp-share-size-' . $args['size']
		);
		
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
			<?php if ( $args['show_count'] ) : 
				$controller = BP_Share_Post_Type_Controller::get_instance();
				$count = $controller->get_share_count( $args['post_id'] );
				?>
				<div class="bp-share-inline-count">
					<span class="count-number"><?php echo $this->format_count( $count ); ?></span>
					<span class="count-label"><?php esc_html_e( 'Shares', 'buddypress-share' ); ?></span>
				</div>
			<?php endif; ?>
			
			<div class="bp-share-inline-buttons">
				<?php foreach ( $services as $service_key ) : 
					$service = $settings->get_service_info( $service_key );
					if ( ! $service ) continue;
					
					$share_url = BP_Share_Post_Type_Controller::get_share_url( $service_key, $args['post_id'] );
					?>
					<a href="<?php echo esc_url( $share_url ); ?>" 
					   class="bp-share-button bp-share-button-<?php echo esc_attr( $service_key ); ?>" 
					   data-service="<?php echo esc_attr( $service_key ); ?>"
					   data-post-id="<?php echo esc_attr( $args['post_id'] ); ?>"
					   <?php if ( $service_key !== 'print' && $service_key !== 'copy' ) : ?>
					   target="_blank" 
					   rel="noopener noreferrer"
					   <?php endif; ?>>
						<i class="<?php echo esc_attr( $service['icon'] ); ?>"></i>
						<?php if ( $args['show_labels'] ) : ?>
							<span class="button-label"><?php echo esc_html( $service['name'] ); ?></span>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Format share count for display.
	 *
	 * @param int $count Share count.
	 * @return string Formatted count.
	 */
	private function format_count( $count ) {
		if ( $count < 1000 ) {
			return $count;
		} elseif ( $count < 1000000 ) {
			return round( $count / 1000, 1 ) . 'K';
		} else {
			return round( $count / 1000000, 1 ) . 'M';
		}
	}

	/**
	 * Get the HTML for a single share button.
	 *
	 * @param string $service Service key.
	 * @param int    $post_id Post ID.
	 * @param array  $args    Additional arguments.
	 * @return string Button HTML.
	 */
	public function get_share_button_html( $service, $post_id, $args = array() ) {
		$settings = BP_Share_Post_Type_Settings::get_instance();
		$service_info = $settings->get_service_info( $service );
		
		if ( ! $service_info ) {
			return '';
		}
		
		$defaults = array(
			'style' => 'icon', // icon, button, text
			'size' => 'medium',
			'show_label' => false,
			'custom_class' => ''
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		$share_url = BP_Share_Post_Type_Controller::get_share_url( $service, $post_id );
		
		$classes = array(
			'bp-share-single-button',
			'bp-share-' . $service,
			'bp-share-style-' . $args['style'],
			'bp-share-size-' . $args['size'],
			$args['custom_class']
		);
		
		ob_start();
		?>
		<a href="<?php echo esc_url( $share_url ); ?>" 
		   class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" 
		   data-service="<?php echo esc_attr( $service ); ?>"
		   data-post-id="<?php echo esc_attr( $post_id ); ?>"
		   title="<?php echo esc_attr( sprintf( __( 'Share on %s', 'buddypress-share' ), $service_info['name'] ) ); ?>"
		   <?php if ( $service !== 'print' && $service !== 'copy' ) : ?>
		   target="_blank" 
		   rel="noopener noreferrer"
		   <?php endif; ?>>
			<i class="<?php echo esc_attr( $service_info['icon'] ); ?>"></i>
			<?php if ( $args['show_label'] ) : ?>
				<span class="bp-share-label"><?php echo esc_html( $service_info['name'] ); ?></span>
			<?php endif; ?>
		</a>
		<?php
		return ob_get_clean();
	}
}