<?php

namespace Underpin\Registries;

use Exception;
use ReflectionException;
use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Enums\Logger_Events;
use Underpin\Exceptions\Instance_Not_Ready;
use Underpin\Exceptions\Invalid_Callback;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Data_Providers\Int_Provider;
use Underpin\Factories\Event_Type;
use Underpin\Factories\Log_Item;
use Underpin\Helpers\Array_Helper;
use Underpin\Helpers\Processors\Array_Processor;
use Underpin\Interfaces as Interfaces;
use Underpin\Interfaces\Data_Provider;
use Underpin\Interfaces\Observer;
use Underpin\Interfaces\Singleton;
use Underpin\Traits\With_Broadcaster;
use UnitEnum;


/**
 * Class Logger
 * Houses methods to manage event logging
 *
 * @package Underpin\Loaders
 */
final class Logger extends Object_Registry implements Singleton, Interfaces\Can_Broadcast {

	use With_Broadcaster;

	/**
	 * @inheritDoc
	 */
	protected string $abstraction_class = Interfaces\Event_Type::class;

	protected string $default_factory = Event_Type::class;

	protected int $volume = 50;

	/**
	 * Determines if the logger should log events, or not. Can be changed with mute, and unmute.
	 *
	 * @var bool
	 */
	protected bool $is_muted = false;

	/**
	 * @var self
	 */
	private static self $instance;

	private static bool $setting_up = false;

	/**
	 * @throws Invalid_Registry_Item
	 * @throws Operation_Failed
	 */
	public function __construct() {
		self::$setting_up = true;
		$this->is_muted   = true;
		$this->set_default_items();
		$this->is_muted   = false;
		self::$setting_up = false;
	}


