<?php

namespace Underpin\Registries;


use Underpin\Abstracts\Rest_Middleware;
use Underpin\Factories\Request;
use Underpin\Helpers\Array_Helper;
use Underpin\Interfaces\Feature_Extension;
use Underpin\Interfaces\Has_Response;
use Underpin\Interfaces\With_Middleware;

abstract class Rest_Action implements Feature_Extension, With_Middleware, Has_Response {

	protected Object_Registry $middleware;
	protected mixed           $response;
	private bool              $middleware_ran = false;
	protected Request         $request;

	public function __construct() {
		$this->middleware = new Object_Registry( Rest_Middleware::class, Rest_Middleware::class );
	}

	public function set_request( Request $request ): static {
		$this->request = $request;

		return $this;
	}

	/**
	 * Does the middleware actions for this request.
	 *
	 * @return void
	 * @throws \Underpin\Exceptions\Middleware_Exception
	 */
	public function do_middleware_actions(): void {
		if ( ! $this->middleware_ran() ) {
			Array_Helper::each( $this->middleware->to_array(), fn ( Rest_Middleware $middleware ) => $middleware->run( $this->request ) );
			$this->middleware_ran = true;
		}
	}

	/**
	 * Returns true if the middleware ran.
	 *
	 * @return bool
	 */
	public function middleware_ran(): bool {
		return $this->middleware_ran;
	}

	/**
	 * Set the response.
	 *
	 * @param mixed $value
	 *
	 * @return $this
	 */
	protected function set_response( mixed $value ): static {
		$this->response = $value;

		return $this;
	}

	public function get_response(): mixed {
		return $this->response;
	}

}
