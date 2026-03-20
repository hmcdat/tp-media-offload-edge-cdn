<?php
/**
 * PluginSettings Unit Tests.
 *
 * @package CFR2OffLoad
 */

namespace ThachPN165\CFR2OffLoad\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use ThachPN165\CFR2OffLoad\Constants\BatchConfig;
use ThachPN165\CFR2OffLoad\Constants\Settings;
use ThachPN165\CFR2OffLoad\Services\PluginSettings;

/**
 * PluginSettingsTest class.
 */
class PluginSettingsTest extends TestCase {

	/**
	 * Reset mocked state before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		cfr2_test_reset_wp_state();
	}

	/**
	 * Test initialize creates canonical defaults when the option is missing.
	 */
	public function test_initialize_creates_canonical_defaults_when_missing(): void {
		$defaults = PluginSettings::initialize();

		$this->assertSame( PluginSettings::defaults(), $defaults );
		$this->assertSame( PluginSettings::defaults(), get_option( Settings::OPTION_KEY ) );
	}

	/**
	 * Test migration normalizes legacy settings and persists the canonical schema.
	 */
	public function test_migrate_if_needed_normalizes_legacy_settings(): void {
		update_option(
			Settings::OPTION_KEY,
			array(
				'r2_bucket'         => 'My-Bucket',
				'batch_size'        => 999,
				'keep_local_files'  => '',
				'cdn_url'           => 'https://cdn.example.com/',
				'image_format'      => 'bmp',
				'content_max_width' => 9999,
				'worker_deployed'   => '1',
				'api_key'           => 'legacy-token',
			)
		);

		$normalized = PluginSettings::migrate_if_needed();
		$stored     = get_option( Settings::OPTION_KEY );

		$this->assertSame( 'my-bucket', $normalized['r2_bucket'] );
		$this->assertSame( BatchConfig::MAX_SIZE, $normalized['batch_size'] );
		$this->assertSame( 0, $normalized['keep_local_files'] );
		$this->assertSame( 'https://cdn.example.com', $normalized['cdn_url'] );
		$this->assertSame( 'webp', $normalized['image_format'] );
		$this->assertSame( 1920, $normalized['content_max_width'] );
		$this->assertTrue( $normalized['worker_deployed'] );
		$this->assertSame( 'legacy-token', $normalized['cf_api_token'] );
		$this->assertArrayNotHasKey( 'api_key', $stored );
		$this->assertSame( $normalized, $stored );
	}
}
