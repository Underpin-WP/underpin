<?php

namespace Underpin\Registries;

use Head_Tag;
use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Exceptions\Middleware_Exception;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Factories\Request;
use Underpin\Helpers\Processors\Dependency_Processor;
use Underpin\Interfaces\Can_Remove;
use Underpin\Traits\Can_Remove_Registry_Item;

class Head_Tag_Collection extends Object_Registry implements Can_Remove  {
	use Can_Remove_Registry_Item;

	protected string $abstraction_class = Head_Tag::class;

	protected function include_in_request( Head_Tag $tag, Request $request ): bool {
		$tag->set_request( $request );
		try {
			$tag->do_middleware_actions();

			return true;
		} catch ( Middleware_Exception $e ) {
			return false;
		}
	}

	/**
	 * Filters out tags that should not be included in this request.
	 *
	 * @param Request $request
	 *
	 * @return $this
	 */
	protected function filter_tags( Request $request ): static {
		return $this->filter( fn ( Head_Tag $tag ) => $this->include_in_request( $tag, $request ) );
	}

	/**
	 * Processes the tags, sorting them by dependency.
	 *
	 * @throws Operation_Failed
	 */
	protected function process_tags( Request $request ): array {
		return ( new Dependency_Processor( $this->filter_tags( $request ) ) )->mutate();
	}

	/**
	 * Gets the tags for the current request, sorted by dependency.
	 *
	 * @param Request $request
	 *
	 * @return Head_Tag_Collection
	 * @throws Operation_Failed
	 */
	public function get_request_tags( Request $request ): static {
		return $this->seed( $this->process_tags( $request ) );
	}

}