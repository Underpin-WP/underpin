<?php
/**
 * Class SampleTest
 *
 * @package Dfs_Monitor
 */

use function Underpin\underpin;

require_once underpin()->dir() . 'tests/phpunit/Template_Tests.php';
require_once underpin()->dir() . 'tests/phpunit/Loader_Tests.php';

/**
 * Sample test case.
 */
class Cron_Jobs extends WP_UnitTestCase {
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->cron_jobs() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->cron_jobs() ) . ' does not have anything registered to it, so it has been skipped.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_has_valid_frequency() {
		foreach ( $this->get_loader() as $key => $value ) {
			$schedules = array_merge( array_keys( wp_get_schedules() ), [ 'weekly' ] );
			$this->assertContains( $value->frequency, $schedules, get_class( $value ) . ' frequency value is not a valid frequency.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->cron_jobs();
	}
}
