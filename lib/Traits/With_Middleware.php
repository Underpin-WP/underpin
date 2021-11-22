<?php
/**
 * Feature Extension Trait.
 *
 * @since   1.3.0
 */

namespace Underpin\Traits;

use Underpin\Factories\Observer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature Extension Trait.
 *
 * @since   1.3.0
 */
trait With_Middleware {

	use With_Subject;

	protected $middlewares = [];

	/**
	 * Automatically set to true when middleware has ran.
	 *
	 * @since 1.3.0
	 *
	 * @var bool true if ran, otherwise false.
	 */
	protected $middleware_ran = false;

	private function prepare_middlewares() {
		foreach ( $this->middlewares as $value ) {
			$this->attach( 'middleware', $value );
		}
	}

	/**
	 * Fires the middleware actions if it has not already been ran.
	 *
	 * @since 1.3.0
	 */
	public function do_middleware_actions() {
		$this->prepare_middlewares();
		if ( false === $this->middleware_ran ) {
			$this->notify( 'middleware' );
		}

		$this->middleware_ran = true;
	}

}