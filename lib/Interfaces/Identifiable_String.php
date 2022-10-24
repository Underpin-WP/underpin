<?php

namespace Underpin\Interfaces;

interface Identifiable_String extends Identifiable {
	/**
	 * @return string
	 */
	public function get_id(): string;

	public function set_id( string $id ): static;
}