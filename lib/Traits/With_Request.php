<?php

namespace Underpin\Traits;


use Underpin\Factories\Request;

trait With_Request {

	protected Request $request;

	public function get_request(): Request{
		return $this->request;
	}

	public function set_request( Request $request ): static{
		$this->request = $request;

		return $this;
	}

}