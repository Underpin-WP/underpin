<?php

namespace Underpin\Helpers\Processors;


use Underpin\Enums\Filter;
use Underpin\Exceptions\Invalid_Field;
use Underpin\Helpers\Array_Helper;
use Underpin\Helpers\Object_Helper;

class List_Filter {

	protected array $args = [];

	public function __construct( protected array $items ) {

	}

	/**
	 * @param $key
	 *
	 * @return array
	 */
	protected function prepare_field( $key ): array {
		// Process the argument key
		$processed = explode( '__', $key );

		// Set the field type to the first item in the array.
		$field = $processed[0];

		// If there was some specificity after a __, use it.
		$type = count( $processed ) > 1 ? $processed[1] : 'in';

		return [ 'field' => $field, 'type' => $type ];
	}

	/**
	 * Determines if a registry item passes the arguments.
	 *
	 * @since 1.3.0
	 *
	 * @param object $item Item to filter
	 *
	 * @return ?object The instance, if it matches the filters.
	 */
	protected function filter_item( object $item, ): ?object {
		$valid = true;

		foreach ( $this->args as $key => $arg ) {
			/* @var string $field */
			/* @var string $type */
			extract( $this->prepare_field( $key ) );


			// Make an instanceof check. If this fails, don't bother going any further.
			if ( 'instanceof' === $field ) {
				$instances       = Array_Helper::wrap( $arg );
				$valid_instances = [];
				foreach ( $instances as $instance ) {
					if ( $item instanceof $instance ) {
						break;
					}
				}
			}

			try {
				$value = Object_Helper::pluck( $item, $field );
			} catch ( Invalid_Field $e ) {
				continue;
			}

			$fields = Array_Helper::intersect( Array_Helper::wrap($arg), Array_Helper::wrap($value) );

			// Check based on type.
			$valid = match ( $type ) {
				Filter::not_in->value => empty( $fields ),
				Filter::in->value     => ! empty( $fields ),
				Filter::and->value    => count( $fields ) === count( $arg ),
				Filter::equals->value => isset( $fields[0] ) && $fields[0] === $arg,
			};

			if ( false === $valid ) {
				break;
			}
		}

		if ( true === $valid ) {
			return $item;
		}

		return null;
	}


	/**
	 * Pre-filters the list of items.
	 *
	 * @since 1.3.0
	 *
	 * @return array
	 */
	protected function filter_item_keys(): array {
		$items = $this->items;

		// Filter out keys, if keys are specified
		if ( isset( $this->args[ Filter::in->key() ] ) ) {
			$items = Array_Helper::intersect( array_keys( $this->items ), $this->args[ Filter::in->key() ] );
			unset( $this->args[ Filter::in->key() ] );
		}

		if ( isset( $this->args[ Filter::not_in->key() ] ) ) {
			$items = Array_Helper::diff( array_keys( $this->items ), $this->args[ Filter::not_in->key() ] );
		}

		return $items;
	}


	/**
	 * Finds the first loader item that matches the provided arguments.
	 *
	 * @since 1.3.0
	 *
	 * @param array $args List of filter arguments
	 *
	 * @return ?object loader item if found.
	 */
	public function find( array $args = [] ): ?object {
		foreach ( $this->filter_item_keys() as $item_key => $item ) {
			if ( ! isset( $this->items[ $item_key ] ) ) {
				continue;
			}

			$item = $this->filter_item( $this->items[ $item_key ] );

			if ( $item ) {
				return $item;
			}
		}

		return null;
	}


	/**
	 * Queries a loader registry.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Filtered items no-longer preserve keys by default. Include "preserve_keys" argument in array if you
	 *              want to preserve keys.
	 *
	 * @return object[] Array of registry items.
	 */
	public function filter(): array {
		$results = [];
		foreach ( $this->filter_item_keys() as $item_key => $item ) {
			if ( ! isset( $this->items[ $item_key ] ) ) {
				continue;
			}

			$item = $this->filter_item( $this->items[ $item_key ] );

			if ( $item ) {
				$results[] = $item;
			}
		}

		return $results;
	}

	/**
	 * Sets the query to only include items that are not an of the provided instances.
	 *
	 * @param array $values The values to filter.
	 *
	 * @return $this
	 */
	public function not_instance_of( ...$values ): static {
		$this->args[ Filter::not_in->field( 'instanceof' ) ] = $values;

		return $this;
	}

	/**
	 * Sets the query to only include items that are an instance of all the provided instances.
	 *
	 * @param array $values The values to filter.
	 *
	 * @return $this
	 */
	public function has_all_instances( ...$values ): static {
		$this->args[ Filter::and->field( 'instanceof' ) ] = $values;

		return $this;
	}

	/**
	 * Sets the query to only include items that are instance of any the provided instances.
	 *
	 * @param array $values The values to filter.
	 *
	 * @return $this
	 */
	public function has_any_instances( ...$values ): static {
		$this->args[ Filter::in->field( 'instanceof' ) ] = $values;

		return $this;
	}

	/**
	 * Sets the query to only include items that are instance provided instances.
	 *
	 * @param string $value the instance
	 *
	 * @return $this
	 */
	public function instance_of( string $value ): static {
		$this->args[ Filter::equals->field( 'instanceof' ) ] = $value;

		return $this;
	}

	/**
	 * Sets the query to filter out items whose field has any of the provided values.
	 *
	 * @param string $field  The field to check against.
	 * @param array  $values The values to filter.
	 *
	 * @return $this
	 */
	public function not_in( string $field, ...$values ): static {
		$this->args[ Filter::not_in->field( $field ) ] = $values;

		return $this;
	}

	/**
	 * Sets the query to filter out items whose field does not have all the provided values.
	 *
	 * @param string $field  The field to check against.
	 * @param array  $values The values to filter.
	 *
	 * @return $this
	 */
	public function and( string $field, ...$values ): static {
		$this->args[ Filter::and->field( $field ) ] = $values;

		return $this;
	}

	/**
	 * Sets the query to filter out items whose field does not have all the provided values.
	 *
	 * @param string $field  The field to check against.
	 * @param array  $values The values to filter.
	 *
	 * @return $this
	 */
	public function in( string $field, ...$values ): static {
		$this->args[ Filter::in->field( $field ) ] = $values;

		return $this;
	}


	/**
	 * Sets the query to filter out items whose value is not identical to the provided value.
	 *
	 * @param string $field The field to check against.
	 * @param mixed  $value The value to check.
	 *
	 * @return $this
	 */
	public function equals( string $field, mixed $value ): static {
		$this->args[ Filter::equals->field( $field ) ] = $value;

		return $this;
	}

	/**
	 * Sets the query to filter out items whose key has any of the provided values.
	 *
	 * @param array $values The values to filter.
	 *
	 * @return $this
	 */
	public function key_not_in( ...$values ): static {
		$this->args[ Filter::not_in->key() ] = $values;

		return $this;
	}

	/**
	 * Sets the query to filter out items whose key does not have all the provided values.
	 *
	 * @param array $values The values to filter.
	 *
	 * @return $this
	 */
	public function key_in( ...$values ): static {
		$this->args[ Filter::in->key() ] = $values;

		return $this;
	}


}