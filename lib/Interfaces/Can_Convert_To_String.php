<?php

namespace Underpin\Interfaces;

use \Stringable;

interface Can_Convert_To_String extends Stringable {

	public function to_string(): string;

}
