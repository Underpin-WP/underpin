<?php
/**
 *
 *
 * @since
 * @package
 */


namespace Underpin\Abstracts;

use Underpin\Factories\Log_Item;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Writer.
 * This is a factory that is instantiated by error loggers.
 * It handles the steps that actually write of error log events
 *
 * @since 1.0.0
 * @package
 */
abstract class Writer {

	/**
	 * @var Event_Type
	 */
	protected $event_type;

	public function __construct( Event_Type $event_type ) {
		$this->event_type = $event_type;
	}

	/**
	 * Writes a single log item.
	 *
	 * @since 1.0.0
	 *
	 * @param $item
	 * @return mixed
	 */
	abstract public function write( Log_Item $item );

	/**
	 * Clears the event log.
	 *
	 * @since 1.0.0
	 * @return true|WP_Error True if the clear was successful, or WP_Error.
	 */
	abstract public function clear();

	/**
	 * Purges logs older than the specified date.
	 *
	 * @since 1.0.0
	 *
	 * @param int $max_file_age The maximum number of days worth of log data to keep.
	 * @return array|WP_Error List of purged files, or WP_Error.
	 */
	abstract public function purge( $max_file_age );

	/**
	 * Cleans up old logs.
	 *
	 * @since 1.0.0
	 */
	public function cleanup() {
		$frequency = underpin()->decision_lists()->decide( 'event_type_purge_frequency', [ 'event_type' => $this->event_type ] );

		return $this->purge( $frequency );
	}

	/**
	 * Writes events.
	 *
	 * @since 1.0.0
	 */
	public function write_events() {
		foreach ( $this->event_type as $event ) {
			$this->write( $event );
		}
	}

	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_error( 'batch_task_param_not_set', 'The batch task key ' . $key . ' could not be found.' );
		}
	}
}