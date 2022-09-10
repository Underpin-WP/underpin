<?php

namespace Underpin\Abstracts;


use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Exceptions\Operation_Failed;

abstract class Registry_Mutator {
	public function __construct( protected Object_Registry $items ) {
	}

	/**
	 * @throws Operation_Failed
	 * @return array
	 */
	abstract public function mutate(): array;

}