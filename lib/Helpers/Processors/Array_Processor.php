<?php

namespace Underpin\Helpers\Processors;

use ReflectionException;
use Stringable;
use Underpin\Helpers\Array_Helper;
use Underpin\Interfaces\Can_Convert_To_Array;

class Array_Processor implements Can_Convert_To_Array, Stringable {

	private string $separator = ',';

	public function __construct( protected array $subject = [] ) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function to_array(): array {
		return $this->subject;
	}

	/**
	 * Flips the array, setting the values as the keys and vice-versa.
	 * Note if the subject is an array of anything but integers or strings this will fail.
	 * If you aren't sure about the values, you may want to use cast(), first.
	 *
	 * @return $this
	 */
	public function flip(): static {
		$this->subject = Array_Helper::flip( $this->subject );

		return $this;
	}

	public function keys(): static {
		$this->subject = array_keys( $this->subject );

		return $this;
	}

	public function each( callable $callback ) {
		$this->subject = Array_Helper::each( $this->subject, $callback );

		return $this;
	}

	public function after( int $position ): static {
		$this->subject = Array_Helper::after( $this->subject, $position );

		return $this;
	}

	public function before( int $position ): static {
		$this->subject = Array_Helper::before( $this->subject, $position );

		return $this;
	}

	/**
	 * Removes the provided key.
	 *
	 * @param string|int $key
	 *
	 * @return $this
	 */
	public function remove( string|int $key ): static {
		$this->subject = Array_Helper::remove( $this->subject, $key );

		return $this;
	}

	/**
	 * Create an array of new instances given arguments to pass
	 *
	 * @param $instance string The instance to create
	 *
	 * @return $this
	 */
	public function hydrate( string $instance ): static {
		$this->subject = Array_Helper::hydrate( $this->subject, $instance );

		return $this;
	}

	/**
	 * Flattens arrays of arrays into a single array where the parent array is embedded as an item keyed by the $key param
	 * Example:
	 * [
	 *   'group-1' => [['key' => 'value', 'another' => 'value'], ['key' => 'another-value', 'another' => 'value']],
	 *   'group-2' => [['key' => 'value', 'another' => 'value'], ['key' => 'another-value', 'another' => 'value']],
	 * ]
	 *
	 * Becomes:
	 *
	 * [
	 *   ['group' => 'group-1', 'key' => 'value', 'another' => 'value'],
	 *   ['group' => 'group-1', 'key' => 'another-value', 'another' => 'value'],
	 *   ['group' => 'group-2', 'key' => 'value', 'another' => 'value'],
	 *   ['group' => 'group-2', 'key' => 'another-value', 'another' => 'value']
	 * ]
	 *
	 * @param string $group_key The key to use for the group identifier.
	 *
	 */
	public function flatten( string $group_key = 'group' ): static {
		$this->subject = Array_Helper::flatten( $this->subject, $group_key );

		return $this;
	}

	public function to_indexed( string $key = 'key', string $value_key = 'value' ): static {
		$this->subject = Array_Helper::to_indexed( $this->subject, $key, $value_key );

		return $this;
	}

	/**
	 * Strips out duplicate items in the provided array.
	 *
	 * @return $this
	 */
	public function unique(): static {
		$this->subject = Array_Helper::unique( $this->subject );

		return $this;
	}

	/**
	 * Merges the provided arrays with the array that is being processed.
	 *
	 * @param array ...$defaults
	 *
	 * @return $this
	 */
	public function merge( array ...$defaults ): static {
		$this->subject = Array_Helper::merge( $this->subject, ...$defaults );

		return $this;
	}

	/**
	 * Adds an item to the beginning of the array.
	 *
	 * @param mixed ...$items items to add.
	 *
	 * @return $this
	 */
	public function prepend( ...$items ): static {
		Array_Helper::prepend( $this->subject, ...$items );

		return $this;
	}

	/**
	 * Adds items to the end of the array.
	 *
	 * @param mixed ...$items Items to add
	 *
	 * @return void
	 */
	public function append( ...$items ): static {
		Array_Helper::append( $this->subject, ...$items );

	}

