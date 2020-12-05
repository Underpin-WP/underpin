<?php
/**
 * Batch Task Abstraction
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts;


use Underpin\Traits\Underpin_Templates;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Batch_Task
 * Handles batch tasks
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Batch_Task {
	use Underpin_Templates;

	/**
	 * The number of tasks that should run in this request
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected $tasks_per_request = 20;

	/**
	 * Set to true to stop this batch processor if task() returns a WP_Error.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $stop_on_error = true;

	/**
	 * Determines the total number of times the task should run.
	 *
	 * @since 1.0.0
	 *
	 * @var int Total number of times the task runs.
	 */
	protected $total_items = 0;

	/**
	 * The message displayed in the admin notice.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $notice_message = '';

	/**
	 * The text for the button.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $button_text = 'Run';

	/**
	 * The required capability to run this task.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $capability = 'administrator';

	/**
	 * Unique batch ID.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $batch_id = '';

	/**
	 * The human-readable description of this batch task.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $description = "";

	/**
	 * The human-readable name of this batch task.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name = "";

	/**
	 * The actual upgrade task. Intended to run on a single item.
	 *
	 * @since 1.0.0
	 *
	 * @param int $current_tally The current number of times this task has ran.
	 * @param int $iteration     The current loop iteration
	 * @return true|WP_Error True if the task was successful, otherwise \WP_Error. Errors get logged by run().
	 */
	abstract protected function task( $current_tally, $iteration );

	/**
	 * This action runs when this batch processor has completely finished.
	 * It is intended to be extended, but not required.
	 *
	 * @since 1.1.0
	 */
	protected function finish_process( $current_tally ) { }

	/**
	 * This action runs just before the current request has started.
	 * It is intended to be extended, but not required.
	 *
	 * @since 1.1.0
	 */
	protected function prepare_task( $current_tally ) { }

	/**
	 * This action runs just before the current request has finished.
	 * It is intended to be extended, but not required.
	 *
	 * @since 1.1.0
	 */
	protected function finish_task( $current_tally ) { }

	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_error( 'batch_task_param_not_set', 'The batch task key ' . $key . ' could not be found.' );
		}
	}

	/**
	 * Runs the actual batch task for this request.
	 *
	 * @param int $current_tally The current number of times this task has ran.
	 * @return int|WP_Error The current tally if successful, WP_Error if the system was stopped early.
	 */
	public function run( $current_tally ) {

		$valid = $this->is_valid();

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$this->prepare_task( $current_tally );

		for ( $i = 0; $i < $this->tasks_per_request; $i++ ) {

			if ( $this->is_done( $current_tally ) ) {

				$this->finish_process( $current_tally );

				underpin()->logger()->log(
					'notice',
					'batch_action_complete',
					'The batch action called ' . $this->name . ' is complete.',
					[ 'ref' => $this->batch_id, 'tally' => $current_tally ]
				);

				return true;
			}

			$status = $this->task( $current_tally, $i );

			if ( is_wp_error( $status ) ) {
				underpin()->logger()->log_wp_error( 'batch_error', $status );

				// Bail early if we're supposed to stop when an error occurs.
				if ( true === $this->stop_on_error ) {

					underpin()->logger()->log(
						'warning',
						'batch_action_stopped_early',
						'The batch action called ' . $this->name . ' stopped early because of an error.',
						[ 'ref' => $current_tally, 'batch_id' => $this->batch_id, 'error' => $status ]
					);

					return $status;
				}
			}

			$current_tally++;
		}

		$this->finish_task( $current_tally );

		return $current_tally;
	}

	/**
	 * Returns true when the run is considered done.
	 *
	 * @sicne 1.0.0
	 *
	 * @param int $current_tally The current tally for this task.
	 * @return bool true if this is done, false otherwise.
	 */
	protected function is_done( $current_tally ) {
		return $current_tally >= $this->total_items;
	}

	/**
	 * Returns true if the batch request is valid.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True if valid, WP_Error otherwise.
	 */
	protected function is_valid() {

		if ( ! current_user_can( $this->capability ) ) {
			return underpin()->logger()->log_as_error(
				'batch_error',
				'batch_task_invalid_user_permissions',
				'The specified user does not have the permission to run this task'
			);
		}

		return true;
	}

	/**
	 * The ajax action that runs when this batch task fires.
	 *
	 * @since 1.0.0
	 */
	public function ajax_action() {
		return wp_send_json( $this->run( (int) $_POST['currentTally'] ) );
	}

	/**
	 * Enqueues this task to run.
	 *
	 * @since 1.0.0
	 */
	public function enqueue() {
		add_action( 'admin_notices', [ $this, 'render_callback' ] );
		add_action( 'wp_ajax_' . $this->batch_id, [ $this, 'ajax_action' ] );
	}

	/**
	 * Renders the batch task notice, and enqueues scripts.
	 *
	 * @since 1.0.0
	 */
	public function render_callback() {

		if ( is_admin() && current_user_can( $this->capability ) ) {

			$batch_params = [ 'total_items' => $this->total_items ];

			underpin()->scripts()->set_param( 'batch', $this->batch_id, $batch_params );
			underpin()->scripts()->enqueue( 'batch' );
			underpin()->styles()->enqueue( 'batch' );
			echo $this->get_template( 'notice', [
				'batch_id'    => $this->batch_id,
				'message'     => $this->notice_message,
				'button_text' => $this->button_text,
			] );

			underpin()->logger()->log(
				'notice',
				'batch_task_enqueued',
				'A batch task was enqueued.',
				[ 'ref' => $this->batch_id ]
				);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_templates() {
		return [
			'notice' => [
				'override_visibility' => 'private',
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function get_template_group() {
		return 'batch';
	}
}