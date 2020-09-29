<?php
/**
 * Custom Post Type Registry
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Custom_Post_Type;
use Underpin\Abstracts\Registries\Loader_Registry;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Custom_Post_Types
 * Registry for Custom Post Types
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */
class Custom_Post_Types extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Custom_Post_Type';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
		// $this->add( 'post_type', 'Namespace\To\Class');
	}

	/**
	 * @param string $key
	 * @return Custom_Post_Type|WP_Error Script Resulting REST Endpoint class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

}