<?php
/**
 * Menu Loader
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Registries\Loader_Registry;
use Underpin\Abstracts\Sidebar;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Menus
 * Loader for menus
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */
class Menus extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = '\Underpin\Abstracts\Menu';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
	}

	/**
	 * @param string $key
	 * @return Menu|WP_Error Script Resulting script class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}
}