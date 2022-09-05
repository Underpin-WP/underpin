<?php

namespace Underpin\Registries;


use Underpin\Factories\Header;
use Underpin\Interfaces\Can_Remove;
use Underpin\Traits\Removable_Registry_Item;

class Header_Registry extends Object_Registry implements Can_Remove {

	use Removable_Registry_Item;

	public function __construct() {
		parent::__construct(Header::class);
	}
}
