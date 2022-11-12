<?php

namespace Underpin\Factories;

use Underpin\Interfaces\Can_Convert_To_String;
use Underpin\Interfaces\Identifiable;

class Html_Attribute implements Identifiable, Can_Convert_To_String {

	protected string $value;

	public function __construct( protected string $id ) {

	}

	public function get_value(): string {
		return $this->value ?? '';
	}

	public function set_value( string $value ): static {
		$this->value = $value;

		return $this;
	}

	public function get_id(): string|int {
		return $this->id;
	}

	public function to_string(): string {
		return $this->get_value() ? $this->get_id() . '="' . $this->get_value() . '"' : $this->get_id();
	}

	public function __toString(): string {
		return $this->to_string();
	}

}