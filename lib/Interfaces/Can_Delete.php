<?php

namespace Underpin\Interfaces;


use Underpin\Exceptions\Operation_Failed;

interface Can_Delete {
	/**
	 * @param int|string $id
	 *
	 * @return mixed
	 * @throws Operation_Failed
	 */
	public function delete( int|string $id ): bool;

}