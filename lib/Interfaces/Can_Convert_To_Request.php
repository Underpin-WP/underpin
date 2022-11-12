<?php

namespace Underpin\Interfaces;

use Underpin\Factories\Request;

interface Can_Convert_To_Request {

	function to_request(): Request;

}