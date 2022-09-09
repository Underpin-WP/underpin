<?php

namespace Underpin\Registries;

use Underpin\Traits\Can_Remove_Registry_Item;

class Mutable_Collection_With_Remove extends Mutable_Collection {
	use Can_Remove_Registry_Item;

}