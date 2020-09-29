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
class Test_Logger extends WP_UnitTestCase {
	use Loader_Tests;

	public static function wpSetUpBeforeClass() {
		if ( empty( (array) underpin()->logger() ) ) {
			self::markTestSkipped( 'The loader ' . get_class( underpin()->logger() ) . ' does not have anything registered to it, so it has been skipped.' );
		}
	}

	public function test_writer_is_instance_of_writer_class() {
		foreach ( $this->get_loader() as $logger ) {
			$this->assertInstanceOf( 'Underpin\Abstracts\Writer', new $logger->writer_class( $logger ), 'The writer for logger ' . get_class( $logger ) . ' Is not a valid instance of the Writer class.' );
		}
	}

	public function test_logger_has_frequency() {
		foreach ( $this->get_loader() as $logger ) {
			$this->assertTrue( is_numeric( $logger->purge_frequency ), 'The purge frequency for logger ' . get_class( $logger ) . ' does not have a valid purge frequency.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_loader() {
		return underpin()->logger();
	}
}