	/**
	 * @return self
	 * @throws Instance_Not_Ready
	 */
	public static function instance(): static {
		// This instance is in the midst of being set up, so return null.
		if ( self::$setting_up ) {
			throw new Instance_Not_Ready( 'The logger is still being set up, and cannot be accessed.', null );
		}

		if ( ! isset( self::$instance ) || ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Sets the volume for logged events.
	 * If an event requires the volume is higher than this set value, it will not be logged.
	 *
	 * @param int $volume
	 *
	 * @throws Instance_Not_Ready
	 * @return Logger
	 */
	public static function set_volume( int $volume ): Logger {
		$instance         = self::instance();
		$instance->volume = $volume;
		$instance->broadcast( Logger_Events::volume_changed, new Int_Provider( $volume ) );

		return $instance;
	}

	/**
	 * Returns true if the provided event type can be logged.
	 *
	 * @param Event_Type $event_type
	 *
	 * @return bool
	 */
	private function can_log( Event_Type $event_type ): bool {
		return ! $this->is_muted() && $this->volume >= $event_type->get_volume();
	}

	/**
	 * Enqueues an event to be logged in the system
	 *
	 *
	 * @param string   $type     Event log type
	 * @param Log_Item $log_item Item to log
	 *
	 * @return ?Log_Item Log item, with error message. Null if muted, or type is invalid.
	 */
	public static function log( string $type, Log_Item $log_item ): ?Log_Item {
		try {
			$event_type = self::instance()->get( $type );

			if ( ! self::instance()->can_log( $event_type ) ) {
				return null;
			}
		} catch ( Unknown_Registry_Item|Instance_Not_Ready ) {
			return null;
		}

		return $event_type->log( $log_item );
	}

	protected static function auto_log( string $type, Log_Item|Exception $item ): ?Log_Item {
		if ( $item instanceof Log_Item ) {
			return self::log( $type, $item );
		} else {
			return self::log_exception( $type, $item );
		}
	}

	/**
	 * Logs an item of the specified type.
	 *
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function debug( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log( 'debug', $item );
	}

	/**
	 * Logs an item of the specified type.
	 *
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function info( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log( 'info', $item );
	}

	/**
	 * Logs an item of the specified type.
	 *
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function notice( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log( 'notice', $item );
	}

	/**
	 * Logs an item of the specified type.
	 *
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function warning( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log( 'warning', $item );
	}

	/**
	 * Logs an item of the specified type.
	 *
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function error( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log( 'error', $item );
	}

	/**
	 * Logs an item of the specified type.
	 *
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function critical( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log( 'critical', $item );
	}

	/**
	 * Logs an item of the specified type.
	 *
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function alert( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log( 'alert', $item );
	}

	/**
	 * Logs an item of the specified type.
	 *
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function emergency( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log( 'emergency', $item );
	}

	/**
	 * Mutes the logger.
	 *
	 * @throws Instance_Not_Ready
	 *
	 */
	public static function mute(): Logger {
		$instance           = self::instance();
		$instance->is_muted = true;
		$instance->broadcast( Logger_Events::muted );

		return $instance;
	}

	/**
	 * Un-Mutes the logger.
	 *
	 * @throws Instance_Not_Ready
	 *
	 */
	public static function unmute(): Logger {
		$instance = self::instance();
		$instance->broadcast( Logger_Events::unmuted );
		$instance->is_muted = false;

		return $instance;
	}

	/**
	 * Fetches the mute status of the logger.
	 *
	 */
	public static function is_muted(): bool {
		try {
			return self::instance()->is_muted;
		} catch ( Instance_Not_Ready ) {
			return true;
		}
	}

	/**
	 * Logs an error from within an exception.
	 *
	 *
	 * @param string          $type      Error log type
	 * @param Exception       $exception Exception instance to log.
	 * @param string|int|null $ref
	 * @param array           $data      array Data associated with this error message
	 *
	 * @return ?Log_Item Log Item, with error message if successful, otherwise null.
	 */
	public static function log_exception( string $type, Exception $exception, string|int|null $ref = null, array $data = array() ): ?Log_Item {
		try {
			$event_type = self::instance()->get( $type );

			if ( ! self::instance()->can_log( $event_type ) ) {
				return null;
			}

			$error = $event_type->log_exception( $exception, $ref, $data );
		} catch ( Unknown_Registry_Item $e ) {
			// Fail silently if the instance is invalid.
			return null;
		}

		return $error;
	}

	/**
	 * Gets all events in the logger
	 *
	 * @return Mutable_Collection
	 * @throws Operation_Failed
	 * @throws ReflectionException
	 */
	public function get_events(): Mutable_Collection {
		return Immutable_Collection::make( Log_Item::class )->seed( $this->reduce( fn ( array $acc, Event_Type $event_type ) => array_merge( $acc, $event_type->to_array() ), [] ) );
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
	 * @throws Operation_Failed
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
				'volume'      => 10,
			] ) )
			->add( 'alert', array_merge( $defaults, [
				'type'        => 'alert',
				'description' => 'Intended to be used when someone should be notified about this problem.',
				'name'        => "Alert",
				'psr_level'   => 'alert',
				'volume'      => 20,
			] ) )
			->add( 'critical', array_merge( $defaults, [
				'type'        => 'critical',
				'description' => 'Intended to log events when an error occurs that is potentially damaging.',
				'name'        => "Critical Error",
				'psr_level'   => 'critical',
				'volume'      => 30,
			] ) )
			->add( 'error', array_merge( $defaults, [
				'type'        => 'error',
				'description' => 'Intended to log events when something goes wrong.',
				'name'        => "Error",
				'psr_level'   => 'error',
				'volume'      => 40,
			] ) )
			->add( 'warning', array_merge( [
				'type'        => 'warning',
				'description' => 'Intended to log events when something seems wrong.',
				'name'        => 'Warning',
				'psr_level'   => 'warning',
				'volume'      => 50,
			] ) )
			->add( 'notice', array_merge( [
				'type'        => 'notice',
				'description' => 'Posts informative notices when something is neither good nor bad.',
				'name'        => 'Notice',
				'psr_level'   => 'notice',
				'volume'      => 60,
			] ) )
			->add( 'info', array_merge( [
				'type'        => 'info',
				'description' => 'Posts informative messages that something is most-likely going as-expected.',
				'name'        => 'Info',
				'psr_level'   => 'info',
				'volume'      => 70,
			] ) )
			->add( 'debug', array_merge( [
				'type'        => 'debug',
				'description' => 'A place to put information that is only useful in debugging context.',
				'name'        => 'Debug',
				'psr_level'   => 'debug',
				'volume'      => 80,
			] ) );

		$this->broadcast( Logger_Events::ready );
	}

	/**
	 * @param string $key The enum case to use as the key.
	 * @param callable $observer
	 *
	 * @return $this
	 * @throws Operation_Failed
	 * @throws Unknown_Registry_Item
	 * @see Logger_Events
	 *
	 */
	public function attach( string $key, callable $observer ): static {
		$this->get_broadcaster()->attach( $key, $observer );

		return $this;
	}

	/**
	 * @param string $key         The enum case to use as the key.
	 * @param string   $observer_id The instance to detach.
	 *
	 * @see Logger_Events
	 *
	 * @return $this
	 * @throws Operation_Failed
	 */
	function detach( string $key, string $observer_id ): static {
		$this->get_broadcaster()->detach( $key, $observer_id );

		return $this;
	}

	protected function broadcast( Logger_Events $key, ?Data_Provider $args = null ): static {
		$this->get_broadcaster()->broadcast( $key->name, $args );

		Logger::debug( new Log_Item(
			code   : 'item_broadcasted',
			message: "An item was broadcasted",
			context: 'instance',
			ref    : get_called_class()
		) );

		return $this;
	}

}