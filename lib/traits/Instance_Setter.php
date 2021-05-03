<?php

namespace Underpin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Instance_Setter {

	protected function set_values( &$args ) {
		// Override default params.
		foreach ( $args as $arg => $value ) {
			if ( property_exists( $this, $arg ) ) {
				$this->$arg = $value;
				unset( $args[ $arg ] );
			}
		}
	}

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