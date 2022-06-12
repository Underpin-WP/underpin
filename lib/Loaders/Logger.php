<?php
/**
 *
 *
 * @since
 * @package
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Exception;
use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Exceptions\Invalid_Callback;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Event_Type;
use Underpin\Factories\Log_Item;
use Underpin\Factories\Data_Providers\Accumulator;
use Underpin\Interfaces as Interfaces;
use Underpin\Interfaces\Singleton;
use Underpin\Traits\With_Subject;


/**
 * Class Logger
 * Houses methods to manage event logging
 *
 * @since   1.0.0
 * @package Underpin\Loaders
 */
final class Logger extends Object_Registry implements Singleton {

	use With_Subject;

	/**
	 * @inheritDoc
	 */
	protected string $abstraction_class = Interfaces\Event_Type::class;

	protected string $default_factory = Event_Type::class;

	protected bool $debug_mode_enabled;

	/**
	 * Determines if the logger should log events, or not. Can be changed with mute, and unmute.
	 *
	 * @var bool
	 */
	protected static bool $is_muted = false;

	/**
	 * @var self
	 */
	private static self $instance;

	/**
	 * @inheritDoc
	 */
	public function add( $key, $value ): static {
		$valid = parent::add( $key, $value );

		// If valid, set up actions.
		if ( true === $valid ) {
			$this->get( $key )->do_actions();
		}

		return $this;
	}

	/**
	 * Determines if debug mode is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if debug mode is enabled, otherwise false.
	 */
	public static function is_debug_mode_enabled(): bool {
		if ( ! isset( self::instance()->debug_mode_enabled ) ) {
			try {
				return self::instance()->apply_filters( 'logger:debug_mode_enabled', new Accumulator(
					default       : false,
					valid_callback: fn ( $state ) => is_bool( $state ),
				) );
			} catch ( Exception ) {
				// If something goes wrong, just assume debug mode is disabled.
				self::instance()->debug_mode_enabled = false;
				return false;
			}
		}

		return self::instance()->debug_mode_enabled;
	}

