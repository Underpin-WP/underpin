<?php

namespace Underpin\Interfaces;


namespace Underpin\Interfaces;

use UnitEnum;

interface Broadcaster {

	function attach( UnitEnum $key, Observer $observer ): static;

	function detach( UnitEnum $key, string $observer_id ): static;

}