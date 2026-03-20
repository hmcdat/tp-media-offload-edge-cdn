<?php
/**
 * Plugin Settings Service class.
 *
 * @package CFR2OffLoad
 */

namespace ThachPN165\CFR2OffLoad\Services;

defined( 'ABSPATH' ) || exit;

use ThachPN165\CFR2OffLoad\Constants\BatchConfig;
use ThachPN165\CFR2OffLoad\Constants\Settings;

/**
 * PluginSettings class - canonical settings schema and normalization.
 */
class PluginSettings {

	/**
	 * Get canonical settings defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'r2_account_id'        => '',
			'r2_access_key_id'     => '',
			'r2_secret_access_key' => '',
			'r2_bucket'            => '',
			'r2_public_domain'     => '',
			'auto_offload'         => 0,
			'batch_size'           => BatchConfig::DEFAULT_SIZE,
			'keep_local_files'     => 1,
			'sync_delete'          => 0,
			'cdn_enabled'          => 0,
			'cdn_url'              => '',
			'quality'              => 85,
			'image_format'         => 'webp',
			'smart_sizes'          => 0,
			'content_max_width'    => 800,
			'cf_api_token'         => '',
			'worker_deployed'      => false,
			'worker_name'          => '',
			'worker_deployed_at'   => '',
		);
	}

	/**
	 * Create settings option if missing.
	 *
	 * @return array<string, mixed>
	 */
	public static function initialize(): array {
		$defaults = self::defaults();

		if ( false === get_option( Settings::OPTION_KEY, false ) ) {
			add_option( Settings::OPTION_KEY, $defaults );
		}

		return $defaults;
	}

	/**
	 * Get normalized settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function get(): array {
		return self::normalize( self::get_raw() );
	}

	/**
	 * Normalize and persist saved settings when needed.
	 *
	 * @return array<string, mixed>
	 */
	public static function migrate_if_needed(): array {
		if ( false === get_option( Settings::OPTION_KEY, false ) ) {
			return self::initialize();
		}

		$raw        = self::get_raw();
		$normalized = self::normalize( $raw );

		if ( $normalized !== $raw ) {
			update_option( Settings::OPTION_KEY, $normalized );
		}

		return $normalized;
	}

	/**
	 * Get raw option value as array.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_raw(): array {
		$settings = get_option( Settings::OPTION_KEY, array() );

		return is_array( $settings ) ? $settings : array();
	}

	/**
	 * Normalize settings against canonical defaults and remove stale keys.
	 *
	 * @param array<string, mixed> $settings Raw settings.
	 * @return array<string, mixed>
	 */
	public static function normalize( array $settings ): array {
		$defaults = self::defaults();
		$legacy   = self::apply_legacy_aliases( $settings );
		$merged   = wp_parse_args( $legacy, $defaults );

		return array(
			'r2_account_id'        => sanitize_text_field( (string) $merged['r2_account_id'] ),
			'r2_access_key_id'     => sanitize_text_field( (string) $merged['r2_access_key_id'] ),
			'r2_secret_access_key' => (string) $merged['r2_secret_access_key'],
			'r2_bucket'            => sanitize_text_field( strtolower( (string) $merged['r2_bucket'] ) ),
			'r2_public_domain'     => self::normalize_url( (string) $merged['r2_public_domain'] ),
			'auto_offload'         => ! empty( $merged['auto_offload'] ) ? 1 : 0,
			'batch_size'           => max( BatchConfig::MIN_SIZE, min( absint( $merged['batch_size'] ), BatchConfig::MAX_SIZE ) ),
			'keep_local_files'     => ! empty( $merged['keep_local_files'] ) ? 1 : 0,
			'sync_delete'          => ! empty( $merged['sync_delete'] ) ? 1 : 0,
			'cdn_enabled'          => ! empty( $merged['cdn_enabled'] ) ? 1 : 0,
			'cdn_url'              => self::normalize_url( (string) $merged['cdn_url'] ),
			'quality'              => max( 1, min( absint( $merged['quality'] ), 100 ) ),
			'image_format'         => self::normalize_image_format( (string) $merged['image_format'] ),
			'smart_sizes'          => ! empty( $merged['smart_sizes'] ) ? 1 : 0,
			'content_max_width'    => max( 320, min( absint( $merged['content_max_width'] ), 1920 ) ),
			'cf_api_token'         => (string) $merged['cf_api_token'],
			'worker_deployed'      => ! empty( $merged['worker_deployed'] ),
			'worker_name'          => sanitize_text_field( (string) $merged['worker_name'] ),
			'worker_deployed_at'   => sanitize_text_field( (string) $merged['worker_deployed_at'] ),
		);
	}

	/**
	 * Apply safe legacy aliases before canonical normalization.
	 *
	 * @param array<string, mixed> $settings Raw settings.
	 * @return array<string, mixed>
	 */
	private static function apply_legacy_aliases( array $settings ): array {
		if ( empty( $settings['cf_api_token'] ) && ! empty( $settings['api_key'] ) ) {
			$settings['cf_api_token'] = $settings['api_key'];
		}

		return $settings;
	}

	/**
	 * Normalize URL-like settings.
	 *
	 * @param string $url Raw URL.
	 * @return string
	 */
	private static function normalize_url( string $url ): string {
		return rtrim( esc_url_raw( $url ), '/' );
	}

	/**
	 * Normalize image format into supported set.
	 *
	 * @param string $format Raw image format.
	 * @return string
	 */
	private static function normalize_image_format( string $format ): string {
		$allowed_formats = array( 'original', 'webp', 'avif' );
		$format          = sanitize_text_field( $format );

		return in_array( $format, $allowed_formats, true ) ? $format : 'webp';
	}
}
