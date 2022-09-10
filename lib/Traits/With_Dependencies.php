<?php

namespace Underpin\Traits;


use Underpin\Helpers\Array_Helper;
use Underpin\Helpers\Processors\Array_Processor;

trait With_Dependencies {

	protected array  $dependencies = [];
	protected int $priority;

	/**
	 * @inheritDoc
	 */
	public function get_priority(): int {
		return $this->priority;
	}

	/**
	 * @inheritDoc
	 */
	public function get_dependencies(): array {
		return $this->dependencies;
	}

	/**
	 * @inheritDoc
	 */
	public function add_dependency( string $dependency_id ): static {
		Array_Helper::append( $this->dependencies, $dependency_id );

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function remove_dependency( string $dependency_id ): static {
		$this->dependencies = ( new Array_Processor( $this->dependencies ) )
			->flip()
			->remove( $dependency_id )
			->flip()
			->to_array();

		return $this;
	}

}