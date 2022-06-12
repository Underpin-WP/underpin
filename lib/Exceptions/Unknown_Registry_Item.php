<?php

namespace Underpin\Exceptions;

use Underpin\Abstracts\Exception;


class Unknown_Registry_Item extends Exception {

	public function __construct( $item, $registry, $type = 'error' ) {
		parent::__construct( message: "Could not find registry item $item in registry $registry", type: $type );
	}

}