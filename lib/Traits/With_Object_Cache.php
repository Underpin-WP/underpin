<?php

namespace Underpin\Traits;


trait With_Object_Cache {

	protected array $object_cache;

	/**
	 * Fetches the item from the cache
	 */
	protected function load_from_cache( string $key, callable $setter ) {
		if ( ! isset( $this->object_cache[ $key ] ) ) {
			$this->object_cache[ $key ] = $setter();
		}

		return $this->object_cache[$key];
	}

}