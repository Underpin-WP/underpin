<?php
namespace Underpin\Loaders;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Underpin\Abstracts\Registries\Loader_Registry;

class User_Meta extends Loader_Registry {

	protected $abstraction_class = 'Underpin\Factories\User_Meta_Type';

	protected function set_default_items() {
		// TODO: Implement set_default_items() method.
	}

	/**
	 * @param string $key
	 *
	 * @return \Underpin\Factories\User_Meta_Type|\WP_Error Post Meta instance, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

}