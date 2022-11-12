<?php

namespace Underpin\Abstracts;


use Underpin\Enums\Direction;
use Underpin\Exceptions\Operation_Failed;

abstract class Sort_Method {

	/**
	 * @param object    $a
	 * @param object    $b
	 * @param string    $field
	 * @param Direction $direction
	 *
	 * @return int
	 * @throws Operation_Failed
	 */
	abstract public function sort( object $a, object $b, string $field, Direction $direction ): int;

}