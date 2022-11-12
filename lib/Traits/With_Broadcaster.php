<?php

namespace Underpin\Traits;


use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Broadcaster;
use Underpin\Factories\Log_Item;
use Underpin\Interfaces\Data_Provider;
use Underpin\Registries\Logger;
use UnitEnum;

trait With_Broadcaster {

	protected Broadcaster $broadcaster;

	protected function get_broadcaster(): Broadcaster {
		if ( ! isset( $this->broadcaster ) ) {
			$this->broadcaster = new Broadcaster;
		}

		return $this->broadcaster;
	}

}