<?php

namespace Underpin\Registries;


use Underpin\Abstracts\Registries\Loader;

class Rest_API_Loader extends Loader {

	public function __construct( Controller ...$items ) {
		parent::__construct( Controller::class, ...$items );
	}

}