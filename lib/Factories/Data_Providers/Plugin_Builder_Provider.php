<?php

namespace Underpin\Factories\Data_Providers;


use Underpin\Interfaces\Data_Provider;
use Underpin\WordPress\Interfaces\ServiceProvider;

class Plugin_Builder_Provider implements Data_Provider {

	public function __construct( protected ServiceProvider $builder ) {
	}

	public function get_builder(): ServiceProvider {
		return $this->builder;
	}

}