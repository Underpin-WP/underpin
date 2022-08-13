<?php

namespace Underpin\Interfaces;

interface Query {

	/**
	 * @return Model[]
	 */
	public function get_results(): array;

	public function get_count(): int;
}