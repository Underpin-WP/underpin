<?php

namespace Underpin\Factories\Registry_Items;

use Underpin\Enums\Types;
use Underpin\Exceptions\Validation_Failed;
use Underpin\Interfaces\Can_Convert_To_String;
use Underpin\Interfaces\Identifiable;

class Param implements Identifiable, Can_Convert_To_String {

	protected mixed $value;

	public function __construct(
		protected string       $id,
		protected Types        $type,
	) {

	}

	/**
	 * @return Types
	 */
	public function get_type(): Types {
		return $this->type;
	}

	/**
	 * @param mixed $value
	 *
	 * @return $this
	 * @throws Validation_Failed
	 */
	public function set_value( mixed $value ): static {
		if ( $this->validate( $value ) ) {
			$this->value = $value;
		}

		return $this;
	}

	/**
	 * @param $value
	 *
	 * @throws Validation_Failed
	 * @return true
	 */
	protected function validate( $value ): bool {
		if ( gettype( $value ) !== $this->type->value ) {
			throw new Validation_Failed( 'Param ' . $this->get_id() . ' expects ' . $this->type->value . ', ' . gettype( $value ) . ' given.' );
		}

		return true;
	}

	/**
	 * @return mixed
	 */
	public function get_value(): mixed {
		return $this->value;
	}

	/**
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return $this->to_string();
	}

	/**
	 * @return string
	 */
	public function to_string(): string {
		return (string) $this->value;
	}

}