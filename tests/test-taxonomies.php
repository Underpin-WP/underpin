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
class Test_Taxonomies extends WP_UnitTestCase {
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->taxonomies() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->taxonomies() ) . ' does not have anything registered to it, so it has been skipped.' );
		}
	}

	public function test_post_type_is_set() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->post_type, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->taxonomies();
	}
}
