<?php

namespace Underpin\Abstracts;

abstract class Builder {
	protected array $args = [];

	protected function set_bool( string $key, bool $value ): static {
		$this->args[ $key ] = $value;

		return $this;
	}

	protected function set_string( string $key, string $value ): static {
		$this->args[ $key ] = $value;

		return $this;
	}

	protected function set_array( string $key, array $values ): static {
		$this->args[$key] = $values;

		return $this;
	}

	protected function set_int( string $key, int $value ): static {
		$this->args[ $key ] = $value;

		return $this;
	}

	protected function set_varidic( string $single_key, string $multi_key, array $values ): static {
		if ( count( $values ) === 1 ) {
			$this->args[$single_key] = $values[0];
		} else {
			$this->args[$multi_key] = $values;
		}

		return $this;
	}
}