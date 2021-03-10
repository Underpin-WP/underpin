<?php
namespace Underpin\Loaders;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Underpin\Abstracts\Registries\Loader_Registry;

class Post_Meta extends Loader_Registry {

	protected $abstraction_class = 'Underpin\Factories\Post_Meta_Type';

	protected function set_default_items() {
		// TODO: Implement set_default_items() method.
	}

	/**
	 * @param string $key
	 *
	 * @return \Underpin\Factories\Post_Meta_Type|\WP_Error Post Meta instance, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

}