<?php

namespace Underpin\Registries;

use Underpin\Abstracts\Rest_Action;
use Underpin\Abstracts\Rest_Middleware;
use Underpin\Enums\Rest;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Registry_Items\Url_Param;
use Underpin\Helpers\Array_Helper;
use Underpin\Interfaces\Can_Convert_To_Array;
use Underpin\Interfaces\Loader_Item;
use Underpin\Middlewares\Rest\Has_Param_Middleware;

class Controller implements Loader_Item, Can_Convert_To_Array {

	private Object_Registry $middleware;

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
		$this->middleware = new Object_Registry( Rest_Middleware::class, Rest_Middleware::class );
		$this->signature  = new Object_Registry( Url_Param::class, Url_Param::class );
	}

	/**
	 * Adds middleware
	 *
	 * @throws Unknown_Registry_Item
	 * @throws Invalid_Registry_Item
	 */
	public function add_middleware( string $key, Rest_Middleware $middleware ): static {
		$this->middleware->add( $key, $middleware );

		return $this;
	}

	public function add_param( Url_Param $param, bool $required = false ): static {
		$this->signature->add( $param->get_id(), $param );

		if ( $required ) {
			$this->middleware->add( 'required_param_' . $param->get_id(), new Has_Param_Middleware( $param->get_id() ) );
		}

		return $this;
	}

	public function get_action( Rest $type ): Rest_Action {
		$type = strtolower( $type->value );

		return new $this->$type( $this->middleware, $this->signature );
	}

	public function get_id(): string {
		return $this->route;
	}

	public function to_array(): array {
		return Array_Helper::where_not_null( [
			Rest::Get->value    => $this->get,
			Rest::Post->value   => $this->post,
			Rest::Put->value    => $this->put,
			Rest::Delete->value => $this->delete,
		] );
	}

}
