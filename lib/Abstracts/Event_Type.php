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
use Underpin\Factories\Registry;
use Underpin\Traits\With_Subject;
use Underpin\Factories\Log_Item;
use Underpin\Loaders\Logger;
use WP_Error;

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
	use With_Subject;

	/**
	 * Event type
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $type = '';

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
	 * PSR3 Syslog Level. Can be emergency, alert, critical, error, warning, notice, info, or debug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $psr_level = '';

	/**
	 * The class to instantiate when writing to the error log.
	 *
	 * @since 1.0.0
	 *
	 * @var string Namespaced instance of writer class.
	 */
	protected $writers;

	/**
	 * The class to instantiate when logging a new item.
	 *
	 * @since 1.0.0
	 *
	 * @var string Namespaced instance of log item class.
	 */
	protected $log_item_class = 'Underpin\Factories\Log_Item';

	/**
	 * Placeholder to put actions
	 */
	public function do_actions() {
		register_shutdown_function( [ $this, 'log_events' ] );
	}

	/**
	 * Log events to the logger.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 - Added support for multiple logger writers
	 */
	public function log_events() {
		$this->write_events( $this );
		reset( $this );
	}

	/**
	 * Write the events in this event type to each specified writer.
	 *
	 * @since 2.0.0
	 */
	protected function write_events( Event_Type $event_type ) {
		$this->notify( 'log:write', [ 'event_type' => $event_type ] ) ;
	}


	/**
	 * Enqueues an event to be logged in the system.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code    The event code to use.
	 * @param string $message The message to log.
	 * @param array  $data    Arbitrary data associated with this event message.
	 *
	 * @return Log_Item|WP_Error The logged item, or a WP_Error if something went wrong.
	 */
	public function log( $code, $message, $data = array() ) {
		if ( Logger::is_muted() ) {
			return new WP_Error(
				'logger_muted',
				'The logger is currently muted.',
				[ 'log_item_class' => $this->log_item_class ]
			);
		}
		return Logger::do_muted_action( function () use ( $code, $message, $data ) {

			/**
			 * Makes it possible to add additional data to logged events.
			 *
			 * @since 2.0.0
			 *
			 * @param array      $data     list of data to add
			 * @param string     $code     event code
			 * @param string     $message  event message
			 * @param Event_Type $instance The current event instance
			 *
			 */
			$this->notify( 'log:init', [ 'code' => $code, 'message' => $message, 'data' => $data ] ) ;

			$item = new $this->log_item_class( $this, $code, $message, $data );

			if ( ! $item instanceof Log_Item ) {
				return new WP_Error(
					'log_item_class_invalid',
					'The log item class must be extend the Log_Item class.',
					[ 'log_item_class' => $this->log_item_class ]
				);
			}


			$this[] = $item;

			$this->notify( 'log:item_logged', [ 'item' => $item ] ) ;

			return $item;
		} );
	}

	/**
	 * Logs an error using a WP Error object.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Error $wp_error Instance of WP_Error to use for log
	 * @param array    $data     Additional data to log
	 *
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
	 *
	 * @return Log_Item The logged item.
	 */
	public function log_exception( Exception $exception, $data = array() ) {
		return $this->log( $exception->getCode(), $exception->getMessage(), $data );
	}

	/**
	 * Getter method.
	 *
	 * @param $key
	 *
	 * @return mixed|WP_Error
	 */
	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_Error( 'logger_param_not_set', 'The logger param ' . $key . ' could not be found.' );
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