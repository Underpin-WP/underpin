<?php

namespace Underpin\Exceptions;


class Plugin_Already_Registered extends Exception {

	public function __construct( string $plugin_id, int $code = 0, $previous = null ) {
		parent::__construct( 'The specified plugin is already registered, and cannot be registered again.', $code, 'error', $previous, null, [ 'plugin_id' => $plugin_id ] );
	}

}