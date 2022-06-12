<?php

namespace Underpin\Abstracts;

use Underpin\Loaders\Logger;


abstract class Exception extends \Exception {

	public function __construct( $message, $code = 0, $type = 'error', $data = [], $previous = null ) {
		parent::__construct( $message, $code, $previous );
		Logger::log_exception( $type, $this, $data );
	}
}