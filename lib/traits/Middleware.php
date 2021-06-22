<?php
/**
 * Feature Extension Trait.
 *
 * @since   1.3.0
 */

namespace Underpin\Traits;

use Underpin\Abstracts\Underpin;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature Extension Trait.
 *
 * @since   1.3.0
 */
trait Middleware {

	/**
	 * List of middleware to run when this class is instantiated.
	 *
	 * @since 1.3.0
	 *
	 * @var array List of middleware instance references.
	 */
	protected $middlewares    = [];

	/**
	 * Automatically set to true when middleware has ran.
	 *
	 * @since 1.3.0
	 *
	 * @var bool true if ran, otherwise false.
	 */
	protected $middleware_ran = false;

	/**
	 * Fetches, and sorts middleware instances.
	 *
	 * @since 1.3.0
	 *
	 * @return bool true if sorted, otherwise false.
	 */
	private function prepare_middleware() {
		foreach ( $this->middlewares as $key => $middleware ) {
			$this->get_middleware( $key );
		}
		return usort( $this->middlewares, ( function ( $a, $b ) {
			return $a->priority <= $b->priority ? -1 : 1;
		} ) );
	}

	/**
	 * Gets the middleware by the provided key.
	 *
	 * @since 1.3.0
	 *
	 * @param int $key array key.
	 *
	 * @return \Underpin\Abstracts\Middleware|\WP_Error Instance of Middleware, or WP Error
	 */
	private function get_middleware( $key ) {

		if ( ! isset( $this->middlewares[ $key ] ) ) {
			return new \WP_Error( 'key_not_set', 'The specified key is not set', [
				'key'         => $key,
				'middlewares' => $this->middlewares,
			] );
		}

		if ( is_wp_error( $this->middlewares[ $key ] ) ) {
			return $this->middlewares[ $key ];
		}

		$class = Underpin::make_class( $this->middlewares[ $key ], 'Underpin\Factories\Middleware_Instance' );
		if ( $class instanceof \Underpin\Abstracts\Middleware ) {
			$class->set_loader( $this );
		}
		$this->middlewares[ $key ] = $class;

		return $this->middlewares[ $key ];
	}

	/**
	 * Fires the middleware actions if it has not already been ran.
	 *
	 * @since 1.3.0
	 */
	public function do_middleware_actions() {
		if ( false === $this->middleware_ran ) {
			$this->prepare_middleware();
			foreach ( $this->middlewares as $key => $middleware ) {
				$class = $this->get_middleware( $key );

				if ( $class instanceof \Underpin\Abstracts\Middleware ) {
					$class->do_actions();
				} else {
					underpin()->logger()->log( 'warning', 'middleware_action_failed_to_run', 'Middleware action failed to run. Invalid instance type', [
						'class'   => get_class( $class ),
						'expects' => 'Underpin\Abstracts\Middleware',
					] );
				}
			}
		}

		$this->middleware_ran = true;
	}

	/**
	 *
	 *
	 * @since 1.3.0
	 *
	 * @param $middleware Middleware reference. See Underpin::make_class.
	 */
	public function add_middleware( $middleware ) {
		$this->middlewares[] = $middleware;
	}

}