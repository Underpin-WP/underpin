<?php
/**
 * Class SampleTest
 *
 * @package Dfs_Monitor
 */

use function Underpin\Underpin;

require_once Underpin()->dir() . 'tests/phpunit/Template_Tests.php';
require_once Underpin()->dir() . 'tests/phpunit/Loader_Tests.php';

/**
 * Sample test case.
 */
class Underpin_Admin_Sub_Menus extends WP_UnitTestCase {
	use Template_Tests;
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) Underpin()->admin_sub_menus() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( Underpin()->admin_sub_menus() ) . ' does not have anything registered to it.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_has_parent_menu() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->parent_menu, get_class( $value ) . ' is not set properly.' );
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
	public function test_has_menu_title() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->menu_title, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_has_menu_slug() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotEmpty( $value->menu_slug, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_has_nonce_action() {
		foreach ( $this->get_loader() as $key => $value ) {
			$this->assertNotNull( $value->nonce_action, get_class( $value ) . ' is not set properly.' );
		}
	}

	/**
	 * A single example test.
	 */
	public function test_sections_have_ids() {
		foreach ( $this->get_loader() as $key => $value ) {
			foreach ( $value->sections as $section ) {
				$section = new $section;
				$this->assertNotEmpty( $section->id, get_class( $section ) . ' is not set properly.' );
			}
		}
	}

	public function test_sections_are_instances_of_section_class() {
		foreach ( $this->get_loader() as $key => $value ) {
			foreach ( $value->sections as $section ) {
				$section = new $section;
				$this->assertInstanceOf( 'Underpin\Abstracts\Admin_Section', $section, get_class( $section ) . ' is not set properly.' );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return Underpin()->admin_sub_menus();
	}
}
