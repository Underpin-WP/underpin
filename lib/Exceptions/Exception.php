<?php

namespace Underpin\Exceptions;

use Underpin\Registries\Logger;


class Exception extends \Exception {

	public function __construct( string $message, int $code = 0, ?string $type = 'error', $previous = null, string|int|null $ref = null, $data = [] ) {
		parent::__construct( $message, $code, $previous );
		if ( $type ) {
			Logger::log_exception( $type, $this, $ref, $data );
		}
	}

}