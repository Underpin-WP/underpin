<?php

namespace Underpin\Interfaces;


interface With_Middleware {

	/**
	 * Fires the middleware actions if it has not already been ran.
	 *
	 */
	function do_middleware_actions(): void;
}