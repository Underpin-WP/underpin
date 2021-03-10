<?php
/**
 * Role Registry
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Custom_Post_Type;
use Underpin\Abstracts\Registries\Loader_Registry;
use Underpin\Abstracts\Role;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Roles
 * Registry for Roles
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */
class Roles extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Role';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
		// $this->add( 'role', 'Namespace\To\Class');
	}

	/**
	 * @param string $key
	 * @return Role|WP_Error Script Resulting REST Endpoint class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

}