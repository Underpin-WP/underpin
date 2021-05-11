<?php

namespace Underpin\Factories;

use Underpin\Traits\Instance_Setter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Loader_Registry_Item extends \Underpin\Abstracts\Registries\Loader_Registry {
	use Instance_Setter;

	private $default_items = [];

	public function __construct( $args ) {
		$this->set_values($args);
		parent::__construct();
	}

	protected function set_default_items() {
		foreach ( $this->default_items as $key => $value ) {
			$this->add( $key, $value );
		}
	}

}