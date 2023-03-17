<?php

namespace Underpin\Helpers;

use Closure;
use ReflectionException;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Traits\With_Closure_Converter;

class String_Helper {

	use With_Closure_Converter;

	public static function pluarize( $singular, $count, $plural = 's' ) {
		if ( $count === 1 ) {
			return $singular;
		}

		return $singular . $plural;
	}

	/**
	 * Converts the given string to use camelCase
	 *
	 * @param string $subject
	 *
	 * @return string
	 */
	public static function camel_case(string $subject): string
	{
		return lcfirst(static::pascal_case($subject));
	}

	/**
	 * Converts the given string to use PascalCase
	 *
	 * @param string $subject
	 *
	 * @return string
	 */
	public static function pascal_case(string $subject) : string
	{
		return Array_Helper::process(explode(' ', str_replace(['-', '_'], ' ', $subject)))
											 ->map(fn (string $piece) => ucfirst($piece))
											 ->set_separator('')
											 ->to_string();
	}

	/**
	 * Creates a 32 character hash from the provided value.
	 *
	 * @param mixed        $data The value to hash.
	 * @param false|string $key  Optional. The secret key to provide. Required if hash needs to be secure.
	 *
	 * @return string a 32 character hash from the provided value.
	 * @throws Operation_Failed
	 */
	public static function create_hash( mixed $data, bool|string $key = false ): string {
		try {
			// If object, convert to array.
			if ( is_object( $data ) ) {
				$data = (array) $data;
			}

			// Normalize the array
			if ( is_array( $data ) ) {
				$data = Array_Helper::normalize( $data );
			}

			// Convert closures
			if ( $data instanceof Closure ) {
				$data = self::convert_closure( $data );
			}

			if ( false === $key ) {
				return hash( 'md5', serialize( $data ) );
			} else {
				return hash_hmac( 'md5', serialize( $data ), $key );
			}
		} catch ( ReflectionException $e ) {
			throw new Operation_Failed( 'Could not create hash from the provided data.', previous: $e );
		}
	}

	/**
	 * Trim the specified item from the end of the string.
	 *
	 * @param string $haystack Original string
	 * @param string $needle   String to check
	 *
	 * @return bool
	 */
	public static function ends_with( string $haystack, string $needle ): bool {
		return str_ends_with( $haystack, $needle );
	}

	/**
	 * Trim the specified item from the front of the string.
	 *
	 * @param string $haystack Original string
	 * @param string $needle   String to check
	 *
	 * @return bool
	 */
	public static function starts_with( string $haystack, string $needle ): bool {
		return str_starts_with( $haystack, $needle );
	}

	/**
	 * Trim the specified item from the end of the string.
	 *
	 * @param string $subject Original string
	 * @param string $trim    Content to trim from the end, if it exists.
	 *
	 * @return string
	 */
	public static function trim_trailing( string $subject, string $trim ): string {
		if ( $subject === $trim ) return '';

		if ( self::ends_with( $subject, $trim ) ) {
			return substr( $subject, 0, strlen( $subject ) - strlen( $trim ) );
		}

		return $subject;
	}

	/**
	 * Trim the specified item from the front of the string.
	 *
	 * @param string $subject Original string
	 * @param string $trim    Content to trim from the beginning, if it exists.
	 *
	 * @return string
	 */
	public static function trim_leading( string $subject, string $trim ): string {
		if ( $subject === $trim ) return '';

		if ( self::starts_with( $subject, $trim ) ) {
			return substr( $subject, strlen( $trim ) );
		}

		return $subject;
	}

	/**
	 * Append the specified string, if that string is not already appended.
	 *
	 * @param string $subject The subject to append to
	 * @param string $append  The string to append if it isn't already appended.
	 *
	 * @return string
	 */
	public static function append( string $subject, string $append ): string {
		if ( self::ends_with( $subject, $append ) ) {
			return $subject;
		}

		return $subject . $append;
	}

	/**
	 * Prepends the specified string, if that string is not already prepended.
	 *
	 * @param string $subject The subject to prepend to
	 * @param string $prepend The string to prepend if it isn't already prepended.
	 *
	 * @return string
	 */
	public static function prepend( string $subject, string $prepend ): string {
		if ( self::starts_with( $subject, $prepend ) ) {
			return $subject;
		}

		return $prepend . $subject;
	}

	public static function basename( $subject, $divider = '/' ): ?string {
		$items = explode( $divider, $subject );

		return array_pop( $items );
	}

	public static function after( $subject, $after = ' ' ): string {
		return substr( $subject, strpos( $subject, $after ) + 1 );
	}

	public static function before( $subject, $before = ' ' ): string {
		return substr( $subject, 0, strpos( $subject, $before ) - strlen( $subject ) );
	}

	public static function get_buffer( callable $callback, ...$args ): string {
		ob_start();
		$callback( ...$args );
		return (string) ob_get_clean();
	}

}