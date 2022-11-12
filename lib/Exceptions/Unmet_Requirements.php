<?php

namespace Underpin\Exceptions;


class Unmet_Requirements extends Exception {

	/**
	 * @param array $unmet_expected A list of required versions keyed by the unmet requirement type.
	 * @param int   $code           Error code.
	 * @param       $previous       Exception previous exception
	 */
	public function __construct( public readonly array $unmet_expected, int $code = 0, $previous = null ) {
		parent::__construct( "This site does not meet the minimum requirements.", $code, 'error', $previous, null, $unmet_expected );
	}

}