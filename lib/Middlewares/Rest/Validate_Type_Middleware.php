<?php

namespace Underpin\Middlewares\Rest;

use Underpin\Abstracts\Rest_Middleware;
use Underpin\Enums\Types;
use Underpin\Exceptions\Middleware_Exception;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Request;

abstract class Validate_Type_Middleware extends Rest_Middleware {

	public function __construct( protected string $param, protected Types $type ) {

	}

	/**
	 * @throws Middleware_Exception
	 * @throws Unknown_Registry_Item
	 */
	public function run( Request $request ): void {
		$type = gettype( $request->get_param( $this->param ) );
		if ( $type !== $this->type->name ) {
			throw new Middleware_Exception( message: 'Param ' . $this->param . ' must be a ' . $this->type->name . ', ' . $type . ' given.' );
		}
	}

}
