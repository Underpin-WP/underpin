<?php
/**
 *
 *
 * @since
 * @package
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Template_Tests
 *
 *
 * @since
 * @package
 */
trait Loader_Tests {

	/**
	 * Retrieves the class that houses the template trait for this test case.
	 *
	 * @return mixed
	 */
	abstract protected function get_loader();

	public function test_has_name() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->name, 'Item ' . $key . ' is missing a name.' );
		}
	}

	public function test_has_description() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->description, 'Item ' . $key . ' is missing a description.' );
		}
	}

	public function test_is_valid() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertFalse( is_wp_error( $value ), 'Item ' . $key . ' is invalid. This most likely means the class could not be instantiated.' );
		}
	}

}