<?php

namespace Underpin\Factories\Data_Providers;


use Closure;
use Underpin\Exceptions\Invalid_Callback;
use Underpin\Interfaces\Data_Provider;


class Accumulator implements Data_Provider {

	protected closure $valid_callback;
	protected mixed   $state = null;

	/**
	 * @throws Invalid_Callback
	 */
	public function __construct( protected mixed $default = null, ?Closure $valid_callback = null, ) {
		$this->valid_callback = $valid_callback ?? fn () => true;
		$this->reset();
	}

	/**
	 * Retrieves the state.
	 *
	 * @return mixed
	 */
	public function get_state(): mixed {
		return $this->state;
	}

	/**
	 * @throws Invalid_Callback
	 */
	public function reset() {
		$this->update( $this->default );
	}

	/**
	 * Updates the accumulator state.
	 *
	 * @param $state
	 *
	 * @return bool
	 * @throws Invalid_Callback
	 */
	public function update( $state ): bool {
		$valid = $this->is_valid( $state );

		if ( true === $valid ) {
			$this->state = $state;
			return true;
		}

		return false;
	}

	/**
	 * Checks to see if the instance is valid.
	 *
	 * @param $state
	 *
	 * @return boolean
	 * @throws Invalid_Callback
	 */
	protected function is_valid( $state ): bool {
		return call_user_func( $this->valid_callback, $state );
	}

}