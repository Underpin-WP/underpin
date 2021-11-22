<?php

namespace Underpin\Interfaces;

interface Item_With_Dependencies{

	function get_dependencies();

	function get_id();

	function get_priority();

	function add_dependency( string $dependency_id );

	function remove_dependency( string $dependency_id );
}