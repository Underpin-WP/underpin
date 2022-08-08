<?php

namespace Underpin\Loaders;

use Exception;
use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Enums\Logger_Events;
use Underpin\Exceptions\Invalid_Callback;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Data_Providers\Int_Provider;
use Underpin\Factories\Event_Type;
use Underpin\Factories\Log_Item;
use Underpin\Interfaces as Interfaces;
use Underpin\Interfaces\Singleton;
use Underpin\Traits\With_Broadcaster;
use UnitEnum;


/**
 * Class Logger
 * Houses methods to manage event logging
 *
 * @since   1.0.0
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

	/**
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	public function __construct() {
		self::mute();
		$this->set_default_items();
		self::unmute();
	}


	/**
	 * @return self
	 */
	public static function instance(): static {
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
	 * Sets the volume for logged events.
	 * If an event requires the volume is higher than this set value, it will not be logged.
	 *
	 * @param int $volume
	 *
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
		return ! self::instance()->is_muted() && $event_type->get_volume() < self::instance()->volume;
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
		try {
			$event_type = self::instance()->get( $type );
		} catch ( Unknown_Registry_Item ) {
			return null;
		}

		if ( ! self::instance()->can_log( $event_type ) ) {
			return null;
		}

		return $event_type->log( $log_item );
	}

	protected static function auto_log(string $type, Log_Item|Exception $item): ?Log_Item {
		if($item instanceof Log_Item){
			return self::log($type, $item);
		} else{
			return self::log_exception($type, $item);
		}
	}

	/**
	 * Logs an item of the specified type.
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function debug( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log('debug', $item);
	}

	/**
	 * Logs an item of the specified type.
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function info( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log('info', $item);
	}

	/**
	 * Logs an item of the specified type.
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function notice( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log('notice', $item);
	}

	/**
	 * Logs an item of the specified type.
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function warning( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log('warning', $item);
	}

	/**
	 * Logs an item of the specified type.
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function error( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log('error', $item);
	}

	/**
	 * Logs an item of the specified type.
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function critical( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log('critical', $item);
	}

	/**
	 * Logs an item of the specified type.
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function alert( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log('alert', $item);
	}

	/**
	 * Logs an item of the specified type.
	 * @param Log_Item|Exception $item A log item to log, or an exception.
	 *
	 * @return Log_Item|null
	 */
	public static function emergency( Log_Item|Exception $item ): ?Log_Item {
		return self::auto_log('emergency', $item);
	}

	/**
	 * Mutes the logger.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 */
	public static function unmute(): Logger {
		$instance           = self::instance();
		$instance->is_muted = false;
		$instance->broadcast( Logger_Events::unmuted );

		return $instance;
	}

	/**
	 * Fetches the mute status of the logger.
	 *
	 * @since 1.0.0
	 */
	public static function is_muted(): bool {
		return self::instance()->is_muted;
	}

	/**
	 * Logs an error from within an exception.
	 *
	 * @since 1.0.0
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
		} catch ( Invalid_Callback|Invalid_Registry_Item|Unknown_Registry_Item $e ) {
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
				'volume'      => 80,
			] ) )
			->add( 'alert', array_merge( $defaults, [
				'type'        => 'alert',
				'description' => 'Intended to be used when someone should be notified about this problem.',
				'name'        => "Alert",
				'psr_level'   => 'alert',
				'volume'      => 70,
			] ) )
			->add( 'critical', array_merge( $defaults, [
				'type'        => 'critical',
				'description' => 'Intended to log events when an error occurs that is potentially damaging.',
				'name'        => "Critical Error",
				'psr_level'   => 'critical',
				'volume'      => 60,
			] ) )
			->add( 'error', array_merge( $defaults, [
				'type'        => 'error',
				'description' => 'Intended to log events when something goes wrong.',
				'name'        => "Error",
				'psr_level'   => 'error',
				'volume'      => 50,
			] ) )
			->add( 'warning', array_merge( [
				'always_enabled' => false,
				'type'           => 'warning',
				'description'    => 'Intended to log events when something seems wrong.',
				'name'           => 'Warning',
				'psr_level'      => 'warning',
				'volume'         => 40,
			] ) )
			->add( 'notice', array_merge( [
				'always_enabled' => false,
				'type'           => 'notice',
				'description'    => 'Posts informative notices when something is neither good nor bad.',
				'name'           => 'Notice',
				'psr_level'      => 'notice',
				'volume'         => 30,
			] ) )
			->add( 'info', array_merge( [
				'always_enabled' => false,
				'type'           => 'info',
				'description'    => 'Posts informative messages that something is most-likely going as-expected.',
				'name'           => 'Info',
				'psr_level'      => 'info',
				'volume'         => 20,
			] ) )
			->add( 'debug', array_merge( [
				'always_enabled' => false,
				'type'           => 'debug',
				'description'    => 'A place to put information that is only useful in debugging context.',
				'name'           => 'Debug',
				'psr_level'      => 'debug',
				'volume'         => 10,
			] ) );

		$this->broadcast( Logger_Events::ready );
	}

	/**
	 * @param UnitEnum $key The enum case to use as the key.
	 * @param Interfaces\Observer $observer
	 *
	 * @see Logger_Events
	 *
	 * @return $this
	 */
	public function attach( UnitEnum $key, Interfaces\Observer $observer ): static {
		$this->get_broadcaster()->attach( $key, $observer );

		return $this;
	}

	/**
	 * @param UnitEnum $key The enum case to use as the key.
	 * @param string $observer_id The instance to detach.
	 *
	 * @see Logger_Events
	 *
	 * @return $this
	 */
	function detach( UnitEnum $key, string $observer_id ): static {
		$this->get_broadcaster()->detach( $key, $observer_id );

		return $this;
	}
}