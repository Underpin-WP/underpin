<?php

namespace Underpin\Helpers\Processors;


use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Traits\Filter_Params;
use Underpin\Traits\Sort_Params;

class Registry_Query {

	use Filter_Params;
	use Sort_Params;

	public function __construct( protected Object_Registry $registry ) {
	}

	/**
	 * @throws Operation_Failed
	 */
	public function get_results(): Object_Registry {
		$filtered_items = List_Filter::seed( $this->registry->to_array(), $this->filter_args )->filter();
		$sorted = List_Sorter::seed($filtered_items, $this->sort_args)->sort();

		try {
			return $this->registry->seed( $sorted );
		}catch(Invalid_Registry_Item $e){
			throw new Operation_Failed('Failed to seed the registry.', previous: $e);
		}
	}

}