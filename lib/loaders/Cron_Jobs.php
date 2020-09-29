<?php
/**
 * Cron Jobs
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Cron_Task;
use Underpin\Abstracts\Registries\Loader_Registry;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cron_Jobs
 * Registry for Cron Jobs
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */

class Cron_Jobs extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Cron_Task';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
		$this->add( 'purge_logs', 'Underpin\Cron_Jobs\Purge_Logs' );
	}

	/**
	 * @param string $key
	 * @return Cron_Task|WP_Error Script Resulting cron task class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}
}