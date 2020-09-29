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
class Underpin_Admin_Bar_Menus extends WP_UnitTestCase {
	use Template_Tests;
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->admin_bar_menus() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->admin_bar_menus() ) . ' does not have anything registered to it.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_has_capability() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->capability, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_has_position() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertInternalType( 'int', $value->position, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_position_is_valid() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertGreaterThan( 0, $value->position, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->admin_bar_menus();
	}
}
