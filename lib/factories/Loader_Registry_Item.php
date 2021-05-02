<?php

namespace Underpin\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Loader_Registry_Item extends \Underpin\Abstracts\Registries\Loader_Registry {

	private $default_items;

	public function __construct( $abstraction_class, $default_items = [] ) {
		$this->abstraction_class = $abstraction_class;
		$this->default_items     = $default_items;
		parent::__construct();
	}

	protected function set_default_items() {
		foreach ( $this->default_items as $key => $value ) {
			$this->add( $key, $value );
		}
	}

}