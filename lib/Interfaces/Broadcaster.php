<?php

namespace Underpin\Interfaces;


namespace Underpin\Interfaces;

interface Broadcaster {

	function attach( string $key, Observer $observer ): void;

	function detach( string $key, string $observer_id ): void;

	function broadcast( string $key, ?Data_Provider $args = null ): void;

}