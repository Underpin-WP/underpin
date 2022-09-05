<?php

namespace Underpin\Abstracts;

use Underpin\Exceptions\Middleware_Exception;
use Underpin\Factories\Request;

abstract class Rest_Middleware {

	/**
	 * @throws Middleware_Exception
	 */
	abstract public function run( Request $request ): void;

}