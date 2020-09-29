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
trait Template_Tests {

	/**
	 * Retrieves the class that houses the template trait for this test case.
	 *
	 * @return mixed
	 */
	abstract protected function get_loader();

	/**
	 * Ensures all of the provided template files exist
	 *
	 * Covers Underpin/Traits/Templates::get_templates
	 */
	public function test_default_template_file_exists() {
		foreach ( $this->get_loader() as $key => $value ) {
			foreach ( $value->get_templates() as $template => $args ) {
				$this->assertTrue( $value->template_file_exists( $template ), 'Item ' . $key . "'s " . $template . ' template file could not be found.' );
			}
		}
	}

}