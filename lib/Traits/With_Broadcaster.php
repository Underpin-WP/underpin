<?php

namespace Underpin\Traits;


use Underpin\Exceptions\Invalid_Registry_Item;
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


	/**
	 * @throws Unknown_Registry_Item
	 * @throws Invalid_Registry_Item
	 */
	protected function broadcast( UnitEnum $key, ?Data_Provider $args = null ): static {
		$this->get_broadcaster()->broadcast( $key, $args );

		Logger::debug( new Log_Item(
			code   : 'item_broadcasted',
			message: "An item was broadcasted",
			context: 'instance',
			ref    : get_called_class()
		) );

		return $this;
	}

}