<?php

namespace Underpin\Helpers;

use Exception;
use Underpin\Exceptions\Invalid_Field;


class Object_Helper {

	/**
	 * Helper function used to construct factory classes from a standard array syntax.
	 *
	 * @since 1.2
	 *
	 * @param string|array|object $value           The value used to generate the class.
	 *                                             Can be an array with "class" and "args", an associative array, a
	 *                                             string, or a class instance. If it is an array with "class" and
	 *                                             "args", make_class will construct the factory specified in
	 *                                             "class" using the provided "args"
	 *                                             If it is an associative array, make_class will construct the default
	 *                                             factory, passing the array of arguments to the constructor. If it is a
	 *                                             string, make_class will try to instantiate the class with no args. If
	 *                                             it is already a class, make_class will simply return the class
	 *                                             directly.
	 * @param string              $default_factory The default factory to use if a class is not provided in $value.
	 *
	 * @return object The instantiated class.
	 */
	public static function make_class( string|array|object $value = [], string $default_factory = '' ): object {
		// If the value is a string, assume it's a class reference.
		if ( is_string( $value ) ) {
			$class = new $value;

			// If the value is an array, the class still needs to be defined.
		} elseif ( is_array( $value ) ) {

			// If the class is specified, construct the class from the specified value.
			if ( isset( $value['class'] ) ) {
				$class = $value['class'];
				$args  = $value['args'] ?? [];

				// Otherwise, fallback to the default, and use the value as an array of arguments for the default.
			} else {

				$class = $default_factory;
				$args  = $value;
			}

			$class = new $class( ...$args );

			// Otherwise, assume the class is already instantiated, and return it directly.
		} else {
			$class = $value;
		}

		return $class;
	}

	/**
	 * Gets a field from an object. Attempts to call get_field, and the fields accessor.
	 *
	 * @throws Invalid_Field
	 */
	public static function pluck( object $value, string $fields ): mixed {
		$fields = explode( '.', $fields );
		foreach ( $fields as $field ) {
			// Bail early if this field is not in this object.
			if ( is_callable( [ $value, "get_$field" ] ) ) {
				$value = call_user_func( [ $value, "get_$field" ] );
			} else {
				try {
					$value = $value->$field;
				} catch ( Exception $e ) {
					throw new Invalid_Field( message: 'The provided field cannot be retrieved.', code: 0, type: 'error', previous: $e );
				}
			}
		}

		return $value;
	}

}