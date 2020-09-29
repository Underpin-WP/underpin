<?php
/**
 * Event Type Abstraction
 * Handles events related to logging events of a specified type.
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */

namespace Underpin\Abstracts;


use ArrayIterator;
use Exception;
use \Underpin\Factories\Log_Item;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Event_Type
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Event_Type extends ArrayIterator {

	/**
	 * Event type
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $type = '';

	/**
	 * Writes this to the log.
	 * Set this to true to cause this event to get written to the log.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $write_to_log = false;

	/**
	 * Force logged events to include a backtrace.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $include_backtrace = false;

	/**
	 * List of capabilities.
	 * If a user has any of these capabilities, they will be able to see events of this type.
	 * Administrator ALWAYS has access, even if they're not on this list.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $capabilities = [];

	/**
	 * The minimum volume to be able to see events of this type.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected $volume = 2;

	/**
	 * A string used to group different event types together.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $group = '';

	/**
	 * A human-readable description of this event type.
	 * This is used in debug logs to make it easier to understand why this exists.
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 * A human-readable name for this event type.
	 * This is used in debug logs to make it easier to understand what this is.
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * The class to instantiate when writing to the error log.
	 *
	 * @since 1.0.0
	 *
	 * @var string Namespaced instance of writer class.
	 */
	public $writer_class = 'Underpin\Factories\Basic_Logger';

	/**
	 * The class to instantiate when logging a new item.
	 *
	 * @since 1.0.0
	 *
	 * @var string Namespaced instance of log item class.
	 */
	public $log_item_class = 'Underpin\Factories\Log_Item';

	/**
	 * Determines how often this event type should be purged.
	 *
	 * @since 1.0.0
	 *
	 * @var int The number of days an event type will be kept before it is purged.
	 */
	protected $purge_frequency = 30;

	/**
	 * Event_Type constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		/**
		 * Filters the writer that is used when logging events.
		 *
		 * @since 1.0.0
		 * @param string $writer_class   The writer class to instantiate when a writer is created.
		 * @param string $type           The current event type.
		 * @param string $log_item_class The writer class to instantiate when a writer is created.
		 */
		$this->writer_class = apply_filters( 'underpin/event_type/writer_class', $this->writer_class, $this->type, $this->log_item_class );

		/**
		 * Filters the log item that is used when logging events.
		 *
		 * @since 1.0.0
		 * @param string $log_item_class The writer class to instantiate when a writer is created.
		 * @param string $type           The current event type.
		 * @param string $writer_class   The writer class to instantiate when a writer is created.
		 */
		$this->log_item_class = apply_filters( 'underpin/event_type/log_item_class', $this->log_item_class, $this->type, $this->writer_class );

		/**
		 * Filters the capabilities for event types.
		 *
		 * @since 1.0.0
		 * @param array $capabilities The list of capabilities to set for this event type.
		 * @param type  $type         The current event type
		 * @param type  $group        The current event group
		 */
		$this->capabilities   = apply_filters( 'underpin/event_type/capabilities', $this->capabilities, $this->type );
		$this->capabilities[] = 'administrator';


		/**
		 * Filters the group for event types.
		 *
		 * @since 1.0.0
		 * @param array $capabilities The list of capabilities to set for this event type.
		 * @param type  $type         The current event type
		 * @param type  $group        The current event group
		 */
		$this->group = apply_filters( 'underpin/event_type/group', $this->group, $this->type );

		/**
		 * Filters the volume for event types.
		 *
		 * @since 1.0.0
		 * @param array $capabilities The list of capabilities to set for this event type.
		 * @param type  $type         The current event type
		 * @param type  $group        The current event group
		 */
		$this->volume = apply_filters( 'underpin/event_type/volume', $this->volume, $this->type );
	}

	/**
	 * Placeholder to put actions
	 */
	public function do_actions() {
		add_action( 'shutdown', array( $this, 'log_events' ) );
	}

	/**
	 * Log events to the logger.
	 *
	 * @since 1.0.0
	 */
	public function log_events() {
		$writer = $this->writer();

		if ( ! is_wp_error( $writer ) ) {
			$writer->write_events();
			reset( $this );
		}

		do_action( 'logger/after_log_events', $this->writer_class, $this->type );
	}

	/**
	 * Fetch the log writer instance, if this class supports logging.
	 *
	 * @since 1.0.0
	 *
	 * @return Writer|WP_Error The logger instance if this can write to log. WP_Error otherwise.
	 */
	public function writer() {
		if ( true !== $this->write_to_log ) {
			return new WP_Error(
				'event_type_does_not_write',
				'The specified event type does not write to the logger. To change this, set the write_to_log param to true.',
				[ 'logger' => $this->type, 'write_to_log_value' => $this->write_to_log ]
			);
		} elseif ( true === underpin()->logger()->is_muted() ) {
			return new WP_Error(
				'events_are_muted',
				'The specified event type was not logged because events are muted.',
				[ 'logger' => $this->type ]
			);
		}

		if ( ! is_subclass_of( $this->writer_class, 'Underpin\Abstracts\Writer' ) ) {
			return new WP_Error(
				'writer_class_invalid',
				'The writer class must be extend the Writer class.',
				[ 'writer_class' => $this->writer_class ]
			);
		}

		return new $this->writer_class( $this );
	}


	/**
	 * Enqueues an event to be logged in the system.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code    The event code to use.
	 * @param string $message The message to log.
	 * @param array  $data    Arbitrary data associated with this event message.
	 * @return Log_Item|WP_Error The logged item, or a WP_Error if something went wrong.
	 */
	public function log( $code, $message, $data = array() ) {

		if ( true === $this->include_backtrace ) {
			$data['backtrace'] = wp_debug_backtrace_summary( null, 3, false );
		}

		$item = new $this->log_item_class( $this->type, $code, $message, $data );

		if ( ! $item instanceof Log_Item ) {
			return new WP_Error(
				'log_item_class_invalid',
				'The log item class must be extend the Log_Item class.',
				[ 'log_item_class' => $this->log_item_class ]
			);
		}


		$this[] = $item;

		do_action( 'underpin/logger/after_logged_item', $item, $this->writer() );

		return $item;
	}

	/**
	 * Logs an error using a WP Error object.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Error $wp_error Instance of WP_Error to use for log
	 * @param array    $data     Additional data to log
	 * @return Log_Item The logged item.
	 */
	public function log_wp_error( WP_Error $wp_error, $data = [] ) {
		if ( ! empty( $data ) ) {
			$current_data = $wp_error->get_error_data();
			$data         = array_merge( (array) $current_data, $data );
			$wp_error->add_data( $data );
		}

		return $this->log( $wp_error->get_error_code(), $wp_error->get_error_message(), $wp_error->get_error_data() );
	}

	/**
	 * Logs an error using a WP Error object.
	 *
	 * @since 1.0.0
	 *
	 * @param Exception $exception Exception instance to log.
	 * @param array     $data      array Data associated with this error message
	 * @return Log_Item The logged item.
	 */
	public function log_exception( Exception $exception, $data = array() ) {
		return $this->log( $exception->getCode(), $exception->getMessage(), $data );
	}

	/**
	 * Getter method.
	 *
	 * @param $key
	 * @return mixed|WP_Error
	 */
	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_error( 'logger_param_not_set', 'The logger param ' . $key . ' could not be found.' );
		}
	}

	public function __isset( $key ) {
		$key = $this->$key;
		if ( is_wp_error( $key ) ) {
			return false;
		}

		return true;
	}

}