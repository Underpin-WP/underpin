<?php

namespace Underpin\Factories\Sort_Methods;


use Underpin\Abstracts\Sort_Method;
use Underpin\Enums\Direction;
use Underpin\Exceptions\Exception;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Helpers\Object_Helper;

class Basic extends Sort_Method {

	public function sort( object $a, object $b, string $field, Direction $direction ): int {
		$item_a = Object_Helper::pluck( $a, $field );
		$item_b = Object_Helper::pluck( $b, $field );


		switch ( gettype( $item_a ) ) {
			case 'double':
			case "integer":
				$result = $item_a <=> $item_b;
				break;
			case "array":
				$result = count( $item_a ) <=> count( $item_b );
				break;
			case "boolean":
			case "NULL":
				$result = (int) $item_a <=> (int) $item_b;
				break;
			default:
				try {
					settype( $item_a, 'string' );
					settype( $item_b, 'string' );
					$result = strcmp( (string) $item_a, (string) $item_b );
				} catch ( Exception $e ) {
					throw new Operation_Failed( 'could not sort items using basic sort method.', previous: $e );
				}
		}

		if ( $direction === Direction::Descending ) {
			$result = $result * -1;
		}

		return $result;
	}

}