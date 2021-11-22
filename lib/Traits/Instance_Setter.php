<?php

namespace Underpin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Instance_Setter {

	/**
	 * Loop through each argument, set the value, and remove the value if it was already set.
	 *
	 * @since 1.2.0
	 *
	 * @param array $args Arguments to set, and manipulate.
	 */
	protected function set_values( &$args ) {
		// Override default params.
		foreach ( $args as $arg => $value ) {
			if ( property_exists( $this, $arg ) ) {
				$this->$arg = $value;
				unset( $args[ $arg ] );
			}
		}
	}

	/**
	 * Set a custom callback from the provided argument, and set or arguments.
	 *
	 * @since 1.2.0
	 *
	 * @param callable $callable The callback
	 * @param mixed ...$args The arguments to pass to the callback
	 *
	 * @return false|mixed|\WP_Error
	 */
	protected function set_callable( $callable, ...$args ) {
		if ( is_callable( $callable ) ) {
			return call_user_func( $callable, ...$args );
		}

		return new \WP_Error(
			'invalid_callback',
			'The provided callback is invalid',
			[
				'callback' => $callable,
				'stack'    => debug_backtrace(),
			]
		);
	}

}