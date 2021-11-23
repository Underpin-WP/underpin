<?php

namespace Underpin\Abstracts;


use WP_Error;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Storage {

	protected $params = [];

	public function __get( $key ) {
		if ( isset( $this->params[ $key ] ) ) {
			return $this->params[ $key ];
		}

		return new WP_Error( 'param_not_set', 'The provided param ' . $key . ' is not set' );
	}

}