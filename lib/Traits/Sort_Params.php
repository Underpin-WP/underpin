<?php

namespace Underpin\Traits;


use Underpin\Abstracts\Sort_Method;
use Underpin\Enums\Direction;

trait Sort_Params {

	protected array $sort_args;

	public function by( string $field, Direction $direction, Sort_Method $method ): static {
		$this->sort_args[ $field . '__' . $direction->value ] = $method;

		return $this;
	}

}