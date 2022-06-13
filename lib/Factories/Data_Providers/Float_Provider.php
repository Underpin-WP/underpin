<?php

namespace Underpin\Factories\Data_Providers;


use Underpin\Interfaces\Data_Provider;

class Float_Provider implements Data_Provider {

	public function __construct( public readonly float $value ) {

	}

}