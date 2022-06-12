<?php

namespace Underpin\Traits;

use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Object_Registry;

trait With_Loaders {

	/**
	 * @var Object_Registry
	 */
	protected Object_Registry $loader_registry;

	/**
	 * Dynamically calls methods.
	 *
	 * @since 1.2.0
	 *
	 * @param string $method    The method to call
	 * @param array  $arguments The arguments to pass to the method.
	 *
	 * @return mixed
	 * @throws Unknown_Registry_Item
	 */
	function __call( string $method, array $arguments ) {
		// If this method exists, bail and just get the method.
		if ( method_exists( $this, $method ) ) {
			return $this->$method( ...$arguments );
		}

		// Otherwise, try and get the loader.
		return $this->loader_registry->get( $method );
	}

	protected function setup_loaders(): void {
		$this->loader_registry = new Object_Registry( Object_Registry::class );
	}

	/**
	 * Loader registry getter.
	 *
	 * @since 1.2.0
	 *
	 * @return Object_Registry
	 */
	public function loaders(): Object_Registry {
		return $this->loader_registry;
	}

}