<?php

namespace Underpin\Factories\Data_Providers;


use Underpin\Interfaces\Data_Provider;
use Underpin\Interfaces\Plugin_Builder;

class Plugin_Builder_Provider implements Data_Provider {

	public function __construct( protected Plugin_Builder $builder ) {
	}

	public function get_builder(): Plugin_Builder {
		return $this->builder;
	}

}