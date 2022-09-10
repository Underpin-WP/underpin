<?php

namespace Underpin\Factories;

use Underpin\Helpers\String_Helper;
use Underpin\Interfaces\Can_Convert_To_String;

class Header implements Can_Convert_To_String {

	protected mixed $value;

	public function __construct( protected string $key ) {

	}

	/**
	 * Creates a new instance from a header string.
	 *
	 * @param string $header
	 *
	 * @return Header
	 */
	public static function from_string( string $header ): Header {
		$id    = String_Helper::before( $header, ':' );
		$value = trim( String_Helper::after( $header, ':' ) );

		return ( new static( $id ) )->set_value( $value );
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
	public function get_key(): string {
		return $this->key;
	}

	public function to_string(): string {
		return $this->get_key() . ': ' . $this->get_value();
	}

	public function __toString(): string {
		return $this->to_string();
	}

}
