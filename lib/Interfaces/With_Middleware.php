<?php

namespace Underpin\Interfaces;


interface With_Middleware {

	/**
	 * Fires the middleware actions if it has not already been ran.
	 *
	 * @since 2.2.0
	 */
	function do_middleware_actions(): void;

	/**
	 * Returns true if middleware already ran.
	 *
	 * @return bool
	 */
	function middleware_ran(): bool;
}