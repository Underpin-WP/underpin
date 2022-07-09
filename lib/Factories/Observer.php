<?php


namespace Underpin\Factories;


use Closure;
use Underpin\Interfaces\Data_Provider;

class Observer implements \Underpin\Interfaces\Observer {

	protected array $deps = [];

	public function __construct(
		protected string  $id,
		protected Closure $callback,
		protected int     $priority = 10
	) {
	}

	public function get_priority(): int {
		return $this->priority;
	}

	public function get_dependencies(): array {
		return $this->deps;
	}

	public function add_dependency( string $dependency_id ): static {
		$this->deps[] = $dependency_id;
		return $this;
	}

	public function remove_dependency( string $dependency_id ): static {
		foreach ( $this->deps as $key => $dep ) {
			if ( $dep === $dependency_id ) {
				unset( $this->deps[ $key ] );
			}
		}

		return $this;
	}

	public function update( $instance, ?Data_Provider $args ): void {
		call_user_func( $this->callback, $instance, $args );
	}

	public function get_id(): string {
		return $this->id;
	}

}