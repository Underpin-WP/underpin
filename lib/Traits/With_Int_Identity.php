<?php

namespace Underpin\Traits;

trait With_Int_Identity {

	protected int $id;

	public function get_id(): int|null {
		return $this->id;
	}

	public function set_id( int|null $id ): static {
		$this->id =$id;

		return $this;
	}

}