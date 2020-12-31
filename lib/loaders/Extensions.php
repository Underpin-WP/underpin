<?php

namespace Underpin\Loaders;

use Underpin\Abstracts\Registries\Loader_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Extensions extends Loader_Registry {

	protected $abstraction_class = 'Underpin\Abstracts\Extension';

	protected function set_default_items() {
		// Extensions are loaded externally.
	}

	/**
	 * @param string $key
	 *
	 * @return Extension|WP_Error Extension instance, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		$valid = parent::get( $key );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		return $valid->get();
	}

}