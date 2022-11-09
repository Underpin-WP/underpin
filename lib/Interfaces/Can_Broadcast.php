<?php

namespace Underpin\Interfaces;


namespace Underpin\Interfaces;

interface Can_Broadcast {

	function attach( string $key, Observer $observer ): static;

	function detach( string $key, string $observer_id ): static;

}