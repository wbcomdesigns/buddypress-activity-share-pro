<?php
/**
 * Share image card for text-only activity (X1).
 *
 * The first time a card is needed (link preview or share), it is drawn once with GD and cached
 * as uploads/bpas-cards/{id}-{hash}.png; it is removed with the activity. Without GD + FreeType
 * the site image is used as before.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Cards;

defined( 'ABSPATH' ) || exit;

/**
 * Swaps the fallback site image for a generated card.
 */
final class Image_Card {

	const DIR = 'bpas-cards';

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		if ( ! function_exists( 'imagettftext' ) || ! function_exists( 'imagepng' ) ) {
			return;
		}
		add_filter( 'bpas_preview_image', array( __CLASS__, 'preview_image' ), 10, 2 );
		add_action( 'template_redirect', array( __CLASS__, 'serve' ), 1 );
		add_action( 'bp_activity_deleted_activities', array( __CLASS__, 'delete_cards' ) );
	}

	/**
	 * Activity without an image of its own: preview it with its card instead of the site image.
	 *
	 * @param string $image Stand-in image from Free (site icon or logo).
	 * @param object $ctx   Share context.
	 * @return string
	 */
	public static function preview_image( $image, $ctx ) {
		if ( 'activity' !== $ctx->type || ! bpas_can_share_externally( $ctx ) ) {
			return $image;
		}
		$hash = self::hash( $ctx->object );
		$file = self::path( (int) $ctx->id, $hash );
		// Existing card: its static URL. Otherwise a generator URL that creates it on first request.
		return file_exists( $file ) ? self::url( (int) $ctx->id, $hash ) : add_query_arg(
			array(
				'bpas_card' => (int) $ctx->id,
				'h'         => $hash,
			),
			home_url( '/' )
		);
	}

	/**
	 * Generator endpoint: draw once, then redirect to the static file.
	 */
	public static function serve(): void {
		$id = isset( $_GET['bpas_card'] ) ? absint( $_GET['bpas_card'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public, read-only, idempotent.
		if ( ! $id ) {
			return;
		}
		$ctx = bpas_share_context( 'activity', $id );
		if ( ! $ctx || ! bpas_can_share_externally( $ctx ) ) {
			status_header( 404 );
			exit;
		}
		$hash = self::hash( $ctx->object );
		$file = self::path( $id, $hash );
		if ( ! file_exists( $file ) && ! self::draw( $ctx, $file ) ) {
			status_header( 404 );
			exit;
		}
		wp_safe_redirect( self::url( $id, $hash ), 302 );
		exit;
	}

	/**
	 * Draw the card.
	 *
	 * @param object $ctx  Share context.
	 * @param string $file Target file.
	 */
	private static function draw( $ctx, string $file ): bool {
		$font = BPAS_PRO_DIR . 'assets/fonts/DejaVuSans.ttf';
		if ( ! is_readable( $font ) || ! wp_mkdir_p( dirname( $file ) ) ) {
			return false;
		}
		$colors = (array) apply_filters(
			'bpas_pro_card_colors',
			array(
				'background' => '#0f172a',
				'text'       => '#f8fafc',
				'muted'      => '#94a3b8',
				'accent'     => '#3b82f6',
			)
		);

		$w   = 1200;
		$h   = 630;
		$img = imagecreatetruecolor( $w, $h );
		$bg  = self::color( $img, $colors['background'] );
		$fg  = self::color( $img, $colors['text'] );
		$mut = self::color( $img, $colors['muted'] );
		$acc = self::color( $img, $colors['accent'] );
		imagefilledrectangle( $img, 0, 0, $w, $h, $bg );
		imagefilledrectangle( $img, 0, 0, 16, $h, $acc );

		$author = bp_core_get_user_displayname( (int) $ctx->object->user_id );
		$text   = wp_trim_words( wp_strip_all_tags( (string) $ctx->object->content ), 45, '...' );
		if ( '' === $text ) {
			$text = wp_strip_all_tags( (string) $ctx->title );
		}

		imagettftext( $img, 30, 0, 80, 110, $fg, $font, self::fit( $author, 30, $font, $w - 160 ) );
		$y = 190;
		foreach ( array_slice( self::wrap( $text, 36, $font, $w - 160 ), 0, 6 ) as $line ) {
			imagettftext( $img, 36, 0, 80, $y, $fg, $font, $line );
			$y += 62;
		}
		imagettftext( $img, 22, 0, 80, $h - 60, $mut, $font, self::fit( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), 22, $font, $w - 160 ) );

		$ok = imagepng( $img, $file, 6 );
		imagedestroy( $img );
		return (bool) $ok;
	}

	/**
	 * Remove cards of deleted activity.
	 *
	 * @param int[] $ids Activity IDs.
	 */
	public static function delete_cards( $ids ): void {
		foreach ( (array) $ids as $id ) {
			foreach ( (array) glob( self::path( (int) $id, '*' ) ) as $file ) {
				wp_delete_file( $file );
			}
		}
	}

	/**
	 * Content hash: a changed post gets a new card.
	 *
	 * @param object $activity Activity.
	 */
	private static function hash( object $activity ): string {
		return substr( md5( (string) $activity->content . '|' . (int) $activity->user_id . '|' . get_bloginfo( 'name' ) ), 0, 10 );
	}

	/**
	 * File path.
	 *
	 * @param int    $id   Activity.
	 * @param string $hash Hash or '*'.
	 */
	private static function path( int $id, string $hash ): string {
		$uploads = wp_upload_dir( null, false );
		return trailingslashit( $uploads['basedir'] ) . self::DIR . '/' . $id . '-' . $hash . '.png';
	}

	/**
	 * File URL.
	 *
	 * @param int    $id   Activity.
	 * @param string $hash Hash.
	 */
	private static function url( int $id, string $hash ): string {
		$uploads = wp_upload_dir( null, false );
		return trailingslashit( $uploads['baseurl'] ) . self::DIR . '/' . $id . '-' . $hash . '.png';
	}

	/**
	 * Allocate a hex colour.
	 *
	 * @param \GdImage $img Image.
	 * @param string   $hex Hex.
	 * @return int
	 */
	private static function color( $img, string $hex ): int {
		$clean = (string) sanitize_hex_color( $hex );
		$hex   = ltrim( '' !== $clean ? $clean : '#000000', '#' );
		return (int) imagecolorallocate( $img, (int) hexdec( substr( $hex, 0, 2 ) ), (int) hexdec( substr( $hex, 2, 2 ) ), (int) hexdec( substr( $hex, 4, 2 ) ) );
	}

	/**
	 * Word-wrap to a pixel width.
	 *
	 * @param string $text  Text.
	 * @param int    $size  Font size.
	 * @param string $font  Font file.
	 * @param int    $width Max width.
	 * @return string[]
	 */
	private static function wrap( string $text, int $size, string $font, int $width ): array {
		$lines = array();
		$line  = '';
		foreach ( preg_split( '/\s+/u', $text ) as $word ) {
			$try = '' === $line ? $word : $line . ' ' . $word;
			$box = imagettfbbox( $size, 0, $font, $try );
			if ( $box && ( $box[2] - $box[0] ) > $width && '' !== $line ) {
				$lines[] = $line;
				$line    = $word;
			} else {
				$line = $try;
			}
		}
		if ( '' !== $line ) {
			$lines[] = $line;
		}
		return $lines;
	}

	/**
	 * Shorten one line to a pixel width.
	 *
	 * @param string $text  Text.
	 * @param int    $size  Size.
	 * @param string $font  Font.
	 * @param int    $width Width.
	 */
	private static function fit( string $text, int $size, string $font, int $width ): string {
		$lines = self::wrap( $text, $size, $font, $width );
		return count( $lines ) > 1 ? $lines[0] . '...' : ( $lines[0] ?? '' );
	}
}
