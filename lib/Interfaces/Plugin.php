<?php

namespace Underpin\Interfaces;

interface Plugin
{
	public function get_provider() : Provider;

	public function get_builder() : Plugin_Builder;
}