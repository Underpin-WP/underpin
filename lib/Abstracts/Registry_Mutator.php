<?php

namespace Underpin\Abstracts;


use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Exceptions\Operation_Failed;

abstract class Registry_Mutator {
	public function __construct( protected Object_Registry $items ) {
	}

	/**
	 * @throws Operation_Failed
	 * @return Object_Registry
	 */
	abstract public function mutate(): Object_Registry;

}