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
class Test_Rest_Endpoints extends WP_UnitTestCase {
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->rest_endpoints() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->rest_endpoints() ) . ' does not have anything registered to it, so it has been skipped.' );
		}
	}

	public function test_rest_namespace_is_set(){
		foreach($this->get_loader() as $key => $value) {
			$this->assertNotEmpty( $value->rest_namespace, get_class( $value ) . ' is not set properly.' );

		}
	}

	public function test_route_is_set(){
		foreach($this->get_loader() as $key => $value) {
			$this->assertNotEmpty( $value->route, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->rest_endpoints();
	}
}
