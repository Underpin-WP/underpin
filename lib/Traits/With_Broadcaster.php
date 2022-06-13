<?php

namespace Underpin\Traits;


use Underpin\Factories\Broadcaster;

trait With_Broadcaster {

	protected Broadcaster $broadcaster;

	protected function get_broadcaster(): \Underpin\Interfaces\Broadcaster {
		if ( ! isset( $this->broadcaster ) ) {
			$this->broadcaster = new Broadcaster;
		}

		return $this->broadcaster;
	}
}