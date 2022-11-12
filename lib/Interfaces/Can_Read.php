<?php

namespace Underpin\Interfaces;


use Underpin\Exceptions\Item_Not_Found;

interface Can_Read {

	/**
	 * @param int|string $id
	 *
	 * @return mixed
	 * @throws Item_Not_Found
	 */
	public function read( int|string $id ): Model;

}