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
class Custom_Post_Types extends WP_UnitTestCase {
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->custom_post_types() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->custom_post_types() ) . ' does not have anything registered to it, so it has been skipped.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_has_type() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->type, get_class( $value ) . ' field is invalid.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->custom_post_types();
	}
}
