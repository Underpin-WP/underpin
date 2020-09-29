<?php
/**
 * Admin Bar Menu Registry
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Admin_Bar_Menu;
use Underpin\Abstracts\Registries\Loader_Registry;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Bar_Menus
 * Registry for Admin Pages
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */
class Admin_Bar_Menus extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Admin_Bar_Menu';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
		if ( underpin()->is_debug_mode_enabled() ) {
			$this->add( 'debug_bar', 'Underpin\Utilities\Debug_Bar' );
		}
	}

	/**
	 * @param string $key
	 * @return Admin_Bar_Menu|WP_Error Script Resulting admin page class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}
}