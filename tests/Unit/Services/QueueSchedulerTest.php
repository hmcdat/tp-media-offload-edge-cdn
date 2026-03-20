<?php
/**
 * QueueScheduler Unit Tests.
 *
 * @package CFR2OffLoad
 */

namespace ThachPN165\CFR2OffLoad\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use ThachPN165\CFR2OffLoad\Services\QueueScheduler;

/**
 * QueueSchedulerTest class.
 */
class QueueSchedulerTest extends TestCase {

	/**
	 * Reset mocked state before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		cfr2_test_reset_wp_state();
	}

	/**
	 * Test schedule falls back to WP-Cron when Action Scheduler is unavailable.
	 */
	public function test_schedule_uses_wp_cron_fallback(): void {
		global $_test_scheduled_events;

		QueueScheduler::schedule( 15 );

		$this->assertCount( 1, $_test_scheduled_events );
		$this->assertSame( 'cfr2_process_queue', $_test_scheduled_events[0]['hook'] );
		$this->assertFalse( $_test_scheduled_events[0]['recurring'] );
		$this->assertGreaterThanOrEqual( time() + 14, $_test_scheduled_events[0]['timestamp'] );
	}

	/**
	 * Test schedule does not add duplicate pending events.
	 */
	public function test_schedule_skips_duplicate_events(): void {
		global $_test_scheduled_events;

		QueueScheduler::schedule();
		QueueScheduler::schedule();

		$this->assertCount( 1, $_test_scheduled_events );
	}
}
