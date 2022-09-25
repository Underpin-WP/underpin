<?php

namespace Underpin\Interfaces;

interface Query {

	/**
	 * Set specific item IDs
	 *
	 * @param string|int ...$ids
	 *
	 * @return static
	 */
	public function set_ids( string|int ...$ids ): static;

	/**
	 * @return Model[]
	 */
	public function get_results(): array;

	/**
	 * @return int
	 */
	public function get_count(): int;

	/**
	 * @return int[]
	 */
	public function get_ids(): array;

}