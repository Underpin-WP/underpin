<?php

namespace Underpin\Interfaces;


use Underpin\Helpers\Processors\List_Filter;

interface Queryable extends Can_Convert_To_Array {
	function query(): List_Filter;
	function get(string $key): mixed;
}