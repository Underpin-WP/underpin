<?php
/**
 * Class SampleTest
 *
 * @package Dfs_Monitor
 */

use function Underpin\underpin;

require_once underpin()->dir() . 'tests/phpunit/Loader_Tests.php';

/**
 * Sample test case.
 */
class Test_Widgets extends WP_UnitTestCase {
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->widgets() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->widgets() ) . ' does not have anything registered to it, so it has been skipped.' );
		}
	}

	public function test_fields_are_is_set() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertContainsOnlyInstancesOf( 'Underpin\Abstracts\Settings_Field', $value->get_fields( $value->get_settings() ), get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->widgets();
	}
}
