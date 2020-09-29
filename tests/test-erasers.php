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
class Erasers extends WP_UnitTestCase {
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->erasers() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->erasers() ) . ' does not have anything registered to it, so it has been skipped.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_has_number_of_items() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertInternalType( 'int', $value->per_page, get_class( $value ) . ' per page should be an int.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_has_id() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertInternalType( 'string', $value->id, get_class( $value ) . ' id should be a string.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->erasers();
	}
}
