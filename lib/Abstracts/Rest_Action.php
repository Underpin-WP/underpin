<?php

namespace Underpin\Abstracts;


use Underpin\Exceptions\Operation_Failed;
use Underpin\Factories\Registry_Items\Param;
use Underpin\Factories\Request;
use Underpin\Interfaces\Feature_Extension;
use Underpin\Interfaces\Has_Response;
use Underpin\Interfaces\With_Middleware;
use Underpin\Middlewares\Rest\Has_Param_Middleware;
use Underpin\Registries\Mutable_Collection;
use Underpin\Registries\Param_Collection;

abstract class Rest_Action implements Feature_Extension, With_Middleware, Has_Response {

	protected mixed   $response;
	protected Request $request;

	public function __construct( protected Mutable_Collection $middleware, protected Param_Collection $signature ) {
	}


	/**
	 * Adds middleware.
	 *
	 * @throws Operation_Failed
	 */
	protected function add_middleware( Request_Middleware $middleware ): static {
		$this->middleware->add( $middleware->get_id(), $middleware );

		return $this;
	}

	/**
	 * Registers a typed URL param to be included in this request.
	 *
	 * @param Param $param    The param to include
	 * @param bool  $required Set to true if this param is required in the request.
	 *
	 * @return $this
	 * @throws Operation_Failed
	 */
	protected function add_param( Param $param, bool $required = false ): static {
		$this->signature->add( $param->get_id(), $param );

		if ( $required ) {
			$this->add_middleware( new Has_Param_Middleware( $param->get_id() ) );
		}

		return $this;
	}

	public function set_request( Request $request ): static {
		$this->request = $request;

		return $this;
	}

	public function get_request(): Request {
		return $this->request;
	}

	/**
	 * Does the middleware actions for this request.
	 *
	 * @return void
	 */
	public function do_middleware_actions(): void {
		$this->middleware->each( fn ( Request_Middleware $middleware ) => $middleware->run( $this->request ) );
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

	/**
	 * Retrieves the list of params used in this action.
	 *
	 * @return Param[]
	 */
	public function get_signature(): array {
		return $this->signature->to_array();
	}

}
