<?php

namespace Underpin\Interfaces;


interface Observer extends Item_With_Dependencies {

	/**
	 * @inheritDoc
	 */
	public function get_priority(): int;

	/**
	 * @inheritDoc
	 */
	public function get_dependencies(): array;

	/**
	 * @inheritDoc
	 */
	public function add_dependency( string $dependency_id ): static;

	/**
	 * @inheritDoc
	 */
	public function remove_dependency( string $dependency_id ): static;

	/**
	 * @param                    $instance
	 * @param Data_Provider|null $provider
	 *
	 * @return void
	 */
	public function update( $instance, ?Data_Provider $provider ): void;

}