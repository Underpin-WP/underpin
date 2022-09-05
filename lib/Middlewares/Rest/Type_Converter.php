<?php

namespace Underpin\Middlewares\Rest;

use Underpin\Abstracts\Rest_Middleware;
use Underpin\Enums\Types;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Registry_Items\Url_Param;
use Underpin\Factories\Request;

class Type_Converter extends Rest_Middleware {

	public function __construct( protected string $param, protected Types $type ) {

	}

	/**
	 * @throws Operation_Failed
	 * @throws Unknown_Registry_Item
	 */
	public function run( Request $request ): void {
		$request->set_param( ( new Url_Param( $this->param, $this->type ) )->set_value( (int) $request->get_param( 'id' )->get_value() ) );
	}

}