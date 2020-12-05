<?php
/**
 * Registers a menu
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts;

use Underpin\Traits\Feature_Extension;
use Underpin\Traits\Templates;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Menu
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Menu {
	use Feature_Extension;

	public $location = '';

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		// Register the sidebar.
		add_action( 'after_setup_theme', function() {
			register_nav_menu( $this->location, $this->name );
		} );
	}

	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_error( 'batch_task_param_not_set', 'The batch task key ' . $key . ' could not be found.' );
		}
	}

}