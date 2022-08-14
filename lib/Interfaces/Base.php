<?php

namespace Underpin\Interfaces;

use Underpin\Interfaces\Provider;

interface Base
{
	public function get_provider() : Provider;

	public function get_builder() : Integration_Provider;
}