<?php
/**
 * Batch_Tasks
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Registries\Loader_Registry;
use Underpin\Abstracts\Batch_Task;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Batch_Tasks
 * Registry to run Batch Tasks
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */
class Batch_Tasks extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Batch_Task';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
		// $this->add();
	}

	/**
	 * @param string $key
	 * @return Batch_Task|WP_Error Script Resulting block class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

	/**
	 * Attempts to enqueue the specified batch action to run.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key used to register this batch item.
	 * @return true|WP_Error True if enqueued, WP_Error if something went wrong.
	 */
	public function enqueue( $key ) {
		$batch_task = $this->get( $key );

		if ( is_wp_error( $batch_task ) ) {
			underpin()->logger()->log_wp_error( 'error', $batch_task );

			return $batch_task;
		}

		$batch_task->enqueue();

		return true;
	}
}