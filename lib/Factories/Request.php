<?php

namespace Underpin\Factories;


use Underpin\Enums\Rest;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Registry_Items\Param;
use Underpin\Interfaces\Identifiable;
use Underpin\Registries\Header_Registry;

class Request {

	protected Header_Registry $headers;
	protected string          $path;
	protected string          $host;
	protected Rest            $method;
	protected string          $ip;
	protected Url             $url;
	protected string          $body;
	protected ?Identifiable   $identity = null;

	public function __construct() {
		$this->headers = new Header_Registry;
	}

	/**
	 * Updates this request's identity
	 *
	 * @return ?Identifiable
	 */
	public function get_identity(): ?Identifiable {
		return $this->identity;
	}

	/**
	 * Sets this request's identity
	 *
	 * @param ?Identifiable $id
	 *
	 * @return $this
	 */
	public function set_identity( ?Identifiable $id ): static {
		$this->identity = $id;

		return $this;
	}

	/**
	 * Sets the URL
	 *
	 * @param Url $url The URL object to set.
	 *
	 * @return $this
	 */
	public function set_url( Url $url ): static {
		$this->url = $url;

		return $this;
	}

	/**
	 * Gets the request URL
	 *
	 * @return Url
	 */
	public function get_url(): Url {
		return $this->url;
	}

	/**
	 * Retrieves the list of headers in this request.
	 *
	 * @return Header_Registry
	 */
	public function get_headers(): Header_Registry {
		return $this->headers;
	}

	/**
	 * @param Header $header
	 *
	 * @return $this
	 * @throws Operation_Failed
	 */
	public function set_header( Header $header ): static {
		try {
			$this->get_headers()->add( $header->get_id(), $header );
		} catch ( Unknown_Registry_Item|Invalid_Registry_Item $e ) {
			throw new Operation_Failed( "Could not set header", 500, previous: $e );
		}

		return $this;
	}

	/**
	 * Gets the specified URL param from the URL.
	 *
	 * @param string $key
	 *
	 * @return Param
	 * @throws Unknown_Registry_Item
	 */
	public function get_param( string $key ): Param {
		return $this->get_url()->get_params()->get( $key );
	}

	/**
	 * Sets, or updates the URL param to the specified value.
	 *
	 * @param Param $param
	 *
	 * @return $this
	 * @throws Operation_Failed
	 */
	public function set_param( Param $param ): static {
		if ( $this->get_url()->get_params()->is_registered( $param->get_id() ) ) {
			try {
				$original = $this->get_url()->get_params()->get( $param->get_id() );
			} catch ( Unknown_Registry_Item $e ) {
				throw new Operation_Failed( 'An item was registered, but the registry could not obtain the registered item.', 500, 'error', $e );
			}

			if ( ! $param instanceof $original ) {
				throw new Operation_Failed( 'The param ' . $param->get_id() . ' Is already set, and the new param is not an instance of the newly specified param.', 500, 'error' );
			}

			$original->set_value( $param->get_value() );
		}

		try {
			$this->get_url()->get_params()->add( $param->get_id(), $param );
		} catch ( Invalid_Registry_Item|Unknown_Registry_Item $e ) {
			throw new Operation_Failed( 'Could not set the param.', 500, 'error', $e );
		}

		return $this;
	}

	/**
	 * Removes a header
	 *
	 * @param string $id
	 *
	 * @return $this
	 * @throws Operation_Failed
	 */
	public function remove_header( string $id ): static {
		$this->get_headers()->remove( $id );

		return $this;
	}

	/**
	 * Sets the request method.
	 *
	 * @param Rest $method The method to set.
	 *
	 * @return $this
	 */
	public function set_method( Rest $method ): static {
		$this->method = $method;

		return $this;
	}

	/**
	 * Gets the rest method.
	 *
	 * @return Rest
	 */
	public function get_method(): Rest {
		return $this->method;
	}

	/**
	 * Sets the Request IP address.
	 *
	 * @param string $ip
	 *
	 * @return $this
	 */
	public function set_ip( string $ip ): static {
		$this->ip = $ip;

		return $this;
	}

	/**
	 * Gets the request IP address.
	 *
	 * @return string
	 */
	public function get_ip(): string {
		return $this->ip;
	}

	/**
	 * Sets the request body.
	 *
	 * @param string $body
	 *
	 * @return $this
	 */
	public function set_body( string $body ): static {
		$this->body = $body;

		return $this;
	}

	/**
	 * Gets the request body.
	 *
	 * @return string
	 */
	public function get_body(): string {
		return $this->body;
	}

}
