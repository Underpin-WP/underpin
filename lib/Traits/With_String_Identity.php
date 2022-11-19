<?php

namespace Underpin\Traits;

trait With_String_Identity {

	protected string $id;

	public function get_id(): ?string {
		return $this->id;
	}

	public function set_id( ?string $id  ): static {
		$this->id =$id;

		return $this;
	}

}