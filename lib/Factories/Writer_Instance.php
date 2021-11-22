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


use Underpin\Traits\Instance_Setter;
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
class Writer_Instance extends Writer {

	use Instance_Setter;

	protected $write_callback;
	protected $clear_callback;
	protected $purge_callback;

	public function __construct( $args ) {
		$this->set_values( $args );
		parent::__construct( $args['event_type'] );
	}

	public function write( Log_Item $item ) {
		return $this->set_callable( $this->write_callback, $item );
	}

	public function clear() {
		return $this->set_callable( $this->clear_callback );
	}

	public function purge( $max_file_age ) {
		return $this->set_callable( $this->purge_callback, $max_file_age );
	}

}