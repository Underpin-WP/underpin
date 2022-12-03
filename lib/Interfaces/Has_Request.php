<?php

namespace Underpin\Interfaces;

use Underpin\Factories\Request;

interface Has_Request {

	function get_request(): Request;

	function set_request( Request $request ): static;

}
