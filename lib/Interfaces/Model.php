<?php

namespace Underpin\Interfaces;

use Underpin\Exceptions\Operation_Failed;

interface Model {

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