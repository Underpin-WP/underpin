<?php

namespace Underpin\Abstracts;

use Underpin\Exceptions\Middleware_Exception;
use Underpin\Factories\Request;
use Underpin\Interfaces\Identifiable;
use Underpin\Interfaces\With_Middleware;

abstract class Rest_Middleware implements Identifiable {

	/**
	 * @throws Middleware_Exception
	 */
	abstract public function run( Request $request ): void;

	/**
	 * Gets the ID for this middleware.
	 *
	 * @return string|int
	 */
	public function get_id(): string|int {
		return static::class;
	}

}