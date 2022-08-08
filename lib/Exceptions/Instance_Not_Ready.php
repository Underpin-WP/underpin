<?php

namespace Underpin\Exceptions;

class Instance_Not_Ready extends Exception {

	public function __construct( string $message, ?string $type = 'error' ) {
		parent::__construct( message: 'The instance is not ready, and cannot be accessed at this time.', type: $type );
	}

}