<?php

namespace Underpin\Interfaces;

use Underpin\Exceptions\Cached_Item_Not_Found;
use Underpin\Exceptions\Cache_Store_Failed;

interface Cache_Strategy
{
	/**
	 * Gets a single item from the cache.
	 *
	 * @param string $key The cache key, used to identify what should be fetched from the cache
	 *
	 * @return mixed
	 * @throws Cached_Item_Not_Found
	 */
	public function get(string $key) : mixed;

	/**
	 * Sets a single item to the cache.
	 *
	 * @param string $key   The cache key, used to identify what should be fetched from the cache when requested.
	 * @param mixed  $value The value to provide when this item is requested.
	 * @throws Cache_Store_Failed
	 */
	public function set(string $key, mixed $value): void;
}