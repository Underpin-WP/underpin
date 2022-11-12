<?php

namespace Underpin\Registries;

use Underpin\Exceptions\Operation_Failed;

class Immutable_Collection extends Mutable_Collection {

	protected bool $mutable = true;

	public function seed( array $items ): static {
		$this->mutable   = true;
		$result          = parent::seed( $items );
		$result->mutable = false;
		$this->mutable   = false;

		return $result;
	}

	public function lock(): static {
		$this->mutable = false;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function add( string $key, mixed $value ): static {
		if ( ! $this->mutable ) {
			throw new Operation_Failed( 'This collection is immutable. More items cannot be added to it.', 403, 'error', ref: 'class', data: [ static::class ] );
		}

		return parent::add( $key, $value );
	}

}