<?php
/**
 *
 *
 * @since
 * @package
 */


namespace Underpin\Abstracts\Registries;


use Exception;
use Underpin\Abstracts\Event_Type;
use Underpin\Factories\Log_Item;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Logger
 *
 *
 * @since
 * @package
 */
abstract class Event_Registry extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Event_Type';

	/**
	 * Determines if the logger should log events, or not. Can be changed with mute, and unmute.
	 *
	 * @var bool
	 */
	protected $is_muted = false;

	/**
	 * @inheritDoc
	 */
	public function add( $key, $value ) {
		$valid = parent::add( $key, $value );

		// If valid, set up actions.
		if ( true === $valid ) {
			$this->get( $key )->do_actions();
		}

		return $valid;
	}

	/**
	 * Retrieves all events that have happened for this request.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $type The event type to retrieve. If false, this will get all events.
	 * @return array|WP_Error list of all events, or a WP_Error if something went wrong.
	 */
	public function get_request_events( $type = false ) {
		if ( false !== $type ) {
			$events = $this->get( $type );

			// Return the error.
			if ( is_wp_error( $events ) ) {
				return $events;
			} else {
				return (array) $events;
			}

		} else {
			$result = [];
			foreach ( $this as $type => $events ) {
				$result[ $type ] = (array) $this->get( $type );
			}

			return $result;
		}
	}

	/**
	 * Enqueues an event to be logged in the system
	 *
	 * @since 1.0.0
	 *
	 * @param string $type    Event log type
	 * @param string $code    The event code to use.
	 * @param string $message The message to log.
	 * @param array  $data    Arbitrary data associated with this event message.
	 * @return Log_Item|WP_Error Log item, with error message. WP_Error if something went wrong.
	 */
	public function log( $type, $code, $message, $data = array() ) {
		$event_type = $this->get( $type );

		if ( is_wp_error( $event_type ) ) {
			return $event_type;
		}

		return $event_type->log( $code, $message, $data );
	}

	/**
	 * Mutes the logger.
	 *
	 * @since 1.0.0
	 */
	private function mute() {
		$this->is_muted = true;
	}

	/**
	 * Un-Mutes the logger.
	 *
	 * @since 1.0.0
	 */
	private function unmute() {
		$this->is_muted = false;
	}

	/**
	 * Does a flareWP action, muting all events that would otherwise happen.
	 *
	 * @since 1.2.4
	 *
	 * @param callable $action The muted action to call.
	 * @return mixed|WP_Error The action returned result, or WP_Error if something went wrong.
	 */
	public function do_muted_action( $action ) {
		if ( is_callable( $action ) ) {
			$this->mute();
			$result = $action();
			$this->unmute();
		} else {
			$result = new WP_Error(
				'muted_action_not_callable',
				'The provided muted action is not callable',
				[ 'ref' => $action, 'type' => 'function' ]
			);
		}

		return $result;
	}

	/**
	 * Fetches the mute status of the logger.
	 *
	 * @since 1.0.0
	 */
	public function is_muted() {
		return $this->is_muted;
	}

	/**
	 * Enqueues an event to be logged in the system, and then returns a WP_Error object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type    Event log type
	 * @param string $code    The event code to use.
	 * @param string $message The message to log.
	 * @param array  $data    Arbitrary data associated with this event message.
	 * @return WP_Error Log item, with error message. WP_Error if something went wrong.
	 */
	public function log_as_error( $type, $code, $message, $data = array() ) {
		$item = $this->log( $type, $code, $message, $data );

		if ( ! is_wp_error( $item ) ) {
			$item = $item->error();
		}

		return $item;
	}

	/**
	 * Logs an error using a WP Error object.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $type     Error log type
	 * @param WP_Error $wp_error Instance of WP_Error to use for log
	 * @param array    $data     Additional data to log
	 * @return Log_Item|WP_Error The logged item, if successful, otherwise WP_Error.
	 */
	public function log_wp_error( $type, WP_Error $wp_error, $data = [] ) {
		$item = $this->get( $type );

		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$error = $item->log_wp_error( $wp_error, $data );

		return $error;
	}

	/**
	 * Logs an error from within an exception.
	 *
	 * @since 1.0.0
	 *
	 * @param string    $type      Error log type
	 * @param Exception $exception Exception instance to log.
	 * @param array     $data      array Data associated with this error message
	 * @return Log_Item|WP_Error Log Item, with error message if successful, otherwise WP_Error.
	 */
	public function log_exception( $type, Exception $exception, $data = array() ) {
		$item = $this->get( $type );

		if ( is_wp_error( $type ) ) {
			return $item;
		}

		$error = $item->log_exception( $exception, $data );

		return $error;
	}

	/**
	 * @param string $key
	 * @return Event_Type|WP_Error Event type, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		$result = parent::get( $key );

		// If the logged event could not be found, search event type keys.
		if ( is_wp_error( $result ) ) {
			$event_types = $this->filter( [
				'type' => $key,
			] );

			// If that also comes up empty, return the error.
			if ( empty( $event_types ) ) {
				return $result;
			}

			// Return the discovered event.
			$result = $event_types[0];
		}

		return $result;
	}

	/**
	 * Purge old logged events.
	 *
	 * @since 1.0.0
	 *
	 */
	public function cleanup() {
		foreach ( $this as $key => $class ) {
			$writer = $this->get( $key )->writer();

			if ( ! is_wp_error( $writer ) ) {
				$purged = $writer->cleanup();

				if ( is_wp_error( $purged ) ) {
					$this->log_wp_error( 'error', $purged );
				}
			}
		}
	}

}