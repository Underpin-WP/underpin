<?php

namespace Underpin\Registries;

use Underpin\Abstracts\Rest_Action;
use Underpin\Abstracts\Rest_Middleware;
use Underpin\Enums\Rest;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Helpers\Array_Helper;
use Underpin\Interfaces\Can_Convert_To_Array;
use Underpin\Interfaces\Loader_Item;
use Underpin\Interfaces\With_Middleware;

class Controller implements Loader_Item, Can_Convert_To_Array {

	private Object_Registry $middleware;
	private bool            $middleware_ran;

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

	public function get_action( Rest $type ) {
		$type = $type->value;

		return new $this->$type( $this->middleware );
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
