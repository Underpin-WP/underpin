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
class Decision_Lists extends WP_UnitTestCase {
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->decision_lists() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->decision_lists() ) . ' does not have anything registered to it, so it has been skipped.' );
		}
	}

	public function test_decisions_have_names() {
		foreach ( $this->get_loader() as $key => $value ) {
			foreach ( $value as $decision_key => $decision ) {
				$this->assertNotEmpty( $value->name, 'Decision ' . $decision->name . ' Item ' . $key . ' is missing a name.' );
			}
		}
	}

	public function test_decisions_have_descriptions() {
		foreach ( $this->get_loader() as $key => $value ) {
			foreach ( $value as $decision_key => $decision ) {
				$this->assertNotEmpty( $value->name, 'Decision ' . $decision->name . ' Item ' . $key . ' is missing a description.' );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->decision_lists();
	}
}
