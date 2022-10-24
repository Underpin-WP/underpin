<?php

namespace Underpin\Interfaces;

interface Identifiable_Int extends Identifiable {
	/**
	 * @return int
	 */
	public function get_id(): int;

	public function set_id( int $id ): static;
}