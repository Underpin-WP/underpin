<?php

namespace Underpin\Factories;

use Underpin\Enums\Types;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Exceptions\Url_Exception;
use Underpin\Factories\Registry_Items\Param;
use Underpin\Helpers\Processors\Array_Processor;
use Underpin\Helpers\String_Helper;
use Underpin\Interfaces\Can_Convert_To_Array;
use Underpin\Interfaces\Can_Convert_To_String;
use Underpin\Registries\Param_Registry;

class Url implements Can_Convert_To_String, Can_Convert_To_Array {

	protected Param_Registry $params;
	protected ?string        $path;
	protected string             $protocol;
	protected string             $host;
	protected ?int               $port;
	protected string             $fragment;

	public function __construct() {
		$this->params = new Param_Registry;
	}

	/**
	 * @param string  $url
	 * @param Types[] $param_signature
	 *
	 * @return static
	 * @throws Url_Exception
	 */
	public static function from( string $url, array $param_signature = [] ): static {
		$pieces = parse_url( $url );

		if ( ! is_array( $pieces ) ) {
			throw new Url_Exception( message: 'Failed to create URL', data: [ 'url' => $pieces ] );
		}

		$instance = new static;

		if ( isset( $pieces['scheme'] ) ) {
			$instance->set_protocol( $pieces['scheme'] );
		}

		if ( isset( $pieces['host'] ) ) {
			$instance->set_host( $pieces['host'] );
		}

		if ( isset( $pieces['port'] ) ) {
			$instance->set_port( $pieces['port'] );
		}

		if ( isset( $pieces['path'] ) ) {
			$instance->set_path( $pieces['path'] );
		}

		if ( isset( $pieces['query'] ) ) {
			$instance->get_params()->from_string( $pieces['query'], $param_signature );
		}

		if ( isset( $pieces['fragment'] ) ) {
			$instance->set_fragment( $pieces['fragment'] );
		}

		return $instance;
	}

	/**
	 * Magic Method. Allows casting of object using (string)
	 *
	 * @return string
	 */
	public function __toString(): string {
		return $this->to_string();
	}

	/**
	 * Gets the URL path. Example: /path/to/location
	 *
	 * @return string
	 */
	public function get_path(): string {
		return $this->path;
	}

	/**
	 * Sets the URL path.
	 *
	 * @param string $path The path to set.
	 *
	 * @return $this
	 */
	public function set_path( string $path ): static {
		$this->path = String_Helper::prepend( $path, '/' );

		return $this;
	}

	/**
	 * Gets the URL protocol, without the trailing ://. Examples: http, https
	 *
	 * @return string
	 */
	public function get_protocol(): string {
		return $this->protocol;
	}

	/**
	 * Sets the protocol. Only allows lowercase characters per standard.
	 *
	 * @param string $protocol
	 *
	 * @return $this
	 */
	public function set_protocol( string $protocol ): static {
		$this->protocol = preg_replace( '/[^a-z]/', '', $protocol );

		return $this;
	}

	/**
	 * Gets the host. Example: www.domain.com
	 *
	 * @return string
	 */
	public function get_host(): string {
		return $this->host;
	}

	/**
	 * Sets the host.
	 *
	 * @param string $host
	 *
	 * @return $this
	 */
	public function set_host( string $host ): static {
		$this->host = str_replace( '/', '', $host );

		return $this;
	}

	/**
	 * Sets the port.
	 *
	 * @param int $port
	 *
	 * @return $this
	 */
	public function set_port( int $port ): static {
		$this->port = $port;

		return $this;
	}

	/**
	 * Gets the port.
	 *
	 * @return int|null
	 */
	public function get_port(): ?int {
		return $this->port;
	}

	/**
	 * Gets the URL fragment, without the leading '#'
	 *
	 * @return string|null
	 */
	public function get_fragment(): ?string {
		return $this->fragment;
	}

	/**
	 * Sets the fragment.
	 *
	 * @param string $fragment
	 *
	 * @return $this
	 */
	public function set_fragment( string $fragment ): static {
		$this->fragment = str_replace( '#', '', $fragment );

		return $this;
	}

	/**
	 * Gets the URL param registry object.
	 *
	 * @return Param_Registry
	 */
	public function get_params(): Param_Registry {
		return $this->params;
	}

	/**
	 * Adds a param to the URL
	 *
	 * @param Param $param
	 *
	 * @return $this
	 * @throws Operation_Failed
	 */
	public function add_param( Param $param ): static {
		try {
			$this->params->add( $param->get_id(), $param );
		} catch ( Unknown_Registry_Item|Invalid_Registry_Item $e ) {
			throw new Operation_Failed( 'Could not add URL param', previous: $e );
		}

		return $this;
	}

	/**
	 * @param string $key
	 *
	 * @return $this
	 * @throws Operation_Failed
	 */
	public function remove_param( string $key ): static {
		$this->params->remove( $key );

		return $this;
	}

	/**
	 * Converts this URL object to a string.
	 *
	 * @return string
	 */
	public function to_string(): string {
		return (string) ( new Array_Processor( $this->to_array() ) )->where_not_null()->set_separator( '' );
	}

	/**
	 * Converts URL parts to an implode-ready array
	 *
	 * @return array
	 */
	public function to_array(): array {
		return [
			'protocol' => $this->get_protocol() . '://',
			'host'     => $this->get_host(),
			'port'     => $this->get_port() ? ':' . $this->get_port() : null,
			'path'     => $this->get_path(),
			'params'   => ! empty( $this->get_params()->to_array() ) ? '?' . $this->get_params() : null,
			'fragment' => '#' . $this->get_fragment(),
		];
	}

}
