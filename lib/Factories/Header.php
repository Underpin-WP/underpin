<?php

namespace Underpin\Factories;

use Underpin\Helpers\String_Helper;
use Underpin\Interfaces\Can_Convert_To_String;
use Underpin\Interfaces\Identifiable;

class Header implements Can_Convert_To_String, Identifiable {

	protected ?string $id = null;

	public function __construct( protected string $key, protected string $value ) {

	}

	/**
	 * Gets a string identifier for this header.
	 *
	 * @return string
	 * @throws \Underpin\Exceptions\Operation_Failed
	 */
	public function get_id(): string {
		if ( ! $this->id ) {
			$this->id = String_Helper::create_hash( $this->to_string() );
		}

		return $this->id;
	}

	/**
	 * Creates a new instance from a header string.
	 *
	 * @param string $header
	 *
	 * @return Header
	 */
	public static function from_string( string $header ): Header {
		$key   = String_Helper::before( $header, ':' );
		$value = trim( String_Helper::after( $header, ':' ) );

		return new static( $key, $value );
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
