<?php
/**
 * Logs events to a file.
 *
 * @since 1.0.0
 *
 * @since
 * @package
 */


namespace Underpin\Factories;


use Underpin\Abstracts\Event_Type;
use Underpin\Abstracts\Writer;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Basic Logger
 *
 *
 * @since
 * @package
 */
class Basic_Logger extends Writer {

	/**
	 * @inheritDoc
	 */
	public function write( Log_Item $item ) {
		error_log( $item->format() );
	}

	/**
	 * @inheritDoc
	 */
	public function clear() {
		// The basic writer simply uses PHP's error logger, so this isn't really do-able
	}

	/**
	 * @inheritDoc
	 */
	public function purge( $max_file_age ) {
		// The basic writer simply uses PHP's error logger, so this isn't really do-able
	}
}