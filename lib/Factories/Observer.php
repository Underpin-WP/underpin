<?php


namespace Underpin\Factories;


use Closure;
use Underpin\Interfaces\Data_Provider;

class Observer implements \Underpin\Interfaces\Observer {

	public function __construct(
		protected string  $id,
		protected Closure $callback
	) {
	}

	public function update( $instance, ?Data_Provider $args ): void {
		call_user_func( $this->callback, $instance, $args );
	}

	public function get_id(): string {
		return $this->id;
	}

}