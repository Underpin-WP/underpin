<?php

namespace Underpin\Factories;

use Underpin\Abstracts\Registries\Registry;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Loader_Registry extends Registry {

	protected function set_default_items() {
		// Loaders are added externally.
	}

	/**
	 * @param string $key
	 *
	 * @return object|WP_Error Extension instance, if it exists. WP_Error, otherwise.
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
			$default           = isset( $value['default'] ) ? $value['default'] : '';
			$value['registry'] = new Loader_Registry_Item( $value['instance'], $default );
		}

		return parent::_add( $key, $value );
	}

	protected function validate_item( $key, $value ) {
		$errors = new WP_Error();
		if ( ! is_array( $value ) ) {
			$errors->add(
				'loader_item_must_be_array',
				'The registered specification for the loader passed something other than an array.',
				[
					'value' => $value,
					'key'   => $key,
				]
			);
		}

		if ( ! isset( $value['instance'] ) && ! isset( $value['registry'] ) ) {
			$errors->add(
				'loader_item_must_provide_instance_or_registry',
				'The registered specification for the loader did not provide an instance, or a registry.',
				[
					'value' => $value,
					'key'   => $key,
				]
			);
		}

		// Log errors, if possible.
		if ( ! is_wp_error( underpin()->logger() ) ) {
			underpin()->logger()->log_wp_error( 'error', $errors );
		}

		return ! $errors->has_errors();
	}

}