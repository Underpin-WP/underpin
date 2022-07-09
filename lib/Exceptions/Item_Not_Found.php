<?php

namespace Underpin\Exceptions;


class Item_Not_Found extends Exception {

	public function __construct( string $item, $type = null, $previous = null ) {
		parent::__construct( message: "Could not find item $item", code: $type, type: $type, previous: $previous, ref: null );
	}

}