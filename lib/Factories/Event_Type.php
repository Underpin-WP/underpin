<?php
/**
 * Event Type Abstraction
 * Handles events related to logging events of a specified type.
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */

namespace Underpin\Factories;


use Exception;
use Underpin\Enums\Logger_Item_Events;
use Underpin\Exceptions\Invalid_Callback;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Interfaces\Can_Convert_To_Array;
use Underpin\Interfaces\Data_Provider;
use Underpin\Loaders\Logger;
use Underpin\Interfaces;
use Underpin\Traits\With_Broadcaster;

/**
 * Class Event_Type
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
class Event_Type implements Interfaces\Event_Type, Can_Convert_To_Array, Interfaces\Data_Provider, Interfaces\Feature_Extension {

	use With_Broadcaster;

	protected array $events = [];


	public function __construct(

		/**
		 * Event type
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public readonly string $type = '',

		/**
		 * The minimum volume to be able to see events of this type.
		 *
		 * @since 1.0.0
		 *
		 * @var int
		 */
		public readonly int    $volume = 2,

		/**
		 * A string used to group different event types together.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public readonly string $group = '',

		/**
		 * A human-readable description of this event type.
		 * This is used in debug logs to make it easier to understand why this exists.
		 *
		 * @var string
		 */
		public readonly string $description = '',

		/**
		 * A human-readable name for this event type.
		 * This is used in debug logs to make it easier to understand what this is.
		 *
		 * @var string
		 */
		public readonly string $name = '',

		/**
		 * PSR3 Syslog Level. Can be emergency, alert, critical, error, warning, notice, info, or debug.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public readonly string $psr_level = '',
	) {
	}

	/**
	 * Placeholder to put actions
	 */
	public function do_actions(): void {
		register_shutdown_function( [ $this, 'log_events' ] );
	}

	/**
	 * Log events to the logger.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 - Added support for multiple logger writers
	 */
	public function log_events() {
		$this->broadcast( Logger_Item_Events::write_events, $this );
	}

	/**
	 * Enqueues an event to be logged in the system.
	 *
	 * @since 1.0.0
	 *
	 * @param Interfaces\Log_Item $item The item to log
	 *
	 * @return Log_Item|null The logged item.
	 */
	public function log( Interfaces\Log_Item $item ): ?Interfaces\Log_Item {
		Logger::mute();
		try {
			$this->events[] = $item->set_type( $this );

			$this->broadcast( Logger_Item_Events::event_logged, $item );
		} catch ( Invalid_Registry_Item|Unknown_Registry_Item $e ) {
		}
		Logger::unmute();

		return $item;
	}

	/**
	 * Logs an error from an exception object.
	 *
	 * @since 1.0.0
	 *
	 * @param Exception       $exception Exception instance to log.
	 * @param string|int|null $ref
	 * @param array           $data      array Data associated with this error message
	 *
	 * @return Log_Item|null The logged item.
	 */
	public function log_exception( Exception $exception, string|int|null $ref = null, array $data = array() ): ?Log_Item {
		return $this->log( new Log_Item( code: $exception->getCode(), message: $exception->getMessage(), ref: $ref, data: $data ) );
	}

	/**
	 * @return string
	 */
	function get_type(): string {
		return $this->type;
	}

	/**
	 * @return int
	 */
	function get_volume(): int {
		return $this->volume;
	}

	/**
	 * @return string
	 */
	function get_group(): string {
		return $this->group;
	}

	/**
	 * @return string
	 */
	function get_psr_level(): string {
		return $this->psr_level;
	}

	public function to_array(): array {
		return $this->events;
	}

	protected function broadcast( Logger_Item_Events $id, ?Data_Provider $provider = null ): static {
		$this->get_broadcaster()->broadcast( $id->name, $provider );

		return $this;
	}

	/**
	 * @param Logger_Item_Events $key
	 * @param Observer           $observer
	 *
	 * @return $this
	 */
	public function attach( Logger_Item_Events $key, Observer $observer ): static {
		$this->get_broadcaster()->attach( $key->name, $observer );

		return $this;
	}

}