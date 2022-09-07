<?php
/**
 * Registry Class.
 * This is used any time a set of identical things are stored.
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts\Registries;

use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Helpers\Array_Helper;
use Underpin\Helpers\Processors\List_Filter;
use Underpin\Interfaces\Queryable;

/**
 * Class Registry.
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Registry implements Queryable {

	protected array $storage = [];

	/**
	 * Validates an item. This runs just before adding items to the registry.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The key to validate.
	 * @param mixed  $value The value to validate.
	 *
	 * @return boolean true if the item is valid.
	 * @throws Invalid_Registry_Item
	 */
	abstract protected function validate_item( string $key, mixed $value ): bool;

	/**
	 * Adds the item to the registry.
	 *
	 * @param string $key   The key to validate.
	 * @param mixed  $value The value to validate.
	 *
	 * @return void
	 */
	protected function _add( string $key, mixed $value ): void {
		$this->storage[ $key ] = $value;
	}

	/**
	 * Returns true if an item is registered to this registry.
	 *
	 * @param string $key The key to check.
	 *
	 * @return bool True if registered, otherwise false.
	 */
	public function is_registered( string $key ): bool {
		return isset( $this->storage[ $key ] );
	}

	/**
	 * Validates, and adds an item to the registry.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The key to validate.
	 * @param mixed  $value The value to validate.
	 *
	 * @return static The current instance
	 * @throws Invalid_Registry_Item
	 */
	public function add( string $key, mixed $value ): static {
		$valid = $this->validate_item( $key, $value );

		if ( true === $valid ) {
			$this->_add( $key, $value );
		}

		return $this;
	}

	/**
	 * Retrieves a registered item.
	 *
	 * @param string $key The identifier for the item.
	 *
	 * @return mixed the item value.
	 * @throws Unknown_Registry_Item
	 */
	public function get( string $key ): mixed {
		if ( $this->is_registered( $key ) ) {
			return $this->storage[ $key ];
		} else {
			throw new Unknown_Registry_Item( $key, get_called_class() );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function to_array(): array {
		return $this->storage;
	}

	/**
	 * Instantiates a list filter query against this registry.
	 *
	 * @since 3.0.0
	 *
	 * @return List_Filter query object
	 */
	public function query(): List_Filter {
		return new List_Filter( $this->to_array() );
	}


	/**
	 * Maps through items in this registry.
	 *
	 * @param callable $callback
	 *
	 * @return array
	 */
	public function each( callable $callback ): array {
		return Array_Helper::each( $this->to_array(), $callback );
	}

	/**
	 * Plucks a value from an array, if it is an array. Falls back to default value if not-set.
	 *
	 * @param string      $key
	 * @param mixed|false $default
	 *
	 * @return array
	 */
	public function pluck( string $key, mixed $default = false ): array {
		return Array_Helper::pluck( $this->to_array(), $key, $default );
	}

}