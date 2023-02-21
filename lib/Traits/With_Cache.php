<?php

namespace Underpin\Traits;

use Underpin\Exceptions\Cached_Item_Not_Found;
use Underpin\Exceptions\Cache_Store_Failed;
use Underpin\Interfaces\Cache_Strategy;
use Underpin\Registries\Logger;

trait With_Cache
{
	protected array $object_cache = [];

	/**
	 * @param string          $key      The cache key
	 * @param callable        $setter   The setter that sets the cache value
	 * @param ?Cache_Strategy $strategy The strategy to use when loading from the cache. If unset, this will default to
	 *                                  Underpin's object cache strategy.
	 *
	 * @return mixed
	 */
	protected function load_from_cache(string $key, callable $setter, ?Cache_Strategy $strategy = null) : mixed
	{
		if (! $strategy) {
			return $this->load_from_object($key, $setter);
		}

		return $this->load_from_strategy($key, $setter, $strategy);
	}

	/**
	 * Loads the data using the provided strategy
	 *
	 * @param string         $key       The cache key
	 * @param callable       $setter    The setter that sets the cache value
	 * @param Cache_Strategy $strategy  The strategy to use when loading from the cache. If unset, this will default to
	 *                                  Underpin's object cache strategy.
	 *
	 * @return mixed
	 */
	private function load_from_strategy(string $key, callable $setter, Cache_Strategy $strategy) : mixed
	{
		try {
			$result = $strategy->get($key);
		} catch (Cached_Item_Not_Found $e) {
			$result = $setter();
			$this->set_cache($key, $result, $strategy);
		}

		return $result;
	}

	/**
	 * Stores the data in the provided cache strategy
	 *
	 * @param string         $key       The cache key
	 * @param mixed          $value     The setter that sets the cache value
	 * @param Cache_Strategy $strategy  The strategy to use when loading from the cache. If unset, this will default to
	 *                                  Underpin's object cache strategy.
	 *
	 * @return void
	 */
	private function set_cache(string $key, mixed $value, Cache_Strategy $strategy) : void
	{
		try {
			$strategy->set($key, $value);
		} catch (Cache_Store_Failed $e) {
			Logger::warning($e);
		}
	}

	/**
	 * Loads an item from the object cache.
	 *
	 * @param string   $key
	 * @param callable $setter
	 *
	 * @return mixed
	 */
	private function load_from_object(string $key, callable $setter) : mixed
	{
		if (! isset($this->object_cache[$key])) {
			$this->object_cache[$key] = $setter();
		}

		return $this->object_cache[$key];
	}
}