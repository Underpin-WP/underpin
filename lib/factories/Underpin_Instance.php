<?php

namespace Underpin\Factories;

use Underpin\Abstracts\Underpin;
use Underpin\Traits\Instance_Setter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Underpin_Instance extends Underpin {

	use Instance_Setter;

	protected $setup_callback;

	public function __construct( $args = [] ) {
		$this->set_values( $args );
	}

	protected function _setup() {
		$this->set_callable( $this->setup_callback, $this );
	}

}