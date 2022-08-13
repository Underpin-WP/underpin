<?php

namespace Underpin\Interfaces;


use Underpin\Exceptions\Operation_Failed;

interface Can_Create {
	/**
	 * @param Model $model
	 *
	 * @return int|string
	 * @throws Operation_Failed
	 */
	public function create( Model $model ): int|string;
}