<?php

namespace Underpin\Factories;

use Underpin\Traits\Instance_Setter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Object_Registry extends \Underpin\Abstracts\Registries\Object_Registry {

	use Instance_Setter;

	protected $default_items = [];

	public function __construct( $args ) {
		$this->set_values( $args );
		parent::__construct();
	}

	protected function set_default_items() {
		foreach ( $this->default_items as $key => $default_item ) {
			$this->add( $key, $default_item );
		}
	}
}