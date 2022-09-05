<?php

namespace Underpin\Registries;



use Underpin\Interfaces\Feature_Extension;
use Underpin\Interfaces\Has_Response;
use Underpin\Interfaces\With_Middleware;

abstract class Rest_Action implements Feature_Extension, With_Middleware {

	protected Object_Registry $middleware;
	private bool              $middleware_ran = false;
	protected Request         $request;

	public function __construct( protected Has_Response $response ) {
		$this->middleware = new Object_Registry( Rest_Middleware::class, Rest_Middleware::class );
	}

	public function set_request( Request $request ): static {
		$this->request = $request;

		return $this;
	}

	public function do_middleware_actions(): void {
		Array_Helper::each( $this->middleware->to_array(), fn ( Rest_Middleware $middleware ) => $middleware->run( $this->request ) );
		$this->middleware_ran = true;
	}

	public function middleware_ran(): bool {
		return $this->middleware_ran;
	}

	public function get_response(): Has_Response {
		return $this->response;
	}

}
