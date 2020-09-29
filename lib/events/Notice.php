<?php
/**
 * Logs notices to the logger.
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
class Notice extends Event_Type {

	/**
	 * Event type
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $type = 'notice';

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
	public $description = 'Posts informative notices that do not necessarily mean anything is wrong.';

	/**
	 * @var inheritDoc
	 */
	public $name = "Notice";

	protected $group = 'flare_wp';
}