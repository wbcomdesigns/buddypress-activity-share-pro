<?php
/**
 * Trending / most-shared block and widget (X3).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Analytics;

use BPAS_Pro\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * Server-rendered block `bpas/trending` + legacy widget. Public items only.
 */
final class Trending {

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_action( 'widgets_init', array( __CLASS__, 'register_widget' ) );
	}

	/**
	 * Block.
	 */
	public static function register_block(): void {
		register_block_type(
			'bpas/trending',
			array(
				'api_version'     => 3,
				'title'           => __( 'Most shared', 'buddypress-activity-share-pro' ),
				'description'     => __( 'The community posts shared most recently.', 'buddypress-activity-share-pro' ),
				'category'        => 'widgets',
				'icon'            => 'chart-line',
				'editor_script'   => 'bpas-pro-block',
				'attributes'      => array(
					'days'  => array(
						'type'    => 'number',
						'default' => 7,
					),
					'limit' => array(
						'type'    => 'number',
						'default' => 5,
					),
				),
				'render_callback' => array( __CLASS__, 'render' ),
				'supports'        => array( 'html' => false ),
			)
		);
	}

	/**
	 * Legacy widget.
	 */
	public static function register_widget(): void {
		register_widget( Trending_Widget::class );
	}

	/**
	 * Render the list.
	 *
	 * @param array $atts days (7|30), limit.
	 */
	public static function render( $atts = array() ): string {
		$days  = 30 === (int) ( $atts['days'] ?? 7 ) ? 30 : 7;
		$limit = min( 20, max( 1, (int) ( $atts['limit'] ?? 5 ) ) );
		$items = Analytics::trending( $days, $limit );
		Assets::enqueue_front();

		if ( empty( $items ) ) {
			return '<p class="bpas-pro-trending__empty">' . esc_html__( 'Nothing has been shared yet.', 'buddypress-activity-share-pro' ) . '</p>';
		}
		$html = '<ol class="bpas-pro-trending">';
		foreach ( $items as $item ) {
			$html .= sprintf(
				'<li><a href="%1$s">%2$s</a><span class="bpas-pro-trending__count">%3$s</span></li>',
				esc_url( $item['url'] ),
				esc_html( $item['title'] ),
				/* translators: %s: number of shares. */
				esc_html( sprintf( _n( '%s share', '%s shares', $item['count'], 'buddypress-activity-share-pro' ), number_format_i18n( $item['count'] ) ) )
			);
		}
		return $html . '</ol>';
	}
}
