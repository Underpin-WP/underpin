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
class Underpin_Batch_Tasks extends WP_UnitTestCase {
	use Template_Tests;
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->batch_tasks() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->batch_tasks() ) . ' does not have anything registered to it.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_number_of_tasks_is_int() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertInternalType( 'int', $value->tasks_per_request, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_number_of_tasks_is_positive() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertGreaterThan( 0 , $value->tasks_per_request , get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_total_items_is_int() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertInternalType( 'int', $value->total_items, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_stop_on_error_is_boolean() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertInternalType( 'bool', $value->stop_on_error, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_notice_message_is_set() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->notice_message, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_button_text_is_set() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->button_text, get_class( $value ) . ' is not set properly.' );
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
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->batch_tasks();
	}
}
