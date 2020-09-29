<?php
/**
 * Taxonomy Registry
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Custom_Post_Type;
use Underpin\Abstracts\Registries\Loader_Registry;
use Underpin\Abstracts\Taxonomy;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Custom_Post_Types
 * Registry for Taxonomies
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */
class Taxonomies extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Taxonomy';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
//		$this->add( 'taxonomy', 'Namespace\To\Class');
	}

	/**
	 * @param string $key
	 * @return Taxonomy|WP_Error Script Resulting REST Endpoint class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

}