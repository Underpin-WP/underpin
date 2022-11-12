<?php

namespace Underpin\Middlewares\Rest;

use Underpin\Abstracts\Request_Middleware;
use Underpin\Exceptions\Middleware_Exception;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Factories\Request;

class Has_Param_Middleware extends Request_Middleware {

	public function __construct( protected string $param ) {

	}

	public function run( Request $request ): void {
		try {
			$request->get_param( $this->param );
		} catch ( Operation_Failed $exception ) {
			throw new Middleware_Exception( message: 'Missing ' . $this->param . '.', code: 400, previous: $exception );
		}
	}

	public function get_id(): string|int {
		return "has_param_$this->param";
	}

}