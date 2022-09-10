<?php

namespace Underpin\Helpers\Processors;

use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Abstracts\Registry_Mutator;
use Underpin\Factories\Log_Item;
use Underpin\Helpers\Array_Helper;
use Underpin\Interfaces\Item_With_Dependencies;
use Underpin\Registries\Logger;


class Dependency_Processor extends Registry_Mutator {

	private function get_dependencies( Item_With_Dependencies $item ): array {
		$deps = $item->get_dependencies();
		foreach ( $item->get_dependencies() as $dep ) {
			$dep_item = $this->items->query()->equals( 'id', $dep )->find();
			if ( $dep_item ) {
				/** @var Item_With_Dependencies $dep_item */
				$deps = array_merge( $deps, $this->get_dependencies( $dep_item ) );
			}
		}

		return $deps;
	}

	public function filter_dependencies(): array {
		$queue       = array_values( $this->items->to_array() );
		$items       = [];
		$queued_deps = [];

		// If there's zero, or 1 item in the array, there's nothing to sort. Just return it.
		if ( count( $queue ) < 2 ) {
			return $queue;
		}

		while ( ! empty( $queue ) ) {
			/* @var Item_With_Dependencies $item */
			$item = $queue[0];

			// If this item depends on something that doesn't exist, skip it.
			$unmet_dependencies = array_diff( $this->get_dependencies( $item ), $this->items->pluck( 'id' ) );

			if ( ! empty( $unmet_dependencies ) ) {
				Logger::log(
					'debug',
					new Log_Item(
						code   : 'observer_detached',
						message: 'An event was detached because it has unmet dependencies',
						context: 'id',
						ref    : $item->get_id(),
						data   : [
							'unmet_dependencies' => $unmet_dependencies,
						]
					)
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
			$last_dependency_key = $this->get_last_dependency( $item, $items );
			if ( 0 === $last_dependency_key ) {
				array_unshift( $items, $item );
			} else {
				$items = array_merge(
					array_slice( $items, 0, $last_dependency_key + 1 ),
					[ $item ],
					array_slice( $items, $last_dependency_key + 1, count( $items ) - $last_dependency_key + 1 )
				);
			}
			$queued_deps[] = $item->get_id();
			array_shift( $queue );
		}

		return $items;
	}

	protected function get_last_dependency( Item_With_Dependencies $item, $items ): int|string {
		$last_dependency = 0;
		foreach ( $items as $key => $value ) {
			/* @var Item_With_Dependencies $value */
			$found_dependencies = in_array( $value->get_id(), $item->get_dependencies() );
			if ( ! empty( $found_dependencies ) ) {
				$last_dependency = $key;
				continue;
			}

			// If both items have the same dependencies, use priority.
			if ( ! empty( Array_Helper::intersect( $value->get_dependencies(), $item->get_dependencies() ) ) ) {
				if ( $item->get_priority() > $value->get_priority() ) {
					$last_dependency = $key;
				}
			}
		}

		return $last_dependency;
	}

	public function mutate(): Object_Registry {
		return $this->items->seed( $this->filter_dependencies() );
	}

}