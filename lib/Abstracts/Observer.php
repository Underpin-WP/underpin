<?php

namespace Underpin\Abstracts;


use Underpin\Interfaces\Item_With_Dependencies;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Observer implements Item_With_Dependencies {

	protected $id;

	public    $name        = '';
	public    $description = '';
	protected $priority    = 10;

	protected $deps = [];

	public function __construct( $id ) {
		$this->id = $id;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_priority() {
		return $this->priority;
	}

	public function get_dependencies() {
		return $this->deps;
	}

	public function add_dependency( string $dependency_id ) {
		return $this->deps[] = $dependency_id;
	}

	public function remove_dependency( string $dependency_id ) {
		foreach ( $this->deps as $key => $dep ) {
			if ( $dep === $dependency_id ) {
				unset( $this->deps[ $key ] );
			}
		}
	}

	abstract public function update( $instance, Storage $args );

	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_Error( 'observer_param_not_set', 'The observer value for ' . $key . ' could not be found.' );
		}
	}

}