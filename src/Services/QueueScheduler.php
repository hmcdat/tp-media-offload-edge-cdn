<?php
/**
 * Queue Scheduler Service class.
 *
 * @package CFR2OffLoad
 */

namespace ThachPN165\CFR2OffLoad\Services;

defined( 'ABSPATH' ) || exit;

/**
 * QueueScheduler class - consistent queue scheduling with fallback support.
 */
class QueueScheduler {

	/**
	 * Queue hook name.
	 */
	private const QUEUE_HOOK = 'cfr2_process_queue';

	/**
	 * Schedule queue processing when not already scheduled.
	 *
	 * @param int $delay Delay in seconds.
	 */
	public static function schedule( int $delay = 0 ): void {
		if ( self::is_scheduled() ) {
			return;
		}

		$timestamp = time() + max( 0, $delay );

		if ( function_exists( 'as_schedule_single_action' ) ) {
			as_schedule_single_action( $timestamp, self::QUEUE_HOOK );
			return;
		}

		wp_schedule_single_event( $timestamp, self::QUEUE_HOOK );
	}

	/**
	 * Check whether queue processing is already scheduled.
	 *
	 * @return bool
	 */
	public static function is_scheduled(): bool {
		if ( function_exists( 'as_next_scheduled_action' ) && as_next_scheduled_action( self::QUEUE_HOOK ) ) {
			return true;
		}

		return (bool) wp_next_scheduled( self::QUEUE_HOOK );
	}
}
