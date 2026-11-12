<?php
/**
 * Short share links /s/{code} (X8). Replaces tracking query params; the code reveals no member ID.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Links;

use BPAS_Pro\Settings;
use BPAS_Pro\Tracking\Events;
use BPAS_Pro\Tracking\Tracker;

defined( 'ABSPATH' ) || exit;

/**
 * The code is one AES block holding (type, object id, sharer id) plus 7 zero bytes, sealed with a
 * key derived from the site's salts: nobody without the key can read the sharer or forge a code
 * (a random code passes the zero-byte check about once in 2^56 tries). No table, no lookup.
 */
final class Short_Links {

	const COOKIE = 'bpas_ref';

	/**
	 * Type byte inside the code.
	 */
	const TYPES = array(
		'activity' => 1,
		'post'     => 2,
	);

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'rewrite' ), 20 );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'resolve' ), 1 );
		add_filter( 'bpas_share_url', array( __CLASS__, 'share_url' ), 10, 3 );
		add_action( 'user_register', array( __CLASS__, 'on_signup' ) );
		add_action( 'wp_login', array( __CLASS__, 'forget' ) );
	}

	/**
	 * URL prefix: /s/, or /share/ when any public post already uses the slug s.
	 */
	public static function prefix(): string {
		$prefix = get_page_by_path( 's', OBJECT, get_post_types( array( 'public' => true ) ) ) ? 'share' : 's';
		return (string) apply_filters( 'bpas_pro_short_link_prefix', $prefix );
	}

	/**
	 * Rewrite rule (+ one flush after upgrades or toggles, or when the stored rules lack it,
	 * e.g. the prefix moved to /share/ because a post took the slug s).
	 */
	public static function rewrite(): void {
		$regex = '^' . preg_quote( self::prefix(), '/' ) . '/([A-Za-z0-9_-]{22})/?$';
		add_rewrite_rule( $regex, 'index.php?bpas_code=$matches[1]', 'top' );
		$rules = get_option( 'rewrite_rules' );
		if ( get_option( 'bpas_pro_flush_rewrites' ) || ( is_array( $rules ) && ! isset( $rules[ $regex ] ) ) ) {
			delete_option( 'bpas_pro_flush_rewrites' );
			flush_rewrite_rules( false );
		}
	}

	/**
	 * Query vars.
	 *
	 * @param array $vars Vars.
	 */
	public static function query_vars( $vars ): array {
		$vars   = (array) $vars;
		$vars[] = 'bpas_code';
		return $vars;
	}

	/**
	 * Short link for a context.
	 *
	 * @param string $url     Current URL.
	 * @param array  $network Network.
	 * @param object $ctx     Share context.
	 */
	public static function share_url( $url, $network, $ctx ): string {
		unset( $network );
		if ( ! isset( self::TYPES[ $ctx->type ] ) ) {
			return (string) $url;
		}
		$short = self::for_object( $ctx->type, (int) $ctx->id, get_current_user_id() );
		return '' !== $short ? $short : (string) $url;
	}

	/**
	 * Build a short link ('' when it cannot be built: no OpenSSL, or an ID beyond 32 bits).
	 *
	 * @param string $type    'activity' | 'post'.
	 * @param int    $id      Object ID.
	 * @param int    $sharer  Sharing member (0 = visitor).
	 */
	public static function for_object( string $type, int $id, int $sharer ): string {
		$code = self::encode( $type, $id, $sharer );
		return '' !== $code ? home_url( '/' . self::prefix() . '/' . $code . '/' ) : '';
	}

	/**
	 * Visit: verify, record, credit, redirect.
	 *
	 * 302, not 301: browsers cache a 301 for good, so repeat visits would skip the visit record,
	 * and a link to an item that later turns private or is deleted would keep pointing at it.
	 */
	public static function resolve(): void {
		$code = (string) get_query_var( 'bpas_code' );
		if ( '' === $code ) {
			return;
		}
		$link = self::decode( $code );
		$ctx  = $link ? bpas_share_context( $link['type'], $link['id'] ) : null;
		if ( ! $ctx || ! bpas_can_share_externally( $ctx ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
		$sharer = $link['sharer'];

		$hash = Tracker::visitor_hash();
		$key  = 'bpas_v_' . md5( $hash . $code );
		if ( ! get_transient( $key ) ) {
			set_transient( $key, 1, DAY_IN_SECONDS );
			// Stored for the Analytics report only, so it follows the site owner's Analytics switch.
			// Rewards do not read it: they listen to the action below, which always fires.
			if ( Settings::feature( 'analytics' ) ) {
				Events::add( 'visit', $link['type'], $link['id'], 'link', $sharer, $hash );
			}
			if ( $sharer && get_current_user_id() !== $sharer ) {
				do_action( 'bpas_pro_share_visit', $sharer, $link['type'], $link['id'] );
			}
		}

		// The cookie holds the sealed code, never a member ID; it is rewritten only when it changes.
		if ( $sharer && ! is_user_logged_in() && sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ?? '' ) ) !== $code ) {
			self::cookie( $code, time() + 30 * DAY_IN_SECONDS );
		}

		wp_safe_redirect( $ctx->url, 302 );
		exit;
	}

	/**
	 * A visitor who came through a member's link signed up.
	 *
	 * @param int $user_id New user.
	 */
	public static function on_signup( $user_id ): void {
		$link   = self::decode( sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ?? '' ) ) );
		$sharer = $link ? $link['sharer'] : 0;
		if ( $sharer && get_userdata( $sharer ) && $sharer !== (int) $user_id ) {
			do_action( 'bpas_pro_share_signup', $sharer, (int) $user_id );
		}
		self::forget();
	}

	/**
	 * Drop the referral cookie: an existing member logged in, or a signup was credited.
	 */
	public static function forget(): void {
		if ( isset( $_COOKIE[ self::COOKIE ] ) ) {
			self::cookie( '', time() - HOUR_IN_SECONDS );
		}
	}

	/**
	 * Set or clear the referral cookie (HttpOnly, SameSite=Lax, Secure on HTTPS).
	 *
	 * @param string $value   Code ('' to clear).
	 * @param int    $expires Expiry timestamp.
	 */
	private static function cookie( string $value, int $expires ): void {
		if ( headers_sent() ) {
			return;
		}
		setcookie(
			self::COOKIE,
			$value,
			array(
				'expires'  => $expires,
				'path'     => COOKIEPATH,
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	/**
	 * Seal (type, id, sharer) into a 22-character URL-safe code.
	 *
	 * @param string $type   'activity' | 'post'.
	 * @param int    $id     Object ID.
	 * @param int    $sharer Sharer ID.
	 */
	private static function encode( string $type, int $id, int $sharer ): string {
		if ( ! isset( self::TYPES[ $type ] ) || $id < 1 || $id > 0xFFFFFFFF || $sharer < 0 || $sharer > 0xFFFFFFFF || ! function_exists( 'openssl_encrypt' ) ) {
			return '';
		}
		$block = pack( 'CNN', self::TYPES[ $type ], $id, $sharer ) . str_repeat( "\0", 7 );
		$raw   = openssl_encrypt( $block, 'aes-128-ecb', self::key(), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING );
		return false === $raw ? '' : self::base64url( $raw );
	}

	/**
	 * URL-safe base64 without padding.
	 *
	 * @param string $raw Bytes.
	 */
	private static function base64url( string $raw ): string {
		return rtrim( strtr( base64_encode( $raw ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- URL-safe code, not obfuscation.
	}

	/**
	 * Open a code: null for anything forged, tampered or malformed.
	 *
	 * @param string $code Code.
	 * @return array{type:string,id:int,sharer:int}|null
	 */
	private static function decode( string $code ): ?array {
		if ( ! preg_match( '/^[A-Za-z0-9_-]{22}$/', $code ) || ! function_exists( 'openssl_decrypt' ) ) {
			return null;
		}
		$raw   = base64_decode( strtr( $code, '-_', '+/' ) . '==', true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- URL-safe code, not obfuscation.
		// Re-encoding must give the same text: the last character carries 4 spare bits, so several
		// spellings decode to one code; only the canonical one is accepted.
		$block = false !== $raw && 16 === strlen( $raw ) && self::base64url( $raw ) === $code ? openssl_decrypt( $raw, 'aes-128-ecb', self::key(), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING ) : false;
		if ( false === $block || str_repeat( "\0", 7 ) !== substr( $block, 9 ) ) {
			return null;
		}
		$fields = unpack( 'Ctype/Nid/Nsharer', $block );
		$type   = array_search( (int) $fields['type'], self::TYPES, true );
		return false === $type || $fields['id'] < 1 ? null : array(
			'type'   => (string) $type,
			'id'     => (int) $fields['id'],
			'sharer' => (int) $fields['sharer'],
		);
	}

	/**
	 * 128-bit key derived from the site's salts (changing the salts retires every short link).
	 */
	private static function key(): string {
		return substr( hash_hmac( 'sha256', 'bpas-pro-short-link', wp_salt( 'auth' ), true ), 0, 16 );
	}
}
