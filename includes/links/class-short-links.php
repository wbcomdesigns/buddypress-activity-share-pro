<?php
/**
 * Short share links /s/{code}-{sig} (X8). Replaces tracking query params; no member IDs in plain sight.
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro\Links;

use BPAS_Pro\Settings;
use BPAS_Pro\Tracking\Events;
use BPAS_Pro\Tracking\Tracker;

defined( 'ABSPATH' ) || exit;

/**
 * Code = type letter + base36(object id) + 'u' + base36(sharer id); sig = HMAC prefix (no forging / probing).
 */
final class Short_Links {

	const COOKIE = 'bpas_ref';

	/**
	 * Hook registration.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'rewrite' ), 20 );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'resolve' ), 1 );
		add_filter( 'bpas_share_url', array( __CLASS__, 'share_url' ), 10, 3 );
		add_action( 'user_register', array( __CLASS__, 'on_signup' ) );
	}

	/**
	 * URL prefix: /s/, or /share/ when a page already uses /s/.
	 */
	public static function prefix(): string {
		$prefix = get_page_by_path( 's' ) ? 'share' : 's';
		return (string) apply_filters( 'bpas_pro_short_link_prefix', $prefix );
	}

	/**
	 * Rewrite rule (+ one flush after upgrades or toggles).
	 */
	public static function rewrite(): void {
		add_rewrite_rule( '^' . preg_quote( self::prefix(), '/' ) . '/([a-z0-9]+)-([a-f0-9]{8})/?$', 'index.php?bpas_code=$matches[1]&bpas_sig=$matches[2]', 'top' );
		if ( get_option( 'bpas_pro_flush_rewrites' ) ) {
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
		$vars[] = 'bpas_sig';
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
		if ( ! in_array( $ctx->type, array( 'activity', 'post' ), true ) ) {
			return (string) $url;
		}
		return self::for_object( $ctx->type, (int) $ctx->id, get_current_user_id() );
	}

	/**
	 * Build a short link.
	 *
	 * @param string $type    'activity' | 'post'.
	 * @param int    $id      Object ID.
	 * @param int    $sharer  Sharing member (0 = visitor).
	 */
	public static function for_object( string $type, int $id, int $sharer ): string {
		$code = ( 'post' === $type ? 'p' : 'a' ) . base_convert( (string) $id, 10, 36 ) . 'u' . base_convert( (string) $sharer, 10, 36 );
		return home_url( '/' . self::prefix() . '/' . $code . '-' . self::sign( $code ) . '/' );
	}

	/**
	 * Visit: verify, record, credit, redirect.
	 */
	public static function resolve(): void {
		$code = (string) get_query_var( 'bpas_code' );
		$sig  = (string) get_query_var( 'bpas_sig' );
		if ( '' === $code ) {
			return;
		}
		if ( ! hash_equals( self::sign( $code ), $sig ) || ! preg_match( '/^([ap])([a-z0-9]+)u([a-z0-9]+)$/', $code, $m ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		$type   = 'p' === $m[1] ? 'post' : 'activity';
		$id     = (int) base_convert( $m[2], 36, 10 );
		$sharer = (int) base_convert( $m[3], 36, 10 );
		$ctx    = bpas_share_context( $type, $id );
		if ( ! $ctx || ! bpas_can_share_externally( $ctx ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		$hash = Tracker::visitor_hash();
		$key  = 'bpas_v_' . md5( $hash . $code );
		if ( ! get_transient( $key ) ) {
			set_transient( $key, 1, DAY_IN_SECONDS );
			if ( Settings::feature( 'analytics' ) ) {
				Events::add( 'visit', $type, $id, 'link', $sharer, $hash );
			}
			if ( $sharer && get_current_user_id() !== $sharer ) {
				do_action( 'bpas_pro_share_visit', $sharer, $type, $id );
			}
		}

		if ( $sharer && ! is_user_logged_in() ) {
			setcookie( self::COOKIE, (string) $sharer, time() + 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
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
		$sharer = isset( $_COOKIE[ self::COOKIE ] ) ? absint( $_COOKIE[ self::COOKIE ] ) : 0;
		if ( $sharer && get_userdata( $sharer ) && $sharer !== (int) $user_id ) {
			do_action( 'bpas_pro_share_signup', $sharer, (int) $user_id );
			setcookie( self::COOKIE, '', time() - HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		}
	}

	/**
	 * Signature.
	 *
	 * @param string $code Code.
	 */
	private static function sign( string $code ): string {
		return substr( hash_hmac( 'sha256', $code, wp_salt( 'nonce' ) ), 0, 8 );
	}
}
