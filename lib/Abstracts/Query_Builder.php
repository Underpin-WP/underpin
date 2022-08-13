<?php

namespace Underpin\Abstracts;

use Underpin\Interfaces\Can_Convert_To_Instance;
use Underpin\Interfaces\Query;

abstract class Query_Builder extends Builder implements Can_Convert_To_Instance {

	public function to_instance(): Query {

	}
}