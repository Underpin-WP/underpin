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
class Test_Shortcodes extends WP_UnitTestCase {
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->shortcodes() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->scripts() ) . ' does not have anything registered to it, so it has been skipped.' );
		}
	}

	public function test_defaults_are_set() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->defaults, get_class( $value ) . ' is not set properly.' );

		}
	}

	public function test_shortcode_is_set() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertInternalType( 'string', $value->shortcode, get_class( $value ) . ' is not set properly. It should either be "false" or a string representation of a version.' );

		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->shortcodes();
	}
}
