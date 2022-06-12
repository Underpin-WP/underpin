<?php

namespace Underpin\Factories\Observers;


use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Data_Providers\Plugin_Builder_Provider;
use Underpin\Interfaces\Data_Provider;
use Underpin\Abstracts\Observer;

class Loader extends Observer {

	public function __construct( protected string $key, protected string|array|object $instance, protected int $priority = 10 ) {
		parent::__construct( $key, $this->priority );
		$this->id = $key . '_loader';
	}

	/**
	 * @throws Unknown_Registry_Item
	 * @throws Invalid_Registry_Item
	 */
	public function update( $instance, ?Data_Provider $provider ): void {
		if ( $provider instanceof Plugin_Builder_Provider ) {
			$provider->get_builder()->loaders()->add( $this->key, $this->instance );
		}
	}

}