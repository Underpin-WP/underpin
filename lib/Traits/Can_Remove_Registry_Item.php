<?php

namespace Underpin\Traits;

use Underpin\Exceptions\Operation_Failed;

trait Can_Remove_Registry_Item {

	protected array $storage = [];

	/**
	 * @param string $key
	 *
	 * @return $this
	 * @throws Operation_Failed
	 */
	public function remove( string $key ): static {
		if ( ! isset( $this->storage[ $key ] ) ) {
			throw new Operation_Failed( 'Could not remove, item does not exist.' );
		}

		unset( $this->storage[ $key ] );

		return $this;
	}

}