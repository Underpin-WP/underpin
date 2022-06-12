<?php

namespace Underpin\Factories;

class Object_Registry extends \Underpin\Abstracts\Registries\Object_Registry {

	public function __construct( protected string $abstraction_class, ?string $default = null ) {
		$this->default_factory = $default ?? $this->abstraction_class;
	}

}