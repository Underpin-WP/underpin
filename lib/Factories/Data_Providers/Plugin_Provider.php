<?php

namespace Underpin\Factories\Data_Providers;


use Underpin\Interfaces\Data_Provider;
use Underpin\WordPress\Interfaces\Base;

class Plugin_Provider implements Data_Provider {

	public function __construct( protected Base $plugin ) {
	}

	/**
	 * Gets the plugin.
	 *
	 * @return Base
	 */
	public function get_plugin(): Base {
		return $this->plugin;
	}

}