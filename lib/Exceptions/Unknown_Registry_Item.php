<?php

namespace Underpin\Exceptions;


class Unknown_Registry_Item extends Exception {

	public function __construct( $item, $registry, $type = 'error' ) {
		parent::__construct( message: "Could not find registry item $item in registry $registry", code: 404, type: $type, previous: null, ref: null );
	}

}