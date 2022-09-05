<?php

namespace Underpin\Middlewares\Rest;

use Underpin\Abstracts\Rest_Middleware;
use Underpin\Exceptions\Middleware_Exception;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Request;

class Has_Param_Middleware extends Rest_Middleware {

	public function __construct( protected string $param ) {

	}

	public function run( Request $request ): void {
		try {
			$request->get_param( $this->param );
		} catch ( Unknown_Registry_Item $exception ) {
			throw new Middleware_Exception( message: 'Missing ' . $this->param . '.', code: 400, previous: $exception );
		}
	}

}