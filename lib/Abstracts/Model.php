<?php

namespace Underpin\Abstracts;

use Underpin\Exceptions\Item_Not_Found;
use Underpin\Interfaces\Data_Store;

abstract class Model implements \Underpin\Interfaces\Model {

	protected string|int $id;

	public function __construct( protected Data_Store $data_store ) {

	}

	/**
	 * @throws Item_Not_Found
	 */
	public function get( int|string $id ): Beer {
		return $this->data_store->read( $id );
	}

	/**
	 * @return string|int
	 */
	public function get_id(): string|int {
		return $this->id;
	}

	public function delete(): static {
		$this->data_store->delete( $this->get_id() );
		$this->set_id( null );

		return $this;
	}

	public function clone(): \Underpin\Interfaces\Model {
		$new = clone $this;
		$new->set_id( null );

		return $new;
	}

	public function save(): static {
		if ( is_null( $this->get_id() ) ) {
			$this->data_store->create( $this );
		} else {
			$this->data_store->update( $this );
		}

		return $this;
	}

	public function set_id( int|string|null $id ): static {
		$this->id = $id;

		return $this;
	}
}