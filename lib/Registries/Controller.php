<?php

namespace Underpin\Registries;

use Underpin\Abstracts\Rest_Action;
use Underpin\Enums\Rest;
use Underpin\Helpers\Array_Helper;
use Underpin\Interfaces\Can_Convert_To_Array;
use Underpin\Interfaces\Loader_Item;

class Controller implements Loader_Item, Can_Convert_To_Array {

	/**
	 * @param string                         $route
	 * @param class-string<Rest_Action>|null $get
	 * @param class-string<Rest_Action>|null $post
	 * @param class-string<Rest_Action>|null $put
	 * @param class-string<Rest_Action>|null $delete
	 */
	public function __construct(
		public readonly string $route,
		public readonly ?string $get = null,
		public readonly ?string $post = null,
		public readonly ?string $put = null,
		public readonly ?string $delete = null
	) {

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
