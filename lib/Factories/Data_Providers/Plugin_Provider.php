<?php

namespace Underpin\Factories\Data_Providers;


use Underpin\Interfaces\Data_Provider;
use Underpin\Interfaces\Plugin;

class Plugin_Provider implements Data_Provider {

	public function __construct( protected Plugin $plugin ) {
	}

	/**
	 * Gets the plugin.
	 *
	 * @return Plugin
	 */
	public function get_plugin(): Plugin {
		return $this->plugin;
	}

}