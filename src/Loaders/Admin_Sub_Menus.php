<?php
/**
 * Admin Pages
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Admin_Page;
use Underpin\Abstracts\Registries\Loader_Registry;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Pages
 * Registry for Admin Pages
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */
class Admin_Sub_Menus extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Admin_Sub_Menu';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
		// $this->add( 'admin_page', 'Underpin\Admin_Page' );
	}

	/**
	 * @param string $key
	 * @return Admin_Page|WP_Error Script Resulting admin page class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}
}