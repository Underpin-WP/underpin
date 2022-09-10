<?php

namespace Underpin\Abstracts;


use Underpin\Helpers\Array_Helper;
use Underpin\Helpers\Processors\Array_Processor;
use Underpin\Traits\With_Dependencies;

abstract class Observer implements \Underpin\Interfaces\Observer {

	use With_Dependencies;

	public function __construct( protected string $id, protected int $priority = 10 ) {
	}

	/**
	 * @inheritDoc
	 */
	public function get_id(): string {
		return $this->id;
	}

}