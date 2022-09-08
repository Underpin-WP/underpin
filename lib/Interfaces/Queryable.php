<?php

namespace Underpin\Interfaces;


use Underpin\Helpers\Processors\Registry_Query;

interface Queryable extends Can_Convert_To_Array {
	function query(): Registry_Query;
}