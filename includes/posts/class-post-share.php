<?php
/**
 * Share buttons on posts / pages / any public post type, plus the Share block and [bpas_share] (C1, C2).
 *
 * Uses Free's one share menu - no second renderer.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Posts;

use BPAS_Pro\Assets;
use BPAS_Pro\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Placement per post type: above / below the content, or a floating button.
 */
final class Post_Share {

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue' ), 12 );
		add_filter( 'the_content', array( __CLASS__, 'filter_content' ), 20 );
		add_action( 'wp_footer', array( __CLASS__, 'floating' ) );
	}

	/**
	 * Placement for the current singular view, or ''.
	 */
	private static function placement(): string {
		if ( ! is_singular() || is_front_page() || ( function_exists( 'is_buddypress' ) && is_buddypress() ) ) {
			return '';
		}
		$types = Settings::content_types();
		$type  = get_post_type();
		return isset( $types[ $type ] ) ? (string) $types[ $type ] : '';
	}

	/**
	 * Enqueue in the head on pages that will show buttons (no late styles).
	 */
	public static function maybe_enqueue(): void {
		if ( '' !== self::placement() ) {
			Assets::enqueue_front();
		}
	}

	/**
	 * Above / below the main post content.
	 *
	 * @param string $content Content.
	 */
	public static function filter_content( $content ): string {
		$placement = self::placement();
		if ( ! in_array( $placement, array( 'above', 'below' ), true ) || ! in_the_loop() || ! is_main_query() || get_the_ID() !== get_queried_object_id() ) {
			return (string) $content;
		}
		if ( self::placed_by_hand( (int) get_the_ID() ) ) {
			return (string) $content;
		}
		$menu = self::menu( (int) get_the_ID(), $placement );
		return 'above' === $placement ? $menu . $content : $content . $menu;
	}

	/**
	 * The owner put [bpas_share] or the Share block in this post: that is where the menu goes,
	 * so the automatic above / below / floating menu stands down instead of showing a second one.
	 *
	 * @param int $post_id Post.
	 */
	private static function placed_by_hand( int $post_id ): bool {
		$post = get_post( $post_id );
		return $post && ( has_shortcode( $post->post_content, 'bpas_share' ) || has_block( 'bpas/share', $post ) );
	}

	/**
	 * Floating button.
	 */
	public static function floating(): void {
		if ( 'floating' === self::placement() && ! self::placed_by_hand( (int) get_queried_object_id() ) ) {
			echo self::menu( (int) get_queried_object_id(), 'floating' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Free's template escapes.
		}
	}

	/**
	 * [bpas_share id="123"] - defaults to the current post.
	 *
	 * @param array|string $atts Attributes.
	 */
	public static function shortcode( $atts ): string {
		$atts = shortcode_atts( array( 'id' => 0 ), (array) $atts, 'bpas_share' );
		$id   = absint( $atts['id'] ) ? absint( $atts['id'] ) : (int) get_the_ID();
		Assets::enqueue_front();
		return $id ? self::menu( $id, 'inline' ) : '';
	}

	/**
	 * Server-rendered Share block.
	 */
	public static function register_block(): void {
		wp_register_script( 'bpas-pro-block', BPAS_PRO_URL . 'assets/js/bpas-pro-block.js', array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-server-side-render', 'wp-block-editor', 'wp-components' ), BPAS_PRO_VERSION, true );
		wp_set_script_translations( 'bpas-pro-block', 'buddypress-activity-share-pro', BPAS_PRO_DIR . 'languages' );
		register_block_type(
			'bpas/share',
			array(
				'api_version'     => 3,
				'title'           => __( 'Share buttons', 'buddypress-activity-share-pro' ),
				'description'     => __( 'The share menu for this page.', 'buddypress-activity-share-pro' ),
				'category'        => 'widgets',
				'icon'            => 'share',
				'editor_script'   => 'bpas-pro-block',
				'render_callback' => array( __CLASS__, 'render_block' ),
				'supports'        => array( 'html' => false ),
			)
		);
	}

	/**
	 * Block render.
	 */
	public static function render_block(): string {
		$id = (int) get_the_ID();
		if ( ! $id ) {
			return '';
		}
		Assets::enqueue_front();
		return self::menu( $id, 'inline' );
	}

	/**
	 * Menu markup for a post.
	 *
	 * @param int    $post_id   Post.
	 * @param string $placement above | below | floating | inline.
	 */
	private static function menu( int $post_id, string $placement ): string {
		$ctx = bpas_share_context( 'post', $post_id );
		if ( ! $ctx ) {
			return '';
		}
		$html = bpas_render_share_menu( $ctx, array( 'position' => 'floating' === $placement ? 'before' : 'after' ) );
		return '' === $html ? '' : sprintf( '<div class="bpas-pro-post-share bpas-pro-post-share--%s">%s</div>', esc_attr( $placement ), $html );
	}
}
