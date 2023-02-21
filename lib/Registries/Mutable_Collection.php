<?php

namespace Underpin\Registries;

use Underpin\Abstracts\Registries\Object_Registry;

class Mutable_Collection extends Object_Registry {

	public static function make( string $abstraction_class, ?string $default = null ): static {
		$self                    = new static;
		$self->abstraction_class = $abstraction_class;
		$self->default_factory   = $default ?? $self->abstraction_class;

		return $self;
	}

}