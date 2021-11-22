<?php

namespace Underpin\Factories;


use Underpin\Abstracts\Storage;
use Underpin\Loaders\Logger;
use Underpin\Traits\Instance_Setter;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Accumulator extends Storage {
	use Instance_Setter;

	protected $state;
	protected $default;
	protected $valid_callback = '__return_true';
	protected $params;

	public function __construct( array $args ) {
		$this->set_values( $args );
		$this->params = $args;
		$this->reset();
	}

	public function get_state() {
		return $this->state;
	}

	public function reset() {
		$this->update( $this->default );
	}

	public function update( $state ) {
		$valid = $this->is_valid( $state );

		if ( ! is_wp_error( $valid ) ) {
			$this->state = $state;
			return true;
		} else {
			Logger::log_wp_error( 'error', $valid );
			return $valid;
		}
	}

	protected function is_valid( $state ) {
		return $this->set_callable( $this->valid_callback, $state, $this );
	}

	public function __get( $key ) {
		if ( $key === 'state' ) {
			return $this->get_state();
		}

		return parent::__get($key);
	}

}