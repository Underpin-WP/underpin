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

	/**
	 * Sets a limit on how many records will be received
	 *
	 * @param int $count
	 *
	 * @return $this
	 */
	public function set_limit( int $count ): static;

	/**
	 * Gets the limit on how many records will be received
	 *
	 * @return int
	 */
	public function get_limit(): int;
}