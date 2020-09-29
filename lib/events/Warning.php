<?php
/**
 * Logs warnings to the logger.
 *
 * @since
 * @package
 */


namespace Underpin\Events;


use Underpin\Abstracts\Event_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Warning
 * Warning event type.
 *
 * @since 1.0.0
 *
 * @since
 * @package
 */
class Warning extends Event_Type {

	/**
	 * Event type
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $type = 'warning';

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
	 * @var inheritDoc
	 */
	public $description = 'Intended to log events when something seems wrong.';

	/**
	 * @var inheritDoc
	 */
	public $name = "Warning";

	protected $group = 'flare_wp';
}