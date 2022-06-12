<?php

namespace Underpin\Abstracts;


use Underpin\Helpers\Array_Helper;
use Underpin\Helpers\Processors\Array_Processor;

abstract class Observer implements \Underpin\Interfaces\Observer {

	protected array  $dependencies = [];

	public function __construct( protected string $id, protected int $priority = 10 ) {
	}

	/**
	 * @inheritDoc
	 */
	public function get_id(): string {
		return $this->id;
	}

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