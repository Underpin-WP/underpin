<?php

namespace Underpin\Interfaces;

use Exception;

interface Event_Type {

	/**
	 * Event type
	 *
	 *
	 * @var string
	 */
	function get_type(): string;

	/**
	 * The minimum volume to be able to see events of this type.
	 *
	 *
	 * @var int
	 */
	function get_volume(): int;

	/**
	 * A string used to group different event types together.
	 *
	 *
	 * @var string
	 */
	function get_group(): string;

	/**
	 * PSR3 Syslog Level. Can be emergency, alert, critical, error, warning, notice, info, or debug.
	 *
	 *
	 * @var string
	 */
	function get_psr_level(): string;

	/**
	 * Enqueues an event to be logged in the system.
	 *
	 *
	 * @param Log_Item $item
	 *
	 * @return ?Log_Item Log item, with error message. Null if muted, or type is invalid.
	 */
	function log( Log_Item $item ): ?Log_Item;

	/**
	 * Logs an error from an exception object.
	 *
	 *
	 * @param Exception       $exception Exception instance to log.
	 * @param string|int|null $ref
	 * @param array           $data      array Data associated with this error message
	 *
	 * @return Log_Item|null The logged item.
	 */
	function log_exception( Exception $exception, string|int|null $ref, array $data = array() ): ?Log_Item;
}