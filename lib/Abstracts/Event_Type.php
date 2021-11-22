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
use Underpin\Abstracts\Underpin;
use Underpin\Factories\Registry;
use Underpin\Traits\Middleware;
use Underpin\Abstracts\Registries\Capability_Registry;
use Underpin\Abstracts\Registries\Writer_Registry;
use Underpin\Factories\Log_Item;
use Underpin\Loaders\Logger;
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

	use Middleware;

	/**
	 * Event type
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * List of capabilities.
	 * If a user has any of these capabilities, they will be able to see events of this type.
	 * Administrator ALWAYS has access, even if they're not on this list.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $capabilities;

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
		 * Makes it possible to modify the middleware for all logged events.
		 *
		 * @since 2.0.0
		 *
		 * @param array      $middlewares list of middlewares to add
		 * @param Event_Type $event_type  The event type instance
		 *
		 */
		$this->middlewares = apply_filters( 'underpin\event_logs\middlewares', $this->middlewares, $this );

		$this->writers = new Registry( [
			'registry_id'       => 'Underpin_' . $this->type,
			'skip_logging'      => true,
			'validate_callback' => function ( $key, $value ) {
				$abstraction_class = '\Underpin\Abstracts\Writer';
				if ( $value === $abstraction_class || is_subclass_of( $value, $abstraction_class ) || $value instanceof $abstraction_class ) {
					return true;
				}

				if ( is_array( $value ) ) {
					if ( isset( $value['write_callback'] ) && isset( $value['clear_callback'] ) && isset( $value['purge_callback'] ) ) {
						return true;
					}
				}

				return false;
			},
		] );

		$this->capabilities = new Registry( [
			'registry_id'       => 'Underpin_capabilities_' . $this->type,
			'skip_logging'      => true,
			'default_items'     => [ 'administrator' ],
			'validate_callback' => function ( $key, $value ) {
				return is_string( $value );
			},
		] );
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
	 * @since 2.0.0 - Added support for multiple logger writers
	 */
	public function log_events() {
		$this->write_events( $this );
		reset( $this );
	}


	/**
	 * Constructs a writer instance from the provided key and event type.
	 *
	 * @since 2.0.0
	 *
	 * @param            $key
	 * @param Event_Type $event_type
	 *
	 * @return Writer|WP_Error
	 */
	public function make_writer( $key, Event_Type $event_type ) {
		$item = $this->writers->get( $key );

		// If something went wrong, return the WP Error
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		// if this event was made using the array method, construct it with make class.
		if ( is_array( $item ) ) {
			$logger['event_type'] = $event_type;
			return Underpin::make_class( $logger, '\Underpin\Factories\Event_Type_Instance' );
		}

		// Return the event
		return new $item( $event_type );
	}

	/**
	 * Write the events in this event type to each specified writer.
	 *
	 * @since 2.0.0
	 */
	protected function write_events( Event_Type $event_type ) {
		foreach ( (array) $this->writers as $key => $logger ) {
			$writer = $this->make_writer( $key, $event_type );
			if ( ! is_wp_error( $logger ) ) {
				$writer->write_events();
			}
		}
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
		$additional_logged_data = apply_filters( 'underpin/logger/additional_logged_data', [], $code, $message, $this );

		// Add additional data, but original data should take priority.
		$data = array_merge( (array) $additional_logged_data, $data );
		$item = new $this->log_item_class( $this, $code, $message, $data,  );

		if ( ! $item instanceof Log_Item ) {
			return new WP_Error(
				'log_item_class_invalid',
				'The log item class must be extend the Log_Item class.',
				[ 'log_item_class' => $this->log_item_class ]
			);
		}


		$this[] = $item;

		do_action( 'underpin/logger/after_logged_item', $item, $this );

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