<?php
/**
 * Admin Menus
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Admin_Menu;
use Underpin\Abstracts\Registries\Loader_Registry;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Menus
 * Registry for Admin Menus
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */
class Admin_Menus extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Admin_Menu';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
		// $this->add( 'admin_page', 'Underpin\Admin_Menu' );
	}

	/**
	 * @param string $key
	 * @return Admin_Menu|WP_Error Script Resulting admin page class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}
}