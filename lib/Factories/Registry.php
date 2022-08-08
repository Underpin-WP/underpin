<?php

namespace Underpin\Factories;


use Closure;

class Registry extends \Underpin\Abstracts\Registries\Registry {

	public function __construct( protected Closure $validate_callback ) {
	}

	protected function validate_item( $key, $value ): bool {
		return call_user_func( $this->validate_callback, $key, $value ) === true;
	}

}