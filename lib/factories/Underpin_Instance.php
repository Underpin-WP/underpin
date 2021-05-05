<?php

namespace Underpin\Factories;

use Underpin\Abstracts\Underpin;
use Underpin\Traits\Instance_Setter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Underpin_Instance extends Underpin {

	use Instance_Setter;

	/**
	 * The callback to fire in setup.
	 *
	 * @since 1.2.0
	 *
	 * @var callable The callback.
	 */
	protected $setup_callback;

	/**
	 * Underpin_Instance constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param array $args Arguments to set in this class
	 */
	public function __construct( $args = [] ) {
		$this->set_values( $args );
	}

	protected function _setup() {
		$this->set_callable( $this->setup_callback, $this );
	}

}