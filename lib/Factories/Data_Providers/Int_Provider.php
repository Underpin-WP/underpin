<?php

namespace Underpin\Factories\Data_Providers;


use Underpin\Interfaces\Data_Provider;

class Int_Provider implements Data_Provider {

	public function __construct( public readonly int $value ) {

	}

}