<?php

namespace Underpin\Factories;

use Underpin\Abstracts\Request_Middleware;
use Underpin\Interfaces\Can_Convert_To_String;
use Underpin\Interfaces\Identifiable;
use Underpin\Interfaces\Item_With_Dependencies;
use Underpin\Interfaces\With_Middleware;
use Underpin\Registries\Mutable_Collection;
use Underpin\Traits\With_Dependencies;

class Head_Tag implements Identifiable, With_Middleware, Item_With_Dependencies, Can_Convert_To_String {

	use With_Dependencies;

	private Request            $request;
	protected bool             $middleware_ran = false;
	private Mutable_Collection $middleware;

	public function __construct( protected string $id, protected Tag $tag, protected int $priority = 10 ) {
		$this->middleware = Mutable_Collection::make( Request_Middleware::class );
	}

	public function add_middleware( Request_Middleware $middleware ): static {
		$this->middleware->add( $middleware->get_id(), $middleware );

		return $this;
	}

	public function set_request( Request $request ): static {
		$this->request = $request;

		return $this;
	}

	public function get_request(): Request {
		return $this->request;
	}

	public function get_id(): string|int {
		return $this->id;
	}

	public function get_tag(): Tag {
		return $this->tag;
	}

	/**
	 * @return void
	 * @throws Middleware_Exception
	 */
	public function do_middleware_actions(): void {
		//TODO: RENAME REST MIDDLEWARE TO SOMETHING MORE-GENERIC
		$this->middleware->each( function ( Request_Middleware $middleware ) {
			$middleware->run( $this->request );
		} );
	}

	public function to_string(): string {
		return $this->get_tag()->to_string();
	}

	public function __toString(): string {
		return $this->to_string();
	}

}