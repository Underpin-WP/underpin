<?php

namespace Underpin\Factories;

use Underpin\Abstracts\Registries\Registry;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Loader_Registry extends Registry {

	protected function set_default_items() {
		// Extensions are loaded externally.
	}

	/**
	 * @param string $key
	 *
	 * @return Loader|WP_Error Extension instance, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		$valid = parent::get( $key );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		// Maybe instantiate loader item.
		if ( is_array( $valid ) && isset( $valid['registry'] ) && is_string( $valid['registry'] ) ) {
			$this[ $key ]['registry'] = new $valid['registry'];
		}

		return $this[ $key ]['registry'];
	}

	protected function _add( $key, $value ) {
		// Maybe auto-set the registry.
		if ( ! isset( $value['registry'] ) ) {
			$value['registry'] = new Loader_Registry_Item( $value['instance'] );
		}

		return parent::_add( $key, $value );
	}

	protected function validate_item( $key, $value ) {
		if ( ! is_array( $value ) ) {
			// Items must be an array
		}

		if ( ! isset( $value['instance'] ) ) {
			// Items must have an instance.
		}

		return true;
	}

}