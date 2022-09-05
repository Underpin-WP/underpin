<?php

namespace Underpin\Abstracts\Registries;


use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Registries\Object_Registry;
use Underpin\Helpers\Array_Helper;
use Underpin\Helpers\Processors\List_Filter;
use Underpin\Interfaces\Queryable;
use Underpin\Interfaces\Loader_Item;

abstract class Loader implements Queryable {

	protected Object_Registry $registry;

	/**
	 * @throws Unknown_Registry_Item
	 * @throws Invalid_Registry_Item
	 */
	public function __construct( $abstraction_class, Loader_Item ...$loader_item ) {
		$this->registry = new Object_Registry( $abstraction_class );
		foreach ( Array_Helper::after(func_get_args(), 1) as $loader_item ) {
			/* @var Loader_Item $loader_item */
			$this->registry->add( $loader_item->get_id(), $loader_item );
		}
	}

	/**
	 * @return Loader_Item
	 * @throws Unknown_Registry_Item
	 */
	public function get( string $key ): mixed {
		return $this->registry->get( $key );
	}

	public function to_array(): array {
		return $this->registry->to_array();
	}

	function query(): List_Filter {
		return $this->registry->query();
	}

}