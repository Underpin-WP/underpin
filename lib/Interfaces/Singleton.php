<?php
namespace Underpin\Interfaces;

use Underpin\Exceptions\Instance_Not_Ready;

interface Singleton {

	/**
	 * @return static
	 * @throws Instance_Not_Ready
	 */
	public static function instance() : static;

}