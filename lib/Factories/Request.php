<?php

namespace Underpin\Factories;


use Underpin\Enums\Rest;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Registry_Items\Param;
use Underpin\Helpers\String_Helper;
use Underpin\Interfaces\Identifiable;
use Underpin\Registries\Immutable_Collection;
use Underpin\Registries\Mutable_Collection_With_Remove;

class Request {

	protected Mutable_Collection_With_Remove $headers;
	protected string                         $path;
	protected Rest                           $method;
	protected string                         $ip;
	protected Url                            $url;
	protected string                         $body;
	protected ?Identifiable                  $identity = null;
	protected array                          $flags    = [];
	protected static Request                 $current;

	public function __construct() {
		$this->headers = Mutable_Collection_With_Remove::make( Header::class );
	}

	/**
	 * Gets the current request.
	 *
	 * @return Request
	 */
	public static function current(): Request {
		if ( ! isset( static::$current ) ) {
			static::$current = new static;
		}

		return static::$current;
	}

	/**
	 * Sets a flag on this request.
	 *
	 * @param string $flag
	 *
	 * @return $this
	 */
	public function set_flag( string $flag ): static {
		if ( ! $this->has_flag( $flag ) ) {
			$this->flags[] = $flag;
		}

		return $this;
	}

	/**
	 * Unsets a flag on this request.
	 *
	 * @param string $flag
	 *
	 * @return $this
	 */
	public function unset_flag( string $flag ): static {
		if ( $this->has_flag( $flag ) ) {
			unset( $this->flags[ $flag ] );
		}

		return $this;
	}

	/**
	 * Sets or unsets a flag based on a boolean value
	 *
	 * @param string $flag
	 * @param bool   $binding
	 *
	 * @return $this
	 */
	public function bind_flag( string $flag, bool $binding ): static {
		return $binding ? $this->set_flag( $flag ) : $this->unset_flag( $flag );
	}

	/**
	 * Returns true if this flag is set.
	 *
	 * @param string $flag
	 *
	 * @return bool
	 */
	public function has_flag( string $flag ): bool {
		return in_array( $flag, $this->flags );
	}

	/**
	 * Updates this request's identity
	 *
	 * @return ?Identifiable
	 */
	public function get_identity(): ?Identifiable {
		return $this->identity ?? null;
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
	 * @return Mutable_Collection_With_Remove
	 */
	public function get_headers(): Mutable_Collection_With_Remove {
		return $this->headers;
	}

	/**
	 * Gets all the headers from the specified key.
	 *
	 * It is technically possible for a request to have multiple headers with identical keys
	 * Because of this, we can't simply get a header by the key directly.
	 *
	 * @param string $key
	 *
	 * @return array
	 * @throws Operation_Failed
	 */
	public function get_headers_by_key( string $key ): array {
		return $this->headers->query()->in( 'key', $key )->get_results()->to_array();
	}

	/**
	 * @param Header $header
	 *
	 * @return $this
	 * @throws Operation_Failed
	 */
	public function set_header( Header $header ): static {
		try {
			// Headers are not set based on the key because it's possible to set multiple headers with the same ID
			// If you're trying to find a specific header, you'll need to run a query against get_headers.
			// Super annoying, I know, but hey - I didn't create http headers!
			// @see get_headers_by_key
			$this->get_headers()->add( count( $this->headers->to_array() ), $header );
		} catch ( Operation_Failed|Invalid_Registry_Item $e ) {
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
		} catch ( Operation_Failed $e ) {
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
