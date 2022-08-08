<?php

namespace Underpin\Interfaces;

use Underpin\Abstracts\Builder;

interface Model {

	/**
	 * @return Model_Item[]
	 */
	public function get_many(Builder $builder): array;

	public function get_one(string|int $id): Model_Item;

	public function update(Model_Item $item): static;

	/**
	 * @param Model_Item[] $items
	 * @return Model_Item[]
	 */
	public function update_many(array $items): array;

}