	/**
	 * Recursively sorts, and optionally mutates an array of arrays.
	 *
	 * @type bool $convert_closures If true, closures will be converted to an identifiable string. Default true.
	 * @type bool $recursive        if true, this function will normalize recursively, manipulating sub-arrays.
	 *
	 * @throws ReflectionException
	 */
	public function normalize( $convert_colsures = true, $recursive = true ): static {
		$this->subject = Array_Helper::normalize( $this->subject, $convert_colsures, $recursive );

		return $this;
	}

	/**
	 * Sorts an array by the keys.
	 *
	 * @return static
	 */
	public function key_sort(): static {
		Array_Helper::key_sort( $this->subject );

		return $this;
	}

	/**
	 * Sorts the array.
	 *
	 * @param callable|int $method  The method. Can be any supported flag documented in PHP's asort, or a sorting
	 *                              callback.
	 *
	 * @return static
	 */
	public function sort( callable|int $method = SORT_REGULAR ): static {
		Array_Helper::sort( $this->subject, $method );

		return $this;
	}

	/**
	 * Applies the callback to the elements of the array being processed.
	 *
	 * @param callable $callback The callback.
	 *
	 * @return static
	 */
	public function map( callable $callback ): static {
		$this->subject = Array_Helper::map( $this->subject, $callback );

		return $this;
	}

	/**
	 * Iterates over each value in the <b>array</b>
	 * passing them to the <b>callback</b> function.
	 * If the <b>callback</b> function returns true, the
	 * current value from <b>array</b> is returned into
	 * the result array.
	 *
	 * @param callable $callback
	 *
	 * @return static
	 */
	public function filter( callable $callback ): static {
		$this->subject = Array_Helper::filter( $this->subject, $callback );

		return $this;
	}

	/**
	 * Strips the keys from the array.
	 *
	 * @return static
	 */
	public function values(): static {
		$this->subject = Array_Helper::values( $this->subject );

		return $this;
	}

	/**
	 * Cast all items in the array to the specified type.
	 *
	 * @param string $type
	 *
	 * @return static
	 */
	public function cast( string $type ): static {
		$this->subject = Array_Helper::cast( $this->subject, $type );

		return $this;
	}

	/**
	 * Plucks a value from an array, if it is an array. Falls back to default value if not-set.
	 *
	 * @param string $key     The key to pluck
	 * @param mixed  $default The fallback value
	 *
	 * @return static
	 */
	public function pluck( string $key, mixed $default = false ): static {
		$this->subject = Array_Helper::pluck_recursive( $this->subject, $key, $default );

		return $this;
	}

	/**
	 * Filters the array to only contain values contained in all provided arrays.
	 *
	 * @param array ...$items
	 *
	 * @return static
	 */
	public function intersect( array ...$items ): static {
		$this->subject = Array_Helper::intersect( $this->subject, ...$items );

		return $this;
	}

	/**
	 * Filters the array to only contain values contained in all provided arrays.
	 *
	 * @param array ...$items
	 *
	 * @return static
	 */
	public function intersect_keys( array ...$items ): static {
		$this->subject = Array_Helper::intersect_keys( $this->subject, ...$items );

		return $this;
	}

	/**
	 * Filters the array to only contain values only contained in a single array.
	 *
	 * @param array ...$items
	 *
	 * @return static
	 */
	public function diff( ...$items ): static {
		$this->subject = Array_Helper::diff( $this->subject, ...$items );

		return $this;
	}

	/**
	 * Reverses the order of the items in the array.
	 *
	 * @param bool $preserve_keys If set to true keys are preserved.
	 *
	 * @return static
	 */
	public function reverse( bool $preserve_keys = true ): static {
		$this->subject = Array_Helper::reverse( $this->subject, $preserve_keys );

		return $this;
	}

	public function set_separator( string $separator ): static {
		$this->separator = $separator;

		return $this;
	}

	public function __toString(): string {
		return implode( $this->separator, $this->subject );
	}

}