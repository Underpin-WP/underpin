<?php

namespace Underpin\Registries;


use Underpin\Factories\Log_Item;
use Underpin\Interfaces\Feature_Extension;
use Underpin\Interfaces\With_Middleware;

class Loader extends Immutable_Collection implements With_Middleware, Feature_Extension {

	function do_actions(): void {
		$this->query()->instance_of( Feature_Extension::class )->get_results()->each( function ( Feature_Extension $extension, string $key ) {
			$extension->do_actions();
			Logger::log(
				'debug',
				new Log_Item(
					code   : 'middleware_actions_ran',
					message: 'The middleware actions for a registry item ran.',
					ref    : $key
				)
			);
		} );
	}

	function do_middleware_actions(): void {
		$this->query()->instance_of( With_Middleware::class )->get_results()->each( function ( With_Middleware $middleware, string $key ) {
			$middleware->do_middleware_actions();
			Logger::log(
				'debug',
				new Log_Item(
					code   : 'loader_actions_ran',
					message: 'The actions for a registry item ran.',
					ref    : $key
				)
			);
		} );
	}

}