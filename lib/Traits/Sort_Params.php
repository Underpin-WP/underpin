<?php

namespace Underpin\Traits;


use Underpin\Abstracts\Sort_Method;
use Underpin\Enums\Direction;
use Underpin\Factories\Sort_Methods\Basic;

trait Sort_Params {

	protected array $sort_args = [];

	/**
	 * @param string                                $field
	 * @param Direction                             $direction
	 * @param Sort_Method|class-string<Sort_Method> $method
	 *
	 * @return $this
	 */
	public function sort_by( string $field, Direction $direction = Direction::Ascending, Sort_Method|string $method = Basic::class ): static {
		$this->sort_args[ $field . '__' . $direction->value ] = $method;

		return $this;
	}

}