	/**
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	public function __construct() {
		self::mute();
		$this->notify( 'init' );
		$this->set_default_items();
		self::unmute();
	}


	/**
	 * @return self
	 */
	public static function instance(): Singleton {
		if ( ! isset( self::$instance ) || ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Retrieves all events that have happened for this request.
	 *
	 * @since 1.0.0
	 *
	 * @param ?string $type The event type to retrieve. If null, this will get all events.
	 *
	 * @return array list of all events.
	 */
	public static function get_request_events( ?string $type = null ): array {
		if ( ! $type ) {
			try {
				return self::instance()->get( $type )->to_array();
			} catch ( Unknown_Registry_Item $e ) {
				return [];
			}
		} else {
			$result = [];
			foreach ( self::instance() as $type => $events ) {
				try {
					$result[ $type ] = self::instance()->get( $type )->to_array();
				} catch ( Unknown_Registry_Item $e ) {
					$result[ $type ] = '<invalid instance>';
				}
			}

			return $result;
		}
	}

	/**
	 * Enqueues an event to be logged in the system
	 *
	 * @since 1.0.0
	 *
	 * @param string   $type     Event log type
	 * @param Log_Item $log_item Item to log
	 *
	 * @return ?Log_Item Log item, with error message. Null if muted, or type is invalid.
	 */
	public static function log( string $type, Log_Item $log_item ): ?Log_Item {
		if ( self::is_muted() ) {
			return null;
		}

		try {
			$event_type = self::instance()->get( $type );
		} catch ( Unknown_Registry_Item ) {
			return null;
		}

		try {
			$item = $event_type->is_enabled() ? $event_type->log( $log_item ) : null;
		} catch ( Invalid_Callback|Invalid_Registry_Item $e ) {
			return null;
		}

		try {
			self::instance()->notify( 'event:logged', [ 'event' => $item ] );
		} catch ( Invalid_Registry_Item|Unknown_Registry_Item ) {
			return $item;
		}

		return $item;
	}

	/**
	 * Mutes the logger.
	 *
	 * @since 1.0.0
	 */
	private static function mute() {
		self::$is_muted = true;
	}

	/**
	 * Un-Mutes the logger.
	 *
	 * @since 1.0.0
	 */
	private static function unmute() {
		self::$is_muted = false;
	}

	/**
	 * Does an action, muting all events that would otherwise happen.
	 *
	 * @since 1.2.4
	 *
	 * @param callable $action The muted action to call.
	 *
	 * @return mixed The action returned result.
	 * @throws Invalid_Callback
	 */
	public static function do_muted_action( $action ) {
		if ( is_callable( $action ) ) {
			self::mute();
			$result = $action();
			self::unmute();
		} else {
			throw new Invalid_Callback( $action );
		}

		return $result;
	}

	/**
	 * Fetches the mute status of the logger.
	 *
	 * @since 1.0.0
	 */
	public static function is_muted() {
		return self::$is_muted;
	}

	/**
	 * Logs an error from within an exception.
	 *
	 * @since 1.0.0
	 *
	 * @param string    $type      Error log type
	 * @param Exception $exception Exception instance to log.
	 * @param array     $data      array Data associated with this error message
	 *
	 * @return ?Log_Item Log Item, with error message if successful, otherwise null.
	 */
	public static function log_exception( string $type, Exception $exception, array $data = array() ): ?Log_Item {
		// If the logger is muted, don't log.
		if ( Logger::is_muted() ) {
			return null;
		}

		try {
			$item  = self::instance()->get( $type );
			$error = $item->is_enabled() ? $item->log_exception( $exception, $data ) : null;
		} catch ( Invalid_Callback|Invalid_Registry_Item $e ) {
			// Fail silently if the instance is invalid.
			return null;
		}

		return $error;
	}

	/**
	 * @param string $key
	 *
	 * @return Interfaces\Event_Type
	 * @throws Unknown_Registry_Item
	 */
	public function get( string $key ): Event_Type {
		try {
			return parent::get( $key );
			// If the logged event could not be found, search event type keys.
		} catch ( Unknown_Registry_Item $exception ) {
			/* @var Event_Type $result */
			$result = $this->query()->equals( 'type', $key )->find();

			if ( ! $result ) {
				throw $exception;
			}

			return $result;
		}
	}

	/**
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	protected function set_default_items(): void {
		$defaults = [
			'group' => 'core',
		];

		$this
			->add( 'emergency', array_merge( $defaults, [
				'type'        => 'emergency',
				'description' => 'Intended to be used only for the most-severe events.',
				'name'        => "Emergency",
				'psr_level'   => 'emergency',
			] ) )
			->add( 'alert', array_merge( $defaults, [
				'type'        => 'alert',
				'description' => 'Intended to be used when someone should be notified about this problem.',
				'name'        => "Alert",
				'psr_level'   => 'alert',
			] ) )
			->add( 'critical', array_merge( $defaults, [
				'type'        => 'critical',
				'description' => 'Intended to log events when an error occurs that is potentially damaging.',
				'name'        => "Critical Error",
				'psr_level'   => 'critical',
			] ) )
			->add( 'error', array_merge( $defaults, [
				'type'        => 'error',
				'description' => 'Intended to log events when something goes wrong.',
				'name'        => "Error",
				'psr_level'   => 'error',
			] ) )
			->add( 'warning', array_merge( [
				'always_enabled' => false,
				'type'           => 'warning',
				'description'    => 'Intended to log events when something seems wrong.',
				'name'           => 'Warning',
				'psr_level'      => 'warning',
			] ) )
			->add( 'notice', array_merge( [
				'always_enabled' => false,
				'type'           => 'notice',
				'description'    => 'Posts informative notices when something is neither good nor bad.',
				'name'           => 'Notice',
				'psr_level'      => 'notice',
			] ) )
			->add( 'info', array_merge( [
				'always_enabled' => false,
				'type'           => 'info',
				'description'    => 'Posts informative messages that something is most-likely going as-expected.',
				'name'           => 'Info',
				'psr_level'      => 'info',
			] ) )
			->add( 'debug', array_merge( [
				'always_enabled' => false,
				'type'           => 'debug',
				'description'    => 'A place to put information that is only useful in debugging context.',
				'name'           => 'Debug',
				'psr_level'      => 'debug',
			] ) );
	}

}