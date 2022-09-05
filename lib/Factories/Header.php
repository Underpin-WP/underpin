<?php

namespace Underpin\Factories;

use Underpin\Interfaces\Identifiable;

class Header implements Identifiable {

	protected mixed $value;

	public function __construct( protected string $id ) {

	}

	/**
	 * Gets the header value.
	 *
	 * @return mixed
	 */
	public function get_value(): mixed {
		return $this->value;
	}

	/**
	 * Sets the header value.
	 *
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function set_value( mixed $value ): static {
		$this->value = $value;

		return $this;
	}

	/**
	 * Gets the header key.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

}
