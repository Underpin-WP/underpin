<?php

namespace Underpin\Interfaces;

interface Item_With_Dependencies extends Identifiable {

	/**
	 * @return array
	 */
	function get_dependencies() : array;

	/**
	 * @return int
	 */
	function get_priority(): int;

	/**
	 * Adds a dependency to the list of dependencies.
	 * @param string $dependency_id
	 *
	 * @return static
	 */
	function add_dependency( string $dependency_id ): static;

	/**
	 * Removes a dependency from the list of dependencies.
	 * @param string $dependency_id
	 *
	 * @return mixed
	 */
	function remove_dependency( string $dependency_id ): static;
}