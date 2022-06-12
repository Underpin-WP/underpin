<?php

namespace Underpin\Exceptions;

use Underpin\Abstracts\Exception;


class Invalid_Callback extends Exception {

	public function __construct( $callback_value, $type = 'error' ) {
		$value_type = gettype( $callback_value );
		parent::__construct(
			message: "The provided callback is invalid. Expected closure, got $value_type",
			type   : $type
		);
	}

}