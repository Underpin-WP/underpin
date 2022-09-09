<?php

namespace Underpin\Helpers\Processors;


use Underpin\Enums\Filter;
use Underpin\Exceptions\Invalid_Field;
use Underpin\Helpers\Array_Helper;
use Underpin\Helpers\Object_Helper;
use Underpin\Traits\Filter_Params;

class List_Filter {

	use Filter_Params;

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
	 * @param object $item Item to filter
	 *
	 * @return ?object The instance, if it matches the filters.
	 */
	protected function filter_item( object $item, ): ?object {
		$valid = true;

		foreach ( $this->filter_args as $key => $arg ) {
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

			if ( $type === Filter::callback->value ) {
				$valid = $arg( $value );
			} else {

				$fields = Array_Helper::intersect( Array_Helper::wrap( $arg ), Array_Helper::wrap( $value ) );

				// Check based on type.
				$valid = match ( $type ) {
					Filter::not_in->value                   => empty( $fields ),
					Filter::in->value                       => ! empty( $fields ),
					Filter::and->value                      => count( $fields ) === count( $arg ),
					Filter::equals->value                   => isset( $fields[0] ) && $fields[0] === $arg,
					Filter::less_than->value                => array_sum( Array_Helper::wrap( $value ) ) < $arg,
					Filter::greater_than->value             => array_sum( Array_Helper::wrap( $value ) ) > $arg,
					Filter::greater_than_or_equal_to->value => array_sum( Array_Helper::wrap( $value ) ) >= $arg,
					Filter::less_than_or_equal_to->value    => array_sum( Array_Helper::wrap( $value ) ) <= $arg,
				};
			}

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
	 * @return array
	 */
	protected function filter_item_keys(): array {
		$items = $this->items;

		// Filter out keys, if keys are specified
		if ( isset( $this->filter_args[ Filter::in->key() ] ) ) {
			$items = Array_Helper::intersect( array_keys( $this->items ), $this->filter_args[ Filter::in->key() ] );
			unset( $this->filter_args[ Filter::in->key() ] );
		}

		if ( isset( $this->filter_args[ Filter::not_in->key() ] ) ) {
			$items = Array_Helper::diff( array_keys( $this->items ), $this->filter_args[ Filter::not_in->key() ] );
		}

		return $items;
	}


	/**
	 * Finds the first loader item that matches the provided arguments.
	 *
	 * @return ?object loader item if found.
	 */
	public function find(): ?object {
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
	 * Seeds a new instance of the list filter, using pre-generated arguments and items.
	 *
	 * @param array $items
	 * @param array $args
	 *
	 * @return static
	 */
	public static function seed( array $items, array $args ): static {
		$self              = new static( $items );
		$self->filter_args = $args;

		return $self;
	}

}