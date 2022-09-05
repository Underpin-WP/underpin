<?php

namespace Underpin\Registries;

use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Enums\Types;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Exceptions\Url_Exception;
use Underpin\Factories\Registry_Items\Url_Param;
use Underpin\Helpers\Processors\Array_Processor;
use Underpin\Interfaces\Can_Convert_To_String;
use Underpin\Interfaces\Can_Remove;
use Underpin\Traits\Removable_Registry_Item;

class Url_Param_Registry extends Object_Registry implements Can_Remove, Can_Convert_To_String {

	use Removable_Registry_Item;

	protected string $abstraction_class = Url_Param::class;

	/**
	 * @param string $key
	 *
	 * @return Url_Param
	 * @throws Unknown_Registry_Item
	 */
	public function get( string $key ): Url_Param {
		return parent::get( $key );
	}

	/**
	 * Creates a URL object from a url string.
	 *
	 * @param string  $query           The query string to parse, excluding the ? at the beginning. Example:
	 *                                 foo=bar&bar=baz
	 * @param Types[] $param_signature List of param types keyed by the param value. Used to set the query string value
	 *                                 types. If a query param is in the URL, but not in the signature, from_string will
	 *                                 automatically set the type to string.
	 *
	 * @return static
	 * @throws Url_Exception
	 */
	public function from_string( string $query, array $param_signature = [] ): static {
		$query = explode( '&', $query );
		try {
			foreach ( $query as $key => $value ) {
				if ( isset( $param_signature[ $key ] ) ) {
					$type = $param_signature[ $key ];
					$this->add( $key, ( new Url_Param( $key, $type ) )->set_value( $value ) );
				} else {
					$this->add( $key, ( new Url_Param( $key, Types::String ) )->set_value( $value ) );
				}
			}
		} catch ( Invalid_Registry_Item|Unknown_Registry_Item $exception ) {
			throw new Url_Exception( 'Could not create URL from string', 500, $exception );
		}

		return $this;
	}

	/**
	 * Magic method. Makes it possible to cast this registry with (string)
	 *
	 * @return string
	 */
	public function __toString(): string {
		return $this->to_string();
	}

	/**
	 * Converts the set of URL params into a string.
	 *
	 * @return string
	 */
	public function to_string(): string {
		return (string) ( new Array_Processor( $this->to_array() ) )
			->values()
			->each( fn ( Url_Param $value ) => $value->to_string() )
			->set_separator( '&' );
	}

}