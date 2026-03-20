<?php
/**
 * Settings Validator Service class.
 *
 * @package CFR2OffLoad
 */

namespace ThachPN165\CFR2OffLoad\Services;

defined( 'ABSPATH' ) || exit;

/**
 * SettingsValidator class - validates required runtime configuration.
 */
class SettingsValidator {

	/**
	 * Validate R2 credentials before offload/restore/delete operations.
	 *
	 * @param array<string, string> $credentials Decrypted credentials array.
	 * @return string|null Error message or null when valid.
	 */
	public static function validate_r2_credentials( array $credentials ): ?string {
		$required_fields = array(
			'account_id'        => __( 'Missing R2 account ID.', 'tp-media-offload-edge-cdn' ),
			'access_key_id'     => __( 'Missing R2 access key ID.', 'tp-media-offload-edge-cdn' ),
			'secret_access_key' => __( 'Missing R2 secret access key.', 'tp-media-offload-edge-cdn' ),
			'bucket'            => __( 'Missing R2 bucket.', 'tp-media-offload-edge-cdn' ),
		);

		foreach ( $required_fields as $field => $message ) {
			if ( empty( $credentials[ $field ] ) ) {
				return $message;
			}
		}

		return null;
	}

	/**
	 * Validate Cloudflare settings for DNS validation or worker management.
	 *
	 * @param array<string, mixed> $settings Plugin settings.
	 * @param bool                 $require_bucket Require an R2 bucket.
	 * @param bool                 $require_cdn_url Require a CDN URL.
	 * @return string|null Error message or null when valid.
	 */
	public static function validate_cloudflare_settings(
		array $settings,
		bool $require_bucket = false,
		bool $require_cdn_url = false
	): ?string {
		if ( empty( $settings['cf_api_token'] ) ) {
			return __( 'Missing Cloudflare API token.', 'tp-media-offload-edge-cdn' );
		}

		if ( empty( $settings['r2_account_id'] ) ) {
			return __( 'Missing Cloudflare account ID.', 'tp-media-offload-edge-cdn' );
		}

		if ( $require_bucket && empty( $settings['r2_bucket'] ) ) {
			return __( 'Missing R2 bucket.', 'tp-media-offload-edge-cdn' );
		}

		if ( $require_cdn_url && empty( $settings['cdn_url'] ) ) {
			return __( 'Missing CDN URL.', 'tp-media-offload-edge-cdn' );
		}

		return null;
	}
}
