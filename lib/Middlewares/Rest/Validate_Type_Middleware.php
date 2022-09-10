<?php

namespace Underpin\Middlewares\Rest;

use Underpin\Abstracts\Request_Middleware;
use Underpin\Enums\Types;
use Underpin\Exceptions\Middleware_Exception;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Factories\Request;

abstract class Validate_Type_Middleware extends Request_Middleware {

	public function __construct( protected string $param, protected Types $type ) {

	}

	/**
	 * @throws Middleware_Exception
	 * @throws Operation_Failed
	 */
	public function run( Request $request ): void {
		$type = gettype( $request->get_param( $this->param ) );
		if ( $type !== $this->type->name ) {
			throw new Middleware_Exception( message: 'Param ' . $this->param . ' must be a ' . $this->type->name . ', ' . $type . ' given.' );
		}
	}

	public function get_id(): string {
		return "validate_{$this->param}_type_{$this->type->name}";
	}

}
