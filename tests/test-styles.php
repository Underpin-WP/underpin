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
class Test_Styles extends WP_UnitTestCase {
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->styles() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->styles() ) . ' does not have anything registered to it, so it has been skipped.' );
		}
	}

	public function test_handle_is_set() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->handle, get_class( $value ) . ' is not set properly.' );

		}
	}

	public function test_ver_is_set() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertTrue( gettype( $value->ver ) === 'string' || false === $value->ver, get_class( $value ) . ' is not set properly. It should either be "false" or a string representation of a version.' );

		}
	}

	public function test_src_is_set() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->src, get_class( $value ) . ' is not set properly.' );

		}
	}

	public function test_media_is_set() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->media, get_class( $value ) . ' is not set properly.' );

		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->styles();
	}
}
