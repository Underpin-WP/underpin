<?php

namespace Underpin\Abstracts;


use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Middleware_Exception;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Registry_Items\Param;
use Underpin\Factories\Request;
use Underpin\Helpers\Array_Helper;
use Underpin\Interfaces\Feature_Extension;
use Underpin\Interfaces\Has_Response;
use Underpin\Interfaces\With_Middleware;
use Underpin\Middlewares\Rest\Has_Param_Middleware;
use Underpin\Registries\Object_Registry;
use Underpin\Registries\Param_Registry;

abstract class Rest_Action implements Feature_Extension, With_Middleware, Has_Response {

	protected mixed   $response;
	private bool      $middleware_ran = false;
	protected Request $request;

	public function __construct( protected Object_Registry $middleware, protected Param_Registry $signature ) {
	}


	/**
	 * Adds middleware.
	 *
	 * @throws Unknown_Registry_Item
	 * @throws Invalid_Registry_Item
	 */
	protected function add_middleware( string $key, Rest_Middleware $middleware ): static {
		$this->middleware->add( $key, $middleware );

		return $this;
	}

	/**
	 * Registers a typed URL param to be included in this request.
	 *
	 * @param Param $param    The param to include
	 * @param bool  $required Set to true if this param is required in the request.
	 *
	 * @return $this
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	protected function add_param( Param $param, bool $required = false ): static {
		$this->signature->add( $param->get_id(), $param );

		if ( $required ) {
			$this->middleware->add( 'required_param_' . $param->get_id(), new Has_Param_Middleware( $param->get_id() ) );
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
	 * @throws Middleware_Exception
	 */
	public function do_middleware_actions(): void {
		if ( ! $this->middleware_ran() ) {
			$this->middleware->each( fn ( Rest_Middleware $middleware ) => $middleware->run( $this->request ) );
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

	/**
	 * Retrieves the list of params used in this action.
	 *
	 * @return Param[]
	 */
	public function get_signature(): array {
		return $this->signature->to_array();
	}

}
