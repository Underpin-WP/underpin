<?php

namespace Underpin\Interfaces;

use Underpin\Exceptions\Operation_Failed;

interface Can_Update {
	/**
	 * @param Model $model
	 *
	 * @return int|string
	 * @throws Operation_Failed
	 */
	public function update( Model $model ): int|string;
}