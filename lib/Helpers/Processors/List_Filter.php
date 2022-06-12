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
	 * @param array  $args List of arguments
	 *
	 * @return ?object The instance, if it matches the filters.
	 */
	protected function filter_item( object $item, array $args ): ?object {
		$valid = true;

		foreach ( $args as $key => $arg ) {
			/* @var string $field */
			/* @var string $type */
			extract( $this->prepare_field( $key ) );

			try {
				$field = Object_Helper::pluck( $item, $field );
			} catch ( Invalid_Field $e ) {
				continue;
			}

			$fields = Array_Helper::intersect( $arg, $field );

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
		foreach ( $this->filter_item_keys() as $item_key ) {
			if ( ! isset( $this->items[ $item_key ] ) ) {
				continue;
			}

			$item = $this->filter_item( $this->items[ $item_key ], $args );

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

		// Filter out items, if loader keys are specified
		foreach ( $this->filter_item_keys() as $item_key ) {
			if ( ! isset( $this->items[ $item_key ] ) ) {
				continue;
			}

			$item = $this->items[ $item_key ];

			if ( $item ) {
				if ( isset( $args['preserve_keys'] ) && true === $args['preserve_keys'] ) {
					$results[ $item_key ] = $item;
				} else {
					$results[] = $item;
				}
			}
		}

		return $results;
	}

	/**
	 * Sets the query to filter out items whose field has any of the provided values.
	 *
	 * @param string $field  The field to check against.
	 * @param array  $values The values to filter.
	 *
	 * @return $this
	 */
	public function not_in( string $field, array $values ): static {
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
	public function and( string $field, array $values ): static {
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
	public function in( string $field, array $values ): static {
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
	public function key_not_in( array $values ): static {
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
	public function key_in( array $values ): static {
		$this->args[ Filter::in->key() ] = $values;

		return $this;
	}


	/**
	 * Sets the query to filter out items whose key is not identical to the provided value.
	 *
	 * @param mixed $value The value to check.
	 *
	 * @return $this
	 */
	public function key_equals( mixed $value ): static {
		$this->args[ Filter::equals->key() ] = $value;

		return $this;
	}

}