<?php

namespace Underpin\Factories\Data_Providers;


use Underpin\Interfaces\Data_Provider;

class Bool_Provider implements Data_Provider {

	public function __construct( public readonly bool $value ) {

	}

}