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
class Test_Admin_Notices extends WP_UnitTestCase {
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->admin_notices() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->admin_notices() ) . ' does not have anything registered to it, so it has been skipped.' );
		}
	}

	public function test_type_is_valid() {
		$valid_types = [ 'error', 'warning', 'success', 'info' ];

		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertContains( $value->type, $valid_types , get_class( $value ) . ' is not set properly.' );

		}
	}

	public function test_wrapper_classes_is_array() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertInternalType( 'array', $value->wrapper_classes, get_class( $value ) . ' is not set properly.' );
		}
	}

	public function test_id_is_set() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->id, get_class( $value ) . ' is not set properly.' );
		}
	}

	public function test_message_is_set() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->message, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->admin_notices();
	}
}
