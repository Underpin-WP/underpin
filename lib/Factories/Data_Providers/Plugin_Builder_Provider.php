<?php

namespace Underpin\Factories\Data_Providers;


use Underpin\Interfaces\Data_Provider;
use Underpin\WordPress\Interfaces\Base_Builder;

class Plugin_Builder_Provider implements Data_Provider {

	public function __construct( protected Base_Builder $builder ) {
	}

	public function get_builder(): Base_Builder {
		return $this->builder;
	}

}