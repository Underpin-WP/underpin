<?php

namespace Underpin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Instance_Setter {

	protected function set_values( &$args ) {
		// Override default params.
		foreach ( $args as $arg => $value ) {
			if ( isset( $this->$arg ) ) {
				$this->$arg = $value;
				unset( $args[ $arg ] );
			}
		}
	}

}