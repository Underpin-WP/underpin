<?php

namespace Underpin\Factories\Data_Providers;


use Underpin\Interfaces\Data_Provider;

class String_Provider implements Data_Provider {

	public function __construct( public readonly string $value ) {
	}

}