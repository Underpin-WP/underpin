<?php

namespace Underpin\Registries;

use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Abstracts\Rest_Action;
use Underpin\Abstracts\Rest_Middleware;
use Underpin\Enums\Rest;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Factories\Registry_Items\Param;
use Underpin\Helpers\Array_Helper;
use Underpin\Interfaces\Can_Convert_To_Array;
use Underpin\Interfaces\Loader_Item;
use Underpin\Middlewares\Rest\Has_Param_Middleware;

class Controller implements Loader_Item, Can_Convert_To_Array {

	public readonly Object_Registry  $middleware;
	public readonly Param_Collection $signature;

	/**
	 * @param string                         $route
	 * @param class-string<Rest_Action>|null $get
	 * @param class-string<Rest_Action>|null $post
	 * @param class-string<Rest_Action>|null $put
	 * @param class-string<Rest_Action>|null $delete
	 */
	public function __construct(
		public readonly string $route,
		protected ?string      $get = null,
		protected ?string      $post = null,
		protected ?string      $put = null,
		protected ?string      $delete = null
	) {
		$this->middleware = Mutable_Collection::make( Rest_Middleware::class, Rest_Middleware::class );
		$this->signature  = new Param_Collection;
	}

	/**
	 * Adds middleware.
	 *
	 * @throws Operation_Failed
	 */
	public function add_middleware( Rest_Middleware $middleware ): static {
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
	public function add_param( Param $param, bool $required = false ): static {
		$this->signature->add( $param->get_id(), $param );

		if ( $required ) {
			$this->middleware->add( 'required_param_' . $param->get_id(), new Has_Param_Middleware( $param->get_id() ) );
		}

		return $this;
	}

	/**
	 * Gets the action in this controller from the request type.
	 *
	 * @param Rest $type The request type.
	 *
	 * @return Rest_Action
	 */
	public function get_action( Rest $type ): Rest_Action {
		$type = strtolower( $type->value );

		return new $this->$type( $this->middleware, $this->signature );
	}

	/**
	 * Gets this controller's ID
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->route;
	}

	/**
	 * Converts this endpoint to an array
	 *
	 * @return array
	 */
	public function to_array(): array {
		return Array_Helper::where_not_null( [
			Rest::Get->value    => $this->get,
			Rest::Post->value   => $this->post,
			Rest::Put->value    => $this->put,
			Rest::Delete->value => $this->delete,
		] );
	}

}
