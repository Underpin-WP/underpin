<?php
/**
 * Cron Task Abstraction
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */

namespace Underpin\Abstracts;

use Underpin\Traits\Feature_Extension;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cron_Task
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Cron_Task {
	use Feature_Extension;

	/**
	 * How often the cron task should recur. See wp_get_schedules() for accepted values.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $frequency = 'hourly';

	/**
	 * The name of this event.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $event;

	/**
	 * List of registered events.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private static $registered_events = [];

	/**
	 * The cron action that will fire on the scheduled time.
	 *
	 * @since 1.0.0
	 */
	abstract function cron_action();

	/**
	 * Cron_Task constructor.
	 *
	 * @param string $event     The name of this event.
	 * @param string $frequency How often the cron task should recur. See wp_get_schedules() for accepted values.
	 */
	public function __construct( $event, $frequency = 'hourly' ) {
		if ( ! isset( self::$registered_events[ $this->event ] ) ) {
			$this->event     = 'underpin\sessions\\' . $event;
			$this->frequency = $frequency;

				// Adds the job to the registry.
			self::$registered_events[ $this->event ] = $this->frequency;
		} else {
			underpin()->logger()->log(
				'error',
				'cron_event_exists',
				__( 'A cron event was not registered because an event of the same name has already been registered.' ),
				array( 'event' => $event, 'frequency' => $frequency )
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		// Registers this cron job to activate when the plugin is activated.
		add_action( 'init', [ $this, 'activate' ] );

		// Registers the action that fires when the cron job runs
		add_action( $this->event, [ $this, 'cron_action' ] );
	}

	/**
	 * Activates the cron task on plugin activation
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		// If this event is not scheduled, schedule it.
		if ( ! wp_next_scheduled( $this->event ) ) {
			wp_schedule_event( time(), $this->frequency, $this->event );
			underpin()->logger()->log(
				'notice',
				'cron_task_activated',
				'The cron task ' . $this->event . ' has been scheduled'
			);
		}
	}

	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_error( 'batch_task_param_not_set', 'The batch task key ' . $key . ' could not be found.' );
		}
	}

}