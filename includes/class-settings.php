<?php
/**
 * Pro settings store (plan 12.1: Features + Content types, nothing else).
 *
 * @package BPAS_Pro
 */

namespace BPAS_Pro;

defined( 'ABSPATH' ) || exit;

/**
 * Reads and writes `bpas_pro_settings`.
 */
final class Settings {

	const OPTION = 'bpas_pro_settings';

	/**
	 * Feature keys => default.
	 */
	public static function feature_defaults(): array {
		return array(
			'repost'      => true,
			'send_friend' => true,
			'reply_share' => true,
			'counts'      => true,
			'image_card'  => true,
			'short_links' => true,
			'analytics'   => false,
		);
	}

	/**
	 * Defaults.
	 */
	public static function defaults(): array {
		return (array) apply_filters(
			'bpas_pro_default_settings',
			array(
				'features'      => self::feature_defaults(),
				'content_types' => array( 'post' => 'below' ),
			)
		);
	}

	/**
	 * All settings merged with defaults.
	 */
	public static function all(): array {
		$saved    = get_option( self::OPTION, array() );
		$saved    = is_array( $saved ) ? $saved : array();
		$defaults = self::defaults();
		return array(
			'features'      => array_merge( $defaults['features'], (array) ( $saved['features'] ?? array() ) ),
			'content_types' => isset( $saved['content_types'] ) ? (array) $saved['content_types'] : $defaults['content_types'],
		);
	}

	/**
	 * Is a feature on? Filterable per feature.
	 *
	 * @param string $key Feature key.
	 */
	public static function feature( string $key ): bool {
		$on = ! empty( self::all()['features'][ $key ] );
		return (bool) apply_filters( "bpas_pro_feature_{$key}_enabled", $on );
	}

	/**
	 * Post type => placement ('above' | 'below' | 'floating').
	 */
	public static function content_types(): array {
		return self::all()['content_types'];
	}

	/**
	 * Merge, sanitise and save.
	 *
	 * @param array $changes Partial settings.
	 */
	public static function update( array $changes ): array {
		$all = self::all();

		if ( isset( $changes['features'] ) && is_array( $changes['features'] ) ) {
			foreach ( array_keys( self::feature_defaults() ) as $key ) {
				if ( array_key_exists( $key, $changes['features'] ) ) {
					$all['features'][ $key ] = (bool) $changes['features'][ $key ];
				}
			}
		}

		if ( isset( $changes['content_types'] ) && is_array( $changes['content_types'] ) ) {
			$types = array();
			foreach ( $changes['content_types'] as $type => $placement ) {
				$type = sanitize_key( (string) $type );
				if ( post_type_exists( $type ) && is_post_type_viewable( $type ) && in_array( $placement, array( 'above', 'below', 'floating' ), true ) ) {
					$types[ $type ] = $placement;
				}
			}
			$all['content_types'] = $types;
		}

		update_option( self::OPTION, $all, false );
		do_action( 'bpas_pro_settings_updated', $all );
		return $all;
	}
}
