<?php

namespace Underpin\Factories;

use Underpin\Abstracts\Registries\Registry;
use Underpin\Traits\With_Parent;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Loader_Registry extends Registry {

	use With_Parent;

	public function __construct( $registry_id ) {
		$this->parent_id = $registry_id;
		parent::__construct( $registry_id );
	}

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
			/* @var $valid \Underpin\Abstracts\Registries\Loader_Registry */
			$this[ $key ]['registry'] = new $valid['registry']( $this->registry_id );
		}

		return $this[ $key ]['registry'];
	}

	protected function _add( $key, $value ) {
		// Maybe auto-set the registry.
		if ( ! isset( $value['registry'] ) ) {
			$default           = isset( $value['default'] ) ? $value['default'] : '';
			$value['registry'] = new Loader_Registry_Item( [
				'abstraction_class' => $value['instance'],
				'default_factory'   => $default,
				'parent_id'         => $this->registry_id,
			] );
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
		if ( ! is_wp_error( underpin()->logger() ) && $errors->has_errors() ) {
			underpin()->logger()->log_wp_error( 'error', $errors );
		}

		return ! $errors->has_errors();
	}

}