<?php

namespace Underpin\Factories\Observers;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Loader extends \Underpin\Abstracts\Observer {

	public function __construct( $key, $args ) {
		$this->key  = $key;
		$this->args = $args;
		parent::__construct( $key . '_loader' );
	}

	public function update( $instance, \Underpin\Abstracts\Storage $args ) {
		$instance->loaders()->add( $this->key, $this->args );
	}

}