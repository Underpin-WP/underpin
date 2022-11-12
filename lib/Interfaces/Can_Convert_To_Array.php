<?php

namespace Underpin\Interfaces;


interface Can_Convert_To_Array {

	/**
	 * Converts this object into an array.
	 *
	 * @return array
	 */
	public function to_array(): array;

}