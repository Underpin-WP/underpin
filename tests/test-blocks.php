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
class Underpin_Blocks extends WP_UnitTestCase {
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->blocks() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->blocks() ) . ' does not have anything registered to it, so it has been skipped.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_has_type() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertInternalType( 'string', $value->type, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_has_script() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertInstanceOf( 'Underpin\Abstracts\Script', underpin()->scripts()->get( $value->script ), get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_has_style() {
		foreach ( $this->get_loader() as $key => $value ) {
			if ( false !== $value->style ) {
				$this->assertInstanceOf( 'Underpin\Abstracts\Style', underpin()->styles()->get( $value->style ), get_class( $value ) . ' is not set properly.' );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->blocks();
	}
}
