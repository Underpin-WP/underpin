<?php
/**
 * Feature Extension Trait.
 *
 * @since   1.3.0
 */

namespace Underpin\Traits;

use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;

/**
 * Feature Extension Trait.
 *
 * @since   1.3.0
 */
trait With_Middleware {

	use With_Subject;

	protected array $middlewares = [];

	/**
	 * Automatically set to true when middleware has ran.
	 *
	 * @since 1.3.0
	 *
	 * @var bool true if ran, otherwise false.
	 */
	protected bool $middleware_ran = false;

	/**
	 * Prepares the middleware
	 *
	 * @throws Invalid_Registry_Item|Unknown_Registry_Item
	 */
	private function prepare_middlewares(): void {
		foreach ( $this->middlewares as $value ) {
			$this->attach( 'middleware', $value );
		}
	}

	public function middleware_ran(): bool {
		return $this->middleware_ran;
	}

	/**
	 * Fires the middleware actions if it has not already been ran.
	 *
	 * @since 1.3.0
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	public function do_middleware_actions(): void {
		$this->prepare_middlewares();
		if ( false === $this->middleware_ran() ) {
			$this->notify( 'middleware' );
		}

		$this->middleware_ran = true;
	}

}