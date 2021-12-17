<?php

namespace Underpin\Factories;

use Underpin\Abstracts\Underpin;
use Underpin\Interfaces\Item_With_Dependencies;
use Underpin\Loaders\Logger;


class Dependency_Processor {

	/**
	 * @var Object_Registry List of items
	 */
	private $items;

	public function __construct( \Underpin\Abstracts\Registries\Object_Registry $items ) {
		$this->items = $items;
	}

	private function get_dependencies( Item_With_Dependencies $item ) {
		$deps = $item->get_dependencies();
		foreach ( $item->get_dependencies() as $dep ) {
			$dep_item = $this->items->find( [ 'id' => $dep ] );
			if ( ! is_wp_error( $dep_item ) ) {
				/** @var Item_With_Dependencies $dep_item */
				$deps = array_merge( $deps, $this->get_dependencies( $dep_item ) );
			}
		}

		return $deps;
	}

	public function filter_dependencies() {
		$dependency_ids = wp_list_pluck( (array) $this->items, 'id' );
		$queue          = (array) $this->items;
		$items          = [];
		$queued_deps    = [];

		while ( ! empty( $queue ) ) {
			/* @var Item_With_Dependencies $item */
			$item = $queue[0];

			// If this item depends on something that doesn't exist, skip it.
			$unmet_dependencies = array_diff( $this->get_dependencies( $item ), $dependency_ids );

			if ( ! empty( $unmet_dependencies ) ) {
				Logger::log(
					'debug',
					'observer_detached',
					'An event was detached because it has unmet dependencies',
					[
						'item_id'            => $item->id,
						'unmet_dependencies' => $unmet_dependencies,
					]
				);
				array_shift( $queue );
				continue;
			}


			$dependencies_not_added_yet = array_diff( $item->get_dependencies(), $queued_deps );

			// If all dependencies have not been added yet, push this to the bottom of the queue
			if ( ! empty( $dependencies_not_added_yet ) ) {
				$queue_item = array_shift( $queue );
				$queue[]    = $queue_item;
				continue;
			}

			// If all dependencies have been added, add this after the last dependency
			if ( empty( $dependencies_not_added_yet ) ) {
				$last_dependency_key = $this->get_last_dependency( $item, $items );
				if(0 === $last_dependency_key){
					array_unshift($items, $item);
				} else {
					$items         = array_merge(
						array_slice( $items, 0, $last_dependency_key + 1 ),
						[ $item ],
						array_slice( $items, $last_dependency_key + 1, count( $items ) - $last_dependency_key + 1 )
					);
				}
				$queued_deps[] = $item->get_id();
				array_shift( $queue );
			}
		}

		return $items;
	}

	protected function get_last_dependency( Item_With_Dependencies $item, $items ) {
		$last_dependency = 0;
		foreach ( $items as $key => $value ) {
			/* @var Item_With_Dependencies $value */
			$found_dependencies = in_array( $value->get_id(), $item->get_dependencies() );
			if ( ! empty( $found_dependencies ) ) {
				$last_dependency = $key;
				continue;
			}

			// If both items have the same dependencies, use priority.
			if ( ! empty( array_intersect( $value->get_dependencies(), $item->get_dependencies() ) ) ) {
				if ( $item->get_priority() > $value->get_priority() ) {
					$last_dependency = $key;
				}
			}
		}

		return $last_dependency;
	}

	public static function prepare( $items ) {
		$instance = new self( $items );
		return $instance->filter_dependencies();
	}

}