<?php
/**
 *
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
 * Class Error
 * Error event type.
 *
 * @since 1.0.0
 *
 * @since
 * @package
 */
class Error extends Event_Type {

	/**
	 * Event type
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $type = 'error';

	/**
	 * Writes this to the log.
	 * Set this to true to cause this event to get written to the log.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $write_to_log = true;

	protected $group = 'flare_wp';

	/**
	 * @inheritDoc
	 */
	public $description = 'Intended to log events when something goes wrong while running this plugin.';

	/**
	 * @inheritDoc
	 */
	public $name = "Error";

	/**
	 * @inheritDoc
	 */
	protected $include_backtrace = true;

	/**
	 * @inheritDoc
	 */
	protected $purge_frequency = 7;

}