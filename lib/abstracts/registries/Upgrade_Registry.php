<?php
/**
 * Loader to handle plugin version updates.
 *
 * @since   1.0.0
 * @package Underpin\Loaders
 */


namespace Underpin\Abstracts\Registries;


use Underpin\Abstracts\Batch_Task;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Upgrades
 *
 *
 * @since   1.0.0
 * @package Underpin\Loaders
 */
abstract class Upgrade_Registry extends Loader_Registry {

	public $abstraction_class = 'Underpin\Abstracts\Batch_Task';

	/**
	 * Enqueues the next upgrade in the list.
	 * This will display the next batch task only.
	 */
	public function enqueue_next_upgrade() {
		// Sort this array by version
		$this->ksort();

		foreach ( $this as $version => $upgrade ) {
			if ( version_compare( $this->get_stored_version(), $version, '<' ) ) {
				underpin()->batch_tasks()->get( $this->get_batch_task_key( $version ) )->enqueue();

				return;
			}
		}
	}

	/**
	 * Determines if this plugin needs an update.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|int
	 */
	public function needs_update() {
		return version_compare( $this->get_current_version(), $this->get_stored_version(), '<' );
	}

	/**
	 * Get the current plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	abstract public function get_current_version();

	/**
	 * Get the stored plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	abstract public function get_stored_version();

	/**
	 * @inheritDoc
	 */
	public function add( $version, $batch_task ) {
		$valid = $this->validate_item( $version, $batch_task );

		if ( ! is_wp_error( $valid ) ) {
			$added = underpin()->batch_tasks()->add( $this->get_batch_task_key( $version ), $batch_task );

			if ( ! is_wp_error( $added ) ) {
				$this[ $version ] = $this->get_batch_task_key( $version );
			}
		} else {
			return $valid;
		}

		return $added;
	}

	/**
	 * Retrieves the batch task key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $version The current version to retrieve the key.
	 * @return string
	 */
	protected function get_batch_task_key( $version ) {
		return 'version_' . $version . '_update';
	}

	/**
	 * @param string $key
	 * @return Batch_Task
	 */
	public function get( $key ) {
		return parent::get( $key );
	}
}