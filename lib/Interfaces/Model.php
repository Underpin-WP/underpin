<?php

namespace Underpin\Interfaces;

use Underpin\Exceptions\Operation_Failed;

interface Model {

	public function get_id(): string|int|null;

	public function set_id( string|int $id ): static;

	/**
	 * @throws Operation_Failed
	 */
	public function save(): static;

	/**
	 * @throws Operation_Failed
	 */
	public function delete(): static;

	/**
	 * @throws Operation_Failed
	 */
	public function clone(): Model;

}