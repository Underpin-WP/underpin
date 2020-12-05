<?php
/**
 * Core functionality for Underpin
 *
 * @since
 * @package
 */


namespace Underpin;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Underpin
 *
 *
 * @since
 * @package
 */
class Underpin extends Abstracts\Underpin {

	protected $minimum_php_version = '5.6';
	protected $minimum_wp_version = '5.0';
	protected $version = '1.0.0';
	protected $root_namespace = 'Underpin';


	protected function _setup() {
		$this->cron_jobs();
		$this->admin_bar_menus();
		$this->scripts();
		$this->styles();
		$this->options();
		$this->logger();
		$this->decision_lists();
	}
}
